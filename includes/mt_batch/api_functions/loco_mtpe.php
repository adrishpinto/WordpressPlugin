<?php
function upload_file_to_activeloc($file_path, $languages = array('fr'))
{
    error_log("upload: file=" . $file_path);

    // Get current user + token
    $user_id = get_current_user_id();
    $token_data = get_user_meta($user_id, 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        $activeloc_token = $token_data['token'];
    } else {
        error_log("No valid token found for user.");
        return false;
    }

    $url = ENDPOINT . 'upload_single_file_wp';
    error_log("req url=" . $url);

    if (!file_exists($file_path)) {
        error_log("file not found: " . $file_path);
        return false;
    }

    $boundary = wp_generate_password(24, false);

    $body = '';

    $file_contents = file_get_contents($file_path);
    $body .= "--$boundary\r\n";
    $body .= 'Content-Disposition: form-data; name="file"; filename="' . basename($file_path) . "\"\r\n";
    $body .= "Content-Type: application/octet-stream\r\n\r\n";
    $body .= $file_contents . "\r\n";

    foreach ($languages as $lang) {
        $body .= "--$boundary\r\n";
        $body .= 'Content-Disposition: form-data; name="languages[]"' . "\r\n\r\n";
        $body .= $lang . "\r\n";
    }

    $body .= "--$boundary--\r\n";

    $headers = array(
        'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
        'Authorization' => 'Bearer ' . $activeloc_token,
    );

    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body'    => $body,
        'timeout' => 60,
    ));

    if (is_wp_error($response)) {
        error_log("req error: " . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    $resp_body = wp_remote_retrieve_body($response);
    error_log("resp code=" . $code);

    if ($code !== 200) {
        error_log("non-200 body: " . $resp_body);
        return false;
    }

    error_log("upload ok, resp: " . $resp_body);
    return json_decode($resp_body, true);
}


function activeloc_download_translations($folder_name)
{
    $url = ENDPOINT . 'download_folder_plugin_wp?folder_name=' . urlencode($folder_name);

    $user_id = get_current_user_id();
    $token_data = get_user_meta($user_id, 'activeloc_token', true);
    if (empty($token_data) || time() >= $token_data['expires']) {
        error_log("No valid token found.");
        return false;
    }
    $token = $token_data['token'];

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
        ),
        'timeout' => 60,
    ));

    if (is_wp_error($response)) {
        error_log("Download failed: " . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        error_log("Download failed: HTTP " . $code);
        return false;
    }

    $body = wp_remote_retrieve_body($response);

    // Save to a temporary file
    $tmp_file = wp_tempnam($folder_name . '.zip');
    if (!$tmp_file) {
        error_log("Could not create temp file for download.");
        return false;
    }
    file_put_contents($tmp_file, $body);

    // Extract into WP languages directory
    $dest = WP_LANG_DIR . '/plugins/';
    if (!file_exists($dest)) {
        wp_mkdir_p($dest);
    }

    $zip = new ZipArchive;
    if ($zip->open($tmp_file) === TRUE) {
        $zip->extractTo($dest);
        $zip->close();
        unlink($tmp_file);
        error_log("Translations extracted to: " . $dest);
        return true;
    } else {
        error_log("Failed to open downloaded zip.");
        return false;
    }
}


function list_user_blobs_plugin($folder_name = '')
{
    $folder_name = rtrim($folder_name, '/');

    error_log("list_user_folder_contents() called with folder_name: $folder_name");
    if (empty($folder_name)) {
        return ['error' => 'Folder name is required.'];
    }

    $token_data = get_user_meta(get_current_user_id(), 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        $activeloc_token = $token_data['token'];
    } else {
        $activeloc_token = null;
    }

    $url = ENDPOINT . '/list_files_plugin_wp';
    if ($folder_name !== '') {
        $url .= '?folder_name=' . urlencode($folder_name);
    }

    error_log("Token found, sending request to: $url");

    $response = wp_remote_get(
        $url,
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $activeloc_token,
            ],
        ]
    );

    if (is_wp_error($response)) {
        error_log("wp_remote_get error: " . $response->get_error_message());
        return ['error' => $response->get_error_message()];
    }

    $body = wp_remote_retrieve_body($response);
    error_log("Response body: " . $body);

    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return ['error' => 'Invalid JSON response from API'];
    }

    error_log("Successfully parsed response: " . print_r($data, true));
    return $data;
}


