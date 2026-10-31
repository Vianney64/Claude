<?php get_header(); ?>
<div class="page-body container">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  <article <?php post_class('prose'); ?>>
    <h1><?php the_title(); ?></h1>
    <?php the_content(); ?>
  </article>
<?php endwhile; else : ?>
  <article class="prose"><h1>Nada encontrado</h1><p>Não encontrámos o que procurava. <a href="<?php echo esc_url(home_url('/')); ?>">Voltar ao início</a>.</p></article>
<?php endif; ?>
</div>
<?php get_footer(); ?>
