<?php
function mtpe_download_folder()
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

    $zip = new ZipArchive();
    $tmp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('zip_', true) . '.zip';
    if ($zip->open($tmp_file, ZipArchive::CREATE) !== TRUE) {
        wp_die('Could not create ZIP file');
    }

    foreach ($files_data['files'] as $file_name) {
        if (substr($file_name, -1) === '/') continue;

        $file_url = ENDPOINT . 'download_file_ext_wp?file_name=' . urlencode($folder_name . '/' . $file_name);
        $file_response = wp_remote_get($file_url, [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);

        if (is_wp_error($file_response)) continue;

        $file_body = wp_remote_retrieve_body($file_response);
        $zip->addFromString($file_name, $file_body);
    }

    $zip->close();

    $filesize = filesize($tmp_file);
    if (!$filesize) {
        wp_die('Zip file is empty or cannot be read');
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $folder_name . '.zip"');
    header('Content-Length: ' . $filesize);

    while (ob_get_level()) {
        ob_end_clean();
    }

    readfile($tmp_file);
    unlink($tmp_file);
    exit;
}
