#!/usr/bin/env bash
# Instala WooCommerce (se houver acesso a wordpress.org), ativa o tema Ar Seco e povoa páginas, menus e catálogo de exemplo.
set -euo pipefail
cd "$(dirname "$0")"
./setup.sh
wp() { docker compose run --rm -T wpcli wp "$@"; }

if ! wp plugin is-installed woocommerce 2>/dev/null; then
  wp plugin install woocommerce --activate \
    || echo "AVISO: WooCommerce não instalado (sem acesso a wordpress.org). Carrinho, checkout e catálogo ficam inativos."
else
  wp plugin activate woocommerce || true
fi
wp theme activate arseco
wp eval-file wp-content/themes/arseco/inc/seed.php
