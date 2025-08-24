<?php
function activeloc_render_mtpe_list_page()
{
    echo '<div class="wrap">
        <h1>MTPE Completed List</h1>';

    $mtpe_files = list_user_folder_contents();

    if (isset($mtpe_files['error'])) {
        echo '<div class="notice notice-error">
            <p>' . esc_html($mtpe_files['error']) . '</p>
        </div>';
    } elseif (!empty($mtpe_files['files'])) {
        echo '<table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Folder Name</th>
                    <th>Download</th>
                    <th>Import</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($mtpe_files['files'] as $item) {
            // Folder row
            echo '<tr class="folder-row" data-folder="' . esc_attr($item) . '">
                <td>' . esc_html($item) . '</td>
                <td><a class="button" href="' . esc_url(admin_url('admin-post.php?action=mtpe_download_folder&folder_name=' . urlencode($item))) . '">View Folder</a></td>
                <td>
                    <a class="button" href="' . esc_url(admin_url('admin-post.php?action=mtpe_import_folder_draft&folder_name=' . urlencode($item))) . '">Import as Draft</a>
                    <a class="button" href="' . esc_url(admin_url('admin-post.php?action=mtpe_import_folder_publish&folder_name=' . urlencode($item))) . '">Import & Publish</a>
                </td>
                <td>
                    <button class="button view-status" data-folder="' . esc_attr($item) . '">View Status</button>
                    <button class="button toggle-folder">Show Files</button>
                    <a class="button" href="' . esc_url(admin_url('admin-post.php?action=mtpe_view_file_list&folder_name=' . urlencode($item))) . '">FileList Page</a>
                </td>
            </tr>';

            // Hidden row for folder files
            echo '<tr class="folder-files-row" style="display:none;">
                <td colspan="4" class="folder-files-container"></td>
            </tr>';

            // Hidden row for status content
            echo '<tr class="folder-status-row" style="display:none;">
                <td colspan="4" class="folder-status-container"></td>
            </tr>';
        }

        echo '</tbody>
        </table>';
    } else {
        echo '<p>No folders found.</p>';
    }

    // Inline JS for toggling files and status
    echo '<script type="text/javascript">
    jQuery(document).ready(function($) {

        // Toggle folder files
        $(".toggle-folder").on("click", function() {
            var $btn = $(this);
            var $row = $btn.closest("tr");
            var folder = $row.data("folder");
            var $filesRow = $row.nextAll(".folder-files-row").first();
            var $container = $filesRow.find(".folder-files-container");

            if ($filesRow.is(":visible")) {
                $filesRow.slideUp();
                return;
            }

            if (!$container.data("loaded")) {
                $.post(ajaxurl, {
                    action: "mtpe_get_folder_files",
                    folder_name: folder
                }, function(response) {
                    if (response.success) {
                        var html = "<div class=\'folder-files-panel\' style=\'padding:10px; background:#f9f9f9; max-width:300px; box-shadow:0 2px 5px rgba(0,0,0,0.1);\'>";
                        html += "<strong>Files:</strong><ul style=\'list-style-type: disc; margin-left: 20px; padding-left:0;\'>";
                        response.data.files.forEach(function(f) {
                            html += "<li style=\'margin-bottom:5px;\'>" + f + "</li>";
                        });
                        html += "</ul></div>";
                        $container.html(html).data("loaded", true);
                    } else {
                        $container.html("<p>Error loading files</p>");
                    }
                    $filesRow.slideDown();
                });
            } else {
                $filesRow.slideDown();
            }
        });

        // View status content
        $(".view-status").on("click", function() {
            var $btn = $(this);
            var $row = $btn.closest("tr");
            var folder = $btn.data("folder");
            var $statusRow = $row.nextAll(".folder-status-row").first();
            var $container = $statusRow.find(".folder-status-container");

            if ($statusRow.is(":visible")) {
                $statusRow.slideUp();
                return;
            }

            if (!$container.data("loaded")) {
                $.post(ajaxurl, {
                    action: "mtpe_get_status_file",
                    folder_name: folder
                }, function(response) {
                    if (response.success) {
                        $container.html("<pre style=\'background:#f4f4f4; padding:10px; white-space: pre-wrap;\'>" + response.data.status + "</pre>").data("loaded", true);
                    } else {
                        $container.html("<p>Error loading status</p>");
                    }
                    $statusRow.slideDown();
                });
            } else {
                $statusRow.slideDown();
            }
        });

    });
    </script>';

    echo '</div>';
}
