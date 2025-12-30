<?php
defined('ABSPATH') || exit;
/**
 * TE FILE: inc/ajax.php
 * AJAX endpoints: login/register, favorites, create_request and related handlers.
 * Loaded via functions.php
 */


add_action('wp_ajax_transescort_login', 'transescort_ajax_login');
add_action('wp_ajax_nopriv_transescort_login', 'transescort_ajax_login');

/**
 * AJAX login handler: validates nonce/credentials, applies brute-force limits, starts session.
 *
 * @function transescort_ajax_login
 */
function transescort_ajax_login() {

    if (
        empty($_POST['login_nonce']) ||
        !wp_verify_nonce($_POST['login_nonce'], 'transescort_login')
    ) {
        wp_send_json_error(['message' => 'Security error.'], 403);
        return;
    }

    $login = '';
    if (!empty($_POST['user_login'])) {
        $login = sanitize_text_field(wp_unslash($_POST['user_login']));
    } elseif (!empty($_POST['user_email'])) {
        $login = sanitize_text_field(wp_unslash($_POST['user_email']));
    }

    $password = !empty($_POST['user_password']) ? (string) wp_unslash($_POST['user_password']) : '';
    $remember = !empty($_POST['remember_me']) && (string) $_POST['remember_me'] === '1';

    if ($login === '' || $password === '') {
        wp_send_json_error(['message' => 'Login and password required.'], 400);
        return;
    }

    if (is_email($login)) {
        $u = get_user_by('email', $login);
        if ($u) {
            $login = $u->user_login;
        } else {
            wp_send_json_error(['message' => $user->get_error_message()], 401);
            return;
        }
    }

    // Brute-force + Remember me
    $attempt_key = transescort_check_login_attempts($login);
    $remember = ! empty($_POST["remember"]);

    $user = wp_signon([
        'user_login'    => $login,
        'user_password' => $password,
        'remember' => $remember,

        'remember'      => $remember,
    ], is_ssl());

    if (is_wp_error($user)) {
        transescort_register_failed_attempt($attempt_key);

        wp_send_json_error(['message' => $user->get_error_message()], 401);
        return;
    }

    if (user_can($user, 'manage_options')) {
    // Success: clear attempts + notify admin
    transescort_clear_failed_attempts();
    $u = wp_get_current_user();
    if ($u && $u->ID) {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "";
        wp_mail(get_option("admin_email"), "Новый вход на сайт", "Пользователь: {$u->user_login}
Email: {$u->user_email}
IP: {$ip}");
    }

        wp_send_json_success(['redirect' => admin_url()]);
        return;
    }

    // Success: clear attempts + notify admin
    transescort_clear_failed_attempts();
    $u = wp_get_current_user();
    if ($u && $u->ID) {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "";
        wp_mail(get_option("admin_email"), "Новый вход на сайт", "Пользователь: {$u->user_login}
Email: {$u->user_email}
IP: {$ip}");
    }

    wp_send_json_success(['redirect' => home_url('/account/')]);
    return;
}

add_action('wp_ajax_nopriv_transescort_register', function () {

    if (!isset($_POST['register_nonce']) || !wp_verify_nonce($_POST['register_nonce'], 'transescort_register')) {
        wp_send_json_error(['message' => 'Security error.']);
    }

    $email = sanitize_email($_POST['user_email'] ?? '');
    $pass1 = $_POST['user_password'] ?? '';
    $pass2 = $_POST['user_password_repeat'] ?? '';
    $terms = isset($_POST['terms']);

    if (!$email || !$pass1 || !$pass2) {
        wp_send_json_error(['message' => 'All fields are required.']);
    }

    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Invalid email.']);
    }

    if ($pass1 !== $pass2) {
        wp_send_json_error(['message' => 'Passwords do not match.']);
    }

    if (strlen($pass1) < 6) {
        wp_send_json_error(['message' => 'Password too short (min 6).']);
    }

    if (!$terms) {
        wp_send_json_error(['message' => 'You must confirm 18+ and Terms.']);
    }

    if (email_exists($email)) {
        wp_send_json_error(['message' => 'User already exists.']);
    }

    $user_id = wp_create_user($email, $pass1, $email);
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => 'Registration failed.']);
    }

    // Set role: customer
    $user = new WP_User($user_id);
    $user->set_role('customer');

    // Send confirmation email (soft)
    $confirm_link = add_query_arg([
        'confirm_email' => $user_id,
        'key'           => md5($email),
    ], home_url('/login/'));

    wp_mail(
        $email,
        'Confirm your email',
        "Welcome!\n\nPlease confirm your email:\n$confirm_link"
    );

    // Auto login
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    // Success: clear attempts + notify admin
    transescort_clear_failed_attempts();
    $u = wp_get_current_user();
    if ($u && $u->ID) {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "";
        wp_mail(get_option("admin_email"), "Новый вход на сайт", "Пользователь: {$u->user_login}
Email: {$u->user_email}
IP: {$ip}");
    }

    wp_send_json_success(['redirect' => home_url('/personal-profile/')]);
});


add_action('template_redirect', function () {
    if (is_page('login') && is_user_logged_in()) {
        wp_redirect(home_url('/account/'));
        exit;
    }
});


