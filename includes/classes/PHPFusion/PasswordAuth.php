<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: PasswordAuth.php
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
    private $newAlgo;
    private $newSalt;
    private $newPasswordHash;
    private $error = '';

    /**
     * PasswordAuth constructor.
     *
     * @param string $passwordAlgorithm
     */
    public function __construct($passwordAlgorithm = 'sha256') {
        $this->newAlgo = $passwordAlgorithm;
    }

    /**
     * Checks if new password is valid
     *
     * @param bool $createNewHash
     *
     * @return bool
     */
    public function isValidCurrentPassword($createNewHash = FALSE) {
        $inputPasswordHash = $this->hashPassword($this->inputPassword, $this->currentAlgo, $this->currentSalt);
        if ($inputPasswordHash == $this->currentPasswordHash) {
            if ($createNewHash === TRUE) {
                $this->setNewHash($this->inputPassword);
            }

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Strengh settings check
     *
     * @param string $value
     *
     * @return bool
     */
    public function checkInputPassword($value) {
        $locale = fusion_get_locale();

        if ($value) {
            //$currentPassCheckLength
            $regex = self::passwordStrengthOpts($this->currentPassCheckLength, FALSE);
            if (!preg_match('/'.$regex.'/', $value)) {
                $this->error = $locale['u303'];
                return FALSE;
            }

            if ($this->currentPassCheckNum) {
                // Check contains number
                $regex = self::passwordStrengthOpts($this->currentPassCheckLength, $this->currentPassCheckNum);
                if (!preg_match('/'.$regex.'/', $value)) {
                    $this->error = $locale['u302'];
                    return FALSE;
                }
            }

            if ($this->currentPassCheckCase) {
                // Check contains at least 1 upper and 1 lowercase
                $regex = self::passwordStrengthOpts($this->currentPassCheckLength, $this->currentPassCheckNum, $this->currentPassCheckCase);
                if (!preg_match('/'.$regex.'/', $value)) {
                    $this->error = $locale['u301'];
                    return FALSE;
                }
            }

            if ($this->currentPassCheckSpecialchar) {
                // Must contain at least 1 special char
                $regex = self::passwordStrengthOpts($this->currentPassCheckLength, $this->currentPassCheckNum, $this->currentPassCheckCase, $this->currentPassCheckSpecialchar);

                if (!preg_match('/'.$regex.'/', $value)) {
                    $this->error = $locale['u300'];
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
    public static function passwordStrengthOpts($minimum_length = 8, $number = TRUE, $camelcase = FALSE, $special_char = FALSE) {
        $pass_regex = '(?=.*?[A-Za-z])';
        if ($camelcase) {
            $pass_regex = '(?=.*?[a-z])(?=.*[A-Z])';
        }

        if ($number) {
            $pass_regex .= '(?=.*?[0-9])';
        }

        if ($special_char) {
            $pass_regex .= '(?=.*?[@!#$%&\[\]()=\-_\\\\\/?+*.,:;^*])';
        }

        return '^'.$pass_regex.'.{'.$minimum_length.',64}$';
    }

    /**
     * Encrypts the password with given algorithm and salt
     *
     * @param string $password
     * @param string $algorithm
     * @param string $salt
     *
     * @return string
     */
    private function hashPassword($password, $algorithm, $salt) {
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
    protected function setNewHash($password) {
        $this->newSalt = self::getNewRandomSalt();
        $this->newPasswordHash = $this->hashPassword($password, $this->newAlgo, $this->newSalt);
    }

    /**
     * Generate a random password salt
     *
     * @param int $length
     *
     * @return string
     */
    public static function getNewRandomSalt($length = 12) {
        return sha1(self::getNewPassword($length));
    }

    /**
     * Generates a random password with given length
     *
     * @param int $length
     *
     * @return string
     */
    public static function getNewPassword($length = 12) {
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
    public function setNewPassword($user_password) {
        $salt = self::getNewRandomSalt();

        return [
            "salt" => $salt,
            "algo" => $this->newAlgo,
            "hash" => $this->hashPassword($user_password, $this->newAlgo, $salt)
        ];
    }

    /**
     * Checks whether new input password is valid
     *
     * @return int
     */
    public function isValidNewPassword() {
        if ($this->inputNewPassword != $this->inputPassword) {
            if ($this->inputNewPassword == $this->inputNewPassword2) {
                if ($this->isValidPasswordInput()) {
                    $this->setNewHash($this->inputNewPassword);

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
    private function isValidPasswordInput() {
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
    public function getNewAlgo() {
        return $this->newAlgo;
    }

    /**
     * Get new password salt
     *
     * @return mixed
     */
    public function getNewSalt() {
        return $this->newSalt;
    }

    /**
     * Get new password hash
     *
     * @return mixed
     */
    public function getNewHash() {
        return $this->newPasswordHash;
    }
}
