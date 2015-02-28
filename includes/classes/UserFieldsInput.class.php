<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserFieldsInput.class.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

require_once CLASSES."PasswordAuth.class.php";

class UserFieldsInput {
	public $adminActivation 				= 1;
	public $emailVerification 				= 1;
	public $isAdminPanel 					= false;
	public $verifyNewEmail					= false;
	public $userData;
	public $validation 						= 0;
	public $registration					= false;

	// On insert or admin edit
	public $skipCurrentPass					= false;

	private $_completeMessage;
	private $_errorMessages 				= array();
	private $_fieldsRequired 				= array();
	private $_method;
	private $_noErrors 						= true;
	private $_userEmail;
	private $_userHideEmail;
	private $_userName;

	// Passwords
	private $_isValidCurrentPassword		= false;
	private $_isValidCurrentAdminPassword	= false;

	private $_userHash						= false;
	private $_userPassword					= false;
	private $_newUserPassword				= false;
	private $_newUserPassword2				= false;

	private $_newUserPasswordHash			= false;
	private $_newUserPasswordSalt			= false;
	private $_newUserPasswordAlgo			= false;

	private $_userAdminPassword 			= false;
	private $_newUserAdminPassword 			= false;
	private $_newUserAdminPassword2 		= false;

	// Database inputs
	private $_dbFields;
	private $_dbValues;

	// User Log System
	private $_userLogData					= array();
	private $_userLogFields					= array();

	// Settings
	private $_userNameChange				= true;

	// Flags
	private $_themeChanged					= false;

	public function saveInsert() {
		$this->_method = "validate_insert";

		$this->userData = array(
			"user_password" => "", "user_algo" => "", "user_salt" => "",
			"user_admin_password" => "", "user_admin_algo" => "", "user_admin_salt" => "",
			"user_name" => "", "user_email" => ""
		);

		$this->_fieldsRequired = array(
			"user_name" 		=> true,
			"user_password" 	=> true,
			"user_email" 		=> true,
			"user_captcha" 		=> true,
			"email_activation" 	=> true
		);

		if ($this->_userNameChange) {
			$this->_settUserName();
		}
		$this->_setNewUserPassword();
		$this->_setUserEmail();
		if ($this->validation == 1) { $this->_setValidationError(); }
		$this->_setEmptyFields();
		$this->_setCustomUserFieldsData();
		if ($this->_noErrors) {
			if ($this->emailVerification) { $this->_setEmailVerification(); }
			else { $this->_setUserDataInput(); }
		}
	}

	public function saveUpdate() {
		$this->_method = "validate_update";

		$this->_settUserName();
		$this->_setNewUserPassword();
		$this->_setNewAdminPassword();
		$this->_setUserEmail();
		if ($this->validation == 1) { $this->_setValidationError(); }
		$this->_setEmptyFields();
		$this->_setUserAvatar();
		$this->_setCustomUserFieldsData();
		if ($this->_noErrors) { $this->_setUserDataUpdate(); }
	}

	public function getErrorsArray() {
		return $this->_errorMessages;
	}

