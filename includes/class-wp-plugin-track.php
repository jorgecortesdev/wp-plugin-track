<?php
class WP_Plugin_Track {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'wp-plugin-track';
        $this->version     = '1.0.0';

        $this->load_dependencies();
        $this->define_public_hooks();

        if (is_admin()) {
            $this->define_admin_hooks();
        }
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-plugin-track-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-plugin-track-view.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-plugin-track-public.php';

        if (is_admin()) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-plugin-track-admin.php';
        }

        $this->loader = new WP_Plugin_Track_Loader();
    }

    private function define_admin_hooks() {
        $plugin_admin = new WP_Plugin_Track_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'menu');
    }

    private function define_public_hooks() {
        $plugin_public = new WP_Plugin_Track_Public($this->get_plugin_name(), $this->get_version());

        // $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        $this->loader->add_action('wp_ajax_process_impression', $plugin_public, 'process_impression');
        $this->loader->add_action('wp_ajax_nopriv_process_impression', $plugin_public, 'process_impression');

        $this->loader->add_action('wp_ajax_process_click', $plugin_public, 'process_click');
        $this->loader->add_action('wp_ajax_nopriv_process_click', $plugin_public, 'process_click');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}
