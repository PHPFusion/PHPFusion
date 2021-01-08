<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: includes/defender/validation/user.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
 * Class User
 * Validates User type Input
 */
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
            return Text::verify_text();
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
            return Text::verify_text();
        }

        return NULL;
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
            return Text::verify_text();
        }

        return NULL;
    }
}
