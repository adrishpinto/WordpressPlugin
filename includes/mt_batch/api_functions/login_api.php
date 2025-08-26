<?php
function activeloc_wp_login($user_id, $email)
{
    $url = ENDPOINT . 'wordpress_customer_login';

    $timestamp = time();
    $payload   = $email . $timestamp;

    $secret = get_option('activeloc_wp_secret');
    $secret = "1234";

    if (empty($secret)) {
        error_log("ActiveLoc Error: Secret not set");
        return ['error' => 'Secret not configured'];
    }

    $signature = hash_hmac('sha256', $payload, $secret);

    $headers = [
        'Content-Type'      => 'application/json',
        'X-Plugin-Email'    => $email,
        'X-Plugin-Timestamp'=> $timestamp,
        'X-Plugin-Signature'=> $signature,
    ];

    $body = json_encode(['email' => $email]);

    $response = wp_remote_post($url, [
        'headers' => $headers,
        'body'    => $body,
        'timeout' => 10,
    ]);

    // Logging
    error_log("ActiveLoc Login Request to $url");
    error_log("Request headers: " . print_r($headers, true));
    error_log("Request body: " . $body);

    if (is_wp_error($response)) {
        error_log("WP Error: " . $response->get_error_message());
        return ['error' => $response->get_error_message()];
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    error_log("Response Code: $code");
    error_log("Response Body: " . print_r($body, true));

    // Store token with 24-hour expiration
    if ($code === 200 && !empty($body['token'])) {
        $expiration = time() + 24 * 60 * 60;
        update_user_meta($user_id, 'activeloc_token', [
            'token'   => $body['token'],
            'expires' => $expiration,
        ]);
    }

    return ($code === 200) ? $body : ['error' => $body['error'] ?? 'Unknown error'];
}


// Example of checking token later
function get_activeloc_token($user_id)
{
    $token_data = get_user_meta($user_id, 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        return $token_data['token'];
    }

    return null;
}

register_uninstall_hook(__FILE__, 'activeloc_plugin_uninstall');

function activeloc_plugin_uninstall()
{
    // Remove the token from all users
    $users = get_users();
    foreach ($users as $user) {
        delete_user_meta($user->ID, 'activeloc_token');
    }
}
