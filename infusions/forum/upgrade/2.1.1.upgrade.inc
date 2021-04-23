<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 2.1.1.upgrade.inc
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$inf_version = '2.1.1';

$check_ranks_array = [
    'rank_color' => " ADD rank_color VARCHAR(20) NOT NULL DEFAULT '' AFTER rank_type",
    'rank_icon'  => " ADD rank_icon VARCHAR(50) NOT NULL DEFAULT '' AFTER rank_color",
];

foreach ($check_ranks_array as $key => $value) {
    if (!column_exists(DB_FORUM_RANKS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUM_RANKS.$value;
    }
}

$check_tags_array = [
    'tag_icon' => " ADD tag_icon VARCHAR(50) NOT NULL DEFAULT '' AFTER tag_color"
];

foreach ($check_tags_array as $key => $value) {
    if (!column_exists(DB_FORUM_TAGS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUM_TAGS.$value;
    }
}
