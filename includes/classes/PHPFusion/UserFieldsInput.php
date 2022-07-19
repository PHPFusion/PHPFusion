<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: UserFieldsInput.php
| Author: Hans Kristian Flaatten (Starefossen)
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

/**
 * Class UserFieldsInput
 *
 * @package PHPFusion
 */
class UserFieldsInput {

    public $adminActivation = 1;

    public $emailVerification = 1;

    public $verifyNewEmail = FALSE;

    public $userData = ['user_name' => NULL];

    public $validation = 0;

    public $registration = FALSE;

    // On insert or admin edit
    public $skipCurrentPass = FALSE; // FALSE to skip pass. True to validate password. New Register always FALSE.

    public $isAdminPanel = FALSE;

    private $_completeMessage;

    private $_method;

    private $_userEmail;

    private $_userName;

    // Passwords
    private $data = [];
    private $_isValidCurrentPassword = FALSE;
    private $_newUserPassword = FALSE;
    private $_newUserPassword2 = FALSE;

    private $username_change = TRUE;

    private $_themeChanged = FALSE;

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

        if ($this->username_change) {

            $this->setUserName();
        }

        $this->setPassword();

        $this->setUserEmail();

        /**
         * For validation purposes only to show required field errors
         *
         * @todo - look further for optimization
         */
        $quantum = new QuantumFields();
        $quantum->setCategoryDb(DB_USER_FIELD_CATS);
        $quantum->setFieldDb(DB_USER_FIELDS);
        $quantum->setPluginFolder(INCLUDES."user_fields/");
        $quantum->setPluginLocaleFolder(LOCALE.LOCALESET."user_fields/");
        $quantum->loadFields();
        $quantum->loadFieldCats();
        $quantum->setCallbackData($this->data);

        $fields_input = $quantum->returnFieldsInput(DB_USERS, 'user_id');

        if (!empty($fields_input)) {
            foreach ($fields_input as $fields_array) {
                $this->data += $fields_array;
            }
        }

        if ($this->validation == 1) {
            $this->setValidationError();
        }

        if (fusion_safe()) {

            if ($this->emailVerification) {

                $this->setEmailVerification();

            } else {

                /**
                 * Create user
                 */
                dbquery_insert(DB_USERS, $this->data, 'save', ['keep_session' => TRUE]);
                $this->_completeMessage = $locale['u160']." - ".$locale['u161'];

                if (defined("ADMIN_PANEL")) {
                    $aidlink = fusion_get_aidlink();
                    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/members_email.php");
                    require_once INCLUDES."sendmail_include.php";
                    $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['email_create_subject']);
                    $replace_this = ["[USER_NAME]", "[PASSWORD]", "[SITENAME]", "[SITEUSERNAME]"];
                    $replace_with = [
                        $this->_userName, $this->_newUserPassword, $settings['sitename'], $settings['siteusername']
                    ];
                    $message = str_replace($replace_this, $replace_with, $locale['email_create_message']);
                    sendemail($this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'],
                        $subject, $message);

                    // Administrator complete message
                    $this->_completeMessage = $locale['u172'];
                    unset($aidlink);

                } else {
                    // got admin activation and not
                    if ($this->adminActivation) {
                        $this->_completeMessage = $locale['u160']." - ".$locale['u162'];
                    }
                }

            }
            $this->data['new_password'] = $this->getPasswordInput('user_password1');

            if ($this->_completeMessage) {
                addnotice("info", $this->_completeMessage, fusion_get_settings("opening_page"));
            }

