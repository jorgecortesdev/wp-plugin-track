<?php
/**
 * Plugin Name: WP Plugin Track
 * Description: Add a CTR tracking per post
 * Plugin URI: http://www.dacure.com
 * Author: Jorge Cortes
 * Author URI: http://www.dacure.com
 * Version: 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

function activate_wp_plugin_track() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-plugin-track-activator.php';
    WP_Plugin_Track_Activator::activate();
}

function deactivate_wp_plugin_track() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-plugin-track-deactivator.php';
    WP_Plugin_Track_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_plugin_track');
register_deactivation_hook(__FILE__, 'deactivate_wp_plugin_track');

require plugin_dir_path(__FILE__) . 'includes/class-wp-plugin-track.php';

function run_wp_plugin_track() {
    $plugin = new WP_Plugin_Track();
    $plugin->run();
}

run_wp_plugin_track();