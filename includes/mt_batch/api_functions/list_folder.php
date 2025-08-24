<?php
function list_user_folder_contents()
{
    error_log("list_user_folder_contents() called");

    $user_id = get_current_user_id();
    $token_data = get_user_meta($user_id, 'activeloc_token', true);

    if (empty($token_data) || !isset($token_data['expires']) || time() >= $token_data['expires']) {
        error_log("No valid token found or token expired");
        return ['error' => 'Authentication required.'];
    }

    $token = $token_data['token'];
    error_log("Token found, sending request to: " . ENDPOINT . 'list_files_ext_wp');

    $response = wp_remote_get(
        ENDPOINT . 'list_files_ext_wp',
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
