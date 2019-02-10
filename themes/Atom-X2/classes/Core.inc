<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-X2/classes/Core.inc
| Author: PHP-Fusion Inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace AtomX2Theme;

class Core {
    private static $options = [
        'right'                => TRUE,
        'right_content'        => '',
        'section_header'       => '',
        'section_header_class' => '',
        'mainbody_class'       => 'p-0 p-t-10'
    ];
    private static $instance = NULL;
    public $locale = [];
    public $userdata = [];
    public $settings = [];
    public $aidlink = '';

    public function __construct() {
        $this->locale = self::SetLocale();
        $this->locale += fusion_get_locale();
        $this->userdata = fusion_get_userdata();
        $this->settings = fusion_get_settings();
        $this->aidlink = fusion_get_aidlink();
    }

    public static function GetInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->SetLocale();
        }

        return self::$instance;
    }

    protected static function GetParam($name = NULL) {
        if (isset(self::$options[$name])) {
            return self::$options[$name];
        }

        return NULL;
    }

    public static function SetParam($name, $value) {
        self::$options[$name] = $value;
    }

    public function SetLocale() {
        if (empty($this->locale)) {
            $locale = [];

            if (file_exists(THEME.'locale/'.LANGUAGE.'.php')) {
                include THEME.'locale/'.LANGUAGE.'.php';
            } else {
                include THEME.'locale/English.php';
            }

            $this->locale = $locale;
        }

        return $this->locale;
    }
}
