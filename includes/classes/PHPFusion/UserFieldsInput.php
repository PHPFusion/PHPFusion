<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: UserFieldsInput.php
| Author: Hans Kristian Flaatten (Starefossen), meangczac (Chan)
| Lead Developer PHPFusion, Core Developer Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

use Defender;

use PHPFusion\Userfields\Accounts\AccountsValidate;
use PHPFusion\Userfields\Notifications\NotificationsValidate;
use PHPFusion\Userfields\Privacy\PrivacyValidate;
use PHPFusion\Userfields\UserFieldsValidate;

/**
 * Class UserFieldsInput
 *
 * @package PHPFusion
 */
class UserFieldsInput {

    private $_quantum = NULL;

    public $adminActivation = FALSE;

    public $emailVerification = FALSE;

    public $verifyNewEmail = FALSE;

    public $userData = ['user_name' => NULL];

    public $validation = 0;

    public $registration = FALSE;

    // On insert or admin edit
    public $skipCurrentPass = FALSE; // FALSE to skip pass. True to validate password. New Register always FALSE.

    public $isAdminPanel = FALSE;

    public $_method;

    private $_userEmail;

    private $_userName;

    // Passwords
    private $data = [];

    private $_newUserPassword = FALSE;

    public $username_change = TRUE;

    public $moderation = 0;