	public function displayMessages() {
		global $locale;

		if ($this->_noErrors) {
			if ($this->_method == "validate_insert") {
				$title = $locale['u170'];
				$message = "<br />\n".$this->_completeMessage."<br /><br />\n";
			} else {
				$title = $locale['u169'];
				$message = "<br />\n".$this->_completeMessage."<br /><br />\n";
			}
		} else {
			$title = $this->_method == "validate_insert" ? $locale['u165'] : $locale['u164'];
			$message = $title." ".$locale['u167']."<br /><br />\n";
			foreach ($this->_errorMessages as $err => $msg) {
				$message .= $msg."<br />\n";
			}
			$message .= "\n".$locale['u168'];
		}
		opentable($title);
		echo "<div style='text-align:center'>".$message."</div>\n";
		closetable();
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
						$this->_noErrors = false;
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

	private function _settUserName() {
		global $locale;
		$this->_userName = isset($_POST['user_name']) ? stripinput(trim(preg_replace("/ +/i", " ", $_POST['user_name']))) : "";

		if ($this->_userName != "" && $this->_userName != $this->userData['user_name']) {
			if (!preg_check("/^[-0-9A-Z_@\s]+$/i", $this->_userName)) {
				$this->_setError("user_name", $locale['u120']);
			} else {
				$name_active = dbcount("(user_id)", DB_USERS, "user_name='".$this->_userName."'");
				$name_inactive = dbcount("(user_code)", DB_NEW_USERS, "user_name='".$this->_userName."'");
				if ($name_active == 0 && $name_inactive == 0) {
					$this->_userLogFields[] = "user_name";
					$this->_setDBValue("user_name", $this->_userName);
				} else {
					$this->_setError("user_name", $locale['u121']);
				}
			}
		} else {
			$this->_setError("user_name", $locale['u122'], true);
		}
	}

	private function _isValidCurrentPassword($loginPass = true, $skipCurrentPass = false) {
		if ($loginPass && !$skipCurrentPass) {
			$this->_userHash 							= $this->_getPasswordInput("user_hash");
			$this->_userPassword 						= $this->_getPasswordInput("user_password");
			$password									= $this->_userPassword;
			$hash										= $this->userData['user_password'];
			$salt										= $this->userData['user_salt'];
			$algo										= $this->userData['user_algo'];
		} elseif ($loginPass == false && !$skipCurrentPass) {
			$this->_userAdminPassword 					= $this->_getPasswordInput("user_admin_password");
			$password									= $this->_userAdminPassword;
			$hash										= $this->userData['user_admin_password'];
			$salt										= $this->userData['user_admin_salt'];
			$algo										= $this->userData['user_admin_algo'];
		}

		if ($skipCurrentPass == false) {
			// Check user auth
			if ($loginPass && $this->_userHash != $hash) {
				redirect(BASEDIR."index.php");
			}

			// Intialize password auth
			$passAuth 									= new PasswordAuth();
			$passAuth->inputPassword 					= $password;
			$passAuth->currentAlgo 						= $algo;
			$passAuth->currentSalt						= $salt;
			$passAuth->currentPasswordHash 				= $hash;

			// Check if password is correct
			if ($passAuth->isValidCurrentPassword(false)) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	// Set New User Password
	private function _setNewUserPassword() {
		global $locale;

		$this->_isValidCurrentPassword					= $this->_isValidCurrentPassword(true, $this->skipCurrentPass);
		$this->_newUserPassword				 			= $this->_getPasswordInput("user_new_password");
		$this->_newUserPassword2 						= $this->_getPasswordInput("user_new_password2");

		if ($this->_newUserPassword) {
			// Set new password
			if ($this->_isValidCurrentPassword) {
				// Intialize password auth
				$passAuth 								= new PasswordAuth();
				$passAuth->inputPassword 				= $this->_userPassword;
				$passAuth->inputNewPassword				= $this->_newUserPassword;
				$passAuth->inputNewPassword2			= $this->_newUserPassword2;

				// Check new password
				$_isValidNewPassword = $passAuth->isValidNewPassword();

				if ($_isValidNewPassword === 0) {
					// New password is valid
					$this->_newUserPasswordHash = $passAuth->getNewHash();
					$this->_newUserPasswordAlgo = $passAuth->getNewAlgo();
					$this->_newUserPasswordSalt = $passAuth->getNewSalt();

					$this->_setDBValue("user_algo", $this->_newUserPasswordAlgo);
					$this->_setDBValue("user_salt", $this->_newUserPasswordSalt);
					$this->_setDBValue("user_password", $this->_newUserPasswordHash);
					if (!$this->isAdminPanel && !$this->skipCurrentPass) {
						Authenticate::setUserCookie($this->userData['user_id'], $passAuth->getNewSalt(),
													$passAuth->getNewAlgo(), false);
					}
				} else {
					if ($_isValidNewPassword === 1) {
						// New Password equal old password
						$this->_setError("user_password", $locale['u134'].$locale['u146'].$locale['u133'].".");
					} elseif ($_isValidNewPassword === 2) {
						// The two new passwords are not identical
						$this->_setError("user_password", $locale['u148']);
					} elseif ($_isValidNewPassword === 3) {
						// New password contains invalid chars / symbols
						$this->_setError("user_password", $locale['u134'].$locale['u142']."<br />".$locale['u147']);
					}
				}
			} else {
				// Current user password is invalid
				$this->_setError("user_password", $locale['u149']);
			}
		} else {
			// New user password is empty
			$this->_setError("user_password", $locale['u134'].$locale['u143a'], true);
		}
	}

	// Set New Admin Password
	private function _setNewAdminPassword() {
		global $locale;

		// Only accept if user is admin, updating his profile (not admin panel)
		if (iADMIN && $this->_method == "validate_update" && !$this->isAdminPanel) {

			if ($this->_getPasswordInput("user_admin_password") == "") {
				if ($this->userData['user_admin_password'] == "") {
					$this->_isValidCurrentAdminPassword			= true;
					$showError = false;
				} else {
					$this->_isValidCurrentAdminPassword			= false;
					$showError = true;
				}
			} else {
				$this->_isValidCurrentAdminPassword			= $this->_isValidCurrentPassword(false, false);
				$showError = true;
			}

			//$this->_isValidCurrentAdminPassword				= $this->_isValidCurrentPassword(false, false);

			$this->_newUserAdminPassword 					= $this->_getPasswordInput("user_new_admin_password");
			$this->_newUserAdminPassword2 					= $this->_getPasswordInput("user_new_admin_password2");

			// Require current password
			if ($this->_isValidCurrentAdminPassword) {
				// Require current admin password
				if ($this->_isValidCurrentPassword) {
					if ($this->_userAdminPassword != $this->_userPassword) {
						// Intialize password auth
						$passAuth 						= new PasswordAuth();
						$passAuth->inputPassword 		= $this->_userAdminPassword;
						$passAuth->inputNewPassword		= $this->_newUserAdminPassword;
						$passAuth->inputNewPassword2	= $this->_newUserAdminPassword2;

						// Check admin new password
						$_isValidNewPassword = $passAuth->isValidNewPassword();

						if ($_isValidNewPassword === 0) {
							// New password is valid
							$this->_setDBValue("user_admin_algo", $passAuth->getNewAlgo());
							$this->_setDBValue("user_admin_salt", $passAuth->getNewSalt());
							$this->_setDBValue("user_admin_password", $passAuth->getNewHash());
						} else {
							if ($_isValidNewPassword === 1) {
								// New Password equal old password
								$this->_setError("user_password", $locale['u144'].$locale['u146'].$locale['u131']);
							} elseif ($_isValidNewPassword === 2) {
								// The two new passwords are not identical
								$this->_setError("user_password", $locale['u148a']);
							} elseif ($_isValidNewPassword === 3) {
								// New password contains invalid chars / symbols
								$this->_setError("user_password", $locale['u144'].$locale['u142']."<br />".$locale['u147']);
							}
						}
					} else {
						// New admin password equal Login password
						$this->_setError("user_admin_password", $locale['u144'].$locale['u146'].$locale['u133']);
					}
				} else {
					// Current login password is invalid
					$this->_setError("user_admin_password", $locale['u149b']);
				}
			} else {
				// Current admin password is invalid
				$this->_setError("user_admin_password", $locale['u149a'], $showError);
			}
		}
	}

	// Set New User Email
	private function _setUserEmail() {
		global $locale, $settings;

		$this->_userEmail = (isset($_POST['user_email']) ? stripinput(trim(preg_replace("/ +/i", " ", $_POST['user_email']))) : "");

		if ($this->_userEmail != "" && $this->_userEmail != $this->userData['user_email']) {
			// Require user password for email change
			if ($this->_isValidCurrentPassword) {
				// Require a valid email account
				if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $this->_userEmail)) {
					$email_domain = substr(strrchr($this->_userEmail, "@"), 1);
					if (dbcount("(blacklist_id)", DB_BLACKLIST, "blacklist_email='".$this->_userEmail."' OR blacklist_email='".$email_domain."'") != 0) {
						$this->_setError("user_email", $locale['u124']);
					} else {
						$email_active = dbcount("(user_id)", DB_USERS, "user_email='".$this->_userEmail."'");
						$email_inactive = dbcount("(user_code)", DB_NEW_USERS, "user_email='".$this->_userEmail."'");
						if ($email_active == 0 && $email_inactive == 0) {
							if ($this->verifyNewEmail && $settings['email_verification'] == "1") {
								$this->_verifyNewEmail();
							} else {
								$this->_userLogFields[] = "user_email";
								$this->_setDBValue("user_email", $this->_userEmail);
							}
						} else {
							$this->_setError("user_email", $locale['u125']);
						}
					}
				} else {
					$this->_setError("user_email", $locale['u123']);
				}
			} else {
				$this->_setError("user_email", $locale['u156']);
			}
		} else {
			$this->_setError("user_email", $locale['u126'], true);
		}
	}

	private function _verifyNewEmail() {
		global $locale, $settings, $userdata;
		require_once INCLUDES."sendmail_include.php";
		mt_srand((double)microtime()*1000000); $salt = "";
		for ($i = 0; $i <= 10; $i++) { $salt .= chr(rand(97, 122)); }
		$user_code = md5($this->_userEmail.$salt);
		$email_verify_link = $settings['siteurl']."edit_profile.php?code=".$user_code;
		$mailbody = str_replace("[EMAIL_VERIFY_LINK]", $email_verify_link, $locale['u203']);
		$mailbody = str_replace("[USER_NAME]", $userdata['user_name'], $mailbody);
		sendemail($this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'], $locale['u202'], $mailbody);
		$result = dbquery("DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$this->userData['user_id']."'");
		$result = dbquery("INSERT INTO ".DB_EMAIL_VERIFY." (user_id, user_code, user_email, user_datestamp) VALUES('".$this->userData['user_id']."', '$user_code', '".$this->_userEmail."', '".time()."')");
	}

	private function _setValidationError() {
		global $locale, $settings;

		$_CAPTCHA_IS_VALID = false;
		include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
		if ($_CAPTCHA_IS_VALID == false) { $this->_setError("user_captcha", $locale['u194']); }
	}

	private function _setUserAvatar() {
		global $locale, $settings;
		
		if (isset($_POST['delAvatar'])) {
			if ($this->userData['user_avatar'] != "" && file_exists(IMAGES."avatars/".$this->userData['user_avatar']) &&
				is_file(IMAGES."avatars/".$this->userData['user_avatar']) ) {
				unlink(IMAGES."avatars/".$this->userData['user_avatar']);
			}
			$this->_setDBValue("user_avatar", "");
		}

		if (isset($_FILES['user_avatar']) && $_FILES['user_avatar']['name'] != "") {
			require_once INCLUDES."infusions_include.php";
			$avatarUpload = upload_image(
				"user_avatar", "", IMAGES."avatars/", "2000", "2000", $settings['avatar_filesize'], true, true, false,
				$settings['avatar_ratio'], IMAGES."avatars/", "[".$this->userData['user_id']."]", $settings['avatar_width'], $settings['avatar_height']
			);
			if ($avatarUpload['error'] == 0) {
				if ($this->userData['user_avatar'] != "" && file_exists(IMAGES."avatars/".$this->userData['user_avatar']) &&
					is_file(IMAGES."avatars/".$this->userData['user_avatar']) ) {
					unlink(IMAGES."avatars/".$this->userData['user_avatar']);
				}
				$this->_setDBValue("user_avatar", $avatarUpload['thumb1_name']);
			} elseif ($avatarUpload['error'] == 1) {
				$this->_setError("user_avatar", $locale['u180']);
			} elseif ($avatarUpload['error'] == 2) {
				$this->_setError("user_avatar", $locale['u181']);
			} elseif ($avatarUpload['error'] == 3) {
				$this->_setError("user_avatar", $locale['u182']);
			} elseif ($avatarUpload['error'] == 4) {
				// Invalid query string
			} elseif ($avatarUpload['error'] == 5) {
				$this->_setError("user_avatar", $locale['u183']);
			}
		}
	}

	private function _setEmptyFields() {
		$this->_userHideEmail = isset($_POST['user_hide_email']) && $_POST['user_hide_email'] == 1 ? 1 : 0;

		$userStatus = $this->adminActivation == 1 ? 2 : 0;

		if ($this->_method == "validate_insert") {
			$this->_setDBValue("user_hide_email", $this->_userHideEmail);
			$this->_setDBValue("user_avatar", "");
			$this->_setDBValue("user_posts", 0);
			$this->_setDBValue("user_threads", 0);
			$this->_setDBValue("user_joined", time());
			$this->_setDBValue("user_lastvisit", 0);
			$this->_setDBValue("user_ip", USER_IP);
			$this->_setDBValue("user_ip_type", USER_IP_TYPE);
			$this->_setDBValue("user_rights", "");
			$this->_setDBValue("user_groups", "");
			$this->_setDBValue("user_level", 101);
			$this->_setDBValue("user_status", $userStatus);
		} else {
			$this->_setDBValue("user_hide_email", $this->_userHideEmail);
		}
	}

	private function _setCustomUserFieldsData() {
		global $locale, $settings;

		$profile_method = $this->_method;

		if ($this->registration) {
			$where = "WHERE field_registration='1'";
		} else {
			$where = "";
		}

		$result = dbquery(
			"SELECT * FROM ".DB_USER_FIELDS." tuf
			INNER JOIN ".DB_USER_FIELD_CATS." tufc ON tuf.field_cat = tufc.field_cat_id
			".$where."
			ORDER BY field_cat_order, field_order"
		);

		if (dbrows($result)) {
			while($data = dbarray($result)) {
				$db_fields = ""; $db_values = "";
				if ($data['field_required'] == 1) {
					$this->_fieldsRequired[$data['field_name']] = true;
				}

				if ($data['field_log'] == 1) {
                    $this->_userLogFields[] = $data['field_name'];
                }

				if (file_exists(LOCALE.LOCALESET."user_fields/".$data['field_name'].".php")) {
					include LOCALE.LOCALESET."user_fields/".$data['field_name'].".php";
				}
				if (file_exists(INCLUDES."user_fields/".$data['field_name']."_include.php")) {
					include INCLUDES."user_fields/".$data['field_name']."_include.php";
				}
				$this->_dbFields .= $db_fields;
				$this->_dbValues .= $db_values;
			}
		}
	}

	private function _setDBValue($field, $value) {
		if ($this->_method == "validate_insert") {
			$this->_dbFields .= ($this->_dbFields != "" ? ", " : "").$field;
			$this->_dbValues .= ($this->_dbValues != "" ? ", " : "")."'".$value."'";
		} else {
			if (in_array($field, $this->_userLogFields)) {
                $this->_userLogData[$field] = $value;
            }
			$this->_dbValues .= ($this->_dbValues != "" ? ", " : "").$field."='".$value."'";
		}
	}

	private function _setError($field, $message, $empty = false) {
		if (!$empty || (isset($this->_fieldsRequired[$field]) && $this->_fieldsRequired[$field] == true)) {
			$this->_noErrors = false;
			$this->_errorMessages[$field] = $message;
		}
	}

	private function _isNotRequired($field) {
		if (isset($this->_fieldsRequired[$field])) {
			return false;
		} else {
			return true;
		}
	}

	// Get Password Input - if empty return false
	private function _getPasswordInput($field) {
		return isset($_POST[$field]) && $_POST[$field] != "" ? $_POST[$field] : false;
	}

	private function _setEmailVerification() {
		global $settings, $locale;

		require_once INCLUDES."sendmail_include.php";

		$userCode = hash_hmac("sha1", PasswordAuth::getNewPassword(), $this->_userEmail);
		$activationUrl = $settings['siteurl']."register.php?email=".$this->_userEmail."&code=".$userCode;

		$message = str_replace("USER_NAME", $this->_userName, $locale['u152']);
		$message = str_replace("USER_PASSWORD", $this->_newUserPassword, $message);
		$message = str_replace("ACTIVATION_LINK", $activationUrl, $message);

		if (sendemail($this->_userName,$this->_userEmail,$settings['siteusername'], $settings['siteemail'], $locale['u151'], $message)) {
			$userInfo = serialize(array(
				"user_name" => $this->_userName,
				"user_password" => $this->_newUserPasswordHash,
				"user_salt" => $this->_newUserPasswordSalt,
				"user_algo" => $this->_newUserPasswordAlgo,
				"user_email" => $this->_userEmail,
				"user_field_fields" => $this->_dbFields,
				"user_field_inputs" => $this->_dbValues
			));
			$userInfo = addslash($userInfo);

			$result = dbquery(
				"INSERT INTO ".DB_NEW_USERS." (
					user_code, user_name, user_email, user_datestamp, user_info
				) VALUES(
					'".$userCode."', '".$this->_userName."', '".$this->_userEmail."', '".time()."', '".$userInfo."'
				)"
			);
			$this->_completeMessage = $locale['u150'];
		} else {
			$this->_setError("email_activation", $locale['u153']."<br />".$locale['u154']);
		}
	}

	private function _setUserDataInput() {
		global $locale, $settings, $userdata, $aidlink;

		$result = dbquery("INSERT INTO ".DB_USERS." (".$this->_dbFields.") VALUES(".$this->_dbValues.")");
		if ($this->adminActivation) {
			$this->_completeMessage = $locale['u160']."<br /><br />\n".$locale['u162'];
		} else {
			if (!$this->isAdminPanel) {
				$this->_completeMessage = $locale['u160']."<br /><br />\n".$locale['u161'];
			} else {
				require_once LOCALE.LOCALESET."admin/members_email.php";
				require_once INCLUDES."sendmail_include.php";

				$subject = $locale['email_create_subject'].$settings['sitename'];
				$replace_this = array("[USER_NAME]", "[PASSWORD]");
				$replace_with = array($this->_userName, $this->_newUserPassword);
				$message = str_replace($replace_this, $replace_with, $locale['email_create_message']);
				sendemail($this->_userName, $this->_userEmail, $settings['siteusername'], $settings['siteemail'], $subject, $message);

				$this->_completeMessage = $locale['u172']."<br /><br />\n<a href='members.php".$aidlink."'>".$locale['u173']."</a>";
				$this->_completeMessage .= "<br /><br /><a href='members.php".$aidlink."&amp;step=add'>".$locale['u174']."</a>";
			}
		}
	}

	private function _setUserDataUpdate() {
		global $locale;

		$this->_saveUserLog();

		$result = dbquery("UPDATE ".DB_USERS." SET ".$this->_dbValues." WHERE user_id='".$this->userData['user_id']."'");
		$this->_completeMessage = $locale['u163'];
	}

	private function _saveUserLog() {
		$i = 0; $sql = "";

		foreach ($this->_userLogData AS $field => $value) {
			if ($this -> userData[$field] != $value) {
				if ($i == 0) {
					$sql = "INSERT INTO ".DB_USER_LOG." (userlog_user_id, userlog_field, userlog_value_new, userlog_value_old, userlog_timestamp) VALUES ";
				}
				$sql .= ($i > 0 ? ", " : "")."('".$this->userData[$field]."', '".$field."', '".$value."', '".$this->userData[$field]."', '".time()."')";
				$i++;
			}
		}

        	if ($sql != "") { $result = dbquery($sql); }
	}
}
?>