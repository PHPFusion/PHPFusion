<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/google_auth/google_auth.login.inc
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
defined('IN_FUSION') || exit;
$locale = fusion_get_locale("", [INCLUDES.'login/google_auth/locale/'.LANGUAGE.'.php']);

$login_plugin_name = $locale['uf_gauth'];
$login_plugin_desc = $locale['uf_gauth_desc'];
$login_plugin_folder = "google_auth"; // the folder
$login_plugin_handler = "GoogleAuthenticator"; // the class name
$login_plugin_login = ["GoogleAuthenticator", "login_authenticate"]; // method for showing plugin
$login_plugin_connect = ["GoogleAuthenticator", "display_connector"]; // method for showing the connector -- DONE
$login_plugin_settings = ["GoogleAuthenticator", "display_settings_form"]; // method for showing settings
$login_plugin_verify = ["GoogleAuthenticator", "display_verification"]; // method for showing settings
$login_plugin_type = "2FA";
$login_plugin_version = "1.0.0";
$login_plugin_icon = "<img class='icon-sm' src='".INCLUDES."login/google_auth/images/google2fa.png' title='Facebook' alt='Facebook'/>";

$user_field_dbname = "user_gauth";
$user_field_group = 1;
$user_field_dbinfo = "VARCHAR(20) NOT NULL DEFAULT ''";
$user_field_default = '';
$user_field_options = '';
$user_field_error = '';

require_once __DIR__.'/google_auth.php';
