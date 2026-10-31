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
