<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: includes/defender/validation.php
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
namespace Defender;

use ReflectionClass;
use ReflectionException;

abstract class Validation {

    protected static $inputName = '';

    protected static $inputValue;

    protected static $inputDefault = '';

    protected static $isMultiLang = '';

    protected static $inputConfig = [];

    protected static $validate_instance = NULL;

    protected static $validate_method = NULL;

    protected static $validation_rules_assigned = [
        'color'       => ['text', 'verifyText'],
        'dropdown'    => ['text', 'verifyText'],
        'text'        => ['text', 'verifyText'],
        'textarea'    => ['text', 'verifyText'],
        'textbox'     => ['text', 'verifyText'],
        'checkbox'    => ['checkbox', 'verifyChecked'],
        'password'    => ['text', 'verifyPassword'],
        'date'        => ['date', 'verifyDate'],
        'timestamp'   => ['date', 'verifyDate'],
        'number'      => ['number', 'verifyNumber'],
        'email'       => ['text', 'verifyEmail'],
        'address'     => ['user', 'verifyAddress'],
        'name'        => ['user', 'verifyName'],
        'url'         => ['uri', 'verifyURL'],
        'image'       => ['upload', 'verifyImageUpload'],
        'file'        => ['upload', 'verifyFileUpload'],
        'document'    => ['user', 'verifyDocument'],
        'radio'       => ['text', 'verifyText'],
        'mediaSelect' => ['uri', 'verifyPath'],
        'contact'     => ['user', 'verifyContact']
    ];

    public static function inputName($value = NULL) {
        self::$inputName = $value;
    }

    public static function inputConfig($value = NULL) {
        self::$inputConfig = $value;
    }

    public static function inputValue($value = NULL) {
        self::$inputValue = $value;
    }

    public static function inputDefault($value = NULL) {
        self::$inputDefault = $value;
    }

    public static function isMultilang($value = NULL) {
        self::$isMultiLang = $value;
    }

    public static function getValidated() {
        if (!isset(self::$validate_instance[self::$inputName])) {
            if (class_exists(strtoupper(self::$validation_rules_assigned[self::$inputConfig['type']][0]))) {
                try {
                    $class = new ReflectionClass(strtoupper(self::$validation_rules_assigned[self::$inputConfig['type']][0]));
                    self::$validate_instance[self::$inputName] = $class->newInstance();
                } catch (ReflectionException $e) {
                    set_error(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
                }
            }
        }

        if (isset(self::$validate_instance[self::$inputName]) && self::$validate_instance[self::$inputName] !== NULL) {
            
            $object = self::$validate_instance[self::$inputName];
            
            $method = self::$validation_rules_assigned[self::$inputConfig['type']][1];

            if (is_callable([$object, $method])) {

                return $object->$method();
                
            } else {
                $locale['type_unset'] = '%s: has no type set of %s'; // to be moved
                fusion_stop(sprintf($locale['type_unset'], self::$inputName));
            }
        } else {
            $locale['type_unset'] = '%s: has no validation file'; // to be moved
            fusion_stop(sprintf($locale['type_unset'], self::$inputName));
        }

        return FALSE;
    }

}

require_once(__DIR__.'/validation/checkbox.php');
require_once(__DIR__.'/validation/date.php');
require_once(__DIR__.'/validation/number.php');
require_once(__DIR__.'/validation/text.php');
require_once(__DIR__.'/validation/upload.php');
require_once(__DIR__.'/validation/uri.php');
require_once(__DIR__.'/validation/user.php');
require_once(__DIR__.'/validation/contact.php');
