<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserFieldsInput.inc
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

class UserFieldsInput {
    
    public $post_name = 'update_profile';
    
    public $admin_activation = TRUE;
    public $email_verification = TRUE;
    public $verifyNewEmail = FALSE;
    public $user_name_change = FALSE;
    public $redirect_uri = '';
    public $user_data;
    public $validation = 0;
    public $registration = FALSE;
    public $auto_login = FALSE;
    // On insert or admin edit
    public $skip_password = FALSE; // FALSE to skip pass. True to validate password. New Register always FALSE.
    public $is_admin_panel = FALSE;
    private $_completeMessage;
    private $_method;
    public $hide_user_email = TRUE;
    
    private $_userEmail;
    // New for UF 2.00
    private $_userName;
    // Passwords
    private $data = [];
    
    private $_isValidCurrentPassword = FALSE;
    private $_isValidCurrentAdminPassword = FALSE;
    private $_userPassword = FALSE;
    private $_newUserPassword = FALSE;
    private $_newUserPassword2 = FALSE;
    private $_newUserPasswordHash = FALSE;
    private $_newUserPasswordSalt = FALSE;
    private $_newUserPasswordAlgo = FALSE;
    private $_userAdminPassword = FALSE;
    private $_newUserAdminPassword = FALSE;
    // Settings
    private $_newUserAdminPassword2 = FALSE;
    // Flags
    private $_themeChanged = FALSE;
    // Locale
    private $locale = [];
    private static $instance = NULL;
    
    /**
     * @return object
     */
    public static function get_instance() {
        if ( empty( self::$instance ) ) {
            self::$instance = new static();
        }
        return (object)self::$instance;
    }
    
    private $section = '';
    
    public function __construct() {
        $this->locale = fusion_get_locale();
        $this->section = get( 'section' );
    }
    
    /**
     * Checks user password integrity
     * Requires POST of 'user_hash'
     *
     * @return bool
     * @throws \Exception
     */
    public function checkUserHash() {
        if ( $this->is_admin_panel === TRUE && checkrights( 'M' ) ) {
            return TRUE;
        }
        $user_hash = sanitizer( 'user_hash', '', 'user_hash' );
        if ( $user_hash == $this->user_data['user_password'] ) {
            return TRUE;
        }
        fusion_stop();
        return FALSE;
    }
    
    /**
     * Save User Fields
     */
    public function saveInsert() {
    
        $this->_method = "validate_insert";
    
        if ( post( $this->post_name ) ) {
            
            $data = $this->_setEmptyFields();
            // Set Core Fields.
            $data['user_name'] = $this->validateUserName();
            $data['user_email'] = $this->validateEmail();
            $data += $this->validate_user_password();
        
            $quantum = new UserFieldsQuantum();
            $quantum->loadUserFields();
            $quantum->loadUserFieldCats();
            $quantum->setCallbackData( $this->data );
            $fields_input = $quantum->sanitizeUserFields( DB_USERS, 'user_id' );
            if ( !empty( $fields_input ) ) {
                foreach ( $fields_input as $table_name => $fields_array ) {
                    $data += $fields_array;
                }
            }
            // Captcha check
            if ( $this->validation == 1 ) {
                $this->validate_captcha();
            }
        
            if ( fusion_safe() ) {
            
                if ( $this->email_verification === TRUE && $this->is_admin_panel === FALSE ) {
                    $this->sendVerificationEmail();
                } else {
                    $this->create_user_account( $data );
                    if ( $this->auto_login === TRUE ) {
                        switch ( fusion_get_settings( "login_method" ) ) {
                            case "2" :
                                $authenticate_id = 'user_name';
                                break;
                            case "1" :
                                $authenticate_id = 'user_email';
                                break;
                            default:
                                $authenticate_id = 'user_name';
                        }
                        new Authenticate( $data[ $authenticate_id ], self::_getPasswordInput( 'user_password1' ), TRUE );
                    }
                }
            
                redirect( $this->redirect_uri );
            }
        }
    }
    
