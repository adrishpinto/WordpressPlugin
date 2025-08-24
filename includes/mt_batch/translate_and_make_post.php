<?php
require_once plugin_dir_path(__FILE__) . 'utils.php';


function translate_and_make_post($post, $target_lang, $replace_if_exists = false, $should_publish = false)
{
    $translated_title = activeloc_translate_title($post->post_title, $target_lang);
    $translated_content = translate_html_preserving_images($post->post_content, $target_lang);

    if (!$translated_title || !$translated_content) {
        error_log("Translation failed for post ID {$post->ID}");
        return false;
    }

    if ($replace_if_exists) {
        $query = new WP_Query([
            'post_type'      => $post->post_type,
            'post_status'    => ['publish', 'draft', 'pending', 'future', 'private'],
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'   => '_original_post_id',
                    'value' => $post->ID,
                    'compare' => '=',
                ],
                [
                    'key'   => 'activeloc_lang',
                    'value' => $target_lang,
                    'compare' => '=',
                ],
            ],
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);

        if ($query->have_posts()) {
            $existing_post_id = $query->posts[0];
            wp_delete_post($existing_post_id, true);
        }

        wp_reset_postdata();
    }

    $slug = sanitize_title($translated_title);

    $new_post = [
        'post_title'   => $replace_if_exists ? $translated_title : get_unique_title($translated_title),
        'post_name'    => $slug,
        'post_content' => $translated_content,
        'post_status'  => $should_publish ? 'publish' : 'draft',
        'post_type'    => $post->post_type,
        'post_parent'  => $post->post_parent,
    ];

    $new_post_id = wp_insert_post($new_post);

    if (is_wp_error($new_post_id)) {
        error_log("Failed to create translated post for ID {$post->ID}");
        return;
    }

    update_post_meta($new_post_id, 'activeloc_lang', $target_lang);
    update_post_meta($new_post_id, '_original_post_id', $post->ID);

    // Ensure slug & title get saved properly
    wp_update_post(['ID' => $new_post_id]);

    // Log
    error_log("[ActiveLoc] New translated post created:");
    error_log("  ID: {$new_post_id}");
    error_log("  Title: " . get_the_title($new_post_id));
    error_log("  Slug: " . get_post_field('post_name', $new_post_id));
    error_log("  Permalink: " . get_permalink($new_post_id));
    error_log("  Language: {$target_lang}");
    error_log("  Original Post ID: {$post->ID}");

    // Assign category for target language
    $uncategorized_id = get_cat_ID('Uncategorized');
    $current_cats = wp_get_post_categories($new_post_id);
    $current_cats = array_filter($current_cats, function ($cat_id) use ($uncategorized_id) {
        return $cat_id !== $uncategorized_id;
    });

    $cat_id = get_cat_ID($target_lang);
    if ($cat_id === 0) {
        $cat_id = wp_create_category($target_lang);
    }

    $current_cats[] = $cat_id;
    // --- Update translations array on original post ---
    $translations = get_post_meta($post->ID, 'activeloc_translations', true);
    if (!is_array($translations)) {
        $translations = [];
    }
    $translations[$target_lang] = $new_post_id;

    update_post_meta($post->ID, 'activeloc_translations', $translations);
    wp_set_post_categories($new_post_id, $current_cats, false);
    return [
        'new_post_id' => $new_post_id,
        'original_post_id' => $post->ID,
        'lang' => $target_lang,
        'title' => $translated_title,
    ];

    error_log("Created translated post ID {$new_post_id} from original ID {$post->ID}");
}

function translate_html_preserving_images($html, $target_lang)
{
    $translated = activeloc_translate_text($html, $target_lang);
    if ($translated === false) {
        return false;
    }
    return $translated;
}
