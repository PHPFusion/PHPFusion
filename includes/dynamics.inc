<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Dynamics Field Inputs
| Filename: dynamics.inc
| Author: Frederick MC Chan (Chan)
| Version: 9.0
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Class Dynamics
 *
 * @package PHPFusion
 */
class Dynamics {

    private static $instance = NULL;

    private function __construct() {
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->__load_dynamic_components();
        }

        return self::$instance;
    }

    private function __load_dynamic_components() {
        $dynamic_folder = makefilelist(DYNAMICS.'includes/', '.|..|.htaccess|index.php|._DS_Store|.tmp', TRUE, 'files');

        if (!empty($dynamic_folder)) {
            foreach ($dynamic_folder as $folder) {
                require_once DYNAMICS."includes/".$folder;
            }
        }

        if (!defined("SELECT2")) {
            define("SELECT2", TRUE);
            \PHPFusion\OutputHandler::addToFooter("<script src='".fusion_get_settings('siteurl')."includes/dynamics/assets/select2/select2.min.js'></script>");
            \PHPFusion\OutputHandler::addToHead("<link href='".DYNAMICS."assets/select2/select2.min.css' rel='stylesheet' />");
            $select2_locale = fusion_get_locale("select2");
            $select2_locale_path = DYNAMICS."assets/select2/select2_locale_".$select2_locale.".js";
            if (file_exists($select2_locale_path)) {
                \PHPFusion\OutputHandler::addToFooter("<script src='$select2_locale_path'></script>");
            }
        }
    }
}
