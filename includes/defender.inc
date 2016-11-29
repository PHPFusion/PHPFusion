<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: defender.inc.php
| Author : Frederick MC Chan (Chan)
| Co-Author: Dan C (JoiNNN)
| Version : 9.0.5 (please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

class defender {

    private static $debug = FALSE;
    private static $defender_instance = NULL;
    private static $remote_file = '';
    public $ref = array();

    public $error_title = '';
    public $input_errors = array();
    public $field = array();

    // Declared by Form Sanitizer
    public $field_name = '';
    public $field_value = '';
    public $field_default = '';
    public $field_config = array(
        'type' => '',
        'value' => '',
        //'default' => '',
        'name' => '',
        //'id' => '',
        'safemode' => '',
        'path' => '',
        'thumbnail_1' => '',
        'thumbnail_2' => '',
    );
    private $input_error_text = array();
    private $tokenIsValid = TRUE;
    // Sanitize Fields Automatically
    /**
     * Generate a Token
     * Generates a unique token
     * @param string $form_id The ID of the form
     * @param int    $max_tokens The ammount of tokens to be kept for each form before we start removing older tokens from session
     * @return string|string[]        The token string
     */
    private $recycled_token = "";

    /**
     * Generates and return class instance
     * Eliminates global usage in functions
     * Instead of using  - `global $defender`, try `\defender->getInstance()`
     * @return null|static
     */
    public static function getInstance() {
        if (self::$defender_instance === NULL) {
            self::$defender_instance = new static();
        }
        return self::$defender_instance;
    }

    /**
     * Serialize an array securely
     * @param array $array
     * @return string
     */
    public static function serialize(array $array = array()) {
        $return_default = "";
        if (is_array($array)) {
            return base64_encode(serialize($array));
        }

        return $return_default;
    }

    /**
     * Read serialized array
     * @param $string - serialized string
     * @return array|mixed
     */
    public static function unserialize($string) {
        $return_default = array();
        if (!empty($string)) {
            $array = unserialize(base64_decode($string));
            if (!empty($array)) {
                return $array;
            }
        }

        return $return_default;
    }

    /**
     * ID for Session
     * No $userName because it can be changed and tampered via Edit Profile.
     * Using IP address extends for guest
     * @return mixed
     */
    static function set_sessionUserID() {
        $userdata = fusion_get_userdata();
        return isset($userdata['user_id']) && !isset($_POST['login']) ? (int)$userdata['user_id'] : str_replace('.', '-', USER_IP);
    }

    static function add_field_session(array $array) {
        $_SESSION['form_fields'][self::pageHash(self::$remote_file)][$array['input_name']] = $array;
    }

    // Checks whether an input was marked as invalid
    /**
     * Generates a md5 hash of the current page to make token session unique
     * Eg. /php-fusion/infusions/blog/blog.php for Non
     * @param string     $file
     * @param bool|FALSE $encrypt
     * @return string
     */
    public static function pageHash($file = "", $encrypt = TRUE) {
        if (fusion_get_settings("site_seo") == 1 && !preg_match('/administration/i', $_SERVER['PHP_SELF'])) {
            $hash = $encrypt ? md5('administration') : 'administration';
        } else {
            $hash = $encrypt ? md5($_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'];
            if (!empty($file)) {
                $hash = $encrypt ? md5($file) : $file;
            } elseif (!empty($_POST['file'])) {
                $hash = $encrypt ? md5($_POST['file']) : $_POST['file'];
            }
        }
        return (string) $hash;
    }

    /**
     * Return the current document field session or sessions
     * Use for debug purposes
     * @param string $input_name
     * @return string
     */
    static function get_current_field_session($input_name = "") {
        if ($input_name && isset($_SESSION['form_fields'][self::pageHash()][$input_name])) {
            return $_SESSION['form_fields'][self::pageHash()][$input_name];
        } else {
            if ($input_name) {
                return "The session for this field is not found";
            } else {
                //return $_SESSION['form_fields'];
                return $_SESSION['form_fields'][self::pageHash()];
            }
        }
    }

    public static function unset_field_session() {
        unset($_SESSION['form_fields']);
    }

    static function sanitize_array($array) {
        foreach ($array as $name => $value) {
            $array[stripinput($name)] = trim(censorwords(stripinput($value)));
        }

        return (array)$array;
    }

    /**
     * @param bool|FALSE $debug_status
     */
    public function setDebug($debug_status = FALSE) {
        self::$debug = $debug_status;
    }


    public function generate_token($form_id = 'phpfusion', $max_tokens = 10, $file = "") {

        $userdata = fusion_get_userdata();
        $user_id = (iMEMBER ? $userdata['user_id'] : 0);
        // store just one token for each form if the user is a guest
        if ($user_id == 0) {
            $max_tokens = 1;
        }
        // resets remote file every callback
        self::$remote_file = ($file ? $file : '');

        if (isset($_POST['fusion_token']) && $this->tokenIsValid && ($form_id == stripinput($_POST['form_id']))) {
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

            $this->recycled_token = $token;

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

    /**
     * Request whether safe to proceed at all times
     * @return bool
     */
    public static function safe() {
        if (!defined("FUSION_NULL")) {
            return TRUE;
        }

        return FALSE;
    }

    public function remove_token() {

        if (self::safe() && !empty($_POST['form_id']) && $this->tokenIsValid) {
            $tokens = $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']];
            $current_token = reset($tokens);
            if ($this->recycled_token && $this->recycled_token !== $current_token) {
                array_shift($tokens);
            }
        }
    }

    /**
     * @return array
     */
    public function getInputErrors() {
        return $this->input_errors;
    }

    /**
     * Set and override default field error text
     * @param $input_name
     * @param $text
     */
    public function setErrorText($input_name, $text) {
        $this->input_error_text[$input_name] = $text;
    }

    /**
     * Fetches the latest error text of this input
     * Important! Ensure your applications do not refresh screen for this error to show.
     * Usage \defender::safe(); for conditional redirect.
     * @param $input_name
     * @return null
     */
    public function getErrorText($input_name) {
        if ($this->inputHasError($input_name)) {
            return isset($this->input_error_text[$input_name]) ? $this->input_error_text[$input_name] : NULL;
        }

        return NULL;
    }

    public function inputHasError($input_name) {
        if (isset($this->input_errors[$input_name])) {
            return TRUE;
        }

        return FALSE;
    }

    public function get_inputError() {
        return $this->input_errors;
    }

    /**
     * Token Sniffer
     * Checks whether a post contains a valid token
     */
    public function sniff_token() {
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
            $this->tokenIsValid = FALSE;
            // Flag that something went wrong
            $this->stop();
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
        $token_data = explode(".", stripinput($_POST['fusion_token']));
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

    /**
     * Send an Unsafe Signal acorss all PHP-Fusion Components
     * This will automatically halt on all important execution without exiting.
     */
    static function stop() {
        if (!defined('FUSION_NULL')) {
            addNotice('danger', fusion_get_locale('error_request', LOCALE.LOCALESET.'defender.php'));
            define('FUSION_NULL', TRUE);
            if (self::$debug) {
                debug_print_backtrace();
                define("STOP_REDIRECT", TRUE);
            }
        }
    }

    /**
     * Sanitize
     * @param            $value
     * @param string     $default
     * @param bool|FALSE $input_name
     * @param bool|FALSE $is_multiLang
     * @return string
     * @throws Exception
     */
    public function form_sanitizer($value, $default = "", $input_name = FALSE, $is_multiLang = FALSE) {

        $val = array();

        if ($input_name) {

            if ($is_multiLang) {

                foreach (fusion_get_enabled_languages() as $lang => $language) {
                    $iname = $input_name."[".$lang."]";

                    if (isset($_SESSION['form_fields'][self::pageHash()][$iname])) {

                        $this->field_config = $_SESSION['form_fields'][self::pageHash()][$iname];
                        $this->field_name = $iname;
                        $this->field_value = $value[$lang];
                        $this->field_default = $default;
                        $val[$lang] = $this->validate();
                    }
                }

                if ($this->field_config['required'] && (!$value[LANGUAGE])) {

                    $this->stop();
                    $iname = $input_name."[".LANGUAGE."]";
                    $this->setInputError($iname);

                    return $default;

                } else {

                    foreach ($val as $lang => $value) {
                        $val[$lang] = $val[$lang];
                    }

                    return serialize($val);
                }

            } else {

                // Make sure that the input was actually defined in code..
                // AND there must be a value to worth the processing power expense!
                if (isset($_SESSION['form_fields'][self::pageHash()][$input_name])) {
                    $this->field_config = $_SESSION['form_fields'][self::pageHash()][$input_name];
                    $this->field_name = $input_name;
                    $this->field_value = $value;
                    $this->field_default = $default;
                    // These two checks won't be neccesary after we add the options in all inputs
                    // NOTE: Please don't pass 'stripinput' as callback, before we reach a callback
                    // everything is checked and sanitized already. The callback should only check
                    // if certain conditions are met then return TRUE|FALSE and not do any alterations
                    // the the value itself
                    $callback = isset($this->field_config['callback_check']) ? $this->field_config['callback_check'] : FALSE;
                    $regex = isset($this->field_config['regex']) ? $this->field_config['regex'] : FALSE;
                    $secured = $this->validate();
                    // If truly FALSE the check failed
                    if ($secured === FALSE || ($this->field_config['required'] == 1 && ($secured === FALSE || $secured == '')) ||
                        ($secured != '' && $regex && !preg_match('@^'.$regex.'$@i',
                                                                 $secured)) || // regex will fail for an imploded array, maybe move this check
                        (is_callable($callback) && !$callback($secured))
                    ) {
                        // Flag that something went wrong
                        $this->stop();
                        $this->setInputError($input_name);

                        // Add regex error message.
                        if ($secured != '' && $regex && !preg_match('@^'.$regex.'$@i', $secured)) {
                            global $locale;
                            addNotice("danger", sprintf($locale['regex_error'], $this->field_config['title']));
                            unset($locale);
                        }
                        // Add a notice
                        if (self::$debug) {
                            addNotice('warning',
                                      '<strong>'.$input_name.':</strong>'.($this->field_config['safemode'] ? ' is in SAFEMODE and the' : '').' check failed');
                        }
                        // Return user's input for correction
                        return $this->field_value;

                    } else {
                        if (self::$debug) {
                            addNotice('info', $input_name.' = '.(is_array($secured) ? 'array' : $secured));
                        }

                        return $secured;
                    }
                } else {
                    return $default;
                }
            }
        } else {
            if ($value) {
                if (!is_array($value)) {
                    if (intval($value)) {
                        return stripinput($value); // numbers
                    } else {
                        return stripinput(trim(preg_replace("/ +/i", " ", censorwords($value))));
                    }
                } else {
                    $secured = array();
                    foreach ($value as $arr => $unsecured) {
                        if (intval($unsecured)) {
                            $secured[] = stripinput($unsecured); // numbers
                        } else {
                            $secured[] = stripinput(trim(preg_replace("/ +/i", " ", censorwords($unsecured))));
                        }
                    }

                    return implode($this->field_config['delimiter'], $secured);
                }
            } else {
                return $default;
            }
        }

        throw new \Exception('The form sanitizer could not handle the request! (input: '.$input_name.')');
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function validate() {

        \Defender\Validation::inputName($this->field_name);
        \Defender\Validation::inputDefault($this->field_default);
        \Defender\Validation::inputValue($this->field_value);
        \Defender\Validation::inputConfig($this->field_config);

        $locale = fusion_get_locale(LOCALE.LOCALESET.'defender.php');
        // Are there situations were inputs could have leading
        // or trailing spaces? If not then uncomment line below
        //$this->field_value = trim($this->field_value);
        // Don't bother processing and validating empty inputs
        //if ($this->field_value == '') return $this->field_value;
        /**
         * Keep this include in the constructor
         * This solution was needed to load the defender.inc.php before
         * defining LOCALESET
         */
        // declare the validation rules and assign them
        // type of fields vs type of validator

        // execute sanitisation rules at point blank precision using switch
        try {
            if (!empty($this->field_config['type'])) {
                if (empty($this->field_value) && ($this->field_config['type'] !== "number")) {
                    return $this->field_default;
                }
                return \Defender\Validation::getValidated();
            } else {
                $this->stop();
                $locale['type_unknown'] = '%s: has an unknown type set'; // to be moved
                addNotice('danger', sprintf($locale['type_unknown'], self::$inputName));
            }
        } catch (Exception $e) {
            $this->stop();
            addNotice('danger', $e->getMessage());
        }
    }

    public function setInputError($input_name) {
        $this->input_errors[$input_name] = TRUE;
    }

}

/**
 * Verify and Sanitize Inputs
 * @param            $value
 * @param string     $default
 * @param bool|FALSE $input_name
 * @param bool|FALSE $is_multiLang
 * @return mixed
 */
function form_sanitizer($value, $default = "", $input_name = FALSE, $is_multiLang = FALSE) {
    return defender::getInstance()->form_sanitizer($value, $default, $input_name, $is_multiLang);
}

require(dirname(__FILE__) . '/defender/validation.php');
