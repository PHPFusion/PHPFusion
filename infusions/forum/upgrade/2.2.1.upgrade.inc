<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 2.2.1.upgrade.inc
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
$inf_version = '2.2.1';

$settings = get_settings('forum');
$change_forums = [
    'post_id' => " CHANGE post_id post_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT, add PRIMARY KEY (post_id)",
];
foreach ($change_forums as $key => $value) {
    if (column_exists(DB_POST_NOTIFY, $key, FALSE)) {
        $inf_altertable[] = DB_POST_NOTIFY.$value;
    }
}
