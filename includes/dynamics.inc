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
 * @package PHPFusion
 */
class Dynamics {

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new Static();
            self::$instance->__load_dynamic_components();
        }

        return self::$instance;
    }

    private function __load_dynamic_components() {
        require_once DYNAMICS."includes/form_main.php";
        require_once DYNAMICS."includes/form_text.php";
        require_once DYNAMICS."includes/form_name.php";
        require_once DYNAMICS."includes/form_select.php";
        require_once DYNAMICS."includes/form_textarea.php";
        require_once DYNAMICS."includes/form_hidden.php";
        require_once DYNAMICS."includes/form_buttons.php";
        require_once DYNAMICS."includes/form_ordering.php";
        require_once DYNAMICS."includes/form_datepicker.php";
        require_once DYNAMICS."includes/form_fileinput.php";
        require_once DYNAMICS."includes/form_colorpicker.php";
        require_once DYNAMICS."includes/form_geomap.php";
        require_once DYNAMICS."includes/form_modal.php";
        require_once DYNAMICS."includes/form_checkbox.php";
        require_once DYNAMICS."includes/form_paragraph.php";
        require_once DYNAMICS."includes/form_document.php";

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
