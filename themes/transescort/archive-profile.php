<?php get_header(); ?>

<main class="site-main">
  <section class="profiles-page">
    <div class="container">

      <header class="section-head">
        <h1 class="section-title"><?php post_type_archive_title(); ?></h1>
        <p class="section-subtitle">All profiles</p>
      </header>

      <?php if ( have_posts() ) : ?>
        <div class="cards-grid cards-grid--profiles">
          <?php while ( have_posts() ) : the_post(); ?>
            <article class="profile-card">
              <a class="profile-card__media" href="<?php the_permalink(); ?>">
                <?php if ( has_post_thumbnail() ) : ?>
                  <?php the_post_thumbnail('large', ['class' => 'profile-card__img']); ?>
                <?php else : ?>
                  <div class="profile-card__img profile-card__img--placeholder"></div>
                <?php endif; ?>

                <?php
                  $price = get_post_meta(get_the_ID(), 'price', true);
                  if ($price !== ''):
                ?>
                  <span class="profile-card__price"><?php echo esc_html($price); ?></span>
                <?php endif; ?>
              </a>

              <div class="profile-card__body">
                <h3 class="profile-card__title">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>

                <div class="profile-card__meta">
                  <?php
                    $city = get_post_meta(get_the_ID(), 'city', true);
                    if ($city) echo esc_html($city);
                  ?>
                </div>
              </div>
            </article>
          <?php endwhile; ?>
        </div>

        <div class="pagination" style="margin-top:24px;">
          <?php the_posts_pagination(); ?>
        </div>

      <?php else : ?>
        <p>No profiles found.</p>
      <?php endif; ?>

    </div>
  </section>
</main>

<?php get_footer(); ?>

