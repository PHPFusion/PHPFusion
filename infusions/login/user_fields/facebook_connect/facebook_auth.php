<?php
require_once __DIR__.'/../../../../maincore.php';
require_once __DIR__.'/facebook_connect.php';

/**
 * Authentication for Facebook with PHP Fusion
 *
 * Class Facebook_Auth
 */
class Facebook_Auth extends Facebook_Connect {

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
    public static function get_fb_json_authenticate() {
        // extends user column with the following field structure
        $response = 'error';

        if (isset($_REQUEST['id']) && isset($_REQUEST['email'])) {

            $locale = fusion_get_locale("", LOGIN_LOCALESET.'user_fb_connect.php');
            $facebook_id = stripinput($_REQUEST['id']);
            $facebook_email = stripinput($_REQUEST['email']);

            $data['facebook_data'] = array(
                'user_email'        => $facebook_email,
                'user_facebook_uid' => $facebook_id,
                'user_firstname'    => stripinput($_REQUEST['first_name']),
                'user_lastname'     => stripinput($_REQUEST['last_name']),
                'user_gender'       => stripinput($_REQUEST['gender']),
                'user_timezone'     => stripinput($_REQUEST['timezone'])
            );
            $table = fieldgenerator(DB_USERS);
            $is_admin = iMEMBER && !empty($_REQUEST['skip_auth']) ? TRUE : FALSE;

            if (in_array('user_fb_connect', $table)) {

                // Check if use has connect before
                if (dbcount("(user_id)", DB_USERS, "user_fb_connect=:id AND user_status=0", array(
                    ':id' => $facebook_id,
                ))) {
                    // your account has been associated before.
                    $response = 'authenticated';
                    if ($is_admin) {
                        addNotice('success', $locale['uf_fb_connect_504']);
                    } else {
                        $user = dbarray(dbquery("SELECT user_id, user_salt, user_algo, user_level, user_theme FROM ".DB_USERS." WHERE user_facebook_uid=:id LIMIT 1", array(
                            ':id' => $facebook_id,
                        )));
                        \PHPFusion\Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], FALSE, TRUE);
                        \PHPFusion\Authenticate::_setUserTheme($user);
                        unset($_SESSION['facebook_user'][USER_IP]);
                    }

                } else {

                    // Not connected to Facebook yet.
                    $user_id = fusion_get_userdata('user_id');
                    $user_name = fusion_get_userdata('user_name');
                    $user_email = fusion_get_userdata('user_email');
                    $user_emails = array();
                    // additional emails
                    // cache system emails
                    $email_result = dbquery("SELECT email_address FROM ".DB_LOGIN_EMAILS." WHERE email_address=:email AND email_user=:id", [
                        ':email' => $facebook_email,
                        ':id'    => $user_id
                    ]);
                    if (dbrows($email_result)) {
                        while ($edata = dbarray($email_result)) {
                            $user_emails[] = $edata['email_address'];
                        }
                    }
                    if (!empty($user_email)) {
                        $user_emails = array_flip($user_emails);
                    }

                    if ($user_email == $facebook_email || isset($user_emails[$facebook_email])) {

                        // Update Facebook ID
                        $user = fusion_get_userdata();
                        $user['user_fb_connect'] = $facebook_id;
                        dbquery_insert(DB_USERS, $user, 'update', ['keep_session' => true]);

                        if (!$is_admin) {
                            // Log you in
                            self::authenticate_user_login($user['user_id']);
                        }

                    } else {

                        // check if anyone has used this email address
                        if (!dbcount("(user_id)", DB_USERS, "user_email=:email", [':email' => $facebook_email])) {

                            // send an email verification - activation email
                            if ($is_admin) {

                                $data['email_data'] = [
                                    'email_user'     => $user_id,
                                    'email_address'  => $facebook_email,
                                    'email_type'     => 'facebook',
                                    'email_ref'      => $facebook_id,
                                    'email_verified' => 0,
                                ];
                                dbquery_insert(DB_LOGIN_EMAILS, $data['email_data'], 'save', ['keep_session' => true]);
                                addNotice('success', $locale['uf_fb_connect_502'], 'all');
                                $code = json_encode(array('email_address' => $facebook_email, 'user_id' => $user_id, 'datestamp' => TIME));
                                $code = \defender::encrypt_string($code, SECRET_KEY_SALT);
                                $link = INFUSIONS.'login/user_fields/facebook_connect/facebook_verify.php?code='.$code;
                                $subject = strtr($locale['uf_fb_connect_500'], array('{SITE_NAME}' => fusion_get_settings('sitename')));
                                $message = strtr($locale['uf_fb_connect_501'], array(
                                    '{USER_NAME}'  => $user_name,
                                    '{SITE_NAME}'  => fusion_get_settings('sitename'),
                                    '{ADMIN_NAME}' => fusion_get_settings('siteusername'),
                                    '{LINK}'       => "<a href='$link'>$link</a>",
                                ));
                                sendemail($user_name, $facebook_email, fusion_get_settings('siteusername'), $subject, $message, 'html');
                                addNotice('success', $locale['uf_fb_connect_502'], 'all');

                            } else {

                                $settings = fusion_get_settings();
                                $user_password = self::get_new_user_password();
                                $user_name = $data['facebook_data']['user_firstname'].$data['facebook_data']['user_lastname'].random_int(1, 99);
                                $user = [
                                    'user_name'       => $user_name,
                                    'user_hash'       => $user_password['hash'],
                                    'user_algo'       => $user_password['algo'],
                                    'user_salt'       => $user_password['salt'],
                                    'user_email'      => '',
                                    'user_gender'     => $data['facebook_data']['user_gender'],
                                    'user_hide_email' => 1,
                                    'user_status'     => $settings['admin_activation'] ? 2 : 0,
                                    'user_xxx'        => '',
                                    'user_joined'     => TIME,
                                    'user_ip'         => USER_IP,
                                    'user_ip_type'    => USER_IP_TYPE,
                                    'user_level'      => USER_LEVEL_MEMBER,
                                    'user_theme'      => 'Default',
                                    'user_language'   => LANGUAGE,
                                    'user_timezone'   => fusion_get_settings('timeoffset'),
                                ];
                                if ($settings['email_verification']) {
                                    self::send_email_verification($user);
                                } else {
                                    dbquery_insert(DB_USERS, $user, 'save', ['keep_session' => true]);
                                    // Authenticate and login
                                    addNotice("success", $locale['uf_fb_connect_505'], 'all');
                                    self::authenticate_user_login($user['user_id']);

                                }
                            }
                        } else {
                            // cannot use this account.
                            addNotice('danger', $locale['uf_fb_connect_503'], 'all');
                        }
                    }

                }
            }
        }

        return json_encode(array('response' => $response, 'data' => $data));
    }
}

echo Facebook_Auth::get_fb_json_authenticate();
