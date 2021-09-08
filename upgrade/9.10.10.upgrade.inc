<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.10.10.upgrade.inc
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
$locale = fusion_get_locale('', LOCALE.LOCALESET.'setup.php');
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version' => '9.10.10',
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

/**
 * Delete all unused files
 */
$deleted_files = [
];

foreach ($deleted_files as $file) {
    if (is_file($file)) {
        @unlink($file);
    } else {
        if (is_dir($file)) {
            rrmdir($file);
        }
    }
}
