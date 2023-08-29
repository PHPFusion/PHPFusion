<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.30.00.upgrade.inc
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$locale = fusion_get_locale( '', LOCALE . LOCALESET . 'setup.php' );
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version' => '9.30.00'
];

$insert_settings_tbl = [
    'username_display' => "1",
    'password_length'  => "8",
    'password_char'    => "1",
    'password_num'     => "1",
    'password_case'    => "1",
];

if (!column_exists( DB_USERS, 'user_firstname', FALSE )) {
    $inf_altertable[] = DB_USERS . " ADD user_firstname VARCHAR(50) NOT NULL DEFAULT '' AFTER user_name";
    $inf_altertable[] = DB_USERS . " ADD user_lastname VARCHAR(50) NOT NULL DEFAULT '' AFTER user_firstname";
    $inf_altertable[] = DB_USERS . " ADD user_addname VARCHAR(50) NOT NULL DEFAULT '' AFTER user_lastname";
    $inf_altertable[] = DB_USERS . " ADD user_phone VARCHAR(50) NOT NULL DEFAULT '' AFTER user_email";
    $inf_altertable[] = DB_USERS . " ADD user_hide_phone TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER user_hide_email";
}
