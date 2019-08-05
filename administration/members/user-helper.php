<?php
namespace PHPFusion\Administration\Members;

use PHPFusion\PasswordAuth;

/**
 * A helper class
 * Class User_Helper
 *
 * @package PHPFusion\Administration\Members
 */
class User_Helper {

    private $class = NULL;

    public function __construct(UserForms $obj) {
        $this->class = $obj;
    }

    public function checkUserName() {
        $user_name = sanitizer('user_name', '', 'user_name');
        if ($user_name && $user_name != $this->class->user_data['user_name']) {
            if (dbcount('(user_id)', DB_USERS, 'user_name=:uname', [':uname'=>$user_name]) ||dbcount('(user_name)', DB_NEW_USERS, 'user_name=:uname', [':uname'=>$user_name])) {
                \Defender::stop();
                \Defender::setInputError('user_name');
                \Defender::setErrorText('user_name', 'The user name is registered to another user.');
            }
        }
        return $user_name;
    }

    public function checkUserPass($user_exist = FALSE) {

        if ($user_exist === FALSE) {
            $this->class->user_data['user_password'] = '';
            $this->class->user_data['user_algo'] = '';
            $this->class->user_data['user_salt'] = '';
        }

        $password = sanitizer('user_password', '', 'user_password');
        if ($password) {
            $passAuth = new PasswordAuth();
            $passAuth->inputPassword = '';
            $passAuth->inputNewPassword = $password;
            $passAuth->inputNewPassword2 = $password;
            $passAuth->currentPasswordHash = $this->class->user_data['user_password'];
            $passAuth->currentAlgo = $this->class->user_data['user_algo'];
            $passAuth->currentSalt = $this->class->user_data['user_salt'];
            // Change new password
            switch ($passAuth->isValidNewPassword()) {
                case 0:
                    // New password is valid
                    $user_data['user_password'] = $passAuth->getNewHash();
                    $user_data['user_algo'] = $passAuth->getNewAlgo();
                    $user_data['user_salt'] = $passAuth->getNewSalt();

                    return $user_data;
                    break;
                case 1:
                    // New Password equal old password
                    \Defender::stop();
                    \Defender::setInputError('user_password');
                    \Defender::setErrorText('user_password', $this->locale['u134'].$this->locale['u146'].$this->locale['u133']);
                    break;
                case '2':
                    // The two new passwords are not identical
                    \Defender::stop();
                    \Defender::setInputError('user_password');
                    break;
                case '3':
                    // New password contains invalid chars / symbols
                    \Defender::stop();
                    \Defender::setInputError('user_password');
                    break;
            }
        }
    }

    public function checkUserEmail() {
        $email = sanitizer('user_email', '', 'user_email');
        if ($email && $email != $this->class->user_data['user_email']) {
            if (dbcount('(user_id)', DB_USERS, 'user_email=:email', [':email'=>$email]) || dbcount('(user_name)', DB_NEW_USERS, 'user_email=:email', [':email'=>$email]) ) {
                \Defender::stop();
                \Defender::setInputError('user_email');
                \Defender::setErrorText('user_email', 'The email address is registered to another user.');
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
        $userCode = hash_hmac("sha1", PasswordAuth::getNewPassword(), $user_email);
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
            addNotice('warning', $locale['u153']."<br />".$message, 'all');
        } else {
            addNotice('success', 'A verification email has been sent to '.$user_name.' The account will only be active after email verification.');
        }
        if (\Defender::safe()) {
            $userInfo = base64_encode(serialize($this->class->user_data));
            dbquery("INSERT INTO ".DB_NEW_USERS." (user_code, user_name, user_firstname, user_lastname, user_email, user_datestamp, user_language, user_status, user_info) VALUES 
            ('".$userCode."', '".$user_name."', '".$user_firstname."', '".$user_lastname."', '".$user_email."', '".TIME."', '".$user_language."', '".$user_status."', '".$userInfo."')");
        }
    }
}

require_once INCLUDES."sendmail_include.php";