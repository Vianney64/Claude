#!/usr/bin/env bash
# Démarre la stack et installe WordPress via WP-CLI (idempotent).
set -euo pipefail
cd "$(dirname "$0")"
[ -f .env ] || cp .env.example .env
set -a; . ./.env; set +a

docker compose up -d db wordpress

echo "Attente de WordPress..."
for _ in $(seq 1 60); do
  curl -fsS -o /dev/null "http://localhost:${WP_PORT}/" 2>/dev/null && break || \
  [ "$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${WP_PORT}/")" = 302 ] && break
  sleep 2
done

wp() { docker compose run --rm -T wpcli wp "$@"; }

if wp core is-installed 2>/dev/null; then
  echo "WordPress déjà installé."
else
  wp core install --url="$WP_URL" --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" --skip-email
  wp language core install fr_FR --activate \
    || echo "AVERTISSEMENT : pack fr_FR non installé (accès à api.wordpress.org requis); site en anglais."
  wp rewrite structure '/%postname%/' --hard
  wp post create --post_title="Article de test" --post_status=publish \
    --post_content="Contenu de simulation." >/dev/null
fi
wp core version
echo "Site : $WP_URL   Admin : $WP_URL/wp-admin ($WP_ADMIN_USER)"
