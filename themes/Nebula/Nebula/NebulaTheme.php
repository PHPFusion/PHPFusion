<?php
namespace Nebula;

class NebulaTheme {

    protected static $options = array(
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

    /**
     * Generates Nebula Instance
     * @return object
     */
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

    public function render_page($license = FALSE) {
        new Layouts\MainFrame();
    }

}