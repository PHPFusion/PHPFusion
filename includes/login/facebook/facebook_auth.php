<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/login/facebook/facebook_auth.php
| Author: Deviance (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../../../maincore.php';
require_once __DIR__.'/facebook.login.inc';
require_once __DIR__.'/facebook.php';

/**
 * Authentication for Facebook with PHP Fusion
 *
 * Class Facebook_Auth
 */
class Facebook_Email_Auth extends \PHPFusion\LoginAuth {
    /**
     * Authentication Method
     * Returns 2 possible scenario
     *
     * 1. Have previously connected - user_fb_connect is not empty
     * Function will authenticate user login.
     *
     * 2. Have not connected before - user_fb_connect is empty
     * Function will check for matching email.
     * If found more than one account - a selector form will be displayed to select the correct account to be connected with
     * If found just one - then update user account and authenticate user login.
     * If not found - then a registration form will be displayed. Admin settings for activation will be used.
     *
     * In both 1 and 2 outcome, all actions will be skipped by $_REQUEST['skip_auth'] === true
     *
     */
    public static function fb_authenticate() {
        $login = new \PHPFusion\LoginAuth();
        $fbSettings = $login->load_driver_settings('facebook');
        $locale = fusion_get_locale("", [INCLUDES.'/login/facebook/locale/'.LANGUAGE.'.php']);
        $settings = fusion_get_settings();
        // extends user column with the following field structure
        // get the facebook driver settings and then read which column is the email
        $response = 'error';
        $data = [];
        // we will need to support multiple emails to support LDAP logins.
        if (isset($_REQUEST['id']) && isset($_REQUEST['email'])) {
            // must verify whether the modules has been installed if not cannot use this API
            $facebook_id = stripinput($_REQUEST['id']);
            $facebook_email = stripinput($_REQUEST['email']);
            $facebook_user_field = "user_facebook";

            $data['facebook_data'] = [
                $facebook_user_field => $facebook_email,
                'user_facebook_uid'  => $facebook_id,
                'user_firstname'     => stripinput($_REQUEST['first_name']),
                'user_lastname'      => stripinput($_REQUEST['last_name']),
                'user_timezone'      => stripinput($_REQUEST['timezone'])
            ];

            if (iMEMBER) {
                $user_data = fusion_get_userdata();
                /**
                 * Security Notes:
                 * On public access, you cannot connect to Facebook if someone else is using that account.
                 * On member access, you cannot connect to Facebook if someone else has that email address in his user_email, or someone else has already use that email address
                 * in his facebook email.
                 */
                // make sure he has not any facebook pairings earlier
                if (empty($user_data[$facebook_user_field]) || stristr($user_data[$facebook_user_field], "unverified:")) {

                    if (!dbcount("(user_id)", DB_USERS, "user_facebook=:fem", [":fem" => $facebook_email]) &&
                        !dbcount("(user_id)", DB_USERS, "user_email=:fem AND user_id !=:uid", [":fem"=>$facebook_email, ":uid"=>$user_data['user_id']])) {

                        if ($settings['email_verification']) {
                            // add facebook email to the field with 'unverified:' tag
                            dbquery("UPDATE ".DB_USERS." SET $facebook_user_field=:fem WHERE user_id=:uid", [
                                ":fem" => "unverified:".$facebook_email,
                                ":uid" => $user_data['user_id']
                            ]);
                            // Not functional
                            // This one requires a database to mark as unverified.
                            include(INCLUDES.'sendmail_include.php');
                            $code = json_encode(['email_address' => $facebook_email, 'user_id' => $user_data['user_id'], 'datestamp' => TIME]);
                            $code = \Defender::encrypt_string($code, SECRET_KEY_SALT);
                            $link = BASEDIR.'login.php?sc=facebook&amp;code='.$code; // new API command.
                            $link = urlencode($link);
                            $subject = strtr($locale['uf_fb_connect_500'], ['{SITE_NAME}' => $settings['sitename']]);
                            $message = strtr($locale['uf_fb_connect_501'], [
                                '{USER_NAME}'  => $user_data['user_name'],
                                '{SITE_NAME}'  => $settings['sitename'],
                                '{ADMIN_NAME}' => $settings['siteusername'],
                                '{LINK}'       => "<a href='$link'>$link</a>",
                            ]);
                            sendemail($user_data['user_name'], $facebook_email, $settings['siteusername'], $subject, $message, 'html');

                            $response = "Pairing email verification required";
                            addNotice('success', "<strong>".$locale['uf_fb_connect_502']."</strong> ".$locale['uf_fb_connect_506'], 'all');

                        } else {

                            dbquery("UPDATE ".DB_USERS." SET $facebook_user_field=:fem WHERE user_id=:uid", [
                                ":fem" => $facebook_email,
                                ":uid" => $user_data['user_id']
                            ]);

                            $response = "You have successfully connected your Facebook Account.";
                            addNotice("success", $locale['uf_fb_connect_505'], "all");
                        }
                    } else {
                        $response = $locale['uf_fb_connect_503'];
                        addNotice("danger", $locale['uf_fb_connect_503'], "all");
                    }
                }

            } else {

                if (dbcount("(user_id)", DB_USERS, "$facebook_user_field=:fem AND user_status=0", [':fem' => $facebook_email])) {
                    // if yes, login into his account.
                    $user = dbarray(dbquery("SELECT user_id, user_salt, user_algo, user_level, user_theme FROM ".DB_USERS." WHERE $facebook_user_field=:fem LIMIT 1", [
                        ':fem' => $facebook_email,
                    ]));

                    self::authenticate_user_login($user['user_id']);
                    $response = "Successfully login with Facebook account.";

                } else {

                    // If there are nobody using that email/facebook email
                    if (!dbcount("(user_id)", DB_USERS, "user_facebook=:fem OR user_email=:fem01", [":fem" => $facebook_email, ":fem01"=>$facebook_email])) {
                        // Auto register that account.
                        $user_password = self::get_new_user_password();
                        $user_name = $data['facebook_data']['user_firstname'].$data['facebook_data']['user_lastname'].random_int(1, 99);
                        $user = [
                            'user_name'          => $user_name,
                            'user_hash'          => $user_password['hash'],
                            'user_algo'          => $user_password['algo'],
                            'user_salt'          => $user_password['salt'],
                            'user_email'         => $facebook_email,
                            'user_hide_email'    => 1,
                            'user_status'        => $settings['admin_activation'] ? 2 : 0,
                            'user_joined'        => TIME,
                            'user_ip'            => USER_IP,
                            'user_ip_type'       => USER_IP_TYPE,
                            'user_level'         => USER_LEVEL_MEMBER,
                            'user_theme'         => 'Default',
                            'user_language'      => LANGUAGE,
                            $facebook_user_field => $facebook_email,
                            'user_timezone'      => fusion_get_settings('timeoffset'),
                        ];
                        if ($settings['enable_registration']) {
                            if ($settings['email_verification']) {
                                self::send_email_verification($user);
                                $response = "Pairing email verification required";
                            } else {
                                $user['user_id'] = dbquery_insert(DB_USERS, $user, 'save', ['keep_session' => TRUE]);
                                // Authenticate and login
                                addNotice("success", $locale['uf_fb_connect_505'], 'all');
                                self::authenticate_user_login($user['user_id']);
                                $response = "Registration complete and pairing successful";
                            }
                        } else {
                            $response = "Registration is closed for this site.";
                        }
                    }
                    elseif (dbcount("(user_id)", DB_USERS, "user_email=:fem", [":fem"=>$facebook_email])) {
                        $user_data = dbarray(dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_email=:fem", [":fem"=>$facebook_email]));
                        // send the verification email
                        if ($settings['email_verification']) {
                            // add facebook email to the field with 'unverified:' tag
                            dbquery("UPDATE ".DB_USERS." SET $facebook_user_field=:fem WHERE user_id=:uid", [
                                ":fem" => "unverified:".$facebook_email,
                                ":uid" => $user_data['user_id']
                            ]);
                            // Not functional
                            // This one requires a database to mark as unverified.
                            include(INCLUDES.'sendmail_include.php');
                            $code = json_encode(['email_address' => $facebook_email, 'user_id' => $user_data['user_id'], 'datestamp' => TIME]);
                            $code = \Defender::encrypt_string($code, SECRET_KEY_SALT);
                            $link = BASEDIR.'login.php?sc=facebook&amp;code='.$code; // new API command.
                            $link = urlencode($link);
                            $subject = strtr($locale['uf_fb_connect_500'], ['{SITE_NAME}' => $settings['sitename']]);
                            $message = strtr($locale['uf_fb_connect_501'], [
                                '{USER_NAME}'  => $user_data['user_name'],
                                '{SITE_NAME}'  => $settings['sitename'],
                                '{ADMIN_NAME}' => $settings['siteusername'],
                                '{LINK}'       => "<a href='$link'>$link</a>",
                            ]);
                            sendemail($user_data['user_name'], $facebook_email, $settings['siteusername'], $subject, $message, 'html');
                            $response = "Pairing email verification required";
                            addNotice('success', "<strong>".$locale['uf_fb_connect_502']."</strong> ".$locale['uf_fb_connect_506'], 'all');
                        } else {
                            addNotice('success', $locale['uf_fb_connect_502'], 'all');
                            dbquery("UPDATE ".DB_USERS." SET $facebook_user_field=:fem WHERE user_id=:uid", [
                                ":fem" => $facebook_email,
                                ":uid" => $user_data['user_id']
                            ]);
                            $response = "You have successfully connected your Facebook Account.";
                            addNotice("success", $locale['uf_fb_connect_505'], "all");
                        }
                    } else {
                        $response = "Account existed with this email. Please login to connect to Facebook.";
                        addNotice("danger", $locale['uf_fb_connect_503']." ".$locale['uf_fb_connect_508'], "all");
                    }
                }
            }
        } else {
            $response = "Invalid response or settings";
            addNotice("danger", $locale['uf_fb_connect_507'], "all");
        }

        return json_encode(['response' => $response, 'data' => $data, 'request' => $_REQUEST]);
    }
}

echo Facebook_Email_Auth::fb_authenticate();
