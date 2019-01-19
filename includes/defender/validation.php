<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/defender/validation.php
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

abstract class Validation {
    protected static $inputName = '';
    protected static $inputValue;
    protected static $inputDefault = '';
    protected static $isMultiLang = '';
    protected static $inputConfig = [];
    protected static $validate_instance = NULL;
    protected static $validate_method = NULL;

    protected static $validation_rules_assigned = [
        'color'       => ['text', 'verify_text'],
        'dropdown'    => ['text', 'verify_text'],
        'text'        => ['text', 'verify_text'],
        'textarea'    => ['text', 'verify_text'],
        'textbox'     => ['text', 'verify_text'],
        'checkbox'    => ['checkbox', 'verify_checked'],
        'password'    => ['text', 'verify_password'],
        'date'        => ['date', 'verify_date'],
        'timestamp'   => ['date', 'verify_date'],
        'number'      => ['number', 'verify_number'],
        'email'       => ['text', 'verify_email'],
        'address'     => ['user', 'verify_address'],
        'name'        => ['user', 'verify_name'],
        'url'         => ['uri', 'verify_url'],
        'image'       => ['upload', 'verify_image_upload'],
        'file'        => ['upload', 'verify_file_upload'],
        'document'    => ['user', 'verify_document'],
        'radio'       => ['text', 'verify_text'],
        'mediaSelect' => ['uri', 'verify_path']
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
                $class = new \ReflectionClass(strtoupper(self::$validation_rules_assigned[self::$inputConfig['type']][0]));
                self::$validate_instance[self::$inputName] = $class->newInstance();
            }
        }

        if (self::$validate_instance[self::$inputName] !== NULL) {
            $object = self::$validate_instance[self::$inputName];
            $method = self::$validation_rules_assigned[self::$inputConfig['type']][1];
            if (is_callable([$object, $method])) {
                return $object->$method();
            } else {
                \defender::stop();
                $locale['type_unset'] = '%s: has no type set of %s'; // to be moved
                addNotice('danger', sprintf($locale['type_unset'], self::$inputName, $method));
            }
        } else {
            \defender::stop();
            $locale['type_unset'] = '%s: has no validation file'; // to be moved
            addNotice('danger', sprintf($locale['type_unset'], self::$inputName));
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
