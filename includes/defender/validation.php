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
    protected static $inputValue = '';
    protected static $inputDefault = '';
    protected static $isMultiLang = '';
    protected static $inputConfig = '';
    protected static $validate_instance = NULL;
    protected static $validate_method = NULL;

    protected static $validation_rules_assigned = array(
        'color'     => array('text', 'verify_text'),
        'dropdown'  => array('text', 'verify_text'),
        'text'      => array('text', 'verify_text'),
        'textarea'  => array('text', 'verify_text'),
        'textbox'   => array('text', 'verify_text'),
        'checkbox'  => array('checkbox', 'verify_checked'),
        'password'  => array('text', 'verify_password'),
        'date'      => array('date', 'verify_date'),
        'timestamp' => array('date', 'verify_date'),
        'number'    => array('number', 'verify_number'),
        'email'     => array('text', 'verify_email'),
        'address'   => array('user', 'verify_address'),
        'name'      => array('user', 'verify_name'),
        'url'       => array('uri', 'verify_url'),
        'image'     => array('upload', 'verify_image_upload'),
        'file'      => array('upload', 'verify_file_upload'),
        'document'  => array('user', 'verify_document'),
        'radio'     => array('text', 'verify_text'),
        'mediaSelect' => array('uri', 'verify_path')
    );

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
            if (is_callable(array($object, $method))) {
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

require_once(dirname(__FILE__) . '/validation/checkbox.php');
require_once(dirname(__FILE__) . '/validation/date.php');
require_once(dirname(__FILE__) . '/validation/number.php');
require_once(dirname(__FILE__) . '/validation/text.php');
require_once(dirname(__FILE__) . '/validation/upload.php');
require_once(dirname(__FILE__) . '/validation/uri.php');
require_once(dirname(__FILE__) . '/validation/user.php');