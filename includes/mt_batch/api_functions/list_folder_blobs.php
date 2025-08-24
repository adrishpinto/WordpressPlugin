<?php
function list_user_blobs($folder_name = '')
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

    $url = ENDPOINT . 'list_files_ext_wp';
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
