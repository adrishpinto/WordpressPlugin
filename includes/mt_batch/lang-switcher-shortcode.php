<?php

function activeloc_get_supported_languages()
{
    $url = plugin_dir_url(__FILE__) . 'includes/mt_batch/lang_switcher.js';
    // Full list (master list)
    $all_languages = [
        'en' => '🇺🇸 English',
        'fr' => '🇫🇷 French',
        'es' => '🇪🇸 Spanish',
        'it' => '🇮🇹 Italian',
        'kn' => '🇮🇳 Kannada',
        'ru' => '🇷🇺 Russian',
        'zh' => '🇨🇳 Chinese',
        'de' => '🇩🇪 German',
    ];

    // Get admin-selected active langs
    $active_languages = get_option('activeloc_languages', []);

    // If nothing saved yet → return all
    if (empty($active_languages)) {
        return $all_languages;
    }

    // Build filtered array
    $filtered = [];
    foreach ($active_languages as $code) {
        if (isset($all_languages[$code])) {
            $filtered[$code] = $all_languages[$code];
        }
    }

    $filtered['en'] = $all_languages['en'];

    return $filtered;
}



add_shortcode('activeloc_lang_switcher', function ($atts) {
    if (is_admin()) return '';

    $atts = shortcode_atts([
        'navbar'          => false,
        'footer'          => false,
        'top'             => '',
        'bottom'          => '',
        'left'            => '',
        'right'           => '',
        'margin'          => '',
        'position'        => 'absolute',
        'display'         => '',
        'background'      => '',
        'text-color'      => 'black',
        'drop-up' => false,
    ], $atts);

    // Ensure default positions for floating mode
    if (!$atts['navbar'] && !$atts['footer']) {
        if (empty($atts['bottom']) && empty($atts['top'])) $atts['top'] = '10px';
        if (empty($atts['right']) && empty($atts['left'])) $atts['right'] = '10px';
    }

    // Navbar and Footer 
    if ($atts['navbar'] || $atts['footer']) {
        $wrapper_style = 'display: ' . esc_attr($atts['display']) . '; position: relative;';
        if (!empty($atts['background'])) $wrapper_style .= ' background: ' . esc_attr($atts['background']) . ';';
        if (!empty($atts['text-color'])) $wrapper_style .= ' color: ' . esc_attr($atts['text-color']) . ';';

        // Apply top/bottom/left/right margins
        if (!empty($atts['top']))    $wrapper_style .= ' margin-top: ' . esc_attr($atts['top']) . ';';
        if (!empty($atts['bottom'])) $wrapper_style .= ' margin-bottom: ' . esc_attr($atts['bottom']) . ';';
        if (!empty($atts['right']))  $wrapper_style .= ' margin-right: ' . esc_attr($atts['right']) . ';';
        if (!empty($atts['left']))   $wrapper_style .= ' margin-left: ' . esc_attr($atts['left']) . ';';

        // Apply shorthand margin if set (overrides individual sides)
        if (!empty($atts['margin'])) $wrapper_style .= ' margin: ' . esc_attr($atts['margin']) . ';';
    } else { // Advanced Build
        $wrapper_style = 'padding: 0px; border: 0px solid #ccc;';
        $wrapper_style .= ' position: ' . esc_attr($atts['position']) . ';';
        $wrapper_style .= ' display: ' . esc_attr($atts['display']) . ';';
        if (!empty($atts['top']))    $wrapper_style .= ' top: ' . esc_attr($atts['top']) . ';';
        if (!empty($atts['bottom'])) $wrapper_style .= ' bottom: ' . esc_attr($atts['bottom']) . ';';
        if (!empty($atts['right']))  $wrapper_style .= ' right: ' . esc_attr($atts['right']) . ';';
        if (!empty($atts['left']))   $wrapper_style .= ' left: ' . esc_attr($atts['left']) . ';';
        if (!empty($atts['background'])) $wrapper_style .= ' background: ' . esc_attr($atts['background']) . ';';
        if (!empty($atts['text-color'])) $wrapper_style .= ' color: ' . esc_attr($atts['text-color']) . ';';

        // Apply shorthand margin if set
        if (!empty($atts['margin'])) $wrapper_style .= ' margin: ' . esc_attr($atts['margin']) . ';';
    }

    // Floating mode: check bottom position
    $is_low = false;
    if ($atts['footer']) {
        $is_low = true;
    }

    if (!empty($atts['drop-up'])) $is_low = true;


    $langs = activeloc_get_supported_languages();
    $current_lang = activeloc_get_current_lang();
    global $post;

    ob_start();

    echo '<div style="' . esc_attr($wrapper_style) . '" id="activeloc-dropdown">';

    // Current language
    $current_label = $langs[$current_lang];
    $parts = explode(' ', $current_label, 2);
    $flag_emoji = $parts[0];
    $lang_name = $parts[1] ?? '';

    echo '<div id="activeloc-current-flag">';
    echo '<span class="flag">' . esc_html($flag_emoji) . '</span> ';
    echo '<span class="lang-name">' . esc_html($lang_name) . '</span>';
    echo '</div>';

    // Dropdown position
    $dropdown_style = $atts['navbar'] ? 'top: 100%; left: 0; position: absolute;' : ($is_low ? 'bottom: 40px;' : 'top: 40px;');

    // Language options
    echo '<ul id="activeloc-options" style="' . esc_attr($dropdown_style) . '">';
    foreach ($langs as $code => $label) {
        if ($post) {
            $source_id = get_post_meta($post->ID, '_original_post_id', true) ?: $post->ID;
            $segments = explode('/', trim(parse_url(get_permalink($source_id), PHP_URL_PATH), '/'));
            array_shift($segments);
            $url = home_url("/{$code}/" . implode('/', $segments));
        } else {

            $url = home_url("/{$code}/");
        }

        $parts = explode(' ', $label, 2);
        $flag_emoji = $parts[0];
        $lang_name = $parts[1] ?? '';

        echo '<li id="dropdown-items" data-lang="' . esc_attr($code) . '" data-url="' . esc_url($url) . '">';
        echo '<span class="flag">' . esc_html($flag_emoji) . '</span> ';
        echo '<span class="lang-name">' . esc_html($lang_name) . '</span> ';
        echo '<span class="lang-url" style="font-size:10px; color:#666;">'  . '</span>';
        echo '</li>';
    }
    echo '</ul>';
    echo '</div>';

    // JS: toggle + cookie


    return ob_get_clean();
});
