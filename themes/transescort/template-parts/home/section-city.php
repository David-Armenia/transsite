<?php
$city = (string) get_query_var('te_city');
if (!$city) $city = 'Москва';

$q = new WP_Query([
  'post_type'      => 'profile',
  'post_status'    => 'publish',
  'posts_per_page' => 8,
  'orderby'        => 'date',
  'order'          => 'DESC',
  'meta_query'     => [
    [
      'key'     => '_profile_city',
      'value'   => $city,
      'compare' => 'LIKE'
    ]
  ],
]);
?>

<section class="home-section home-city">
  <div class="container">

    <div class="section-head section-head--spaced">
      <h2 class="section-title">Транссексуалы г. <?php echo esc_html($city); ?></h2>
    </div>

    <div class="cards-grid cards-grid--city" aria-label="Каталог анкет">

      <?php if ($q->have_posts()) : ?>

        <?php
          /* 1) PROMO = первый профиль (как в Figma: текст слева + фото справа) */
          $q->the_post();
          $promo_id = get_the_ID();
          $promo_title = get_the_title($promo_id);
          $promo_city  = get_post_meta($promo_id, '_profile_city', true);
          $promo_price = get_post_meta($promo_id, '_profile_price', true);

          $raw_excerpt = get_post_field('post_excerpt', $promo_id);
          $promo_text = $raw_excerpt ? $raw_excerpt : wp_trim_words( wp_strip_all_tags( get_post_field('post_content', $promo_id) ), 28, '…' );

          $promo_img = "";
          if (has_post_thumbnail($promo_id)) {
            $promo_img = get_the_post_thumbnail_url($promo_id, "large");
          }
          if (!$promo_img) {
            $raw = get_post_meta($promo_id, "_profile_gallery_ids", true);
            $ids = is_string($raw) ? maybe_unserialize($raw) : $raw;
            if (is_array($ids) && !empty($ids)) {
              $first_id = (int) $ids[0];
              if ($first_id) {
                $promo_img = wp_get_attachment_image_url($first_id, "large");
              }
            }
          }
          if (!$promo_img) {
            $promo_img = get_template_directory_uri() . "/assets/img/hero-bg.jpg";
          }
          $promo_link = get_permalink($promo_id);
        ?>

        <article class="profile-card profile-card--promo">
          <div class="profile-card__promo-left">

            <div class="profile-card__promo-top">
              <div class="profile-card__promo-avatar"></div>
              <div class="profile-card__promo-meta">
                <div class="profile-card__promo-name"><?php echo esc_html($promo_title); ?></div>
                <div class="profile-card__promo-sub">
                  <?php if ($promo_city) : ?><span><?php echo esc_html($promo_city); ?></span><?php endif; ?>
                  <?php if ($promo_price) : ?><span class="profile-card__promo-dot">•</span><span><?php echo esc_html($promo_price); ?></span><?php endif; ?>
                </div>
              </div>
            </div>

            <div class="profile-card__promo-text"><?php echo esc_html($promo_text); ?></div>

            <a class="profile-card__promo-btn" href="<?php echo esc_url($promo_link); ?>">
              Перейти к анкете <span aria-hidden="true">→</span>
            </a>
          </div>

          <a class="profile-card__promo-media" href="<?php echo esc_url($promo_link); ?>" aria-label="Открыть анкету">
<?php
$price_raw = get_post_meta($promo_id, "_profile_price", true);
$price = "";
if ($price_raw !== "") {
  $num = preg_replace("/[^0-9]/", "", $price_raw);
  if ($num) $price = "от " . number_format((int)$num, 0, ".", " ") . "₽";
}
?>
<?php if ($price): ?>
<span class="profile-card__chip"><?php echo esc_html($price); ?></span>
<?php endif; ?>

            <img src="<?php echo esc_url($promo_img); ?>" alt="" loading="lazy">
            <span class="profile-card__fav" aria-hidden="true">♡</span>
            
          </a>
        </article>

        <?php
          /* 2) остальные карточки */
          while ($q->have_posts()) : $q->the_post();
            get_template_part('template-parts/components/profile-card');
          endwhile;
          wp_reset_postdata();
        ?>

      <?php else : ?>
        <div class="city-empty">Пока нет анкет по выбранному городу.</div>
      <?php endif; ?>

    </div><!-- /.cards-grid -->

  </div><!-- /.container -->
</section>

