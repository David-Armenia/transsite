<?php
defined('ABSPATH') || exit;

add_action('add_meta_boxes_profile', function () {
	    // Profile gallery
    add_meta_box(
      'te_profile_gallery',
      'Profile gallery',
      'te_profile_gallery_metabox',
      'profile',
      'normal',
      'default'
    );

    // Profile details
    add_meta_box(
        'transescort_profile_details',
        'Profile details',
        'transescort_profile_meta_box',
        'profile',
        'normal',
        'high'
    );

    // Linked user (admin only)
    add_meta_box(
        'profile_user_link',
        'Linked user',
        'transescort_profile_user_link_metabox',
        'profile',
        'side',
        'default'
    );

});

function te_profile_gallery_metabox($post){
  wp_nonce_field('te_save_profile_gallery', 'te_profile_gallery_nonce');

  $ids = get_post_meta($post->ID, '_profile_gallery_ids', true);
  if (!is_array($ids)) $ids = [];

  echo '<p>Select photos for profile gallery.</p>';
  echo '<input type="hidden" id="te_profile_gallery_ids" name="te_profile_gallery_ids" value="'.esc_attr(implode(',', $ids)).'">';

  echo '<div id="te-profile-gallery-preview" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:10px;">';
  foreach ($ids as $id) {
    $img = wp_get_attachment_image_url($id, 'thumbnail');
    if ($img) {
      echo '<img src="'.esc_url($img).'" style="width:100%;border-radius:10px;">';
    }
  }
  echo '</div>';

  echo '<button type="button" class="button" id="te-profile-gallery-add">Add photos</button> ';
  echo '<button type="button" class="button" id="te-profile-gallery-clear">Clear</button>';
}


add_action("admin_menu", function () {
  add_options_page(
    "Cities",
    "Cities",
    "manage_options",
    "transescort-cities",
    "transescort_cities_settings_page"
  );
});


