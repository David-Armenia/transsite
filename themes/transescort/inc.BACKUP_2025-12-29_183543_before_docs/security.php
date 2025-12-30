<?php
defined('ABSPATH') || exit;

/**
 * Brute-force protection (IP based)
 */
function transescort_check_login_attempts($email = "") {
    $ip = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
    $key = "transescort_login_attempts_" . md5($ip);
    $data = get_transient($key);
    if (!is_array($data)) {
        $data = ["count" => 0, "time" => time()];
    }
    // 5 tries / 10 minutes
    if ((int)($data["count"] ?? 0) >= 5 && (time() - (int)($data["time"] ?? time())) < 600) {
        wp_send_json_error(["message" => "Слишком много попыток. Попробуйте через 10 минут."]);
    }
    return $key;
}

function transescort_register_failed_attempt($key) {
    $data = get_transient($key);
    if (!is_array($data)) {
        $data = ["count" => 0, "time" => time()];
    }
    $data["count"] = (int)($data["count"] ?? 0) + 1;
    $data["time"] = time();
    set_transient($key, $data, 600);
}

function transescort_clear_failed_attempts() {
    $ip = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
    $key = "transescort_login_attempts_" . md5($ip);
    delete_transient($key);
}
