<?php
/**
 * Povoamento idempotente. Uso: wp eval-file wp-content/themes/arseco/inc/seed.php
 */
defined('ABSPATH') || exit;

update_option('blogname', 'Ar Seco');
update_option('blogdescription', 'Eletrodomésticos para uma casa mais saudável');
update_option('timezone_string', 'Europe/Lisbon');
update_option('date_format', 'd/m/Y');
update_option('time_format', 'H:i');
update_option('start_of_week', 1);

$page_ids = [];
foreach (arseco_pages() as $slug => [$title, $content]) {
    $existing = get_page_by_path($slug);
    $args = ['post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content];
    $page_ids[$slug] = $existing ? wp_update_post($args + ['ID' => $existing->ID]) : wp_insert_post($args);
}

$home = get_page_by_path('inicio');
$home_id = $home ? $home->ID : wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'inicio', 'post_title' => 'Início', 'post_content' => '']);
update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
update_option('permalink_structure', '/%postname%/');

// Produtos e páginas WooCommerce, só se o plugin estiver ativo.
if (class_exists('WooCommerce')) {
    update_option('woocommerce_currency', 'EUR');
    update_option('woocommerce_default_country', 'PT');
    update_option('woocommerce_price_decimal_sep', ',');
    update_option('woocommerce_price_thousand_sep', ' ');
    update_option('woocommerce_currency_pos', 'right_space');
    update_option('woocommerce_calc_taxes', 'no'); // por validar: ver MANQUANTS.md
    update_option('woocommerce_enable_guest_checkout', 'yes');
    update_option('woocommerce_enable_coupons', 'no');
    update_option('woocommerce_terms_page_id', $page_ids['termos-e-condicoes']);
    update_option('woocommerce_privacy_policy_page_id', $page_ids['politica-de-privacidade'] ?? 0);

    foreach (arseco_demo_products() as $d) {
        $existing = get_page_by_path($d['slug'], OBJECT, 'product');
        $product = $existing ? wc_get_product($existing->ID) : new WC_Product_Simple();
        $product->set_name($d['title']);
        $product->set_slug($d['slug']);
        $product->set_status('publish');
        $product->set_regular_price($d['price']);
        $attrs = [];
        $pos = 0;
        foreach ($d['attrs'] as $label => $value) {
            $a = new WC_Product_Attribute();
            $a->set_name($label);
            $a->set_options([$value]);
            $a->set_position($pos++);
            $a->set_visible(true);
            $a->set_variation(false);
            $attrs[] = $a;
        }
        $product->set_attributes($attrs);
        $term = term_exists($d['cat'], 'product_cat') ?: wp_insert_term($d['cat'], 'product_cat');
        $term_id = is_array($term) ? (int) $term['term_id'] : (int) $term;
        $product->set_category_ids([$term_id]);
        $pid = $product->save();
        update_post_meta($pid, '_arseco_tone', $d['tone']);
        update_post_meta($pid, '_arseco_demo', '1');
    }
}

// Menus.
foreach (arseco_menus() as $location => $items) {
    $menu = wp_get_nav_menu_object("arseco-$location") ?: wp_get_nav_menu_object(wp_create_nav_menu("arseco-$location"));
    foreach (wp_get_nav_menu_items($menu->term_id) ?: [] as $old) {
        wp_delete_post($old->ID, true);
    }
    foreach ($items as [$label, $target]) {
        $url = null;
        if (str_starts_with($target, 'cat:')) {
            if (!class_exists('WooCommerce')) {
                continue; // sem WooCommerce não há categoria: não se cria link para o vazio
            }
            $link = get_term_link(substr($target, 4), 'product_cat');
            $url = is_wp_error($link) ? null : $link;
        } elseif ($target === 'shop') {
            $url = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : null;
        } else {
            $url = get_permalink($page_ids[$target] ?? 0) ?: null;
        }
        if ($url) {
            wp_update_nav_menu_item($menu->term_id, 0, ['menu-item-title' => $label, 'menu-item-url' => $url, 'menu-item-status' => 'publish']);
        }
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations[$location] = $menu->term_id;
    set_theme_mod('nav_menu_locations', $locations);
}

flush_rewrite_rules();
echo "Seed concluído.\n";
