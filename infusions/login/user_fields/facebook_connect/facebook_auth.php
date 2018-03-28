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
     * Returns 3 possible scenario
     * 1. User have used facebook_id before.
     */
    public static function get_fb_json_authenticate() {
        // extends user column with the following field structure
        $facebook_id = stripinput($_REQUEST['id']);
        $facebook_email = stripinput($_REQUEST['email']);
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
        if (in_array('user_facebook_uid', $table)) {
            if (dbcount("(user_id)", DB_USERS, "user_email=:email AND user_facebook_uid=:id", array(
                ':id'    => $facebook_id,
                ':email' => $facebook_email
            ))) {
                $user = dbarray(dbquery("SELECT user_id, user_salt, user_algo, user_level, user_theme FROM ".DB_USERS." WHERE user_email=:email AND user_facebook_uid=:id LIMIT 1", array(
                    ':id'    => $facebook_id,
                    ':email' => $facebook_email
                )));
                /*
                 * Do release authentication code here.
                 */
                \PHPFusion\Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], FALSE, TRUE);
                \PHPFusion\Authenticate::_setUserTheme($user);
                unset($_SESSION['facebook_user'][USER_IP]);

                $response = 'authenticated';

            } else {
                $response = 'register-form';
                if ($count = dbcount("(user_id)", DB_USERS, "user_email=:email", array(':email' => $facebook_email))) {
                    if ($count > 1) {
                        $response = 'connect-form';
                    } else {

                        $user = dbarray(dbquery("SELECT user_id, user_salt, user_algo, user_level, user_theme FROM ".DB_USERS." WHERE user_email=:email LIMIT 1", array(
                            ':email' => $facebook_email
                        )));
                        // Update the facebook user id
                        $user['user_facebook_uid'] = $facebook_id;
                        dbquery_insert(DB_USERS, $user, 'update', ['keep_session' => true]);
                        // update the user to use and authenticate.
                        \PHPFusion\Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], FALSE, TRUE);
                        \PHPFusion\Authenticate::_setUserTheme($user);
                        unset($_SESSION['facebook_user'][USER_IP]);

                        $response = 'authenticated';
                    }
                }
            }
        }

        return json_encode(array('response' => $response));
    }
}

echo Facebook_Auth::get_fb_json_authenticate();
