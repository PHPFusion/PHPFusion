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

if (fusion_get_settings('error_logging_enabled') == 1) {
    error_reporting(E_ALL ^ E_STRICT);
    set_error_handler("set_error");
}

/**
 * Custom Error Handler
 *
 * @param $error_level
 * @param $error_message
 * @param $error_file
 * @param $error_line
 */
function set_error($error_level, $error_message, $error_file, $error_line) {
    $error_level = stripinput($error_level);
    $error_message = descript(stripinput($error_message));
    $error_file = stripinput($error_file);
    $error_line = stripinput($error_line);

    if (fusion_get_settings('error_logging_method') == 'database') {
        $errors = PHPFusion\Errors::getInstance();
        if (method_exists($errors, "setError")) {
            $errors->setError($error_level, $error_message, $error_file, $error_line);
        }
    } else {
        write_error($error_message, $error_file, $error_line);
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

function write_error($error_message, $error_file, $error_line) {
    $error_message = stripinput($error_message);
    $error_file = stripinput($error_file);
    $error_line = stripinput($error_line);

    $file = BASEDIR.'fusion_error_log.log';

    if (!file_exists($file)) {
        touch($file);
    }

    $error = file_get_contents($file);
    $error .= '[LONG_DATE] [ERROR_MESSAGE] in [ERROR_FILE] on line [ERROR_LINE]'.PHP_EOL;

    $error = strtr($error, [
        'LONG_DATE'       => date('d-M-Y H:i:s', time()),
        '[ERROR_MESSAGE]' => $error_message,
        '[ERROR_FILE]'    => $error_file,
        '[ERROR_LINE]'    => $error_line
    ]);

    write_file($file, $error);
}
