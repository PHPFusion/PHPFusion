<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: LostPassword.class.php
| Author: gh0st2k
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

class LostPassword extends PasswordAuth {
	private	$_html					= "";
	private $_error					= "";
	private $_userName				= "";
	private $_userEMail				= "";
	private $_newPassword			= "";

	public function sendPasswordRequest ($email) {
		global $locale, $settings;
		if ($this->_isValidEMailAddress($email)) {
			$data = dbarray(dbquery("SELECT user_name, user_password FROM ".DB_USERS." WHERE user_email='".$this->_userEMail."'"));
			$this->_userName = $data['user_name'];
			$link = $settings['siteurl']."lostpassword.php?user_email=".$this->_userEMail."&account=".$data['user_password'];
			$mailBody = str_replace("[NEW_PASS_LINK]", $link, $locale['410']);
			$mailBody = str_replace("[USER_NAME]", $data['user_name'], $mailBody);
			sendemail($data['user_name'], $this->_userEMail, $settings['siteusername'], $settings['siteemail'], $locale['409'].$settings['sitename'], $mailBody);
			$this->_html .= "<div style='text-align:center'><br />\n".$locale['401']."<br /><br />\n<a href='index.php'>".$locale['403']."</a><br /><br />\n</div>\n";
			return true;
		}
	}

	public function checkPasswordRequest ($email, $account) {
		if (!$this->_isValidEMailAddress($email)) { return false; }
		if ((preg_match("/^[0-9a-z]{32}$/", $account) && dbcount("(user_id)", DB_USERS, "user_email='".$email."' AND user_algo='md5'")) ||
			 preg_match("/^[0-9a-z]{64}$/", $account)) {
			$result = dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_email='".$email."' AND user_password='".$account."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				$this->_userName = $data['user_name'];
				$this->_newPassword = $this->getNewPassword();
				$this->_setNewHash($this->_newPassword);
				$this->_sendNewPassword();
				return true;
			} else {
				$this->_error = 3;
				return false;
			}
		} else {
			$this->_error = 3;
			return false;
		}
	}

	public function displayErrors() {
		global $locale;
		if ($this->_error != "") {
			switch ($this->_error) {
				case 1: $message = $locale['404']; break;
				case 2: $message = $locale['405']; break;
				case 3: $message = $locale['412']; break;
			}
			$this->_html .= "<div style='text-align:center'><br />".$message."<br /><br />\n<a href='".BASEDIR."lostpassword.php'>".$locale['406']."</a> -  <a href='".BASEDIR."index.php'>".$locale['403']."</a></div>\n";
			return true;
		} else {
			return false;
		}
	}

	public function renderInputForm () {
		global $locale;
		$this->_html .= "<div style='text-align:center'>\n<form name='passwordform' method='post' action='".FUSION_SELF."'>\n";
		$this->_html .= $locale['407']."<br /><br />\n";
		$this->_html .= "<input type='text' name='email' class='textbox' maxlength='100' style='width:200px;' /><br /><br />\n";
		$this->_html .= "<input type='submit' name='send_password' value='".$locale['408']."' class='button' />\n";
		$this->_html .= "</form>\n</div>\n";
		return true;
	}

	public function displayOutput () {
		$this->displayErrors();
		echo $this->_html;
	}

	private function _isValidEMailAddress ($email) {
		$email = stripinput(trim(preg_replace("/ +/i", "", $email)));
		if (preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
			$check = dbcount("(user_id)", DB_USERS, "user_email='".$email."'");
			if ($check > 0) {
				$this->_userEMail = $email;
				return true;
			} else {
				// e-mail adress is not found
				$this->_error = 1;
				return false;
			}
		} else {
			// no valid e-mail adress
			$this->_error = 2;
			return false;
		}
	}

	private function _sendNewPassword () {
		global $settings, $locale;
		$mailbody = str_replace("[NEW_PASS]", $this->_newPassword, $locale['411']);
		$mailbody = str_replace("[USER_NAME]", $this->_userName, $mailbody);
		sendemail($this->_userName, $this->_userEMail, $settings['siteusername'], $settings['siteemail'], $locale['409'].$settings['sitename'], $mailbody);
		$result = dbquery("UPDATE ".DB_USERS." SET user_algo='".$settings['password_algorithm']."', user_password='".$this->getNewHash()."', user_salt='".$this->getNewSalt()."' WHERE user_email='".$this->_userEMail."'");
		$this->_html .= "<div style='text-align:center'><br />\n".$locale['402']."<br /><br />\n<a href='index.php'>".$locale['403']."</a><br /><br />\n</div>\n";
	}
}
?>