    /**
     * Creates user account
     *
     * @param $data
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function create_user_account( $data ) {
        $settings = fusion_get_settings();
        
        dbquery_insert( DB_USERS, $data, 'save' );
        $message = "<strong>".$this->locale['u160']."</strong> ".$this->locale['u161'];
        
        if ( $this->is_admin_panel === TRUE ) {
            $this->locale = fusion_get_locale( '', LOCALE.LOCALESET."admin/members_email.php" );
            require_once INCLUDES."sendmail_include.php";
            $subject = str_replace( "[SITENAME]", $settings['sitename'], $this->locale['email_create_subject'] );
            $replace_this = [ "[USER_NAME]", "[PASSWORD]", "[SITENAME]", "[SITEUSERNAME]" ];
            $replace_with = [ $data['user_name'], $data['user_password'], $settings['sitename'], $settings['siteusername'] ];
            $message = str_replace( $replace_this, $replace_with, $this->locale['email_create_message'] );
            sendemail( $data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message );
            $message = $this->locale['u172'];
        } else {
            // got admin activation and not
            if ( $this->admin_activation ) {
                $message = "<strong>".$this->locale['u160']."</strong> ".$this->locale['u162'];
            }
        }
        
        addNotice( 'success', $message, $this->redirect_uri );
    }
    
    /**
     * Initialise empty fields
     *
     * @return array
     */
    private function _setEmptyFields() {
    
        /** Prepare initial variables for settings */
        if ( $this->_method == "validate_insert" ) {
            
            // Compulsory Core Fields
            return [
                'user_id'         => 0,
                'user_hide_email' => $this->hide_user_email,
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
                'user_status'     => $this->admin_activation == 1 ? 2 : 0,
                'user_theme'      => 'Default',
                'user_language'   => LANGUAGE,
                'user_timezone'   => fusion_get_settings( 'timeoffset' ),
                'user_reputation' => 0
            ];
    
        } else {
            return NULL;
        }
    }
    
    
    private function validate_user_password() {
        $password = [
            'user_password' => '',
            'user_salt'     => '',
            'user_algo'     => '',
        ];
        $this->_newUserPassword = self::_getPasswordInput( 'user_password1' );
        $this->_newUserPassword2 = self::_getPasswordInput( 'user_password2' );
        if ( !empty( $this->_newUserPassword ) ) {
            $passAuth = new PasswordAuth();
            $passAuth->inputNewPassword = $this->_newUserPassword;
            $passAuth->inputNewPassword2 = $this->_newUserPassword2;
            $_isValidNewPassword = $passAuth->isValidNewPassword();
            
            switch ( $_isValidNewPassword ) {
                case '0':
                    // New password is valid
                    $this->_newUserPasswordHash = $passAuth->getNewHash();
                    $this->_newUserPasswordAlgo = $passAuth->getNewAlgo();
                    $this->_newUserPasswordSalt = $passAuth->getNewSalt();
    
                    $password['user_algo'] = $this->_newUserPasswordAlgo;
                    $password['user_salt'] = $this->_newUserPasswordSalt;
                    $password['user_password'] = $this->_newUserPasswordHash;
                    /*if (!defined('ADMIN_PANEL') && !$this->skip_password) {
                        Authenticate::setUserCookie($this->user_data['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo(), FALSE);
                    }*/
                    break;
                case '1':
                    // New Password equal old password
                    Defender::stop();
                    Defender::setInputError( 'user_password2' );
                    Defender::setInputError( 'user_password2' );
                    Defender::setErrorText( 'user_password', $this->locale['u134'].$this->locale['u146'].$this->locale['u133'] );
                    Defender::setErrorText( 'user_password2', $this->locale['u134'].$this->locale['u146'].$this->locale['u133'] );
                    break;
                case '2':
                    // The two new passwords are not identical
                    Defender::stop();
                    Defender::setInputError( 'user_password1' );
                    Defender::setInputError( 'user_password2' );
                    Defender::setErrorText( 'user_password1', $this->locale['u148'] );
                    Defender::setErrorText( 'user_password2', $this->locale['u148'] );
                    break;
                case '3':
                    // New password contains invalid chars / symbols
                    Defender::stop();
                    Defender::setInputError( 'user_password1' );
                    Defender::setErrorText( 'user_password1', $this->locale['u134'].$this->locale['u142']."<br />".$this->locale['u147'] );
                    break;
            }
        } else {
            Defender::stop();
            Defender::setInputError( 'user_password1' );
            Defender::setErrorText( 'user_password1', $this->locale['u134'].$this->locale['u143a'] );
        }
        
        return (array)$password;
    }
    
    private function validate_admin_password() {
        $password = [
            'user_admin_password' => '',
            'user_admin_salt'     => '',
            'user_admin_algo'     => '',
        ];
        $this->_newUserPassword = self::_getPasswordInput( 'user_password1' );
        $this->_newUserPassword2 = self::_getPasswordInput( 'user_password2' );
        if ( !empty( $this->_newUserPassword ) ) {
            $passAuth = new PasswordAuth();
            $passAuth->inputNewPassword = $this->_newUserPassword;
            $passAuth->inputNewPassword2 = $this->_newUserPassword2;
            $_isValidNewPassword = $passAuth->isValidNewPassword();
            
            switch ( $_isValidNewPassword ) {
                case '0':
                    // New password is valid
                    $this->_newUserPasswordHash = $passAuth->getNewHash();
                    $this->_newUserPasswordAlgo = $passAuth->getNewAlgo();
                    $this->_newUserPasswordSalt = $passAuth->getNewSalt();
    
                    $password['user_admin_algo'] = $this->_newUserPasswordAlgo;
                    $password['user_admin_salt'] = $this->_newUserPasswordSalt;
                    $password['user_admin_password'] = $this->_newUserPasswordHash;
                    /*if (!defined('ADMIN_PANEL') && !$this->skip_password) {
                        Authenticate::setUserCookie($this->user_data['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo(), FALSE);
                    }*/
                    break;
                case '1':
                    // New Password equal old password
                    fusion_stop();
                    Defender::setInputError( 'user_admin_password2' );
                    Defender::setInputError( 'user_admin_password2' );
                    Defender::setErrorText( 'user_admin_password', $this->locale['u134'].$this->locale['u146'].$this->locale['u133'] );
                    Defender::setErrorText( 'user_admin_password2', $this->locale['u134'].$this->locale['u146'].$this->locale['u133'] );
                    break;
                case '2':
                    // The two new passwords are not identical
                    fusion_stop();
                    Defender::setInputError( 'user_admin_password1' );
                    Defender::setInputError( 'user_admin_password2' );
                    Defender::setErrorText( 'user_admin_password1', $this->locale['u148'] );
                    Defender::setErrorText( 'user_admin_password2', $this->locale['u148'] );
                    break;
                case '3':
                    // New password contains invalid chars / symbols
                    fusion_stop();
                    Defender::setInputError( 'user_admin_password1' );
                    Defender::setErrorText( 'user_admin_password1', $this->locale['u134'].$this->locale['u142']."<br />".$this->locale['u147'] );
                    break;
            }
        } else {
            fusion_stop();
            Defender::setInputError( 'user_admin_password1' );
            Defender::setErrorText( 'user_admin_password1', $this->locale['u134'].$this->locale['u143a'] );
        }
        
        return (array)$password;
    }
    