function list_user_folder_wordpress()
{
    error_log("list_user_folder_contents() called");

    $user_id = get_current_user_id();
    $token_data = get_user_meta($user_id, 'activeloc_token', true);

    if (empty($token_data) || !isset($token_data['expires']) || time() >= $token_data['expires']) {
        error_log("No valid token found or token expired");
        return ['error' => 'Authentication required.'];
    }

    $token = $token_data['token'];
    error_log("Token found, sending request to: " . ENDPOINT . '/list_files_plugin_wp');

    $response = wp_remote_get(
        ENDPOINT . '/list_files_plugin_wp',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]
    );

    if (is_wp_error($response)) {
        error_log("wp_remote_get error: " . $response->get_error_message());
        return ['error' => $response->get_error_message()];
    }

    $body = wp_remote_retrieve_body($response);
    error_log("Response body: " . $body);

    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return ['error' => 'Invalid JSON response from API'];
    }

    error_log("Successfully parsed response: " . print_r($data, true));
    return $data;
}

function download_user_folder_wordpress($folder_name)
{
    error_log("download_user_folder_wordpress() called with folder: " . $folder_name);

    $user_id    = get_current_user_id();
    $token_data = get_user_meta($user_id, 'activeloc_token', true);

    if (empty($token_data['token']) || empty($token_data['expires']) || time() >= $token_data['expires']) {
        return ['error' => 'Authentication required.'];
    }

    if (empty($folder_name)) {
        return ['error' => 'Missing folder_name parameter'];
    }

    $folder_name = rtrim($folder_name, '/');

    // 1. List files in folder
    $list_url = ENDPOINT . '/list_files_plugin_wp?folder_name=' . urlencode($folder_name);
    $list_response = wp_remote_get($list_url, [
        'headers' => ['Authorization' => 'Bearer ' . $token_data['token']],
        'timeout' => 30,
    ]);

    if (is_wp_error($list_response)) {
        return ['error' => 'Failed to list files: ' . $list_response->get_error_message()];
    }

    $files_data = json_decode(wp_remote_retrieve_body($list_response), true);
    if (empty($files_data['files'])) {
        return ['error' => 'No files found in folder'];
    }

    $dest_dir = WP_CONTENT_DIR . '/languages/plugins/';
    if (!file_exists($dest_dir)) {
        wp_mkdir_p($dest_dir);
    }

    $saved_files = [];

    foreach ($files_data['files'] as $file_name) {
        if (substr($file_name, -1) === '/') continue; 

        $download_url = ENDPOINT . '/download_file_wp_plugin?file_name=' . urlencode($folder_name . '/' . $file_name);
        $file_response = wp_remote_get($download_url, [
            'headers' => ['Authorization' => 'Bearer ' . $token_data['token']],
            'timeout' => 60,
        ]);

        if (is_wp_error($file_response)) {
            error_log("Failed to download $file_name: " . $file_response->get_error_message());
            continue;
        }

        $file_data = wp_remote_retrieve_body($file_response);
        $file_path = $dest_dir . basename($file_name);

        if (file_put_contents($file_path, $file_data)) {
            $saved_files[] = $file_path;
            error_log("Saved: " . $file_path);
        } else {
            error_log("Failed to save file: " . $file_path);
        }
    }

    if (empty($saved_files)) {
        return ['error' => 'No files were saved'];
    }

    return ['success' => true, 'files' => $saved_files];
}




function download_user_folder_local($folder_name)
{
    error_log("download_user_folder_wordpress() called with folder: " . $folder_name);

    $user_id    = get_current_user_id();
    $token_data = get_user_meta($user_id, 'activeloc_token', true);

    if (empty($token_data['token']) || empty($token_data['expires']) || time() >= $token_data['expires']) {
        error_log("No valid token found or token expired");
        return ['error' => 'Authentication required.'];
    }

    if (empty($folder_name)) {
        error_log("Missing folder_name parameter");
        return ['error' => 'Missing folder_name parameter'];
    }

    $folder_name = rtrim($folder_name, '/'); // ✅ fix: trim before request
    $url = ENDPOINT . '/download_folder_plugin_wp?folder_name=' . urlencode($folder_name);
    error_log("Sending request to: " . $url);

    $response = wp_remote_get($url, [
        'headers' => ['Authorization' => 'Bearer ' . $token_data['token']],
        'timeout' => 60,
    ]);

    if (is_wp_error($response)) {
        error_log("wp_remote_get error: " . $response->get_error_message());
        return ['error' => $response->get_error_message()];
    }

    if (wp_remote_retrieve_response_code($response) !== 200) {
        $body = wp_remote_retrieve_body($response);
        error_log("Non-200 response: " . $body);
        return ['error' => "Failed to download folder: $body"];
    }

    $zip_data   = wp_remote_retrieve_body($response);
    $upload_dir = wp_upload_dir();
    $file_path  = $upload_dir['path'] . '/' . sanitize_file_name($folder_name) . '.zip';

    if (!file_put_contents($file_path, $zip_data)) {
        error_log("Failed to save zip file");
        return ['error' => 'Failed to save zip file'];
    }

    $file_url = $upload_dir['url'] . '/' . basename($file_path);
    error_log("File successfully saved: " . $file_url);

    return ['success' => true, 'file_url' => $file_url];
}
