<?php
function get_unique_title($title)
{
    global $wpdb;

    $suffix = 2;
    $new_title = $title;

    do {
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts
WHERE post_title = %s
AND post_status IN ('publish', 'draft', 'pending', 'future', 'private')
LIMIT 1",
            $new_title
        ));

        if ($existing) {
            $new_title = $title . " ($suffix)";
            $suffix++;
        }
    } while ($existing);

    return $new_title;
}
