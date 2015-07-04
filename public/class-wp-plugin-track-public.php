<?php
class WP_Plugin_Track_Public {
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
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp-plugin-track.js', array('jquery'), $this->version, false);

        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        wp_localize_script($this->plugin_name, 'wpp_plugin_track_ajax_object', array('ajax_url' => admin_url('admin-ajax.php', $protocol)));
    }

    public function process_impression() {
        global $wpdb;

        $result = false;

        $table_name = $wpdb->prefix . 'track';

        $posts = array();
        if (isset($_POST['posts'])) {
            $posts = $_POST['posts'];
        }

        if (is_array($posts)) {
            $date = date('Y-m-d 00:00:00');

            foreach ($posts as $post) {
                if (empty($post[0]) || empty($post[1])) {
                    continue;
                }
                $post_id    = $post[0];
                $section_id = $post[1];

                $sql = "INSERT INTO $table_name 
                        (section_id, post_id, created, impressions, clicks) VALUES (%d, %d, UNIX_TIMESTAMP('%s'), 1, 1)
                        ON DUPLICATE KEY UPDATE impressions=impressions+1";

                $sql = $wpdb->prepare($sql, $section_id, $post_id, $date);
                
                if ($wpdb->query($sql) !== false) {
                    $result = true;
                }
            }
        } 

        if ($result) {
            wp_send_json_success('ok');
        } else {
            wp_send_json_error(array('error' => 'error'));
        }
    }

    public function process_click() {
        global $wpdb;

        $result = false;

        $table_name = $wpdb->prefix . 'track';

        $post = array();
        if (isset($_POST['post'])) {
            $post = $_POST['post'];
        }

        if (is_array($post)) {
            $timestamp = strtotime(date('Y-m-d'));
            if (!empty($post[0]) && !empty($post[1])) {
                $post_id    = $post[0];
                $section_id = $post[1];

                $sql = "INSERT INTO $table_name 
                        (section_id, post_id, created, impressions, clicks) VALUES (%d, %d, %d, 1, 1)
                        ON DUPLICATE KEY UPDATE clicks=clicks+1";

                $sql = $wpdb->prepare($sql, $section_id, $post_id, $timestamp);
                if ($wpdb->query($sql) !== false) {
                    $result = true;
                }
            }
        } 

        if ($result) {
            wp_send_json_success('ok');
        } else {
            wp_send_json_error(array('error' => 'error'));
        }
    }
}
