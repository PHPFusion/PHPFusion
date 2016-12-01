<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /downloads/upgrade/1.0.5.upgrade.inc
| Upgrade FAQ to Version 9.0.2 (RC5)
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
$inf_version = '1.0.5';
require_once(INCLUDES .'infusions_include.php');

$array_check = [
    // Option to use keywords in downloads
    'download_keywords' => " ADD download_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER download_description",
    // Add individual download item access
    'download_visibility' => " ADD download_visibility CHAR(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."' AFTER download_datestamp"
];
foreach ($array_check as $key => $value) {
    if (!column_exists(DB_DOWNLOADS, $key, FALSE)) {
        $inf_altertable[] = DB_DOWNLOADS.$value;
    }
}
$array_check = [
    // Add multilanguage support
    'download_cat_language' => " ADD download_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER download_cat_access",
    'download_cat_parent' => " ADD download_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER download_cat_id"
];

foreach ($array_check as $key => $value) {
    if (!column_exists(DB_DOWNLOAD_CATS, $key, FALSE)) {
        $inf_altertable[] = DB_DOWNLOAD_CATS.$value;
    }
}
// Moving access level from downloads categories to downloads and create field for subcategories
if (column_exists(DB_DOWNLOAD_CATS, 'download_cat_access', FALSE)) {
	$result = dbquery("SELECT download_cat_id, download_cat_access FROM ".DB_DOWNLOAD_CATS);
	if (dbrows($result)) {
	    while ($data = dbarray($result)) {
	        $inf_updatedbrow[] = DB_DOWNLOADS." SET download_visibility='-".$data['download_cat_access']."' WHERE download_cat='".$data['download_cat_id']."'";
	    }
	}
}

if (column_exists(DB_DOWNLOAD_CATS, 'download_cat_access', FALSE)) {
    $inf_altertable[] = DB_DOWNLOAD_CATS." DROP COLUMN download_cat_access";
}

$array_check = [
    'download_max_b' => 512000,
    'download_types' => '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z',
    'download_screen_max_b' => '150000',
    'download_screen_max_w' => '1920',
    'download_screen_max_h' => '1080',
    'download_screenshot' => 1,
    'download_stats' => 1,
    'download_thumb_max_w' => '640',
    'download_thumb_max_h' => '480',
    'download_pagination' => 15,
    'download_extended_required' => 1,
    'download_screenshot_required' => 1,
    'download_allow_submissions' => 1
];
$settings = get_settings('downloads');
foreach ($array_check as $key => $value) {
    // Clear old settings from core if they are there regardless of current state
    $inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='$key'";
    // Insert new and old settings tables to Infusions table
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'downloads')";
    }
}

// Remove old cats link and update new path for admin link
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='download_cats.php'";
$inf_updatedbrow[] = DB_ADMIN." SET `admin_image`='../infusions/downloads/download.png', `admin_link`='../infusions/downloads/downloads_admin.php' WHERE admin_link='downloads.php'";