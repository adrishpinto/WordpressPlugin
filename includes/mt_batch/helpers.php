<?php
function activeloc_enqueue_assets() {
    wp_enqueue_style('select2css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
}

function activeloc_register_debug_menu() {
    add_menu_page(
        'ActiveLoc Debug Page',
        'ActiveLoc Debug',
        'manage_options',
        'activeloc-debug',
        'activeloc_render_debug_page',
        'dashicons-search',
        99
    );
}

function activeloc_search_title_only($where) {
    global $wpdb;
    if (!empty($_POST['search_term'])) {
        $search = esc_sql($_POST['search_term']);
        $where .= " AND {$wpdb->posts}.post_title LIKE '%{$search}%'";
    }
    return $where;
}
