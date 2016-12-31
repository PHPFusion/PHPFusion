<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/upgrade/1.2.upgrade.inc
| Update weblinks to Version 9.02 (RC5)
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
$inf_version = '1.2';
require_once INCLUDES."infusions_include.php";

// Upgrade 1.2
if (column_exists(DB_WEBLINK_CATS, "weblink_cat_sorting", FALSE)) {
    $inf_altertable[] = DB_WEBLINK_CATS." DROP COLUMN weblink_cat_sorting";
}

$check_wbl12 = [
    'weblink_language' => " ADD weblink_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER weblink_count",
    'weblink_status' => " ADD weblink_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER weblink_visibility",

];

foreach ($check_wbl12 as $key => $value) {
    if (!column_exists(DB_WEBLINKS, $key, FALSE)) {
        $inf_altertable[] = DB_WEBLINKS.$value;
    }
}

$check_wblC12 = [
    'weblink_cat_visibility' => " ADD weblink_cat_visibility TINYINT(4) NOT NULL DEFAULT '0' AFTER weblink_cat_description",
    'weblink_cat_status' => " ADD weblink_cat_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER weblink_cat_description",

];

foreach ($check_wblC12 as $key => $value) {
    if (!column_exists(DB_WEBLINK_CATS, $key, FALSE)) {
        $inf_altertable[] = DB_WEBLINK_CATS.$value;
    }
}

// Upgrade 1.1
// Moving access level from weblinks categories to weblinks and create field for subcategories
$check_array = [
    'weblink_visibility' => " ADD weblink_visibility CHAR(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."' AFTER weblink_datestamp",
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_WEBLINKS, $key, FALSE)) {
        $inf_altertable[] = DB_WEBLINKS.$value;
    }
}

// Set weblink visibility
if (column_exists(DB_WEBLINK_CATS, "weblink_cat_access", FALSE)) {
	$result = dbquery("SELECT weblink_cat_id, weblink_cat_access FROM ".DB_WEBLINK_CATS);
	if (dbrows($result) > 0) {
	    while ($data = dbarray($result)) {
	        $inf_updatedbrow[] = DB_WEBLINKS." SET weblink_visibility='-".$data['weblink_cat_access']."' WHERE weblink_cat='".$data['weblink_cat_id']."'";
	    }
	}
}

if (column_exists(DB_WEBLINK_CATS, "weblink_cat_access", FALSE)) {
    $inf_altertable[] = DB_WEBLINK_CATS." DROP COLUMN weblink_cat_access";
}

$check_array = [
    'weblink_cat_parent' => " ADD weblink_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER weblink_cat_id",
    // Add multilocale support
    'weblink_cat_language' => " ADD weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER weblink_cat_sorting"
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_WEBLINK_CATS, $key, FALSE)) {
        $inf_altertable[] = DB_WEBLINK_CATS.$value;
    }
}

// Insert new and old settings tables to Infusions table
$settings = get_settings('weblinks');
$check_array = [
    'links_per_page' => 15,
    'links_extended_required' => 1,
    'links_allow_submission' => 1
];
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'weblinks')";
    }
}

// Remove old cats link and update new path for admin link
$inf_deldbrow[] = DB_PREFIX."admin WHERE admin_link='weblink_cats.php'";
$inf_updatedbrow[] = DB_PREFIX."admin SET `admin_image`='../infusions/weblinks/weblink.png', `admin_link`='../infusions/weblinks/weblinks_admin.php' WHERE admin_link='weblinks.php'";
