<?php
function loco_user_guide() {
   echo'
    <div>
        <h1 style="font-weight:bold; font-size:2rem; margin-bottom:0.5rem; margin-top:4rem;">Plugin Translator</h1>
        <div style="width:95%; margin:0 auto;">

            <!-- Enable Language -->
           <section style="margin-top:1.5rem;">
    <h2 style="font-size:1.25rem; font-weight:600; color:#1f2937;">Enable Language</h2>
    <p style="margin-top:0.5rem; color:#374151;">
        In WordPress settings &gt; General, check "Site Language" and click to install the desired language. Any language your plugin is translated into must be installed so visitors can see the translated content.
    </p>
    <p style="margin-top:0.5rem; color:#374151;">
        <strong>Note:</strong> When selecting a language for Machine Translation, it is highly recommended to choose an <strong>ActiveLoc language</strong> for full compatibility. Some WordPress languages are not available for Machine Translation and ActiveLoc language switcher.

    </p>
</section>


            <!-- Machine Translation -->
            <section style="margin-top:2rem;">
                <h2 style="font-size:1.25rem; font-weight:600; color:#1f2937;">Machine Translation</h2>
                <ol style="list-style-type:decimal; padding-left:1.25rem; margin-top:0.75rem; color:#374151;">
                    <li style="margin-top:0.5rem;">
    <strong>Go to Home, Plugins, or Themes:</strong> Click on the corresponding submenu under ActiveLoc Plugin Translator.
</li>
                    <li style="margin-top:0.5rem;">
                        <strong>Create a new template:</strong> Click on your chosen plugin/theme and create a new translation template.
                    </li>
                    <li style="margin-top:0.5rem;">
                        <strong>Start translating:</strong> Once the template is created, click on <strong>“New Language”</strong> Then select your target language and choose the desired directory.
                    </li>
                    <li style="margin-top:0.5rem;">
                        Click <strong>“Start Translating”</strong>. When the new translation page opens, click on <strong>“Auto”</strong> and then on <strong>“ActiveLoc Translator.”</strong>
                    </li>
                </ol>
                <p style="margin-top:0.5rem; color:#374151;">
                  <strong>Note:</strong>ActiveLoc Languages are the WordPress compatable languages, choose WordPress 
                </p>
            </section>

            <!-- Machine Translation Post-Editing (MTPE) -->
            <section style="margin-top:2.5rem;">
                <h2 style="font-size:1.25rem; font-weight:600; color:#1f2937;">Machine Translation Post-Editing (MTPE)</h2>
                <ol style="list-style-type:decimal; padding-left:1.25rem; margin-top:0.75rem; color:#374151;">
                   <li style="margin-top:0.5rem;">
    <strong>Select plugin or theme and create template:</strong> Go to the <strong>Home, Plugins,</strong> or <strong>Themes</strong> submenu, select your plugin or theme, and then click <strong>“Create Template.”</strong>
</li>

                    <li style="margin-top:0.5rem;">
                        <strong>Plugins with templates:</strong> The list of plugins that already have templates will be displayed under <strong>“Plugins with templates”</strong> in the Plugin MTPE submenu.
                    </li>
                    <li style="margin-top:0.5rem;">
                        <strong>Select plugins and languages:</strong> You can choose multiple plugins and target languages for post-editing.
                    </li>
                    <li style="margin-top:0.5rem;">
                        <strong>Click “Upload”:</strong> Upload your selected plugin files for MTPE processing.
                    </li>
                    <li style="margin-top:0.5rem;">
                        <strong>After completion:</strong> Once MTPE is finished, the results will appear in the <strong>File List.</strong>
                    </li>
                    <li style="margin-top:0.5rem;">
    <strong>After import:</strong> Once imported, go to Home, Plugins, or Themes, click on the plugin or theme, and the requested languages should show with 100% completion.
</li>
                </ol>
            </section>

        </div>
    </div>
    ';
}
