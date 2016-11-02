<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Core.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace ThemeFactory;

class Core {
    /*
     * readme.md
     */
    private static $options = array(
        'header' => TRUE, // has header
        'header_content' => '', // content in the header
        'headerBg' => TRUE, // use header_background
        'headerBg_class' => '', // use custom header background class
        'subheader_content' => '',
        'subheader_container' => TRUE,
        'body_class' => '', // set body section class
        'body_container' => TRUE, // whether is a container or full grid
        'breadcrumbs' => FALSE, // show breadcrumbs
        'right' => TRUE, // RIGHT
        'left' => TRUE, // LEFT
        'upper' => TRUE, //AU_UPPER
        'upper_container' => TRUE,
        'body_upper' => TRUE, // U_CENTER
        'body_upper_container' => TRUE,
        'lower' => TRUE, // BL_LOWER
        'lower_container' => TRUE,
        'body_lower' => TRUE, // L_CENTER
        'body_lower_container' => TRUE,
        'footer' => TRUE, // has footer
        'footer_container' => TRUE,
        'copyright' => TRUE,
        'copyright_container' => TRUE,
        'right_span' => 3,
        'right_class' => '',
        'main_span' => '',
        'right_is_affix' => FALSE, // @todo: auto affix
        'right_pre_content' => '', // right side top content
        'right_post_content' => '', // right side bottom content
    );

    private static $instance = NULL;
    private static $module_instance = NULL;
    private static $module_list = array();

    private function __construct() {
        if (empty(self::$module_list)) {
            // Get Theme Factory Modules
            $ModuleType = makefilelist(THEME."ThemeFactory/Lib/Modules", ".|..|.htaccess|index.php|._DS_STORE|.tmp", "folder");
            if (!empty($ModuleType)) {
                foreach ($ModuleType as $ModuleFolder) {
                    $Modules = makefilelist(THEME."ThemeFactory/Lib/Modules/$ModuleFolder", ".|..|.htaccess|index.php|._DS_STORE|.tmp");
                    if (!empty($Modules)) {
                        foreach ($Modules as $ModuleFile) {
                            self::$module_list[] = "$ModuleFolder\\".str_replace('.php', '', $ModuleFile);
                        }
                    }
                }
            }
        }
    }

    public static function replaceParam($prop, $value) {
        self::$options[$prop] = $value;
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function setParam($prop, $value) {
        self::$options[$prop] = (is_bool($value)) ? $value : self::getParam($prop).$value;
    }

    protected static function getParam($prop = FALSE) {
        if (isset(self::$options[$prop])) { // will return an error if $prop is not available
            return self::$options[$prop];
        } else {
            print_p($prop);
            debug_print_backtrace();
        }

        return NULL;
    }

    public function get_themePack($themePack) {
        $path = THEME."ThemePack/".$themePack."/Theme.php";
        $cssPath = THEME."ThemePack/".$themePack."/Styles.css";
        add_to_head("<link rel='stylesheet' href='$cssPath' type='text/css'/>");
        require_once $path;
    }


    /**
     * @param string $modules
     * @return mixed
     */
    protected function get_Modules($modules = 'Footer\\News') {
        if (!isset(self::$module_instance[$modules]) or self::$module_instance[$modules] === NULL) {
            if (!empty(self::$module_list)) {
                $module_ = array_flip(self::$module_list);
                if (isset($module_[$modules])) {
                    $namespace_ = "ThemeFactory\\Lib\\Modules\\";
                    $module_ = new \ReflectionClass($namespace_.$modules);
                    self::$module_instance[$modules] = $module_->newInstance();
                }
            }
        }

        return self::$module_instance[$modules];
    }

    private function __clone() {
    }

    private function __wakeup() {
    }

}