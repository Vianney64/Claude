<?php get_header();
$is_woo = arseco_has_woo() && (is_cart() || is_checkout() || is_account_page());
?>
<div class="page-body container<?php echo $is_woo ? ' woo woo--page' : ''; ?>">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  <article <?php post_class($is_woo ? '' : 'prose'); ?>>
    <h1><?php the_title(); ?></h1>
    <?php the_content(); ?>
  </article>
<?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>
