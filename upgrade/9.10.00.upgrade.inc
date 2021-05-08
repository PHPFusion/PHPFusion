<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.10.00.upgrade.inc
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
$locale = fusion_get_locale('', LOCALE.LOCALESET.'setup.php');
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version' => '9.10.00'
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

$insert_settings_tbl = [
    'recaptcha_score' => '0.5'
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
    }
}

$delete_settings_tbl = [
    'logoposition_xs',
    'logoposition_sm',
    'logoposition_md',
    'logoposition_lg',
    'subheaderdate',
    'bootstrap',
    'entypo',
    'fontawesome'
];

foreach ($delete_settings_tbl as $value) {
    $inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='$value'";
}

// Rename PHP Info to Server Info
$inf_updatedbrow[] = DB_ADMIN." SET admin_image='serverinfo.png', admin_title='".$locale['setup_3021']."', admin_link='serverinfo.php' WHERE admin_link='phpinfo.php'";

/**
 * Delete all unused files
 */
$deleted_files = [
    THEMES.'templates/render_functions.php',
    LOCALE.'English/admin/banners.php',
    ADMIN.'updateuser.php',
    ADMIN.'images/phpinfo.png',
    ADMIN.'phpinfo.php',
    LOCALE.'English/admin/phpinfo.php',
    INCLUDES.'elFinder/themes/manifests/'
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
