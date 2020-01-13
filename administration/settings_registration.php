<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_registration.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';

class Settings_Registration {
    private $locale = [];
    private $settings = [];
    private $login = NULL;

    public function __construct() {
        pageAccess('S4');
        $this->settings = fusion_get_settings();
        $this->locale = fusion_get_locale("", [LOCALE.LOCALESET."admin/login.php", LOCALE.LOCALESET."admin/settings.php"]);
        $aidlink = fusion_get_aidlink();
        $admin = \PHPFusion\Admins::getInstance();
        $admin->addAdminPage("S4", "General and Policy", "S41", ADMIN."settings_registration.php".$aidlink);
        $admin->addAdminPage("S4", "Social Login Providers", "S42", ADMIN."settings_registration.php".$aidlink."&amp;ref=login");
        add_breadcrumb(['link' => ADMIN.'settings_registration.php'.$aidlink, 'title' => $this->locale['register_settings']]);
        $this->displayForm();
    }

    private function displayForm() {
        $is_multilang = count(fusion_get_enabled_languages()) > 1 ? TRUE : FALSE;

        if (post('savesettings')) {

            $inputData = [
                'login_method'        => sanitizer('login_method', '0', 'login_method'),
                'license_agreement'   => form_sanitizer($_POST['license_agreement'], '', 'license_agreement', $is_multilang),
                'enable_registration' => sanitizer('enable_registration', '0', 'enable_registration'),
                'email_verification'  => (post('email_verification') ? 1 : 0),
                'admin_activation'    => (post('admin_activation') ? 1 : 0),
                'display_validation'  => (post('display_validation') ? 1 : 0),
                'enable_terms'        => sanitizer('enable_terms', '0', 'enable_terms'),
                'license_lastupdate'  => ($_POST['license_agreement'] != $this->settings['license_agreement'] ? time() : $this->settings['license_lastupdate'])
            ];

            if (fusion_safe()) {
                foreach ($inputData as $settings_name => $settings_value) {
                    $data = [
                        'settings_name'  => $settings_name,
                        'settings_value' => $settings_value
                    ];
                    dbquery_insert(DB_SETTINGS, $data, 'update', ['primary_key' => 'settings_name']);
                }
                addNotice('success', $this->locale['900']);
                redirect(FUSION_REQUEST);
            }
        }
        opentable("General and Policy Settings");
        echo openform('registrationfrm', 'post');
        echo "<p>".$this->locale['register_description']."</p>\n<hr/>";
        echo "<div class='".grid_row()."'>\n<div class='".grid_column_size(100, 20)."'>\n";
        echo "<h4 class='m-0'>".$this->locale['register_settings']."</h4>";
        echo "</div>\n<div class='".grid_column_size(100, 80)."'>\n";
        echo form_checkbox('enable_registration', $this->locale['551'], $this->settings['enable_registration'], ['reverse_label' => TRUE]);
        echo form_checkbox('email_verification', $this->locale['552'], $this->settings['email_verification'], ['reverse_label' => TRUE]);
        echo form_checkbox('admin_activation', $this->locale['557'], $this->settings['admin_activation'], ['reverse_label' => TRUE]);
        echo form_checkbox('display_validation', $this->locale['553'], $this->settings['display_validation'], ['reverse_label' => TRUE]);
        echo "</div>\n</div>\n";
        echo "<hr/>\n";
        echo "<div class='".grid_row()."'>\n";
        echo "<div class='".grid_column_size(100, 20)."'>\n";
        echo "<h4 class='m-0'>Login Behaviors</h4>";
        echo "</div>\n<div class='".grid_column_size(100, 80)."'>\n";
        $opts = ['0' => $this->locale['global_101'], '1' => $this->locale['699e'], '2' => $this->locale['699b']];
        echo form_select('login_method', $this->locale['699'], $this->settings['login_method'], ['options' => $opts]);
        echo "</div>\n</div>\n";
        echo "<hr/>\n";
        echo form_select('enable_terms', $this->locale['558'], $this->settings['enable_terms'], ['options' => [
            0 => $this->locale['disable'],
            1 => $this->locale['enable'],
        ]]);
        if ($is_multilang == TRUE) {
            echo \PHPFusion\UserFieldsQuantum::quantum_multilocale_fields('license_agreement', $this->locale['559'], $this->settings['license_agreement'], [
                'form_name' => 'settingsform',
                'input_id'  => 'enable_license_agreement',
                'autosize'  => !fusion_get_settings('tinymce_enabled') ? FALSE : TRUE,
                'type'      => (fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html'),
                'function'  => 'form_textarea'
            ]);
        } else {
            echo form_textarea('license_agreement', $this->locale['559'], $this->settings['license_agreement'], [
                'form_name' => 'settingsform',
                'autosize'  => !fusion_get_settings('tinymce_enabled') ? FALSE : TRUE,
                'html'      => !fusion_get_settings('tinymce_enabled') ? TRUE : FALSE
            ]);
        }
        echo form_button('savesettings', $this->locale['750'], $this->locale['750'], ['class' => 'btn-success']);
        echo closeform();
        closetable();
    }

}

new Settings_Registration();

require_once THEMES.'templates/footer.php';
