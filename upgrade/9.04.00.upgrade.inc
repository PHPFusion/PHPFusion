<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 9.04.00.upgrade.inc
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
    'version'         => '9.04.00',
    'pm_email_notify' => $settings['pm_email_notify'] == 2 ? 1 : 0,
    'pm_save_sent'    => $settings['pm_save_sent'] == 2 ? 1 : 0
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

if (db_exists(DB_PREFIX.'login_connectors')) {
    $inf_newtable[] = DB_LOGIN." (
        login_name VARCHAR(100) NOT NULL DEFAULT '0',
        login_type VARCHAR(10) NOT NULL DEFAULT '0',
        login_status TINYINT(1) NOT NULL DEFAULT '0',
        login_settings TEXT NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
}
