#!/usr/bin/env bash
# Instala WooCommerce (se houver acesso a wordpress.org), ativa o tema Ar Seco e povoa páginas, menus e catálogo de exemplo.
set -euo pipefail
cd "$(dirname "$0")"
./setup.sh
wp() { docker compose run --rm -T wpcli wp "$@"; }

if ! wp plugin is-installed woocommerce 2>/dev/null; then
  # Zip local (vendor/woocommerce.zip) em primeiro lugar; senão, wordpress.org.
  SRC=woocommerce
  [ -f vendor/woocommerce.zip ] && SRC=/vendor/woocommerce.zip
  wp plugin install "$SRC" --activate \
    || echo "AVISO: WooCommerce não instalado (sem acesso a wordpress.org). Carrinho, checkout e catálogo ficam inativos."
else
  wp plugin activate woocommerce || true
fi
wp theme activate arseco
# Pacote de tradução oficial, se houver rede (senão o tema usa o dicionário de recurso).
wp language plugin install woocommerce pt_PT 2>/dev/null || echo "AVISO: sem pacote pt_PT oficial; a usar traduções de recurso do tema."
# Duas passagens: a 1.ª define as bases de URL (produto, categoria), a 2.ª cria menus com essas URL.
wp eval-file wp-content/themes/arseco/inc/seed.php
wp eval-file wp-content/themes/arseco/inc/seed.php
wp rewrite flush --hard
