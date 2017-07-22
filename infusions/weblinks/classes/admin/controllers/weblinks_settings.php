<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/admin/controllers/weblinks_settings.php
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
namespace PHPFusion\Weblinks;

class WeblinksSettingsAdmin extends WeblinksAdminModel {

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayWeblinksAdmin() {

        pageAccess("W");
        $this->locale = self::get_WeblinkAdminLocale();
        $weblink_settings = self::get_weblink_settings();

        // Save
        if (isset($_POST['savesettings'])) {
            $inputArray = array(
                "links_per_page"        => form_sanitizer($_POST['links_per_page'], 15, "links_per_page"),
                "links_allow_submission"  => form_sanitizer($_POST['links_allow_submission'], 0, "links_allow_submission"),
                "links_extended_required" => form_sanitizer($_POST['links_extended_required'], 0, "links_extended_required")
            );

            // Update
            if (\defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = array(
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "weblinks",
                    );
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
                }
                addNotice("success", $this->locale['900']);
                redirect(FUSION_REQUEST);
            } else {
                addNotice("danger", $this->locale['901']);
                $weblink_settings = $inputArray;
            }
        }

        echo openform("settingsform", "post", FUSION_REQUEST);
        echo "<div class='well spacer-xs'>".$this->locale['WLS_0400']."</div>\n";

        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12'>\n";

        echo form_text("links_per_page", $this->locale['WLS_0132'], $weblink_settings['links_per_page'], array(
            "max_length" => 4, "inner_width" => "250px", "type" => "number", 'inline' => TRUE
            ));

        echo form_select("links_allow_submission", $this->locale['WLS_0007'], $weblink_settings['links_allow_submission'], array(
                "options" => array($this->locale['disable'], $this->locale['enable']), 'inline' => TRUE
            ));

        echo form_select("links_extended_required", $this->locale['WLS_0403'], $weblink_settings['links_extended_required'], array(
                "options" => array($this->locale['disable'], $this->locale['enable']), 'inline' => TRUE
            ));
        echo "</div>\n";
        echo "</div>\n";

        echo form_button("savesettings", $this->locale['750'], $this->locale['750'], array("class" => "btn-success", "icon" => "fa fa-fw fa-hdd-o"));
        echo closeform();
    }
}
