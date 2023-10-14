<?php
/*
 * -------------------------------------------------------+
 * | PHPFusion Content Management System
 * | Copyright (C) PHP Fusion Inc
 * | https://phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename: theme.php
 * | Author:  Meangczac (Chan)
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */

namespace PHPFusion\Userfields\Accounts;

use Defender;

use PHPFusion\Authenticate;
use PHPFusion\PasswordAuth;
use PHPFusion\Userfields\UserFieldsValidate;

class AccountsValidate extends UserFieldsValidate {

    /**
     * @var string
     */
    private $_userEmail;
    /**
     * @var bool
     */
    private $_isValidCurrentPassword;
    /**
     * @var false|mixed
     */
    private $_newUserPassword;
    /**
     * @var false|mixed
     */
    private $_newUserPassword2;
    /**
     * @var string
     */
    private $_username;

    /**
     * @param $fieldname
     *
     * @return string
     */
    public function sanitizer($fieldname) {
        return sanitizer($fieldname, '', $fieldname);
    }


    /**
     * Handle Username Input and Validation
     */
    public function setUserName() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        if ($this->userFieldsInput->username_change) {
            $uban = explode( ',', $settings['username_ban'] );
            $this->_username = sanitizer( 'user_name', '', 'user_name' );

            if ($this->_username != $this->userFieldsInput->userData['user_name']) {

                if (!preg_match( '/^[-a-z\p{L}\p{N}_]*$/ui', $this->_username )) {
                    // Check for invalid characters
                    fusion_stop();
                    Defender::setInputError( 'user_name' );
                    Defender::setErrorText( 'user_name', $locale['u120'] );

                } else if (in_array( $this->_username, $uban )) {

                    // Check for prohibited usernames
                    fusion_stop();
                    Defender::setInputError( 'user_name' );
                    Defender::setErrorText( 'user_name', $locale['u119'] );

                } else {

                    // Make sure the username is not used already
                    $name_active = dbcount( "(user_id)", DB_USERS, "user_name=:name", [':name' => $this->_username] );
                    $name_inactive = dbcount( "(user_code)", DB_NEW_USERS, "user_name=:name", [':name' => $this->_username] );

                    if ($name_active == 0 && $name_inactive == 0) {

                        return $this->_username;

                    } else {
                        fusion_stop();
                        Defender::setInputError( 'user_name' );
                        Defender::setErrorText( 'user_name', $locale['u121'] );
                    }

                }
            } elseif ($this->userFieldsInput->_method == 'validate_update') {
                return $this->_username;
            }

//            else {
//                Defender::setErrorText( 'user_name', $locale['u122'] );
//                Defender::setInputError( 'user_name' );
//            }
        }

