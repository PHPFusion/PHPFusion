<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: PasswordAuth.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
/**
 * Class PasswordAuth
 *
 * @package PHPFusion
 */
class PasswordAuth {

    public $currentAlgo = "";

    public $currentSalt = "";

    public $currentPasswordHash = "";

    public $inputPassword = "";

    public $inputNewPassword = "";

    public $inputNewPassword2 = "";

    public $currentPassCheckLength = 8;

    public $currentPassCheckCase = FALSE;

    public $currentPassCheckNum = FALSE;

    public $currentPassCheckSpecialchar = FALSE;

    private $_newAlgo;

    private $_newSalt;

    private $_newPasswordHash;

    private $error = '';

    /**
     * PasswordAuth constructor.
     *
     * @param string $passwordAlgorithm
     */
    public function __construct($passwordAlgorithm = 'sha256') {
        $this->_newAlgo = $passwordAlgorithm;
    }

    /**
     * Checks if new password is valid
     *
     * @param false $createNewHash
     *
     * @return bool
     */
    public function isValidCurrentPassword($createNewHash = FALSE): bool {
        $inputPasswordHash = $this->_hashPassword($this->inputPassword, $this->currentAlgo, $this->currentSalt);
        if ($inputPasswordHash == $this->currentPasswordHash) {
            if ($createNewHash === TRUE) {
                $this->_setNewHash($this->inputPassword);
            }

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Strengh settings check
     *
     * @param $value
     *
     * @return bool
     */
    public function checkInputPassword($value) {
        if ($value) {
            //$currentPassCheckLength
            $regex = self::_passwordStrengthOpts($this->currentPassCheckLength, FALSE, FALSE, FALSE);
            if (!preg_match('/'.$regex.'/', $value)) {
                $this->error = 'Password should be at least 8 characters long';
                return FALSE;
            }

            if ($this->currentPassCheckNum) {
                // Check contains number
                $regex = self::_passwordStrengthOpts($this->currentPassCheckLength, $this->currentPassCheckNum, FALSE, FALSE);
                if (!preg_match('/'.$regex.'/', $value)) {
                    $this->error = 'Password should contain at least 1 number character';
                    return FALSE;
                }
            }

            if ($this->currentPassCheckCase) {
                // Check contains at least 1 upper and 1 lowercase
                $regex = self::_passwordStrengthOpts($this->currentPassCheckLength, $this->currentPassCheckNum, $this->currentPassCheckCase, FALSE);
                if (!preg_match('/'.$regex.'/', $value)) {
                    $this->error = 'Password should contain at least 1 uppercase and 1 lowercase character';
                    return FALSE;
                }
            }

            if ($this->currentPassCheckSpecialchar) {
                // Must contain at least 1 special char
                $regex = self::_passwordStrengthOpts($this->currentPassCheckLength, $this->currentPassCheckNum, $this->currentPassCheckCase, $this->currentPassCheckSpecialchar);

                if (!preg_match('/'.$regex.'/', $value)) {
                    $this->error = 'Password should contain at least 1 special character';
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * @param int   $minimum_length
     * @param bool  $number
     * @param false $camelcase
     * @param false $special_char
     *
     * @return string
     */
    public static function _passwordStrengthOpts($minimum_length = 8, $number = TRUE, $camelcase = FALSE, $special_char = FALSE) {
        $pass_regex = '(?=.*?[A-Za-z])';
        $pass_content = 'A-Za-z\w\W';
        if ($camelcase) {
            $pass_regex = '(?=.*?[a-z])(?=.*[A-Z])';
        }

        if ($number) {
            $pass_regex .= '(?=.*?[0-9])';
            $pass_content .= '\d';
        }

        if ($special_char) {
            $pass_regex .= '(?=.*?[@!#$%&\[\]()=\-_\\\\\/?+*.,;^*])';
        }

        return '^'.$pass_regex.'.{'.$minimum_length.',64}$';
    }

    /**
     * Encrypts the password with given algorithm and salt
     *
     * @param $password
     * @param $algorithm
     * @param $salt
     *
     * @return string
     */
    private function _hashPassword($password, $algorithm, $salt): string {
        if ($algorithm != "md5") {
            return hash_hmac($algorithm, $password, $salt);
        } else {
            return md5(md5($password));
        }
    }

    /**
     *Generate new password hash and password salt
     *
     * @param $password
     */
    protected function _setNewHash($password) {
        $this->_newSalt = PasswordAuth::getNewRandomSalt();
        $this->_newPasswordHash = $this->_hashPassword($password, $this->_newAlgo, $this->_newSalt);
    }

    /**
     * Generate a random password salt
     *
     * @param int $length
     *
     * @return string
     */
    public static function getNewRandomSalt($length = 12): string {
        return sha1(PasswordAuth::getNewPassword($length));
    }

    /**
     * Generates a random password with given length
     *
     * @param int $length
     *
     * @return string
     */
    public static function getNewPassword($length = 12): string {
        $chars = ["abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789", "@!#$%&/()=-_?+*.,:;"];
        $count = [(strlen($chars[0]) - 1), (strlen($chars[1]) - 1), (strlen($chars[2]) - 1)];
        if ($length > 64) {
            $length = 64;
        }
        $pass = "";
        for ($i = 0; $i <= $length; $i++) {
            $type = mt_rand(0, 2);
            $pass .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
        }

        return $pass;
    }


    public function getError() {
        return $this->error;
    }

    /**
     * Get hash, salt, and algo
     *
     * @param $user_password
     *
     * @return array
     */
    public function setNewPassword($user_password): array {
        $salt = self::getNewRandomSalt();

        return [
            "salt" => $salt,
            "algo" => $this->_newAlgo,
            "hash" => $this->_hashPassword($user_password, $this->_newAlgo, $salt)
        ];
    }

    /**
     * Checks whether new input password is valid
     *
     * @return int
     */
    public function isValidNewPassword(): int {
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

    /**
     * Checks if new password input is valid
     *
     * @return bool
     */
    private function _isValidPasswordInput(): bool {
        if (preg_match("/^[0-9A-Z@<>\[\]!#$%&\/\(\)=\-_?+\*\.,:~;\{\}]{8,64}$/i", $this->inputNewPassword)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Get new password algorithem
     *
     * @return string
     */
    public function getNewAlgo(): string {
        return $this->_newAlgo;
    }

    /**
     * Get new password salt
     *
     * @return mixed
     */
    public function getNewSalt() {
        return $this->_newSalt;
    }

    /**
     * Get new password hash
     *
     * @return mixed
     */
    public function getNewHash() {
        return $this->_newPasswordHash;
    }
}
