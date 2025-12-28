<?php
$limit = 12;

// 1) Сначала берём проверенные профили
$q = new WP_Query([
  'post_type'      => 'profile',
  'post_status'    => 'publish',
  'posts_per_page' => $limit,
  'orderby'        => 'rand',
  'meta_query'     => [
    [
      'key'     => '_profile_verified',
      'value'   => '1',
      'compare' => '='
    ]
  ]
]);

$ids = wp_list_pluck($q->posts, 'ID');
$need = $limit - count($ids);

// 2) Если мало — добираем обычные
if ($need > 0) {
  $q2 = new WP_Query([
    'post_type'      => 'profile',
    'post_status'    => 'publish',
    'posts_per_page' => $need,
    'orderby'        => 'rand',
    'post__not_in'   => $ids,
  ]);
  $ids = array_merge($ids, wp_list_pluck($q2->posts, 'ID'));
}

if (empty($ids)) return;
?>

<section class="home-section home-recommend">
  <div class="container">

    <div class="recommend-wrap">
      <div class="recommend-head">
        <h2 class="recommend-title">Рекомендации</h2>
        <a class="recommend-link" href="<?php echo esc_url(get_post_type_archive_link('profile')); ?>">→</a>
      </div>

      <div class="recommend-row">
        <?php foreach ($ids as $pid): ?>
          <?php
            $img = '';
            if (has_post_thumbnail($pid)) {
              $img = get_the_post_thumbnail_url($pid, 'medium_large');
            } else {
              $raw = get_post_meta($pid, '_profile_gallery_ids', true);
              $ids2 = is_string($raw) ? maybe_unserialize($raw) : $raw;
              if (is_array($ids2) && !empty($ids2)) {
                $img = wp_get_attachment_image_url($ids2[0], 'medium_large');
              }
            }
            if (!$img) continue;
          ?>
          <a class="profile-card" href="<?php echo get_permalink($pid); ?>">
            <img src="<?php echo esc_url($img); ?>" alt="">
            <div class="profile-card__info">
              <div class="profile-card__name"><?php echo esc_html(get_the_title($pid)); ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>
