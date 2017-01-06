<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/classes/admin/controllers/faq_settings.inc
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
namespace PHPFusion\FAQ;

class FaqSettingsAdmin extends FaqAdminModel {

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayFaqAdmin() {

        pageAccess("FQ");
        $this->locale = self::get_faqAdminLocale();

        // Save
        if (isset($_POST['savesettings'])) {
            $inputArray = array(
                "faq_allow_submission"  => form_sanitizer($_POST['faq_allow_submission'], 0, "faq_allow_submission"),
            );
            // Update
            if (\defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = array(
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "faq",
                    );
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
                }
                addNotice("success", $this->locale['900']);
                redirect(FUSION_REQUEST);
            } else {
                addNotice("danger", $this->locale['901']);
                self::$faq_settings = $inputArray;
            }
        }

        echo openform("settingsform", "post", FUSION_REQUEST);
        echo "<div class='well m-b-0'>".$this->locale['faq_0400']."</div>\n";

            echo form_select('faq_allow_submission', $this->locale['faq_0401'], self::$faq_settings['faq_allow_submission'], array(
                'inline' => TRUE, 'options' => array($this->locale['disable'], $this->locale['enable'])
            ));

        echo form_button("savesettings", $this->locale['750'], $this->locale['750'], array("class" => "btn-success", "icon" => "fa fa-fw fa-hdd-o"));
        echo closeform();
    }
}
