<?php
namespace Nebula;

class NebulaTheme {

    protected static $options = array(
        'header' => TRUE,
        'footer' => TRUE,
        'boxed_content' => TRUE,
        'headerBg' => TRUE,
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
        self::$options[$prop] = $value;
    }

    public static function getParam($prop = FALSE) {
        if (isset(self::$options[$prop])) {
            return self::$options[$prop];
        }
        return NULL;
    }

    public function render_page($license = FALSE) {
        new Layouts\MainFrame();
    }

}