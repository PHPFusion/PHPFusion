<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

class UserFieldsInput {
	public $adminActivation = 1;
	public $emailVerification = 1;
	public $verifyNewEmail = FALSE;
	public $userData;
	public $validation = 0;
	public $registration = FALSE;
	// On insert or admin edit
	public $skipCurrentPass = FALSE; // FALSE to skip pass. True to validate password. New Register always FALSE.
    public $isAdminPanel = FALSE;
	private $_completeMessage;
	private $_method;
	private $_noErrors = TRUE;
	private $_userEmail;
	private $_userHideEmail;
	// New for UF 2.00
	private $_userName;
	// Passwords
	private $data = array();
	private $_isValidCurrentPassword = FALSE;
	private $_isValidCurrentAdminPassword = FALSE;
	private $_userHash = FALSE;
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
	private $_userNameChange = TRUE;
	// Flags
	private $_themeChanged = FALSE;

	/**
	 * Save User Fields
	 * @return bool - true if successful.
	 */
	public function saveInsert() {
        global $defender;

        $this->_method = "validate_insert";
        $this->_setEmptyFields();

		if ($this->_userNameChange) {
			$this->_settUserName();
		}

		$this->_setPassword();
		$this->_setUserEmail();

		if ($this->validation == 1) $this->_setValidationError();

        if ($defender->safe()) {

            if ($this->emailVerification) {
                $this->_setEmailVerification();
            } else {
                $this->_setUserDataInput();
            }

            $this->data['new_password'] = $this->_getPasswordInput('user_password1');

            if (!defined("ADMIN_PANEL")) {
                addNotice('success', $locale['u170'], fusion_get_settings('opening_page'));
            }
            return TRUE;
        }

        return FALSE;
	}

	private function _setEmptyFields() {
		$this->_userHideEmail = isset($_POST['user_hide_email']) && $_POST['user_hide_email'] == 1 ? 1 : 0;
		$userStatus = $this->adminActivation == 1 ? 2 : 0;
		if ($this->_method == "validate_insert") { // register
			$this->data = array(
				'user_id' => 0,
				'user_hide_email' => $this->_userHideEmail,
				'user_avatar' => '',
				'user_posts' => 0,
				'user_threads' => 0,
				'user_joined' => time(),
				'user_lastvisit' => 0,
				'user_ip' => USER_IP,
				'user_ip_type' => USER_IP_TYPE,
				'user_rights' => '',
				'user_groups' => '',
				'user_level' => USER_LEVEL_MEMBER,
				'user_status' => $userStatus,
				'user_theme' => 'Default',
				'user_language' => LANGUAGE,
				"user_timezone" => fusion_get_settings("timeoffset")
			);
		} elseif ($this->_method == 'validate_update') {
			$this->data['user_theme'] = (isset($_POST['user_theme'])) ? $_POST['user_theme'] : 'Default';
			$this->data['user_timezone'] = (isset($_POST['user_timezone'])) ? $_POST['user_timezone'] : fusion_get_settings('timeoffset');
			$this->data['user_hide_email'] = $this->_userHideEmail;
			$this->data['user_language'] = LANGUAGE;
		}
	}

	private function _settUserName() {
		global $locale, $defender;
        $this->_userName = isset($_POST['user_name']) ? form_sanitizer($_POST['user_name'], "",
                                                                       "user_name") : ""; //stripinput(trim(preg_replace("/ +/i", " ", $_POST['user_name']))) : "";
		// No need to check fi empty since defender handles that now
		if ($this->_userName != $this->userData['user_name']) {
			// Check for invalid characters
			if (!preg_check("/^[-0-9A-Z_@\s]+$/i", $this->_userName)) {
				$defender->stop();
				$defender->setInputError('user_name');
				$defender->setErrorText('user_name', $locale['u120']);
				addNotice('danger', $locale['u120']);
			// Make sure the username is not used already
			} else {
				$name_active = dbcount("(user_id)", DB_USERS, "user_name='".$this->_userName."'");
				$name_inactive = dbcount("(user_code)", DB_NEW_USERS, "user_name='".$this->_userName."'");
				if ($name_active == 0 && $name_inactive == 0) {
					$this->data['user_name'] = $this->_userName;
				} else {
					$defender->stop();
					$defender->setInputError('user_name');
					$defender->setErrorText('user_name', $locale['u121']);
					addNotice('danger', $locale['u121']);
				}
			}
		} else {
			// User Name Cannot Be Left Empty on Register mode
			if ($this->_method == 'validate_insert') {
				$defender->stop();
				$defender->setInputError('user_name');
				$defender->setErrorText('user_name', $locale['u122']);
				addNotice('danger', $locale['u122']);
			} else {
				$this->data['user_name'] = $this->_userName;
			}
		}
	}

