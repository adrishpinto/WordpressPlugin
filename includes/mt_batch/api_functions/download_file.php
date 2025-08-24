<?php
function download_file($file_name)
{
    error_log("download: file=" . $file_name);

    $user_id = get_current_user_id();
    error_log("user_id=" . $user_id);

    $token_data = get_user_meta(get_current_user_id(), 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        $activeloc_token = $token_data['token'];
    } else {
        $activeloc_token = null;
    }

    $url = ENDPOINT . 'download_file_ext_wp?file_name=' . urlencode($file_name);
    error_log("req url=" . $url);

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $activeloc_token,
        ],
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        error_log("req error: " . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    error_log("resp code=" . $code);

    if ($code !== 200) {
        $body = wp_remote_retrieve_body($response);
        error_log("non-200 body: " . $body);
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    error_log("file ok, first 500: " . substr($body, 0, 500));

    return $body;
}
