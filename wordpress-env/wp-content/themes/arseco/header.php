<?php defined('ABSPATH') || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#conteudo">Saltar para o conteúdo</a>
<header class="site-header">
  <div class="site-header__bar">
    <button class="icon-btn menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principal" aria-label="Abrir menu">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </button>
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(arseco_brand()); ?>, página inicial">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3c3.2 3.7 6 6.6 6 10a6 6 0 1 1-12 0c0-3.4 2.8-6.3 6-10z" fill="currentColor"/></svg>
      <span><?php echo esc_html(arseco_brand()); ?></span>
    </a>
    <nav id="menu-principal" class="nav" aria-label="Principal">
      <?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'fallback_cb' => false, 'depth' => 1]); ?>
    </nav>
    <div class="site-header__actions">
      <?php if (arseco_has_woo()) : ?>
        <a class="pill pill--light" href="<?php echo esc_url(wc_get_cart_url()); ?>">Carrinho <span class="count"><?php echo (int) WC()->cart->get_cart_contents_count(); ?></span></a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main id="conteudo">
