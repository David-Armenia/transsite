<?php
defined('ABSPATH') || exit;

function transescort_get_linked_profile_id($user_id = 0) {
    $user_id = $user_id ? (int) $user_id : get_current_user_id();
    return (int) get_user_meta($user_id, '_linked_profile_id', true);
}

function transescort_get_profile_owner_user_id($profile_id) {
    return (int) get_post_meta((int) $profile_id, '_profile_user_id', true);
}

if (!function_exists('transescort_user_owns_profile')) {
    function transescort_user_owns_profile($user_id, $profile_id) {
        $user_id    = (int) $user_id;
        $profile_id = (int) $profile_id;

        if ($user_id <= 0 || $profile_id <= 0) return false;

        $owner_user_id = (int) get_post_meta($profile_id, '_profile_user_id', true);
        if ($owner_user_id > 0 && $owner_user_id === $user_id) return true;

        $linked_profile_id = (int) get_user_meta($user_id, '_linked_profile_id', true);
        if ($linked_profile_id > 0 && $linked_profile_id === $profile_id) return true;

        return false;
    }
}
