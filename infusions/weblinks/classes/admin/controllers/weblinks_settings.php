<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: weblinks_settings.php
| Author: Core Development Team
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

        pageaccess("W");
        $locale = self::getWeblinkAdminLocale();
        $weblink_settings = self::getWeblinkSettings();

        // Save
        if (check_post('savesettings')) {

            $inputArray = [
                'links_per_page'          => sanitizer('links_per_page', 15, 'links_per_page'),
                'links_allow_submission'  => post('links_allow_submission') ? 1 : 0,
                'links_extended_required' => post('links_extended_required') ? 1 : 0,
                'links_submission_access' => sanitizer(['links_submission_access'], USER_LEVEL_MEMBER, 'links_submission_access')
            ];

            // Update
            if (fusion_safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        'settings_name' => $settings_name, 'settings_value' => $settings_value, 'settings_inf' => "weblinks",
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
                }
                addnotice('success', $locale['admins_900']);
                redirect(FUSION_REQUEST);
            } else {
                addnotice('danger', $locale['admins_901']);
                $weblink_settings = $inputArray;
            }
        }

        echo openform('settingsform', 'post', FUSION_REQUEST);
        echo "<div class='well'>".$locale['WLS_0400']."</div>\n";

        echo form_text('links_per_page', $locale['WLS_0132'], $weblink_settings['links_per_page'], [
            'max_length'  => 4,
            'inner_width' => '250px',
            'type'        => 'number',
            'inline'      => TRUE
        ]);
        echo form_select('links_allow_submission', $locale['WLS_0007'], $weblink_settings['links_allow_submission'], [
            'inline'  => TRUE,
            'options' => [
                $locale['disable'], $locale['enable']
            ]
        ]);
        echo form_select('links_submission_access[]', $locale['submit_access'], $weblink_settings['links_submission_access'], [
            'inline'   => TRUE,
            'options'  => fusion_get_groups([USER_LEVEL_PUBLIC]),
            'multiple' => TRUE
        ]);
        echo form_select('links_extended_required', $locale['WLS_0403'], $weblink_settings['links_extended_required'], [
            'inline'  => TRUE,
            'options' => [
                $locale['no'], $locale['yes']
            ]
        ]);

        echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-success', 'icon' => 'fa fa-fw fa-hdd-o']);
        echo closeform();
    }
}
