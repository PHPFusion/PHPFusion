<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: widget.php
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

class FusionThemeAdmin {

    private $exclude_list = ".|..|.htaccess|.DS_Store|config.php|config.temp.php|.gitignore|LICENSE|README.md|robots.txt|reactivate.php|rewrite.php|maintenance.php|maincore.php|lostpassword.php|index.php|error.php";

    private $theme_pack_dir = THEMES;

    public function __construct() {
        $this->theme_pack_dir = $this->theme_pack_dir."FusionTheme/themepack/";
        $this->settings();
    }

    public function settings() {
        $settings = get_theme_settings("FusionTheme");
        if (isset($_POST['save_settings'])) {
            $inputArray = array(
                "theme_pack" => form_sanitizer($_POST['theme_pack'], "", "theme_pack"),
            );
            if (defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $sqlArray = array(
                        "settings_name"  => $settings_name,
                        "settings_value" => $settings_value,
                        "settings_theme" => "FusionTheme",
                    );
                    dbquery_insert(DB_SETTINGS_THEME, $sqlArray, "update", array("primary_key" => "settings_name"));
                    addNotice("success", fusion_get_locale('WIDGET_001', THEME.'locale/'.LANGUAGE.'.php'));
                }
                if (defender::safe()) {
                    redirect(FUSION_REQUEST);
                }
            }
        }
        echo openform("main_settings", "post", FUSION_REQUEST, array("class" => "clearfix m-t-20"));
        echo form_select("theme_pack", fusion_get_locale('theme_1037', LOCALE.LOCALESET."admin/theme.php"), $settings['theme_pack'], array(
            "options" => $this->get_template_list(), "required" => TRUE, "inline" => TRUE
        ));
        echo form_button("save_settings", fusion_get_locale('save_changes'), "save", array("class" => "btn-primary"));
        echo closeform();
    }

    /**
     * Returns all available Atom Theme Template
     *
     * @return array
     */
    public function get_template_list() {
        $_list = array();
        $file_list = makefilelist($this->theme_pack_dir, $this->exclude_list, TRUE, "folders");
        foreach ($file_list as $files) {
            $_list[$files] = str_replace(".php", "", str_replace("_", " ", ucwords($files)));
        }

        return (array)$_list;
    }

}

new FusionThemeAdmin();