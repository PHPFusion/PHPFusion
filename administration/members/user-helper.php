<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user-helper.php
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
namespace PHPFusion\Administration\Members;

use Defender;
use PHPFusion\PasswordAuth;

/**
 * A helper class
 * Class User_Helper
 *
 * @package PHPFusion\Administration\Members
 */
class User_Helper {

    private $class = NULL;
    private $locale = [];
    private $user_data = [];

    public function __construct(UserForms $obj) {
        $this->class = $obj;

    }

    public function checkUserName() {
        $user_name = sanitizer('user_name', '', 'user_name');
        if ($user_name && $user_name != $this->class->user_data['user_name']) {
            if (dbcount('(user_id)', DB_USERS, 'user_name=:uname', [':uname' => $user_name]) || dbcount('(user_name)', DB_NEW_USERS, 'user_name=:uname', [':uname' => $user_name])) {
                fusion_stop();
                Defender::setInputError('user_name');
                Defender::setErrorText('user_name', 'The user name is registered to another user.');
            }
        }
        return $user_name;
    }

    public function checkUserPass($user_exist = FALSE) {

        $user_data = [
            'user_password' => '',
            'user_algo'     => '',
            'user_salt'     => '',
        ];

        if ($user_exist === FALSE) {
            $this->class->user_data['user_password'] = '';
            $this->class->user_data['user_algo'] = '';
            $this->class->user_data['user_salt'] = '';
        }

        $password = sanitizer('user_password', '', 'user_password');

        if ($password) {

            $passAuth = new PasswordAuth();
            $passAuth->inputPassword = $this->class->user_data['user_password'];
            $passAuth->inputNewPassword = $password;
            $passAuth->inputNewPassword2 = $password;
            $passAuth->currentPasswordHash = $this->class->user_data['user_password'];
            $passAuth->currentAlgo = $this->class->user_data['user_algo'];
            $passAuth->currentSalt = $this->class->user_data['user_salt'];
            $_isValidNewPassword = $passAuth->isValidNewPassword();
            if ($_isValidNewPassword == 0) {
                $user_data['user_password'] = $passAuth->getNewHash();
                $user_data['user_algo'] = $passAuth->getNewAlgo();
                $user_data['user_salt'] = $passAuth->getNewSalt();
            }
        }
        return (array)$user_data;
    }

    public function checkUserEmail() {
        $email = sanitizer('user_email', '', 'user_email');
        if ($email && $email != $this->class->user_data['user_email']) {
            if (dbcount('(user_id)', DB_USERS, 'user_email=:email', [':email' => $email]) || dbcount('(user_name)', DB_NEW_USERS, 'user_email=:email', [':email' => $email])) {
                fusion_stop();
                Defender::setInputError('user_email');
                Defender::setErrorText('user_email', 'The email address is registered to another user.');
            }
        }
        return $email;
    }

    public function checkUserAvatar() {
        if (!empty($_FILES['user_avatar']['tmp_name'])) {
            $avatar_upload = form_sanitizer($_FILES['user_avatar'], '', 'user_avatar');
            if (isset($avatar_upload['error']) && !$avatar_upload['error'] && !empty($avatar_upload['image_name'])) {
                return $avatar_upload['image_name'];
            }
        }
    }

    public function getUserLevelOptions() {
        $user_level_opts = [];
        $user_groups = fusion_get_groups();
        foreach ($user_groups as $group_level => $group_name) {
            if ($group_level < 0) {
                $user_level_opts[$group_level] = $group_name;
            }
        }
        return $user_level_opts;
    }

    public function sendNewAccountEmail() {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        $user_email = $this->class->user_data['user_email'];
        $user_name = $this->class->user_data['user_name'];
        $user_password = post('user_password');
        $user_firstname = $this->class->user_data['user_firstname'];
        $user_lastname = $this->class->user_data['user_lastname'];
        $user_language = $this->class->user_data['user_language'];
        $passAuth = new PasswordAuth();
        $userCode = hash_hmac("sha1", $passAuth->getNewPassword(), $user_email);
        $user_status = fusion_get_settings('admin_activation') ? $this->class::VERIFY_USER_REVIEW : $this->class::VERIFY_USER_EMAIL;

        $activationUrl = $settings['siteurl']."register.php?email=".$user_email."&code=".$userCode;

        $message = str_replace("USER_NAME", $user_name, $locale['u152']);
        $message = str_replace("SITENAME", $settings['sitename'], $message);
        $message = str_replace("SITEUSERNAME", $settings['siteusername'], $message);
        $message = str_replace("USER_PASSWORD", $user_password, $message);
        $message = str_replace("ACTIVATION_LINK", $activationUrl, $message);
        $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['u151']);

        if (!sendemail($user_name, $user_email, $settings['siteusername'], $settings['siteemail'], $subject, $message)) {
            $message = strtr($locale['u154'], [
                '[LINK]'  => "<a href='".BASEDIR."contact.php'><strong>",
                '[/LINK]' => "</strong></a>"
            ]);
            add_notice('warning', $locale['u153']."<br />".$message, 'all');
        } else {
            add_notice('success', 'A verification email has been sent to '.$user_name.' The account will only be active after email verification.');
        }
        if (fusion_safe()) {
            $userInfo = base64_encode(serialize($this->class->user_data));
            dbquery("INSERT INTO ".DB_NEW_USERS." (user_code, user_name, user_firstname, user_lastname, user_email, user_datestamp, user_language, user_status, user_info) VALUES
            ('".$userCode."', '".$user_name."', '".$user_firstname."', '".$user_lastname."', '".$user_email."', '".TIME."', '".$user_language."', '".$user_status."', '".$userInfo."')");
        }
    }

    public function sendNewPasswordEmail() {
        $locale = fusion_get_locale();
        include INCLUDES."sendmail_include.php";
        add_notice("success", str_replace("USER_NAME", $this->class->user_data['user_name'], $locale['global_458']));
        $settings = fusion_get_settings();
        $password = sanitizer('user_password', '', 'user_password');
        $input = [
            "mailname" => $this->class->user_data['user_name'],
            "email"    => $this->class->user_data['user_email'],
            "subject"  => str_replace("[SITENAME]", $settings['sitename'], $locale['global_456']),
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
                    $this->class->user_data['user_name'],
                    $password,
                ],
                $locale['global_457']
            )
        ];
        if (!sendemail($input['mailname'], $input['email'], $settings['siteusername'], $settings['siteemail'], $input['subject'],
            $input['message'])) {
            add_notice('warning', str_replace("USER_NAME", $this->user_data['user_name'], $this->locale['global_459']));
        }
    }

}

require_once INCLUDES."sendmail_include.php";
