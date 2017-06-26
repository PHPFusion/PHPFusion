<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: theme.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer: Craig, Chan
| Site: http://www.phpfusionmods.co.uk
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Load Septenary Parts
include THEME."theme_autoloader.php";

// Factoring Septenary
$septenary = PHPFusion\SeptenaryTheme::Factory();

// Declare custom codes functions here
include THEME."templates/custom_news.php";

// Definition of Constant
if (!defined("THEME_BULLET")) {
    define("THEME_BULLET", "<img src='".THEME."images/bullet.png' class='bullet'  alt='&raquo;' />");
}

/**
 * Legacy Render Page Function
 *
 * @param bool|FALSE $license
 */
function render_page($license = FALSE) {
    \PHPFusion\SeptenaryTheme::Factory()->render_page($license);
}

/**
 * Legacy openside function
 *
 * @param bool|FALSE $title
 * @param string     $state
 */
function openside($title = FALSE, $state = 'ON') {
    \PHPFusion\SeptenaryTheme::openside($title, $state);
}

/**
 * Legacy closeside function
 */
function closeside() {
    \PHPFusion\SeptenaryTheme::closeside();
}

/**
 * Legacy opentable function
 *
 * @param bool|FALSE $title
 */
function opentable($title = FALSE) {
    \PHPFusion\SeptenaryTheme::opentable($title = FALSE);
}

/**
 * Legacy closetable function
 */
function closetable() {
    \PHPFusion\SeptenaryTheme::closetable();
}

/**
 * Legacy output replacement function
 *
 * @param $output
 *
 * @return array
 */
function theme_output($output) {
    return \PHPFusion\SeptenaryTheme::theme_output($output);
}