    private function _getPasswordInput( $field ) {
        return isset( $_POST[ $field ] ) && $_POST[ $field ] != "" ? $_POST[ $field ] : FALSE;
    }
    
    /**
     * To validate password manually
     *
     * @param $input_field
     *
     * @return bool
     */
    private function verify_password( $input_field ) {
        // Validation of password using user_password_verify field
        $_userPassword = self::_getPasswordInput( $input_field );
        if ( $_userPassword ) {
            $passAuth = new PasswordAuth();
            // These 3 goes together
            $passAuth->inputPassword = $_userPassword;
            $passAuth->currentAlgo = $this->user_data['user_algo'];
            $passAuth->currentSalt = $this->user_data['user_salt'];
            $passAuth->currentPasswordHash = $this->user_data['user_password'];
            if ( $passAuth->isValidCurrentPassword() ) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     *  Handle new email verification procedures
     *
     * @param $email
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function verify_new_email( $email ) {
        $settings = fusion_get_settings();
        $userdata = fusion_get_userdata();
        $this->locale = fusion_get_locale();
        require_once INCLUDES."sendmail_include.php";
        mt_srand( (double)microtime() * 1000000 );
        $salt = "";
        for ( $i = 0; $i <= 10; $i++ ) {
            $salt .= chr( rand( 97, 122 ) );
        }
        $user_code = md5( $email.$salt );
        $email_verify_link = $settings['siteurl']."edit_profile.php?code=".$user_code;
        $mailbody = str_replace( "[EMAIL_VERIFY_LINK]", $email_verify_link, $this->locale['u203'] );
        $mailbody = str_replace( "[SITENAME]", $settings['sitename'], $mailbody );
        $mailbody = str_replace( "[SITEUSERNAME]", $settings['siteusername'], $mailbody );
        $mailbody = str_replace( "[USER_NAME]", $userdata['user_name'], $mailbody );
        $mailSubject = str_replace( "[SITENAME]", $settings['sitename'], $this->locale['u202'] );
        sendemail( $this->_userName, $email, $settings['siteusername'], $settings['siteemail'], $mailSubject, $mailbody );
        addNotice( 'warning', strtr( $this->locale['u200'], [ '(%s)' => $email ] ) );
        dbquery( "DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$this->user_data['user_id']."'" );
        dbquery( "INSERT INTO ".DB_EMAIL_VERIFY." (user_id, user_code, user_email, user_datestamp) VALUES('".$this->user_data['user_id']."', '$user_code', '".$email."', '".TIME."')" );
    }
    
    // Get New Password Hash and Directly Set New Cookie if Authenticated
    private function validate_captcha() {
        $this->locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $_CAPTCHA_IS_VALID = FALSE;
        include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
        if ( $_CAPTCHA_IS_VALID == FALSE ) {
            fusion_stop();
            Defender::setInputError( 'user_captcha' );
            addNotice( 'danger', $this->locale['u194'] );
        }
    }
    
    /**
     * Handle request for email verification
     * Sends Verification code when you change email
     * Sends Verification code when you register
     */
    private function sendVerificationEmail() {
        $settings = fusion_get_settings();
        $this->locale = fusion_get_locale();
        require_once INCLUDES."sendmail_include.php";
        $userCode = hash_hmac( "sha1", PasswordAuth::getNewPassword(), $this->_userEmail );
        $activationUrl = $settings['siteurl']."register.php?email=".$this->_userEmail."&code=".$userCode;
        $message = str_replace( "USER_NAME", $this->_userName, $this->locale['u152'] );
        $message = str_replace( "SITENAME", $settings['sitename'], $message );
        $message = str_replace( "SITEUSERNAME", $settings['siteusername'], $message );
        $message = str_replace( "USER_PASSWORD", $this->_newUserPassword, $message );
        $message = str_replace( "ACTIVATION_LINK", $activationUrl, $message );
        $subject = str_replace( "[SITENAME]", $settings['sitename'], $this->locale['u151'] );
        if ( !sendemail( $this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'], $subject, $message ) ) {
            $message = strtr( $this->locale['u154'], [
                '[LINK]'  => "<a href='".BASEDIR."contact.php'><strong>",
                '[/LINK]' => "</strong></a>"
            ] );
            addNotice( 'warning', $this->locale['u153']."<br />".$message, 'all' );
        }
        $userInfo = base64_encode( serialize( $this->data ) );
        if ( fusion_safe() ) {
            dbquery( "INSERT INTO ".DB_NEW_USERS."
					(user_code, user_name, user_email, user_datestamp, user_info)
					VALUES
					('".$userCode."', '".$this->data['user_name']."', '".$this->data['user_email']."', '".TIME."', '".$userInfo."')
					" );
        
        }
        addNotice( 'success', $this->locale['u150'] );
    }
    
    /**
     * Note: Preference field must be available in Admin mode
     *
     * @throws \Exception
     */
    private function updatePreference() {
        // is admin panel allowable to set preference details?
        if ( $this->is_admin_panel || post( $this->post_name.'_pref' ) ) {
    
            $data['user_language'] = sanitizer( 'user_language', '', 'user_language' );
            $data['user_hide_email'] = sanitizer( 'user_hide_email', '0', 'user_hide_email' );
            $data['user_location'] = sanitizer( 'user_location', '', 'user_location' );
            $data['user_pm_email_notify'] = sanitizer( 'pm_email_notify', 0, 'pm_email_notify' );
            $quantum = new UserFieldsQuantum();
            $quantum->loadUserFields();
            $quantum->getCatList();
            $quantum->setCallbackData( $this->user_data );
            $input = $quantum->sanitizeUserFields( DB_USERS, 'user_id' );
            if ( !empty( $input ) ) {
                foreach ( $input as $input_data ) {
                    $data += $input_data;
                }
            }
            if ( fusion_safe() ) {
                $data['user_id'] = $this->user_data['user_id'];
                dbquery_insert( DB_USERS, $data, 'update' );
                if ( !$this->is_admin_panel ) {
                    addNotice( 'success', 'Your account preference has been updated successfully' );
                    redirect( FUSION_REQUEST );
                }
            }
        }
    }
    
    private function updateSecurity() {
        $this->updateUserName();
        $this->updatePass();
        $this->updateAdminPass();
        $this->updateEmail();
        $this->closeAccount();
        // Security - added level security
        //if ( post( $this->post_name.'_block' ) || post( $this->post_name.'_unblock' ) ) {
        //    $this->update_user_blacklist();
        //}
    }
    
    // Update Mode
    private function updatePublic() {
    
        if ( post( $this->post_name ) ) {
            if ( $this->checkUserHash() ) {
                $data = [];
                $data['user_avatar'] = $this->set_user_avatar();
                $quantum = new UserFieldsQuantum();
                $quantum->loadUserFields();
                $quantum->getCatList();
                $quantum->setCallbackData( $this->user_data );
                $input = $quantum->sanitizeUserFields( DB_USERS, 'user_id' );
                if ( !empty( $input ) ) {
                    foreach ( $input as $input_data ) {
                        $data += $input_data;
                    }
                }
                //if ( fusion_safe() ) {
                //    $data['user_id'] = $this->user_data['user_id'];
                //    $quantum->log_user_action( DB_USERS, 'user_id' );
                //    dbquery_insert( DB_USERS, $data, 'update' );
                //    addNotice( 'success', 'Your profile has been updated successfully.', 'all' );
                //    redirect( FUSION_REQUEST );
                //}
            }
        }
    }
    
    /**
     * Update se_profile
     *
     * @throws \Exception
     */
    private function updateSettings() {
        if ( $this->section == 'preferences' ) {
            $this->updatePreference();
        } else if ( $this->section == 'acc_settings') {
            $this->updateSecurity();
        }
    }
    
    /*
     * Update user blacklists
     */
    private function update_user_blacklist() {
        if ( $this->is_admin_panel === FALSE ) {
            $userdata = $this->user_data;
            $unblock_name = $this->post_name."_unblock";
            $block_name = $this->post_name."_block";
            // Block user
            if ( isset( $_POST[ $block_name ] ) && isset( $_POST['user_block'] ) && isnum( $_POST['user_block'] ) ) {
                $input_value = stripinput( $_POST['user_block'] );
                $userdata['user_block'] = explode( '.', $userdata['user_block'] );
                if ( !in_array( $input_value, $userdata['user_block'] ) ) {
                    $userdata['user_block'][] = $input_value;
                    array_filter( $userdata['user_block'] );
                    $userdata['user_block'] = implode( ".", $userdata['user_block'] );
                    dbquery( "UPDATE ".DB_USERS." SET user_block=:users WHERE user_id=:uid", [
                        ":users" => $userdata['user_block'],
                        ":uid"   => $userdata['user_id']
                    ] );
                    addNotice( "success", "The selected user has been blocked" );
                } else {
                    Defender::stop();
                    addNotice( "danger", "The selected user has already been blocked." );
                }
                redirect( FUSION_REQUEST );
            }
            // Unblock User
            if ( isset( $_GET[ $unblock_name ] ) && isnum( $_GET[ $unblock_name ] ) ) {
                $userdata['user_block'] = explode( ".", $userdata['user_block'] );
                if ( !empty( $userdata['user_block'] ) ) {
                    $blocked_uids = array_flip( $userdata['user_block'] );
                    unset( $blocked_uids[ $_GET[ $this->post_name."_unblock" ] ] );
                    $userdata['user_block'] = implode( '.', array_flip( $blocked_uids ) );
                    dbquery( "UPDATE ".DB_USERS." SET user_block=:users WHERE user_id=:uid", [
                        ':users' => $userdata['user_block'],
                        ':uid'   => $userdata['user_id']
                    ] );
                    addNotice( "success", "The selected user has been unblocked." );
                } else {
                    fusion_stop();
                    addNotice( "danger", "The user does not exist in your block list." );
                }
                redirect( clean_request( "", [ $this->post_name."_unblock" ], FALSE ) );
            }
        }
    }
    
    /**
     * Update User Fields
     */
    public function saveUpdate() {
        $this->locale = fusion_get_locale();
        $this->_method = "validate_update";
    
        $uf_class = new UserFields();
        $current_page = $uf_class->getEditCurrentPage();
    
        switch ( $current_page ) {
            case 'pu_profile':
                $this->updatePublic();
                break;
            case 'se_profile':
                // Do this first.
                if ( get( 'code' ) && fusion_get_settings( 'email_verification' ) == 1 ) {
                    $this->verifyCode( get( 'code' ) );
                    redirect( clean_request( '', [ 'code' ], FALSE ) );
                }
                $this->updateSettings();
                break;
            case 'custom':
                break;
        }
    }
    
    /*
     * Save user avatar
     */
    private function set_user_avatar() {
    
        if ( post( 'delAvatar' ) ) {
            if ( $this->user_data['user_avatar'] != "" && file_exists( IMAGES."avatars/".$this->user_data['user_avatar'] ) && is_file( IMAGES."avatars/".$this->user_data['user_avatar'] ) ) {
                unlink( IMAGES."avatars/".$this->user_data['user_avatar'] );
            }
            return NULL;
        }
    
        if ( isset( $_FILES['user_avatar'] ) && $_FILES['user_avatar']['name'] ) { // uploaded avatar
            if ( !empty( $_FILES['user_avatar'] ) && is_uploaded_file( $_FILES['user_avatar']['tmp_name'] ) ) {
                $upload = form_sanitizer( $_FILES['user_avatar'], '', 'user_avatar' );
                //print_p($upload);
                //define('STOP_REDIRECT', true);
                if ( isset( $upload['error'] ) && empty( $upload['error'] ) ) {
                    return $upload['image_name'];
                }
    
                return NULL;
            }
        }
    
        return (string)$this->user_data['user_avatar'];
    }
    
    public function verifyCode( $value ) {
        $this->locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        if ( !preg_check( "/^[0-9a-z]{32}$/i", $value ) ) {
            redirect( BASEDIR.'index.php' );
        }
        $result = dbquery( "SELECT * FROM ".DB_EMAIL_VERIFY." WHERE user_code=:usercode", [ ':usercode' => $value ] );
        if ( dbrows( $result ) ) {
            $data = dbarray( $result );
            if ( $data['user_id'] == $userdata['user_id'] ) {
                if ( $data['user_email'] != $userdata['user_email'] ) {
                    $result = dbquery( "SELECT user_email FROM ".DB_USERS." WHERE user_email=:useremail", [ ':useremail' => $data['user_email'] ] );
                    if ( dbrows( $result ) > 0 ) {
                        addNotice( "danger", $this->locale['u164']."<br />\n".$this->locale['u121'] );
                    } else {
                        $this->_completeMessage = $this->locale['u169'];
                    }
                    dbquery( "UPDATE ".DB_USERS." SET user_email='".$data['user_email']."' WHERE user_id='".$data['user_id']."'" );
                    dbquery( "DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$data['user_id']."'" );
                }
            } else {
                redirect( BASEDIR.'index.php' );
            }
        } else {
            redirect( BASEDIR.'index.php' );
        }
    }
    
    public function themeChanged() {
        return $this->_themeChanged;
    }
    
    /**
     * @return string
     * @throws \Exception
     */
    private function validateUserName() {
        $settings = fusion_get_settings();
        $field_name = $this->registration === TRUE ? 'user_name_reg' : 'user_name';
        $username = sanitizer( $field_name, '', $field_name );
        // check only if old user name is different than current user name
        if ( !empty( $username ) && $username != $this->user_data['user_name'] ) {
            $uban = explode( ',', $settings['user_name_ban'] );
            if ( !preg_check( "/^[-0-9A-Z_@\s]+$/i", $username ) ) {
                // Check for invalid characters
                fusion_stop();
                Defender::setInputError( $field_name );
                Defender::setErrorText( $field_name, $this->locale['u120'] );
            } else if ( in_array( $username, $uban ) ) {
                fusion_stop();
                Defender::setInputError( $field_name );
                Defender::setErrorText( $field_name, $this->locale['u119'] );
            } else {
                // Make sure the username is not used already
                $name_active = dbcount( "(user_id)", DB_USERS, "user_name=:uname", [ ":uname" => $username, ] );
                $name_inactive = dbcount( "(user_code)", DB_NEW_USERS, "user_name=':uname'", [ ':uname' => $username ] );
                if ( $name_active == 0 && $name_inactive == 0 ) {
                    if ( $this->registration === FALSE && $this->is_admin_panel === FALSE ) {
                        addNotice( 'success', 'Your username has been updated successfully.' );
                    }
                    return (string)$username;
                } else {
                    fusion_stop();
                    Defender::setInputError( $field_name );
                    Defender::setErrorText( $field_name, $this->locale['u121'] );
                }
            }
        }
    
        return (string)$username;
    }
    
    /**
     * Handle User Name Input and Validation
     *
     * @throws \Exception
     */
    private function updateUserName() {
        if ( $this->user_name_change && post( $this->post_name.'_username' ) || $this->is_admin_panel ) {
            $new_username = $this->validateUserName();
            if ( fusion_safe() ) {
                dbquery( "UPDATE `".DB_USERS."` SET `user_name`=:uname WHERE `user_id`=:uid", [
                    ':uname' => $new_username,
                    ':uid'   => $this->user_data['user_id']
                ] );
                save_user_log( $this->user_data['user_id'], "user_name", $new_username, $this->user_data['user_name'] );
            } else {
                addNotice( 'danger', "You cannot change your username" );
            }
    
            if ( !$this->is_admin_panel ) {
                redirect( FUSION_REQUEST );
            }
        }
    
    }
    
    /**
     * Handle User Password Input and Validation
     */
    private function updatePass() {
        $user_salt = '';
        $user_algo = '';
        $user_password = '';
        $notice_class = '';
        $notice_message = '';
        // method - validate_update
        $this->_userPassword = self::_getPasswordInput( 'user_password' );
        $this->_newUserPassword = self::_getPasswordInput( 'user_password1' );
        $this->_newUserPassword2 = self::_getPasswordInput( 'user_password2' );
        if ( $this->is_admin_panel || ( post( $this->post_name.'_password' ) ) ) {
            if ( !fusion_get_userdata( 'user_password' ) ) {
                $skip_pass = TRUE;
            }
            /**
             * Validation of Password
             */
            $passAuth = new PasswordAuth();
            $passAuth->inputPassword = $this->_userPassword;
            $passAuth->inputNewPassword = $this->_newUserPassword;
            $passAuth->inputNewPassword2 = $this->_newUserPassword2;
            $passAuth->currentPasswordHash = $this->user_data['user_password'];
            $passAuth->currentAlgo = $this->user_data['user_algo'];
            $passAuth->currentSalt = $this->user_data['user_salt'];
    
            if ( $this->is_admin_panel or $passAuth->isValidCurrentPassword() or isset( $skip_pass ) ) {
                
                // Just for validation purposes for example email change
                $this->_isValidCurrentPassword = 1;
        
                // Change new password
                if ( !empty( $this->_newUserPassword ) ) {
                    
                    $_isValidNewPassword = $passAuth->isValidNewPassword();
    
                    switch ( $_isValidNewPassword ) {
                        case '0':
                            // New password is valid
                            $this->_newUserPasswordHash = $passAuth->getNewHash();
                            $this->_newUserPasswordAlgo = $passAuth->getNewAlgo();
                            $this->_newUserPasswordSalt = $passAuth->getNewSalt();
    
                            $user_algo = $this->_newUserPasswordAlgo;
                            $user_salt = $this->_newUserPasswordSalt;
                            $user_password = $this->_newUserPasswordHash;
                            $notice_class = "success";
                            $notice_message = "Your new password has been updated successfully.";
    
                            //if (!defined('ADMIN_PANEL') && !$this->skip_password) {
                            //Authenticate::setUserCookie($this->user_data['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo(), FALSE);
                            //}
    
                            break;
                        case '1':
                            // New Password equal old password
                            fusion_stop();
                            Defender::setInputError( 'user_password' );
                            Defender::setInputError( 'user_password1' );
                            Defender::setErrorText( 'user_password', $this->locale['u134'].$this->locale['u146'].$this->locale['u133'] );
                            Defender::setErrorText( 'user_password1', $this->locale['u134'].$this->locale['u146'].$this->locale['u133'] );
                            $notice_class = 'danger';
                            $notice_message = $this->locale['u133'];
                            break;
                        case '2':
                            // The two new passwords are not identical
                            fusion_stop();
                            Defender::setInputError( 'user_password1' );
                            Defender::setInputError( 'user_password2' );
                            Defender::setErrorText( 'user_password1', $this->locale['u148'] );
                            Defender::setErrorText( 'user_password2', $this->locale['u148'] );
                            $notice_class = 'danger';
                            $notice_message = $this->locale['u148'];
                            break;
                        case '3':
                            // New password contains invalid chars / symbols
                            fusion_stop();
                            Defender::setInputError( 'user_password1' );
                            Defender::setErrorText( 'user_password1', $this->locale['u134'].$this->locale['u142']."<br />".$this->locale['u147'] );
                            $notice_class = 'danger';
                            $notice_message = $this->locale['u134'].$this->locale['u142'].$this->locale['u147'];
                            break;
                    }
                }
        
            } else {
        
                Defender::stop();
                Defender::setInputError( 'user_password' );
                Defender::setErrorText( 'user_password', $this->locale['u149'] );
                $notice_class = 'danger';
                $notice_message = $this->locale['u149'];
            }
            if ( fusion_safe() ) {
                dbquery( "UPDATE `".DB_USERS."` SET user_salt=:u_salt, user_algo=:u_algo, user_password=:u_passwd WHERE user_id=:uid", [
                    ':u_salt'   => $user_salt,
                    ':u_algo'   => $user_algo,
                    ':u_passwd' => $user_password,
                    ':uid'      => $this->user_data['user_id']
                ] );
                
                include INCLUDES."sendmail_include.php";
    
                addNotice( "success", str_replace( "USER_NAME", $this->user_data['user_name'], $this->locale['global_458'] ) );
                $settings = fusion_get_settings();
                $input = [
                    "mailname" => $this->user_data['user_name'],
                    "email"    => $this->user_data['user_email'],
                    "subject"  => str_replace( "[SITENAME]", $settings['sitename'], $this->locale['global_456'] ),
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
                            $this->user_data['user_name'],
                            $this->_newUserPassword,
                        ],
                        $this->locale['global_457']
                    )
                ];
                if ( !sendemail( $input['mailname'], $input['email'], $settings['siteusername'], $settings['siteemail'], $input['subject'],
                    $input['message'] ) ) {
                    addNotice( 'warning', str_replace( "USER_NAME", $this->user_data['user_name'], $this->locale['global_459'] ) );
                }
                if ( !$this->is_admin_panel ) {
                    addNotice( $notice_class, $notice_message );
                }
            }
        }
    
    }
    
    private function updateAdminPass() {
        $user_admin_algo = '';
        $user_admin_password = '';
        $user_admin_salt = '';
        $notice_message = '';
        $notice_class = '';
        
        if ( $this->_getPasswordInput( "user_admin_password" ) && ( post( $this->post_name.'_password' ) ) ) { // if submit current admin password
            
            $this->_userAdminPassword = $this->_getPasswordInput( "user_admin_password" ); // var1
            $this->_newUserAdminPassword = $this->_getPasswordInput( "user_admin_password1" ); // var2
            $this->_newUserAdminPassword2 = $this->_getPasswordInput( "user_admin_password2" ); // var3
            
            $adminpassAuth = new PasswordAuth();
            
            if ( !$this->user_data['user_admin_password'] && !$this->user_data['user_admin_salt'] ) {
                // New Admin
                $adminpassAuth->inputPassword = 'fake';
                $adminpassAuth->inputNewPassword = $this->_userAdminPassword;
                $adminpassAuth->inputNewPassword2 = $this->_newUserAdminPassword2;
                $valid_current_password = TRUE;
                
            } else {
                
                // Old Admin changing password
                $adminpassAuth->inputPassword = $this->_userAdminPassword; // var1
                $adminpassAuth->inputNewPassword = $this->_newUserAdminPassword; // var2
                $adminpassAuth->inputNewPassword2 = $this->_newUserAdminPassword2; // var3
                $adminpassAuth->currentPasswordHash = $this->user_data['user_admin_password'];
                $adminpassAuth->currentAlgo = $this->user_data['user_admin_algo'];
                $adminpassAuth->currentSalt = $this->user_data['user_admin_salt'];
                $valid_current_password = $adminpassAuth->isValidCurrentPassword();
            }
            
            if ( $valid_current_password ) {
                $this->_isValidCurrentAdminPassword = 1;
                // authenticated. now do the integrity check
                $_isValidNewPassword = $adminpassAuth->isValidNewPassword();
                switch ( $_isValidNewPassword ) {
                    case '0':
                        // New password is valid
                        $user_admin_password = $adminpassAuth->getNewHash();
                        $user_admin_salt = $adminpassAuth->getNewSalt();
                        $user_admin_algo = $adminpassAuth->getNewAlgo();
                        $notice_class = "success";
                        $notice_message = "Your new admin password has been updated successfully.";
                        break;
                    case '1':
                        // new password is old password
                        fusion_stop();
                        Defender::setInputError( 'user_admin_password' );
                        Defender::setInputError( 'user_admin_password1' );
                        Defender::setErrorText( 'user_admin_password', $this->locale['u144'].$this->locale['u146'].$this->locale['u133'] );
                        Defender::setErrorText( 'user_admin_password1', $this->locale['u144'].$this->locale['u146'].$this->locale['u133'] );
                        $notice_class = "danger";
                        $notice_message = $this->locale['u144'].$this->locale['u146'].$this->locale['u133'];
                        break;
                    case '2':
                        // The two new passwords are not identical
                        fusion_stop();
                        Defender::setInputError( 'user_admin_password1' );
                        Defender::setInputError( 'user_admin_password2' );
                        Defender::setErrorText( 'user_admin_password1', $this->locale['u144'].$this->locale['u148a'] );
                        Defender::setErrorText( 'user_admin_password2', $this->locale['u144'].$this->locale['u148a'] );
                        $notice_class = "danger";
                        $notice_message = $this->locale['u144'].$this->locale['u148a'];
                        break;
                    case '3':
                        // New password contains invalid chars / symbols
                        fusion_stop();
                        Defender::setInputError( 'user_admin_password1' );
                        Defender::setErrorText( 'user_admin_password1', $this->locale['u144'].$this->locale['u142']."<br />".$this->locale['u147'] );
                        $notice_class = "danger";
                        $notice_message = $this->locale['u144'].$this->locale['u142'].$this->locale['u147'];
                        break;
                }
            } else {
                Defender::stop();
                Defender::setInputError( 'user_admin_password' );
                Defender::setErrorText( 'user_admin_password', $this->locale['u149a'] );
                $notice_class = "danger";
                $notice_message = $this->locale['u149a'];
            }
            
            if ( fusion_safe() ) {
                dbquery( "UPDATE `".DB_USERS."` SET user_admin_algo=:ua_algo, user_admin_password=:ua_passwd, user_admin_salt=:ua_salt WHERE user_id=:uid", [
                    ':uid'       => $this->user_data['user_id'],
                    ':ua_algo'   => $user_admin_algo,
                    ':ua_salt'   => $user_admin_salt,
                    ':ua_passwd' => $user_admin_password
                ] );
                // changing password by member
                $settings = fusion_get_settings();
                include INCLUDES."sendmail_include.php";
                addNotice( "success", str_replace( "USER_NAME", $this->user_data['user_name'], $this->locale['global_458'] ) );
                $input = [
                    "mailname" => $this->user_data['user_name'],
                    "email"    => $this->user_data['user_email'],
                    "subject"  => str_replace( "[SITENAME]", $settings['sitename'], $this->locale['global_456'] ),
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
                            $this->user_data['user_name'],
                            $this->_newUserPassword,
                        ],
                        $this->locale['global_457']
                    )
                ];
                if ( !sendemail( $input['mailname'], $input['email'], $settings['siteusername'], $settings['siteemail'], $input['subject'], $input['message'] ) ) {
                    addNotice( 'warning', str_replace( "USER_NAME", $this->user_data['user_name'], $this->locale['global_459'] ) );
                }
                if ( !$this->is_admin_panel ) {
                    addNotice( $notice_class, $notice_message );
                    redirect( FUSION_REQUEST );
                }
            }
            
        } else {
            // check db only - admin cannot save profile page without password
            if ( iADMIN && checkrights( 'M' ) ) {
                $require_valid_password = $this->user_data['user_admin_password'] ? TRUE : FALSE;
                if ( !$require_valid_password ) {
                    fusion_stop();
                    Defender::setInputError( 'user_admin_password' );
                    Defender::setErrorText( 'user_admin_password', $this->locale['u149a'] );
                    $notice_class = "danger";
                    $notice_message = $this->locale['u149a'];
                    addNotice( $notice_class, $notice_message );
                }
            }
        }
    }
    
    /**
     * Handle User Email Input and Validation
     */
    private function validateEmail() {
        $settings = fusion_get_settings();
        $new_user_email = sanitizer( 'user_email', '', 'user_email' );
        // To change email address old user email must be different than current one
        if ( !empty( $new_user_email ) && $new_user_email != $this->user_data['user_email'] ) {
            // Start password check
            $is_valid_password = FALSE;
            if ( $this->is_admin_panel === TRUE && ( iSUPERADMIN or ( iADMIN && checkrights( 'M' ) ) ) || $this->registration === TRUE ) {
                // override the requirements of password to change email address of a member in admin panel
                $is_valid_password = TRUE; // changing an email in administration panel
            } else {
                if ( post( 'user_email_hash' ) ) {
                    $is_valid_password = $this->verify_password( 'user_email_hash' );
                }
                if ( !$is_valid_password ) {
                    if ( post( 'account_close_hash' ) ) {
                        Defender::setInputError( 'user_email_hash' );
                        Defender::setErrorText( 'user_email_hash', 'The password you given does not match your current account password' );
                    }
                    $modal = openmodal( 'emailchange_modal', "Change of Primary Email address", [ 'static' => TRUE ] );
                    $modal .= "<div>As a security measure, please enter your current account password to change your email address.</div><hr/>";
                    $modal .= openform( 'emailChange_frm', 'post', FUSION_REQUEST, [ 'max_tokens' => 5 ] );
                    $modal .= form_text( 'user_email_hash', "Type in your Current Password", "", [ 'required' => TRUE, 'type' => 'password' ] );
                    $modal .= form_hidden( 'user_email', '', $new_user_email );
                    $modal .= form_button( 'update_email', "Confirm change email", "update_email", [ 'class' => 'btn-primary', 'input_id' => 'update_email_confirm' ] );
                    $modal .= closeform();
                    $modal .= closemodal();
                    add_to_footer( $modal );
                }
            }
            if ( $is_valid_password ) {
                // Require a valid email account
                if ( preg_check( "/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,6}$/i", $new_user_email ) ) {
                    if ( dbcount( "(blacklist_id)", DB_BLACKLIST,
                        ":email like replace(if (blacklist_email like '%@%' or blacklist_email like '%\\%%', blacklist_email, concat('%@', blacklist_email)), '_', '\\_')",
                        [ ':email' => $new_user_email ] ) ) {
                        // this email blacklisted.
                        Defender::stop();
                        Defender::setInputError( 'user_email' );
                        Defender::setErrorText( 'user_email', $this->locale['u124'] );
                    } else {
                        $email_active = dbcount( "(user_id)", DB_USERS, "user_email=:u_email", [ ':u_email' => $new_user_email ] );
                        $email_inactive = dbcount( "(user_code)", DB_NEW_USERS, "user_email=:u_email", [ ':u_email' => $new_user_email ] );
                        if ( $email_active == 0 && $email_inactive == 0 ) {
                            // Log Email
                            save_user_log( $this->user_data['user_id'], "user_email", $new_user_email, $this->user_data['user_email'] );
                            if ( $settings['email_verification'] == 1 && $this->registration === FALSE ) {
                                $this->verify_new_email( $new_user_email );
                            } else {
                                return $new_user_email;
                            }
                        } else {
                            // email taken
                            fusion_stop();
                            Defender::setInputError( 'user_email' );
                            Defender::setErrorText( 'user_email', $this->locale['u125'] );
                        }
                    }
                } else {
                    // invalid email address
                    fusion_stop();
                    Defender::setInputError( 'user_email' );
                    Defender::setErrorText( 'user_email', $this->locale['u123'] ); // once refresh, text lost.
                }
            }
        } // end of email change
    
        return (string)$new_user_email;
    }
    
    private function updateEmail() {
        if ( post( $this->post_name.'_email' ) || $this->is_admin_panel ) {
            $user_email = $this->validateEmail();
            if ( fusion_safe() ) {
                if ( $user_email ) {
                    dbquery( "UPDATE `".DB_USERS."` SET user_email=:u_email WHERE user_id=:uid", [
                        ':uid'     => $this->user_data['user_id'],
                        ':u_email' => $user_email
                    ] );
                    if ( !$this->is_admin_panel ) {
                        addNotice( "success", "Your email has been updated successfully." );
                        redirect( FUSION_REQUEST );
                    }
                }
            }
        }
    }
    
    /**
     * Deactivate User Account.
     * Administrative interface is disabled
     */
    private function closeAccount() {
        if ( $this->registration === FALSE && ( post( $this->post_name.'_close' ) ) ) {
            $is_valid_password = FALSE;
            $user_close_message = form_sanitizer( $_POST['user_close_message'], '', 'user_close_message' );
    
            if ( isset( $_POST['account_close_hash'] ) ) {
                $is_valid_password = $this->verify_password( 'account_close_hash' );
            }
            if ( !$is_valid_password ) {
                if ( isset( $_POST['account_close_hash'] ) ) {
                    Defender::setInputError( 'account_close_hash' );
                    Defender::setErrorText( 'account_close_hash', 'The password you given does not match your current account password' );
                }
                $modal = openmodal( 'userclose_modal', "Closing of your user account", [ 'static' => TRUE ] );
                $modal .= "<div>As a security measure, please enter your current account password to close your user account.</div><hr/>";
                $modal .= openform( 'accountClose_frm', 'post', FUSION_REQUEST, [ 'max_tokens' => 5 ] );
                $modal .= form_text( 'account_close_hash', "Type in your Current Password", "", [ 'required' => TRUE, 'type' => 'password' ] );
                $modal .= form_hidden( 'user_close_message', '', $user_close_message );
                $modal .= form_button( 'update_close', "Confirm closing account", "update_close", [ 'class' => 'btn-primary', 'input_id' => 'update_close_alt' ] );
                $modal .= closeform();
                $modal .= closemodal();
                add_to_footer( $modal );
            } else {
                print_p( 'deactivating account' );
            }
        }
    }
    
}
