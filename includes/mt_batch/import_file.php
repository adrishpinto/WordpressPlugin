<?php
function mtpe_import_file()
{
    if (!current_user_can('edit_others_posts')) {
        wp_die('Unauthorized');
    }


    if (!isset($_GET['file_name'])) {
        wp_die('Missing file name');
    }

    $file_name = sanitize_file_name($_GET['file_name']);

    // Parse filename: safe-title_type_originalID_lang.html
    $name_no_ext = pathinfo($file_name, PATHINFO_FILENAME);
    $parts = explode('_', $name_no_ext);

    if (count($parts) < 4) {
        wp_die('Invalid file name format. Expected: {title}_{type}_{originalID}_{lang}.html');
    }

    $safe_title       = $parts[0];
    $post_type        = $parts[1];
    $original_post_id = intval($parts[2]);
    $target_lang      = $parts[3];

    // Get auth token
    $token_data = get_user_meta(get_current_user_id(), 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        $token = $token_data['token'];
    } else {
        $token = null; 
    }
    if (!$token) {
        wp_die('Missing token');
    }

    // Download translated file from Azure
    $url = ENDPOINT . 'download_file_ext_wp?file_name=' . urlencode($file_name);
    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
    ]);
    if (is_wp_error($response)) {
        wp_die('Download failed: ' . $response->get_error_message());
    }
    $translated_content = wp_remote_retrieve_body($response);

    // Fetch the original post
    $original_post = get_post($original_post_id);
    if (!$original_post) {
        wp_die('Original post not found for ID ' . $original_post_id);
    }

    // Generate a unique slug for the translated title
    $slug = wp_unique_post_slug(
        sanitize_title($safe_title),
        0,
        'draft',
        $post_type,
        0
    );

    // Create the translated post
    $new_post_id = wp_insert_post([
        'post_title'   => $safe_title,
        'post_name'    => $slug,
        'post_content' => $translated_content,
        'post_status'  => 'draft',
        'post_type'    => $post_type,
    ]);

    if (is_wp_error($new_post_id)) {
        wp_die('Failed to create translated post: ' . $new_post_id->get_error_message());
    }

    // Add translation meta
    update_post_meta($new_post_id, 'activeloc_lang', $target_lang);
    update_post_meta($new_post_id, '_original_post_id', $original_post_id);

    // Assign categories
    $uncategorized_id = get_cat_ID('Uncategorized');
    $current_cats = wp_get_post_categories($new_post_id);
    $current_cats = array_filter($current_cats, function ($cat_id) use ($uncategorized_id) {
        return $cat_id !== $uncategorized_id;
    });
    $lang_cat_id = get_cat_ID($target_lang);
    if ($lang_cat_id === 0) {
        $lang_cat_id = wp_create_category($target_lang);
    }
    $current_cats[] = $lang_cat_id;
    wp_set_post_categories($new_post_id, $current_cats, false);

    // Redirect to the new translated post
    wp_redirect(get_permalink($new_post_id));
    exit;
}
