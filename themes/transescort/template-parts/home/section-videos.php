<?php
/**
 * Home section: Videos
 * CPT: video
 * Meta:
 * - _video_attachment_id (mp4 attachment id)
 * - _video_duration (e.g. 02:14)
 * - _video_profile_id (profile id)
 * - _video_cover_id (image attachment id)
 */

$q = new WP_Query([
  'post_type'      => 'video',
  'post_status'    => 'publish',
  'posts_per_page' => 8,
  'orderby'        => 'date',
  'order'          => 'DESC',
]);
?>

<section class="home-section home-videos">
  <div class="container">

    <div class="videos-wrap">
      <div class="videos-head">
        <h2 class="videos-title">Последние видео</h2>
        <a class="videos-link" href="<?php echo esc_url(home_url('/videos/')); ?>" aria-label="All videos">→</a>
      </div>

      <div class="videos-row">

        <?php if ($q->have_posts()): ?>
          <?php while ($q->have_posts()): $q->the_post(); ?>
            <?php
              $video_id = get_the_ID();

              // video file
              $att_id = (int) get_post_meta($video_id, '_video_attachment_id', true);
              $url    = $att_id ? wp_get_attachment_url($att_id) : '';

              // cover
              $cover_id  = (int) get_post_meta($video_id, '_video_cover_id', true);
              $cover_url = $cover_id ? wp_get_attachment_image_url($cover_id, 'large') : '';

              // duration
              $dur = (string) get_post_meta($video_id, '_video_duration', true);

              // city from linked profile
              $profile_id = (int) get_post_meta($video_id, '_video_profile_id', true);
              $city = $profile_id ? (string) get_post_meta($profile_id, '_profile_city', true) : '';
            ?>

            <article class="video-card">

              <!-- ✅ VIDEO PREVIEW (Figma 1:1) -->
              <button
                class="video-card__media video-open"
                type="button"
                data-video="<?php echo esc_url($url); ?>"
                aria-label="Play video"
                <?php if ($cover_url): ?>
                  style="background-image:url('<?php echo esc_url($cover_url); ?>')"
                <?php endif; ?>
                <?php echo $url ? '' : 'disabled'; ?>
              >
                <span class="video-card__play" aria-hidden="true">▶</span>

                <?php if ($dur): ?>
                  <span class="video-card__duration">
                    <?php echo esc_html($dur); ?>
                  </span>
                <?php endif; ?>
              </button>

              <div class="video-card__body">
                <div class="video-card__title"><?php the_title(); ?></div>
                <div class="video-card__meta">
                  <?php if ($city): ?>
                    <?php echo esc_html($city); ?> ·
                  <?php endif; ?>
                  <?php echo esc_html(get_the_date('d.m.Y')); ?>
                </div>
              </div>

            </article>

          <?php endwhile; wp_reset_postdata(); ?>

        <?php else: ?>
          <p style="opacity:.75; margin:0;">Пока нет видео.</p>
        <?php endif; ?>

      </div>
    </div>

  </div>
</section>

