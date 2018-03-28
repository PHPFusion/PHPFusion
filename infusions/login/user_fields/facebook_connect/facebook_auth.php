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
        $locale = fusion_get_locale("", LOGIN_LOCALESET.'user_fb_connect.php');
        $facebook_id = stripinput($_REQUEST['id']);
        $facebook_email = stripinput($_REQUEST['email']);
        // if $_REQUEST['skip_auth'] is true - do not authorize login.

        $_SESSION['facebook_user'][USER_IP] = [
            'user_email'        => $facebook_email,
            'user_facebook_uid' => $facebook_id,
            'user_firstname'    => stripinput($_REQUEST['first_name']),
            'user_last_name'    => stripinput($_REQUEST['last_name']),
            'user_gender'       => stripinput($_REQUEST['gender']),
            'user_timezone'     => stripinput($_REQUEST['timezone'])
        ];
        $response = 'error';

        $table = fieldgenerator(DB_USERS);

        if (in_array('user_fb_connect', $table)) {

            if (dbcount("(user_id)", DB_USERS, "user_fb_connect=:id AND user_status=0", array(
                ':id'    => $facebook_id,
                //':email' => $facebook_email
            ))) {

                $response = 'authenticated-skip';

                $user = dbarray(dbquery("SELECT user_id, user_salt, user_algo, user_level, user_theme FROM ".DB_USERS." WHERE user_facebook_uid=:id LIMIT 1", array(
                    ':id'    => $facebook_id,
                    //':email' => $facebook_email
                )));

                if (empty($_REQUEST['skip_auth'])) { // if  is false only
                    \PHPFusion\Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], FALSE, TRUE);
                    \PHPFusion\Authenticate::_setUserTheme($user);
                    unset($_SESSION['facebook_user'][USER_IP]);
                    $response = 'authenticated';
                }

            } else {

                $response = 'register-form';

                if ($count = dbcount("(user_id)", DB_USERS, "user_email=:email AND user_status=0", array(':email' => $facebook_email))) {
                    if ($count > 1) {

                        $response = 'connect-form';
                        // for existing user account. select that account to another page.

                    } else {

                        $user = dbarray(dbquery("SELECT user_id, user_salt, user_algo, user_level, user_theme FROM ".DB_USERS." WHERE user_email=:email LIMIT 1", array(
                            ':email' => $facebook_email
                        )));

                        // Update the facebook user id
                        if (empty($_REQUEST['skip_auth'])) {
                            $user['user_facebook_connect'] = $facebook_id;
                            dbquery_insert(DB_USERS, $user, 'update', ['keep_session' => true]);
                            // update the user to use and authenticate.
                            \PHPFusion\Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], FALSE, TRUE);
                            \PHPFusion\Authenticate::_setUserTheme($user);
                            unset($_SESSION['facebook_user'][USER_IP]);
                        }
                        $response = 'authenticated';
                    }
                } else {
                    // this user does not have account. we need to check if the user is login or not. if he is...
                    // then it means he is in edit_profile.php
                    // double check with skip_auth is present.
                    if (iMEMBER && !empty($_REQUEST['skip_auth'])) {
                        // this person is trying to pair his current login FB account into this user account.
                        // now we need to check if he has entered email if yes, we show him link to activate email.
                        // if no, we confirm and send email.
                        if (!dbcount("(email_user)", DB_LOGIN_EMAILS, "email_address=:email", [':email' => $facebook_email])) {
                            // not yet entered
                            // enter email.
                            $response = 'emailed';
                            $user_id = fusion_get_user('user_id');
                            $user_name = fusion_get_user('user_name');
                            $email_data = [
                                'email_user'     => fusion_get_user('user_id'),
                                'email_address'  => $facebook_email,
                                'email_type'     => 'facebook',
                                'email_validate' => 1,
                            ];
                            $email_id = dbquery_insert(DB_LOGIN_EMAILS, $email_data, 'save', ['keep_session' => true]);
                            $code = json_encode(array('email_id' => $email_id, 'facebook_email' => $facebook_email, 'user_id' => $user_id, 'datestamp' => TIME));
                            $code = \defender::encrypt_string($code, SECRET_KEY_SALT);
                            $link = INFUSIONS.'login/user_fields/facebook_connect/facebook_verify.php?code='.$code;

                            $subject = strtr($locale['uf_fb_connect_500'], array('{SITE_NAME}' => fusion_get_settings('sitename')));
                            $message = strtr($locale['uf_fb_connect_501'], array(
                                '{USER_NAME}'  => $user_name,
                                '{SITE_NAME}'  => fusion_get_settings('sitename'),
                                '{ADMIN_NAME}' => fusion_get_settings('siteadmin'),
                                '{LINK}'       => "<a href='$link'>$link</a>",
                            ));
                            sendemail($user_name, $facebook_email, fusion_get_settings('site_admin'), $subject, $message, 'html');
                            addNotice('success', $locale['uf_fb_connect_502'], 'all');
                        } else {
                            $response = 'verify-email';
                            addNotice('success', $locale['uf_fb_connect_503'], 'all');
                        }
                    } else {
                        // go for registration form here.
                    }
                }
            }
        }

        return json_encode(array('response' => $response));
    }
}

echo Facebook_Auth::get_fb_json_authenticate();
