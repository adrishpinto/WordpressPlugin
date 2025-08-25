<?php

/**
 * Plugin Name: ActiveLoc API Plugin
 * Description: Translates text via ActiveLoc API and enables categories for pages.
 * Version: 1.2
 * Author: Your Name
 */

define('ENDPOINT', 'https://api.activeloc.com/');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/api_functions/login_api.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/api_functions/translate.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/api_functions/translate_title.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/api_functions/list_folder_blobs.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/admin-ui-main.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/mtpe-list.php';
function activeloc_render_short_code_page()
{
    include plugin_dir_path(__FILE__) . 'includes/mt_batch/admin-ui-shortcode.php';
}
function activeloc_render_guide_support_page()
{
    include plugin_dir_path(__FILE__) . 'includes/mt_batch/admin-ui-guide.php';
}


require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/import_file.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/lang-utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/lang-switcher-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/download_folder_handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/import_folder_draft.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/import_folder_publish.php';
require_once plugin_dir_path(__FILE__) . 'includes/mt_batch/api_functions/download_status_file.php';


// download and import mtpe actions
add_action('admin_post_mtpe_download_folder', 'mtpe_download_folder');
add_action('admin_post_mtpe_import_folder_draft', 'mtpe_import_folder_draft');
add_action('admin_post_mtpe_import_folder_publish', 'mtpe_import_folder_publish');
add_action('admin_post_mtpe_download_status_file', 'mtpe_download_status_file');

//toasts
function enqueue_toastify()
{
    wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', [], null, true);
    wp_enqueue_style('toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_toastify');


add_action('wp_enqueue_scripts', function () {
    if (is_admin()) return;

    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_script(
        'activeloc-lang-switcher',
        $plugin_url . 'includes/mt_batch/lang_switcher.js',
        [],
        null,
        true
    );
});

// VIEW FILE LIST START
add_action('wp_ajax_mtpe_get_folder_files', 'mtpe_get_folder_files');
function mtpe_get_folder_files()
{
    if (!isset($_POST['folder_name'])) {
        wp_send_json_error(['message' => 'Folder name required']);
    }

    $folder_name = sanitize_text_field($_POST['folder_name']);
    $files = list_user_blobs($folder_name);

    if (isset($files['error'])) {
        wp_send_json_error(['message' => $files['error']]);
    }

    wp_send_json_success(['files' => $files['files'] ?? []]);
}


function mtpe_enqueue_scripts($hook)
{
    if ($hook !== 'activeloc-translator_page_activeloc-mtpe-list') return;

    wp_enqueue_script('jquery');

    wp_add_inline_script('jquery', " ... your JS here ... ");
}
add_action('admin_enqueue_scripts', 'mtpe_enqueue_scripts');


// AJAX handler to get status file content
add_action('wp_ajax_mtpe_get_status_file', 'mtpe_get_status_file');
function mtpe_get_status_file()
{
    if (!isset($_POST['folder_name'])) {
        wp_send_json_error(['message' => 'Folder name required']);
    }

    $folder_name = sanitize_text_field($_POST['folder_name']);
    $token_data = get_user_meta(get_current_user_id(), 'activeloc_token', true);

    if (!empty($token_data) && isset($token_data['expires']) && time() < $token_data['expires']) {
        $token = $token_data['token'];
    } else {
        $token = null;
    }

    if (!$token) {
        wp_send_json_error(['message' => 'Missing token']);
    }

    $file_path = $folder_name . '__status__.txt';
    $url = ENDPOINT . 'download_file_ext_wp?file_name=' . urlencode($file_path);

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }

    $body = wp_remote_retrieve_body($response);

    wp_send_json_success(['status' => $body]);
}






$lang_array = ['fr', 'es', 'de', 'it', 'kn', 'en'];

add_action('admin_post_mtpe_view_file_list', 'mtpe_view_file_list_handler');

