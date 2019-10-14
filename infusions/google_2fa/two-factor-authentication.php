<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/google_auth/authentication.php
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
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';
require_once INFUSIONS.'google_2fa/google_2fa.php';

function google_2fa_authenticate() {

    $locale = fusion_get_locale('', [G2FA_LOCALE]);
    $google = new GoogleAuthenticator();
    $settings = fusion_get_settings();
    $secret_key = defined('SECRET_KEY') ? SECRET_KEY : "secret_key";
    $secret_key_salt = defined('SECRET_KEY_SALT') ? SECRET_KEY_SALT : "secret_salt";
    $algo = $settings['password_algorithm'] ? $settings['password_algorithm'] : "sha256";
    $secret = '';

    $remember = '';

    $google_secret = session_get('google_secret_code');

    if ($google_secret) {

        $google_secret = stripinput($google_secret);

        $user_id = session_get('google_uid');
        $verify_secret = dbcount("(user_id)", DB_USERS, "user_id=:uid AND user_google2fa=:secret", [':uid' => (int) $user_id, ':secret' => $google_secret]);
        if (!empty($verify_secret)) {
            // Log the user out.
            $user = fusion_get_user($user_id);

            if (!empty($user)) {

                if (post('authenticate')) {

                    if (!isset($_SESSION['auth_attempt'][USER_IP])) {
                        $_SESSION['auth_attempt'][USER_IP] = 3;
                    }

                    $gCode = sanitizer('g_code', '', 'g_code');

                    if (fusion_safe()) {

                        $checkResult = $google->verifyCode($google_secret, $gCode, 2);    // 2 = 2*30sec clock tolerance

                        if ($checkResult) {
                            // Authenticate the User
                            \PHPFusion\Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], $remember, TRUE);
                            \PHPFusion\Authenticate::_setUserTheme($user);
                            session_add('google_2fa_auth', $user['user_id'].'-'.$user['user_google2fa'].'-'.TIME);
                            unset($_SESSION['uid']);
                            unset($_SESSION['secret_code']);
                            unset($_SESSION['auth_attempt'][USER_IP]);
                            addNotice('success', 'Two factor verification success.', fusion_get_settings('opening_page'));
                            redirect(BASEDIR.fusion_get_settings('opening_page'));

                        } else {

                            if (!empty($_SESSION['auth_attempt'][USER_IP])) {
                                $_SESSION['auth_attempt'][USER_IP] = $_SESSION['auth_attempt'][USER_IP] - 1;
                                addNotice('danger', str_replace('{D}', $_SESSION['auth_attempt'][USER_IP], $locale['uf_gauth_123']));
                            } else {

                                $key = $user_id.$secret.$secret_key;
                                $hash = hash_hmac($algo, $key, $secret_key_salt);

                                $restore_hash = $user_id.$hash;
                                $restore_link = $settings['siteurl']."/includes/login/google_auth/authentication.php?uid=$user_id&amp;restore_hash=$restore_hash";

                                // ban the user
                                addNotice("danger", str_replace('{SITE_NAME}', $settings['sitename'], $locale['uf_gauth_120']));
                                $subject = $locale['uf_gauth_121'];
                                $message = parse_textarea(strtr($locale['uf_gauth_122'], [
                                    '{USERNAME}'     => $user['user_name'],
                                    '{SITENAME}'     => $settings['sitename'],
                                    '{RESTORE_LINK}' => "<a href='$restore_link'>$restore_link</a>",
                                    '{SITE_ADMIN}'   => $settings['siteusername'],
                                ]));

                                require_once INCLUDES.'sendmail_include.php';
                                $mail = sendemail($user['user_name'], $user['user_email'], $settings['siteusername'], $settings['site_email'], $subject, $message);
                                if ($mail) {

                                    // Whether to block the user?
                                    // dbquery("UPDATE ".DB_USERS." SET user_status=5 WHERE user_id=:uid", [':uid' => $user['user_id']]);

                                    unset($_SESSION['secret_code']);
                                    unset($_SESSION['auth_attempt'][USER_IP]);

                                    Authenticate::logOut();

                                    redirect(BASEDIR.fusion_get_settings('opening_page'));
                                }
                            }

                        }
                    }
                }

                $tpl = \PHPFusion\Template::getInstance('g_authenticate');
                $tpl->set_template(__DIR__.'/templates/login.html');
                $tpl->set_tag('image_src', 'google_2fa.svg');
                $tpl->set_tag('title', $locale['uf_gauth_100']);
                $tpl->set_tag('description', $locale['uf_gauth_101']);
                $tpl->set_tag('detail', $locale['uf_gauth_102']);

                $tpl->set_tag('input', form_text('g_code', $locale['uf_gauth_103'], '', [
                    'inline'=>FALSE,
                    'type'=>'number',
                    'class'=>'form-group-lg',
                    'error_text'  => $locale['uf_gauth_104'],
                    'placeholder' => $locale['uf_gauth_105']
                ]));

                $tpl->set_tag('button', form_button('authenticate', $locale['uf_gauth_106'], $locale['uf_gauth_106'], ['class' => 'btn-block btn-primary btn-md']));
                echo openform('gauth_frm', 'post', FUSION_REQUEST);
                echo $tpl->get_output();
                echo closeform();

            } else {
                // invalid user
                redirect(BASEDIR.fusion_get_settings('opening_page'));
            }
        } else {
            // deliberately visit this page, so must redirect.
            redirect(BASEDIR.fusion_get_settings('opening_page'));
        }
    } else {
        redirect(BASEDIR.fusion_get_settings('opening_page'));
    }
}

google_2fa_authenticate();

require_once THEMES.'templates/footer.php';
