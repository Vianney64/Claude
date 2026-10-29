#!/usr/bin/env python3
"""Montage automatique d'une vidéo verticale 9:16 pour TikTok / Facebook.

Usage :
    python3 tools/montage.py DOSSIER [-o sortie.mp4] [--dry-run]
    python3 tools/montage.py --selftest

Le dossier d'entrée contient :
    plans.json   liste de {"d": durée en s, "calque": "rouge" ou "vert", "texte": "..."}
                 (les autres clés, comme "prompt", sont ignorées)
    img01.png .. imgNN.png   une image par plan, dans l'ordre des plans (format libre,
                 recadrée au centre en 9:16)
    voix.mp3     la voix off
    packshot.png optionnel, affiché sur la carte finale

Sortie : 1080x1920, 30 images/s, H.264 (preset veryfast, CRF 23) + AAC.

Montage :
    - les durées des plans sont recalées proportionnellement sur la durée de voix.mp3 ;
    - zoom lent (Ken Burns) sur chaque plan ;
    - calque rouge semi-transparent pulsé ou calque vert léger selon le plan ;
    - texte du plan en gros caractères blancs gras à contour noir, dans le tiers haut,
      marges de 80 px, rien dans les 150 px du haut ni dans les 300 px du bas ;
    - carte finale de 3 s orange #c63f17 : LIVREZ-MOI, paiement et livraison, téléphone.

Dépendances : Python 3, Pillow, ffmpeg et ffprobe dans le PATH, police DejaVu Sans Bold.
"""
import argparse
import json
import math
import os
import shutil
import subprocess
import sys
import tempfile

from PIL import Image, ImageDraw, ImageFont

W, H, FPS = 1080, 1920, 30
MARGE_X = 80
ZONE_HAUT = 150          # rien dans les 150 px du haut
ZONE_BAS = 300           # ni dans les 300 px du bas
TEXTE_HAUT = ZONE_HAUT + 20
TEXTE_BAS = H // 3       # le texte reste dans le tiers haut
CARTE_SECONDES = 3
ORANGE = (0xC6, 0x3F, 0x17)
BLANC = (255, 255, 255)
NOIR = (0, 0, 0)
ROUGE = (210, 25, 25)
VERT = (30, 170, 80)
TEL = "+229 01 94 80 27 20"

POLICES = [
    "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf",
    "/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf",
    "/usr/share/fonts/TTF/DejaVuSans-Bold.ttf",
    "/Library/Fonts/DejaVuSans-Bold.ttf",
    "C:/Windows/Fonts/DejaVuSans-Bold.ttf",
]


class MontageError(Exception):
    pass


# ----------------------------------------------------------------- polices
_chemin_police = None


def chemin_police():
    global _chemin_police
    if _chemin_police:
        return _chemin_police
    for p in POLICES:
        if os.path.isfile(p):
            _chemin_police = p
            return p
    if shutil.which("fc-match"):
        r = subprocess.run(["fc-match", "-f", "%{file}", "DejaVu Sans:bold"],
                           capture_output=True, text=True)
        if r.returncode == 0 and os.path.isfile(r.stdout.strip()) and "DejaVu" in r.stdout:
            _chemin_police = r.stdout.strip()
            return _chemin_police
    raise MontageError("police DejaVu Sans Bold introuvable (paquet fonts-dejavu-core)")


_cache_polices = {}


def police(taille):
    if taille not in _cache_polices:
        _cache_polices[taille] = ImageFont.truetype(chemin_police(), taille)
    return _cache_polices[taille]


# ----------------------------------------------------------------- texte
def decouper(texte, font, largeur_max):
    """Retour à la ligne automatique. Renvoie None si un mot dépasse la largeur."""
    lignes = []
    for paragraphe in texte.split("\n"):
        courante = ""
        for mot in paragraphe.split():
            if font.getlength(mot) > largeur_max:
                return None
            essai = mot if not courante else courante + " " + mot
            if font.getlength(essai) <= largeur_max:
                courante = essai
            else:
                lignes.append(courante)
                courante = mot
        lignes.append(courante)
    return [l for l in lignes if l] or [""]


