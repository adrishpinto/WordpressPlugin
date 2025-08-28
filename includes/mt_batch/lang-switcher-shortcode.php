<?php

function activeloc_get_supported_languages()
{
    $url = plugin_dir_url(__FILE__) . 'includes/mt_batch/lang_switcher.js';
    // Full list (master list)
    $all_languages = [
        'af' => '🇿🇦 Afrikaans',
        'sq' => '🇦🇱 Albanian',
        'ar' => '🇸🇦 Arabic',
        'az' => '🇦🇿 Azerbaijani (Latin)',
        'ba' => '🇷🇺 Bashkir',
        'eu' => '🇪🇸 Basque',
        'bs' => '🇧🇦 Bosnian (Latin)',
        'bg' => '🇧🇬 Bulgarian',
        'yue' => '🇭🇰 Cantonese (Traditional)',
        'ca' => '🇪🇸 Catalan',
        'lzh' => '🇨🇳 Chinese (Literary)',
        'zh-Hans' => '🇨🇳 Chinese Simplified',
        'zh-Hant' => '🇹🇼 Chinese Traditional',
        'hr' => '🇭🇷 Croatian',
        'cs' => '🇨🇿 Czech',
        'da' => '🇩🇰 Danish',
        'nl' => '🇳🇱 Dutch',
        'en' => '🇺🇸 English',
        'et' => '🇪🇪 Estonian',
        'fo' => '🇫🇴 Faroese',
        'fj' => '🇫🇯 Fijian',
        'fil' => '🇵🇭 Filipino',
        'fi' => '🇫🇮 Finnish',
        'fr' => '🇫🇷 French',
        'fr-ca' => '🇨🇦 French (Canada)',
        'gl' => '🇪🇸 Galician',
        'de' => '🇩🇪 German',
        'ht' => '🇭🇹 Haitian Creole',
        'hi' => '🇮🇳 Hindi',
        'mww' => '🌏 Hmong Daw (Latin)',
        'hu' => '🇭🇺 Hungarian',
        'is' => '🇮🇸 Icelandic',
        'id' => '🇮🇩 Indonesian',
        'ia' => '🌍 Interlingua',
        'ikt' => '🇨🇦 Inuinnaqtun',
        'iu-Latn' => '🇨🇦 Inuktitut (Latin)',
        'ga' => '🇮🇪 Irish',
        'it' => '🇮🇹 Italian',
        'ja' => '🇯🇵 Japanese',
        'kn' => '🇮🇳 Kannada',
        'kk' => '🇰🇿 Kazakh (Cyrillic)',
        'kk-cyrl' => '🇰🇿 Kazakh (Cyrillic)',
        'kk-latn' => '🇰🇿 Kazakh (Latin)',
        'ko' => '🇰🇷 Korean',
        'ku-latn' => '🇹🇷 Kurdish (Latin) (Northern)',
        'kmr' => '🇹🇷 Kurdish (Latin) (Northern)',
        'ky' => '🇰🇬 Kyrgyz (Cyrillic)',
        'lv' => '🇱🇻 Latvian',
        'lt' => '🇱🇹 Lithuanian',
        'mk' => '🇲🇰 Macedonian',
        'mg' => '🇲🇬 Malagasy',
        'ms' => '🇲🇾 Malay (Latin)',
        'ml' => '🇮🇳 Malayalam',
        'mt' => '🇲🇹 Maltese',
        'mi' => '🇳🇿 Maori',
        'mr' => '🇮🇳 Marathi',
        'mn-Cyrl' => '🇲🇳 Mongolian (Cyrillic)',
        'ne' => '🇳🇵 Nepali',
        'nb' => '🇳🇴 Norwegian Bokmål',
        'pl' => '🇵🇱 Polish',
        'pt' => '🇧🇷 Portuguese (Brazil)',
        'pt-br' => '🇧🇷 Portuguese (Brazil)',
        'pt-pt' => '🇵🇹 Portuguese (Portugal)',
        'pa' => '🇮🇳 Punjabi',
        'otq' => '🇲🇽 Queretaro Otomi',
        'ro' => '🇷🇴 Romanian',
        'ru' => '🇷🇺 Russian',
        'sm' => '🇼🇸 Samoan (Latin)',
        'sr-Cyrl' => '🇷🇸 Serbian (Cyrillic)',
        'sr' => '🇷🇸 Serbian (Latin)',
        'sr-latn' => '🇷🇸 Serbian (Latin)',
        'sk' => '🇸🇰 Slovak',
        'sl' => '🇸🇮 Slovenian',
        'so' => '🇸🇴 Somali',
        'es' => '🇪🇸 Spanish',
        'sw' => '🇰🇪 Swahili (Latin)',
        'sv' => '🇸🇪 Swedish',
        'ty' => '🇵🇫 Tahitian',
        'ta' => '🇮🇳 Tamil',
        'tt' => '🇷🇺 Tatar (Latin)',
        'te' => '🇮🇳 Telugu',
        'to' => '🇹🇴 Tongan',
        'tr' => '🇹🇷 Turkish',
        'tk' => '🇹🇲 Turkmen (Latin)',
        'uk' => '🇺🇦 Ukrainian',
        'hsb' => '🇩🇪 Upper Sorbian',
        'uz' => '🇺🇿 Uzbek (Latin)',
        'vi' => '🇻🇳 Vietnamese',
        'cy' => '🏴 Welsh',
        'yua' => '🇲🇽 Yucatec Maya',
        'zu' => '🇿🇦 Zulu',
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
        'navbar'     => false,
        'footer'     => false,
        'top'        => '1px',
        'bottom'     => '',
        'left'       => '',
        'right'      => '25px',
        'margin'     => '',
        'position'   => 'absolute',
        'display'    => '',
        'background' => '',
        'text-color' => 'black',
        'drop-up'    => false,
    ], $atts);

    $px_checker = function ($value) {
        if ($value === '' || $value === null) return '';
        if (preg_match('/(px|em|rem|%|vh|vw)$/', trim($value))) return $value;
        if (is_numeric($value)) return $value . 'px';
        return $value;
    };

    $atts['top']    = $px_checker($atts['top']);
    $atts['bottom'] = $px_checker($atts['bottom']);
    $atts['left']   = $px_checker($atts['left']);
    $atts['right']  = $px_checker($atts['right']);
    $atts['margin'] = $px_checker($atts['margin']);

    $atts['navbar'] = filter_var($atts['navbar'], FILTER_VALIDATE_BOOLEAN);
    $atts['footer'] = filter_var($atts['footer'], FILTER_VALIDATE_BOOLEAN);
    $atts['drop-up'] = filter_var($atts['drop-up'], FILTER_VALIDATE_BOOLEAN);

    if (!$atts['navbar'] && !$atts['footer']) {
        if (empty($atts['bottom']) && empty($atts['top'])) $atts['top'] = '10px';
        if (empty($atts['right']) && empty($atts['left'])) $atts['right'] = '10px';
    }

    // Navbar and Footer 
    if ($atts['navbar'] || $atts['footer']) {
        $wrapper_style = 'display: ' . esc_attr($atts['display']) . '; position: relative;';
        if (!empty($atts['background'])) $wrapper_style .= ' background: ' . esc_attr($atts['background']) . ';';
        if (!empty($atts['text-color'])) $wrapper_style .= ' color: ' . esc_attr($atts['text-color']) . ';';

        // Apply margins
        if (!empty($atts['top']))    $wrapper_style .= ' margin-top: ' . esc_attr($atts['top']) . ';';
        if (!empty($atts['bottom'])) $wrapper_style .= ' margin-bottom: ' . esc_attr($atts['bottom']) . ';';
        if (!empty($atts['right']))  $wrapper_style .= ' margin-right: ' . esc_attr($atts['right']) . ';';
        if (!empty($atts['left']))   $wrapper_style .= ' margin-left: ' . esc_attr($atts['left']) . ';';

        if (!empty($atts['margin'])) $wrapper_style .= ' margin: ' . esc_attr($atts['margin']) . ';';
    } else {
        $wrapper_style = 'padding: 0px; border: 0px solid #ccc;';
        $wrapper_style .= ' position: ' . esc_attr($atts['position']) . ';';
        $wrapper_style .= ' display: ' . esc_attr($atts['display']) . ';';
        if (!empty($atts['top']))    $wrapper_style .= ' top: ' . esc_attr($atts['top']) . ';';
        if (!empty($atts['bottom'])) $wrapper_style .= ' bottom: ' . esc_attr($atts['bottom']) . ';';
        if (!empty($atts['right']))  $wrapper_style .= ' right: ' . esc_attr($atts['right']) . ';';
        if (!empty($atts['left']))   $wrapper_style .= ' left: ' . esc_attr($atts['left']) . ';';
        if (!empty($atts['background'])) $wrapper_style .= ' background: ' . esc_attr($atts['background']) . ';';
        if (!empty($atts['text-color'])) $wrapper_style .= ' color: ' . esc_attr($atts['text-color']) . ';';

        if (!empty($atts['margin'])) $wrapper_style .= ' margin: ' . esc_attr($atts['margin']) . ';';
    }

    // Floating mode check
    $is_low = $atts['footer'] || $atts['drop-up'];


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
