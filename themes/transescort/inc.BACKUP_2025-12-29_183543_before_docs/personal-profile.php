<?php
defined('ABSPATH') || exit;

// Handle profile photo upload (front-end)
add_action('template_redirect', function () {

    if (!is_page('personal-profile')) return;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (!is_user_logged_in()) return;

    // run ONLY for upload submit
    if (empty($_POST['action']) || $_POST['action'] !== 'transescort_upload_photos') return;

    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'transescort_profile_upload')) {
        wp_die('Security error');
    }

    $user_id    = get_current_user_id();
    $profile_id = (int) get_user_meta($user_id, '_linked_profile_id', true);

    if (!$profile_id) wp_die('No linked profile');

    $owner_id = (int) get_post_meta($profile_id, '_profile_user_id', true);
    if ($owner_id !== $user_id && !current_user_can('administrator')) {
        wp_die('No permission');
    }

    if (empty($_FILES['photos']) || empty($_FILES['photos']['name'])) {
        wp_redirect(add_query_arg('upload', 'no_files', get_permalink()));
        exit;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $gallery = get_post_meta($profile_id, '_profile_gallery_ids', true);
    if (!is_array($gallery)) $gallery = [];

    foreach ($_FILES['photos']['name'] as $i => $name) {

        if (empty($_FILES['photos']['name'][$i])) continue;
        if (($_FILES['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

        $file = [
            'name'     => $_FILES['photos']['name'][$i],
            'type'     => $_FILES['photos']['type'][$i],
            'tmp_name' => $_FILES['photos']['tmp_name'][$i],
            'error'    => $_FILES['photos']['error'][$i],
            'size'     => $_FILES['photos']['size'][$i],
        ];

        $upload = wp_handle_upload($file, ['test_form' => false]);
        if (!empty($upload['error'])) continue;

        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name($file['name']),
            'post_status'    => 'inherit'
        ], $upload['file'], $profile_id);

        if (!$attachment_id || is_wp_error($attachment_id)) continue;

        $meta = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $meta);

        $gallery[] = (int) $attachment_id;
    }

    update_post_meta($profile_id, '_profile_gallery_ids', array_values(array_unique($gallery)));

    wp_redirect(add_query_arg('upload', 'ok', get_permalink()));
    exit;
});


// Handle profile photo delete (front-end)
add_action('template_redirect', function () {

    if (!is_page('personal-profile')) return;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (!is_user_logged_in()) return;

    if (empty($_POST['action']) || $_POST['action'] !== 'delete_profile_photo') return;

    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'delete_profile_photo')) {
        wp_die('Security error');
    }

    $user_id    = get_current_user_id();
    $profile_id = (int) get_user_meta($user_id, '_linked_profile_id', true);
    if (!$profile_id) wp_die('No profile');

    $owner_id = (int) get_post_meta($profile_id, '_profile_user_id', true);
    if ($owner_id !== $user_id && !current_user_can('administrator')) {
        wp_die('No permission');
    }

    $att_id = isset($_POST['att_id']) ? (int) $_POST['att_id'] : 0;
    if ($att_id <= 0) wp_die('Invalid image');

    $gallery = get_post_meta($profile_id, '_profile_gallery_ids', true);
    if (!is_array($gallery) || !in_array($att_id, $gallery, true)) {
        wp_die('Invalid image');
    }

    // remove from gallery
    $gallery = array_values(array_diff(array_map('intval', $gallery), [$att_id]));
    update_post_meta($profile_id, '_profile_gallery_ids', $gallery);

    // delete attachment
    wp_delete_attachment($att_id, true);

    wp_safe_redirect(add_query_arg('deleted', '1', get_permalink()));
    exit;
});



/**
 * Personal Profile: save city (user selects from admin list)
 */
add_action('template_redirect', function () {
    if (!is_page('personal-profile')) return;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (!is_user_logged_in()) return;

    if (empty($_POST['action']) || $_POST['action'] !== 'transescort_save_profile_city') return;

    if (empty($_POST['transescort_save_profile_city_nonce']) ||
        !wp_verify_nonce($_POST['transescort_save_profile_city_nonce'], 'transescort_save_profile_city')) {
        wp_die('Security error');
    }

    $user_id    = get_current_user_id();
    $profile_id = (int) transescort_get_linked_profile_id($user_id);
    if (!$profile_id) wp_die('No linked profile');

    // owner/admin only
    if (!current_user_can('administrator') && !transescort_user_owns_profile($user_id, $profile_id)) {
        wp_die('No permission');
    }

    $city   = sanitize_text_field($_POST['profile_city'] ?? '');
    $cities = transescort_city_list();

    // allow only from list (no "!" to avoid shell history issues)
    if (array_key_exists($city, $cities) === false) {
        $city = '';
    }

    update_post_meta($profile_id, '_profile_city', $city);

    wp_redirect(add_query_arg('saved', 'city', get_permalink()));
    exit;
});

/* === Profile image helper (featured -> gallery -> fallback) === */
if (!function_exists('transescort_get_profile_image_url')) {
  function transescort_get_profile_image_url($post_id, $size = 'medium_large', $fallback = '' ) {
    if (!$fallback) {
      $fallback = get_template_directory_uri() . '/assets/img/hero-preview.jpg';
    }
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
}


// Create profile on demand from /personal-profile/?create=1
add_action('template_redirect', function () {
    if (!is_page('personal-profile')) return;
    if (!is_user_logged_in()) return;
    if (!isset($_GET['create']) || $_GET['create'] !== '1') return;

    $user_id = get_current_user_id();

    // already linked -> just go back
    $existing = (int) get_user_meta($user_id, '_linked_profile_id', true);
    if ($existing > 0) {
        wp_safe_redirect(remove_query_arg('create', add_query_arg('created', '0', get_permalink())));
        exit;
    }

    $user = get_userdata($user_id);
    $title = ($user && $user->display_name) ? $user->display_name : ($user ? $user->user_login : 'My Profile');

    $profile_id = wp_insert_post([
        'post_type'   => 'profile',
        'post_status' => 'publish',
        'post_title'  => $title,
    ]);

    if (is_wp_error($profile_id) || !$profile_id) {
        wp_die('Failed to create profile');
    }

    update_post_meta($profile_id, '_profile_user_id', (int)$user_id);
    update_user_meta($user_id, '_linked_profile_id', (int)$profile_id);

    wp_safe_redirect(remove_query_arg('create', add_query_arg('created', '1', get_permalink())));
    exit;
});
