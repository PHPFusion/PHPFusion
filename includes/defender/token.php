<?php

namespace Defender;

/**
 * Class Token
 * CSRF protection layer for PHP-Fusion CMS
 * @package Defender
 */
class Token extends \defender {

    public static $remote_file = '';
    private static $tokenIsValid = FALSE;
    private static $recycled_token = '';
    private static $debug = FALSE;

    public function __construct() {
        $locale = fusion_get_locale();
        $error = FALSE;
        if (!empty($_POST)) {
            // Check if a token is being posted and make sure is a string
            if (!isset($_POST['fusion_token']) || !isset($_POST['form_id']) || !is_string($_POST['fusion_token']) || !is_string($_POST['form_id'])) {
                $error = $locale['token_error_2'];
            } elseif (!isset($_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']])) {
                // Cannot find any token for this form
                $error = $locale['token_error_9'];
                // Check if the token exists in storage
            } elseif (!in_array($_POST['fusion_token'], $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']])) {
                $error = $locale['token_error_10'].stripinput($_POST['fusion_token']);
            } elseif (!self::verify_token(0)) {
                $error = $locale['token_error_3'].stripinput($_POST['fusion_token']);
            }
        }
        // Check if any error was set
        if ($error !== FALSE) {
            // Flag the token as invalid
            self::$tokenIsValid = FALSE;
            // Flag that something went wrong
            self::stop();
            if (self::$debug === TRUE) {
                // Add Error Notices
                setError(2, $error, FUSION_SELF, FUSION_REQUEST, "");
                addNotice('danger', $error);
            }
        }
    }

    /**
     * Plain Token Validation - executed at maincore.php through sniff_token() only.
     * Makes thorough checks of a posted token, and the token alone. It does not unset token.
     * @param int $post_time The time in seconds before a posted form is accepted,
     *                            this is used to prevent spamming post submissions
     * @return bool
     */
    private static function verify_token($post_time = 5) {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $error = FALSE;
        $settings = fusion_get_settings();
        $token_data = explode('.', stripinput($_POST['fusion_token']));
        // check if the token has the correct format
        if (count($token_data) == 3) {
            list($tuser_id, $token_time, $hash) = $token_data;
            $user_id = (iMEMBER ? $userdata['user_id'] : 0);
            $algo = $settings['password_algorithm'];
            $salt = md5(isset($userdata['user_salt']) && !isset($_POST['login']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
            // check if the logged user has the same ID as the one in token
            if ($tuser_id != $user_id) {
                $error = $locale['token_error_4'];
                // make sure the token datestamp is a number
            } elseif (!isnum($token_time)) {
                $error = $locale['token_error_5'];
                // check if the hash is valid
            } elseif ($hash != hash_hmac($algo, $user_id.$token_time.stripinput($_POST['form_id']).SECRET_KEY, $salt)) {
                $error = $locale['token_error_7'];
                // check if a post wasn't made too fast. Set $post_time to 0 for instant. Go for System Settings later.
            } elseif (time() - $token_time < $post_time) {
                $error = $locale['token_error_6'];
            }
        } else {
            // token format is incorrect
            $error = $locale['token_error_8'];
        }
        // Check if any error was set
        if ($error !== FALSE) {
            self::stop();
            if (self::$debug) {
                addNotice('danger', $error);
            }

            return FALSE;
        }
        // If we made it so far everything is good
        if (self::$debug) {
            addNotice('info', 'The token for "'.stripinput($_POST['form_id']).'" has been validated successfully');
        }

        return TRUE;
    }

    public static function generate_token($form_id = 'phpfusion', $max_tokens = 1, $file = "") {

        $userdata = fusion_get_userdata();

        $user_id = (iMEMBER ? $userdata['user_id'] : 0);
        // store just one token for each form if the user is a guest
        if ($user_id == 0) {
            $max_tokens = 1;
        }

        // resets remote file every callback
        self::$remote_file = ($file ? $file : '');

        if (isset($_POST['fusion_token']) && self::$tokenIsValid && ($form_id == stripinput($_POST['form_id']))) {
            /**
             * Attempt to recover the token instead of generating a new one
             * Checks if a token is being posted and if is valid, and then
             * checks if the form for which this token was intended is
             * the same form for which we are trying to generate a token
             */
            $token = stripinput($_POST['fusion_token']);

            if (self::$debug) {
                addNotice('success', 'The token for "'.stripinput($_POST['form_id']).'" has been recovered and is being reused');
            }

            self::$recycled_token = $token;

        } else {

            $secret_key = defined('SECRET_KEY') ? SECRET_KEY : 'secret_key';

            $secret_key_salt = defined('SECRET_KEY_SALT') ? SECRET_KEY_SALT : 'secret_salt';

            $token_time = time();
            $algo = fusion_get_settings('password_algorithm') ? fusion_get_settings('password_algorithm') : 'sha256';
            $key = $user_id.$token_time.$form_id.$secret_key;
            $salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].$secret_key_salt : $secret_key_salt);
            // generate a new token
            $token = $user_id.".".$token_time.".".hash_hmac($algo, $key, $salt);
            // store the token in session
            $_SESSION['csrf_tokens'][self::pageHash($file)][$form_id][] = $token;
            if (self::$debug) {
                if (!self::safe()) {
                    addNotice('danger', 'FUSION NULL is DECLARED');
                }
                if (!empty($_SESSION['csrf_tokens'][self::pageHash($file)][$form_id])) {
                    addNotice('danger', 'Current Token That is Going to be validated in this page: ');
                    addNotice('danger',
                              $_SESSION['csrf_tokens'][self::pageHash($file)][$form_id]); // is not going to be able to read the new one.
                } else {
                    addNotice('warning', 'There is no token for this page this round');
                }
            }
            // some cleaning, remove oldest token if there are too many
            if (count($_SESSION['csrf_tokens'][self::pageHash($file)][$form_id]) > $max_tokens) {
                if (self::$debug) {
                    addNotice('warning',
                              'Token that is <b>erased</b> '.$_SESSION['csrf_tokens'][self::pageHash($file)][$form_id][0].'. This token cannot be validated anymore.');
                }
                array_shift($_SESSION['csrf_tokens'][self::pageHash($file)][$form_id]);
            }

            if (self::$debug) {
                if (!empty($_SESSION['csrf_tokens'][self::pageHash($file)][$form_id])) {
                    addNotice('danger', "After clean up, the token remaining is on ".$form_id." is -- ");
                    addNotice('danger', $_SESSION['csrf_tokens'][self::pageHash($file)][$form_id]);
                } else {
                    addNotice('warning', 'There is no token for this page this round');
                }
            }
        }

        return $token;
    }

    public static function remove_token() {
        if (self::safe() && !empty($_POST['form_id']) && self::$tokenIsValid) {
            $tokens = $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']];
            $current_token = reset($tokens);
            if (self::$recycled_token && self::$recycled_token !== $current_token) {
                array_shift($tokens);
            }
        }
    }

}