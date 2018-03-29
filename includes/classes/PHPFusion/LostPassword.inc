<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: LostPassword.inc
| Author: PHP-Fusion Development Team
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

class LostPassword extends PasswordAuth {
    private $_html = "";
    private $_error = "";
    private $_userName = "";
    private $_userEMail = "";
    private $_newPassword = "";

    public function sendPasswordRequest($email) {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        if ($this->_isValidEMailAddress($email)) {
            $data = dbarray(dbquery("SELECT user_name, user_password FROM ".DB_USERS." WHERE user_email=:email", [':email' => $this->_userEMail]));
            $this->_userName = $data['user_name'];
            $link = $settings['siteurl']."lostpassword.php?user_email=".$this->_userEMail."&account=".$data['user_password'];
            $mailBody = str_replace("[SITENAME]", $settings['sitename'], $locale['410']);
            $mailBody = str_replace("[NEW_PASS_LINK]", $link, $mailBody);
            $mailBody = str_replace("[USER_NAME]", $data['user_name'], $mailBody);
            $mailBody = str_replace("[SITEUSERNAME]", $settings['siteusername'], $mailBody);
            sendemail($data['user_name'], $this->_userEMail, $settings['siteusername'], $settings['siteemail'], $locale['409'].$settings['sitename'],
                $mailBody);
            $this->_html .= "<div class='text-center'>".$locale['401']."<br /><br />\n<a href='".BASEDIR."index.php'>".$locale['403']."</a></div>\n";

            return TRUE;
        } else {
            return NULL;
        }
    }

    private function _isValidEMailAddress($email) {
        //$email = stripinput(trim(preg_replace("/ +/i", "", $email)));
        //if (preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $check = dbcount("(user_id)", DB_USERS, "user_email=:email", [':email' => $email]);
            if ($check > 0) {
                $this->_userEMail = $email;

                return TRUE;
            } else {
                // e-mail adress is not found
                $this->_error = 1;

                return FALSE;
            }
        } else {
            // no valid e-mail adress
            $this->_error = 2;

            return FALSE;
        }
    }

    public function checkPasswordRequest($email, $account) {
        if (!$this->_isValidEMailAddress($email)) {
            return FALSE;
        }
        if ((preg_match("/^[0-9a-z]{32}$/", $account) && dbcount("(user_id)", DB_USERS,
                    "user_email=:email AND user_algo=:algo", [':email' => $email, ':algo' => 'md5'])) || preg_match("/^[0-9a-z]{64}$/",
                $account)
        ) {
            $result = dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_email=:email AND user_password=:password", [':email' => $email, ':password' => $account]);
            if (dbrows($result)) {
                $data = dbarray($result);
                $this->_userName = $data['user_name'];
                $this->_newPassword = $this->getNewPassword();
                $this->_setNewHash($this->_newPassword);
                $this->_sendNewPassword();

                return TRUE;
            } else {
                $this->_error = 3;

                return FALSE;
            }
        } else {
            $this->_error = 3;

            return FALSE;
        }
    }

    private function _sendNewPassword() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $mailbody = str_replace("[SITENAME]", $settings['sitename'], $locale['411']);
        $mailbody = str_replace("[NEW_PASS]", $this->_newPassword, $mailbody);
        $mailbody = str_replace("[USER_NAME]", $this->_userName, $mailbody);
        $mailbody = str_replace("[SITEUSERNAME]", $settings['siteusername'], $mailbody);
        sendemail($this->_userName, $this->_userEMail, $settings['siteusername'], $settings['siteemail'], $locale['409'].$settings['sitename'],
            $mailbody);
        dbquery("UPDATE ".DB_USERS." SET user_algo='".fusion_get_settings('password_algorithm')."', user_password='".$this->getNewHash()."', user_salt='".$this->getNewSalt()."' WHERE user_email='".$this->_userEMail."'");
        $this->_html .= "<div class='text-center'>".$locale['402']."<br /><br />\n<a href='".BASEDIR."index.php'>".$locale['403']."</a></div>\n";
    }

    public function renderInputForm() {
        $locale = fusion_get_locale();
        $this->_html = openform('passwordform', 'post', FORM_REQUEST);
        $this->_html .= form_text('email', $locale['413'], '', ['max_length' => 100, 'width' => '200px', 'type' => 'email', 'inline' => FALSE, 'ext_tip' => $locale['407']]);
        $this->_html .= form_button('send_password', $locale['408'], $locale['408'], ['class' => 'btn-primary']);
        $this->_html .= closeform();

        return TRUE;
    }

    public function displayOutput() {
        $this->displayErrors();
        echo $this->_html;
    }

    public function displayErrors() {
        $locale = fusion_get_locale();
        $message = '';

        if ($this->_error != "") {
            switch ($this->_error) {
                case 1:
                    $message = $locale['404'];
                    break;
                case 2:
                    $message = $locale['405'];
                    break;
                case 3:
                    $message = $locale['412'];
                    break;
            }
            $this->_html .= "<div class='text-center'>".$message."<br /><br />\n<a href='".BASEDIR."lostpassword.php'>".$locale['406']."</a> - <a href='".BASEDIR."index.php'>".$locale['403']."</a></div>\n";

            return TRUE;
        } else {
            return FALSE;
        }
    }
}
