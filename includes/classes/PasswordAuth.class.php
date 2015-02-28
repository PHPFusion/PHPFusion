<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PasswordAuth.class.php
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

class PasswordAuth {

	public $currentAlgo 					= "";
	public $currentSalt 					= "";
	public $currentPasswordHash 			= "";
	public $inputPassword 					= "";

	public $inputNewPassword				= "";
	public $inputNewPassword2				= "";

	private $_newAlgo;
	private $_newSalt;
	private $_newPasswordHash;

	// Checks if Current Password is valid
	public function isValidCurrentPassword($createNewHash = false) {
		$inputPasswordHash = $this->_hashPassword($this->inputPassword, $this->currentAlgo, $this->currentSalt);

		if ($inputPasswordHash == $this->currentPasswordHash) {
			if ($createNewHash == true) { $this->_setNewHash($this->inputPassword); }
			return true;
		} else {
			return false;
		}
	}

	// Checks if new password is valid
	public function isValidNewPassword() {
		if ($this->inputNewPassword != $this->inputPassword) {
			if ($this->inputNewPassword == $this->inputNewPassword2) {
				if ($this->_isValidPasswordInput()) {
					$this->_setNewHash($this->inputNewPassword);
					return 0;
				} else {
					// New password contains invalid chars
					return 3;
				}
			} else {
				// The two new passwords are not identical
				return 2;
			}
		} else {
			// New password can not be equal you current password
			return 1;
		}
	}

	// Get new password algorithem
	public function getNewAlgo() {
		return $this->_newAlgo;
	}

	// Get new password salt
	public function getNewSalt() {
		return $this->_newSalt;
	}

	// Get new password hash
	public function getNewHash() {
		return $this->_newPasswordHash;
	}

	// Generate new password hash and password salt
	protected function _setNewHash($password) {
		global $settings;

		$this->_newAlgo 			= $settings['password_algorithm'];
		$this->_newSalt 			= PasswordAuth::getNewRandomSalt();
		$this->_newPasswordHash		= $this->_hashPassword($password, $this->_newAlgo, $this->_newSalt);
	}

	// Checks if new password input is valid
	private function _isValidPasswordInput() {
		if (preg_match("/^[0-9A-Z@!#$%&\/\(\)=\-_?+\*\.,:;]{8,64}$/i", $this->inputNewPassword)) {
			return true;
		} else {
			return false;
		}
	}

	// Encrypts the password with given algorithm and salt
	private function _hashPassword($password, $algorithm, $salt) {
		if ($algorithm != "md5") {
			return hash_hmac($algorithm, $password, $salt);
		} else {
			return md5(md5($password));
		}
	}

	// Generates a random password with given length
	public static function getNewPassword($length = 12) {
		$chars = array("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789", "@!#$%&/()=-_?+*.,:;");
		$count = array((strlen($chars[0]) - 1), (strlen($chars[1]) - 1), (strlen($chars[2]) - 1));

		if ($length > 64) { $length = 64; }

		$pass = "";
		for ($i = 0; $i <= $length; $i++) {
			$type = mt_rand(0, 2);
			$pass .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
		}

		return $pass;
	}

	// Generate a random password salt
	public static function getNewRandomSalt($length = 12) {
		return sha1(PasswordAuth::getNewPassword($length));
	}
}
?>