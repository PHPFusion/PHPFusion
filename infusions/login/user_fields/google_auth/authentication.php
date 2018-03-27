<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/user_fields/google_auth/authentication.php
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
require_once __DIR__.'/../../../../maincore.php';
require_once THEMES.'templates/header.php';

$locale = fusion_get_locale('', LOGIN_LOCALESET.'user_gauth.php');
require_once INFUSIONS.'login/user_fields/user_gauth_include_var.php';

$google = new GoogleAuthenticator();
$secret_key = defined('SECRET_KEY') ? SECRET_KEY : 'secret_key';
$secret_key_salt = defined('SECRET_KEY_SALT') ? SECRET_KEY_SALT : 'secret_salt';
$algo = fusion_get_settings('password_algorithm') ? fusion_get_settings('password_algorithm') : 'sha256';
$user = [];
$secret = '';
$remember = '';

if (isset($_GET['restore_code']) && isset($_GET['uid']) && isnum($_GET['uid'])) {
    $restore_code = stripinput($_GET['restore_code']);
    $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:uid", [':uid' => intval($_GET['uid'])]);
    if (dbrows($result)) {
        $salt = md5(isset($user['user_salt']) ? $user['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
        if ($restore_code == $user['user_id'].hash_hmac($algo, $user['user_id'].$secret.$secret_key, $salt)) {
            // restore the account
            dbquery("UPDATE ".DB_USERS." SET user_status=0 WHERE user_status=5 AND user_id=:uid", [':uid' => intval($_GET['uid'])]);
            addNotice("success", "Your account has been sucessfully restored");
        } else {
            addNotice("danger", "Invalid restore code. We could not restore your account. Please contact the site administrator.");
            redirect(BASEDIR.'index.php');
        }
    } else {
        addNotice("danger", "Sorry, we could not find the user account. Please contact the site administrator.");
        redirect(BASEDIR.'index.php');
    }
}

if (isset($_SESSION['secret_code'])) {
    $secret = stripinput($_SESSION['secret_code']);
    $user_id = intval($_SESSION['uid']);
    $verify_secret = dbcount("(user_id)", DB_USERS, "user_id=:uid AND user_gauth=:secret", [':uid' => $user_id, ':secret' => $secret]);
    if (!empty($verify_secret)) {
        $user = fusion_get_user($user_id);
        if (!empty($user)) {

            if (isset($_POST['authenticate'])) {

                if (!isset($_SESSION['auth_attempt'][USER_IP])) {
                    $_SESSION['auth_attempt'][USER_IP] = 3;
                }

                $gCode = form_sanitizer($_POST['g_code'], '', 'g_code');

                if (\defender::safe()) {
                    $checkResult = $google->verifyCode($secret, $gCode, 2);    // 2 = 2*30sec clock tolerance
                    if ($checkResult) {
                        // Authenticate the User
                        \PHPFusion\Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], $remember, TRUE);
                        \PHPFusion\Authenticate::_setUserTheme($user);
                        unset($_SESSION['uid']);
                        unset($_SESSION['secret_code']);
                        unset($_SESSION['auth_attempt'][USER_IP]);
                        redirect(BASEDIR.'index.php');

                    } else {

                        if (!empty($_SESSION['auth_attempt'][USER_IP])) {
                            $_SESSION['auth_attempt'][USER_IP] = $_SESSION['auth_attempt'][USER_IP] - 1;
                            addNotice('danger', "We could not verify your authentication code. You have ".$_SESSION['auth_attempt'][USER_IP]." attempts left");
                        } else {

                            $key = $user_id.$secret.$secret_key;
                            $hash = hash_hmac($algo, $key, $salt);
                            $restore_hash = $user_id.$hash;
                            $restore_link = fusion_get_settings('siteurl').'/infusions/login/google_auth/authentication.php?uid='.$user_id.'&amp;restore_hash='.$restore_hash;
                            // ban the user
                            addNotice("danger", "Your account has been temporarily suspended. Please contact the administrator at ".fusion_get_settings('site_email'));
                            $subject = "Account Suspended due to Suspicious Account Login";
                            $message = "Dear {USERNAME},\n\n
                            We have recently find that there was multiple attempts to login into your user account at {SITENAME}. And as a security measure, we have temporarily
                            [strong]suspended the account[/strong]. If you feel that there was an error to this, you can restore your account with the link below.\n\n
                            {RESTORE_LINK}\n\n
                            Your regards,\n
                            {SITE_ADMIN}\n
                            Site Administrator,\n
                            {SITENAME}               
                            ";

                            $message = parse_textarea(strtr($message, [
                                '{USERNAME}'     => $user['user_name'],
                                '{SITENAME}'     => fusion_get_settings('sitename'),
                                '{RESTORE_LINK}' => "<a href='$restore_link'>$restore_link</a>",
                                '{SITE_ADMIN}'   => fusion_get_settings('site_admin'),
                            ]));
                            $mail = sendemail($user['user_name'], $user['user_email'], fusion_get_settings('site_admin'), fusion_get_settings('site_email'), $subject, $message);
                            if ($mail) {
                                dbquery("UPDATE ".DB_USERS." SET user_status=5 WHERE user_id=:uid", [':uid' => $user['user_id']]);
                                unset($_SESSION['secret_code']);
                                unset($_SESSION['auth_attempt'][USER_IP]);
                                redirect(BASEDIR.'index.php');
                            }
                        }
                    }
                }
            }

            $tpl = \PHPFusion\Template::getInstance('g_authenticate');
            $path = __DIR__.'/templates/authorize.html';
            $tpl->set_template($path);
            $tpl->set_tag('image_src', 'images/icon.png');
            $tpl->set_tag('input', form_text('g_code', 'Authentication Code', '', [
                'required'    => TRUE,
                'type'        => 'password',
                'error_text'  => 'You need to provide a valid Authentication Code',
                'placeholder' => 'Enter Google Authentication Code'
            ]));
            $tpl->set_tag('button', form_button('authenticate', 'Verify', 'Verify', ['class' => 'btn-block btn-primary btn-bordered']));
            echo openform('gauth_frm', 'post', FUSION_REQUEST);
            echo $tpl->get_output();
            echo closeform();

        } else {
            // invalid user
            redirect(BASEDIR.'index.php');
        }
    } else {
        // deliberately visit this page, so must redirect.
        redirect(BASEDIR.'index.php');
    }
} else {
    redirect(BASEDIR.'index.php');
}

require_once THEMES.'templates/footer.php';
