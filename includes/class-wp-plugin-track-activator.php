<?php

class WP_Plugin_Track_Activator {

    public static function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . "track";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            section_id int(10) NOT NULL DEFAULT '0',
            post_id int(10) NOT NULL,
            created int(10) unsigned NOT NULL,
            impressions int(10) unsigned NOT NULL DEFAULT '0',
            clicks int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY  (section_id, post_id, created)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}