function transescort_cities_settings_page() {
    if (!current_user_can("manage_options")) return;

    $saved = isset($_GET["saved"]) ? (string) $_GET["saved"] : "";
    $value = (string) get_option("transescort_city_list", "Москва
Санкт-Петербург
Ереван
Гюмри
Ванадзор");
    ?>
    <div class="wrap">
      <h1>Cities</h1>

      <?php if ($saved === "1"): ?>
        <div class="notice notice-success is-dismissible"><p>Saved.</p></div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>">
        <?php wp_nonce_field("transescort_save_cities", "transescort_save_cities_nonce"); ?>
        <input type="hidden" name="action" value="transescort_save_cities">

        <p>One city per line. Empty lines ignored.</p>
        <textarea name="cities" rows="14" style="width:700px;max-width:100%;"><?php echo esc_textarea($value); ?></textarea>

        <p><button class="button button-primary" type="submit">Save</button></p>
      </form>
    </div>
    <?php
}


add_action("admin_post_transescort_save_cities", function () {
    if (!current_user_can("manage_options")) wp_die("No permission");

    if (empty($_POST["transescort_save_cities_nonce"]) ||
        !wp_verify_nonce($_POST["transescort_save_cities_nonce"], "transescort_save_cities")) {
        wp_die("Security error");
    }

    $cities = isset($_POST["cities"]) ? (string) wp_unslash($_POST["cities"]) : "";
    $cities = str_replace(["0"], "", $cities);
    update_option("transescort_city_list", $cities);

    wp_safe_redirect(add_query_arg("saved", "1", admin_url("options-general.php?page=transescort-cities")));
    exit;
});


function transescort_profile_meta_box($post) {
    wp_nonce_field('transescort_save_profile_meta', 'transescort_profile_meta_nonce');

    $price    = get_post_meta($post->ID, '_profile_price', true);
    $city     = get_post_meta($post->ID, '_profile_city', true);
    $verified = get_post_meta($post->ID, '_profile_verified', true);
    ?>
    <p>
        <label>Price</label><br>
        <input type="text" name="profile_price" value="<?php echo esc_attr($price); ?>" style="width:100%">
    </p>

    <p>
        <label>City</label><br>
        <select name="profile_city" style="width:100%">
<?php foreach (transescort_city_list() as $val => $label): ?>
  <option value="<?php echo esc_attr($val); ?>" <?php selected((string)$city, (string)$val); ?>><?php echo esc_html($label); ?></option>
<?php endforeach; ?>
</select>
    </p>

    <p>
        <label>
            <input type="checkbox" name="profile_verified" value="1" <?php checked($verified, '1'); ?>>
            Verified
        </label>
    </p>
    <?php
}

function transescort_profile_user_link_metabox($post) {
    if (!current_user_can('administrator')) {
        echo '<p style="opacity:.8;">Only admin can link users.</p>';
        return;
    }

    wp_nonce_field('transescort_save_profile_user_link', 'transescort_profile_user_link_nonce');

    $linked_user_id = (int) get_post_meta($post->ID, '_profile_user_id', true);

    $users = get_users([
        'fields'  => ['ID', 'user_login', 'user_email'],
        'orderby' => 'ID',
        'order'   => 'ASC',
    ]);

    echo '<p><label>User</label></p>';
    echo '<select name="profile_user_id" style="width:100%">';
    echo '<option value="0">— Not linked —</option>';

    foreach ($users as $u) {
        $label = $u->user_login . ' (' . $u->user_email . ')';
        printf(
            '<option value="%d" %s>%s</option>',
            (int) $u->ID,
            selected($linked_user_id, (int) $u->ID, false),
            esc_html($label)
        );
    }

    echo '</select>';

    if ($linked_user_id) {
        echo '<p style="margin-top:10px;opacity:.8;font-size:12px;">User meta will be updated automatically.</p>';
    }
}


add_action('save_post_profile', function ($post_id) {

    // 1) Save Profile details
    if (isset($_POST['transescort_profile_meta_nonce']) &&
        wp_verify_nonce($_POST['transescort_profile_meta_nonce'], 'transescort_save_profile_meta')) {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        update_post_meta($post_id, '_profile_price', sanitize_text_field($_POST['profile_price'] ?? ''));
        $__city = sanitize_text_field($_POST["profile_city"] ?? "");
          $__cities = transescort_city_list();
          if (!array_key_exists($__city, $__cities)) { $__city = ""; }
          update_post_meta($post_id, '_profile_city', $__city);
          update_post_meta($post_id, '_profile_verified', isset($_POST['profile_verified']) ? '1' : '0');
    }

    // 2) Save Linked user (admin only)
    if (isset($_POST['transescort_profile_user_link_nonce']) &&
        wp_verify_nonce($_POST['transescort_profile_user_link_nonce'], 'transescort_save_profile_user_link')) {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('administrator')) return;

        $post_id = (int) $post_id;

        $new_user_id = isset($_POST['profile_user_id']) ? (int) $_POST['profile_user_id'] : 0;
        $old_user_id = (int) get_post_meta($post_id, '_profile_user_id', true);

        // update profile -> user link
        if ($new_user_id > 0) {
            update_post_meta($post_id, '_profile_user_id', $new_user_id);
        } else {
            delete_post_meta($post_id, '_profile_user_id');
        }

        // clean old user's reverse link if it pointed to this profile
        if ($old_user_id > 0) {
            $old_linked_profile = (int) get_user_meta($old_user_id, '_linked_profile_id', true);
            if ($old_linked_profile === $post_id) {
                delete_user_meta($old_user_id, '_linked_profile_id');
            }
        }

        // set new user's reverse link
        if ($new_user_id > 0) {
            update_user_meta($new_user_id, '_linked_profile_id', $post_id);
        }

    }
    // 3) Save Profile gallery
    if (
      isset($_POST['te_profile_gallery_nonce']) &&
      wp_verify_nonce($_POST['te_profile_gallery_nonce'], 'te_save_profile_gallery')
    ) {
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
      if (!current_user_can('edit_post', $post_id)) return;

      $ids = isset($_POST['te_profile_gallery_ids'])
        ? array_filter(array_map('intval', explode(',', $_POST['te_profile_gallery_ids'])))
        : [];

      if ($ids) {
        update_post_meta($post_id, '_profile_gallery_ids', $ids);
      } else {
        delete_post_meta($post_id, '_profile_gallery_ids');
      }
    }

});


