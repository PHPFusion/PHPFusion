<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: LostPassword.php
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

class LostPassword extends PasswordAuth {
    private $html = "";
    private $error = "";
    private $userName = "";
    private $userEmail = "";
    private $newPassword = "";

    /**
     * @param string $email
     *
     * @return bool|null
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendPasswordRequest($email) {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        if ($this->isValidEMailAddress($email)) {
            if (dbcount("(user_id)", DB_USERS, "user_email=:email", [':email' => $email]) > 0) {
                $this->userEmail = $email;
                $data = dbarray(dbquery("SELECT user_name, user_password FROM ".DB_USERS." WHERE user_email=:email", [':email' => $this->userEmail]));
                $this->userName = $data['user_name'];
                $link = $settings['siteurl']."lostpassword.php?user_email=".$this->userEmail."&account=".$data['user_password'];
                $mailBody = str_replace("[SITENAME]", $settings['sitename'], $locale['410']);
                $mailBody = str_replace("[NEW_PASS_LINK]", $link, $mailBody);
                $mailBody = str_replace("[USER_NAME]", $data['user_name'], $mailBody);
                $mailBody = str_replace("[SITEUSERNAME]", $settings['siteusername'], $mailBody);
                sendemail($data['user_name'], $this->userEmail, $settings['siteusername'], $settings['siteemail'], $locale['409'].$settings['sitename'], $mailBody);
            }

            $this->html .= "<div class='text-center'>".$locale['401']."<br /><br />\n<a href='".BASEDIR."index.php'>".$locale['403']."</a></div>\n";

            return TRUE;
        } else {
            return NULL;
        }
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    private function isValidEMailAddress($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return TRUE;
        } else {
            // no valid e-mail adress
            $this->error = 2;

            return FALSE;
        }
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return bool
     */
    public function checkPasswordRequest($email, $password) {
        if (!$this->isValidEMailAddress($email)) {
            return FALSE;
        }
        if ((preg_match("/^[0-9a-z]{32}$/", $password) && dbcount("(user_id)", DB_USERS, "user_email=:email AND user_algo=:algo", [':email' => $email, ':algo' => 'md5'])) || preg_match("/^[0-9a-z]{64}$/", $password)) {
            $result = dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_email=:email AND user_password=:password", [':email' => $email, ':password' => $password]);
            if (dbrows($result)) {
                $this->userEmail = $email;
                $data = dbarray($result);
                $this->userName = $data['user_name'];
                $this->newPassword = $this->getNewPassword();
                $this->setNewHash($this->newPassword);
                $this->sendNewPassword();

                return TRUE;
            } else {
                $this->error = 3;

                return FALSE;
            }
        } else {
            $this->error = 3;

            return FALSE;
        }
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendNewPassword() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $mailbody = str_replace("[SITENAME]", $settings['sitename'], $locale['411']);
        $mailbody = str_replace("[NEW_PASS]", $this->newPassword, $mailbody);
        $mailbody = str_replace("[USER_NAME]", $this->userName, $mailbody);
        $mailbody = str_replace("[SITEUSERNAME]", $settings['siteusername'], $mailbody);
        sendemail($this->userName, $this->userEmail, $settings['siteusername'], $settings['siteemail'], $locale['409'].$settings['sitename'], $mailbody);
        dbquery("UPDATE ".DB_USERS." SET user_algo='".fusion_get_settings('password_algorithm')."', user_password='".$this->getNewHash()."', user_salt='".$this->getNewSalt()."' WHERE user_email='".$this->userEmail."'");
        $this->html .= "<div class='text-center'>".$locale['402']."<br /><br />\n<a href='".BASEDIR."index.php'>".$locale['403']."</a></div>\n";
    }

    /**
     * @return bool
     */
    public function renderInputForm() {
        $locale = fusion_get_locale();
        $this->html = openform('passwordform', 'post', FORM_REQUEST);
        $this->html .= form_text('email', $locale['413'], '', ['max_length' => 100, 'width' => '200px', 'type' => 'email', 'inline' => FALSE, 'ext_tip' => $locale['407']]);
        $this->html .= form_button('send_password', $locale['408'], $locale['408'], ['class' => 'btn-primary']);
        $this->html .= closeform();

        return TRUE;
    }

    /**
     * Display output
     */
    public function displayOutput() {
        $this->displayErrors();
        echo $this->html;
    }

    /**
     * @return bool
     */
    public function displayErrors() {
        $locale = fusion_get_locale();
        $message = '';

        if ($this->error != "") {
            switch ($this->error) {
                /*case 1:
                    $message = $locale['404'];
                    break;*/
                case 2:
                    $message = $locale['405'];
                    break;
                case 3:
                    $message = $locale['412'];
                    break;
            }
            $this->html .= "<div class='text-center'>".$message."<br /><br />\n<a href='".BASEDIR."lostpassword.php'>".$locale['406']."</a> - <a href='".BASEDIR."index.php'>".$locale['403']."</a></div>\n";

            return TRUE;
        } else {
            return FALSE;
        }
    }
}
