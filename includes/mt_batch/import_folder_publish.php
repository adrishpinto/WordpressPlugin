<?php
function mtpe_import_folder_publish()
{
    if (!current_user_can('edit_others_posts')) {
        wp_die('Unauthorized');
    }


    if (empty($_GET['folder_name'])) {
        wp_die('Missing folder name');
    }

    $folder_name = sanitize_file_name($_GET['folder_name']);
    $token_data = get_user_meta(get_current_user_id(), 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        $token = $token_data['token'];
    } else {
        $token = null;
    }
    if (!$token) {
        wp_die('Missing token');
    }

    // Get file list in folder via API
    $list_url = ENDPOINT . 'list_files_ext_wp?folder_name=' . urlencode($folder_name);
    $list_response = wp_remote_get($list_url, [
        'headers' => ['Authorization' => 'Bearer ' . $token],
    ]);

    if (is_wp_error($list_response)) {
        wp_die('Failed to get file list: ' . $list_response->get_error_message());
    }

    $files_data = json_decode(wp_remote_retrieve_body($list_response), true);
    if (empty($files_data['files'])) {
        wp_die('No files found in folder');
    }

    foreach ($files_data['files'] as $file_name) {
        if (substr($file_name, -1) === '/') continue; // skip folders

        $name_no_ext = pathinfo($file_name, PATHINFO_FILENAME);
        $parts = explode('_', $name_no_ext);
        if (count($parts) < 4) continue; // skip invalid format

        list($safe_title, $post_type, $original_post_id, $target_lang) = $parts;
        $original_post_id = intval($original_post_id);

        // Download translated content
        $file_url = ENDPOINT . 'download_file_ext_wp?file_name=' . urlencode($folder_name . '/' . $file_name);
        $file_response = wp_remote_get($file_url, [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        if (is_wp_error($file_response)) continue;

        $translated_content = wp_remote_retrieve_body($file_response);

        // Verify original post exists
        if (!get_post($original_post_id)) continue;

        // Create unique slug
        $slug = wp_unique_post_slug(sanitize_title($safe_title), 0, 'draft', $post_type, 0);

        // Insert new draft post
        $new_post_id = wp_insert_post([
            'post_title'   => $safe_title,
            'post_name'    => $slug,
            'post_content' => $translated_content,
            'post_status'  => 'publish',
            'post_type'    => $post_type,
        ]);
        if (is_wp_error($new_post_id)) continue;

        // Update meta
        update_post_meta($new_post_id, 'activeloc_lang', $target_lang);
        update_post_meta($new_post_id, '_original_post_id', $original_post_id);

        // --- Update translations array on original post ---
        $translations = get_post_meta($original_post_id, 'activeloc_translations', true);
        if (!is_array($translations)) {
            $translations = [];
        }
        $translations[$target_lang] = $new_post_id;
        update_post_meta($original_post_id, 'activeloc_translations', $translations);


        // Assign categories: remove Uncategorized, add language category
        $uncat_id = get_cat_ID('Uncategorized');
        $cats = wp_get_post_categories($new_post_id);
        $cats = array_filter($cats, fn($id) => $id !== $uncat_id);
        $lang_cat_id = get_cat_ID($target_lang);
        if (!$lang_cat_id) {
            $lang_cat_id = wp_create_category($target_lang);
        }
        $cats[] = $lang_cat_id;
        wp_set_post_categories($new_post_id, $cats, false);
    }

    // Redirect back or elsewhere
    wp_redirect(admin_url()); // or another appropriate URL
    exit;
}
