<?php
function lang_dropdown()
{
    $languages = get_option('activeloc_languages', []);
    $languages = [
        'fr' => 'French',
        'es' => 'Spanish',
        'de' => 'German',
        'it' => 'Italian',
        'ru' => 'Russian',
        'zh' => 'Chinese',
        'kn' => 'Kannada'
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