function mtpe_view_file_list_handler()
{
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }

    if (empty($_GET['folder_name'])) {
        wp_die('Folder name missing');
    }

    $folder_name = sanitize_text_field($_GET['folder_name']);
    $files_data = list_user_blobs($folder_name);

    if (isset($files_data['error'])) {
        wp_die('Error: ' . esc_html($files_data['error']));
    }

    echo '<div class="wrap"><h1>Files in folder: ' . esc_html($folder_name) . '</h1><ul>';
    foreach ($files_data['files'] as $file) {
        echo '<li>' . esc_html($file) . '</li>';
    }
    echo '</ul><a href="' . esc_url(admin_url('admin.php?page=activeloc-mtpe-list')) . '">Back</a></div>';

    exit;
}

// Enqueues frontend CSS for the language switcher
add_action('wp_enqueue_scripts', function () {
    if (!is_admin()) {
        wp_enqueue_style(
            'activeloc-lang-switcher',
            plugin_dir_url(__FILE__) . 'includes/mt_batch/lang_switcher.css',
            [],
            '1.0'
        );
    }
});

// Enables category support for WordPress pages
function activeloc_enable_categories_for_pages()
{
    register_taxonomy_for_object_type('category', 'page');
}
add_action('init', 'activeloc_enable_categories_for_pages');



#-------------------------
### ROUTING RULES FROM HERE
#-------------------------

function activeloc_register_query_vars($vars)
{
    $vars[] = 'lang';
    return $vars;
}
add_filter('query_vars', 'activeloc_register_query_vars');

//pagename used for pages
//name used for posts
function activeloc_language_rewrite_rules()
{
    global $lang_array;

    foreach ($lang_array as $lang) {
        // Hierarchical pages 
        add_rewrite_rule(
            $lang . '/(.+)/?$',
            'index.php?pagename=$matches[1]&lang=' . $lang,
            'top'
        );

        // Single posts / custom post types
        add_rewrite_rule(
            $lang . '/([^/]+)/?$',
            'index.php?name=$matches[1]&lang=' . $lang,
            'top'
        );

        // error_log("REWRITE: Added rules for language '$lang'");
    }
}
add_action('init', 'activeloc_language_rewrite_rules');



//which post/page to load
function activeloc_filter_language_query($query)
{
    if (!is_admin() && $query->is_main_query()) {
        $lang = get_query_var('lang');
        $slug = get_query_var('pagename') ?: get_query_var('name');
        error_log("slug=" . $slug);
        error_log("lang=" . $lang);

        if ($lang && $slug) {
            error_log("QUERY: Requested slug='$slug' with lang='$lang'");

            $post_types = get_post_types(['public' => true]);
            $original_post = null;

            if (get_query_var('pagename')) {
                $original_post = get_page_by_path($slug, OBJECT, 'page');
                error_log("QUERY: Searching for page with pagename slug='$slug'");
            }

            if (!$original_post) {
                $original_post = get_page_by_path($slug, OBJECT, $post_types);
                error_log("QUERY: Searching for post with original_post slug='$slug'");
            }

            if ($original_post) {
                error_log("QUERY: Found original post ID={$original_post->ID}, type={$original_post->post_type}");

                $original_id = get_post_meta($original_post->ID, '_original_post_id', true) ?: $original_post->ID;
                error_log("QUERY: Original ID resolved to $original_id");

                $translations = get_post_meta($original_id, 'activeloc_translations', true);
                if (!is_array($translations)) {
                    $translations = [];
                    error_log("QUERY: No translations array found → using empty set");
                }

                $translated_id = $translations[$lang] ?? $original_id;

                if ($translated_id == $original_id) {
                    error_log("QUERY: No translation for '$lang' → falling back to original ($original_id)");
                } else {
                    error_log("QUERY: Found translation for '$lang' → $translated_id");
                }

                set_query_var('translated_id', $translated_id);

                $translated_post = get_post($translated_id);
                if ($translated_post) {
                    if ($translated_post->post_type === 'page') {
                        $query->set('page_id', $translated_id);
                        $query->set('pagename', $translated_post->post_name);
                    } else {
                        $query->set('p', $translated_id);
                        $query->set('name', $translated_post->post_name);
                    }
                }
            } else {
                error_log("QUERY: No matching original post for slug='$slug'");
            }
        } else {
            error_log("QUERY: Missing lang or slug → lang='$lang', slug='$slug'");
        }
    }
}
add_action('pre_get_posts', 'activeloc_filter_language_query');


