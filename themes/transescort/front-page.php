<?php get_header(); ?>

<main class="site-main">

  <?php get_template_part('template-parts/home/hero'); ?>
  <?php get_template_part('template-parts/home/section-recommend'); ?>

  <?php
    // City section (пока фикс "Москва", потом сделаем динамику)
    set_query_var('te_city', 'Москва');
    get_template_part('template-parts/home/section-city');
  ?>

  <?php get_template_part('template-parts/home/section-videos'); ?>

  <?php get_template_part('template-parts/home/section-reviews'); ?>




  <?php
  // City section (пока фикс "Москва", потом сделаем динамику из поиска/выбора города)


  ?>



</main>

<?php get_footer(); ?>

