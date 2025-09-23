<?php
require_once plugin_dir_path(__FILE__) . 'admin-ui-dropdown.php';
require_once plugin_dir_path(__FILE__) . 'translate_and_make_post.php';

require_once plugin_dir_path(__DIR__) . 'mt_batch/api_functions/translate.php';
require_once plugin_dir_path(__DIR__) . 'mt_batch/api_functions/upload_folder.php';
require_once plugin_dir_path(__DIR__) . 'mt_batch/api_functions/list_folder.php';
require_once plugin_dir_path(__DIR__) . 'mt_batch/api_functions/login_api.php';

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('select2css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
});




add_action('admin_init', 'activeloc_handle_translator_form');
add_action('admin_init', 'activeloc_handle_mtpe_submit');


function activeloc_render_translator_page()
{
    // AUTH
    $login_result = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activeloc_authorize'])) {
        $current_user = wp_get_current_user();
        $email = sanitize_email($current_user->user_email);

        $login_result = activeloc_wp_login(get_current_user_id(), $email);
    }

?>

    <div class="wrap">
        <!-- Authorize Form -->
        <form method="post" style="margin-bottom: 30px;">
            <h2>Authorize</h2>
            <button type="submit" name="activeloc_authorize" class="button">Authorize</button>
        </form>

        <?php if (!is_null($login_result)): ?>
            <div class="notice notice-<?php echo isset($login_result['error']) ? 'error' : 'success'; ?>">
                <p><?php echo esc_html($login_result['error'] ?? 'Login successful'); ?></p>
            </div>
        <?php endif; ?>

        <?php
        $user_id = get_current_user_id();
        $token_data = ($user_id > 0) ? get_user_meta($user_id, 'activeloc_token', true) : null;

        if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
            $activeloc_token = $token_data['token'];
            echo "You are currently Logged in";
        } else {
            echo "Click on Authorize to access plugin";
        }


        if (!empty($activeloc_token)):
            // rest of content is below this 
            // Post filter variables

            $post_types = isset($_POST['post_types']) ? array_map('sanitize_text_field', $_POST['post_types']) : ['post', 'page'];
            $post_statuses = isset($_POST['post_statuses']) ? array_map('sanitize_text_field', $_POST['post_statuses']) : ['publish', 'draft'];
            $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';

            $args = [
                'post_type'      => $post_types,
                'post_status'    => $post_statuses,
                'posts_per_page' => -1,
                's'              => $search_term,
            ];

            if (!empty($search_term)) {
                add_filter('posts_where', 'activeloc_search_title_only');
            }

            $query = new WP_Query($args);

            if (!empty($search_term)) {
                remove_filter('posts_where', 'activeloc_search_title_only');
            }

            $posts = $query->posts;
        ?>



            <!-- Main Admin UI -->
            <form method="post">
                <?php wp_nonce_field('activeloc_translator_nonce_action', 'activeloc_translator_nonce'); ?>
                <?php lang_dropdown(); ?>

                <p><strong>Filter by Post Type:</strong><br>
                    <label><input type="checkbox" name="post_types[]" value="post" <?php checked(in_array('post', $post_types)); ?>> Post</label>
                    <label><input type="checkbox" name="post_types[]" value="page" <?php checked(in_array('page', $post_types)); ?>> Page</label>
                </p>

                <p><strong>Filter by Status:</strong><br>
                    <label><input type="checkbox" name="post_statuses[]" value="publish" <?php checked(in_array('publish', $post_statuses)); ?>> Published</label>
                    <label><input type="checkbox" name="post_statuses[]" value="draft" <?php checked(in_array('draft', $post_statuses)); ?>> Draft</label>
                </p>

                <p>
                    <label for="search_term"><strong>Search Title:</strong></label>
                    <input type="text" name="search_term" id="search_term" value="<?php echo esc_attr($search_term); ?>" style="width: 300px;">
                </p>

                <p>
                    <input type="submit" name="activeloc_filter_submit" class="button" value="Apply Filter">
                    <a href="<?php echo admin_url('admin.php?page=activeloc-mtpe-list'); ?>" class="button">
                        MTPE Completed List
                    </a>
                </p>

                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all">
                                Select
                            </th>
                            <th>Title</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody id="post-list">
                        <?php foreach ($posts as $index => $post): ?>
                            <tr class="post-row" <?php if ($index >= 10) echo 'style="display:none;"'; ?>>
                                <td><input type="checkbox" name="activeloc_post_ids[]" value="<?php echo esc_attr($post->ID); ?>"></td>
                                <td><?php echo esc_html($post->post_title); ?></td>
                                <td><?php echo esc_html($post->post_type); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (count($posts) > 10): ?>
                    <p>
                        <button type="button" id="load-more" class="button">Load More</button>
                    </p>
                <?php endif; ?>

                <p>
                    <label><input type="checkbox" name="replace_if_exists" value="1" <?php checked(isset($_POST['replace_if_exists'])); ?>> Overwrite if title & language match</label>
                </p>

                <p>
                    <label><input type="checkbox" name="should_publish" value="1" <?php checked(isset($_POST['should_publish'])); ?>> Publish translated posts</label>
                </p>

                <p>
                    <input type="submit" name="activeloc_translator_submit" class="button button-primary" value="Translate Now">
                    <input type="submit" name="activeloc_mtpe_submit" class="button" value="Upload to MTPE">
                </p>
            </form>

            <script>
                jQuery(document).ready(function($) {
                    // Select All toggle
                    $('#select-all').on('change', function() {
                        var isChecked = $(this).is(':checked');
                        $('#post-list').find('input[type="checkbox"]').prop('checked', isChecked);
                    });

                    // Load More posts
                    $('#load-more').on('click', function() {
                        $('.post-row:hidden').slice(0, 10).slideDown();
                        if ($('.post-row:hidden').length === 0) {
                            $('#load-more').hide();
                        }
                    });
                });
            </script>
        <?php endif; ?>
    </div>
    <?php
}

