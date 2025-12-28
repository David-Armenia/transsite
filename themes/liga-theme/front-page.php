<?php get_header(); ?>

<section class="hero">
  <div class="container">
    <h1><?php echo get_theme_mod('hero_title'); ?></h1>
    <p><?php echo get_theme_mod('hero_text'); ?></p>
    <a href="#" class="btn-primary">View profiles</a>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2>Recommendations</h2>

    <div class="card-grid">
      <?php for ($i=0; $i<8; $i++): ?>
        <div class="card">
          <img src="https://placehold.co/300x400" alt="">
          <h3>Name</h3>
          <p>Moscow</p>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2>Latest Videos</h2>

    <div class="card-grid">
      <?php for ($i=0; $i<4; $i++): ?>
        <div class="card">
          <img src="https://placehold.co/400x250" alt="">
          <h3>Video title</h3>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<?php get_footer(); ?>