	private function _setPassword() {
		global $locale, $defender;
		if ($this->_method == 'validate_insert' || $this->isAdminPanel) {
			// register have 2 fields
			$this->_newUserPassword = self::_getPasswordInput('user_password1');
			$this->_newUserPassword2 = self::_getPasswordInput('user_password2');
			if ($this->_newUserPassword) {
				// Intialize password auth
				$passAuth = new PasswordAuth();
				$passAuth->inputNewPassword = $this->_newUserPassword;
				$passAuth->inputNewPassword2 = $this->_newUserPassword2;
				$_isValidNewPassword = $passAuth->isValidNewPassword();
				switch ($_isValidNewPassword) {
					case '0':
						// New password is valid
						$this->_newUserPasswordHash = $passAuth->getNewHash();
						$this->_newUserPasswordAlgo = $passAuth->getNewAlgo();
						$this->_newUserPasswordSalt = $passAuth->getNewSalt();
						$this->data['user_algo'] = $this->_newUserPasswordAlgo;
						$this->data['user_salt'] = $this->_newUserPasswordSalt;
						$this->data['user_password'] = $this->_newUserPasswordHash;
						$this->_isValidCurrentPassword = 1;
						if (!defined('ADMIN_PANEL') && !$this->skipCurrentPass) {
							Authenticate::setUserCookie($this->userData['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo(), FALSE);
						}
						break;
					case '1':
						// New Password equal old password
						$defender->stop();
						$defender->setInputError('user_password');
						$defender->setInputError('user_new_password');
						$defender->setErrorText('user_password', $locale['error_input_check']);
						$defender->setErrorText('user_new_password', $locale['error_input_check']);
						addNotice('danger', $locale['u134'].$locale['u146'].$locale['u133'].".");
						break;
					case '2':
						// The two new passwords are not identical
						$defender->stop();
						$defender->setInputError('user_new_password');
						$defender->setInputError('user_new_password2');
						$defender->setErrorText('user_password', $locale['error_input_check']);
						$defender->setErrorText('user_new_password', $locale['error_input_check']);
						addNotice('danger', $locale['u148']);
						break;
					case '3':
						// New password contains invalid chars / symbols
						$defender->stop();
						$defender->setInputError('user_new_password');
						$defender->setErrorText('user_new_password', $locale['error_input_check']);
						addNotice('danger', $locale['u134'].$locale['u142']."<br />".$locale['u147']);
						break;
				}
			} else {
				$defender->stop();
				$defender->setInputError('user_new_password');
				$defender->setErrorText('user_new_password', $locale['error_input_check']);
				addNotice('danger', $locale['u134'].$locale['u143a']);
			}
		} else {
			// edit profile have 3 fields
			$this->_userPassword = self::_getPasswordInput('user_password');
			$this->_newUserPassword = self::_getPasswordInput('user_password1');
			$this->_newUserPassword2 = self::_getPasswordInput('user_password2');
			// check password integrity
			if ($this->_userPassword) {
				// Intialize password auth
				$passAuth = new PasswordAuth();
				$passAuth->inputPassword = $this->_userPassword;
				$passAuth->inputNewPassword = $this->_newUserPassword;
				$passAuth->inputNewPassword2 = $this->_newUserPassword2;
				$passAuth->currentPasswordHash = $this->userData['user_password'];
				$passAuth->currentAlgo = $this->userData['user_algo'];
				$passAuth->currentSalt = $this->userData['user_salt'];
				if ($passAuth->isValidCurrentPassword()) {
					$this->_isValidCurrentPassword = 1;
					$_isValidNewPassword = $passAuth->isValidNewPassword();
					switch ($_isValidNewPassword) {
						case '0':
							// New password is valid
							$this->_newUserPasswordHash = $passAuth->getNewHash();
							$this->_newUserPasswordAlgo = $passAuth->getNewAlgo();
							$this->_newUserPasswordSalt = $passAuth->getNewSalt();
							$this->data['user_algo'] = $this->_newUserPasswordAlgo;
							$this->data['user_salt'] = $this->_newUserPasswordSalt;
							$this->data['user_password'] = $this->_newUserPasswordHash;
							if (!defined('ADMIN_PANEL') && !$this->skipCurrentPass) {
								//Authenticate::setUserCookie($this->userData['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo(), FALSE);
							}
							break;
						case '1':
							// New Password equal old password
							$defender->stop();
							$defender->setInputError('user_password');
							$defender->setInputError('user_new_password');
							$defender->setErrorText('user_password', $locale['error_input_check']);
							$defender->setErrorText('user_new_password', $locale['error_input_check']);
							addNotice('danger', $locale['u134'].$locale['u146'].$locale['u133'].".");
							break;
						case '2':
							// The two new passwords are not identical
							$defender->stop();
							$defender->setInputError('user_new_password');
							$defender->setInputError('user_new_password2');
							$defender->setErrorText('user_password', $locale['error_input_check']);
							$defender->setErrorText('user_new_password', $locale['error_input_check']);
							addNotice('danger', $locale['u148']);
							break;
						case '3':
							// New password contains invalid chars / symbols
							$defender->stop();
							$defender->setInputError('user_new_password');
							$defender->setErrorText('user_new_password', $locale['error_input_check']);
							addNotice('danger', $locale['u134'].$locale['u142']."<br />".$locale['u147']);
							break;
					}
				} else {
					$defender->stop();
					$defender->setInputError('user_password');
					$defender->setErrorText('user_password', $locale['error_input_check']);
					addNotice('danger', $locale['u149']);
				}
			}
		}
	}

	private function _getPasswordInput($field) {
		return isset($_POST[$field]) && $_POST[$field] != "" ? $_POST[$field] : FALSE;
	}

	private function _setUserEmail() {
		global $locale, $settings, $defender;
		$this->_userEmail = (isset($_POST['user_email']) ? stripinput(trim(preg_replace("/ +/i", " ", $_POST['user_email']))) : "");
		if ($this->_userEmail != "" && $this->_userEmail != $this->userData['user_email']) {
			// override the requirements of password to change email address of a member in admin panel
			if (iADMIN && checkrights("M")) {
				$this->_isValidCurrentPassword = true;
			}
			// Require user password for email change
			if ($this->_isValidCurrentPassword || $this->registration) {
				// Require a valid email account
				if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,6}$/i", $this->_userEmail)) {
					if (dbcount("(blacklist_id)", DB_BLACKLIST, ":email like replace(if (blacklist_email like '%@%' or blacklist_email like '%\\%%', blacklist_email, concat('%@', blacklist_email)), '_', '\\_')", array(':email' => $this->_userEmail))) {
						// this email blacklisted.
						$defender->stop();
						$defender->setInputError('user_email');
						$defender->setErrorText('user_email', $locale['u124']);
						addNotice('danger', $locale['u124']);
					} else {
						$email_active = dbcount("(user_id)", DB_USERS, "user_email='".$this->_userEmail."'");
						$email_inactive = dbcount("(user_code)", DB_NEW_USERS, "user_email='".$this->_userEmail."'");
						if ($email_active == 0 && $email_inactive == 0) {
							if ($this->verifyNewEmail && $settings['email_verification'] == "1") {
								$this->_verifyNewEmail();
							} else {
								$this->_userLogFields[] = "user_email";
								$this->data['user_email'] = $this->_userEmail;
							}
						} else {
							// email taken
							$defender->stop();
							$defender->setInputError('user_email');
							$defender->setErrorText('user_password', $locale['u125']);
							addNotice('danger', $locale['u125']);
						}
					}
				} else {
					// invalid email address
					$defender->stop();
					$defender->setInputError('user_email');
					$defender->setErrorText('user_email', $locale['u123']); // once refresh, text lost.
					addNotice('danger', $locale['u123']);
				}
			} else {
				// must have a valid password to change email
				$defender->stop();
				$defender->setInputError('user_email');
				$defender->setErrorText('user_email', $locale['u156']);
				addNotice('danger', $locale['u156']);
			}
		} else {
			if ($this->_method !== 'validate_update') { // for register only
				$defender->stop();
				$defender->setInputError('user_email');
				$defender->setErrorText('user_email', $locale['u126']);
				addNotice('danger', $locale['u126']);
			}
		}
	}

