<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 1.1.2.upgrade.inc
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
$inf_version = '1.1.2';

$drop_settings = [
    'hidden_shouts'
];
foreach ($drop_settings as $value) {
    $inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_name='$value' AND settings_inf='shoutbox_panel'";
}

$dropcol_aaray = [
    'shout_hidden'
];

foreach ($dropcol_aaray as $key) {
    if (column_exists(DB_SHOUTBOX, $key, FALSE)) {
        $inf_dropcol[] = ['table' => DB_SHOUTBOX, 'column' => $key];
    }
}
