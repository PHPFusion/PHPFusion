<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: error_handling_include.php
| Author: Hans Kristian Flaatten (Starefossen)
| Co-Author: Frederick MC Chan (Chan)
| Co-Author: Takacs Akos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

error_reporting(E_ALL ^ E_STRICT);

set_error_handler("setError");

/**
 * Custom Error Handler
 *
 * @param $error_level
 * @param $error_message
 * @param $error_file
 * @param $error_line
 * @param $error_context
 */
function setError($error_level, $error_message, $error_file, $error_line, $error_context) {
    $errors = PHPFusion\Errors::getInstance();
    if (method_exists($errors, "setError")) {
        $errors->setError($error_level, $error_message, $error_file, $error_line, $error_context);
    }
}

/**
 * Return footer error notice
 *
 * @return null
 */
function showFooterErrors() {
    $errors = PHPFusion\Errors::getInstance();
    if (method_exists($errors, "showFooterErrors")) {
        return $errors->showFooterErrors();
    }

    return NULL;
}
