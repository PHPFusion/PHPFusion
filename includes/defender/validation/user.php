<?php

class User extends \Defender\Validation {

    public static function verify_name() {
        $name = self::$inputName;
        if (self::$inputConfig['required'] && !$_POST[$name][0]) {
            \defender::stop();
            \defender::setInputError($name.'-firstname');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][1]) {
            \defender::stop();
            \defender::setInputError($name.'-lastname');
        }
        if (\defender::safe()) {
            $return_value = Text::verify_text();
            return $return_value;
        }
    }

    public static function verify_address() {
        $name = self::$inputName;
        if (self::$inputConfig['required'] && !$_POST[$name][0]) {
            \defender::stop();
            \defender::setInputError($name.'-street-1');

        }
        if (self::$inputConfig['required'] && !$_POST[$name][2]) {
            \defender::stop();
            \defender::setInputError($name.'-country');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][3]) {
            \defender::stop();
            \defender::setInputError($name.'-region');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][4]) {
            \defender::stop();
            \defender::setInputError($name.'-city');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][5]) {
            \defender::stop();
            \defender::setInputError($name.'-postcode');
        }
        if (\defender::safe()) {
            $return_value = Text::verify_text();
            return $return_value;
        }
    }

    public function verify_document() {
        $name = self::$inputName;
        if (self::$inputConfig['required'] && !$_POST[$name][0]) {
            \defender::stop();
            \defender::setInputError($name.'-doc-1');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][1]) {
            \defender::stop();
            \defender::setInputError($name.'-doc-2');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][2]) {
            \defender::stop();
            \defender::setInputError($name.'-doc-3');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][3]) {
            \defender::stop();
            \defender::setInputError($name.'-doc-4');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][4]) {
            \defender::stop();
            \defender::setInputError($name.'-doc-5');
        }
        if (\defender::safe()) {
            $return_value = Text::verify_text();
            return $return_value;
        }
    }

}