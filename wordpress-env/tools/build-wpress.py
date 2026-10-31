#!/usr/bin/env python3
"""
Génère une archive .wpress (format All-in-One WP Migration) à partir du site local.

Format: suite de blocs [en-tête 4377 octets + contenu] terminée par un bloc de 4377 octets nuls.
En-tête: nom (255) | taille (14) | date (12) | dossier (4096), texte décimal/ASCII complété par des octets nuls.
Contenu: package.json, database.sql (préfixe remplacé par SERVMASK_PREFIX_), themes/, plugins/, uploads/.

Limite connue: le résultat n'a pas été importé avec le vrai plugin (indisponible ici).
Usage: tools/build-wpress.py SORTIE.wpress
"""
import json, os, re, subprocess, sys, tempfile, time
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
HEADER = 4377
NAME, SIZE, MTIME, PATH = 255, 14, 12, 4096
PREFIX = 'wp_'
MARK = 'SERVMASK_PREFIX_'


def env():
    out = {}
    for line in (ROOT / '.env').read_text().splitlines():
        if '=' in line and not line.startswith('#'):
            k, v = line.split('=', 1)
            out[k] = v.strip().strip('"')
    return out


def sh(*cmd, **kw):
    return subprocess.run(cmd, cwd=ROOT, check=True, capture_output=True, **kw).stdout


def wp(*args):
    return sh('docker', 'compose', 'run', '--rm', '-T', 'wpcli', 'wp', *args).decode().strip().splitlines()[-1]


def dump_database(e):
    raw = sh('docker', 'compose', 'exec', '-T', 'db', 'mariadb-dump', '-uroot', f"-p{e['WP_DB_ROOT_PASSWORD']}",
             '--no-tablespaces', '--single-transaction', '--skip-add-locks', '--skip-comments',
             '--default-character-set=utf8mb4', e['WP_DB_NAME']).decode('utf8')
    # 1. linha de "sandbox mode" do MariaDB 11.8: inútil e estranha para outros servidores
    raw = '\n'.join(l for l in raw.splitlines() if not l.startswith('/*M!999999'))
    # 2. collations exclusivas do MariaDB 11.x -> equivalente que MySQL e MariaDB antigos conhecem
    raw = re.sub(r'utf8mb4_uca1400_\w+', 'utf8mb4_unicode_520_ci', raw)
    # 3. prefixo das tabelas
    raw = re.sub(r'`' + PREFIX, '`' + MARK, raw)
    # 4. chaves que embutem o prefixo (opções e metadados de utilizador). Só as conhecidas, entre aspas:
    #    substituir às cegas corromperia dados serializados.
    keys = ['user_roles', 'capabilities', 'user_level', 'user-settings', 'user-settings-time',
            'dashboard_quick_press_last_post_id', 'persisted_preferences']
    raw = re.sub(r"'" + PREFIX + r"(" + '|'.join(re.escape(k) for k in keys) + r")'", r"'" + MARK + r"\1'", raw)
    return raw.encode('utf8')


def entries(base, rel_root, skip=()):
    """(caminho no disco, dossier no arquivo, nome) para todos os ficheiros sob base."""
    for dirpath, dirs, files in os.walk(base):
        dirs[:] = sorted(d for d in dirs if d not in skip)
        for f in sorted(files):
            full = Path(dirpath) / f
            if full.is_symlink():
                continue
            rel = Path(dirpath).relative_to(base)
            folder = '/'.join([rel_root] + list(rel.parts))
            yield full, folder, f


def header(name, size, mtime, folder):
    parts = [(name, NAME), (str(size), SIZE), (str(int(mtime)), MTIME), (folder, PATH)]
    out = b''
    for text, width in parts:
        b = text.encode('utf8')
        if len(b) > width:
            raise ValueError(f'campo demasiado longo ({width}): {text[:60]}')
        out += b.ljust(width, b'\0')
    assert len(out) == HEADER
    return out


def main(dest):
    e = env()
    url = e.get('WP_URL', 'http://localhost:8080').rstrip('/')
    tmp = Path(tempfile.mkdtemp(prefix='wpress-'))

    plugins = json.loads(wp('option', 'get', 'active_plugins', '--format=json'))
    wp_version = wp('core', 'version')
    cid = sh('docker', 'compose', 'ps', '-q', 'wordpress').decode().strip()
    for sub in ('plugins/woocommerce', 'uploads'):
        target = tmp / sub
        target.parent.mkdir(parents=True, exist_ok=True)
        sh('docker', 'cp', f'{cid}:/var/www/html/wp-content/{sub}', str(target))

    package = {
        'SiteURL': url, 'HomeURL': url, 'InternalSiteURL': None, 'InternalHomeURL': None,
        'Plugin': {'Version': '7.113'},
        'PHP': {'Version': '8.3', 'System': 'Linux', 'Integer': 8},
        'MySQL': {'Version': '8.0'},
        'WordPress': {'Version': wp_version, 'Absolute': '/var/www/html/', 'Content': '/var/www/html/wp-content',
                      'Plugins': '/var/www/html/wp-content/plugins', 'Themes': '/var/www/html/wp-content/themes',
                      'Uploads': '/var/www/html/wp-content/uploads', 'UploadsURL': f'{url}/wp-content/uploads'},
        'Uploads': '/var/www/html/wp-content/uploads', 'UploadsURL': f'{url}/wp-content/uploads',
        'Plugins': plugins, 'Template': 'arseco', 'Stylesheet': 'arseco',
        'Database': {'Prefix': PREFIX, 'Charset': 'utf8mb4', 'Collate': 'utf8mb4_unicode_520_ci'},
        'Encrypted': False, 'EncryptedSignature': '', 'Compression': {'Enabled': False},
    }
    now = time.time()
    blobs = [('package.json', '.', json.dumps(package, ensure_ascii=False).encode('utf8'), now),
             ('database.sql', '.', dump_database(e), now)]

    n_files = 0
    with open(dest, 'wb') as out:
        for name, folder, data, mtime in blobs:
            out.write(header(name, len(data), mtime, folder)); out.write(data)
        sources = [(ROOT / 'wp-content/themes/arseco', 'themes/arseco', ()),
                   (tmp / 'plugins/woocommerce', 'plugins/woocommerce', ()),
                   (tmp / 'uploads', 'uploads', ('wc-logs', 'cache'))]
        for base, rel, skip in sources:
            for full, folder, name in entries(base, rel, skip):
                size = full.stat().st_size
                out.write(header(name, size, full.stat().st_mtime, folder))
                with open(full, 'rb') as fh:
                    while chunk := fh.read(1 << 20):
                        out.write(chunk)
                n_files += 1
        out.write(b'\0' * HEADER)  # fim do arquivo
    print(f'{dest}: {os.path.getsize(dest)/1e6:.1f} Mo, {n_files + len(blobs)} ficheiros')


if __name__ == '__main__':
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    main(sys.argv[1])
