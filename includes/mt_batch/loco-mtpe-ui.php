<?php
function my_loco_custom_page_render()
{
    if (! function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $plugins = get_plugins();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plugin_file'])) {
        $plugin_file = sanitize_text_field($_POST['plugin_file']);
        $input1 = sanitize_text_field($_POST['input1']);
        $input2 = sanitize_text_field($_POST['input2']);

        $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_file);

        // find .pot files in root and languages folder
        $pot_files_root = glob($plugin_dir . '/*.pot');
        $pot_files_lang = glob($plugin_dir . '/languages/*.pot');
        $pot_files = array_merge($pot_files_root ?: [], $pot_files_lang ?: []);

        if (!empty($pot_files)) {
            foreach ($pot_files as $pot_file) {
                $langs = array_filter([$input1, $input2]);
                $result = upload_file_to_activeloc($pot_file, $langs);

                if ($result) {
                    echo '<div class="notice notice-success"><p>Upload OK: ' . esc_html($result['final_file_name']) . '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Upload failed: ' . esc_html(basename($pot_file)) . '</p></div>';
                }
            }
        }
    }
?>
    <div class="wrap">
        <h1>Plugins with .pot files</h1>

        <form method="post">
            <ul>
                <?php foreach ($plugins as $plugin_file => $plugin_data) :
                    $plugin_dir  = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
                    $plugin_name = $plugin_data['Name'];

                    // Skip Loco Translate
                    if ($plugin_name === 'Loco Translate') {
                        continue;
                    }

                    $pot_files_root = glob($plugin_dir . '/*.pot');
                    $pot_files_lang = glob($plugin_dir . '/languages/*.pot');
                    $pot_files = array_merge($pot_files_root ?: [], $pot_files_lang ?: []);

                    if (!empty($pot_files)) : ?>
                        <li>
                            <label>
                                <input type="radio" name="plugin_file" value="<?php echo esc_attr($plugin_file); ?>" required>
                                <?php echo esc_html($plugin_name); ?>
                            </label>
                        </li>
                <?php endif;
                endforeach; ?>
            </ul>


            <h2>Target Languages</h2>
            <p><input type="text" name="input1" placeholder="e.g. fr"></p>
            <p><input type="text" name="input2" placeholder="e.g. de"></p>

            <p><button type="submit" class="button button-primary">Upload</button></p>
        </form>

        <!-- new code should be added from here below the form you dont have to give full code just the part that follows after this, it should include the folder list for now  -->
        <h2>Available Files & Folders</h2>
        <?php
        $files_data = list_user_folder_wordpress();

        if (isset($files_data['error'])) {
            echo '<div class="notice notice-error"><p>' . esc_html($files_data['error']) . '</p></div>';
        } elseif (!empty($files_data['files'])) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>File/Folder Name</th><th>Download Locally</th><th>Import to WordPress</th></tr></thead>';
            echo '<tbody>';

            foreach ($files_data['files'] as $file) {
                $download_local_url = add_query_arg('download_local', urlencode($file));
                $import_wp_url      = add_query_arg('import_wp', urlencode($file));

                echo '<tr>';
                echo '<td>' . esc_html($file) . '</td>';
                echo '<td><a class="button" href="' . esc_url($download_local_url) . '">Download Locally</a></td>';
                echo '<td><a class="button" href="' . esc_url($import_wp_url) . '">Import to WP</a></td>';
                echo '</tr>';
            }

            echo '</tbody></table>';

            // Handle actions
            if (isset($_GET['download_local'])) {
                $folder_to_download = sanitize_text_field($_GET['download_local']);
                $result = download_user_folder_local($folder_to_download);

                if (isset($result['error'])) {
                    echo '<div class="notice notice-error"><p>' . esc_html($result['error']) . '</p></div>';
                } elseif (!empty($result['file_url'])) {
                    wp_redirect($result['file_url']);
                    exit;
                }
            }

            if (isset($_GET['import_wp'])) {
                $folder_to_import = sanitize_text_field($_GET['import_wp']);
                $result = download_user_folder_wordpress($folder_to_import);

                if (isset($result['error'])) {
                    echo '<div class="notice notice-error"><p>' . esc_html($result['error']) . '</p></div>';
                } elseif (!empty($result['file_url'])) {
                    wp_redirect($result['file_url']);
                    exit;
                }
            }
        } else {
            echo '<p>No files found.</p>';
        }
        ?>


    </div>

<?php
}
