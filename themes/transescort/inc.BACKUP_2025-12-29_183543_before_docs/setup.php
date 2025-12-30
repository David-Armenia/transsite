<?php
defined('ABSPATH') || exit;

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    // Create custom role: customer
    if (!get_role('customer')) {
        add_role('customer', 'Customer', [
            'read' => true,
	    'upload_files' => true,
        ]);
    }
  // Ensure capability exists even if role was created earlier
    $role = get_role('customer');
    if ($role && !$role->has_cap('upload_files')) {
        $role->add_cap('upload_files');
    }

}, 20);
add_action('init', function () {
    if (is_user_logged_in() && !current_user_can('administrator')) {
        show_admin_bar(false);
    }
});
