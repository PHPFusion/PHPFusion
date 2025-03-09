<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 1.3.0.upgrade.inc
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
$inf_version = '1.3.0';

$change_blog = [
    'blog_visibility' => " CHANGE blog_visibility blog_visibility VARCHAR(50) NOT NULL DEFAULT '0'",
];
foreach ($change_blog as $key => $value) {
    if (column_exists(DB_BLOG, $key, FALSE)) {
        $inf_altertable[] = DB_BLOG.$value;
    }
}

$settings = get_settings('blog');
$check_array = [
    'blog_submission_access' => USER_LEVEL_MEMBER
];
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'blog')";
    }
}