def ajuster(texte, largeur_max, hauteur_max, taille_max=104, taille_min=40):
    """Plus grande taille de police pour laquelle le texte tient dans la boîte."""
    for taille in range(taille_max, taille_min - 1, -4):
        font = police(taille)
        contour = max(3, round(taille / 11))
        lignes = decouper(texte, font, largeur_max - 2 * contour)
        if lignes is None:
            continue
        pas = round(taille * 1.18)
        if len(lignes) * pas + 2 * contour <= hauteur_max:
            return font, contour, lignes, pas
    raise MontageError("texte trop long pour la zone disponible : " + repr(texte))


def couche_texte(texte):
    """Calque RGBA 1080x1920 transparent portant le texte du plan (ou None)."""
    texte = (texte or "").strip()
    if not texte:
        return None
    font, contour, lignes, pas = ajuster(texte, W - 2 * MARGE_X, TEXTE_BAS - TEXTE_HAUT)
    calque = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    d = ImageDraw.Draw(calque)
    y = TEXTE_HAUT + contour
    for ligne in lignes:
        d.text((W / 2, y), ligne, font=font, fill=BLANC, stroke_width=contour,
               stroke_fill=NOIR, anchor="mt")
        y += pas
    x0, y0, x1, y1 = calque.getchannel("A").getbbox()
    if x0 < MARGE_X or x1 > W - MARGE_X or y0 < ZONE_HAUT or y1 > min(TEXTE_BAS, H - ZONE_BAS):
        raise MontageError("texte hors zone sûre (%s) : %r" % ((x0, y0, x1, y1), texte))
    return calque


# ----------------------------------------------------------------- images
def charger_image(chemin):
    img = Image.open(chemin).convert("RGB")
    if img.height > 2 * H:                      # inutile de garder 8K en mémoire
        img.thumbnail((2 * W, 2 * H), Image.LANCZOS)
    return img


def boite_couverture(img):
    """Plus grande boîte 9:16 centrée dans l'image (recadrage « cover »)."""
    cible = W / H
    if img.width / img.height > cible:
        h = img.height
        w = h * cible
    else:
        w = img.width
        h = w / cible
    x0 = (img.width - w) / 2
    y0 = (img.height - h) / 2
    return x0, y0, w, h


DIRECTIONS = [(-1, 0), (1, 0), (0, -1), (0, 1)]


def image_kenburns(img, boite, i, u):
    """Image du plan i à l'instant relatif u (0 à 1) : zoom lent et léger déplacement."""
    x0, y0, w, h = boite
    z0, z1 = (1.0, 1.12) if i % 2 == 0 else (1.12, 1.0)
    z = z0 + (z1 - z0) * u
    bw, bh = w / z, h / z
    dx, dy = DIRECTIONS[i % 4]
    cx = x0 + w / 2 + dx * 0.6 * (u - 0.5) * (w - bw)
    cy = y0 + h / 2 + dy * 0.6 * (u - 0.5) * (h - bh)
    box = (cx - bw / 2, cy - bh / 2, cx + bw / 2, cy + bh / 2)
    return img.resize((W, H), Image.BICUBIC, box=box)


def alpha_calque(calque, t):
    if calque == "rouge":                        # pulsation d'environ 1 Hz
        return 0.26 + 0.11 * math.sin(2 * math.pi * t)
    return 0.14                                  # vert léger et fixe


