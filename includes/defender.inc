<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: defender.inc
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

    public static $input_errors = array();
    private static $debug = FALSE;
    private static $defender_instance = NULL;
    private static $remote_file = '';
    public $ref = array();
    public $error_title = '';
    public $field = array();

    // Declared by Form Sanitizer
    public $field_name = '';
    public $field_value = '';
    public $field_default = '';
    public $field_config = array(
        'type'        => '',
        'value'       => '',
        //'default' => '',
        'name'        => '',
        //'id' => '',
        'safemode'    => '',
        'path'        => '',
        'thumbnail_1' => '',
        'thumbnail_2' => '',
    );
    private static $input_error_text = array();

    private function __construct() {
    }

    /**
     * Generates and return class instance
     * Eliminates global usage in functions
     * Instead of using  - `global $defender`, try `\defender->getInstance()`
     *
     * @return null|static
     */
    public static function getInstance() {
        if (self::$defender_instance === NULL) {
            self::$defender_instance = new static();
        }

        return self::$defender_instance;
    }

    public static function set_remoteFile($file) {
        self::$remote_file = $file;
    }

    /**
     * Request whether safe to proceed at all times
     *
     * @return bool
     */
    public static function safe() {
        if (!defined("FUSION_NULL")) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Serialize an array securely
     *
     * @param array $array
     *
     * @return string
     */
    public static function serialize(array $array = array()) {
        $return_default = "";
        if (is_array($array)) {
            return base64_encode(serialize($array));
        }

        return $return_default;
    }

    public static function encode($value) {
        return base64_encode(json_encode($value));
    }

    public static function decode($value) {
        return json_decode(base64_decode($value), TRUE);
    }

    /**
     * Read serialized array
     *
     * @param $string - serialized string
     *
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

    public static function add_field_session(array $array) {
        $_SESSION['form_fields'][self::pageHash(self::$remote_file)][$array['input_name']] = $array;
    }

    /**
     * Generates a md5 hash of the current page to make token session unique
     * Eg. /php-fusion/infusions/blog/blog.php for Non
     *
     * @param string     $file
     * @param bool|FALSE $encrypt
     *
     * @return string
     */
    public static function pageHash($file = '', $encrypt = FALSE) {
        if (fusion_get_settings("site_seo") == 1 && !preg_match('/administration/i', $_SERVER['PHP_SELF'])) {
            $hash = $encrypt ? md5('administration') : 'administration';
        } else {
            $hash = $encrypt ? md5($_SERVER['PHP_SELF']) : $_SERVER['PHP_SELF'];
            if (!empty($file)) {
                // file needs to take out the request string.
                if (stristr($file, '?')) {
                    $file_arr = explode('?', $file);
                    $file = $file_arr[0];
                }
                $hash = $encrypt ? md5($file) : $file;
            } elseif (!empty($_POST['file'])) {
                if (stristr($_POST['file'], '?')) {
                    $file_arr = explode('?', $_POST['file']);
                    $file = $file_arr[0];
                }
                $hash = $encrypt ? md5($file) : $file;
            }
        }
        return (string)$hash;
    }

    // Checks whether an input was marked as invalid

    /**
     * Return the current document field session or sessions
     * Use for debug purposes
     *
     * @param string $input_name
     *
     * @return string
     */
    public static function get_current_field_session($input_name = '') {
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
     * ID for Session
     * No $userName because it can be changed and tampered via Edit Profile.
     * Using IP address extends for guest
     *
     * @return mixed
     */
    public static function set_sessionUserID() {
        $userdata = fusion_get_userdata();

        return !empty($userdata['user_id']) && !isset($_POST['login']) ? (int)fusion_get_userdata('user_id') : str_replace('.', '-', USER_IP);
    }

    /**
     * @param bool|FALSE $value
     */
    public function debug($value = FALSE) {
        self::$debug = $value;
    }

    /**
     * @return array
     */
    public function getInputErrors() {
        return self::$input_errors;
    }

    /**
     * Set and override default field error text
     *
     * @param $input_name
     * @param $text
     */
    public static function setErrorText($input_name, $text) {
        self::$input_error_text[$input_name] = $text;
    }

    /**
     * Fetches the latest error text of this input
     * Important! Ensure your applications do not refresh screen for this error to show.
     * Usage \defender::safe(); for conditional redirect.
     *
     * @param $input_name
     *
     * @return null
     */
    public static function getErrorText($input_name) {
        if (self::inputHasError($input_name)) {
            return isset(self::$input_error_text[$input_name]) ? self::$input_error_text[$input_name] : NULL;
        }

        return NULL;
    }

    public static function inputHasError($input_name) {
        if (isset(self::$input_errors[$input_name])) {
            return TRUE;
        }

        return FALSE;
    }

    public static function get_inputError() {
        return self::$input_errors;
    }

    /**
     * Sanitize
     *
     * @param            $value
     * @param string     $default
     * @param bool|FALSE $input_name
     * @param bool|FALSE $is_multiLang
     *
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
                // Make sure that the input was actually defined in code.. AND there must be a value to worth the processing power expense!
                if (isset($_SESSION['form_fields'][self::pageHash()][$input_name])) {
                    $this->field_config = $_SESSION['form_fields'][self::pageHash()][$input_name];
                    $this->field_name = $input_name;
                    $this->field_value = $value;
                    $this->field_default = $default;
                    /*
                     * These two checks won't be neccesary after we add the options in all inputs
                     * NOTE: Please don't pass 'stripinput' as callback, before we reach a callback
                     * everything is checked and sanitized already. The callback should only check
                     * if certain conditions are met then return TRUE|FALSE and not do any alterations
                     * the the value itself
                     */
                    $callback = isset($this->field_config['callback_check']) ? $this->field_config['callback_check'] : FALSE;
                    $regex = isset($this->field_config['regex']) ? $this->field_config['regex'] : FALSE;
                    $secured = $this->validate();
                    // If truly FALSE the check failed
                    if ($secured === FALSE || ($this->field_config['required'] == 1 && ($secured === FALSE || $secured == '')) ||
                        ($secured != '' && $regex && !preg_match('@^'.$regex.'$@i', $secured)) || // regex will fail for an imploded array, maybe move this check
                        (is_callable($callback) && !$callback($secured))
                    ) {
                        $this->stop();
                        // Add regex error message.
                        if ($secured != '' && $regex && !preg_match('@^'.$regex.'$@i', $secured)) {
                            $this->stop();
                            addNotice("danger", sprintf(fusion_get_locale('regex_error'), $this->field_config['title']));
                            unset($locale);
                        } else {
                            $this->setInputError($input_name);
                        }
                        // Add a notice
                        if (self::$debug) {
                            addNotice('warning', '<strong>'.$input_name.':</strong>'.($this->field_config['safemode'] ? ' is in SAFEMODE and the' : '').' check failed');
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

    /**
     * Send an Unsafe Signal acorss all PHP-Fusion Components
     * This will automatically halt on all important execution without exiting.
     *
     * @param $notice - Show custom notice message on validation errors
     */
    static function stop($notice = '') {
        if (!defined('FUSION_NULL')) {
            define('FUSION_NULL', TRUE);
            if ($notice) {
                if (function_exists('alert')) {
                    echo alert($notice);
                } else {
                    addNotice("danger", $notice);
                }
            }
            addNotice('danger', '<strong>'.fusion_get_locale('error_request', LOCALE.LOCALESET.'defender.php').'</strong>');
            if (self::$debug) {
                debug_print_backtrace();
                define("STOP_REDIRECT", TRUE);
            }
        }
    }

    /*
     * Highlight the form field as error
     */
    public static function setInputError($input_name) {
        self::$input_errors[$input_name] = TRUE;
    }

}

/**
 * Verify and Sanitize Inputs
 *
 * @param            $value
 * @param string     $default
 * @param bool|FALSE $input_name
 * @param bool|FALSE $is_multiLang
 *
 * @return mixed
 */
function form_sanitizer($value, $default = "", $input_name = FALSE, $is_multiLang = FALSE) {
    return defender::getInstance()->form_sanitizer($value, $default, $input_name, $is_multiLang);
}

require(dirname(__FILE__).'/defender/validation.php');
require(dirname(__FILE__).'/defender/token.php');
require(dirname(__FILE__).'/defender/mimecheck.php');