add_action('add_meta_boxes', function () {
    // твои существующие (если они уже добавляются где-то ещё — удали один из дублей!)
    add_meta_box('te_video_file', 'Video file', 'te_video_file_metabox', 'video', 'normal', 'default');
    add_meta_box('te_video_profile', 'Linked profile', 'te_video_profile_metabox', 'video', 'side', 'default');
    add_meta_box('te_video_meta', 'Video meta', 'te_video_meta_metabox', 'video', 'side', 'default');

    // ✅ новый cover
    add_meta_box('te_video_cover', 'Video cover', 'te_video_cover_metabox', 'video', 'side', 'default');
});


function te_video_cover_metabox($post) {
    wp_nonce_field('te_video_cover_save', 'te_video_cover_nonce');

    $cover_id  = (int) get_post_meta($post->ID, '_video_cover_id', true);
    $cover_url = $cover_id ? wp_get_attachment_image_url($cover_id, 'medium') : '';

    echo '<p><strong>Cover image (preview)</strong></p>';
    echo '<input type="hidden" name="te_video_cover_id" id="te_video_cover_id" value="'.esc_attr($cover_id).'">';

    echo '<div id="te_video_cover_preview" style="margin-bottom:10px;">';
    if ($cover_url) {
        echo '<img src="'.esc_url($cover_url).'" style="width:100%; border-radius:10px;">';
    } else {
        echo '<p style="opacity:.7;">No cover selected.</p>';
    }
    echo '</div>';

    echo '<button type="button" class="button" id="te_video_cover_pick">Choose cover</button> ';
    echo '<button type="button" class="button" id="te_video_cover_remove">Remove</button>';
    ?>

    <?php
}


add_action('save_post_video', function($post_id){

  // autosave / revision
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (wp_is_post_revision($post_id)) return;

  // permissions
  if (!current_user_can('edit_post', $post_id)) return;

  // nonce: разрешаем сохранение если есть nonce от file метабокса ИЛИ от cover
  $ok_main  = (isset($_POST['te_video_nonce']) && wp_verify_nonce($_POST['te_video_nonce'], 'te_video_save'));
  $ok_cover = (isset($_POST['te_video_cover_nonce']) && wp_verify_nonce($_POST['te_video_cover_nonce'], 'te_video_cover_save'));
  if (!$ok_main && !$ok_cover) return;

  // -------------------------
  // 1) attachment (video file)
  // -------------------------
  $att_id = isset($_POST['te_video_attachment_id']) ? (int) $_POST['te_video_attachment_id'] : 0;
  if ($att_id > 0) {
    update_post_meta($post_id, '_video_attachment_id', $att_id);
  } else {
    delete_post_meta($post_id, '_video_attachment_id');
  }

  // -------------------------
  // 2) linked profile
  // -------------------------
  $profile_id = isset($_POST['te_video_profile_id']) ? (int) $_POST['te_video_profile_id'] : 0;
  if ($profile_id > 0) {
    update_post_meta($post_id, '_video_profile_id', $profile_id);
  } else {
    delete_post_meta($post_id, '_video_profile_id');
  }

  // -------------------------
  // 3) duration
  // -------------------------
    // link both ways
    update_post_meta($profile_id, '_profile_user_id', (int)$user_id);
    update_user_meta($user_id, '_linked_profile_id', (int)$profile_id);
});



