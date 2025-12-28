<?php
$preview_q = new WP_Query([
  'post_type'      => 'profile',
  'post_status'    => 'publish',
  'posts_per_page' => 1,
  'orderby'        => 'date',
  'order'          => 'DESC',
  'meta_query'     => [
    [
      'key'     => '_profile_verified',
      'value'   => '1',
      'compare' => '=',
    ],
  ],

]);
$preview_post_id = ($preview_q->have_posts()) ? $preview_q->posts[0]->ID : 0;

$bg_q = new WP_Query([
  'post_type'      => 'profile',
  'post_status'    => 'publish',
  'posts_per_page' => 1,
  'orderby'        => 'rand',
  'meta_query'     => [
    [
      'key'     => '_profile_verified',
      'value'   => '1',
      'compare' => '=',
    ],
  ],

]);
$bg_post_id = ($bg_q->have_posts()) ? $bg_q->posts[0]->ID : 0;

$fallback_preview = get_template_directory_uri() . '/assets/img/hero-preview.jpg';
$fallback_bg      = get_template_directory_uri() . '/assets/img/hero-bg.jpg';

function transescort_thumb_url_or_fallback($post_id, $size, $fallback) {




  if ($post_id && has_post_thumbnail($post_id)) {


    $src = get_the_post_thumbnail_url($post_id, $size);


    if ($src) return $src;


  }


  return $fallback;


}




function transescort_profile_image_url($post_id, $size, $fallback) {
  if ($post_id && has_post_thumbnail($post_id)) {
    $src = get_the_post_thumbnail_url($post_id, $size);
    if ($src) return $src;
  }
  $raw = $post_id ? get_post_meta($post_id, '_profile_gallery_ids', true) : null;
  $ids = is_string($raw) ? maybe_unserialize($raw) : $raw;
  if (is_array($ids) && !empty($ids)) {
    $first_id = intval($ids[0]);
    if ($first_id) {
      $src2 = wp_get_attachment_image_url($first_id, $size);
      if ($src2) return $src2;
    }
  }
  return $fallback;
}

$preview_img  = transescort_profile_image_url($preview_post_id, 'large', $fallback_preview);
$bg_img       = transescort_profile_image_url($bg_post_id, 'full',  $fallback_bg);
$preview_link = $preview_post_id ? get_permalink($preview_post_id) : '#';
?>

<section class="hero">
  <div class="container hero-inner">

    <!-- LEFT -->
    <div class="hero-left">

      <div class="hero-tariff-card">
        <h3 class="hero-tariff-title">Тарифы</h3>
        <p class="hero-tariff-text">
          Не упусти возможность изучить наши специальные предложения по тарифам,
          чтобы найти больше возможностей и преимуществ.
        </p>

        <a href="#" class="btn btn-primary hero-tariff-btn">
          Посмотреть
        </a>
      </div>

      <a href="<?php echo esc_url($preview_link); ?>" class="hero-preview">
        <img
          src="<?php echo esc_url($preview_img); ?>"
          alt=""
          loading="lazy"
        />
        <span class="hero-preview-btn">Перейти к анкете →</span>
      </a>

    </div>

    <!-- RIGHT -->
    <div class="hero-right">

      <div class="hero-bg">
        <img
          src="<?php echo esc_url($bg_img); ?>"
          alt=""
          loading="lazy"
        />
      </div>

      <div class="hero-content">
        <h1 class="hero-title hero-brand">
          Trans<br>Escort
        </h1>

        <div class="hero-stats">
          <div class="hero-stat">
            <strong>500+</strong>
            <span>человек<br>пользуются нашими услугами</span>
          </div>
          <div class="hero-stat">
            <strong>150+</strong>
            <span>Анкет<br>прошедших проверку</span>
          </div>
        </div>
      </div>

    </div>

  </div>
</section>

