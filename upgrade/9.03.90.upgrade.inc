<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.03.90.upgrade.inc
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
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version' => '9.03.90'
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

$insert_settings_tbl = [
    'number_delimiter'      => '.',
    'thousands_separator'   => ',',
    'error_logging_enabled' => 1,
    'error_logging_method'  => 'database'
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
    }
}

// PM fix
if (db_exists(DB_PREFIX.'messages')) {
    if (!column_exists(DB_MESSAGES, 'message_user')) {
        $inf_altertable[] = DB_MESSAGES." ADD message_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER message_from";
    }

    $inf_updatedbrow[] = DB_MESSAGES." SET message_user=message_to";
}
