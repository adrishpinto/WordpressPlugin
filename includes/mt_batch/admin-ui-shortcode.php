<?php
// short-code-page.php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

function activeloc_render_short_code_page()
{
    $languages = [
        'af'      => 'Afrikaans',
        'sq'      => 'Albanian',
        'ar'      => 'Arabic',
        'az'      => 'Azerbaijani',
        'ba'      => 'Bashkir',
        'eu'      => 'Basque',
        'bs'      => 'Bosnian',
        'bg'      => 'Bulgarian',
        'yue'     => 'Cantonese',
        'ca'      => 'Catalan',
        'lzh'     => 'Classical Chinese',
        'zh-Hans' => 'Chinese (Simplified)',
        'zh-Hant' => 'Chinese (Traditional)',
        'hr'      => 'Croatian',
        'cs'      => 'Czech',
        'da'      => 'Danish',
        'nl'      => 'Dutch',
        'en'      => 'English',
        'et'      => 'Estonian',
        'fo'      => 'Faroese',
        'fj'      => 'Fijian',
        'fil'     => 'Filipino',
        'fi'      => 'Finnish',
        'fr'      => 'French',
        'fr-ca'   => 'French (Canada)',
        'gl'      => 'Galician',
        'de'      => 'German',
        'ht'      => 'Haitian Creole',
        'hi'      => 'Hindi',
        'mww'     => 'Hmong Daw',
        'hu'      => 'Hungarian',
        'is'      => 'Icelandic',
        'id'      => 'Indonesian',
        'ia'      => 'Interlingua',
        'ikt'     => 'Inuinnaqtun',
        'iu-Latn' => 'Inuktitut (Latin)',
        'ga'      => 'Irish',
        'it'      => 'Italian',
        'ja'      => 'Japanese',
        'kn'      => 'Kannada',
        'kk'      => 'Kazakh',
        'kk-cyrl' => 'Kazakh (Cyrillic)',
        'kk-latn' => 'Kazakh (Latin)',
        'ko'      => 'Korean',
        'ku-latn' => 'Kurdish (Latin)',
        'kmr'     => 'Kurdish (Northern)',
        'ky'      => 'Kyrgyz',
        'lv'      => 'Latvian',
        'lt'      => 'Lithuanian',
        'mk'      => 'Macedonian',
        'mg'      => 'Malagasy',
        'ms'      => 'Malay',
        'ml'      => 'Malayalam',
        'mt'      => 'Maltese',
        'mi'      => 'Maori',
        'mr'      => 'Marathi',
        'mn-Cyrl' => 'Mongolian (Cyrillic)',
        'ne'      => 'Nepali',
        'nb'      => 'Norwegian (Bokmål)',
        'pl'      => 'Polish',
        'pt'      => 'Portuguese',
        'pt-br'   => 'Portuguese (Brazil)',
        'pt-pt'   => 'Portuguese (Portugal)',
        'pa'      => 'Punjabi',
        'otq'     => 'Querétaro Otomi',
        'ro'      => 'Romanian',
        'ru'      => 'Russian',
        'sm'      => 'Samoan',
        'sr-Cyrl' => 'Serbian (Cyrillic)',
        'sr'      => 'Serbian',
        'sr-latn' => 'Serbian (Latin)',
        'sk'      => 'Slovak',
        'sl'      => 'Slovenian',
        'so'      => 'Somali',
        'es'      => 'Spanish',
        'sw'      => 'Swahili',
        'sv'      => 'Swedish',
        'ty'      => 'Tahitian',
        'ta'      => 'Tamil',
        'tt'      => 'Tatar',
        'te'      => 'Telugu',
        'to'      => 'Tongan',
        'tr'      => 'Turkish',
        'tk'      => 'Turkmen',
        'uk'      => 'Ukrainian',
        'hsb'     => 'Upper Sorbian',
        'uz'      => 'Uzbek',
        'vi'      => 'Vietnamese',
        'cy'      => 'Welsh',
        'yua'     => 'Yucatec Maya',
        'zu'      => 'Zulu'
    ];

    if (isset($_POST['activeloc_languages']) && check_admin_referer('save_activeloc_languages')) {
        $selected = array_map('sanitize_text_field', $_POST['activeloc_languages']);
        update_option('activeloc_languages', $selected);
        echo '<div class="updated"><p>Active languages saved.</p></div>';
    }
    $active_languages = get_option('activeloc_languages', []);
?>
    <div class="wrap">
        <h1>Short Code Page</h1>
        <form method="post">
            <?php wp_nonce_field('save_activeloc_languages'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Select Active Languages</th>
                    <td>
                        <div class="language-checkboxes">
                            <?php foreach ($languages as $code => $label) : ?>
                                <label>
                                    <input type="checkbox" name="activeloc_languages[]" value="<?php echo esc_attr($code); ?>"
                                        <?php checked(in_array($code, $active_languages)); ?> />
                                    <?php echo esc_html($label); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </td>

                </tr>
            </table>
            <?php submit_button('Save Languages'); ?>
        </form>

        <!-- Shortcode Documentation -->
        <div class="postbox" style="margin-top:25px; padding:20px;">
            <h2 style="margin-bottom:10px;">How to Use the Shortcode</h2>
            <p>
                The ActiveLoc Language Switcher can be added anywhere on your site using the shortcode:
            </p>
            <pre><code>[activeloc_lang_switcher]</code></pre>
            <br>
            <h2>Select Active Languages</h2>
            <ul style="list-style: disc; padding-left: 20px; margin: 10px 0;">
                <li>Chosen languages appear in the flag dropdown that users interact with.</li>
                <li>English is always included as a default fallback language.</li>
                <li>Language selection is persistent: if a translation is missing for a page, the previously selected language remains active and the corresponding flag stays displayed.</li>
            </ul>
            <br>
            <h3>Basic Use</h3>
            <ul>
                <li><strong>Navbar:</strong> <code>[activeloc_lang_switcher navbar="true"]</code> — Inline placement for navigation menus.</li>
                <li><strong>Footer:</strong> <code>[activeloc_lang_switcher footer="true"]</code> — Inline placement at the bottom of the page.</li>
                <li><strong>Centering Vertically:</strong> <code>[activeloc_lang_switcher navbar="true" top="10px"]</code> — Can use this for footer.</li>
                <li><strong>Centering Horizontally:</strong> <code>[activeloc_lang_switcher navbar="true" left="10px"]</code> — Can use this for footer.</li>
            </ul>
            <br>
            <h3>Notes</h3>
            <ul style="list-style: disc; padding-left: 20px; margin: 10px 0;">
                <li>If <code>navbar</code> and <code>footer</code> do not work for your layout, use the floating attributes below to position the switcher manually. Some values below can also be used with <code>footer</code> and <code>navbar</code>.</li>
                <li>It’s best to place this shortcode in the Navbar or Footer, as these appear on most pages. Otherwise, it must be manually added to the pages where it’s needed.</li>
            </ul>

            <br><br>
            <h3>Examples</h3>
            <pre><code>
[activeloc_lang_switcher]  <br>
[activeloc_lang_switcher bottom="20px" left="20px" drop-up="true"]   <br>
[activeloc_lang_switcher background="#333" text_color="#fff" margin="10px 5px"]   <br>
[activeloc_lang_switcher navbar="true" display="inline-block"]   <br>
[activeloc_lang_switcher position="absolute" right="50px" top="100px" footer="true"] <br>

</code></pre>

            <h3>Available Attributes</h3>
            <table class="widefat striped" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th style="width:150px;">Attribute</th>
                        <th style="width:150px;">Default</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>navbar</code></td>
                        <td><code>false</code></td>
                        <td>If set to <code>true</code>, the switcher is inline (ideal for placing inside navigation menus). If <code>false</code>, it floats on the page.</td>
                    </tr>
                    <tr>
                        <td><code>footer</code></td>
                        <td><code>false</code></td>
                        <td>If set to <code>true</code>, the switcher is inline at the bottom of the page (similar to navbar behavior).</td>
                    </tr>
                    <tr>
                        <td><code>position</code></td>
                        <td><code>fixed</code></td>
                        <td>Controls how the floating switcher is positioned (<code>fixed</code>, <code>absolute</code>, or <code>relative</code>).</td>
                    </tr>
                    <tr>
                        <td><code>display</code></td>
                        <td><code>inline-block</code></td>
                        <td>CSS display mode. Can be changed to <code>block</code>, <code>flex</code>, etc. Useful when embedding in layouts.</td>
                    </tr>
                    <tr>
                        <td><code>background</code></td>
                        <td>(empty)</td>
                        <td>Background color (e.g. <code>#fff</code>, <code>#333</code>, <code>lightgray</code>, <code>rgba(0,0,0,0.5)</code>).</td>
                    </tr>
                    <tr>
                        <td><code>text_color</code></td>
                        <td><code>black</code></td>
                        <td>Text color for the language names inside the switcher.</td>
                    </tr>
                    <tr>
                        <td><code>top</code></td>
                        <td><code>10px</code></td>
                        <td>Distance from the top of the screen (applies only in floating mode).</td>
                    </tr>
                    <tr>
                        <td><code>bottom</code></td>
                        <td>(empty)</td>
                        <td>Distance from the bottom of the screen (applies only in floating mode). Overrides <code>top</code> if set.</td>
                    </tr>
                    <tr>
                        <td><code>right</code></td>
                        <td><code>10px</code></td>
                        <td>Distance from the right side of the screen (applies only in floating mode).</td>
                    </tr>
                    <tr>
                        <td><code>left</code></td>
                        <td>(empty)</td>
                        <td>Distance from the left side of the screen (applies only in floating mode). Overrides <code>right</code> if set.</td>
                    </tr>
                    <tr>
                        <td><code>margin</code></td>
                        <td>(empty)</td>
                        <td>Shorthand to set margins when <code>navbar</code> or <code>footer</code> is used. Example: <code>margin="10px 20px"</code>.</td>
                    </tr>
                    <tr>
                        <td><code>drop-up</code></td>
                        <td>(empty)</td>
                        <td>If set, flags and languages will display upwards</td>
                    </tr>
                </tbody>
            </table>

            <br>
            <h3>Behavior</h3>
            <ul style="list-style: disc; padding-left: 20px; margin: 10px 0;">
                <li>If a translation exists, untranslated links will automatically redirect to the corresponding translated page.</li>
                <li>The current language is always shown with a flag and name.</li>
                <li>Clicking it opens a dropdown with other available languages.</li>
                <li>A cookie saves the user's language choice.</li>
                <li>The page redirects to the selected language version if available.</li>
                <li>English (<code>en</code>) is always included as a fallback.</li>
            </ul>
        <?php
    }
