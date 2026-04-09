<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 2.1.0.upgrade.inc
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
$inf_version = '2.1.0';

$new_settings = [
    'forum_attachmax_w' => 5048,
    'forum_attachmax_h' => 5365,
    'default_points'    => 10,
];

$settings = get_settings('forum');
foreach ($new_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'forum')";
    }
}

if (column_exists('users', 'user_reputation')) {
    dbquery("UPDATE ".DB_USERS." SET user_reputation='".$new_settings['default_points']."' WHERE user_reputation='0'");
    $inf_altertable[] = DB_USERS." CHANGE user_reputation user_reputation INT(10) UNSIGNED NOT NULL DEFAULT '0';";
}

$inf_altertable[] = DB_FORUMS." MODIFY COLUMN forum_name TEXT NOT NULL";
