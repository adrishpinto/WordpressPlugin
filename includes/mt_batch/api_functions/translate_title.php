<?php
function activeloc_translate_title($title, $to = 'fr')
{
    $token_data = get_user_meta(get_current_user_id(), 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        $activeloc_token = $token_data['token'];
    } else {
        $activeloc_token = null;
    }
    
    $headers = array('Content-Type' => 'application/json');

    if (!empty($activeloc_token)) {
        $headers['Authorization'] = 'Bearer ' . $activeloc_token;
    }

    $response = wp_remote_post('https://api.activeloc.com/translate_text_ext_wp', array(
        'headers' => $headers,
        'body'    => wp_json_encode(array('text' => $title, 'to' => $to)),
        'timeout' => 15,
    ));

    if (is_wp_error($response)) {
        error_log("Title translation failed: " . $response->get_error_message());
        return $title; // fallback to original
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['translated'])) {
        return $body['translated'];
    }

    error_log("Unexpected title translation response: " . print_r($body, true));
    return $title; // fallback to original
}
