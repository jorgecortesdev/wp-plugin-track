<?php
class WP_Plugin_Track_View {
    protected $view;
    protected $variables;
    protected $type;

    public function __construct($type) {
        $this->variables = array();
        $this->type = $type;
    }

    public function file($view) {
        $view = $view . '.php';
        $view = plugin_dir_path(__FILE__) . '../' . $this->type . '/views/' . $view;
        if (!file_exists($view)) {
            die('couldn\'t load view');
        }
        $this->view = $view;
    }

    public function set($name, $value) {
        $this->variables[$name] = $value;
    }

    public function render($echo = true) {
        foreach ($this->variables as $key => $value) {
            ${$key} = $value;
        }

        ob_start();
            include $this->view;
            $result = ob_get_contents();
        ob_end_clean();

        if ($echo) {
            echo $result;
        } else {
            return $result;
        }
    }
}