    /**
     * Save User Fields
     *
     * @return bool
     */
    public function saveInsert() {

        $settings = fusion_get_settings();

        $locale = fusion_get_locale();

        $this->_method = "validate_insert";

        $this->data = $this->setEmptyFields();

        $this->userData = $this->setEmptyFields();

        $userFieldsValidate = new AccountsValidate( $this );

        $this->data['user_name'] = $userFieldsValidate->setUserName();

        if ($pass = $userFieldsValidate->setPassword()) {
            if (count( $pass ) === 3) {
                list( $this->data['user_algo'], $this->data['user_salt'], $this->data['user_password'] ) = $pass;
            }
        }

        $this->data['user_email'] = $userFieldsValidate->setUserEmail();

        /**
         * For validation purposes only to show required field errors
         *
         * @todo - look further for optimization
         */
        if ($_input = $this->setCustomUserFields()) {
            foreach ($_input as $input) {
                $this->data += $input;
            }
        }

        if ($this->validation == 1) {
            $this->verifyCaptchas();
        }

//        print_p( $this->userData );
//        print_p( 'Email verify: ' . $this->emailVerification );
//        print_p( 'Admin verify: ' . $this->adminActivation );
//        print_p( $this->data );
        if (fusion_safe()) {

            if ($this->emailVerification) {

                $this->sendEmailVerification();

            } else {

                $insert_id = dbquery_insert( DB_USERS, $this->data, 'save' );

                dbquery_insert( DB_USER_SETTINGS, ['user_id' => $insert_id], 'save', ['no_unique' => TRUE, 'primary_key' => 'user_id'] );

                /**
                 * Create user
                 */
                $notice = $locale['u160'] . " - " . $locale['u161'];

                if ($this->moderation == 1) {

                    $this->sendAdminRegistrationMail();

                } else {
                    // got admin activation and not
                    if ($this->adminActivation) {
                        // Missing registration data?
                        $notice = $locale['u160'] . " - " . $locale['u162'];
                    }
                }

                addnotice( 'success', $notice, $settings['opening_page'] );
            }

//            $this->data['new_password'] = $this->getPasswordInput( 'user_password1' );
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Send mail when an administrator adds a user from admin panel
     */
    function sendAdminRegistrationMail() {

        $settings = fusion_get_settings();
        $locale = fusion_get_locale( '', LOCALE . LOCALESET . "admin/members_email.php" );

        require_once INCLUDES . "sendmail_include.php";

        $subject = str_replace( "[SITENAME]", $settings['sitename'], $locale['email_create_subject'] );

        $replace_this = ["[USER_NAME]", "[PASSWORD]", "[SITENAME]", "[SITEUSERNAME]"];

        $replace_with = [
            $this->_userName, $this->_newUserPassword, $settings['sitename'], $settings['siteusername']
        ];

        $message = str_replace( $replace_this, $replace_with, $locale['email_create_message'] );

        sendemail( $this->data['user_name'], $this->data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message );

        // Administrator complete message
        addnotice( 'success', $locale['u172'] );
    }


    /**
     * Initialise empty fields
     *
     * @return array
     */
    private function setEmptyFields() {

        $userStatus = $this->adminActivation == 1 ? 2 : 0;

        /** Prepare initial variables for settings */
        if ($this->_method == "validate_insert") {

            $forum_settings = [];
            if (defined( 'FORUM_EXISTS' )) {
                $forum_settings = get_settings( 'forum' );
            }

            // Compulsory Core Fields
            return [
                'user_id'         => 0,
                'user_name'       => '',
                'user_email'      => '',
                'user_hide_email' => 1,
                'user_avatar'     => '',
                'user_posts'      => 0,
                'user_threads'    => 0,
                'user_joined'     => time(),
                'user_lastvisit'  => 0,
                'user_ip'         => USER_IP,
                'user_ip_type'    => USER_IP_TYPE,
                'user_rights'     => '',
                'user_groups'     => '',
                'user_level'      => USER_LEVEL_MEMBER,
                'user_status'     => $userStatus,
                'user_theme'      => 'Default',
                'user_language'   => LANGUAGE,
                'user_timezone'   => fusion_get_settings( 'timeoffset' ),
                'user_reputation' => $forum_settings['default_points'] ?? ''
            ];

        } else {
            return NULL;
        }
    }

    /**
     * Set validation error
     */
    private function verifyCaptchas() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $_CAPTCHA_IS_VALID = FALSE;
        include INCLUDES . "captchas/" . $settings['captcha'] . "/captcha_check.php";
        if ($_CAPTCHA_IS_VALID == FALSE) {
            fusion_stop( $locale['u194'] );
            Defender::setInputError( 'user_captcha' );
        }
    }

    /**
     * Handle new email verification procedures
     */
    public function verifyNewEmail() {

        $settings = fusion_get_settings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();

        require_once INCLUDES . "sendmail_include.php";
        mt_srand( (double)microtime() * 1000000 );

        $salt = "";
        for ($i = 0; $i <= 10; $i++) {
            $salt .= chr( rand( 97, 122 ) );
        }

        $user_code = md5( $this->_userEmail . $salt );

        $email_verify_link = $settings['siteurl'] . "edit_profile.php?code=" . $user_code;

        $mailbody = str_replace( "[EMAIL_VERIFY_LINK]", $email_verify_link, $locale['u203'] );
        $mailbody = str_replace( "[SITENAME]", $settings['sitename'], $mailbody );
        $mailbody = str_replace( "[SITEUSERNAME]", $settings['siteusername'], $mailbody );
        $mailbody = str_replace( "[USER_NAME]", $userdata['user_name'], $mailbody );

        $mailSubject = str_replace( "[SITENAME]", $settings['sitename'], $locale['u202'] );

        sendemail( $this->data['user_name'], $this->data['user_email'], $settings['siteusername'], $settings['siteemail'], $mailSubject, $mailbody );

        addnotice( 'warning', strtr( $locale['u200'], ['(%s)' => $this->_userEmail] ) );

        dbquery( "DELETE FROM " . DB_EMAIL_VERIFY . " WHERE user_id=:uid", [":uid" => (int)$this->data['user_id']] );

        dbquery( "INSERT INTO " . DB_EMAIL_VERIFY . " (user_id, user_code, user_email, user_datestamp) VALUES (':uid', ':code', ':email', ':time')", [
            ':uid'   => (int)$this->data['user_id'],
            ':code'  => $user_code,
            ':email' => $this->data['user_email'],
            ':time'  => time()
        ] );
    }

    /**
     * Handle request for email verification
     * Sends Verification code when you change email
     * Sends Verification code when you register
     */
    private function sendEmailVerification() {

        $settings = fusion_get_settings();
        $locale = fusion_get_locale();

        require_once INCLUDES . "sendmail_include.php";

        $userCode = hash_hmac( "sha1", PasswordAuth::getNewPassword(), $this->data['user_email'] );
        $activationUrl = $settings['siteurl'] . "register.php?email=" . $this->data['user_email'] . "&code=" . $userCode;

        $message = str_replace( "USER_NAME", $this->data['user_name'], $locale['u152'] );
        $message = str_replace( "SITENAME", $settings['sitename'], $message );
        $message = str_replace( "SITEUSERNAME", $settings['siteusername'], $message );
        $message = str_replace( "USER_PASSWORD", $this->_newUserPassword, $message );
        $message = str_replace( "ACTIVATION_LINK", $activationUrl, $message );

        $subject = str_replace( "[SITENAME]", $settings['sitename'], $locale['u151'] );

        if (!sendemail( $this->data['user_name'], $this->data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message )) {

            $message = strtr( $locale['u154'], [
                '[LINK]'  => "<a href='" . BASEDIR . "contact.php'><strong>",
                '[/LINK]' => "</strong></a>"
            ] );

            addnotice( 'warning', $locale['u153'] . "<br />" . $message, 'all' );
        }

        if (fusion_safe()) {

            $email_rows = [
                'user_code'      => $userCode,
                'user_name'      => $this->data['user_name'],
                'user_email'     => $this->data['user_email'],
                'user_datestamp' => time(),
                'user_info'      => base64_encode( serialize( $this->data ) )
            ];

            dbquery_insert( DB_NEW_USERS, $email_rows, 'save', ['primary_key' => 'user_name', 'no_unique' => TRUE] );
        }

        addnotice( 'success', $locale['u150'] );
    }

    /**
     * Update User Fields
     *
     * @return bool
     */
    public function saveUpdate() {

        $this->data['user_id'] = $this->userData['user_id'];
        $this->_method = 'validate_update';

        return match (get( 'section' )) {
            default => $this->updateAccount(),
            'notifications' => $this->updateNotifications(),
            'privacy' => (new PrivacyValidate( $this ))->validate(),
        };

    }

    /**
     * Update account settings for users
     *
     * @return bool
     */
    private function updateAccount() {

        if (check_post( 'update_profile_btn' )) {

            $locale = fusion_get_locale();

            $userFieldsValidate = new AccountsValidate( $this );

            $callback_function = [
                /**
                 * @uses \PHPFusion\Userfields\Accounts\AccountsValidate::setUserName()
                 * @uses \PHPFusion\Userfields\Accounts\AccountsValidate::setUserEmail()
                 * @uses \PHPFusion\Userfields\Accounts\AccountsValidate::sanitizer()
                 */
                'user_name'      => 'setUserName',
                'user_firstname' => 'sanitizer',
                'user_lastname'  => 'sanitizer',
                'user_addname'   => 'sanitizer',
                'user_phone'     => 'sanitizer',
                'user_email'     => 'setUserEmail',
                'user_bio'       => 'sanitizer',
            ];

            foreach ($callback_function as $fieldname => $functions) {
                if (check_post( $fieldname )) {
                    $value = $userFieldsValidate->$functions( $fieldname );
                    if (fusion_safe()) {
                        $this->data[$fieldname] = $value;
                    }
                }
            }

            if (isset( $this->data['user_phone'] )) {
                $this->data['user_hide_phone'] = (int)check_post( 'user_hide_phone' );
            }

            if (isset( $this->data['user_email'] )) {
                $this->data['user_hide_email'] = (int)check_post( 'user_hide_email' );
            }

            // Set password
            if (check_post( 'user_password1' )) {
                if ($pass = $userFieldsValidate->setPassword()) {
                    if (count( $pass ) === 3) {
                        list( $this->data['user_algo'], $this->data['user_salt'], $this->data['user_password'] ) = $pass;
                    }
                }
            }

            // Set admin password
            if (check_post( 'user_admin_password1' )) {
                if ($admin_pass = $userFieldsValidate->setAdminPassword()) {
                    if (count( $admin_pass ) === 3) {
                        list( $this->data['user_admin_algo'], $this->data['user_admin_salt'], $this->data['user_admin_password'] ) = $admin_pass;
                    }
                }
            }

//        $this->setUserAvatar();

            if ($this->validation) {
                $this->verifyCaptchas();
            }

            // this has got problem, they are all jumbled up.
            if ($_input = $this->setCustomUserFields()) {
                foreach ($_input as $input) {
                    $this->data += $input;
                }
            }

            // id request spoofing request
            if ($this->getAccess()) {

                if (fusion_safe()) {

                    // Log username change
                    if (!empty( $this->data['user_name'] )) {
                        if ($this->data['user_name'] !== $this->userData['user_name']) {
                            save_user_log( $this->userData['user_id'], 'user_name', $this->data['user_name'], $this->userData['user_name'] );
                        }
                    }
                    // Log email change
                    if (!empty( $this->data['user_email'] )) {
                        if ($this->data['user_email'] !== $this->userData['user_email']) {
                            save_user_log( $this->userData['user_id'], 'user_email', $this->data['user_email'], $this->userData['user_email'] );
                        }
                    }

                    // Logs Field changes
                    $this->_quantum->logUserAction( DB_USERS, "user_id" );

                    // Update Table
                    dbquery_insert( DB_USERS, $this->data, 'update' );

                    dbquery_insert( DB_USER_SETTINGS, $this->data, 'update', ['primary_key' => 'user_id'] );
//                if ($this->moderation && !empty( $pass ) && $this->_newUserPassword && $this->_newUserPassword2) {
//                    // inform user that password has changed. and tell him your new password
//                    include INCLUDES . 'sendmail_include.php';
//
//                    $input = [
//                        "mailname" => $this->userData['user_name'],
//                        "email"    => $this->userData['user_email'],
//                        "subject"  => str_replace( "[SITENAME]", $settings['sitename'], $locale['global_456'] ),
//                        "message"  => str_replace(
//                            [
//                                "[SITENAME]",
//                                "[SITEUSERNAME]",
//                                "USER_NAME",
//                                "[PASSWORD]"
//                            ],
//                            [
//                                $settings['sitename'],
//                                $settings['siteusername'],
//                                $this->userData['user_name'],
//                                $this->_newUserPassword,
//                            ],
//                            $locale['global_457']
//                        )
//                    ];
//
//                    if (!sendemail( $input['mailname'], $input['email'], $settings['siteusername'], $settings['siteemail'], $input['subject'],
//                        $input['message'] )
//                    ) {
//                        addnotice( 'warning', str_replace( "USER_NAME", $this->userData['user_name'], $locale['global_459'] ) );
//                    } else {
//                        addnotice( "success", str_replace( "USER_NAME", $this->userData['user_name'], $locale['global_458'] ) );
//                    }
//                    return FALSE;
//                }

                    addnotice( 'success', $locale['u163'] );

                    return TRUE;
                }

            } else {
                fusion_stop();
                addnotice( 'danger', $locale['error_request'] );
            }
        }


        return FALSE;
    }

    /**
     * Update notifications settings for users
     *
     * @return bool
     */
    private function updateNotifications() {

        if (check_post( 'save_notify' )) {

            $rows = (new NotificationsValidate( $this ))->validate();

            if ($this->getAccess()) {

                if (fusion_safe()) {

                    dbquery_insert( DB_USER_SETTINGS, $rows, 'update', ['no_unique' => TRUE, 'primary_key' => 'user_id'] );

                    $locale = fusion_get_locale();
                    addnotice( 'success', $locale['u521'] );

                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * @return bool
     */
    public function getAccess() {
        return ((iADMIN && checkrights( 'M' ) && ($this->userData['user_password'] == sanitizer( 'user_hash', '', "user_hash" ))) || ($this->data['user_id'] == $this->userData['user_id']));
    }

    /**
     * @return array
     */
    private function setCustomUserFields() {

        $this->_quantum = new QuantumFields();
        $this->_quantum->setFieldDb( DB_USER_FIELDS );
        $this->_quantum->setPluginFolder( INCLUDES . "user_fields/" );
        $this->_quantum->setPluginLocaleFolder( LOCALE . LOCALESET . "user_fields/" );
        $this->_quantum->loadFields();
        $this->_quantum->loadFieldCats();
        $this->_quantum->setCallbackData( $this->data );

        return $this->_quantum->returnFieldsInput( DB_USERS, 'user_id' );
    }

    /**
     * Set user avatar
     */
    private function setUserAvatar() {
        if (isset( $_POST['delAvatar'] )) {
            if ($this->userData['user_avatar'] != "" && file_exists( IMAGES . "avatars/" . $this->userData['user_avatar'] ) && is_file( IMAGES . "avatars/" . $this->userData['user_avatar'] )) {
                unlink( IMAGES . "avatars/" . $this->userData['user_avatar'] );
            }
            $this->data['user_avatar'] = '';
        }
        if (isset( $_FILES['user_avatar'] ) && $_FILES['user_avatar']['name']) { // uploaded avatar
            if (!empty( $_FILES['user_avatar'] ) && is_uploaded_file( $_FILES['user_avatar']['tmp_name'] )) {
                $upload = form_sanitizer( $_FILES['user_avatar'], '', 'user_avatar' );
                if (isset( $upload['error'] ) && !$upload['error']) {
                    // ^ maybe use empty($upload['error']) also can but maybe low end php version has problem on empty.
                    $this->data['user_avatar'] = $upload['image_name'];
                }
            }
        }
    }

    /**
     * Returns userhash added userdata array
     *
     * @return array
     */
    public function setUserHash() {
        if (!empty( $this->userData['user_password'] )) {
            // when edit profile
            $this->data['user_hash'] = $this->userData['user_password'];
        } else if (isset( $_POST['user_hash'] )) {
            // when new registration
            $this->data['user_hash'] = sanitizer( 'user_hash', '', 'user_hash' );
        }

        return $this->data;
    }

    /**
     * @param string $value
     */
    public function verifyCode( $value ) {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        if (!preg_check( "/^[0-9a-z]{32}$/i", $value )) {
            redirect( BASEDIR . 'index.php' );
        }
        $result = dbquery( "SELECT * FROM " . DB_EMAIL_VERIFY . " WHERE user_code=:usercode", [':usercode' => $value] );
        if (dbrows( $result )) {
            $data = dbarray( $result );
            if ($data['user_id'] == $userdata['user_id']) {
                if ($data['user_email'] != $userdata['user_email']) {
                    $result = dbquery( "SELECT user_email FROM " . DB_USERS . " WHERE user_email=:useremail", [':useremail' => $data['user_email']] );
                    if (dbrows( $result ) > 0) {
                        addnotice( "danger", $locale['u164'] . "<br />\n" . $locale['u121'] );
                    } else {

                        addnotice( 'success', $locale['u169'] );
                    }
                    dbquery( "UPDATE " . DB_USERS . " SET user_email='" . $data['user_email'] . "' WHERE user_id='" . $data['user_id'] . "'" );
                    dbquery( "DELETE FROM " . DB_EMAIL_VERIFY . " WHERE user_id='" . $data['user_id'] . "'" );
                }
            } else {
                redirect( BASEDIR . 'index.php' );
            }
        } else {
            redirect( BASEDIR . 'index.php' );
        }
    }


}
