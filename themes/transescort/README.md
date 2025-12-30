# TransEscort Theme

Custom WordPress theme with Profile system, Personal Profile frontend page, Requests, Favorites, and AJAX auth flows.

## Structure

- `functions.php` — loads all modules from `/inc` (see “THEME INC MAP” block).
- `inc/security.php` — brute-force helpers via transients (legacy/simple).
- `inc/auth-enhancements.php` — improved auth/brute-force + optional AJAX login override.
- `inc/enqueue.php` — enqueue CSS/JS + wp_localize_script.
- `inc/ajax.php` — AJAX handlers (login, favorites, create request).
- `inc/setup.php` — theme supports, roles, admin tweaks.
- `inc/cpt-profile.php` — Profile CPT.
- `inc/cpt-request.php` — Request CPT.
- `inc/helpers.php` — shared helpers.
- `inc/personal-profile.php` — frontend profile logic.
- `inc/admin-profile.php` — admin profile UI.
- `inc/admin-requests.php` — admin requests UI.

## Key flows

### Personal Profile
- user_meta: `_linked_profile_id`
- post_meta: `_profile_user_id`, `_profile_gallery_ids`
- `/personal-profile/` handles:
  - photo upload
  - delete photo
  - city save
  - profile create
  - main photo selection

### Requests
- CPT: `request`
- Status: `new | accepted | rejected`
- AJAX endpoint: `transescort_create_request`

### Dev checks

```bash
php -l functions.php
for f in inc/*.php; do php -l "$f" || exit 1; done

## Functions map (auto)

### `admin-profile.php`
- `te_profile_gallery_metabox()`
- `transescort_cities_settings_page()`
- `transescort_profile_meta_box()`
- `transescort_profile_user_link_metabox()`
- `te_video_cover_metabox()`
- `transescort_set_main_photo_handler()`

### `ajax.php`
- `transescort_ajax_login()`
- `transescort_create_request()`
- `te_toggle_favorite_profile()`

### `auth-enhancements.php`
- `transescort_bf_key()`
- `transescort_check_login_attempts()`
- `transescort_register_failed_attempt()`
- `transescort_clear_failed_attempts()`
- `transescort_ajax_login_enhanced()`

### `cpt-request.php`
- `transescort_request_statuses()`
- `transescort_get_request_status()`
- `transescort_set_request_status()`
- `transescort_request_details_metabox()`
- `transescort_get_profile_requests()`
- `transescort_request_status_label()`

### `helpers.php`
- `transescort_get_linked_profile_id()`
- `transescort_get_profile_owner_user_id()`
- `transescort_user_owns_profile()`

### `personal-profile.php`
- `transescort_get_profile_image_url()`

### `security.php`
- `transescort_check_login_attempts()`
- `transescort_register_failed_attempt()`
- `transescort_clear_failed_attempts()`