// Handle Translation
function activeloc_handle_translator_form()
{
    if (
        isset($_POST['activeloc_translator_submit']) &&
        check_admin_referer('activeloc_translator_nonce_action', 'activeloc_translator_nonce')
    ) {
        if (empty($_POST['activeloc_target_langs'])) {
            set_transient('activeloc_notice', [
                'type' => 'error',
                'text' => 'Please select at least one target language.'
            ], 30);
            return;
        }

        if (empty($_POST['activeloc_post_ids'])) {
            set_transient('activeloc_notice', [
                'type' => 'error',
                'text' => 'Please select at least one post to translate.'
            ], 30);
            return;
        }

        $target_langs      = array_map('sanitize_text_field', $_POST['activeloc_target_langs']);
        $replace_if_exists = isset($_POST['replace_if_exists']);
        $should_publish    = isset($_POST['should_publish']);

        $messages = [];

        foreach ($_POST['activeloc_post_ids'] as $post_index => $post_id) {
            $post = get_post((int) $post_id);
            if ($post) {
                foreach ($target_langs as $lang_index => $lang) {
                    $new_post_id = translate_and_make_post($post, $lang, $replace_if_exists, $should_publish);

                    if ($new_post_id) { // only add message if translation succeeded
                        $messages[] = sprintf(
                            'Translated "%s" to %s (%d of %d, language %d of %d)',
                            $post->post_title,
                            $lang,
                            $post_index + 1,
                            count($_POST['activeloc_post_ids']),
                            $lang_index + 1,
                            count($target_langs)
                        );
                    } else {
                        $messages[] = sprintf(
                            'Failed to translate "%s" to %s',
                            $post->post_title,
                            $lang
                        );
                    }
                }
            }
        }


        set_transient('activeloc_notice', [
            'type' => 'info',
            'text' => implode("\n", $messages)
        ], 30);
    }
}



// Handle MTPE Upload
function activeloc_handle_mtpe_submit()
{
    if (
        isset($_POST['activeloc_mtpe_submit']) &&
        check_admin_referer('activeloc_translator_nonce_action', 'activeloc_translator_nonce')
    ) {
        // No posts selected
        if (empty($_POST['activeloc_post_ids'])) {
            set_transient('activeloc_notice', [
                'type' => 'error',
                'text' => 'Please select at least one post to upload to MTPE.'
            ], 30);
            return;
        }

        // No target languages selected
        if (empty($_POST['activeloc_target_langs'])) {
            set_transient('activeloc_notice', [
                'type' => 'error',
                'text' => 'Please select at least one target language before uploading.'
            ], 30);
            return;
        }

        // Sanitize input
        $post_ids     = array_map('intval', $_POST['activeloc_post_ids']);
        $target_langs = array_map('sanitize_text_field', $_POST['activeloc_target_langs']);
        $posts        = array_filter(array_map('get_post', $post_ids));

        if (!empty($posts)) {
            // Call upload function and get result
            $upload_result = activeloc_mtpe_bulk_upload($posts, $target_langs);

            if ($upload_result === true) {
                set_transient('activeloc_notice', [
                    'type' => 'success',
                    'text' => 'Posts uploaded to MTPE successfully.'
                ], 30);
            } else {
                $error_text = is_numeric($upload_result)
                    ? "MTPE upload failed (HTTP code: $upload_result). Please try again."
                    : "MTPE upload failed. Please try again.";

                set_transient('activeloc_notice', [
                    'type' => 'error',
                    'text' => $error_text
                ], 30);
            }
        } else {
            set_transient('activeloc_notice', [
                'type' => 'error',
                'text' => 'No valid posts found for upload.'
            ], 30);
        }
    }
}


add_action('admin_notices', function () {
    if ($notice = get_transient('activeloc_notice')) {
        delete_transient('activeloc_notice');

        // Use 'info' as neutral, map classes
        $class_map = [
            'error'   => 'notice-error',
            'success' => 'notice-success',
            'info'    => 'notice-info', // neutral/blue
        ];
        $class = $class_map[$notice['type']] ?? 'notice-info';
    ?>
        <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
            <p><?php echo wp_kses_post(nl2br($notice['text'])); ?></p>
        </div>
<?php
    }
});


function activeloc_search_title_only($where)
{
    global $wpdb;
    if (isset($_POST['search_term']) && $_POST['search_term'] !== '') {
        $search = esc_sql($_POST['search_term']);
        $where .= " AND {$wpdb->posts}.post_title LIKE '%{$search}%'";
    }
    return $where;
}
