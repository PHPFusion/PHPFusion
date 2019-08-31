<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/defender/validation/text.php
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

/**
 * Class Text
 * Validates Text Input
 */
class Text extends \Defender\Validation {

    /**
     * validate and sanitize a text
     * accepts only 50 characters + @ + 4 characters
     * returns str the sanitized input or bool FALSE
     * if safemode is set and the check fails
     */
    public static function verify_text() {

        // each configuration for text validation should have a min and max length check
        $default_length = [
            'min_length'   => 1,
            'max_length'   => '',
            'censor_words' => TRUE,
        ];

        self::$inputConfig += $default_length;

        if (is_array(self::$inputValue)) {
            $vars = [];
            foreach (self::$inputValue as $val) {
                if (self::$inputConfig['max_length']) {
                    // Input max length needs a value.
                    if (!preg_check("^([.\\s\\S]{".self::$inputConfig['min_length'].",".self::$inputConfig['max_length']."})$^", $val)) {
                        \defender::stop();
                        \defender::setInputError(self::$inputName);
                        return self::$inputDefault;
                    }
                }
                $value = stripinput(trim(preg_replace("/ +/i", " ", $val)));
                if (self::$inputConfig['censor_words']) {
                    $value = censorwords($value);
                }
                $vars[] = $value;
            }
            // set options for checking on delimiter, and default is pipe (json,serialized val)
            $delimiter = (!empty(self::$inputConfig['delimiter'])) ? self::$inputConfig['delimiter'] : "|";
            $value = implode($delimiter, $vars);
        } else {
            if (self::$inputConfig['max_length']) {
                if (!preg_check("^([.\\s\\S]{".self::$inputConfig['min_length'].",".self::$inputConfig['max_length']."})$^", self::$inputValue)) {
                    \defender::stop();
                    \defender::setInputError(self::$inputName);
                    return FALSE;
                }
            }
            $value = stripinput(trim(preg_replace("/ +/i", " ", self::$inputValue)));
            if (self::$inputConfig['censor_words']) {
                $value = censorwords($value);
            }
        }
        if (self::$inputConfig['required'] && !$value) {
            \defender::setInputError(self::$inputName);
        }
        if (self::$inputConfig['safemode'] && !preg_check("/^[-0-9A-Z_@\s]+$/i", $value)) {
            return FALSE;
        } else {
            return $value;
        }
    }

    /**
     * Checks if is a valid password
     * accepts minimum of 8 and maximum of 64 due to encrypt limit
     * returns str the input or bool FALSE if check fails
     */

    public function verify_password() {

        // add min length, add max length, add strong password into roadmaps.
        if (self::$inputConfig['required'] && !self::$inputValue) {
            \defender::stop();
            \defender::setInputError(self::$inputName);
        }
        if (preg_match("/^[0-9A-Z@!#$%&\/\(\)=\-_?+\*\.,:;\<\>`]{".self::$inputConfig['min_length'].",".self::$inputConfig['max_length']."}$/i",
            self::$inputValue)) {
            return self::$inputValue;
        }

        return FALSE;

    }


    /**
     * Checks if is a valid email address
     * accepts only 50 characters + @ + 4 characters
     * returns str the input or bool FALSE if check fails
     */
    protected function verify_email() {
        if (self::$inputConfig['required'] && !self::$inputValue) {
            \defender::stop();
            \defender::setInputError(self::$inputName);
        }
        if (preg_check("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,}+)$/i", self::$inputValue)) {
            return self::$inputValue;
        }
        return FALSE;
    }
}
