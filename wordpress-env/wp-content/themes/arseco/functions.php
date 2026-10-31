<?php
/**
 * Tema Ar Seco: carregamento dos módulos e configuração base.
 */
defined('ABSPATH') || exit;

define('ARSECO_VER', '1.0.0');

foreach (['company', 'art', 'demo', 'pages', 'shortcodes', 'woo', 'i18n'] as $module) {
    require_once get_theme_file_path("inc/{$module}.php");
}

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('woocommerce');
    register_nav_menus([
        'primary' => 'Menu principal',
        'footer'  => 'Rodapé: informação',
        'legal'   => 'Rodapé: legal',
    ]);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('arseco', get_theme_file_uri('assets/css/main.css'), [], ARSECO_VER);
    wp_enqueue_script('arseco', get_theme_file_uri('assets/js/main.js'), [], ARSECO_VER, ['strategy' => 'defer', 'in_footer' => true]);
});

add_action('wp_head', function () {
    printf(
        '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
        esc_url(get_theme_file_uri('assets/fonts/inter-tight-latin-wght-normal.woff2'))
    );
}, 1);

add_filter('body_class', function ($classes) {
    if (is_front_page()) {
        $classes[] = 'is-home';
    }
    return $classes;
});

// Cabeçalho sem ruído: remove emojis e oEmbed, que o tema não usa.
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_generator');
add_filter('xmlrpc_enabled', '__return_false');
