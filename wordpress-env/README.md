# Environnement WordPress local (Docker)

Stack : WordPress (PHP 8.3 / Apache) + MariaDB 11 + WP-CLI. Thèmes et plugins
personnalisés montés depuis `wp-content/`.

## Utilisation

```bash
./setup.sh                                   # démarre et installe (idempotent)
docker compose run --rm wpcli wp plugin list # WP-CLI
docker compose down                          # arrêt (données conservées)
docker compose down -v                       # arrêt + suppression des données
```

Site : http://localhost:8080 — Admin : /wp-admin, identifiants dans `.env`
(copié depuis `.env.example`).

## Limites

- Identifiants par défaut = développement uniquement. Ne pas exposer ce port.
- Sans accès à wordpress.org depuis les conteneurs, l'installation de
  langues, thèmes et plugins du répertoire officiel échoue.
- WP-CLI ne peut pas régénérer `.htaccess` (avertissement attendu); celui
  du conteneur Apache gère déjà les permaliens.

## Loja Ar Seco (tema + conteúdos)

```bash
./setup-loja.sh   # instala WooCommerce (se houver rede), ativa o tema e povoa o site
```

- Tema: `wp-content/themes/arseco` (desenho inspirado na maquete fornecida; Inter Tight auto-alojada).
- Idioma dos conteúdos: português de Portugal. Dados da empresa vazios (`[a preencher]`).
- Capturas de ecrã em `captures/`.
- **Ler `MANQUANTS.md`**: catálogo de exemplo, WooCommerce não testado, texto jurídico por rever.
- Sem WooCommerce, a página inicial mostra o catálogo de exemplo só em ambiente local, e os links de produto apontam para `/loja/`, que não existe sem o plugin.