            return TRUE;
        }

        return FALSE;
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
            if (defined('FORUM_EXISTS')) {
                $forum_settings = get_settings('forum');
            }

            // Compulsory Core Fields
            return [
                'user_id'         => 0,
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
                'user_timezone'   => fusion_get_settings('timeoffset'),
                'user_reputation' => (!empty($forum_settings['default_points']) ? $forum_settings['default_points'] : '')
            ];

        } else {
            return NULL;
        }
    }

    /**
     * Handle Username Input and Validation
     */
    private function setUserName() {

        $locale = fusion_get_locale();

        $defender = Defender::getInstance();

        if (post("user_name")) {

            $this->_userName = sanitizer("user_name", "", "user_name");

            if (!empty($this->_userName)) {

                $uban = explode(',', fusion_get_settings('username_ban'));

                if (!defined('ADMIN_PANEL') && $this->registration) {
                    $this->userData["user_name"] = fusion_get_userdata("user_name");
                }

                if ($this->_userName != $this->userData['user_name']) {

                    if (!preg_match('/^[-a-z\p{L}\p{N}_]*$/ui', $this->_userName)) {

                        // Check for invalid characters
                        fusion_stop();

                        $defender::setInputError('user_name');
                        $defender::setErrorText('user_name', $locale['u120']);

                    } else if (in_array($this->_userName, $uban)) {

                        // Check for prohibited usernames
                        fusion_stop();

                        $defender::setInputError('user_name');
                        $defender::setErrorText('user_name', $locale['u119']);

                    } else {

                        // Make sure the username is not used already
                        $name_active = dbcount("(user_id)", DB_USERS, "user_name='".$this->_userName."'");
                        $name_inactive = dbcount("(user_code)", DB_NEW_USERS, "user_name='".$this->_userName."'");

                        if ($name_active == 0 && $name_inactive == 0) {

                            $this->data['user_name'] = $this->_userName;

                        } else {

                            fusion_stop();

                            $defender::setInputError('user_name');

                            $defender::setErrorText('user_name', $locale['u121']);
                        }
                    }

                } else {

                    if ($this->_method == 'validate_update') {
                        $this->data['user_name'] = $this->_userName;
                    }

                }
            }

        } else {

            $defender::setErrorText('user_name', $locale['u122']);
            $defender::setInputError('user_name');
        }
    }

    /**
     * Handle User Password Input and Validation
     */
    private function setPassword() {

        $locale = fusion_get_locale();

        if ($this->_method == 'validate_insert') {

            $this->_newUserPassword = self::getPasswordInput('user_password1');

            $this->_newUserPassword2 = self::getPasswordInput('user_password2');

            if (!empty($this->_newUserPassword)) {

                $passAuth = new PasswordAuth();
                $passAuth->inputNewPassword = $this->_newUserPassword;
                $passAuth->inputNewPassword2 = $this->_newUserPassword2;

                $passAuth->currentPassCheckLength = 8;
                $passAuth->currentPassCheckNum = TRUE;
                $passAuth->currentPassCheckCase = TRUE;
                $passAuth->currentPassCheckSpecialchar = TRUE;

                if ($passAuth->checkInputPassword($this->_newUserPassword)) {

                    $_isValidNewPassword = $passAuth->isValidNewPassword();

                    switch ($_isValidNewPassword) {
                        case '0':
                            // New password is valid
                            $_newUserPasswordHash = $passAuth->getNewHash();
                            $_newUserPasswordAlgo = $passAuth->getNewAlgo();
                            $_newUserPasswordSalt = $passAuth->getNewSalt();

                            $this->data['user_algo'] = $_newUserPasswordAlgo;
                            $this->data['user_salt'] = $_newUserPasswordSalt;
                            $this->data['user_password'] = $_newUserPasswordHash;

                            $this->_isValidCurrentPassword = 1;
                            if (!defined('ADMIN_PANEL') && !$this->skipCurrentPass) {
                                Authenticate::setUserCookie($this->userData['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo());
                            }
                            break;
                        case '1':
                            // New Password equal old password
                            fusion_stop();
                            Defender::setInputError('user_password2');
                            Defender::setInputError('user_password2');
                            Defender::setErrorText('user_password', $locale['u134'].$locale['u146'].$locale['u133']);
                            Defender::setErrorText('user_password2', $locale['u134'].$locale['u146'].$locale['u133']);
                            break;
                        case '2':
                            // The two new passwords are not identical
                            fusion_stop();
                            Defender::setInputError('user_password1');
                            Defender::setInputError('user_password2');
                            Defender::setErrorText('user_password1', $locale['u148']);
                            Defender::setErrorText('user_password2', $locale['u148']);
                            break;
                        case '3':
                            // New password contains invalid chars / symbols
                            fusion_stop();
                            Defender::setInputError('user_password1');
                            Defender::setErrorText('user_password1', $locale['u134'].$locale['u142']."<br />".$locale['u147']);
                            break;
                    }
                } else {
                    fusion_stop();
                    Defender::setInputError('user_password1');
                    Defender::setErrorText('user_password1', $passAuth->getError());
                }
            } else {
                fusion_stop($locale['u134'].$locale['u143a']);
            }

        } else if ($this->_method == 'validate_update') {

            $_userPassword = self::getPasswordInput('user_password');

            $this->_newUserPassword = self::getPasswordInput('user_password1');

            $this->_newUserPassword2 = self::getPasswordInput('user_password2');

            if ($this->isAdminPanel or $_userPassword or $this->_newUserPassword or $this->_newUserPassword2) {

                /**
                 * Validation of Password
                 */
                $passAuth = new PasswordAuth();
                $passAuth->inputPassword = $_userPassword;
                $passAuth->inputNewPassword = $this->_newUserPassword;
                $passAuth->inputNewPassword2 = $this->_newUserPassword2;
                $passAuth->currentPasswordHash = $this->userData['user_password'];
                $passAuth->currentAlgo = $this->userData['user_algo'];
                $passAuth->currentSalt = $this->userData['user_salt'];
                $passAuth->currentPassCheckLength = 8;
                $passAuth->currentPassCheckSpecialchar = TRUE;
                $passAuth->currentPassCheckNum = TRUE;
                $passAuth->currentPassCheckCase = TRUE;

                if ($passAuth->checkInputPassword($this->_newUserPassword)) {

                    if ($this->isAdminPanel or $passAuth->isValidCurrentPassword()) {

                        // Just for validation purposes for example email change
                        $this->_isValidCurrentPassword = 1;

                        // Change new password
                        if (!empty($this->_newUserPassword)) {

                            $_isValidNewPassword = $passAuth->isValidNewPassword();

                            switch ($_isValidNewPassword) {
                                case '0':
                                    // New password is valid
                                    $_newUserPasswordHash = $passAuth->getNewHash();
                                    $_newUserPasswordAlgo = $passAuth->getNewAlgo();
                                    $_newUserPasswordSalt = $passAuth->getNewSalt();
                                    $this->data['user_algo'] = $_newUserPasswordAlgo;
                                    $this->data['user_salt'] = $_newUserPasswordSalt;
                                    $this->data['user_password'] = $_newUserPasswordHash;

                                    // Reset cookie for current session and logs out user
                                    if (!defined('ADMIN_PANEL') && !$this->skipCurrentPass) {
                                        Authenticate::setUserCookie($this->userData['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo());
                                    }

                                    break;
                                case '1':
                                    // New Password equal old password
                                    fusion_stop();
                                    Defender::setInputError('user_password');
                                    Defender::setInputError('user_password1');
                                    Defender::setErrorText('user_password', $locale['u134'].$locale['u146'].$locale['u133']);
                                    Defender::setErrorText('user_password1', $locale['u134'].$locale['u146'].$locale['u133']);
                                    break;
                                case '2':
                                    // The two new passwords are not identical
                                    fusion_stop();
                                    Defender::setInputError('user_password1');
                                    Defender::setInputError('user_password2');
                                    Defender::setErrorText('user_password1', $locale['u148']);
                                    Defender::setErrorText('user_password2', $locale['u148']);
                                    break;
                                case '3':
                                    // New password contains invalid chars / symbols
                                    fusion_stop();
                                    Defender::setInputError('user_password1');
                                    Defender::setErrorText('user_password1', $locale['u134'].$locale['u142']."<br />".$locale['u147']);
                                    break;
                            }
                        }
                    } else {
                        fusion_stop();
                        Defender::setInputError('user_password');
                        Defender::setErrorText('user_password', $locale['u149']);
                    }

                } else {

                    fusion_stop();
                    Defender::setInputError('user_password1');
                    Defender::setErrorText('user_password1', $passAuth->getError());
                }
            }
        }
    }

    /**
     * @param string $field
     *
     * @return false|mixed
     */
    private function getPasswordInput($field) {
        return isset($_POST[$field]) && $_POST[$field] != "" ? $_POST[$field] : FALSE;
    }

    /**
     * Handle User Email Input and Validation
     */
    private function setUserEmail() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $is_core_page = (get("section") == 1 || !check_get("section"));
        if (check_post('user_email') || $this->registration) {
            $this->_userEmail = sanitizer('user_email', '', 'user_email');
        }
        if ($this->_userEmail) {

            $this->userData['user_email'] = !empty($this->userData['user_email']) ? $this->userData['user_email'] : '';

            if ($this->_userEmail != $this->userData['user_email']) {

                // override the requirements of password to change email address of a member in admin panel

                if (defined('ADMIN_PANEL') && (iADMIN && checkrights('M'))) {
                    $this->_isValidCurrentPassword = TRUE; // changing an email in administration panel
                } else if (!$this->registration) {
                    $this->verifyEmailPass();
                }

                // Require user password for email change
                if ($this->_isValidCurrentPassword || $this->registration) {
                    // Require a valid email account
                    if (dbcount("(blacklist_id)", DB_BLACKLIST,
                        ":email like replace(if (blacklist_email like '%@%' or blacklist_email like '%\\%%', blacklist_email, concat('%@', blacklist_email)), '_', '\\_')",
                        [':email' => $this->_userEmail])) {
                        // this email blacklisted.
                        fusion_stop();
                        Defender::setInputError('user_email');
                        Defender::setErrorText('user_email', $locale['u124']);

                    } else {

                        $email_active = dbcount("(user_id)", DB_USERS, "user_email='".$this->_userEmail."'");
                        $email_inactive = dbcount("(user_code)", DB_NEW_USERS, "user_email='".$this->_userEmail."'");

                        if ($email_active == 0 && $email_inactive == 0) {
                            if ($this->verifyNewEmail && $settings['email_verification'] == 1 && !iSUPERADMIN) {
                                $this->verifyNewEmail();
                            } else {
                                $this->data['user_email'] = $this->_userEmail;
                            }

                        } else {
                            // email taken
                            fusion_stop();
                            Defender::setInputError('user_email');
                            Defender::setErrorText('user_email', $locale['u125']);
                        }
                    }

                } else {
                    // must have a valid password to change email
                    fusion_stop();

                    Defender::setInputError('user_email_password');

                    if ($is_core_page) {
                        Defender::setErrorText('user_email_password', $locale['u149']);
                    } else {
                        Defender::setErrorText('user_email_password', $locale['u156']);
                    }

                }
            }
        }

        if (!$this->registration) {
            $this->data['user_hide_email'] = post('user_hide_email') ? 1 : 0;
        }
    }

    /**
     * Handle new email verification procedures
     */
    private function verifyNewEmail() {
        $settings = fusion_get_settings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();
        require_once INCLUDES."sendmail_include.php";
        mt_srand((double)microtime() * 1000000);
        $salt = "";
        for ($i = 0; $i <= 10; $i++) {
            $salt .= chr(rand(97, 122));
        }
        $user_code = md5($this->_userEmail.$salt);
        $email_verify_link = $settings['siteurl']."edit_profile.php?code=".$user_code;
        $mailbody = str_replace("[EMAIL_VERIFY_LINK]", $email_verify_link, $locale['u203']);
        $mailbody = str_replace("[SITENAME]", $settings['sitename'], $mailbody);
        $mailbody = str_replace("[SITEUSERNAME]", $settings['siteusername'], $mailbody);
        $mailbody = str_replace("[USER_NAME]", $userdata['user_name'], $mailbody);
        $mailSubject = str_replace("[SITENAME]", $settings['sitename'], $locale['u202']);
        sendemail($this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'], $mailSubject, $mailbody);
        addnotice('warning', strtr($locale['u200'], ['(%s)' => $this->_userEmail]));
        dbquery("DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$this->userData['user_id']."'");
        dbquery("INSERT INTO ".DB_EMAIL_VERIFY." (user_id, user_code, user_email, user_datestamp) VALUES('".$this->userData['user_id']."', '$user_code', '".$this->_userEmail."', '".time()."')");
    }

    /**
     * Set validation error
     */
    private function setValidationError() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $_CAPTCHA_IS_VALID = FALSE;
        include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
        if ($_CAPTCHA_IS_VALID == FALSE) {
            fusion_stop($locale['u194']);
            Defender::setInputError('user_captcha');
        }
    }

    /**
     * Handle request for email verification
     * Sends Verification code when you change email
     * Sends Verification code when you register
     */
    private function setEmailVerification() {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        require_once INCLUDES."sendmail_include.php";
        $userCode = hash_hmac("sha1", PasswordAuth::getNewPassword(), $this->_userEmail);
        $activationUrl = $settings['siteurl']."register.php?email=".$this->_userEmail."&code=".$userCode;
        $message = str_replace("USER_NAME", $this->_userName, $locale['u152']);
        $message = str_replace("SITENAME", $settings['sitename'], $message);
        $message = str_replace("SITEUSERNAME", $settings['siteusername'], $message);
        $message = str_replace("USER_PASSWORD", $this->_newUserPassword, $message);
        $message = str_replace("ACTIVATION_LINK", $activationUrl, $message);
        $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['u151']);
        if (!sendemail($this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'], $subject, $message)) {
            $message = strtr($locale['u154'], [
                '[LINK]'  => "<a href='".BASEDIR."contact.php'><strong>",
                '[/LINK]' => "</strong></a>"
            ]);
            addnotice('warning', $locale['u153']."<br />".$message, 'all');
        }
        $userInfo = base64_encode(serialize($this->data));
        if (fusion_safe()) {
            dbquery("INSERT INTO ".DB_NEW_USERS."
					(user_code, user_name, user_email, user_datestamp, user_info)
					VALUES
					('".$userCode."', '".$this->data['user_name']."', '".$this->data['user_email']."', '".time()."', '".$userInfo."')
					");

        }
        $this->_completeMessage = $locale['u150'];
    }

    /**
     * Update User Fields
     *
     * @return bool
     */
    public function saveUpdate() {

        $locale = fusion_get_locale();

        $settings = fusion_get_settings();

        $this->_method = "validate_update";

        $is_core_page = (post("user_name") || post("user_password") || post('user_password1') || post('user_password2') || post("user_admin_password") || post("user_email"));

        // Non-applicable to any other custom UF section
        if ($is_core_page) {

            $this->setUserName();

            $this->setPassword();

            if (!defined('ADMIN_PANEL')) {
                $this->setAdminPassword();
            }

            $this->setUserEmail();

            $this->setUserAvatar();
        }

        if ($this->validation == 1) {
            $this->setValidationError();
        }

        $quantum = new QuantumFields();
        $quantum->setCategoryDb(DB_USER_FIELD_CATS);
        $quantum->setFieldDb(DB_USER_FIELDS);
        $quantum->setPluginFolder(INCLUDES."user_fields/");
        $quantum->setPluginLocaleFolder(LOCALE.LOCALESET."user_fields/");
        $quantum->loadFields();
        $quantum->loadFieldCats();
        $quantum->setCallbackData($this->userData);
        $_input = $quantum->returnFieldsInput(DB_USERS, 'user_id');

        if (!empty($_input)) {
            foreach ($_input as $input) {
                $this->data += $input;
            }
        }

        $this->data = $this->getData();

        // hidden input tamper check - user_hash must not be changed.
        // id request spoofing request
        $a_check = ($this->userData["user_password"] != sanitizer("user_hash", "", "user_hash"));
        $b_check = ($this->userData['user_id'] != fusion_get_userdata('user_id'));
        // for admin with sufficient rights, skip all these formats
        if (iADMIN && checkrights("M")) {
            $a_check = FALSE;
            $b_check = FALSE;
        }
        if ($a_check or $b_check) {
            fusion_stop();
        }

        // check for password match
        if (fusion_safe()) {

            if ($is_core_page) {
                // Logs Username change
                if ($this->_userName !== $this->userData['user_name']) {
                    save_user_log($this->userData['user_id'], "user_name", $this->_userName, $this->userData['user_name']);
                }
                // Logs Email change
                if ($this->_userEmail !== $this->userData['user_email']) {
                    save_user_log($this->userData['user_id'], "user_email", $this->_userEmail, $this->userData['user_email']);
                }
            }

            // Logs Field changes
            $quantum->logUserAction(DB_USERS, "user_id");

            // Update Table
            dbquery_insert(DB_USERS, $this->data, 'update', ['keep_session' => TRUE]);

            $this->_completeMessage = $locale['u163'];

            if ($this->isAdminPanel && $this->_isValidCurrentPassword && $this->_newUserPassword && $this->_newUserPassword2) {
                // inform user that password has changed. and tell him your new password
                include INCLUDES."sendmail_include.php";
                addnotice("success", str_replace("USER_NAME", $this->userData['user_name'], $locale['global_458']));

                $input = [
                    "mailname" => $this->userData['user_name'],
                    "email"    => $this->userData['user_email'],
                    "subject"  => str_replace("[SITENAME]", $settings['sitename'], $locale['global_456']),
                    "message"  => str_replace(
                        [
                            "[SITENAME]",
                            "[SITEUSERNAME]",
                            "USER_NAME",
                            "[PASSWORD]"
                        ],
                        [
                            $settings['sitename'],
                            $settings['siteusername'],
                            $this->userData['user_name'],
                            $this->_newUserPassword,
                        ],
                        $locale['global_457']
                    )
                ];

                if (!sendemail($input['mailname'], $input['email'], $settings['siteusername'], $settings['siteemail'], $input['subject'],
                    $input['message'])
                ) {
                    addnotice('warning', str_replace("USER_NAME", $this->userData['user_name'], $locale['global_459']));
                }

                redirect(FUSION_REQUEST);

                return FALSE;
            }

            addnotice('success', $locale['u169']);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Set admin password
     */
    private function setAdminPassword() {
        $locale = fusion_get_locale();
        if ($this->getPasswordInput("user_admin_password")) { // if submit current admin password

            $_userAdminPassword = $this->getPasswordInput("user_admin_password");      // var1
            $_newUserAdminPassword = $this->getPasswordInput("user_admin_password1");  // var2
            $_newUserAdminPassword2 = $this->getPasswordInput("user_admin_password2"); // var3
            $adminpassAuth = new PasswordAuth();

            if (!$this->userData['user_admin_password'] && !$this->userData['user_admin_salt']) {
                // New Admin
                $adminpassAuth->inputPassword = 'fake';
                $adminpassAuth->inputNewPassword = $_userAdminPassword;
                $adminpassAuth->inputNewPassword2 = $_newUserAdminPassword2;
                $valid_current_password = TRUE;

            } else {

                // Old Admin changing password
                $adminpassAuth->inputPassword = $_userAdminPassword;         // var1
                $adminpassAuth->inputNewPassword = $_newUserAdminPassword;   // var2
                $adminpassAuth->inputNewPassword2 = $_newUserAdminPassword2; // var3
                $adminpassAuth->currentPasswordHash = $this->userData['user_admin_password'];
                $adminpassAuth->currentAlgo = $this->userData['user_admin_algo'];
                $adminpassAuth->currentSalt = $this->userData['user_admin_salt'];
                $valid_current_password = $adminpassAuth->isValidCurrentPassword();
            }

            if ($valid_current_password) {

                //$_isValidCurrentAdminPassword = 1;

                // authenticated. now do the integrity check
                $_isValidNewPassword = $adminpassAuth->isValidNewPassword();
                switch ($_isValidNewPassword) {
                    case '0':
                        // New password is valid
                        $new_admin_password = $adminpassAuth->getNewHash();
                        $new_admin_salt = $adminpassAuth->getNewSalt();
                        $new_admin_algo = $adminpassAuth->getNewAlgo();
                        $this->data['user_admin_algo'] = $new_admin_algo;
                        $this->data['user_admin_salt'] = $new_admin_salt;
                        $this->data['user_admin_password'] = $new_admin_password;
                        break;
                    case '1':
                        // new password is old password
                        fusion_stop();
                        Defender::setInputError('user_admin_password');
                        Defender::setInputError('user_admin_password1');
                        Defender::setErrorText('user_admin_password', $locale['u144'].$locale['u146'].$locale['u133']);
                        Defender::setErrorText('user_admin_password1', $locale['u144'].$locale['u146'].$locale['u133']);
                        break;
                    case '2':
                        // The two new passwords are not identical
                        fusion_stop();
                        Defender::setInputError('user_admin_password1');
                        Defender::setInputError('user_admin_password2');
                        Defender::setErrorText('user_admin_password1', $locale['u144'].$locale['u148a']);
                        Defender::setErrorText('user_admin_password2', $locale['u144'].$locale['u148a']);
                        break;
                    case '3':
                        // New password contains invalid chars / symbols
                        fusion_stop();
                        Defender::setInputError('user_admin_password1');
                        Defender::setErrorText('user_admin_password1', $locale['u144'].$locale['u142']."<br />".$locale['u147']);
                        break;
                }
            } else {
                fusion_stop();
                Defender::setInputError('user_admin_password');
                Defender::setErrorText('user_admin_password', $locale['u149a']);
            }
        } else { // check db only - admin cannot save profile page without password

            if (iADMIN) {
                $require_valid_password = $this->userData['user_admin_password'];
                if (!$require_valid_password) {
                    // 149 for admin
                    fusion_stop();
                    Defender::setInputError('user_admin_password');
                    Defender::setErrorText('user_admin_password', $locale['u149a']);
                }
            }
        }
    }

    /**
     * Set user avatar
     */
    private function setUserAvatar() {
        if (isset($_POST['delAvatar'])) {
            if ($this->userData['user_avatar'] != "" && file_exists(IMAGES."avatars/".$this->userData['user_avatar']) && is_file(IMAGES."avatars/".$this->userData['user_avatar'])) {
                unlink(IMAGES."avatars/".$this->userData['user_avatar']);
            }
            $this->data['user_avatar'] = '';
        }
        if (isset($_FILES['user_avatar']) && $_FILES['user_avatar']['name']) { // uploaded avatar
            if (!empty($_FILES['user_avatar']) && is_uploaded_file($_FILES['user_avatar']['tmp_name'])) {
                $upload = form_sanitizer($_FILES['user_avatar'], '', 'user_avatar');
                if (isset($upload['error']) && !$upload['error']) {
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
    public function getData() {
        if (!empty($this->userData['user_password'])) {
            // when edit profile
            $this->data['user_hash'] = $this->userData['user_password'];
        } else if (isset($_POST['user_hash'])) {
            // when new registration
            $this->data['user_hash'] = sanitizer('user_hash', '', 'user_hash');
        }

        return $this->data;
    }

    /**
     * @param string $value
     */
    public function setUserNameChange($value) {
        $this->username_change = $value;
    }

    /**
     * @param string $value
     */
    public function verifyCode($value) {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        if (!preg_check("/^[0-9a-z]{32}$/i", $value)) {
            redirect(BASEDIR.'index.php');
        }
        $result = dbquery("SELECT * FROM ".DB_EMAIL_VERIFY." WHERE user_code=:usercode", [':usercode' => $value]);
        if (dbrows($result)) {
            $data = dbarray($result);
            if ($data['user_id'] == $userdata['user_id']) {
                if ($data['user_email'] != $userdata['user_email']) {
                    $result = dbquery("SELECT user_email FROM ".DB_USERS." WHERE user_email=:useremail", [':useremail' => $data['user_email']]);
                    if (dbrows($result) > 0) {
                        addnotice("danger", $locale['u164']."<br />\n".$locale['u121']);
                    } else {
                        $this->_completeMessage = $locale['u169'];
                    }
                    dbquery("UPDATE ".DB_USERS." SET user_email='".$data['user_email']."' WHERE user_id='".$data['user_id']."'");
                    dbquery("DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$data['user_id']."'");
                }
            } else {
                redirect(BASEDIR.'index.php');
            }
        } else {
            redirect(BASEDIR.'index.php');
        }
    }

    /**
     * @return bool
     */
    public function themeChanged() {
        return $this->_themeChanged;
    }

    /**
     * To validate only when _setUserEmail is true
     * Changing Email address
     */
    private function verifyEmailPass() {
        // Validation of password change
        if ($_userPassword = self::getPasswordInput('user_password')) {
            /**
             * Validation of Password
             */
            $passAuth = new PasswordAuth();
            $passAuth->inputPassword = $_userPassword;
            $passAuth->currentAlgo = $this->userData['user_algo'];
            $passAuth->currentSalt = $this->userData['user_salt'];
            $passAuth->currentPasswordHash = $this->userData['user_password'];

            $passAuth->currentPassCheckLength = 1;          // add settings
            $passAuth->currentPassCheckCase = FALSE;        // add settings
            $passAuth->currentPassCheckNum = FALSE;         // add settings
            $passAuth->currentPassCheckSpecialchar = FALSE; // add settings

            if ($passAuth->isValidCurrentPassword()) {
                $this->_isValidCurrentPassword = 1;
            } else {
                fusion_stop($passAuth->getError());
                Defender::setInputError('user_password');
                Defender::setErrorText('user_password', $passAuth->getError());
            }
        }
    }
}
