<?php
function activeloc_translate_text($text, $to = 'fr')
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

    $response = wp_remote_post('https://api.activeloc.com/translate_wordpress_mt', array(
        'headers' => $headers,
        'body'    => wp_json_encode(array('text' => $text, 'to' => $to)),
        'timeout' => 600,
    ));

    if (is_wp_error($response)) {
        error_log("Translation request failed: " . $response->get_error_message());
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['status']) && $body['status'] === 'success' && isset($body['translated'])) {
        return html_entity_decode($body['translated'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }


    error_log("Unexpected translation response: " . print_r($body, true));
    return false;
}
