<?php
defined('ABSPATH') || exit;

add_action('init', function () {

    register_post_type('request', [
        'labels' => [
            'name' => 'Requests',
            'singular_name' => 'Request',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_admin_bar' => true,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'query_var' => false,
        'rewrite' => false,
        'menu_icon' => 'dashicons-email-alt',
        'supports' => ['title'],
    ]);
});


function transescort_request_statuses() {
    return [
        'new'      => 'New',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
    ];
}

function transescort_get_request_status($request_id) {
    $st = get_post_meta((int)$request_id, '_request_status', true);
    return $st ? $st : 'new';
}

function transescort_set_request_status($request_id, $status) {
    $statuses = transescort_request_statuses();
    if (!isset($statuses[$status])) $status = 'new';
    update_post_meta((int)$request_id, '_request_status', $status);
}

// Default status on create (safety)
add_action('save_post_request', function($post_id, $post, $update){
    if (wp_is_post_revision() || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) return;
    if ($update) return;

    if (!get_post_meta($post_id, '_request_status', true)) {
        update_post_meta($post_id, '_request_status', 'new');
    }
}, 10, 3);


add_filter('manage_request_posts_columns', function($cols){
    $new = [];
    foreach ($cols as $k => $v) {
        if ($k === 'title') {
            $new['title']           = 'Request';
            $new['request_profile'] = 'Profile';
            $new['request_contact'] = 'Contact';
            $new['request_status']  = 'Status';
            $new['request_dt']      = 'Datetime';
        } else {
            $new[$k] = $v;
        }
    }
    return $new;
});

add_action('manage_request_posts_custom_column', function($col, $post_id){
    if ($col === 'request_profile') {
        $pid = (int) get_post_meta($post_id, '_request_profile_id', true);
        if ($pid) {
            echo '<a href="'.esc_url(get_permalink($pid)).'" target="_blank">'.esc_html(get_the_title($pid)).'</a>';
        } else {
            echo '—';
        }
    }

    if ($col === 'request_contact') {
        $name    = (string) get_post_meta($post_id, '_request_name', true);
        $contact = (string) get_post_meta($post_id, '_request_contact', true);
        $out = trim($name . ' / ' . $contact);
        echo esc_html($out !== '' ? $out : '—');
    }

    if ($col === 'request_status') {
        $st  = transescort_get_request_status($post_id);
        $map = transescort_request_statuses();
        echo isset($map[$st]) ? esc_html($map[$st]) : esc_html($st);
    }

    if ($col === 'request_dt') {
        $dt = (string) get_post_meta($post_id, '_request_datetime', true);
        echo $dt !== '' ? esc_html($dt) : '—';
    }
}, 10, 2);


add_action('restrict_manage_posts', function(){
    global $typenow;
    if ($typenow !== 'request') return;

    $current  = isset($_GET['request_status']) ? sanitize_text_field($_GET['request_status']) : '';
    $statuses = transescort_request_statuses();

    echo '<select name="request_status">';
    echo '<option value="">All statuses</option>';
    foreach ($statuses as $k => $label) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($k),
            selected($current, $k, false),
            esc_html($label)
        );
    }
    echo '</select>';
});

add_action('pre_get_posts', function($q){
    if (!is_admin() || !$q->is_main_query()) return;
    if ($q->get('post_type') !== 'request') return;

    if (!empty($_GET['request_status'])) {
        $status   = sanitize_text_field($_GET['request_status']);
        $statuses = transescort_request_statuses();
        if (!isset($statuses[$status])) return;

        $q->set('meta_query', [[
            'key'   => '_request_status',
            'value' => $status,
        ]]);
    }
});


add_action('add_meta_boxes', function () {
    add_meta_box(
        'transescort_request_details',
        'Request details',
        'transescort_request_details_metabox',
        'request',
        'normal',
        'high'
    );
});

