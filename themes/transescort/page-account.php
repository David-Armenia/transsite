<?php
/*
Template Name: Account
*/

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

get_header();

$current_user = wp_get_current_user();

// linked profile id
$linked_profile_id = 0;
if (function_exists('transescort_get_linked_profile_id')) {
    $linked_profile_id = (int) transescort_get_linked_profile_id(get_current_user_id());
} else {
    $linked_profile_id = (int) get_user_meta(get_current_user_id(), '_linked_profile_id', true);
}
?>

<main class="account-page">
  <div class="container">

    <h1>My account</h1>

    <div class="account-box">
      <p><strong>Email:</strong> <?php echo esc_html($current_user->user_email); ?></p>
      <p><strong>Role:</strong> <?php echo esc_html(implode(', ', $current_user->roles)); ?></p>

      <?php
      // Buttons block (Open / Edit)
      if ($linked_profile_id && get_post_status($linked_profile_id)) :
          $profile_url = get_permalink($linked_profile_id);
          $edit_url    = get_edit_post_link($linked_profile_id, ''); // wp-admin edit link
      ?>
        <div class="account-actions" style="margin-top:16px;">
          <a class="btn btn-primary" href="<?php echo esc_url($profile_url); ?>">
            Open my profile
          </a>

          <a class="btn btn-primary" href="<?php echo esc_url(home_url('/personal-profile/')); ?>">
            Personal profile
          </a>

          <?php if ($edit_url): ?>
            <a class="btn btn-outline" href="<?php echo esc_url($edit_url); ?>" style="margin-left:10px;">
              Edit my profile
            </a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <p style="margin-top:16px; opacity:.8;">
          Your account has no linked profile yet.
        </p>

        <div class="account-actions" style="margin-top:16px;">
          <a class="btn btn-primary" href="<?php echo esc_url(home_url('/personal-profile/')); ?>">
            Personal profile
          </a>
        </div>
      <?php endif; ?>

      <?php
      // ============================================================
      // My Requests section
      // ============================================================
      $linked_profile_id_requests = (int) transescort_get_linked_profile_id(get_current_user_id());
      $requests = $linked_profile_id_requests ? transescort_get_profile_requests($linked_profile_id_requests, 10) : [];
      ?>

      <section class="account-requests" style="margin-top:32px;">
        <h2 class="account-section-title">My Requests</h2>

        <?php if (!$linked_profile_id_requests): ?>
          <p style="opacity:.8;">No linked profile. Requests are unavailable.</p>

        <?php elseif (empty($requests)): ?>
          <p style="opacity:.8;">No requests yet.</p>

        <?php else: ?>
          <div class="requests-list">
            <?php foreach ($requests as $r): ?>
              <?php
                $status   = transescort_get_request_status($r->ID);
                $name     = get_post_meta($r->ID, '_request_name', true);
                $contact  = get_post_meta($r->ID, '_request_contact', true);
                $dt       = get_post_meta($r->ID, '_request_datetime', true);
                $message  = get_post_meta($r->ID, '_request_message', true);

                $status_label = transescort_request_status_label($status);
              ?>

              <article class="request-card" style="padding:14px; border:1px solid rgba(255,255,255,.12); border-radius:12px; margin-bottom:12px;">
                <div class="request-card-top" style="display:flex; justify-content:space-between; gap:12px; align-items:center;">
                  <div class="request-person" style="font-weight:600;">
                    <?php echo esc_html($name ?: '—'); ?>
                    <span style="opacity:.75; font-weight:400;">— <?php echo esc_html($contact ?: ''); ?></span>
                  </div>

                  <div class="request-status status-<?php echo esc_attr($status); ?>" style="font-size:13px; opacity:.9;">
                    <?php echo esc_html($status_label); ?>
                  </div>
                </div>

                <?php if ($dt): ?>
                  <div class="request-datetime" style="margin-top:6px; opacity:.75; font-size:13px;">
                    <?php echo esc_html($dt); ?>
                  </div>
                <?php endif; ?>

                <?php if ($message): ?>
                  <div class="request-message" style="margin-top:10px; opacity:.9;">
                    <?php echo nl2br(esc_html($message)); ?>
                  </div>
                <?php endif; ?>

                <div class="request-meta" style="margin-top:10px; opacity:.7; font-size:12px;">
                  Created: <?php echo esc_html(get_the_date('Y-m-d H:i', $r)); ?>
                </div>
              </article>

            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <div style="margin-top:18px;">
        <a class="btn btn-outline" href="<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>">
          Logout
        </a>
      </div>
    </div>

  </div>
</main>

<?php get_footer(); ?>

