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

use PHPFusion\Database\DatabaseFactory;
use PHPFusion\Installer\Install_Core;
use PHPFusion\Installer\Requirements;
use PHPFusion\Installer\Batch_Core;
use PHPFusion\PasswordAuth;

class InstallerAdminSetup extends Install_Core {

    public function __view() {

        self::$connection = self::fusion_get_config(BASEDIR.'config_temp.php');
        require_once(INCLUDES.'multisite_include.php');
        $validation = Requirements::get_system_validation();

        if (isset($validation[3])) {
            switch (INSTALLATION_STEP) {
                case self::STEP_TRANSFER:
                    return $this->_transfer();
                    break;
                case self::STEP_PRIMARY_ADMIN_FORM:
                    return $this->_setup();
                    break;
                case self::STEP_PRIMARY_ADMIN_SAVE:
                    return $this->update();
                    break;
            }
        }

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
                    $this->installer_step(self::STEP_INTRO);
                    new \Authenticate(self::$userData['user_name'], self::$userData['user_password'], TRUE,
                                      filter_input(INPUT_SERVER, 'REQUEST_URI'));

                }
            }
        }

        $result = dbquery("SELECT * FROM ".DB_PREFIX."users WHERE user_id='1'");

        if (dbrows($result) > 0) {

            self::$userData = dbarray($result);

            $content .= "<div class='m-b-20'><h4>".self::$locale['setup_1500']."</h4> ".self::$locale['setup_1501']."</div>\n";
            $content .= form_hidden('transfer', '', '1');
            $content .= form_text('user_name', self::$locale['setup_1504'], self::$userData['user_name'],
                                  array(
                                      'required' => TRUE,
                                      'inline' => TRUE,
                                      'maxlength' => 30,
                                      'error_text' => self::$locale['setup_5010'],
                                      'callback_check' => 'username_check'
                                  )
            );
            $content .= form_text('user_email', self::$locale['setup_1509'], self::$userData['user_email'],
                                  array('required' => TRUE, 'inline' => TRUE, 'type' => 'email', 'error_text' => self::$locale['setup_5020']));
            $content .= form_text('password1', self::$locale['setup_1505'], '',
                                  array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
            $content .= form_text('password2', self::$locale['setup_1506'], '',
                                  array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
            $content .= form_text('admin_password1', self::$locale['setup_1507'], '',
                                  array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
            $content .= form_text('admin_password2', self::$locale['setup_1508'], '',
                                  array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));

        }

        self::$step = array(
            1 => array(
                'name' => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_TRANSFER
            ),
            2 => array(
                'name' => 'step',
                'class' => 'm-l-15 btn-default',
                'label' => self::$locale['setup_0124'],
                'value' => self::STEP_INTRO
            )
        );

        return $content;
    }

    private function validate_UserData() {
        return [
            'user_name' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'user_name'), '', 'user_name'),
            'user_email' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'user_email'), '', 'user_email'),
            'user_algo' => self::INSTALLER_ALGO,
            'user_salt' => '',
            'user_password' => '',
            'user_admin_algo' => self::INSTALLER_ALGO,
            'user_admin_salt' => '',
            'user_admin_password' => '',
            'password1' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'password1'), '', 'password1'),
            'password2' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'password2'), '', 'password2'),
            'admin_password1' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'admin_password1'), '', 'admin_password1'),
            'admin_password2' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'admin_password2'), '', 'admin_password2'),
            'user_rights' => self::USER_RIGHTS_SA,
            'user_hide_email' => 1,
            'user_timezone' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'user_timezone'), '', 'user_timezone'),
            'user_joined' => TIME,
            'user_lastvisit' => TIME,
            'user_ip' => USER_IP,
            'user_level' => USER_LEVEL_SUPER_ADMIN,
            'user_status' => '0',
            'user_theme' => 'Default',
            'user_birthdate' => '1900-01-01',
        ];
    }

    private function _setup() {

        // Should have a db connection now
        self::$siteData = [
            'sitename' => fusion_get_settings('sitename'),
            'siteemail' => fusion_get_settings('siteemail'),
            'siteusername' => fusion_get_settings('siteusername'),
            'default_timezone' => fusion_get_settings('default_timezone'),
        ];

        $content = "<div class='m-b-20'>\n<h4>".self::$locale['setup_1212']."</h4>\n".self::$locale['setup_1213']."</div>\n";

        $content .= form_text('sitename', self::$locale['setup_1214'], self::$siteData['sitename'],
                              array(
                                  'inline' => TRUE, 'required' => TRUE, 'placeholder' => self::$locale['setup_1215'], 'class' => 'form-group-lg'
                              ));
        $content .= form_text('siteemail', self::$locale['setup_1510'], self::$siteData['siteemail'],
                              array('inline' => TRUE, 'required' => TRUE, 'type' => 'email'));
        $content .= form_text('siteusername', self::$locale['setup_1513'], self::$siteData['siteusername'],
                              array(
                                  'required' => TRUE,
                                  'inline' => TRUE,
                                  'maxlength' => 30,
                                  'error_text' => self::$locale['setup_5011']
                              )
        );
        $timezones = timezone_abbreviations_list();
        $timezoneArray = array();
        foreach ($timezones as $zones) {
            foreach ($zones as $zone) {
                if (preg_match('/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $zone['timezone_id'])) {
                    if (!in_array($zone['timezone_id'], $timezoneArray)) {
                        $timezoneArray[$zone['timezone_id']] = $zone['timezone_id'];
                    }
                }
            }
        }
        unset($timezones);
        $content .= form_select('default_timezone', self::$locale['setup_1511'], self::$siteData['default_timezone'],
                                array('options' => $timezoneArray, 'required' => TRUE, 'inline' => TRUE));
        $content .= "<hr />\n";
        $content .= "<div class='m-b-20'><h4>".self::$locale['setup_1500']."</h4> ".self::$locale['setup_1501']."</div>\n";
        $content .= form_text('user_name', self::$locale['setup_1504'], self::$userData['user_name'],
                              array(
                                  'required' => TRUE,
                                  'inline' => TRUE,
                                  'maxlength' => 30,
                                  'error_text' => self::$locale['setup_5010'],
                                  'callback_check' => 'username_check'
                              )
        );

        $content .= form_text('password1', self::$locale['setup_1505'], '',
                              array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
        $content .= form_text('password2', self::$locale['setup_1506'], '',
                              array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
        $content .= form_text('admin_password1', self::$locale['setup_1507'], '',
                              array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
        $content .= form_text('admin_password2', self::$locale['setup_1508'], '',
                              array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
        $content .= form_text('user_email', self::$locale['setup_1509'], self::$userData['user_email'],
                              array('required' => TRUE, 'inline' => TRUE, 'type' => 'email', 'error_text' => self::$locale['setup_5020']));
        $content .= "<div class='m-b-20'>\n<h4>".self::$locale['setup_1512']."</h4>\n".self::$locale['setup_1001']."</div>\n";
        $checkbox_options = [];
        foreach (self::$locale_files as $languageKey => $languageName) {
            $localeFlagPath = BASEDIR."locale/".$languageKey."/$languageKey-s.png";
            $checkbox_options[$languageKey] = "<img src='".$localeFlagPath."' class='m-l-15' alt='$languageName'/> $languageName";
        }
        $content .= form_checkbox('enabled_languages[]', '',
                                  !empty(self::$siteData['enabled_languages']) ? self::$siteData['enabled_languages'] : self::$localeset, array(
                                      'required' => TRUE,
                                      'reverse_label' => TRUE,
                                      'class' => 'm-0 p-0 input-md',
                                      'options' => $checkbox_options,
                                      'deactivate_key' => self::$localeset,
                                      'delimiter' => '.' // Refer to L1051, L1060 and fusion_get_enabled_languages(); it's '.'
                                  ));
        $content .= "<hr />\n";

        self::$step = array(
            1 => array(
                'name' => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_PRIMARY_ADMIN_SAVE
            )
        );

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
                addNotice('danger', self::$locale['setup_5016']);
                \defender::stop();
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

                    self::$userData['user_timezone'] = self::$siteData['default_timezone'];

                    // Create Super Admin
                    if (dbcount("(user_id)", DB_PREFIX."users", "user_id='1'")) {
                        self::$userData['user_id'] = 1;
                        dbquery_insert(DB_PREFIX."users", self::$userData, 'update');
                    } else {
                        dbquery_insert(DB_PREFIX."users", self::$userData, 'save');
                    }

                    // Update Site Settings
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['sitename']."' WHERE settings_name='sitename'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['siteemail']."' WHERE settings_name='siteemail'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['enabled_languages']."' WHERE settings_name='enabled_languages'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['default_timezone']."' WHERE settings_name='default_timezone'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['default_timezone']."' WHERE settings_name='timeoffset'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['default_timezone']."' WHERE settings_name='serveroffset'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['siteusername']."' WHERE settings_name='siteusername'");


                    if (strpos(self::$siteData['enabled_languages'], '.')) {

                        $result = dbquery("SELECT link_language FROM ".DB_PREFIX."site_links GROUP by link_language ASC");
                        if (dbrows($result) > 0) {
                            while ($data = dbarray($result)) {
                                $installed_languages[] = $data['link_language'];
                            }
                        }

                        $langDiff = array_diff(explode('.', self::$siteData['enabled_languages']), $installed_languages);
                        if (!empty($langDiff)) {
                            foreach ($langDiff as $language) {
                                $sql_inserts = Batch_Core::getInstance()->batch_insert_rows('site_links', $language);
                                if ($result = dbquery($sql_inserts)) {
                                    continue;
                                }
                            }
                        }
                        unset($installed_languages);

                        /*
                         * Need to run another check with email_templates because installed languages might be different.
                         */
                        $result = dbquery("SELECT template_language FROM ".DB_PREFIX."email_templates GROUP by template_language ASC");
                        if (dbrows($result) > 0) {
                            while ($data = dbarray($result)) {
                                $installed_languages[] = $data['template_language'];
                            }
                        }

                        $langDiff = array_diff(explode('.', self::$siteData['enabled_languages']), $installed_languages);
                        if (!empty($langDiff)) {
                            foreach ($langDiff as $language) {
                                $sql_inserts = Batch_Core::getInstance()->batch_insert_rows('email_templates', $language);
                                if ($result = dbquery($sql_inserts)) {
                                    continue;
                                }
                            }
                        }
                    }

                    if (\defender::safe()) {
                        require_once BASEDIR."config_temp.php";
                        require_once INCLUDES."multisite_include.php";
                        $this->installer_step(self::STEP_INFUSIONS);
                        redirect(FUSION_REQUEST);
                        //new \Authenticate(self::$userData['user_name'], self::$userData['user_password'], TRUE, FUSION_REQUEST);
                    } else {
                        $this->installer_step(self::STEP_PRIMARY_ADMIN_FORM);
                        redirect(FUSION_REQUEST);
                    }

                } else {
                    // Password and authentication is invalid
                    $this->installer_step(self::STEP_PRIMARY_ADMIN_FORM);
                    redirect(FUSION_REQUEST);
                }

            } else {
                // Redirect back to form due to invalid form input values
                $this->installer_step(self::STEP_PRIMARY_ADMIN_FORM);
                redirect(FUSION_REQUEST);
            }
        } else {
            // Redirect back to form due to missing post values
            $this->installer_step(self::STEP_PRIMARY_ADMIN_FORM);
            redirect(FUSION_REQUEST);
        }
    }

    private function validate_SiteData() {
        return [
            'sitename' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'sitename'), '', 'sitename'),
            'siteemail' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'siteemail'), '', 'siteemail'),
            'enabled_languages' => \defender::getInstance()->form_sanitizer((isset($_POST['enabled_languages']) ? $_POST['enabled_languages'] : ''),
                '', 'enabled_languages'),
            'siteusername' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'siteusername'), '', 'siteusername'),
            'default_timezone' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'default_timezone'), '', 'default_timezone')
        ];
    }

}
