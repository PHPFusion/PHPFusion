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

    /**
     * Error string
     *
     * @var string
     */
    private $error = FALSE;

    public function __construct() {

        $locale = fusion_get_locale();
        // Validate the Token When POST is not Empty Automatically
        if (!empty($_POST)) {

            if ($form_id = post('form_id')) {
                $honeypot = \defender::getInstance()->getHoneypot($form_id.'_honeypot');
                if (!empty($honeypot['type']) && $honeypot['type'] == 'honeypot') {
                    if (post($honeypot['input_name'])) {
                        \Authenticate::logOut();
                        redirect(BASEDIR.'error.php?code=403');
                    }
                }
            }

            if (!isset($_POST['fusion_token']) || !isset($_POST['form_id']) || !is_string(
                    $_POST['fusion_token']
                ) || !is_string($_POST['form_id'])) {
                // Check if a token is being posted and make sure is a string
                $this->error = $locale['token_error_2'];

            } else if (!isset($_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']])) {
                // Cannot find any token for this form
                $this->error = $locale['token_error_9'];

            } else if (!in_array(
                $_POST['fusion_token'], $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']]
            )) {
                // Check if the token exists in storage
                $this->error = $locale['token_error_10'].stripinput($_POST['fusion_token']);

            } else if ($error = self::verify_token()) {
                $this->error = $error;
                // Unable to Verify Token
                //$error = $locale['token_error_3'].stripinput($_POST['fusion_token']).$error;
            }

            $tokens_consumed = '';
            if (isset($_POST['form_id'])) {
                if (!empty($_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']])) {
                    $token_rings = $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']];
                    if (!empty($token_rings)) {
                        foreach ($token_rings as $key => $token_storage) {
                            if ($token_storage == $_POST['fusion_token']) {
                                $tokens_consumed = $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']][$key];
                                // addNotice('warning', "Token $tokens_consumed has been consumed", 'all');
                                unset($tokens_consumed);
                                break;
                            }
                        }
                    }
                }
            }

            if (self::$debug) {
                require_once INCLUDES."theme_functions_include.php";
                define('STOP_REDIRECT', TRUE);
                if (isset($_POST['form_id'])) {
                    $token_ring = $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']];
                    $html = openmodal('debug_modal', 'Debug Token');
                    $html .= alert(
                        "<strong>The Form ID Submitted is '".stripinput(
                            $_POST['form_id']
                        )."' having the following tokens: </strong><ul class='block'><li>".implode(
                            "</li><li>", $token_ring
                        )."</li></ul>\n", ['class' => 'alert-danger']
                    );
                    $html .= alert(
                        "Token posted now is ".stripinput(
                            $_POST['fusion_token']
                        ).(!empty($tokens_consumed) ? " and has been consumed" : ''), ['class' => 'alert-warning']
                    );
                    $html .= modalfooter(
                        "<a class='btn btn-default' href='".FUSION_REQUEST."'>Click to Reload Page</a>"
                    );
                    $html .= closemodal();
                    add_to_footer($html);
                }
            }
        }

        if ($this->error) {
            self::$tokenIsValid = FALSE;
            self::stop();
            $token_notice = FALSE;
            if ($token_notice === TRUE) {
                addNotice('danger', $_SERVER['PHP_SELF']);
                addNotice('danger', $this->error);
            }
        }
    }

    /**
     * Plain Token Validation - executed at maincore.php through sniff_token() only.
     * Makes thorough checks of a posted token, and the token alone. It does not unset token.
     *
     * @return bool
     */
    private static function verify_token() {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $error = FALSE;
        $settings = fusion_get_settings();
        $token_data = explode('-', stripinput($_POST['fusion_token']));
        //if (!$post_time) {
        //  $post_time = $settings['flood_interval'];
        //}
        // check if the token has the correct format
        if (count($token_data) == 3) {
            list($tuser_id, $token_time, $hash) = $token_data;
            $user_id = $userdata['user_id'];
            $algo = $settings['password_algorithm'];
            $secret_key_salt = defined('SECRET_KEY_SALT') ? SECRET_KEY_SALT : 'secret_salt';
            $salt = md5(isset($userdata['user_salt']) && !isset($_POST['login']) ? $userdata['user_salt'].$secret_key_salt : $secret_key_salt);
            // check if the logged user has the same ID as the one in token
            if ($tuser_id != $user_id) {
                $error = $locale['token_error_4'];
                // make sure the token datestamp is a number
            } else if (!isnum($token_time)) {
                $error = $locale['token_error_5'];
                // check if the hash is valid
            } else if ($hash !== hash_hmac(
                    $algo, $user_id.$token_time.stripinput($_POST['form_id']).SECRET_KEY, $salt
                )) {
                $error = $locale['token_error_7'];
            } else if ((TIME - $token_time) < fusion_get_settings('flood_interval') && !iADMIN) {
                // check if a post wasn't made too fast. Set $post_time to 0 for instant. Go for System Settings later.
                $error = $locale['token_error_6'];
            }
        } else {
            // token format is incorrect
            $error = $locale['token_error_8'];
        }

        if ($error) {
            return $error;
        }

        return FALSE;
    }

    /**
     * Generates a unique token
     *
     * @param string $form_id
     * @param int    $max_tokens
     *
     * @return string
     */
    public static function generate_token($form_id = 'phpfusion', $max_tokens = 5) {

        $userdata = fusion_get_userdata();

        $settings = fusion_get_settings();

        $user_id = $userdata['user_id'];

        $secret_key = defined('SECRET_KEY') ? SECRET_KEY : 'secret_key';

        $secret_key_salt = defined('SECRET_KEY_SALT') ? SECRET_KEY_SALT : 'secret_salt';

        $algo = !empty($settings['password_algorithm']) ? $settings['password_algorithm'] : 'sha256';

        $key = $user_id.TIME.$form_id.$secret_key;

        $salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].$secret_key_salt : $secret_key_salt);

        // generate a new token
        $token = $user_id.'-'.TIME.'-'.hash_hmac($algo, $key, $salt);

        $page_file = self::pageHash();

        if (\Defender::safe()) {

            // Store into session
            $_SESSION['csrf_tokens'][$page_file][$form_id][] = $token;

            // Round robin consume token
            if (count($_SESSION['csrf_tokens'][$page_file][$form_id]) > $max_tokens) {

                array_shift($_SESSION['csrf_tokens'][$page_file][$form_id]);

            }

        } else {

            if (!empty($_SESSION['csrf_tokens']) && !empty($_SESSION['csrf_tokens'][$page_file][$form_id])) {

                $token_ring = $_SESSION['csrf_tokens'][$page_file][$form_id];

                $ring = array_rand($token_ring, 1);

                $token = $token_ring[$ring];

            } else {

                $_SESSION['csrf_tokens'][$page_file][$form_id][] = $token;

            }

        }

        // Debugging section
        if (self::$debug) {

            if (!self::safe()) {
                echo alert('FUSION NULL is DECLARED');
            }

            if (!empty($_SESSION['csrf_tokens'][$page_file][$form_id])) {
                $token_ring = $_SESSION['csrf_tokens'][$page_file][$form_id];
                $text = "<strong>New Valid tokens for Form ID <kbd>$form_id</kbd> for ".$page_file.": </strong><ul class='block'><li>".implode("</li><li>", $token_ring)."</li></ul>\n";
                echo alert($text, ['class' => 'alert-success']);
            } else {
                echo alert('There is no token for this page this round');
            }
        }

        return (string)$token;
    }
}
