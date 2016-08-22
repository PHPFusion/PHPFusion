<?php
namespace Nebula;

class NebulaTheme {

    private static $instance = NULL;

    private static $options = array(
        'header' => TRUE,
        'footer' => TRUE
    );

    /**
     * Generates Nebula Instance
     * @return object
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->defineThemeProp();
        }

        return (object)self::$instance;
    }

    public function defineThemeProp() {

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