	private function _verifyNewEmail() {
		global $locale, $settings, $userdata;
		require_once INCLUDES."sendmail_include.php";
		mt_srand((double)microtime()*1000000);
		$salt = "";
		for ($i = 0; $i <= 10; $i++) {
			$salt .= chr(rand(97, 122));
		}
		$user_code = md5($this->_userEmail.$salt);
		$email_verify_link = $settings['siteurl']."edit_profile.php?code=".$user_code;

        $mailbody = str_replace("[EMAIL_VERIFY_LINK]", $email_verify_link, $locale['u203']);
        $mailbody = str_replace("[SITENAME]", fusion_get_settings("sitename"), $mailbody);
        $mailbody = str_replace("[SITEUSERNAME]", fusion_get_settings("siteusername"), $mailbody);
		$mailbody = str_replace("[USER_NAME]", $userdata['user_name'], $mailbody);

        $mailSubject = str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['u202']);

        sendemail($this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'], $mailSubject,
                  $mailbody);

        dbquery("DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$this->userData['user_id']."'");
        dbquery("INSERT INTO ".DB_EMAIL_VERIFY." (user_id, user_code, user_email, user_datestamp) VALUES('".$this->userData['user_id']."', '$user_code', '".$this->_userEmail."', '".time()."')");
	}

