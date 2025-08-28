<?php
function lang_dropdown()
{
    $languages = get_option('activeloc_languages', []);
    $languages = [
        'af' => 'Afrikaans',
        'sq' => 'Albanian',
        'ar' => 'Arabic',
        'az' => 'Azerbaijani (Latin)',
        'ba' => 'Bashkir',
        'eu' => 'Basque',
        'bs' => 'Bosnian (Latin)',
        'bg' => 'Bulgarian',
        'yue' => 'Cantonese (Traditional)',
        'ca' => 'Catalan',
        'lzh' => 'Chinese (Literary)',
        'zh-Hans' => 'Chinese Simplified',
        'zh-Hant' => 'Chinese Traditional',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'da' => 'Danish',
        'nl' => 'Dutch',
        'en' => 'English',
        'et' => 'Estonian',
        'fo' => 'Faroese',
        'fj' => 'Fijian',
        'fil' => 'Filipino',
        'fi' => 'Finnish',
        'fr' => 'French',
        'fr-ca' => 'French (Canada)',
        'gl' => 'Galician',
        'de' => 'German',
        'ht' => 'Haitian Creole',
        'hi' => 'Hindi',
        'mww' => 'Hmong Daw (Latin)',
        'hu' => 'Hungarian',
        'is' => 'Icelandic',
        'id' => 'Indonesian',
        'ia' => 'Interlingua',
        'ikt' => 'Inuinnaqtun',
        'iu-Latn' => 'Inuktitut (Latin)',
        'ga' => 'Irish',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'kn' => 'Kannada',
        'kk' => 'Kazakh (Cyrillic)',
        'kk-cyrl' => 'Kazakh (Cyrillic)',
        'kk-latn' => 'Kazakh (Latin)',
        'ko' => 'Korean',
        'ku-latn' => 'Kurdish (Latin) (Northern)',
        'kmr' => 'Kurdish (Latin) (Northern)',
        'ky' => 'Kyrgyz (Cyrillic)',
        'lv' => 'Latvian',
        'lt' => 'Lithuanian',
        'mk' => 'Macedonian',
        'mg' => 'Malagasy',
        'ms' => 'Malay (Latin)',
        'ml' => 'Malayalam',
        'mt' => 'Maltese',
        'mi' => 'Maori',
        'mr' => 'Marathi',
        'mn-Cyrl' => 'Mongolian (Cyrillic)',
        'ne' => 'Nepali',
        'nb' => 'Norwegian Bokmål',
        'pl' => 'Polish',
        'pt' => 'Portuguese (Brazil)',
        'pt-br' => 'Portuguese (Brazil)',
        'pt-pt' => 'Portuguese (Portugal)',
        'pa' => 'Punjabi',
        'otq' => 'Queretaro Otomi',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sm' => 'Samoan (Latin)',
        'sr-Cyrl' => 'Serbian (Cyrillic)',
        'sr' => 'Serbian (Latin)',
        'sr-latn' => 'Serbian (Latin)',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'so' => 'Somali',
        'es' => 'Spanish',
        'sw' => 'Swahili (Latin)',
        'sv' => 'Swedish',
        'ty' => 'Tahitian',
        'ta' => 'Tamil',
        'tt' => 'Tatar (Latin)',
        'te' => 'Telugu',
        'to' => 'Tongan',
        'tr' => 'Turkish',
        'tk' => 'Turkmen (Latin)',
        'uk' => 'Ukrainian',
        'hsb' => 'Upper Sorbian',
        'uz' => 'Uzbek (Latin)',
        'vi' => 'Vietnamese',
        'cy' => 'Welsh',
        'yua' => 'Yucatec Maya',
        'zu' => 'Zulu'
    ];

?>
    <p>
        <label for="activeloc_target_langs"><strong>Select Target Languages:</strong></label><br>
        <select name="activeloc_target_langs[]" id="activeloc_target_langs" multiple="multiple" style="width: 300px;">
            <?php foreach ($languages as $code => $label): ?>
                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </p>

    <script>
        jQuery(document).ready(function($) {
            $('#activeloc_target_langs').select2({
                placeholder: "Choose languages",
                width: 'resolve'
            });
        });
    </script>
<?php
}
