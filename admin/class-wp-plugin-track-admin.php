<?php
class WP_Plugin_Track_Admin {
    private $plugin_name;
    private $version;
    private $view;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->view = new WP_Plugin_Track_View(basename(plugin_dir_path(__FILE__)));
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-plugin-track.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_register_script('highcharts', 'http://code.highcharts.com/highcharts.js', array('jquery'), $this->version, true); 
        wp_enqueue_script('highcharts');

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp-plugin-track.js', array('jquery', 'highcharts'), $this->version, false);
    }

    public function menu() {
        add_menu_page('WP Plugin Track', 'WPP Track', 'manage_options', 'wp-plugin-track', array($this, 'dashboard'));
    }

    public function dashboard() {
        $this->view->file('page/wp-plugin-track-dashboard');

        add_meta_box('wpp_track_ctr_meta_box', 'Current CTR', array($this, 'metabox_ctr'), 'wppt_overview', 'left', 'core');
        add_meta_box('wpp_track_report_meta_box', 'Current CTR', array($this, 'metabox_report'), 'wppt_overview', 'left', 'core');

        $this->view->render();
    }

    public function metabox_ctr() {
        $this->view->file('metabox/wp-plugin-track-ctr');

        $data = $this->stats_points_per_day();
        $this->view->set('categories', implode(', ', array_keys($data)));
        $this->view->set('series', implode(', ', array_values($data)));
        $this->view->render();
    }

    public function metabox_report() {
        $this->view->file('metabox/wp-plugin-track-report');

        global $wpdb;
        $table_name = $wpdb->prefix . 'track';

        $posts = $wpdb->get_results("SELECT *, (clicks / impressions) AS ctr
            FROM (
                SELECT
                    section_id, post_id, post_title, SUM(impressions) AS impressions, SUM(clicks) AS clicks
                FROM $table_name
                JOIN {$wpdb->prefix}posts ON ID = post_id
                WHERE
                    FROM_UNIXTIME(created) >= NOW() - INTERVAL 1 MONTH
                GROUP BY section_id, post_id
            ) c
            ORDER BY ctr DESC;");

        if (!$posts) {
            $posts = array();
        }

        $sparklines = $this->stats_points_per_day_and_post();

        $this->view->set('sparklines', $sparklines);
        $this->view->set('posts', $posts);
        $this->view->render();
    }

    private function stats_points_per_day($group_by_post = false) {
        $one_mont_ago = date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . "-1 month"));
        $today = date("Y-m-d");

        $range = $this->create_date_range($one_mont_ago, $today);

        global $wpdb;

        $table_name = $wpdb->prefix . 'track';
        $sql = "SELECT *, (clicks / impressions) AS ctr
            FROM (
                SELECT
                    SUM(impressions) AS impressions, SUM(clicks) AS clicks, FROM_UNIXTIME(created, '%Y-%m-%d') AS created
                FROM $table_name
                WHERE
                    FROM_UNIXTIME(created) >= NOW() - INTERVAL 1 MONTH
                GROUP BY created
            ) c;";

        $stats = $wpdb->get_results($sql);
        if (!$stats) {
            $stats = array();
        }
        foreach ($stats as $stat) {
            $range[$stat->created] = number_format($stat->ctr * 100, 2);
        }
        return $range;
    }

    private function stats_points_per_day_and_post() {
        $one_mont_ago = date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . "-1 month"));
        $today = date("Y-m-d");

        $range = $this->create_date_range($one_mont_ago, $today);

        global $wpdb;

        $table_name = $wpdb->prefix . 'track';

        $results = array();

        $sql = "SELECT *, (clicks / impressions) AS ctr
            FROM (
                SELECT
                    section_id, post_id, post_title, SUM(impressions) AS impressions, SUM(clicks) AS clicks, FROM_UNIXTIME(created) AS created
                FROM wp_track
                JOIN wp_posts ON ID = post_id
                WHERE
                    FROM_UNIXTIME(created) >= NOW() - INTERVAL 1 MONTH
                GROUP BY section_id, created, post_id
            ) c;";

        $stats = $wpdb->get_results($sql);

        if (!$stats) {
            $stats = array();
        }

        foreach ($stats as $stat) {
            if (!isset($results[$stat->section_id][$stat->post_id])) {
                $results[$stat->section_id][$stat->post_id] = $range;
            }
            $results[$stat->section_id][$stat->post_id][$stat->created] = number_format($stat->ctr * 100, 2);
        }

        return $results;
    }

    private function create_date_range($date_from, $date_to) {
        $range = array();

        $timestamp_date_from = mktime(1, 0, 0, substr($date_from, 5, 2), substr($date_from, 8, 2), substr($date_from, 0, 4));
        $timestamp_date_to = mktime(1, 0, 0, substr($date_to, 5, 2), substr($date_to, 8, 2), substr($date_to, 0, 4));

        if ($timestamp_date_to >= $timestamp_date_from) {
            $range[date('Y-m-d', $timestamp_date_from)] = 0;
            while ($timestamp_date_from < $timestamp_date_to) {
                $timestamp_date_from += 86400; // add 24 hours
                $range[date('Y-m-d', $timestamp_date_from)] = 0;
            }
        }
        return $range;
    }
}