	// Get New Password Hash and Directly Set New Cookie if Authenticated

	private function _setValidationError() {
		global $locale, $settings, $defender;
		$_CAPTCHA_IS_VALID = FALSE;
		include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
		if ($_CAPTCHA_IS_VALID == FALSE) {
			$defender->stop();
			$defender->setInputError('user_captcha');
			addNotice('danger', $locale['u194']);
		}
	}

	private function _setEmailVerification() {
		global $settings, $locale, $defender;
		require_once INCLUDES."sendmail_include.php";
		$userCode = hash_hmac("sha1", PasswordAuth::getNewPassword(), $this->_userEmail);
		$activationUrl = $settings['siteurl']."register.php?email=".$this->_userEmail."&code=".$userCode;

		$message = str_replace("USER_NAME", $this->_userName, $locale['u152']);
        $message = str_replace("SITENAME", fusion_get_settings("sitename"), $message);
        $message = str_replace("SITEUSERNAME", fusion_get_settings("siteusername"), $message);
		$message = str_replace("USER_PASSWORD", $this->_newUserPassword, $message);
		$message = str_replace("ACTIVATION_LINK", $activationUrl, $message);

        $subject = str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['u151']);

        if (sendemail($this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'], $subject,
                      $message)) {
			$user_info = array();
			$quantum = new QuantumFields();
			$quantum->setCategoryDb(DB_USER_FIELD_CATS);
			$quantum->setFieldDb(DB_USER_FIELDS);
			$quantum->setPluginFolder(INCLUDES."user_fields/");
			$quantum->setPluginLocaleFolder(LOCALE.LOCALESET."user_fields/");
			$quantum->set_Fields();
			$quantum->load_field_cats();
			$quantum->setCallbackData($this->data);
			$fields_input = $quantum->return_fields_input(DB_USERS, 'user_id');
			// how to update all the field tables without override its value?
			if (!empty($fields_input)) {
				foreach ($fields_input as $table_name => $fields_array) {
					$user_info += $fields_array;
				}
			}
			$userInfo = serialize($user_info);
			$userInfo = addslash($userInfo);
			$result = dbquery("INSERT INTO ".DB_NEW_USERS."
					(user_code, user_name, user_email, user_datestamp, user_info)
					VALUES
					('".$userCode."', '".$this->data['user_name']."', '".$this->data['user_email']."', '".time()."', '".$userInfo."'
					)");
			$this->_completeMessage = $locale['u150'];
		} else {
			$defender->stop();
            $message = str_replace("[LINK]", "<a href='".BASEDIR."contact.php'><strong>", $locale['u154']);
            $message = str_replace("[/LINK]", "</strong></a>", $message);
            addNotice('danger', $locale['u153']."<br />".$message);
		}
	}

