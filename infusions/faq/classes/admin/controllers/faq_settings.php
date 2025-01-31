<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: faq_settings.php
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
namespace PHPFusion\FAQ;

class FaqSettingsAdmin extends FaqAdminModel {
    private static $instance = NULL;
    private $locale;

    public function __construct() {
        parent::__construct();

        $this->locale = self::getFaqAdminLocale();
    }

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayFaqAdmin() {
        pageaccess("FQ");
        // Save
        if (!empty($this->save)) {
            $this->saveFaqAdmin();
        }
        $this->faqAdminForm();
    }

    private function saveFaqAdmin() {
        if (check_post('savesettings')) {
            $inputArray = [
                'faq_allow_submission'  => sanitizer('faq_allow_submission', 0, 'faq_allow_submission'),
                'faq_submission_access' => sanitizer(['faq_submission_access'], USER_LEVEL_MEMBER, 'faq_submission_access')
            ];
            // Update
            if (fusion_safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        'settings_name'  => $settings_name,
                        'settings_value' => $settings_value,
                        'settings_inf'   => 'faq'
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
                }
                addnotice('success', $this->locale['admins_900']);
                redirect(FUSION_REQUEST);
            }

            addnotice('danger', $this->locale['admins_901']);
            self::$faq_settings = $inputArray;
        }
    }

    private function faqAdminForm() {
        echo openform('settingsform', 'post', FUSION_REQUEST).
            "<div class='well'>".$this->locale['faq_0400']."</div>".
            form_select('faq_allow_submission', $this->locale['faq_0005'], self::$faq_settings['faq_allow_submission'], [
                'inline'  => TRUE,
                'options' => [
                    $this->locale['disable'], $this->locale['enable']
                ]
            ]).
            form_select('faq_submission_access[]', $this->locale['submit_access'], self::$faq_settings['faq_submission_access'], [
                'inline'   => TRUE,
                'options'  => fusion_get_groups([USER_LEVEL_PUBLIC]),
                'multiple' => TRUE,
            ]).
            form_button('savesettings', $this->locale['admins_750'], $this->locale['admins_750'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']).
            closeform();
    }
}