/** Set main photo (thumbnail) from profile gallery */
add_action("admin_post_transescort_set_main_photo", "transescort_set_main_photo_handler");
function transescort_set_main_photo_handler(){
  if (!is_user_logged_in()) { wp_die("Access denied", "Access denied", ["response"=>403]); }
  if (!isset($_POST["transescort_set_main_photo_nonce"]) || !wp_verify_nonce($_POST["transescort_set_main_photo_nonce"], "transescort_set_main_photo")) {
    wp_die("Bad nonce", "Bad nonce", ["response"=>400]);
  }
  $user_id = get_current_user_id();
  $profile_id = isset($_POST["profile_id"]) ? (int)$_POST["profile_id"] : 0;
  $att_id = isset($_POST["att_id"]) ? (int)$_POST["att_id"] : 0;
  if (!$profile_id || !$att_id) { wp_safe_redirect(wp_get_referer() ?: home_url("/")); exit; }

  if (!(current_user_can("administrator") || (function_exists("transescort_user_owns_profile") && transescort_user_owns_profile($user_id, $profile_id)))) {
    wp_die("Access denied", "Access denied", ["response"=>403]);
  }

  $g = get_post_meta($profile_id, "_profile_gallery_ids", true);
  if (!is_array($g)) $g = [];
  $g = array_values(array_unique(array_map("intval", $g)));
  if (!in_array($att_id, $g, true)) {
    wp_die("Photo not in gallery", "Photo not in gallery", ["response"=>400]);
  }

  set_post_thumbnail($profile_id, $att_id);
  $ref = wp_get_referer();
  if (!$ref) $ref = get_permalink($profile_id);
  $ref = remove_query_arg(["main","upload","deleted"], $ref);
  wp_safe_redirect(add_query_arg("main", "1", $ref));
  exit;
}



add_action("admin_footer", function(){
  $screen = function_exists("get_current_screen") ? get_current_screen() : null;
  if (!$screen) return;
  $pt = isset($screen->post_type) ? $screen->post_type : "";
  if (!in_array($pt, ["profile","video"], true)) return;

  echo "<script>(function($){";

  echo "function initProfileGallery(){";
  echo "if(!$(\"#te-profile-gallery-add\").length) return;";
  echo "let frame;";
  echo "$(document).on(\"click\", \"#te-profile-gallery-add\", function(e){";
  echo "e.preventDefault();";
  echo "frame=wp.media({title:\"Select gallery images\",button:{text:\"Use selected\"},library:{type:\"image\"},multiple:true});";
  echo "frame.on(\"select\", function(){";
  echo "const selection=frame.state().get(\"selection\").toJSON();";
  echo "const ids=selection.map(i=>i.id);";
  echo "$(\"#te_profile_gallery_ids\").val(ids.join(\",\"));";
  echo "const preview=$(\"#te-profile-gallery-preview\");preview.empty();";
  echo "selection.forEach(i=>{const url=(i.sizes&&i.sizes.thumbnail)?i.sizes.thumbnail.url:i.url;preview.append(\"<img src=\\\"\"+url+\"\\\" style=\\\"width:100%;border-radius:10px;\\\">\");});";
  echo "});";
  echo "frame.open();";
  echo "});";
  echo "$(document).on(\"click\", \"#te-profile-gallery-clear\", function(e){e.preventDefault();$(\"#te_profile_gallery_ids\").val(\"\");$(\"#te-profile-gallery-preview\").empty();});";
  echo "}";

  echo "function initVideoCover(){";
  echo "if(!$(\"#te_video_cover_pick\").length) return;";
  echo "let frame;";
  echo "$(document).on(\"click\", \"#te_video_cover_pick\", function(e){";
  echo "e.preventDefault();";
  echo "if(frame){frame.open();return;}";
  echo "frame=wp.media({title:\"Select cover image\",button:{text:\"Use this image\"},library:{type:\"image\"},multiple:false});";
  echo "frame.on(\"select\", function(){";
  echo "const att=frame.state().get(\"selection\").first().toJSON();";
  echo "$(\"#te_video_cover_id\").val(att.id);";
  echo "const url=(att.sizes&&att.sizes.medium)?att.sizes.medium.url:att.url;";
  echo "$(\"#te_video_cover_preview\").html(\"<img src=\\\"\"+url+\"\\\" style=\\\"width:100%; border-radius:10px;\\\">\");";
  echo "});";
  echo "frame.open();";
  echo "});";
  echo "$(document).on(\"click\", \"#te_video_cover_remove\", function(e){e.preventDefault();$(\"#te_video_cover_id\").val(\"\");$(\"#te_video_cover_preview\").html(\"<p style=\\\"opacity:.7;\\\">No cover selected.</p>\");});";
  echo "}";

  echo "$(function(){initProfileGallery();initVideoCover();});";
  echo "})(jQuery);</script>";
});