	// Set New User Email

	private function _setUserDataInput() {
        global $locale, $aidlink, $defender;

        $settings = fusion_get_settings();

		$user_info = array();
		$quantum = new QuantumFields();
		$quantum->setCategoryDb(DB_USER_FIELD_CATS);
		$quantum->setFieldDb(DB_USER_FIELDS);
		$quantum->setPluginFolder(INCLUDES."user_fields/");
		$quantum->setPluginLocaleFolder(LOCALE.LOCALESET."user_fields/");
		$quantum->set_Fields();
		$quantum->load_field_cats();
		$quantum->setCallbackData($this->data);
		$fields_input = $quantum->return_fields_input(DB_USERS, 'user_id');
		// how to update all the field tables without override its value?
		if (!empty($fields_input)) {
			foreach ($fields_input as $table_name => $fields_array) {
				$user_info += $fields_array;
			}
		}
        if ($defender->safe()) {
			$user_info['user_level'] = -101;
		}
		dbquery_insert(DB_USERS, $user_info, 'save', array('keep_session' => 1));
		if ($this->adminActivation) {
			$this->_completeMessage = $locale['u160']."<br /><br />\n".$locale['u162'];
		} else {
			if (!defined('ADMIN_PANEL')) {
				$this->_completeMessage = $locale['u160']."<br /><br />\n".$locale['u161'];
			} else {
				require_once LOCALE.LOCALESET."admin/members_email.php";
				require_once INCLUDES."sendmail_include.php";
                $subject      = str_replace("[SITENAME]", $settings['sitename'], $locale['email_create_subject']);
                $replace_this = array("[USER_NAME]", "[PASSWORD]", "[SITENAME]", "[SITEUSERNAME]");
                $replace_with = array($this->_userName, $this->_newUserPassword, $settings['sitename'], $settings['siteusername']);
				$message = str_replace($replace_this, $replace_with, $locale['email_create_message']);
				sendemail($this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'], $subject, $message);
				$this->_completeMessage = $locale['u172']."<br /><br />\n<a href='members.php".$aidlink."'>".$locale['u173']."</a>";
				$this->_completeMessage .= "<br /><br /><a href='members.php".$aidlink."&amp;step=add'>".$locale['u174']."</a>";
			}
		}
	}

	// Send Verification code when you change email

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

	// Captcha validation

    /**
     * Update User Fields
     * @return bool
     */
	public function saveUpdate() {
		global $locale, $defender;
		$this->_method = "validate_update";
		$this->data = $this->userData;
		self::_setEmptyFields();
		$this->_settUserName();
		$this->_setPassword();
		if (!defined('ADMIN_PANEL')) $this->_setAdminPassword();
		$this->_setUserEmail();
		if ($this->validation == 1) $this->_setValidationError();
		$this->_setUserAvatar();

		if ($defender->safe()) {
            $this->_setUserDataUpdate();
            $settings = fusion_get_settings();
            if ($this->isAdminPanel && $this->_isValidCurrentPassword && $this->_newUserPassword && $this->_newUserPassword2) {
                // inform user that password has changed. and tell him your new password
                include INCLUDES."sendmail_include.php";
                addNotice("success", str_replace("USER_NAME", $this->userData['user_name'], $locale['global_458']));
                $input = array(
                    "mailname" => $this->userData['user_name'],
                    "email" => $this->userData['user_email'],
                    "subject" => str_replace("[SITENAME]", $settings['sitename'], $locale['global_456']),
                    "message" => str_replace(
                        array(
                            "[SITENAME]",
                            "[SITEUSERNAME]",
                            "USER_NAME",
                            "[PASSWORD]"
                        ),
                        array(
                            $settings['sitename'],
                            $settings['siteusername'],
                            $this->userData['user_name'],
                            $this->_newUserPassword,
                        ),
                        $locale['global_457']
                    )
                );
                if (!sendemail($input['mailname'], $input['email'], $settings['siteusername'], $settings['siteemail'], $input['subject'], $input['message'])) {
                    addNotice('warning', str_replace("USER_NAME", $this->userData['user_name'], $locale['global_459']));
                }
            }
            if ($defender->safe()) {
                addNotice('success', $locale['u169']);
            }
			return true;
		}
		return false;
	}

	private function _setAdminPassword() {
		global $locale, $defender;
		if ($this->_getPasswordInput("user_admin_password")) { // if submit current admin password
			$this->_userAdminPassword = $this->_getPasswordInput("user_admin_password"); // var1
			$this->_newUserAdminPassword = $this->_getPasswordInput("user_admin_password1"); // var2
			$this->_newUserAdminPassword2 = $this->_getPasswordInput("user_admin_password2"); // var3
			$passAuth = new PasswordAuth();

			//print_p($this->_userAdminPassword); // this is not available if no password exist
			//print_p($this->_newUserAdminPassword);
			//print_p($this->_newUserAdminPassword2);

			if (!$this->userData['user_admin_password'] && !$this->userData['user_admin_salt']) {
				// New Admin
				//echo 'yes we are new admin';
				$valid_current_password = 1;
				$passAuth->inputPassword = 'fake';
				$passAuth->inputNewPassword = $this->_userAdminPassword;
				$passAuth->inputNewPassword2 = $this->_newUserAdminPassword2;
			} else {
				// Old Admin
				// Intialize password auth
				$passAuth->inputPassword = $this->_userAdminPassword; // var1
				$passAuth->inputNewPassword = $this->_newUserAdminPassword; // var2
				$passAuth->inputNewPassword2 = $this->_newUserAdminPassword2; // var3
				$passAuth->currentPasswordHash = $this->userData['user_admin_password'];
				//print_p($passAuth);
				$passAuth->currentAlgo = $this->userData['user_admin_algo'];
				$passAuth->currentSalt = $this->userData['user_admin_salt'];
				$valid_current_password = $passAuth->isValidCurrentPassword();
				//print_p($valid_current_password);
			}

			if ($valid_current_password) {
				$this->_isValidCurrentAdminPassword = 1;
				// authenticated. now do the integrity check
				$_isValidNewPassword = $passAuth->isValidNewPassword();
				//print_p($_isValidNewPassword);
				switch ($_isValidNewPassword) {
					case '0':
						// New password is valid
						$new_admin_password = $passAuth->getNewHash();
						$new_admin_salt = $passAuth->getNewSalt();
						$new_admin_algo = $passAuth->getNewAlgo();
						$this->data['user_admin_algo'] = $new_admin_algo;
						$this->data['user_admin_salt'] = $new_admin_salt;
						$this->data['user_admin_password'] = $new_admin_password;
						//print_p($this->data);
						break;
					case '1':
						// new password is old password
						$defender->stop();
						$defender->setInputError('user_admin_password');
						$defender->setInputError('user_admin_password1');
						$defender->setErrorText('user_admin_password', $locale['error_input_check']);
						$defender->setErrorText('user_admin_password1', $locale['error_input_check']);
						addNotice('danger', $locale['u144'].$locale['u146'].$locale['u133']);
						break;
					case '2':
						// The two new passwords are not identical
						$defender->stop();
						$defender->setInputError('user_new_admin_password');
						$defender->setInputError('user_new_admin_password2');
						$defender->setErrorText('user_new_admin_password', $locale['error_input_check']);
						$defender->setErrorText('user_new_admin_password2', $locale['error_input_check']);
						addNotice('danger', $locale['u144'].$locale['u148a']);
						break;
					case '3':
						// New password contains invalid chars / symbols
						$defender->stop();
						$defender->setInputError('user_new_admin_password');
						$defender->setErrorText('user_new_admin_password', $locale['error_input_check']);
						addNotice('danger', $locale['u144'].$locale['u142']."<br />".$locale['u147']);
						break;
				}
			} else {
				// 149 for admin
				$defender->stop();
				$defender->setInputError('user_admin_password');
				$defender->setErrorText('user_admin_password', $locale['error_input_check']);
				addNotice('danger', $locale['u149a']);
			}
		} else { // check db only - admin cannot save profile page without password
			//print_p($this->userData['user_level']);
			if (iADMIN) {
				$require_valid_password = $this->userData['user_admin_password'] ? TRUE : FALSE;
				if (!$require_valid_password) {
					// 149 for admin
					$defender->stop();
					$defender->setInputError('user_admin_password');
					$defender->setErrorText('user_admin_password', $locale['error_input_check']);
					addNotice('danger', $locale['u149a']);
				}
			}
		}
	}

	private function _setUserAvatar() {
		if (isset($_POST['delAvatar'])) {
			if ($this->userData['user_avatar'] != "" && file_exists(IMAGES."avatars/".$this->userData['user_avatar']) && is_file(IMAGES."avatars/".$this->userData['user_avatar'])) {
				unlink(IMAGES."avatars/".$this->userData['user_avatar']);
			}
			$this->data['user_avatar'] = '';
		}
		if (isset($_FILES['user_avatar']) && $_FILES['user_avatar']['name']) { // uploaded avatar
			if (!empty($_FILES['user_avatar']) && is_uploaded_file($_FILES['user_avatar']['tmp_name'])) {
				$upload = form_sanitizer($_FILES['user_avatar'], "", "user_avatar");
				if ($upload['error'] == 0) {
					$this->data['user_avatar'] = $upload['image_name'];
				}
			}
		}
	}

	// Get Password Input - if empty return false

	private function _setUserDataUpdate() {
		global $locale;
		$user_info = array();
		$quantum = new QuantumFields();
		$quantum->setCategoryDb(DB_USER_FIELD_CATS);
		$quantum->setFieldDb(DB_USER_FIELDS);
		$quantum->setPluginFolder(INCLUDES."user_fields/");
		$quantum->setPluginLocaleFolder(LOCALE.LOCALESET."user_fields/");
		$quantum->set_Fields();
		$quantum->load_field_cats();
		$quantum->setCallbackData($this->data);
		$fields_input = $quantum->return_fields_input(DB_USERS, 'user_id');
		if (!empty($fields_input)) {
			foreach ($fields_input as $table_name => $fields_array) {
				$user_info += $fields_array;
			}
		}
		if (\defender::safe()) {
			if ($this->_userName != $this->userData['user_name']) {
				save_user_log($this->userData['user_id'], "user_name", $this->_userName, $this->userData['user_name']);
			}
			if ($this->_userEmail != $this->userData['user_email']) {
				save_user_log($this->userData['user_id'], "user_email", $this->_userEmail, $this->userData['user_email']);
			}
		}
		$quantum->log_user_action(DB_USERS, "user_id");

        // @todo: now that updates doesn't override unspecified column, i think can remove this line. confirm later.
		if (iADMIN) {
			$user_info['user_admin_algo'] = $this->data['user_admin_algo'];
			$user_info['user_admin_salt'] = $this->data['user_admin_salt'];
			$user_info['user_admin_password'] = $this->data['user_admin_password'];
		}

        dbquery_insert(DB_USERS, $user_info, 'update');

        $this->_completeMessage = $locale['u163'];
	}

	public function setUserNameChange($value) {
		$this->_userNameChange = $value;
	}

	public function verifyCode($value) {
		global $locale, $userdata;
		if (!preg_check("/^[0-9a-z]{32}$/i", $value)) redirect("index.php");
		$result = dbquery("SELECT * FROM ".DB_EMAIL_VERIFY." WHERE user_code='".$value."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			if ($data['user_id'] == $userdata['user_id']) {
				if ($data['user_email'] != $userdata['user_email']) {
					$result = dbquery("SELECT user_email FROM ".DB_USERS." WHERE user_email='".$data['user_email']."'");
					if (dbrows($result)) {
						$this->_noErrors = FALSE;
						$this->_errorMessages[0] = $locale['u164']."<br />\n".$locale['u121'];
					} else {
						$this->_completeMessage = $locale['u169'];
					}
					$result = dbquery("UPDATE ".DB_USERS." SET user_email='".$data['user_email']."' WHERE user_id='".$data['user_id']."'");
					$result = dbquery("DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$data['user_id']."'");
				}
			} else {
				redirect("index.php");
			}
		} else {
			redirect("index.php");
		}
	}

	public function themeChanged() {
		return $this->_themeChanged;
	}
}