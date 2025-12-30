<?php
defined('ABSPATH') || exit;
/**
 * TE FILE: inc/enqueue.php
 * Enqueue theme styles/scripts and localize JS variables.
 * Loaded via functions.php
 */


add_action('wp_enqueue_scripts', function () {

  // Fonts (Figma)
  wp_enqueue_style(
    'transescort-fonts',
    'https://fonts.googleapis.com/css2?family=Bagel+Fat+One&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
    [],
    null
  );

  // Main styles
  wp_enqueue_style(
    'transescort-main',
    get_template_directory_uri() . '/assets/css/main.css',
    [],
    '1.0'
  );

  // Main scripts
  wp_enqueue_script(
    'transescort-main',
    get_template_directory_uri() . '/assets/js/main.js',
    [],
    '1.0',
    true
  );



    

    wp_localize_script('transescort-main', 'TRANS', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('transescort_request'),
    ]);

// AUTH (Login / Register) - only on login page
if (is_page_template('page-login.php') || is_page('login')) {
    wp_enqueue_script(
              'transescort-auth',
              get_template_directory_uri() . '/assets/js/auth.js',
              [],
              time(), // no cache
              true
          );

    wp_localize_script('transescort-auth', 'transescortAuth', [
              'ajaxurl' => admin_url('admin-ajax.php'),
              'accountUrl' => home_url('/account/'),
          ]);
}

});