function transescort_request_details_metabox($post) {
    wp_nonce_field('transescort_save_request_meta', 'transescort_request_meta_nonce');

    $profile_id = (int) get_post_meta($post->ID, '_request_profile_id', true);
    $user_id    = (int) get_post_meta($post->ID, '_request_user_id', true);

    $name    = (string) get_post_meta($post->ID, '_request_name', true);
    $contact = (string) get_post_meta($post->ID, '_request_contact', true);
    $dt      = (string) get_post_meta($post->ID, '_request_datetime', true);
    $msg     = (string) get_post_meta($post->ID, '_request_message', true);

    $status  = (string) get_post_meta($post->ID, '_request_status', true);
    if ($status === '') $status = 'new';

    $profile_link = $profile_id ? get_edit_post_link($profile_id) : '';
    $user = $user_id ? get_user_by('id', $user_id) : null;
    ?>
    <p><strong>Profile:</strong>
      <?php if ($profile_id): ?>
        <a href="<?php echo esc_url($profile_link); ?>">
          #<?php echo (int)$profile_id; ?> — <?php echo esc_html(get_the_title($profile_id)); ?>
        </a>
      <?php else: ?>
        —
      <?php endif; ?>
    </p>

    <p><strong>User:</strong>
      <?php if ($user): ?>
        #<?php echo (int)$user->ID; ?> — <?php echo esc_html($user->user_login); ?> (<?php echo esc_html($user->user_email); ?>)
      <?php else: ?>
        guest / not logged in
      <?php endif; ?>
    </p>

    <hr>

    <p><strong>Name:</strong> <?php echo esc_html($name ?: '—'); ?></p>
    <p><strong>Contact:</strong> <?php echo esc_html($contact ?: '—'); ?></p>
    <p><strong>Preferred date & time:</strong> <?php echo esc_html($dt ?: '—'); ?></p>

    <p><strong>Message:</strong><br>
      <textarea style="width:100%;min-height:100px;" readonly><?php echo esc_textarea($msg); ?></textarea>
    </p>

    <p>
      <label><strong>Status</strong></label><br>
      <select name="request_status" style="width:260px;">
        <option value="new" <?php selected($status, 'new'); ?>>new</option>
        <option value="accepted" <?php selected($status, 'accepted'); ?>>accepted</option>
        <option value="rejected" <?php selected($status, 'rejected'); ?>>rejected</option>
      </select>
    </p>
    <?php
}


add_action('save_post_request', function ($post_id) {
    if (!isset($_POST['transescort_request_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['transescort_request_meta_nonce'], 'transescort_save_request_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $status = isset($_POST['request_status']) ? sanitize_text_field($_POST['request_status']) : 'new';
    if (!in_array($status, ['new', 'accepted', 'rejected'], true)) {
        $status = 'new';
    }
    update_post_meta($post_id, '_request_status', $status);
});


add_action('pre_get_posts', function($q){
    if (!is_admin() || !$q->is_main_query()) return;
    if ($q->get('post_type') !== 'request') return;

    if (current_user_can('administrator')) return;

    $user_id = get_current_user_id();
    if (!$user_id) return;

    $profile_id = (int) transescort_get_linked_profile_id($user_id);
    if ($profile_id) {
        $meta_query = (array) $q->get('meta_query');
        $meta_query[] = [
            'key'     => '_request_profile_id',
            'value'   => $profile_id,
            'compare' => '=',
            'type'    => 'NUMERIC'
        ];
        $q->set('meta_query', $meta_query);
    } else {
        $q->set('post__in', [0]);
    }
});


function transescort_get_profile_requests($profile_id, $limit = 10) {
    $profile_id = (int) $profile_id;
    $limit      = (int) $limit;

    if (!$profile_id) return [];

    return get_posts([
        'post_type'      => 'request',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_key'       => '_request_profile_id',
        'meta_value'     => $profile_id,
    ]);
}


function transescort_request_status_label($status) {
    $map = transescort_request_statuses();
    return isset($map[$status]) ? $map[$status] : $status;
}




add_action('admin_enqueue_scripts', function ($hook) {

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || empty($screen->post_type)) {
        return;
    }

    // wp.media нужно только для video и profile
    if (in_array($screen->post_type, ['video', 'profile'], true)) {
        wp_enqueue_media();
    }
});


