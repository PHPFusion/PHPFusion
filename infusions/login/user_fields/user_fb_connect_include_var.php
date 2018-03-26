<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/user_fields/user_gauth_include_var.php
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

$user_field_api_version = "1.0.0";
$user_field_name = $locale['uf_fb_connect'];
$user_field_desc = $locale['uf_fb_connect_desc'];
$user_field_dbname = "user_facebook_uid";
$user_field_group = 1;
$user_field_dbinfo = "MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0'";
$user_field_default = '';
$user_field_options = '';
$user_field_error = '';

// add new parameter for reader
$user_field_login = array("Facebook_Connect", "display_login");
$user_field_auth = array("Facebook_Connect", "login_authenticate");
$user_field_auth_type = "LGA";
$user_field_settings = array("Facebook_Connect", "display_settings_form");
// Require new API to store settings.
// What is our APP ID?
// Or anything we required? Yes we need at least an APP ID
// ok, lets start

require_once __DIR__.'/facebook_connect/facebook_connect.php';