<?php
namespace ThemeFactory;

class Core {

    private static $options = array(
        'header' => TRUE, // has header
        'breadcrumbs' => FALSE, // show breadcrumbs
        'footer' => TRUE, // has footer
        'container' => TRUE, // whether is a container or full grid

        'headerBg' => TRUE, // use header_background
        'header_content' => '', // content in the header
        'subheader_content' => '', // page title
        'right_pre_content' => '', // right side top content
        'right_post_content' => '', // right side bottom content
    );

    private static $instance = NULL;

    private function __construct() {
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        return (object)self::$instance;
    }

    public static function setParam($prop, $value) {
        self::$options[$prop] = (is_bool($value)) ? $value : self::getParam($prop).$value;
    }

    protected static function getParam($prop = FALSE) {
        if (isset(self::$options[$prop])) {
            return self::$options[$prop];
        }
        return NULL;
    }

    public function get_themePack($themePack) {
        $path = THEME."ThemePack/".$themePack."/Theme.php";
        $cssPath = THEME."ThemePack/".$themePack."/Styles.css";
        add_to_head("<link rel='stylesheet' href='$cssPath' type='text/css'/>");
        require_once $path;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }

}
