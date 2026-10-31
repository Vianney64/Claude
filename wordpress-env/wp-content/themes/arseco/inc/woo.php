<?php
defined('ABSPATH') || exit;

function arseco_has_woo(): bool
{
    return class_exists('WooCommerce');
}

/** URL de uma categoria; sem WooCommerce, cai na página da loja (nunca num "#"). */
function arseco_cat_url(string $slug): string
{
    if (arseco_has_woo()) {
        $link = get_term_link($slug, 'product_cat');
        if (!is_wp_error($link)) {
            return $link;
        }
        return wc_get_page_permalink('shop');
    }
    return home_url('/loja/');
}

/**
 * Produtos para a página inicial. Com WooCommerce: os publicados. Sem ele e em ambiente local:
 * o catálogo de exemplo, para se poder rever o desenho.
 */
function arseco_home_products(int $limit = 4): array
{
    $out = [];
    if (arseco_has_woo()) {
        foreach (wc_get_products(['status' => 'publish', 'limit' => $limit, 'orderby' => 'date', 'order' => 'ASC']) as $p) {
            $attrs = [];
            foreach ($p->get_attributes() as $attr) {
                if ($attr instanceof WC_Product_Attribute) {
                    $attrs[wc_attribute_label($attr->get_name())] = $p->get_attribute($attr->get_name());
                }
            }
            $tone = get_post_meta($p->get_id(), '_arseco_tone', true) ?: 'pedra';
            $out[] = [
                'title' => $p->get_name(),
                'url'   => get_permalink($p->get_id()),
                'price' => $p->get_price() !== '' ? $p->get_price_html() : 'Preço por definir',
                'image' => $p->get_image_id() ? $p->get_image('woocommerce_single') : arseco_art($tone, 'art'),
                'attrs' => $attrs,
                'tone'  => $tone,
            ];
        }
        return $out;
    }
    if (wp_get_environment_type() === 'local') {
        foreach (array_slice(arseco_demo_products(), 0, $limit) as $d) {
            $out[] = [
                'title' => $d['title'],
                'url'   => home_url('/loja/'),
                'price' => number_format((float) $d['price'], 2, ',', ' ') . ' €',
                'image' => arseco_art($d['tone'], 'art'),
                'attrs' => $d['attrs'],
                'tone'  => $d['tone'],
            ];
        }
    }
    return $out;
}

// Filtros inertes sem WooCommerce: só são chamados quando ele corre.
// Botão final do checkout: formulação exigida em venda à distância (DL 24/2014).
add_filter('woocommerce_order_button_text', fn () => 'Encomenda com obrigação de pagar');
// Imagem provisória: a ilustração do tema, em vez do quadrado cinzento do WooCommerce.
add_filter('woocommerce_placeholder_img', fn ($html, $size, $dims) => arseco_art('pedra', 'art art--ph'), 10, 3);
// Sem preço riscado: só seria legítimo com prova do preço mais baixo dos 30 dias anteriores (Omnibus).
add_filter('woocommerce_format_sale_price', fn ($price, $regular, $sale) => $sale, 10, 3);

// Sem fotografia, a ficha mostra a ilustração do tema (não o ícone cinzento do WooCommerce).
add_filter('woocommerce_single_product_image_thumbnail_html', function ($html, $image_id) {
    global $product;
    if ($product instanceof WC_Product && !$product->get_image_id()) {
        $tone = get_post_meta($product->get_id(), '_arseco_tone', true) ?: 'pedra';
        return '<div class="woocommerce-product-gallery__image--placeholder">' . arseco_art($tone, 'art') . '</div>';
    }
    return $html;
}, 10, 2);

// Sem avaliações: não há compras verificadas que as sustentem.
add_filter('woocommerce_product_tabs', function ($tabs) {
    unset($tabs['reviews']);
    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = 'Ficha técnica';
    }
    return $tabs;
});
add_filter('woocommerce_product_get_rating_html', '__return_empty_string');
