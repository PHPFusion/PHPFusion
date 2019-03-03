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
    private $locale = [];

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
        	$links_extended_required = filter_input(INPUT_POST, 'links_extended_required', FILTER_VALIDATE_INT);
        	$links_allow_submission = filter_input(INPUT_POST, 'links_allow_submission', FILTER_VALIDATE_INT);

            $inputArray = [
                'links_per_page'          => form_sanitizer(filter_input(INPUT_POST, 'links_per_page', FILTER_VALIDATE_INT), 15, 'links_per_page'),
                'links_allow_submission'  => !empty($links_allow_submission) ? $links_allow_submission : 0,
                'links_extended_required' => !empty($links_extended_required) ? $links_extended_required : 0
            ];

            // Update
            if (\defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        'settings_name' => $settings_name, 'settings_value' => $settings_value, 'settings_inf' => "weblinks",
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
                }
                addNotice('success', $this->locale['900']);
                redirect(FUSION_REQUEST);
            } else {
                addNotice('danger', $this->locale['901']);
                $weblink_settings = $inputArray;
            }
        }

        echo openform('settingsform', 'post', FUSION_REQUEST);
        echo "<div class='well spacer-xs'>".$this->locale['WLS_0400']."</div>\n";

        echo form_text('links_per_page', $this->locale['WLS_0132'], $weblink_settings['links_per_page'], [
            'max_length'  => 4,
            'inner_width' => '250px',
            'type'        => 'number',
            'inline'      => TRUE
        ]);

        echo "<hr/>\n";

        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-3'>\n";
        echo "<h4 class='m-0'>".$this->locale['WLS_0400']."</h4>";
        echo "</div>\n<div class='col-xs-12 col-sm-9'>\n";
        echo form_checkbox('links_allow_submission', $this->locale['WLS_0007'], $weblink_settings['links_allow_submission'], ['reverse_label' => TRUE]);
        echo form_checkbox('links_extended_required', $this->locale['WLS_0403'], $weblink_settings['links_extended_required'], ['reverse_label' => TRUE]);

        echo "</div>\n</div>\n";
        echo "<hr/>\n";

        echo form_button('savesettings', $this->locale['750'], $this->locale['750'], ['class' => 'btn-success', 'icon' => 'fa fa-fw fa-hdd-o']);
        echo closeform();
    }
}
