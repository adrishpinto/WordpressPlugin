<?php
function activeloc_mtpe_bulk_upload($posts, $target_langs)
{
    $endpoint = 'https://api.activeloc.com/upload_folder_ext_wp';

    // Folder name with timestamp (no need to repeat in filename)
    $folder_name = 'mtpe_' . date('d-m-Y') . '_hh-' . date('H') . '_mm-' . date('i') . '_ss-' . date('s');

    $boundary = wp_generate_password(24, false);
    $temp_files = [];

    // Token
    $token_data = get_user_meta(get_current_user_id(), 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        $activeloc_token = $token_data['token'];
    } else {
        $activeloc_token = null;
    }

    $body = '';

    // Folder name
    $body .= "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"folderName\"\r\n\r\n";
    $body .= "$folder_name\r\n";

    // Languages
    foreach ($target_langs as $lang) {
        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"languages[]\"\r\n\r\n";
        $body .= esc_html($lang) . "\r\n";
    }

    // Additional instructions (currently empty)
    $instructions = '';
    $body .= "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"instructions\"\r\n\r\n";
    $body .= $instructions . "\r\n";

    // Make to HTML and add files
    foreach ($posts as $post) {
        $type = get_post_type($post); // post or page
        $safe_title = sanitize_title($post->post_title ?: 'untitled');

        // Filename with metadata: title_type_originalID.html
        $filename = "{$safe_title}_{$type}_{$post->ID}.html";

        $content = "\n{$post->post_content}";

        // Create temp file
        $tmp_file = tmpfile();
        $meta = stream_get_meta_data($tmp_file);
        $tmp_path = $meta['uri'];
        fwrite($tmp_file, $content);
        $temp_files[] = $tmp_file;

        // Add file to multipart body
        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"files[]\"; filename=\"$filename\"\r\n";
        $body .= "Content-Type: text/plain\r\n\r\n";
        $body .= $content . "\r\n";
    }

    $body .= "--$boundary--\r\n";

    // Headers
    $headers = [
        'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
    ];
    if (!empty($activeloc_token)) {
        $headers['Authorization'] = 'Bearer ' . $activeloc_token;
    }

    // Send request
    $response = wp_remote_post($endpoint, [
        'method'  => 'POST',
        'headers' => $headers,
        'body'    => $body,
        'timeout' => 60,
    ]);

    // Remove temp files
    foreach ($temp_files as $file) {
        fclose($file);
    }

    // Log results
    $code = wp_remote_retrieve_response_code($response);
    $resp_body = wp_remote_retrieve_body($response);

    if (is_wp_error($response)) {
        error_log("MTPE bulk upload failed [WP Error]: " . $response->get_error_message());
        return false;
    }

    if (!$code) {
        error_log("MTPE bulk upload failed: no HTTP response code. Response body: $resp_body");
        return false;
    }

    if ($code === 200) {
        error_log("MTPE bulk upload success [HTTP $code]: $resp_body");
        return true;
    } else {
        error_log("MTPE bulk upload failed [HTTP $code]: $resp_body");
        return $code;
    }
}
