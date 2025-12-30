<?php
if (!defined('ABSPATH')) exit;
/**
 * TE FILE: inc/auth-enhancements.php
 * Authentication enhancements: overrides/extends AJAX login handler.
 * Loaded via functions.php
 */


/**
 * Brute-force protection (IP + login), 5 tries / 10 minutes
 */
/**
 * Builds brute-force transient key based on IP + login.
 *
 * @function transescort_bf_key
 */
function transescort_bf_key($login) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return 'transescort_login_attempts_' . md5($ip . '|' . strtolower((string)$login));
}
/**
 * Checks brute-force attempts limit and returns transient key (or blocks with JSON error).
 *
 * @function transescort_check_login_attempts
 */
function transescort_check_login_attempts($login) {
    $key  = transescort_bf_key($login);
    $data = get_transient($key);
    if (!is_array($data)) $data = ['count' => 0, 'time' => time()];

    if ((int)$data['count'] >= 5 && (time() - (int)$data['time']) < 600) {
        wp_send_json_error(['message' => 'Слишком много попыток. Попробуйте через 10 минут.'], 429);
        exit;
    }
    return $key;
}
/**
 * Increments failed login attempt counter in transient.
 *
 * @function transescort_register_failed_attempt
 */
function transescort_register_failed_attempt($key) {
    $data = get_transient($key);
    if (!is_array($data)) $data = ['count' => 0, 'time' => time()];
    $data['count'] = (int)$data['count'] + 1;
    $data['time']  = time();
    set_transient($key, $data, 600);
}
/**
 * Clears/reset brute-force attempts transient.
 *
 * @function transescort_clear_failed_attempts
 */
function transescort_clear_failed_attempts($key) {
    delete_transient($key);
}

/**
 * Hook into our AJAX login to add:
 * - brute-force check
 * - remember me mapping
 * - admin notify on success
 */
add_action('init', function () {

    if (!function_exists('transescort_ajax_login')) return;

    // Wrap original handler: remove and re-add with a proxy
    remove_action('wp_ajax_transescort_login', 'transescort_ajax_login');
    remove_action('wp_ajax_nopriv_transescort_login', 'transescort_ajax_login');

    add_action('wp_ajax_transescort_login', 'transescort_ajax_login_enhanced');
    add_action('wp_ajax_nopriv_transescort_login', 'transescort_ajax_login_enhanced');

    /**
 * Describe what this function does.
 *
 * @function transescort_ajax_login_enhanced
 */
    function transescort_ajax_login_enhanced() {

        // Determine login early (same as your handler does)
        $login = '';
        if (!empty($_POST['user_login'])) $login = sanitize_text_field(wp_unslash($_POST['user_login']));
        elseif (!empty($_POST['user_email'])) $login = sanitize_text_field(wp_unslash($_POST['user_email']));

        // brute-force check
        $attempt_key = transescort_check_login_attempts($login);

        // map remember checkbox (support both names)
        if (!isset($_POST['remember'])) {
            $_POST['remember'] = (!empty($_POST['remember_me']) && (string)$_POST['remember_me'] === '1') ? '1' : '';
        }

        // run original login
        ob_start();
        transescort_ajax_login(); // this will wp_send_json_* and exit normally
        ob_end_clean();

        // if original returned error, count attempt
        // (we detect it by checking if user is logged in now)
        if (!is_user_logged_in()) {
            transescort_register_failed_attempt($attempt_key);
            return;
        }

        // success: clear + notify admin
        transescort_clear_failed_attempts($attempt_key);

        $u  = wp_get_current_user();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        @wp_mail(get_option('admin_email'), 'Login on site', "User: {$u->user_login}\nEmail: {$u->user_email}\nIP: {$ip}");
        error_log("[TRANS escort login] user={$u->user_login} email={$u->user_email} ip={$ip}");
    }
});
