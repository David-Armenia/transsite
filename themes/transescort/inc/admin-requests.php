<?php
defined('ABSPATH') || exit;
/**
 * TE FILE: inc/admin-requests.php
 * Admin list tweaks for Requests (title/actions).
 * Loaded via functions.php
 */


/**
 * Admin: Requests list — show Profile name instead of ID in "Title" column
 */
add_filter('the_title', function ($title, $post_id) {
    if (is_admin() && get_post_type($post_id) === 'request') {
        $profile_id = (int) get_post_meta($post_id, '_request_profile_id', true);
        if ($profile_id > 0) {
            $profile_title = get_the_title($profile_id);
            if (!$profile_title) $profile_title = 'Profile #' . $profile_id;

            // Title in list
            return 'Request for ' . $profile_title;
        }
    }
    return $title;
}, 10, 2);

/**
 * Admin: Requests list — make the title link go to Profile edit (optional)
 * If you don't want this behavior, delete this block.
 */
add_filter('post_row_actions', function ($actions, $post) {
    if (is_admin() && $post && $post->post_type === 'request') {
        $profile_id = (int) get_post_meta($post->ID, '_request_profile_id', true);
        if ($profile_id > 0) {
            $actions['edit_profile'] = '<a href="' . esc_url(get_edit_post_link($profile_id)) . '">Edit Profile</a>';
        }
    }
    return $actions;
}, 10, 2);
