<?php

use Gettext\Scanner\PhpScanner;
use Gettext\Translations;
use Gettext\Generator\PoGenerator;

function scan_pdf_ac(string $domain, string $folderPath): \Gettext\Translations
{
    $source = \Gettext\Translations::create($domain);
    $phpScanner = new \Gettext\Scanner\PhpScanner($source);
    $phpScanner->setDefaultDomain($domain);
    $phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');

    if (!is_dir($folderPath)) {
        throw new \InvalidArgumentException("Folder does not exist: $folderPath");
    }

    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folderPath));

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            try {
                $phpScanner->scanFile($file->getPathname());
                error_log("[scan_pdf_ac] Scanned file: " . $file->getPathname());
            } catch (\Exception $e) {
                error_log("[scan_pdf_ac] Skipped file (could not scan): " . $file->getPathname() . " | " . $e->getMessage());
            }
        }
    }

    // Log all extracted strings
    foreach ($source as $translation) {
        error_log("[scan_pdf_ac] Extracted string: " . $translation->getOriginal());
    }

    return $source;
}

function translate_source($source, $locale = 'fr_FR')
{
    $lang = explode('_', $locale)[0];

    $target = [];
    foreach ($source as $translation) {
        $original = $translation->getOriginal();
        $translated = activeloc_translate_title($original, $lang);
        error_log("translate_source():" . $translated);
        $target[$original] = $translated;
    }

    return $target;
}




use Gettext\Generator\MoGenerator;

function save_translations($source, $target, $domain, $locale = 'fr_FR'): array
{
    $langDir = WP_CONTENT_DIR . "/languages/plugins";
    if (!is_dir($langDir)) {
        mkdir($langDir, 0755, true);
    }

    $poPath = "{$langDir}/{$domain}-{$locale}.po";
    $moPath = "{$langDir}/{$domain}-{$locale}.mo";

    // Apply translations from $target to $source
    foreach ($source as $translation) {
        $original = $translation->getOriginal();
        if (isset($target[$original])) {
            $translation->translate($target[$original]);
        }
    }

    // Save .po
    $poGenerator = new PoGenerator();
    $poGenerator->generateFile($source, $poPath);

    // Save .mo
    $moGenerator = new MoGenerator();
    $moGenerator->generateFile($source, $moPath);

    return ['po' => $poPath, 'mo' => $moPath];
}




function activeloc_render_string_translator_page()
{
    echo '<h1>String Translator</h1>';

    if (!current_user_can('edit_pages')) {
        echo '<p>You do not have permission to access this page.</p>';
        return;
    }

    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins');
    $theme = wp_get_theme();

    // --- Form ---
    echo '<form method="post">';
    echo '<h2>Installed Plugins / Theme</h2>';
    echo '<label for="translation_domain">Select plugin or theme:</label><br>';
    echo '<select name="translation_domain" id="translation_domain">';

    // Plugins
    foreach ($all_plugins as $path => $plugin) {
        $status = in_array($path, $active_plugins) ? 'Active' : 'Inactive';
        $domain = isset($plugin['TextDomain']) ? $plugin['TextDomain'] : $path;
        $fullPath = WP_PLUGIN_DIR . '/' . $path;
        echo "<option value='plugin|{$fullPath}|{$domain}'>{$plugin['Name']} ({$status})</option>";
    }

    // Theme
    $theme_domain = $theme->get('TextDomain') ?: $theme->get_stylesheet();
    echo "<option value='theme|{$theme->get_stylesheet()}|{$theme_domain}'>{$theme->get('Name')} (Theme)</option>";

    echo '</select><br><br>';

    // Locale selection
    $locales = ['en_US', 'fr_FR', 'de_DE', 'es_ES'];
    echo '<label for="locale">Select target locale:</label><br>';
    echo '<select name="locale" id="locale">';
    foreach ($locales as $locale) {
        echo "<option value='{$locale}'>{$locale}</option>";
    }
    echo '</select><br><br>';

    // Buttons
    echo '<input type="submit" name="scan_strings" value="Scan Strings"> ';
    echo '<input type="submit" name="auto_translate" value="Auto-Translate"> ';
    echo '<input type="submit" name="save_translations" value="Save">';
    echo '</form><hr>';

    // --- Actions ---
    if (isset($_POST['scan_strings'])) {
        list($type, $path, $domain) = explode('|', $_POST['translation_domain']);
        $locale = $_POST['locale'] ?? 'fr_FR';
        $fullPath = ($type === 'plugin') ? dirname($path) : get_theme_root() . '/' . $path;

        $source = scan_pdf_ac($domain, $fullPath);
        $count = count($source);
        echo "<p><strong>{$count}</strong> strings scanned and stored. You can now Auto-Translate or edit manually.</p>";

        update_option('activeloc_source', serialize($source));
        update_option('activeloc_domain', $domain);
        update_option('activeloc_locale', $locale);

        echo '<p>Strings scanned and stored. You can now Auto-Translate or edit manually.</p>';
    }

    if (isset($_POST['auto_translate'])) {
        $source = unserialize(get_option('activeloc_source'));
        $locale = get_option('activeloc_locale', 'fr_FR');
        $target = translate_source($source, $locale);
        update_option('activeloc_target', $target);

        echo '<p>Strings auto-translated. You can now Save them.</p>';
    }
    // --- Show editable table if source exists ---
    $source = get_option('activeloc_source') ? unserialize(get_option('activeloc_source')) : null;
    $target = get_option('activeloc_target', []);

    if ($source) {
        // Convert $source (Gettext\Translations object) to array for sorting
        $sourceArray = iterator_to_array($source);

        // Sort alphabetically by original string
        usort($sourceArray, function ($a, $b) {
            return strcmp($a->getOriginal(), $b->getOriginal());
        });

        echo '<h2>Scanned Strings</h2>';
        echo '<form method="post">';
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>Original</th><th>Translation</th></tr></thead><tbody>';

        foreach ($sourceArray as $translation) {
            $original = esc_html($translation->getOriginal());
            $current = isset($target[$original]) ? esc_attr($target[$original]) : '';
            echo "<tr>
                <td style='width:50%;'>{$original}</td>
                <td><input type='text' name='translations[" . esc_attr($original) . "]' value='{$current}' style='width:100%;'></td>
              </tr>";
        }

        echo '</tbody></table><br>';
        echo '<input type="submit" name="save_translations" class="button button-primary" value="Save Translations">';
        echo '</form>';
    }


    if (isset($_POST['save_translations'])) {
        $source = unserialize(get_option('activeloc_source'));
        $target = get_option('activeloc_target', []);
        $domain = get_option('activeloc_domain', 'default');
        $locale = get_option('activeloc_locale', 'fr_FR');

        $files = save_translations($source, $target, $domain, $locale);
        echo "<p>PO file saved to:<br><code>{$files['po']}</code></p>";
        echo "<p>MO file saved to:<br><code>{$files['mo']}</code></p>";
    }
}