        return $this->userFieldsInput->userData['user_name'];
    }

    /**
     * Phone number
     *
     * @return string
     */
    public function setUserPhone() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        // design sanitization
        if ($this->userFieldsInput->_method == 'validate_update') {
            return sanitizer( 'user_phone', '', 'user_phone' );
        }
        return '';
    }

    /**
     * Hide phone
     *
     * @return int
     */
    public function setUserHidePhone() {
        if ($this->userFieldsInput->_method == 'validate_update') {
            return post( 'user_hide_phone' ) ? 1 : 0;
        }
        return 0;
    }

    /**
     * Email
     */
    public function setUserEmail() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        // has email posted
        $this->_userEmail = sanitizer( 'user_email', '', 'user_email' );

        if ($this->_userEmail != $this->userFieldsInput->userData['user_email']) {

            /**
             * Checks for valid password requirements
             */
            // Password to change email address
            if ($this->userFieldsInput->moderation && (iADMIN && checkrights( 'M' ))) {
                // Skips checking password
                $this->_isValidCurrentPassword = TRUE; // changing an email in administration panel

            } else if ($this->userFieldsInput->_method == 'validate_update') {
                // Check password
                if ($_userPassword = self::getPasswordInput( 'user_hash' )) {
                    /**
                     * Validation of Password
                     */
                    $passAuth = new PasswordAuth();
                    $passAuth->inputPassword = $_userPassword;
                    $passAuth->currentAlgo = $this->userFieldsInput->userData['user_algo'];
                    $passAuth->currentSalt = $this->userFieldsInput->userData['user_salt'];
                    $passAuth->currentPasswordHash = $this->userFieldsInput->userData['user_password'];

                    $passAuth->currentPassCheckLength = $settings['password_length'];
                    $passAuth->currentPassCheckSpecialchar = $settings['password_char'];
                    $passAuth->currentPassCheckNum = $settings['password_num'];;
                    $passAuth->currentPassCheckCase = $settings['password_case'];

                    if ($passAuth->isValidCurrentPassword()) {
                        $this->_isValidCurrentPassword = TRUE;
                    } else {
                        fusion_stop( $passAuth->getError() );
                        Defender::setInputError( 'user_email' );
                        Defender::setErrorText( 'user_email', $passAuth->getError() );
                    }
                }
            }

            if ($this->_isValidCurrentPassword || $this->userFieldsInput->_method == 'validate_insert') {

                // Require a valid email account
                if (dbcount( "(blacklist_id)", DB_BLACKLIST, ":email like replace(if (blacklist_email like '%@%' or blacklist_email like '%\\%%', blacklist_email, concat('%@', blacklist_email)), '_', '\\_')", [':email' => $this->_userEmail] )) {
                    // this email blacklisted.
                    fusion_stop();
                    Defender::setInputError( 'user_email' );
                    Defender::setErrorText( 'user_email', $locale['u124'] );

                } else {

                    $email_active = dbcount( "(user_id)", DB_USERS, "user_email=:email", [':email' => $this->_userEmail] );
                    $email_inactive = dbcount( "(user_code)", DB_NEW_USERS, "user_email=:email", [':email' => $this->_userEmail] );

                    if ($email_active == 0 && $email_inactive == 0) {

                        if ($this->userFieldsInput->emailVerification && !$this->userFieldsInput->_method == 'validate_update' && !iSUPERADMIN) {

                            $this->userFieldsInput->verifyNewEmail();

                        } else {

                            return $this->_userEmail;
                        }

                    } else {
                        // email taken
                        fusion_stop();
                        Defender::setInputError( 'user_email' );
                        Defender::setErrorText( 'user_email', $locale['u125'] );
                    }
                }

            } else {
                // must have a valid password to change email
                fusion_stop();
                addnotice( 'danger', $locale['u156'] );
                Defender::setInputError( 'user_email' );
                Defender::setErrorText( 'user_email', $locale['u149'] );
            }
        }

        return $this->_userEmail;
    }

    /**
     * @param string $field
     *
     * @return false|mixed
     */
    private function getPasswordInput( $field ) {
        return isset( $_POST[$field] ) && $_POST[$field] != "" ? $_POST[$field] : FALSE;
    }

    /**
     * Handle User Password Input and Validation
     */
    public function setPassword() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $_userPassword = self::getPasswordInput( 'user_password' );
        $this->_newUserPassword = self::getPasswordInput( 'user_password1' );
        $this->_newUserPassword2 = self::getPasswordInput( 'user_password2' );

        $passAuth = new PasswordAuth();
        $passAuth->currentPassCheckLength = $settings['password_length'];
        $passAuth->currentPassCheckSpecialchar = $settings['password_char'];
        $passAuth->currentPassCheckNum = $settings['password_num'];;
        $passAuth->currentPassCheckCase = $settings['password_case'];

        if ($this->userFieldsInput->_method == 'validate_insert') {

            if (!empty( $this->_newUserPassword )) {

                $passAuth->inputNewPassword = $this->_newUserPassword;
                $passAuth->inputNewPassword2 = $this->_newUserPassword2;

                if ($passAuth->checkInputPassword( $this->_newUserPassword )) {

                    $_isValidNewPassword = $passAuth->isValidNewPassword();

                    switch ($_isValidNewPassword) {
                        case '0':
                            // New password is valid
                            $_newUserPasswordHash = $passAuth->getNewHash();
                            $_newUserPasswordAlgo = $passAuth->getNewAlgo();
                            $_newUserPasswordSalt = $passAuth->getNewSalt();

                            $this->_isValidCurrentPassword = 1;

                            if (!$this->userFieldsInput->moderation && !$this->userFieldsInput->skipCurrentPass) {
                                Authenticate::setUserCookie( $this->userFieldsInput->userData['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo() );
                            }

                            return [$_newUserPasswordAlgo, $_newUserPasswordSalt, $_newUserPasswordHash];

                        case '1':
                            // New Password equal old password
                            fusion_stop();
                            Defender::setInputError( 'user_password2' );
                            Defender::setInputError( 'user_password2' );
                            Defender::setErrorText( 'user_password', $locale['u134'] . $locale['u146'] . $locale['u133'] );
                            Defender::setErrorText( 'user_password2', $locale['u134'] . $locale['u146'] . $locale['u133'] );
                            break;
                        case '2':
                            // The two new passwords are not identical
                            fusion_stop();
                            Defender::setInputError( 'user_password1' );
                            Defender::setInputError( 'user_password2' );
                            Defender::setErrorText( 'user_password1', $locale['u148'] );
                            Defender::setErrorText( 'user_password2', $locale['u148'] );
                            break;
                        case '3':
                            // New password contains invalid chars / symbols
                            fusion_stop();
                            Defender::setInputError( 'user_password1' );
                            Defender::setErrorText( 'user_password1', $locale['u134'] . $locale['u142'] . "<br />" . $locale['u147'] );
                            break;
                    }
                } else {
                    fusion_stop();
                    Defender::setInputError( 'user_password1' );
                    Defender::setErrorText( 'user_password1', $passAuth->getError() );
                }
            } else {
                fusion_stop( $locale['u134'] . $locale['u143a'] );
            }

        } else if ($this->userFieldsInput->_method == 'validate_update') {

            if ($this->userFieldsInput->moderation or $_userPassword or $this->_newUserPassword or $this->_newUserPassword2) {

                /**
                 * Validation of Password
                 */
                $passAuth->inputPassword = $_userPassword;
                $passAuth->inputNewPassword = $this->_newUserPassword;
                $passAuth->inputNewPassword2 = $this->_newUserPassword2;
                $passAuth->currentPasswordHash = $this->userFieldsInput->userData['user_password'];
                $passAuth->currentAlgo = $this->userFieldsInput->userData['user_algo'];
                $passAuth->currentSalt = $this->userFieldsInput->userData['user_salt'];

                if ($passAuth->checkInputPassword( $this->_newUserPassword )) {

                    if ($this->userFieldsInput->moderation or $passAuth->isValidCurrentPassword()) {
                        // Change new password
                        if (!empty( $this->_newUserPassword )) {

                            $_isValidNewPassword = $passAuth->isValidNewPassword();

                            switch ($_isValidNewPassword) {
                                case '0':
                                    // New password is valid
                                    $_newUserPasswordHash = $passAuth->getNewHash();
                                    $_newUserPasswordAlgo = $passAuth->getNewAlgo();
                                    $_newUserPasswordSalt = $passAuth->getNewSalt();

                                    // Reset cookie for current session and logs out user
                                    if (!$this->userFieldsInput->moderation && !$this->userFieldsInput->skipCurrentPass) {
                                        Authenticate::setUserCookie( $this->userFieldsInput->userData['user_id'], $_newUserPasswordSalt, $_newUserPasswordAlgo );
                                    }

                                    return [$_newUserPasswordAlgo, $_newUserPasswordSalt, $_newUserPasswordHash];

                                case '1':
                                    // New Password equal old password
                                    fusion_stop();
                                    Defender::setInputError( 'user_password' );
                                    Defender::setInputError( 'user_password1' );
                                    Defender::setErrorText( 'user_password', $locale['u134'] . $locale['u146'] . $locale['u133'] );
                                    Defender::setErrorText( 'user_password1', $locale['u134'] . $locale['u146'] . $locale['u133'] );
                                    break;
                                case '2':
                                    // The two new passwords are not identical
                                    fusion_stop();
                                    Defender::setInputError( 'user_password1' );
                                    Defender::setInputError( 'user_password2' );
                                    Defender::setErrorText( 'user_password1', $locale['u148'] );
                                    Defender::setErrorText( 'user_password2', $locale['u148'] );
                                    break;
                                case '3':
                                    // New password contains invalid chars / symbols
                                    fusion_stop();
                                    Defender::setInputError( 'user_password1' );
                                    Defender::setErrorText( 'user_password1', $locale['u134'] . $locale['u142'] . "<br />" . $locale['u147'] );
                                    break;
                            }
                        }
                    } else {
                        fusion_stop();
                        Defender::setInputError( 'user_password' );
                        Defender::setErrorText( 'user_password', $locale['u149'] );
                    }
                } else {
                    fusion_stop();
                    Defender::setInputError( 'user_password1' );
                    Defender::setErrorText( 'user_password1', $passAuth->getError() );
                }
            }
        }

        return FALSE;
    }

    /**
     * Set admin password
     *
     * @return array
     */
    public function setAdminPassword() {

        if (!$this->userFieldsInput->moderation) {

            $locale = fusion_get_locale();

            $settings = fusion_get_settings();

            if ($this->getPasswordInput( 'user_admin_password' )) { // if submit current admin password

                $_userAdminPassword = $this->getPasswordInput( "user_admin_password" );      // var1
                $_newUserAdminPassword = $this->getPasswordInput( "user_admin_password1" );  // var2
                $_newUserAdminPassword2 = $this->getPasswordInput( "user_admin_password2" ); // var3

                $adminpassAuth = new PasswordAuth();
                $adminpassAuth->currentPassCheckLength = $settings['password_length'];
                $adminpassAuth->currentPassCheckSpecialchar = $settings['password_char'];
                $adminpassAuth->currentPassCheckNum = $settings['password_num'];;
                $adminpassAuth->currentPassCheckCase = $settings['password_case'];


                if (!$this->userFieldsInput->userData['user_admin_password'] && !$this->userFieldsInput->userData['user_admin_salt']) {
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
                    $adminpassAuth->currentPasswordHash = $this->userFieldsInput->userData['user_admin_password'];
                    $adminpassAuth->currentAlgo = $this->userFieldsInput->userData['user_admin_algo'];
                    $adminpassAuth->currentSalt = $this->userFieldsInput->userData['user_admin_salt'];

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

                            return [$new_admin_algo, $new_admin_salt, $new_admin_password];

                        case '1':
                            // new password is old password
                            fusion_stop();
                            Defender::setInputError( 'user_admin_password' );
                            Defender::setInputError( 'user_admin_password1' );
                            Defender::setErrorText( 'user_admin_password', $locale['u144'] . $locale['u146'] . $locale['u133'] );
                            Defender::setErrorText( 'user_admin_password1', $locale['u144'] . $locale['u146'] . $locale['u133'] );
                            break;
                        case '2':
                            // The two new passwords are not identical
                            fusion_stop();
                            Defender::setInputError( 'user_admin_password1' );
                            Defender::setInputError( 'user_admin_password2' );
                            Defender::setErrorText( 'user_admin_password1', $locale['u144'] . $locale['u148a'] );
                            Defender::setErrorText( 'user_admin_password2', $locale['u144'] . $locale['u148a'] );
                            break;
                        case '3':
                            // New password contains invalid chars / symbols
                            fusion_stop();
                            Defender::setInputError( 'user_admin_password1' );
                            Defender::setErrorText( 'user_admin_password1', $locale['u144'] . $locale['u142'] . "<br />" . $locale['u147'] );
                            break;
                    }
                } else {
                    fusion_stop();
                    Defender::setInputError( 'user_admin_password' );
                    Defender::setErrorText( 'user_admin_password', $locale['u149a'] );
                }

            } else { // check db only - admin cannot save profile page without password

                if (iADMIN) {
                    $require_valid_password = $this->userFieldsInput->userData['user_admin_password'];

                    if (!$require_valid_password) {
                        // 149 for admin
                        fusion_stop();
                        Defender::setInputError( 'user_admin_password' );
                        Defender::setErrorText( 'user_admin_password', $locale['u149a'] );
                    }
                }
            }
        }
        return [];
    }

    /**
     * To validate only when _setUserEmail is true
     * Changing Email address
     */
    private function verifyEmailPass() {
        // Validation of password change
    }




}
