<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: defender.inc
| Author : PHP-Fusion Development Team
| Version : 9.04
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
 * @param null   $key
 * @param int    $type
 * @param string $flags
 *
 * @return mixed
 */
function get($key = NULL, $type = FILTER_DEFAULT, $flags = '') {
    return filter_input(INPUT_GET, $key, $type, $flags);
}
/**
 * @param     $key
 * @param int $type
 * @param     $flags
 *
 * @return mixed
 */
function post($key, $type = FILTER_DEFAULT, $flags = '') {
    return filter_input(INPUT_POST, $key, $type, $flags);
}
/**
 * @param     $key
 * @param int $type
 *
 * @return mixed
 */
function server($key, $type = FILTER_DEFAULT) {
    return filter_input(INPUT_SERVER, $key, $type);
}
/**
 * @param     $key
 * @param int $type
 *
 * @return mixed
 */
function environment($key, $type = FILTER_DEFAULT) {
    return filter_input(INPUT_ENV, $key, $type);
}
/**
 * @param     $key
 * @param int $type
 *
 * @return mixed
 */
function cookie($key, $type = FILTER_DEFAULT) {
    return filter_input(INPUT_COOKIE, $key, $type);
}