# ----------------------------------------------------------------- carte finale
def carte_finale(packshot=None):
    img = Image.new("RGB", (W, H), ORANGE)
    d = ImageDraw.Draw(img)
    sombre = (80, 20, 5)
    largeur = W - 2 * MARGE_X

    def bloc(texte, taille_max, taille_min):
        for taille in range(taille_max, taille_min - 1, -4):
            font = police(taille)
            contour = max(2, round(taille / 16))
            lignes = decouper(texte, font, largeur - 2 * contour)
            if lignes is not None:
                return font, contour, lignes, round(taille * 1.2)
        raise MontageError("texte de carte finale trop long : " + texte)

    blocs = [bloc("LIVREZ-MOI", 160, 60),
             bloc("Paiement à la livraison · Livraison gratuite", 58, 36),
             bloc(TEL, 76, 40)]
    hauteurs = [len(b[2]) * b[3] for b in blocs]
    ecarts = [50, 40]
    photo = None
    if packshot is not None:
        photo = packshot.convert("RGBA")
        photo.thumbnail((760, 640), Image.LANCZOS)
    total = sum(hauteurs) + sum(ecarts) + ((photo.height + 60) if photo else 0)
    haut, bas = ZONE_HAUT + 40, H - ZONE_BAS - 40
    y = haut + max(0, (bas - haut - total) // 2)
    if photo:
        img.paste(photo, ((W - photo.width) // 2, y), photo)
        y += photo.height + 60
    for k, (font, contour, lignes, pas) in enumerate(blocs):
        for ligne in lignes:
            d.text((W / 2, y), ligne, font=font, fill=BLANC, stroke_width=contour,
                   stroke_fill=sombre, anchor="mt")
            y += pas
        if k < len(ecarts):
            y += ecarts[k]
    return img


# ----------------------------------------------------------------- entrées
def duree_audio(chemin):
    if not shutil.which("ffprobe"):
        raise MontageError("ffprobe introuvable dans le PATH")
    r = subprocess.run(["ffprobe", "-v", "error", "-show_entries", "format=duration",
                        "-of", "default=nw=1:nk=1", chemin], capture_output=True, text=True)
    try:
        duree = float(r.stdout.strip())
    except ValueError:
        raise MontageError("durée illisible pour " + chemin + " : " + r.stderr.strip())
    if duree <= 0:
        raise MontageError("voix.mp3 est vide")
    return duree


def lire_plans(dossier):
    chemin = os.path.join(dossier, "plans.json")
    if not os.path.isfile(chemin):
        raise MontageError("plans.json introuvable dans " + dossier)
    with open(chemin, encoding="utf-8") as f:
        plans = json.load(f)
    if not isinstance(plans, list) or not plans:
        raise MontageError("plans.json doit être une liste non vide")
    for i, p in enumerate(plans, 1):
        if not isinstance(p, dict) or not isinstance(p.get("d"), (int, float)) or p["d"] <= 0:
            raise MontageError("plan %d : durée « d » invalide" % i)
        if p.get("calque") not in ("rouge", "vert"):
            raise MontageError("plan %d : « calque » doit valoir rouge ou vert" % i)
        if not isinstance(p.get("texte", ""), str):
            raise MontageError("plan %d : « texte » doit être une chaîne" % i)
    return plans


def recaler(durees, total_images):
    """Nombre d'images par plan, proportionnel aux durées, somme exacte."""
    somme = float(sum(durees))
    bornes, cumul = [], 0.0
    for d in durees:
        cumul += d
        bornes.append(round(cumul / somme * total_images))
    nb, prec = [], 0
    for b in bornes:
        nb.append(max(1, b - prec))
        prec = b
    nb[-1] += total_images - sum(nb)             # absorbe l'arrondi
    if min(nb) < 1:
        raise MontageError("voix.mp3 trop courte pour le nombre de plans")
    return nb


# ----------------------------------------------------------------- montage
def monter(dossier, sortie, dry_run=False, log=sys.stderr):
    plans = lire_plans(dossier)
    n = len(plans)
    chemins = [os.path.join(dossier, "img%02d.png" % (i + 1)) for i in range(n)]
    manquants = [os.path.basename(c) for c in chemins if not os.path.isfile(c)]
    if manquants:
        raise MontageError("images manquantes : " + ", ".join(manquants))
    voix = os.path.join(dossier, "voix.mp3")
    if not os.path.isfile(voix):
        raise MontageError("voix.mp3 introuvable dans " + dossier)
    if not shutil.which("ffmpeg"):
        raise MontageError("ffmpeg introuvable dans le PATH")

    duree_voix = duree_audio(voix)
    images_voix = round(duree_voix * FPS)
    nb = recaler([p["d"] for p in plans], images_voix)
    images_carte = CARTE_SECONDES * FPS
    total = images_voix + images_carte
    somme_d = sum(p["d"] for p in plans)
    print("voix : %.2f s (plans demandés : %.2f s) -> %d plans, carte finale %d s"
          % (duree_voix, somme_d, n, CARTE_SECONDES), file=log)
    for i, (p, k) in enumerate(zip(plans, nb), 1):
        print("  plan %2d : %.2f s -> %.2f s (%s)" % (i, p["d"], k / FPS, p["calque"]), file=log)

    # on prépare tout avant de lancer ffmpeg : une erreur de texte arrête tout
    calques_texte = [couche_texte(p.get("texte", "")) for p in plans]
    packshot_chemin = os.path.join(dossier, "packshot.png")
    packshot = Image.open(packshot_chemin) if os.path.isfile(packshot_chemin) else None
    carte = carte_finale(packshot)
    if dry_run:
        return

    cmd = ["ffmpeg", "-y", "-loglevel", "error", "-f", "rawvideo", "-pix_fmt", "rgb24",
           "-s", "%dx%d" % (W, H), "-r", str(FPS), "-i", "-", "-i", voix,
           "-map", "0:v:0", "-map", "1:a:0", "-af", "apad", "-t", "%.4f" % (total / FPS),
           "-c:v", "libx264", "-preset", "veryfast", "-crf", "23", "-pix_fmt", "yuv420p",
           "-r", str(FPS), "-c:a", "aac", "-b:a", "160k", "-movflags", "+faststart", sortie]
    proc = subprocess.Popen(cmd, stdin=subprocess.PIPE, stderr=subprocess.PIPE)
    try:
        t_global = 0
        for i, (p, k) in enumerate(zip(plans, nb)):
            img = charger_image(chemins[i])
            boite = boite_couverture(img)
            solide = Image.new("RGB", (W, H), ROUGE if p["calque"] == "rouge" else VERT)
            texte = calques_texte[i]
            for f in range(k):
                u = f / (k - 1) if k > 1 else 0.0
                frame = image_kenburns(img, boite, i, u)
                frame = Image.blend(frame, solide, alpha_calque(p["calque"], t_global / FPS))
                if texte is not None:
                    frame = Image.alpha_composite(frame.convert("RGBA"), texte).convert("RGB")
                proc.stdin.write(frame.tobytes())
                t_global += 1
            print("  plan %d/%d rendu" % (i + 1, n), file=log)
        octets = carte.tobytes()
        for _ in range(images_carte):
            proc.stdin.write(octets)
        proc.stdin.close()
    except BrokenPipeError:
        pass
    except BaseException:
        proc.kill()
        raise
    erreur = proc.stderr.read().decode("utf-8", "replace")
    if proc.wait() != 0:
        raise MontageError("ffmpeg a échoué :\n" + erreur)
    print("écrit : %s" % sortie, file=log)


def sonder(chemin):
    r = subprocess.run(["ffprobe", "-v", "error", "-show_entries",
                        "stream=codec_name,codec_type,width,height,r_frame_rate,pix_fmt:format=duration",
                        "-of", "json", chemin], capture_output=True, text=True)
    return json.loads(r.stdout)


# ----------------------------------------------------------------- auto-test
def _image_synthetique(chemin, taille, teinte, k):
    w, h = taille
    img = Image.new("RGB", taille)
    px = img.load()
    for y in range(h):
        for x in range(0, w):
            px[x, y] = (int(teinte[0] * (0.5 + 0.5 * y / h)), int(teinte[1] * (0.5 + 0.5 * x / w)),
                        int(teinte[2] * (0.6 + 0.4 * (x + y) / (w + h))))
    d = ImageDraw.Draw(img)
    cx, cy, r = w // 2, int(h * 0.55), int(min(w, h) * 0.22)
    d.ellipse((cx - r, cy - r, cx + r, cy + r), fill=(235, 190, 150))          # visage
    d.ellipse((cx - r // 2, cy - r // 3, cx - r // 4, cy - r // 6), fill=(30, 20, 20))
    d.ellipse((cx + r // 4, cy - r // 3, cx + r // 2, cy - r // 6), fill=(30, 20, 20))
    d.arc((cx - r // 2, cy, cx + r // 2, cy + r // 2), 20, 160, fill=(120, 40, 40), width=6)
    d.rectangle((k * 40, h - 120, k * 40 + 160, h - 60), fill=(255, 255, 0))   # repère de recadrage
    img.save(chemin)


def selftest():
    if not shutil.which("ffmpeg"):
        raise MontageError("ffmpeg introuvable dans le PATH")
    with tempfile.TemporaryDirectory(prefix="montage_test_") as dossier:
        plans = [
            {"d": 3, "calque": "rouge", "texte": "Mon frère, écoute-moi"},
            {"d": 4, "calque": "rouge", "texte": "Ton visage, tu le soignes"},
            {"d": 4, "calque": "vert", "texte": "Soin pour peau sensible · 20 g"},
            {"d": 4, "calque": "vert", "texte": "Douleur, gonflement ou saignement : consulte un médecin"},
            {"d": 4, "calque": "vert", "texte": "Livrez-moi · Commande avant le 15 décembre · Paiement à la livraison"},
        ]
        with open(os.path.join(dossier, "plans.json"), "w", encoding="utf-8") as f:
            json.dump(plans, f, ensure_ascii=False)
        tailles = [(720, 1280), (1024, 1024), (1080, 1920), (1280, 720), (1536, 2730)]
        for i, t in enumerate(tailles):
            _image_synthetique(os.path.join(dossier, "img%02d.png" % (i + 1)), t,
                               [(220, 180, 120), (120, 200, 160), (160, 120, 220), (200, 140, 100), (110, 160, 230)][i], i)
        pack = Image.new("RGBA", (500, 700), (0, 0, 0, 0))
        ImageDraw.Draw(pack).rounded_rectangle((60, 40, 440, 660), 60, fill=(250, 250, 240, 255), outline=(60, 60, 60, 255), width=8)
        pack.save(os.path.join(dossier, "packshot.png"))
        r = subprocess.run(["ffmpeg", "-y", "-loglevel", "error", "-f", "lavfi", "-i",
                            "sine=frequency=330:duration=12.4", "-c:a", "libmp3lame",
                            os.path.join(dossier, "voix.mp3")], capture_output=True, text=True)
        if r.returncode != 0:
            raise MontageError("génération du son impossible : " + r.stderr)
        sortie = os.path.join(dossier, "sortie.mp4")
        monter(dossier, sortie)
        info = sonder(sortie)
        video = next(s for s in info["streams"] if s["codec_type"] == "video")
        audio = next(s for s in info["streams"] if s["codec_type"] == "audio")
        duree = float(info["format"]["duration"])
        attendu = 12.4 + CARTE_SECONDES
        assert (video["width"], video["height"]) == (W, H), video
        assert video["codec_name"] == "h264" and video["r_frame_rate"] == "30/1", video
        assert audio["codec_name"] == "aac", audio
        assert abs(duree - attendu) < 0.15, (duree, attendu)
        print("auto-test réussi : %dx%d %s %s, %.2f s (attendu %.2f s), audio %s"
              % (video["width"], video["height"], video["codec_name"], video["r_frame_rate"],
                 duree, attendu, audio["codec_name"]))


def main(argv=None):
    ap = argparse.ArgumentParser(description="Montage vidéo 9:16 Keshi store")
    ap.add_argument("dossier", nargs="?", help="dossier contenant plans.json, img01.png.., voix.mp3")
    ap.add_argument("-o", "--output", help="fichier de sortie (défaut : DOSSIER/montage.mp4)")
    ap.add_argument("--dry-run", action="store_true", help="vérifie les entrées et affiche les durées, sans rendre")
    ap.add_argument("--selftest", action="store_true", help="teste le montage sur des données générées")
    a = ap.parse_args(argv)
    try:
        if a.selftest:
            selftest()
            return 0
        if not a.dossier:
            ap.error("dossier d'entrée requis (ou --selftest)")
        sortie = a.output or os.path.join(a.dossier, "montage.mp4")
        monter(a.dossier, sortie, dry_run=a.dry_run)
        return 0
    except MontageError as e:
        print("erreur : %s" % e, file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
