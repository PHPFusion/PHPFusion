<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: includes/defender/validation/user.php
| Author: Core Development Team
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
class User extends \Defender\Validation
{
    public static function verifyName()
    {
        $name = self::$inputName;

        if (self::$inputConfig['required'] && !$_POST[$name][0]) {
            fusion_stop();
            \Defender::setInputError($name.'-firstname');
        }
        
        if (self::$inputConfig['required'] && !$_POST[$name][1]) {
            fusion_stop();
            \Defender::setInputError($name.'-lastname');
        }

        if (fusion_safe()) {
            return Text::verifyText();
        }

        return null;
    }

    /**
     * Validate Address type input
     *
     * @return void
     */
    public static function verifyAddress()
    {
        $name = self::$inputName;

        if (self::$inputConfig['required'] && !$_POST[$name][0]) {
            fusion_stop();
            \Defender::setInputError($name.'_street1');
        }

        if (self::$inputConfig['required'] && !$_POST[$name][2]) {
            fusion_stop();
            \Defender::setInputError($name.'-country');
        }

        if (self::$inputConfig['required'] && !$_POST[$name][3]) {
            fusion_stop();
            \Defender::setInputError($name.'-region');
        }

        if (self::$inputConfig['required'] && !$_POST[$name][4]) {
            fusion_stop();
            \Defender::setInputError($name.'-city');
        }

        if (self::$inputConfig['required'] && !$_POST[$name][5]) {
            fusion_stop();
            \Defender::setInputError($name.'-postcode');
        }

        if (fusion_safe()) {
            return Text::verifyText();
        }

        return null;
    }

    /**
     * Validate contact information.
     *
     * @return void
     */
    public static function verifyContact()
    {

        $name = self::$inputName;        

        if (self::$inputConfig['required'] && !$_POST[$name][0]) {
            fusion_stop();
            Defender::setInputError($name);            
        }

        if (self::$inputConfig['required'] && !$_POST[$name][1]) {
            fusion_stop();
            Defender::setInputError($name);            
        }

        if (!empty($_POST[$name][0]) && !empty($_POST[$name][1])) {
            if (!in_array($_POST[$name][0],calling_codes())) {
     
                fusion_stop();
                Defender::setInputError($name);
            }            
        }

        if (fusion_safe()) {                          
            return Text::verifyText();        
        }

        return '';
    }



    public function verifyDocument()
    {
        $name = self::$inputName;
        if (self::$inputConfig['required'] && !$_POST[$name][0]) {
            fusion_stop();
            \Defender::setInputError($name.'-doc-1');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][1]) {
            fusion_stop();
            \Defender::setInputError($name.'-doc-2');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][2]) {
            fusion_stop();
            \Defender::setInputError($name.'-doc-3');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][3]) {
            fusion_stop();
            \Defender::setInputError($name.'-doc-4');
        }
        if (self::$inputConfig['required'] && !$_POST[$name][4]) {
            fusion_stop();
            \Defender::setInputError($name.'-doc-5');
        }
        if (fusion_safe()) {
            return Text::verifyText();
        }

        return null;
    }
}