add_filter('login_redirect', function ($redirect_to, $requested, $user) {
    if (isset($user->roles) && in_array('administrator', $user->roles, true)) {
        return admin_url();
    }
    return home_url('/account/');
}, 10, 3);


add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (!is_user_logged_in()) return;
    if (!current_user_can('administrator')) return;

    $node = $wp_admin_bar->get_node('wp-logo');
    if ($node) {
        $node->href = admin_url();
        $wp_admin_bar->add_node($node);
    }
}, 999);


add_action('admin_init', function () {

    // ignore AJAX
    if (defined('DOING_AJAX') && DOING_AJAX) return;

    // guests -> custom login
    if (!is_user_logged_in()) {
        wp_redirect(home_url('/login/'));
        exit;
    }

    // admin always allowed
    if (current_user_can('administrator')) {
        return;
    }

    // non-admin: allow ONLY edit their own profile
    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    $action  = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

    if ($post_id > 0 && $action === 'edit' && get_post_type($post_id) === 'profile') {
        if (transescort_user_owns_profile(get_current_user_id(), $post_id)) {
            return; // ok
        }
    }

    // everything else blocked
    wp_redirect(home_url('/account/'));
    exit;
});





add_action('wp_ajax_nopriv_transescort_create_request', 'transescort_create_request');
add_action('wp_ajax_transescort_create_request', 'transescort_create_request');

/**
 * AJAX create request: validates nonce, creates Request CPT, stores contact/date/message meta.
 *
 * @function transescort_create_request
 */
function transescort_create_request() {

    $nonce = $_POST['nonce'] ?? ($_POST['_wpnonce'] ?? '');
    if (!$nonce || !wp_verify_nonce($nonce, 'transescort_request')) {
        wp_send_json_error(['message' => 'Security error']);
    }

    $profile_id = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;
    $name       = sanitize_text_field($_POST['name'] ?? '');
    $contact    = sanitize_text_field($_POST['contact'] ?? '');
    $datetime   = sanitize_text_field($_POST['datetime'] ?? '');
    $message    = sanitize_textarea_field($_POST['message'] ?? '');

    if (!$profile_id || !$name || !$contact) {
        wp_send_json_error(['message' => 'Required fields missing']);
    }

    $request_id = wp_insert_post([
        'post_type'   => 'request',
        'post_status' => 'publish',
        'post_title'  => 'Request for profile #' . $profile_id,
    ]);

    if (is_wp_error($request_id)) {
        wp_send_json_error(['message' => 'Failed to create request']);
    }

    update_post_meta($request_id, '_request_profile_id', $profile_id);
    update_post_meta($request_id, '_request_user_id', get_current_user_id());
    update_post_meta($request_id, '_request_name', $name);
    update_post_meta($request_id, '_request_contact', $contact);
    update_post_meta($request_id, '_request_datetime', $datetime);
    update_post_meta($request_id, '_request_message', $message);
    update_post_meta($request_id, '_request_status', 'new');

    // Success: clear attempts + notify admin
    transescort_clear_failed_attempts();
    $u = wp_get_current_user();
    if ($u && $u->ID) {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "";
        wp_mail(get_option("admin_email"), "Новый вход на сайт", "Пользователь: {$u->user_login}
Email: {$u->user_email}
IP: {$ip}");
    }

    wp_send_json_success(['message' => 'Request sent']);
}
/**
 * Favorites (AJAX)
 * user_meta: _favorite_profile_ids (array of profile IDs)
 */
add_action("wp_ajax_te_toggle_favorite_profile", "te_toggle_favorite_profile");
add_action("wp_ajax_nopriv_te_toggle_favorite_profile", "te_toggle_favorite_profile");
/**
 * AJAX favorites toggle: add/remove profile from user favorites list.
 *
 * @function te_toggle_favorite_profile
 */
function te_toggle_favorite_profile(){
  if (!is_user_logged_in()) {
    wp_send_json_error(["message"=>"auth_required"], 401);
  }

  $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
  if (!$nonce || !wp_verify_nonce($nonce, "te_fav_profile")) {
    wp_send_json_error(["message"=>"bad_nonce"], 403);
  }

  $profile_id = isset($_POST["profile_id"]) ? absint($_POST["profile_id"]) : 0;
  if (!$profile_id) {
    wp_send_json_error(["message"=>"bad_profile_id"], 400);
  }

  $uid = get_current_user_id();
  $ids = get_user_meta($uid, "_favorite_profile_ids", true);
  if (!is_array($ids)) $ids = [];
  $ids = array_values(array_unique(array_map("absint", $ids)));

  $active = in_array($profile_id, $ids, true);
  if ($active) {
    $ids = array_values(array_diff($ids, [$profile_id]));
    $active = false;
  } else {
    $ids[] = $profile_id;
    $active = true;
  }

  update_user_meta($uid, "_favorite_profile_ids", $ids);
    // Success: clear attempts + notify admin
    transescort_clear_failed_attempts();
    $u = wp_get_current_user();
    if ($u && $u->ID) {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "";
        wp_mail(get_option("admin_email"), "Новый вход на сайт", "Пользователь: {$u->user_login}
Email: {$u->user_email}
IP: {$ip}");
    }

  wp_send_json_success(["active"=>$active, "count"=>count($ids)]);
}
