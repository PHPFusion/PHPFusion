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
    private $locale = [];

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
        if (!empty($this->save)) {
            $this->SaveFaqAdmin();
        }
        $this->FaqAdminForm();
    }

    private function SaveFaqAdmin() {
        $inputArray = [
            'faq_allow_submission' => form_sanitizer($this->faq_allow_submission, 0, 'faq_allow_submission')
        ];
        // Update
        if (\defender::safe()) {
            foreach ($inputArray as $settings_name => $settings_value) {
                $inputSettings = [
                    'settings_name'  => $settings_name,
                    'settings_value' => $settings_value,
                    'settings_inf'   => 'faq',
                ];
                dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
            }
            addNotice('success', $this->locale['900']);
            redirect(FUSION_REQUEST);
        }

        addNotice('danger', $this->locale['901']);
        self::$faq_settings = $inputArray;
    }

    private function FaqAdminForm() {
        echo openform('settingsform', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']).
            "<div class='well spacer-xs'>".$this->locale['faq_0400']."</div>\n".
            form_select('faq_allow_submission', $this->locale['faq_0005'], self::$faq_settings['faq_allow_submission'], [
                'inline'  => TRUE,
                'options' => [$this->locale['disable'], $this->locale['enable']]
            ]).
            form_button('savesettings', $this->locale['750'], $this->locale['750'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']).
            closeform();
    }
}