// from previous post/page load the right post/page 
function activeloc_force_translated_post($posts, $query)
{
    if (!is_admin() && $query->is_main_query()) {
        $translated_id = get_query_var('translated_id');

        if ($translated_id) {
            $translated_post = get_post($translated_id);

            if ($translated_post) {
                error_log("MAIN QUERY: Forcing translated post → ID={$translated_post->ID}, type={$translated_post->post_type}, slug={$translated_post->post_name}");

                // Set query flags based on post type
                if ($translated_post->post_type === 'page') {
                    $query->is_page = true;
                    $query->is_singular = true;
                    $query->is_single = false;
                    $query->is_home = false;
                    $query->is_post_type_archive = false;
                    $query->queried_object = $translated_post;
                    $query->queried_object_id = $translated_post->ID;
                } elseif ($translated_post->post_type === 'post') {
                    $query->is_single = true;
                    $query->is_singular = true;
                    $query->is_page = false;
                    $query->is_home = false;
                    $query->is_post_type_archive = false;
                    $query->queried_object = $translated_post;
                    $query->queried_object_id = $translated_post->ID;
                } else {
                    // Custom post types
                    $query->is_singular = true;
                    $query->is_page = false;
                    $query->is_single = false;
                    $query->is_home = false;
                    $query->is_post_type_archive = false;
                    $query->queried_object = $translated_post;
                    $query->queried_object_id = $translated_post->ID;
                }

                return [$translated_post];
            }
        }
    }

    return $posts;
}
add_filter('posts_results', 'activeloc_force_translated_post', 10, 2);

add_action('save_post', 'log_post_creation_details', 10, 3);


function log_post_creation_details($post_id, $post, $update)
{
    // Skip revisions or autosaves
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    // Post details
    $title = get_the_title($post_id);
    $slug = $post->post_name;
    $permalink = get_permalink($post_id);
    $post_type = $post->post_type;
    $status = $post->post_status;
    $parent = $post->post_parent;

    error_log("[Post Logger] Post created/updated:");
    error_log("  ID: $post_id");
    error_log("  Title: $title");
    error_log("  Slug: $slug");
    error_log("  Permalink: $permalink");
    error_log("  Post Type: $post_type");
    error_log("  Status: $status");
    error_log("  Parent ID: $parent");
}


function activeloc_disable_canonical_redirect($redirect_url)
{
    if (get_query_var('lang')) {
        return false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'activeloc_disable_canonical_redirect');


function activeloc_prepend_lang_to_permalink($url, $post, $leavename)
{
    global $lang_array;

    if (is_admin()) {
        return $url;
    }

    if (is_numeric($post)) {
        $post = get_post($post);
    }

    $post_id = $post ? $post->ID : 'unknown';


    $original_id = $post ? get_post_meta($post->ID, '_original_id', true) : '';


    $lang = (!$original_id) ? 'en' : ($_COOKIE['activeloc_lang'] ?? 'en');

    if ($lang && in_array($lang, $lang_array) && $post) {
        $url_path = parse_url($url, PHP_URL_PATH);
        $url = home_url("/$lang$url_path");
    } else {
        error_log("PERMALINK: Skipped for Post ID=$post_id, lang='$lang'");
    }

    return $url;
}


add_filter('post_link', 'activeloc_prepend_lang_to_permalink', 10, 3);
add_filter('page_link', 'activeloc_prepend_lang_to_permalink', 10, 3);
add_filter('post_type_link', 'activeloc_prepend_lang_to_permalink', 10, 3);

add_action('admin_post_mtpe_download_file', 'mtpe_download_file');
add_action('admin_post_mtpe_import_file', 'mtpe_import_file');


register_activation_hook(__FILE__, function () {
    activeloc_language_rewrite_rules();
    flush_rewrite_rules();
});

function enqueue_toastr()
{
    wp_enqueue_style('toastr-css', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css');
    wp_enqueue_script('toastr-js', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_toastr');
