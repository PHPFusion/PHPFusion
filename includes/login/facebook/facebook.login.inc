<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: facebook/facebook.login.inc
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
$locale = fusion_get_locale("", [INCLUDES.'login/facebook/locale/'.LANGUAGE.'.php']);
$login_plugin_name = $locale['uf_fb_connect'];
$login_plugin_desc = $locale['uf_fb_connect_desc'];
$login_plugin_folder = "facebook"; // the folder
$login_plugin_handler = "Facebook"; // the class name
$login_plugin_login = ["Facebook", "display_login"]; // method for showing plugin
$login_plugin_connect = ["Facebook", "display_connector"]; // method for showing the connector
$login_plugin_settings = ["Facebook", "display_settings_form"]; // method for showing settings
$login_plugin_verify = ["Facebook", "display_verification"]; // method for showing settings
$login_plugin_type = "LGA";
$login_plugin_version = "1.0.0";
$login_plugin_icon = "<img class='icon-sm' src='".INCLUDES."login/facebook/images/facebook.svg' title='Facebook' alt='Facebook'/>";
/**
 * This is a string according to facebook.
 * https://stackoverflow.com/questions/7138119/saving-facebook-id-as-int-or-varchar
 *
 * Since we do not use it for comparative result searching nor need to index it, it is safe to put it as a varchar.
 */
$user_field_dbname = "user_facebook";
$user_field_group = 1;
$user_field_dbinfo = "VARCHAR(100) NOT NULL DEFAULT ''";
$user_field_default = '';
$user_field_options = '';
$user_field_error = '';

require_once __DIR__.'/facebook.php';