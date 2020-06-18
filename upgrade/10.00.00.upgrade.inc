<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 10.00.00.upgrade.inc
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
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version'         => '10.00.00',
    'pm_email_notify' => $settings['pm_email_notify'] == 2 ? 1 : 0,
    'pm_save_sent'    => $settings['pm_save_sent'] == 2 ? 1 : 0,
    'mime_check'      => 1
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

$insert_settings_tbl = [
    'devmode'               => 0,
    'update_checker'        => 1,
    'error_logging_enabled' => 1,
    'error_logging_method'  => 'database',
    'recaptcha_score'       => '0.5',
    'number_delimiter'      => '.',
    'thousands_separator'   => ',',
    //'cache_server'          => '',
    'gateway_method'        => 2
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
    }
}

if (!column_exists('users', 'user_session')) {
    $inf_altertable[] = DB_USERS." ADD user_session VARCHAR(150) NOT NULL DEFAULT '' AFTER user_actiontime";
}

if (column_exists('users', 'user_aim')) {
    $inf_dropcol[] = ['table' => DB_USERS, 'column' => 'user_aim'];
}

if (column_exists('users', 'user_yahoo')) {
    $inf_dropcol[] = ['table' => DB_USERS, 'column' => 'user_yahoo'];
}

rrmdir(INCLUDES.'filemanager');

if (file_exists(INCLUDES.'classes/PHPFusion/Feedback/Comments.ajax.php')) {
    unlink(INCLUDES.'classes/PHPFusion/Feedback/Comments.ajax.php');
}
