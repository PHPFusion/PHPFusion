<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/defender/token.php
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
namespace Defender;
/**
 * Class Token
 * CSRF protection layer for PHP-Fusion CMS
 *
 * @package Defender
 */
class Token extends \defender {

    /**
     * The remote file must begin with site_path
     *
     * @var string
     */
    public static $remote_file = '';
    /**
     * Allow using back a valid token by not consuming any tokens at all
     *
     * @var bool
     */
    public static $allow_repost = FALSE;
    /**
     * System to check whether post token is valid
     *
     * @var bool
     */
    private static $tokenIsValid = FALSE;
    /**
     * Set debug mode
     *
     * @var bool - true to debug
     */
    private static $debug = FALSE;

    public function __construct() {
        $locale = fusion_get_locale();
        $error = FALSE;

        // Validate the Token When POST is not Empty Automatically
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
            } elseif ($error = self::verify_token()) {
                //$error = $locale['token_error_3'].stripinput($_POST['fusion_token']);
            }

            // If you allow repost, token will be valid since it is not being consumed.
            // Bots will capture a valid token key and repost again and again.
            if (self::$allow_repost == FALSE && !iADMIN) {
                $tokens_consumed = '';
                $token_rings = $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']];
                if (!empty($token_rings)) {
                    foreach ($token_rings as $key => $token_storage) {
                        if ($token_storage == $_POST['fusion_token']) {
                            $tokens_consumed = $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']][$key];
                            unset($_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']][$key]);
                            break;
                        }
                    }
                }
            }

            if (self::$debug) {
                require_once INCLUDES."theme_functions_include.php";
                define('STOP_REDIRECT', true);
                $token_ring = $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']];
                $html = openmodal('debug_modal', 'Debug Token');
                $html .= alert("<strong>The Form ID Submitted is '".stripinput($_POST['form_id'])."' having the following tokens: </strong><ul class='block'><li>".implode("</li><li>", $token_ring)."</li></ul>\n", ['class' => 'alert-danger']);
                $html .= alert("Token posted now is ".stripinput($_POST['fusion_token']).($tokens_consumed ? " and has been consumed" : ''), ['class' => 'alert-warning']);
                $html .= modalfooter("<a class='btn btn-default' href='".FUSION_REQUEST."'>Click to Reload Page</a>");
                $html .= closemodal();
                add_to_footer($html);
            }
        }

        if ($error) {
            self::$tokenIsValid = FALSE;
            self::stop($error);
            if (self::$debug === TRUE) {
                addNotice('danger', $_SERVER['PHP_SELF']);
                addNotice('danger', $error);
            }
        }
    }

    /**
     * Plain Token Validation - executed at maincore.php through sniff_token() only.
     * Makes thorough checks of a posted token, and the token alone. It does not unset token.
     *
     * @param int $post_time      The time in seconds before a posted form is accepted,
     *                            this is used to prevent spamming post submissions
     *
     * @return bool
     */
    private static function verify_token($post_time = 0) {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $error = FALSE;
        $settings = fusion_get_settings();
        $token_data = explode('.', stripinput($_POST['fusion_token']));
        //if (!$post_time) {
        //  $post_time = $settings['flood_interval'];
        //}
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
            } elseif ($hash !== hash_hmac($algo, $user_id.$token_time.stripinput($_POST['form_id']).SECRET_KEY, $salt)) {
                $error = $locale['token_error_7'];
                // check if a post wasn't made too fast. Set $post_time to 0 for instant. Go for System Settings later.
                /*
                 * Disable this because we have flood_control. Either implement flood control here for checks, and increment API altogether
                 * or remove this.
                 */
                // } elseif ((TIME - $token_time) < $post_time && !iADMIN) {
                // $error = $locale['token_error_6'];
            }
        } else {
            // token format is incorrect
            $error = $locale['token_error_8'];
        }

        if ($error) {
            return $error;
        } elseif (self::$debug) {
            addNotice('success', 'The token for "'.stripinput($_POST['form_id']).'" has been validated successfully');
        }

        return FALSE;
    }

    /**
     * Generates a unique token in using open_form();
     *
     * @param string $form_id
     * @param int    $max_tokens
     * @param string $file
     *
     * @return mixed|string|\string[]
     */
    public static function generate_token($form_id = 'phpfusion', $max_tokens = 5, $file = '') {
        // resets remote file every callback
        $remote_file = ($file ? $file : '');
        \defender::getInstance()->set_RemoteFile($remote_file);

        $userdata = fusion_get_userdata();
        $user_id = (iMEMBER ? $userdata['user_id'] : 0);
        if ($user_id == 0) $max_tokens = 1;

        // Only generate new tokens when token is less than max allowed tokens
        if (!isset($_SESSION['csrf_tokens'][self::pageHash($file)][$form_id]) || count($_SESSION['csrf_tokens'][self::pageHash($file)][$form_id]) < $max_tokens) {

            $secret_key = defined('SECRET_KEY') ? SECRET_KEY : 'secret_key';
            $secret_key_salt = defined('SECRET_KEY_SALT') ? SECRET_KEY_SALT : 'secret_salt';
            $token_time = TIME;
            $algo = fusion_get_settings('password_algorithm') ? fusion_get_settings('password_algorithm') : 'sha256';
            $key = $user_id.$token_time.$form_id.$secret_key;
            $salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].$secret_key_salt : $secret_key_salt);
            // generate a new token
            $token = $user_id.'.'.$token_time.'.'.hash_hmac($algo, $key, $salt);
            // Store into session
            $_SESSION['csrf_tokens'][self::pageHash($file)][$form_id][] = $token;
        } else {
            // randomize token output
            $token_ring = $_SESSION['csrf_tokens'][self::pageHash($file)][$form_id];
            $ring = array_rand($token_ring, 1);
            $token = $token_ring[$ring];
        }

        if (self::$debug) {
            if (!self::safe()) {
                echo alert('FUSION NULL is DECLARED');
            }
            if (!empty($_SESSION['csrf_tokens'][self::pageHash($file)][$form_id])) {
                $token_ring = $_SESSION['csrf_tokens'][self::pageHash($file)][$form_id];
                $text = "<strong>New Valid tokens for Form ID '$form_id' for ".self::pageHash($file).": </strong><ul class='block'><li>".implode("</li><li>", $token_ring)."</li></ul>\n";
                echo alert($text, ['class' => 'alert-success']);
            } else {
                echo alert('There is no token for this page this round');
            }
        }

        return $token;
    }
}