<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: AdminSetup.php
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

namespace PHPFusion\Installer\Steps;

use PHPFusion\Installer\Batch;
use PHPFusion\Installer\InstallCore;
use PHPFusion\Installer\Requirements;
use PHPFusion\PasswordAuth;

class AdminSetup extends InstallCore {

    /**
     * @return string|null
     */
    public function view() {
        if (!is_writable( BASEDIR . 'config_temp.php' )) {
            $_SESSION['step'] = self::STEP_PERMISSIONS;
            die( 'Unable to create config_temp.php. Please check CHMOD for root directory (' . $_SERVER['DOCUMENT_ROOT'] . ') and try again.' );
        }

        self::$connection = self::fusionGetConfig( BASEDIR . 'config_temp.php' );
        require_once(INCLUDES . 'multisite_include.php');
        $validation = Requirements::getSystemValidation();
        if (isset( $validation[3] )) {
            if ($this->tableCheck()) {
                switch (INSTALLATION_STEP) {
                    case self::STEP_TRANSFER:
                        return $this->transfer();
                        break;
                    case self::STEP_PRIMARY_ADMIN_FORM:
                        return $this->setup();
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

    /**
     * @return string
     */
    private function transfer() {

        $content = "";

        if (isset( $_POST['transfer'] )) {

            self::$user_data = $this->validateUserData();
            self::$user_data['user_id'] = 1;

            if (self::$user_data['password1'] == self::$user_data['admin_password1']) {
                addnotice( 'danger', self::$locale['setup_5016'] );
                fusion_stop();
            }

            if (fusion_safe()) {
                $user_auth = new \PasswordAuth( self::INSTALLER_ALGO );
                $user_auth->inputNewPassword = self::$user_data['password1'];
                $user_auth->inputNewPassword2 = self::$user_data['password2'];
                switch ($user_auth->isValidNewPassword()) {
                    default:
                        self::$user_data['user_password'] = $user_auth->getNewHash();
                        self::$user_data['user_salt'] = $user_auth->getNewSalt();
                        break;
                    case 2:
                        addnotice( 'danger', self::$locale['setup_5012'] );
                        fusion_stop();
                        break;
                    case 3:
                        addnotice( 'danger', self::$locale['setup_5013'] );
                        fusion_stop();
                        break;
                }
                $admin_auth = new \PasswordAuth( self::INSTALLER_ALGO );
                $admin_auth->inputNewPassword = self::$user_data['admin_password1'];
                $admin_auth->inputNewPassword2 = self::$user_data['admin_password2'];
                switch ($admin_auth->isValidNewPassword()) {
                    default:
                        self::$user_data['user_admin_password'] = $admin_auth->getNewHash();
                        self::$user_data['user_admin_salt'] = $admin_auth->getNewSalt();
                        break;
                    case 2:
                        addnotice( 'danger', self::$locale['setup_5015'] );
                        fusion_stop();
                        break;
                    case 3:
                        addnotice( 'danger', self::$locale['setup_5017'] );
                        fusion_stop();
                        break;
                }

                if (fusion_safe()) {
                    dbquery_insert( DB_PREFIX . "users", self::$user_data, 'update' );
                    addnotice( 'success', self::$locale['setup_1217'] );

                    require_once(INCLUDES . "multisite_include.php");
                    self::installerStep( self::STEP_INTRO );
                    new \Authenticate( self::$user_data['user_name'], self::$user_data['user_password'], TRUE, filter_input( INPUT_SERVER, 'REQUEST_URI' ) );

                }
            }
        }

        $result = dbquery( "SELECT * FROM " . DB_USERS . " WHERE user_id='1'" );

        if (dbrows( $result )) {

            self::$user_data = dbarray( $result );

            $content .= "<h4 class='title'>" . self::$locale['setup_1500'] . "</h4><p>" . self::$locale['setup_1501'] . "</p\n";
            $content .= "<hr/>\n";
            $content .= form_hidden( 'transfer', '', '1' );
            $content .= form_hidden( 'user_rights', '', self::$user_data['user_rights'] );
            $content .= form_text( 'user_name', self::$locale['setup_1504'], self::$user_data['user_name'],
                [
                    'required'       => TRUE,
                    'inline'         => TRUE,
                    'maxlength'      => 30,
                    'error_text'     => self::$locale['setup_5010'],
                    'callback_check' => 'username_check'
                ]
            );
            $content .= form_text( 'user_email', self::$locale['setup_1509'], self::$user_data['user_email'], ['required' => TRUE, 'inline' => TRUE, 'type' => 'email', 'error_text' => self::$locale['setup_5020']] );
            $content .= form_text( 'password1', self::$locale['setup_1505'], '', ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'] );
            $content .= form_text( 'password2', self::$locale['setup_1506'], '', ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'] );
            $content .= form_text( 'admin_password1', self::$locale['setup_1507'], '', ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'] );
            $content .= form_text( 'admin_password2', self::$locale['setup_1508'], '', ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'] );

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

    /**
     * @return array
     */
    private function validateUserData() {
        return [
            'user_name'           => stripinput( filter_input( INPUT_POST, 'user_name' ) ),
            'user_email'          => stripinput( filter_input( INPUT_POST, 'user_email' ) ),
            'user_algo'           => self::INSTALLER_ALGO,
            'user_salt'           => '',
            'user_password'       => '',
            'user_admin_algo'     => self::INSTALLER_ALGO,
            'user_admin_salt'     => '',
            'user_admin_password' => '',
            'password1'           => stripinput( filter_input( INPUT_POST, 'password1' ) ),
            'password2'           => stripinput( filter_input( INPUT_POST, 'password2' ) ),
            'admin_password1'     => stripinput( filter_input( INPUT_POST, 'admin_password1' ) ),
            'admin_password2'     => stripinput( filter_input( INPUT_POST, 'admin_password2' ) ),
            'user_rights'         => isset( $_POST['transfer'] ) ? stripinput( filter_input( INPUT_POST, 'user_rights' ) ) : self::USER_RIGHTS_SA,
            'user_hide_email'     => 1,
            'user_timezone'       => stripinput( filter_input( INPUT_POST, 'user_timezone' ) ),
            'user_joined'         => time(),
            'user_lastvisit'      => time(),
            'user_ip'             => USER_IP,
            'user_level'          => USER_LEVEL_SUPER_ADMIN,
            'user_status'         => '0',
            'user_theme'          => 'Default',
            'user_birthdate'      => '1900-01-01',
            'user_threads'        => '',
            'user_groups'         => '',
            'user_sig'            => ''
        ];
    }

    private function setup() {

        self::$site_data = [
            'sitename'     => fusion_get_settings( 'sitename' ),
            'siteemail'    => fusion_get_settings( 'siteemail' ),
            'siteusername' => fusion_get_settings( 'siteusername' ),
            'timeoffset'   => fusion_get_settings( 'timeoffset' ),
        ];

        $this->update();

        // Should have a db connection now
        $content = "<h4 class='title'>" . self::$locale['setup_1212'] . "</h4><p>" . self::$locale['setup_1213'] . "</p>\n";
        $content .= "<hr/>\n";

        $content .= rendernotices( getnotices() );

        $content .= form_text( 'sitename', self::$locale['setup_1214'], self::$site_data['sitename'],
            [
                'inline' => TRUE, 'required' => TRUE, 'placeholder' => self::$locale['setup_1215'], 'class' => 'form-group-lg'
            ] );
        $content .= form_text( 'siteemail', self::$locale['setup_1510'], self::$site_data['siteemail'],
            ['inline' => TRUE, 'required' => TRUE, 'type' => 'email'] );
        $content .= form_text( 'siteusername', self::$locale['setup_1513'], self::$site_data['siteusername'],
            [
                'required'   => TRUE,
                'inline'     => TRUE,
                'maxlength'  => 30,
                'error_text' => self::$locale['setup_5011']
            ]
        );

        $json_file = @file_get_contents( INCLUDES . 'geomap/timezones.json', FALSE );
        $timezones_json = json_decode( $json_file, TRUE );
        $timezone_array = [];
        foreach ($timezones_json as $zone => $zone_city) {
            $date = new \DateTime( 'now', new \DateTimeZone( $zone ) );
            $offset = $date->getOffset() / 3600;
            $timezone_array[$zone] = '(GMT' . ($offset < 0 ? $offset : '+' . $offset) . ') ' . $zone_city;
        }

        $content .= form_select( 'timeoffset', self::$locale['setup_1511'], self::$site_data['timeoffset'], ['options' => $timezone_array, 'required' => TRUE, 'inline' => TRUE, 'width' => '100%', 'inner_width' => '100%'] );

        $content .= "<h4 class='title'>" . self::$locale['setup_1500'] . "</h4><p>" . self::$locale['setup_1501'] . "</p>\n";
        $content .= "<hr />\n";

        $content .= form_text( 'user_name', self::$locale['setup_1504'], self::$user_data['user_name'],
            [
                'required'       => TRUE,
                'inline'         => TRUE,
                'maxlength'      => 30,
                'error_text'     => self::$locale['setup_5010'],
                'callback_check' => 'username_check'
            ]
        );
        $content .= form_text( 'password1', self::$locale['setup_1505'], self::$user_data['password1'],
            ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password', 'error_text' => ''] );
        $content .= form_text( 'password2', self::$locale['setup_1506'], self::$user_data['password2'],
            ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password', 'error_text' => ''] );
        $content .= form_text( 'admin_password1', self::$locale['setup_1507'], self::$user_data['admin_password1'],
            ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password', 'error_text' => ''] );
        $content .= form_text( 'admin_password2', self::$locale['setup_1508'], self::$user_data['admin_password2'],
            ['required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password', 'error_text' => ''] );
        $content .= form_text( 'user_email', self::$locale['setup_1509'], self::$user_data['user_email'],
            ['required' => TRUE, 'inline' => TRUE, 'type' => 'email', 'error_text' => self::$locale['setup_5020']] );

        $content .= "<h4 class='title'>" . self::$locale['setup_1512'] . "</h4><p>" . self::$locale['setup_1001'] . "</p>\n";
        $content .= "<hr />\n";

        $checkbox_options = [];
        foreach (self::$locale_files as $languageKey => $languageName) {
            $localeFlagPath = BASEDIR . "locale/" . $languageKey . "/$languageKey-s.png";
            $checkbox_options[$languageKey] = "<img src='" . $localeFlagPath . "' class='m-l-15' alt='$languageName'/> $languageName";
        }
        $content .= form_checkbox( 'enabled_languages[]', '',
            !empty( self::$site_data['enabled_languages'] ) ? self::$site_data['enabled_languages'] : self::$localeset, [
                'required'       => TRUE,
                'reverse_label'  => TRUE,
                'class'          => 'm-0 p-0 input-md',
                'options'        => $checkbox_options,
                'deactivate_key' => self::$localeset,
                'delimiter'      => '.' // Refer to L1051, L1060 and fusion_get_enabled_languages(); it's '.'
            ] );

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

        if (isset( $_POST['sitename'] ) && isset( $_POST['siteemail'] ) && isset( $_POST['user_name'] )) {

            function username_check( $username ) {
                return !preg_match( "/^[-0-9A-Z_@\s]+$/i", $username );
            }

            self::$site_data = $this->validateSiteData();

            self::$user_data = $this->validateUserData();

            if (self::$user_data['password1'] == self::$user_data['admin_password1']) {
                fusion_stop();
                addnotice( 'danger', self::$locale['setup_5016'] );
            }

            if (fusion_safe()) {

                $user_auth = new PasswordAuth( self::INSTALLER_ALGO );

                $user_auth->inputNewPassword = self::$user_data['password1'];
                $user_auth->inputNewPassword2 = self::$user_data['password2'];

                switch ($user_auth->isValidNewPassword()) {
                    default:
                        self::$user_data['user_password'] = $user_auth->getNewHash();
                        self::$user_data['user_salt'] = $user_auth->getNewSalt();
                        break;
                    case 2:
                        fusion_stop();
                        \Defender::setInputError( 'password2' );
                        addnotice( 'danger', self::$locale['setup_5012'] );

                        break;
                    case 3:
                        fusion_stop();
                        \Defender::setInputError( 'password1' );
                        addnotice( 'danger', self::$locale['setup_5013'] );
                        break;
                }

                $admin_auth = new \PasswordAuth( self::INSTALLER_ALGO );
                $admin_auth->inputNewPassword = self::$user_data['admin_password1'];
                $admin_auth->inputNewPassword2 = self::$user_data['admin_password2'];
                switch ($admin_auth->isValidNewPassword()) {
                    default:
                        self::$user_data['user_admin_password'] = $admin_auth->getNewHash();
                        self::$user_data['user_admin_salt'] = $admin_auth->getNewSalt();
                        break;
                    case 2:
                        fusion_stop();
                        \Defender::setInputError( 'admin_password2' );
                        addnotice( 'danger', self::$locale['setup_5015'] );
                        break;
                    case 3:
                        fusion_stop();
                        \Defender::setInputError( 'admin_password1' );
                        addnotice( 'danger', self::$locale['setup_5017'] );
                        break;
                }

                if (fusion_safe()) {

                    self::$user_data['user_timezone'] = self::$site_data['timeoffset'];
                    $batch_core = Batch::getInstance();

                    // Create Super Admin
                    if (dbcount( "(user_id)", DB_PREFIX . "users", "user_id='1'" )) {
                        self::$user_data['user_id'] = 1;

                        dbquery_insert( DB_PREFIX . "users", self::$user_data, 'update' );

                        dbquery_insert( DB_PREFIX . 'user_settings', self::$user_data, 'update', ['no_primary' => TRUE, 'primary_key' => 'user_id'] );

                    } else {

                        self::$user_data['user_id'] = dbquery_insert( DB_PREFIX . "users", self::$user_data, 'save' );

                        dbquery_insert( DB_PREFIX . 'user_settings', self::$user_data, 'save', ['no_primary' => TRUE, 'primary_key' => 'user_id'] );
                    }

                    // Update Site Settings
                    dbquery( "UPDATE " . DB_PREFIX . "settings SET settings_value='" . self::$site_data['sitename'] . "' WHERE settings_name='sitename'" );
                    dbquery( "UPDATE " . DB_PREFIX . "settings SET settings_value='" . self::$site_data['siteemail'] . "' WHERE settings_name='siteemail'" );
                    dbquery( "UPDATE " . DB_PREFIX . "settings SET settings_value='" . self::$site_data['enabled_languages'] . "' WHERE settings_name='enabled_languages'" );
                    dbquery( "UPDATE " . DB_PREFIX . "settings SET settings_value='" . self::$site_data['timeoffset'] . "' WHERE settings_name='timeoffset'" );
                    dbquery( "UPDATE " . DB_PREFIX . "settings SET settings_value='" . self::$site_data['siteusername'] . "' WHERE settings_name='siteusername'" );

                    if (strpos( self::$site_data['enabled_languages'], '.' )) {

                        // Update all existing panel and update new enabled language values
                        dbquery( "UPDATE " . DB_PREFIX . "panels SET panel_languages='" . self::$site_data['enabled_languages'] . "'" );

                        $result = dbquery( "SELECT distinct link_language FROM " . DB_PREFIX . "site_links" );
                        $installed_languages = [];
                        if (dbrows( $result ) > 0) {
                            while ($data = dbarray( $result )) {
                                $installed_languages[] = $data['link_language'];
                            }
                        }

                        $langDiff = array_diff( self::$site_data['enabled_languages'], $installed_languages );
                        if (!empty( $langDiff )) {
                            foreach ($langDiff as $language) {
                                $sql_inserts = $batch_core::batchInsertRows( 'site_links', $language );
                                dbquery( $sql_inserts );
                            }
                        }
                        unset( $installed_languages );

                        $result = dbquery( "SELECT distinct admin_language FROM " . DB_PREFIX . "admin" );
                        $installed_languages = [];
                        if (dbrows( $result ) > 0) {
                            while ($data = dbarray( $result )) {
                                $installed_languages[] = $data['admin_language'];
                            }
                        }

                        $langDiff = array_diff( self::$site_data['enabled_languages'], $installed_languages );
                        if (!empty( $langDiff )) {
                            foreach ($langDiff as $language) {
                                $sql_inserts = $batch_core::batchInsertRows( 'admin', $language );
                                dbquery( $sql_inserts );
                            }
                        }
                        unset( $installed_languages );

                        /*
                         * Need to run another check with email_templates because installed languages might be different.
                         */
                        $result = dbquery( "SELECT distinct template_language FROM " . DB_PREFIX . "email_templates" );
                        $installed_languages = [];
                        if (dbrows( $result ) > 0) {
                            while ($data = dbarray( $result )) {
                                $installed_languages[] = $data['template_language'];
                            }
                        }

                        $langDiff = array_diff( self::$site_data['enabled_languages'], $installed_languages );
                        if (!empty( $langDiff )) {

                            foreach ($langDiff as $language) {
                                $sql_inserts = $batch_core::batchInsertRows( 'email_templates', $language );
                                dbquery( $sql_inserts );
                            }

                            // Update all UF Cat Fields
                            $ufc_result = dbquery( "SELECT field_cat_id, field_cat_name FROM " . DB_PREFIX . "user_field_cats" );
                            if (dbrows( $result ) && is_array( $langDiff ) && count( $langDiff )) {
                                $locale_keys = array_flip( self::$site_data['enabled_languages'] );
                                while ($ufc_data = dbarray( $ufc_result )) {
                                    $category_name[self::$localeset] = $ufc_data['field_cat_name'];
                                    // get current locale key
                                    if (isset( $locale_keys[$ufc_data['field_cat_name']] )) {
                                        $lang_key = $locale_keys[$ufc_data['field_cat_name']];
                                        foreach ($langDiff as $language) {
                                            $locale = [];
                                            include LOCALE . $language . '/setup.php';
                                            $category_name[$language] = $locale[$lang_key]; // bind language = translations value
                                        }
                                    }
                                    if (!empty( $category_name )) {
                                        $new_field_cat_name = serialize( $category_name );
                                        dbquery( "UPDATE " . DB_PREFIX . "user_field_cats SET field_cat_name=:field_cat_value WHERE field_cat_id=:field_cat_id", [':field_cat_value' => $new_field_cat_name, ':field_cat_id' => $ufc_data['field_cat_id']] );
                                    }
                                }
                            }
                        }
                    }

                    if (fusion_safe()) {
                        require_once BASEDIR . "config_temp.php";
                        require_once INCLUDES . "multisite_include.php";
                        self::installerStep( self::STEP_INFUSIONS );
                        //new \Authenticate(self::$userData['user_name'], self::$userData['user_password'], TRUE, FUSION_REQUEST);
                    } else {
                        self::installerStep( self::STEP_PRIMARY_ADMIN_FORM );
                    }
                    redirect( FUSION_REQUEST );
                }
            }
        }
    }

    private function validateSiteData() {
        return [
            'sitename'          => sanitizer( 'sitename', '', 'sitename' ),
            'siteemail'         => sanitizer( 'siteemail', '', 'siteemail' ),
            'enabled_languages' => sanitizer( ['enabled_languages'], LANGUAGE, 'enabled_languages' ),
            'siteusername'      => sanitizer( 'siteusername', '', 'siteusername' ),
            'timeoffset'        => sanitizer( 'timeoffset', '', 'timeoffset' )
        ];
    }
}
