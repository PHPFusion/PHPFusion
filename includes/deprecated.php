<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: deprecated.php
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
defined('IN_FUSION') || exit;

/**
 * Current microtime as float to calculate script start/end time
 *
 * @return float
 * @deprecated since version 9.00, use microtime(TRUE) instead
 */
function get_microtime() {
    return microtime(TRUE);
}

/**
 * @param        $text
 * @param bool   $smileys
 * @param bool   $bbcode
 * @param bool   $decode
 * @param string $default_image_folder
 * @param bool   $add_line_breaks
 * @param bool   $descript
 *
 * @return string
 * @deprecated
 */
function parse_textarea($text, $smileys = TRUE, $bbcode = TRUE, $decode = TRUE, $default_image_folder = IMAGES, $add_line_breaks = FALSE, $descript = TRUE) {
    return (string)parse_text($text, $smileys, $bbcode, $decode, $default_image_folder, $add_line_breaks, $descript);
}

/**
 * @param $notices
 *
 * @return string
 * @deprecated
 */
function renderNotices($notices) {
    return render_notices($notices);
}

/**
 * @param string $key
 * @param bool   $delete
 *
 * @return array
 * @deprecated
 */
function getNotices($key = FUSION_SELF, $delete = TRUE) {
    return get_notices($key, $delete);
}

/**
 * This function will be deprecated in the coming patches.
 *
 * @param        $status
 * @param        $value
 * @param string $key
 * @param bool   $removeAfterAccess
 *
 * @deprecated
 */
function addNotice($status, $value, $key = FUSION_SELF, $removeAfterAccess = TRUE) {
    add_notice($status, $value, $key, $removeAfterAccess);
}

/**
 * Custom Error Handler
 *
 * @param $error_level
 * @param $error_message
 * @param $error_file
 * @param $error_line
 *
 * @deprecated
 */
function setError($error_level, $error_message, $error_file, $error_line) {
    set_error($error_level, $error_message, $error_file, $error_line);
}

/**
 * Format spaces and tabs in code bb tags
 *
 * @param $text
 *
 * @return string
 *
 * @deprecated
 */
function formatcode($text) {
    return format_code($text);
}
