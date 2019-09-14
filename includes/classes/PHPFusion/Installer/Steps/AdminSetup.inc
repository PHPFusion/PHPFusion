<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminSetup.inc
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

namespace PHPFusion\Steps;

use PHPFusion\Installer\Batch_Core;
use PHPFusion\Installer\Install_Core;
use PHPFusion\Installer\Requirements;
use PHPFusion\PasswordAuth;

class InstallerAdminSetup extends Install_Core {

    public function __view() {
        self::$connection = self::fusion_get_config(BASEDIR.'config_temp.php');
        require_once(INCLUDES.'multisite_include.php');
        $validation = Requirements::get_system_validation();
        if (isset($validation[3])) {
            if ($this->tableCheck()) {
                switch (INSTALLATION_STEP) {
                    case self::STEP_TRANSFER:
                        return $this->_transfer();
                        break;
                    case self::STEP_PRIMARY_ADMIN_FORM:
                        return $this->_setup();
                        break;
                    default:
                        return NULL;
                }
            }
        } else {
            return NULL;
        }

        return NULL;
    }

    private function _transfer() {

        $content = "";

        if (isset($_POST['transfer'])) {

            self::$userData = $this->validate_UserData();
            self::$userData['user_id'] = 1;

            if (self::$userData['password1'] == self::$userData['admin_password1']) {
                addNotice('danger', self::$locale['setup_5016']);
                \defender::stop();
            }

            if (\defender::safe()) {
                $user_auth = new \PasswordAuth(self::INSTALLER_ALGO);
                $user_auth->inputNewPassword = self::$userData['password1'];
                $user_auth->inputNewPassword2 = self::$userData['password2'];
                switch ($user_auth->isValidNewPassword()) {
                    default:
                        self::$userData['user_password'] = $user_auth->getNewHash();
                        self::$userData['user_salt'] = $user_auth->getNewSalt();
                        break;
                    case 2:
                        addNotice('danger', self::$locale['setup_5012']);
                        \defender::stop();
                        break;
                    case 3:
                        addNotice('danger', self::$locale['setup_5013']);
                        \defender::stop();
                        break;
                }
                $admin_auth = new \PasswordAuth(self::INSTALLER_ALGO);
                $admin_auth->inputNewPassword = self::$userData['admin_password1'];
                $admin_auth->inputNewPassword2 = self::$userData['admin_password2'];
                switch ($admin_auth->isValidNewPassword()) {
                    default:
                        self::$userData['user_admin_password'] = $admin_auth->getNewHash();
                        self::$userData['user_admin_salt'] = $admin_auth->getNewSalt();
                        break;
                    case 2:
                        addNotice('danger', self::$locale['setup_5015']);
                        \defender::stop();
                        break;
                    case 3:
                        addNotice('danger', self::$locale['setup_5017']);
                        \defender::stop();
                        break;
                }

                if (\defender::safe()) {
                    dbquery_insert(DB_PREFIX."users", self::$userData, 'update');
                    addNotice('success', self::$locale['setup_1217']);

                    require_once(INCLUDES."multisite_include.php");
                    self::installer_step(self::STEP_INTRO);
                    new \Authenticate(self::$userData['user_name'], self::$userData['user_password'], TRUE, filter_input(INPUT_SERVER, 'REQUEST_URI'));

                }
            }
        }

        $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='1'");

        if (dbrows($result) > 0) {

            self::$userData = dbarray($result);

            $content .= "<h4 class='title'>".self::$locale['setup_1500']."</h4><p>".self::$locale['setup_1501']."</p\n";
            $content .= "<hr/>\n";
            $content .= form_hidden('transfer', '', '1');
            $content .= form_text('user_name', self::$locale['setup_1504'], self::$userData['user_name'],
                [
                    'required'       => TRUE,
                    'inline'         => TRUE,
                    'maxlength'      => 30,
                    'error_text'     => self::$locale['setup_5010'],
                    'callback_check' => 'username_check'
                ]
            );
            $content .= form_text('user_email', self::$locale['setup_1509'], self::$userData['user_email'], ['required' => TRUE, 'inline' => TRUE, 'type' => 'email', 'error_text' => self::$locale['setup_5020']]);
            $content .= form_text('password1', self::$locale['setup_1505'], '', ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password']);
            $content .= form_text('password2', self::$locale['setup_1506'], '', ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password']);
            $content .= form_text('admin_password1', self::$locale['setup_1507'], '', ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password']);
            $content .= form_text('admin_password2', self::$locale['setup_1508'], '', ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password']);

        }

        self::$step = [
            1 => [
                'name'  => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_TRANSFER
            ],
            2 => [
                'name'  => 'step',
                'class' => 'm-l-15 btn-default',
                'label' => self::$locale['setup_0124'],
                'value' => self::STEP_INTRO
            ]
        ];

        return $content;
    }

    private function validate_UserData() {
        return [
            'user_name'           => stripinput(filter_input(INPUT_POST, 'user_name')),
            'user_email'          => stripinput(filter_input(INPUT_POST, 'user_email')),
            'user_algo'           => self::INSTALLER_ALGO,
            'user_salt'           => '',
            'user_password'       => '',
            'user_admin_algo'     => self::INSTALLER_ALGO,
            'user_admin_salt'     => '',
            'user_admin_password' => '',
            'password1'           => stripinput(filter_input(INPUT_POST, 'password1')),
            'password2'           => stripinput(filter_input(INPUT_POST, 'password2')),
            'admin_password1'     => stripinput(filter_input(INPUT_POST, 'admin_password1')),
            'admin_password2'     => stripinput(filter_input(INPUT_POST, 'admin_password2')),
            'user_rights'         => self::USER_RIGHTS_SA,
            'user_hide_email'     => 1,
            'user_timezone'       => stripinput(filter_input(INPUT_POST, 'user_timezone')),
            'user_joined'         => TIME,
            'user_lastvisit'      => TIME,
            'user_ip'             => USER_IP,
            'user_level'          => USER_LEVEL_SUPER_ADMIN,
            'user_status'         => '0',
            'user_theme'          => 'Default',
            'user_birthdate'      => '1900-01-01',
        ];
    }

    private function _setup() {

        self::$siteData = [
            'sitename'         => fusion_get_settings('sitename'),
            'siteemail'        => fusion_get_settings('siteemail'),
            'siteusername'     => fusion_get_settings('siteusername'),
            'default_timezone' => fusion_get_settings('default_timezone'),
        ];

        $this->update();

        // Should have a db connection now
        $content = "<h4 class='title'>".self::$locale['setup_1212']."</h4><p>".self::$locale['setup_1213']."</p>\n";
        $content .= "<hr/>\n";

        $content .= renderNotices(getNotices());

        $content .= form_text('sitename', self::$locale['setup_1214'], self::$siteData['sitename'],
            [
                'inline' => TRUE, 'required' => TRUE, 'placeholder' => self::$locale['setup_1215'], 'class' => 'form-group-lg'
            ]);
        $content .= form_text('siteemail', self::$locale['setup_1510'], self::$siteData['siteemail'],
            ['inline' => TRUE, 'required' => TRUE, 'type' => 'email']);
        $content .= form_text('siteusername', self::$locale['setup_1513'], self::$siteData['siteusername'],
            [
                'required'   => TRUE,
                'inline'     => TRUE,
                'maxlength'  => 30,
                'error_text' => self::$locale['setup_5011']
            ]
        );

        $timezones_json = json_decode('{
          "Etc/GMT+12": "International Date Line West",
          "Pacific/Midway": "Midway Island, Samoa",
          "Pacific/Honolulu": "Hawaii",
          "America/Juneau": "Alaska",
          "America/Dawson": "Pacific Time (US and Canada); Tijuana",
          "America/Boise": "Mountain Time (US and Canada)",
          "America/Chihuahua": "Chihuahua, La Paz, Mazatlan",
          "America/Phoenix": "Arizona",
          "America/Chicago": "Central Time (US and Canada)",
          "America/Regina": "Saskatchewan",
          "America/Mexico_City": "Guadalajara, Mexico City, Monterrey",
          "America/Belize": "Central America",
          "America/Detroit": "Eastern Time (US and Canada)",
          "America/Indiana/Indianapolis": "Indiana (East)",
          "America/Bogota": "Bogota, Lima, Quito",
          "America/Glace_Bay": "Atlantic Time (Canada)",
          "America/Caracas": "Caracas, La Paz",
          "America/Santiago": "Santiago",
          "America/St_Johns": "Newfoundland and Labrador",
          "America/Sao_Paulo": "Brasilia",
          "America/Argentina/Buenos_Aires": "Buenos Aires, Georgetown",
          "America/Godthab": "Greenland",
          "Etc/GMT+2": "Mid-Atlantic",
          "Atlantic/Azores": "Azores",
          "Atlantic/Cape_Verde": "Cape Verde Islands",
          "GMT": "Dublin, Edinburgh, Lisbon, London",
          "Africa/Casablanca": "Casablanca, Monrovia",
          "Atlantic/Canary": "Canary Islands",
          "Europe/Belgrade": "Belgrade, Bratislava, Budapest, Ljubljana, Prague",
          "Europe/Sarajevo": "Sarajevo, Skopje, Warsaw, Zagreb",
          "Europe/Brussels": "Brussels, Copenhagen, Madrid, Paris",
          "Europe/Amsterdam": "Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
          "Africa/Algiers": "West Central Africa",
          "Europe/Bucharest": "Bucharest",
          "Africa/Cairo": "Cairo",
          "Europe/Helsinki": "Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius",
          "Europe/Athens": "Athens, Istanbul, Minsk",
          "Asia/Jerusalem": "Jerusalem",
          "Africa/Harare": "Harare, Pretoria",
          "Europe/Moscow": "Moscow, St. Petersburg, Volgograd",
          "Asia/Kuwait": "Kuwait, Riyadh",
          "Africa/Nairobi": "Nairobi",
          "Asia/Baghdad": "Baghdad",
          "Asia/Tehran": "Tehran",
          "Asia/Dubai": "Abu Dhabi, Muscat",
          "Asia/Baku": "Baku, Tbilisi, Yerevan",
          "Asia/Kabul": "Kabul",
          "Asia/Yekaterinburg": "Ekaterinburg",
          "Asia/Karachi": "Islamabad, Karachi, Tashkent",
          "Asia/Kolkata": "Chennai, Kolkata, Mumbai, New Delhi",
          "Asia/Kathmandu": "Kathmandu",
          "Asia/Dhaka": "Astana, Dhaka",
          "Asia/Colombo": "Sri Jayawardenepura",
          "Asia/Almaty": "Almaty, Novosibirsk",
          "Asia/Rangoon": "Yangon Rangoon",
          "Asia/Bangkok": "Bangkok, Hanoi, Jakarta",
          "Asia/Krasnoyarsk": "Krasnoyarsk",
          "Asia/Shanghai": "Beijing, Chongqing, Hong Kong SAR, Urumqi",
          "Asia/Kuala_Lumpur": "Kuala Lumpur, Singapore",
          "Asia/Taipei": "Taipei",
          "Australia/Perth": "Perth",
          "Asia/Irkutsk": "Irkutsk, Ulaanbaatar",
          "Asia/Seoul": "Seoul",
          "Asia/Tokyo": "Osaka, Sapporo, Tokyo",
          "Asia/Yakutsk": "Yakutsk",
          "Australia/Darwin": "Darwin",
          "Australia/Adelaide": "Adelaide",
          "Australia/Sydney": "Canberra, Melbourne, Sydney",
          "Australia/Brisbane": "Brisbane",
          "Australia/Hobart": "Hobart",
          "Asia/Vladivostok": "Vladivostok",
          "Pacific/Guam": "Guam, Port Moresby",
          "Asia/Magadan": "Magadan, Solomon Islands, New Caledonia",
          "Pacific/Fiji": "Fiji Islands, Kamchatka, Marshall Islands",
          "Pacific/Auckland": "Auckland, Wellington",
          "Pacific/Tongatapu": "Nuku\'alofa"
        }', TRUE);
        $timezone_array = [];
        foreach ($timezones_json as $zone => $zone_city) {
            $date = new \DateTime(NULL, new \DateTimeZone($zone));
            $offset = $date->getOffset() / 3600;
            $timezone_array[$zone] = '(GMT'.($offset < 0 ? $offset : '+'.$offset).') '.$zone_city;
        }

        $content .= form_select('default_timezone', self::$locale['setup_1511'], self::$siteData['default_timezone'], ['options' => $timezone_array, 'required' => TRUE, 'inline' => TRUE]);

        $content .= "<h4 class='title'>".self::$locale['setup_1500']."</h4><p>".self::$locale['setup_1501']."</p>\n";
        $content .= "<hr />\n";

        $content .= form_text('user_name', self::$locale['setup_1504'], self::$userData['user_name'],
            [
                'required'       => TRUE,
                'inline'         => TRUE,
                'maxlength'      => 30,
                'error_text'     => self::$locale['setup_5010'],
                'callback_check' => 'username_check'
            ]
        );
        $content .= form_text('password1', self::$locale['setup_1505'], self::$userData['password1'],
            ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password', 'error_text' => '']);
        $content .= form_text('password2', self::$locale['setup_1506'], self::$userData['password2'],
            ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password', 'error_text' => '']);
        $content .= form_text('admin_password1', self::$locale['setup_1507'], self::$userData['admin_password1'],
            ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password', 'error_text' => '']);
        $content .= form_text('admin_password2', self::$locale['setup_1508'], self::$userData['admin_password2'],
            ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password', 'error_text' => '']);
        $content .= form_text('user_email', self::$locale['setup_1509'], self::$userData['user_email'],
            ['required' => TRUE, 'inline' => TRUE, 'type' => 'email', 'error_text' => self::$locale['setup_5020']]);

        $content .= "<h4 class='title'>".self::$locale['setup_1512']."</h4><p>".self::$locale['setup_1001']."</p>\n";
        $content .= "<hr />\n";

        $checkbox_options = [];
        foreach (self::$locale_files as $languageKey => $languageName) {
            $localeFlagPath = BASEDIR."locale/".$languageKey."/$languageKey-s.png";
            $checkbox_options[$languageKey] = "<img src='".$localeFlagPath."' class='m-l-15' alt='$languageName'/> $languageName";
        }
        $content .= form_checkbox('enabled_languages[]', '',
            !empty(self::$siteData['enabled_languages']) ? self::$siteData['enabled_languages'] : self::$localeset, [
                'required'       => TRUE,
                'reverse_label'  => TRUE,
                'class'          => 'm-0 p-0 input-md',
                'options'        => $checkbox_options,
                'deactivate_key' => self::$localeset,
                'delimiter'      => '.' // Refer to L1051, L1060 and fusion_get_enabled_languages(); it's '.'
            ]);

        self::$step = [
            1 => [
                'name'  => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_PRIMARY_ADMIN_FORM
            ]
        ];

        return $content;
    }

    /*
     * Update the Super Administrator
     *
     */
    private function update() {

        if (isset($_POST['sitename']) && isset($_POST['siteemail']) && isset($_POST['user_name'])) {

            function user_name_check($username) {
                return !preg_match("/^[-0-9A-Z_@\s]+$/i", $username);
            }

            self::$siteData = $this->validate_SiteData();

            self::$userData = $this->validate_UserData();

            if (self::$userData['password1'] == self::$userData['admin_password1']) {
                \defender::stop();
                addNotice('danger', self::$locale['setup_5016']);
            }

            if (\defender::safe()) {

                $user_auth = new PasswordAuth(self::INSTALLER_ALGO);

                $user_auth->inputNewPassword = self::$userData['password1'];
                $user_auth->inputNewPassword2 = self::$userData['password2'];

                switch ($user_auth->isValidNewPassword()) {
                    default:
                        self::$userData['user_password'] = $user_auth->getNewHash();
                        self::$userData['user_salt'] = $user_auth->getNewSalt();
                        break;
                    case 2:
                        \defender::stop();
                        \defender::setInputError('password2');
                        addNotice('danger', self::$locale['setup_5012']);

                        break;
                    case 3:
                        \defender::stop();
                        \defender::setInputError('password1');
                        addNotice('danger', self::$locale['setup_5013']);
                        break;
                }

                $admin_auth = new \PasswordAuth(self::INSTALLER_ALGO);
                $admin_auth->inputNewPassword = self::$userData['admin_password1'];
                $admin_auth->inputNewPassword2 = self::$userData['admin_password2'];
                switch ($admin_auth->isValidNewPassword()) {
                    default:
                        self::$userData['user_admin_password'] = $admin_auth->getNewHash();
                        self::$userData['user_admin_salt'] = $admin_auth->getNewSalt();
                        break;
                    case 2:
                        \defender::stop();
                        \defender::setInputError('admin_password2');
                        addNotice('danger', self::$locale['setup_5015']);
                        break;
                    case 3:
                        \defender::stop();
                        \defender::setInputError('admin_password1');
                        addNotice('danger', self::$locale['setup_5017']);
                        break;
                }

                if (\defender::safe()) {

                    self::$userData['user_timezone'] = self::$siteData['default_timezone'];
                    $batch_core = Batch_Core::getInstance();
                    // Create Super Admin
                    if (dbcount("(user_id)", DB_PREFIX."users", "user_id='1'")) {
                        self::$userData['user_id'] = 1;
                        dbquery_insert(DB_PREFIX."users", self::$userData, 'update');
                    } else {
                        dbquery_insert(DB_PREFIX."users", self::$userData, 'save');
                    }
                    $enabled_lang = implode('.', self::$siteData['enabled_languages']);
                    // Update Site Settings
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['sitename']."' WHERE settings_name='sitename'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['siteemail']."' WHERE settings_name='siteemail'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".$enabled_lang."' WHERE settings_name='enabled_languages'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['default_timezone']."' WHERE settings_name='default_timezone'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['default_timezone']."' WHERE settings_name='timeoffset'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['default_timezone']."' WHERE settings_name='serveroffset'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['siteusername']."' WHERE settings_name='siteusername'");

                    if (strpos($enabled_lang, '.')) {

                        // Update all existing panel and update new enabled language values
                        dbquery("UPDATE ".DB_PREFIX."panels SET panel_languages='".$enabled_lang."'");

                        $result = dbquery("SELECT link_language FROM ".DB_PREFIX."site_links GROUP BY link_language ASC");
                        $installed_languages = [];
                        if (dbrows($result) > 0) {
                            while ($data = dbarray($result)) {
                                $installed_languages[] = $data['link_language'];
                            }
                        }

                        $langDiff = array_diff(self::$siteData['enabled_languages'], $installed_languages);
                        if (!empty($langDiff)) {
                            foreach ($langDiff as $language) {
                                $sql_inserts = $batch_core::batch_insert_rows('site_links', $language);
                                if ($result = dbquery($sql_inserts)) {
                                    continue;
                                }
                            }
                        }
                        unset($installed_languages);

                        $result = dbquery("SELECT admin_language FROM ".DB_PREFIX."admin GROUP BY admin_language ASC");
                        $installed_languages = [];
                        if (dbrows($result) > 0) {
                            while ($data = dbarray($result)) {
                                $installed_languages[] = $data['admin_language'];
                            }
                        }

                        $langDiff = array_diff(self::$siteData['enabled_languages'], $installed_languages);
                        if (!empty($langDiff)) {
                            foreach ($langDiff as $language) {
                                $sql_inserts = $batch_core::batch_insert_rows('admin', $language);
                                if ($result = dbquery($sql_inserts)) {
                                    continue;
                                }
                            }
                        }
                        unset($installed_languages);

                        /*
                         * Need to run another check with email_templates because installed languages might be different.
                         */
                        $result = dbquery("SELECT template_language FROM ".DB_PREFIX."email_templates GROUP BY template_language ASC");
                        $installed_languages = [];
                        if (dbrows($result) > 0) {
                            while ($data = dbarray($result)) {
                                $installed_languages[] = $data['template_language'];
                            }
                        }

                        $langDiff = array_diff(self::$siteData['enabled_languages'], $installed_languages);
                        if (!empty($langDiff)) {

                            foreach ($langDiff as $language) {
                                $sql_inserts = $batch_core::batch_insert_rows('email_templates', $language);
                                if ($result = dbquery($sql_inserts)) {
                                    continue;
                                }
                            }

                            // Update all UF Cat Fields
                            $ufc_result = dbquery("SELECT field_cat_id, field_cat_name FROM ".DB_PREFIX."user_field_cats");
                            if (dbrows($result) && is_array($langDiff) && count($langDiff)) {
                                $locale_keys = array_flip(self::$siteData['enabled_languages']);
                                while ($ufc_data = dbarray($ufc_result)) {
                                    $category_name[self::$localeset] = $ufc_data['field_cat_name'];
                                    // get current locale key
                                    if (isset($locale_keys[$ufc_data['field_cat_name']])) {
                                        $lang_key = $locale_keys[$ufc_data['field_cat_name']];
                                        foreach ($langDiff as $language) {
                                            $locale = [];
                                            include LOCALE.$language.'/setup.php';
                                            $category_name[$language] = $locale[$lang_key]; // bind language = translations value
                                        }
                                    }
                                    if (!empty($category_name)) {
                                        $new_field_cat_name = serialize($category_name);
                                        dbquery("UPDATE ".DB_PREFIX."user_field_cats SET field_cat_name=:field_cat_value WHERE field_cat_id=:field_cat_id", [':field_cat_value' => $new_field_cat_name, ':field_cat_id' => $ufc_data['field_cat_id']]);
                                    }
                                }
                            }
                        }
                    }

                    if (\defender::safe()) {
                        require_once BASEDIR."config_temp.php";
                        require_once INCLUDES."multisite_include.php";
                        self::installer_step(self::STEP_INFUSIONS);
                        redirect(FUSION_REQUEST);
                        //new \Authenticate(self::$userData['user_name'], self::$userData['user_password'], TRUE, FUSION_REQUEST);
                    } else {
                        self::installer_step(self::STEP_PRIMARY_ADMIN_FORM);
                        redirect(FUSION_REQUEST);
                    }
                }
            }
        }
    }

    private function validate_SiteData() {
        $array = [
            'sitename'          => stripinput($_POST['sitename']),
            'siteemail'         => stripinput($_POST['siteemail']),
            'enabled_languages' => stripinput((isset($_POST['enabled_languages']) ? $_POST['enabled_languages'] : '')),
            'siteusername'      => stripinput(filter_input(INPUT_POST, 'siteusername')),
            'default_timezone'  => stripinput(filter_input(INPUT_POST, 'default_timezone'))
        ];

        return $array;
    }

}
