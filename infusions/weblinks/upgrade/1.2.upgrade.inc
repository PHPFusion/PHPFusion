<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/upgrade/1.2.upgrade.inc
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

$check_wbl12 = [
    'weblink_language'   => " ADD weblink_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER weblink_count",
    'weblink_visibility' => " ADD weblink_visibility CHAR(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."' AFTER weblink_datestamp",
    'weblink_status'     => " ADD weblink_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER weblink_visibility",
];

foreach ($check_wbl12 as $key => $value) {
    if (!column_exists(DB_WEBLINKS, $key, FALSE)) {
        $inf_altertable[] = DB_WEBLINKS.$value;
    }
}

$check_wblC12 = [
    'weblink_cat_parent'     => " ADD weblink_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER weblink_cat_id",
    'weblink_cat_language'   => " ADD weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER weblink_cat_sorting",
    'weblink_cat_visibility' => " ADD weblink_cat_visibility TINYINT(4) NOT NULL DEFAULT '0' AFTER weblink_cat_description",
    'weblink_cat_status'     => " ADD weblink_cat_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER weblink_cat_description",
];

foreach ($check_wblC12 as $key => $value) {
    if (!column_exists(DB_WEBLINK_CATS, $key, FALSE)) {
        $inf_altertable[] = DB_WEBLINK_CATS.$value;
    }
}

$inf_updatedbrow[] = DB_WEBLINKS." SET weblink_status='1'";
$inf_updatedbrow[] = DB_WEBLINK_CATS." SET weblink_cat_status='1'";

// Added weblink_cat_visibility by now.
// Need to check and add weblink category access and copy value to new weblink_cat_visibility

$result = dbquery("SELECT * FROM ".DB_WEBLINK_CATS." ORDER by weblink_cat_id ASC");
if (dbrows($result)) {
    while ($wcData = dbarray($result)) {
        if (!empty($wcData['weblink_cat_access'])) {
            // copy the data over
            switch ($wcData['weblink_cat_access']) {
                case 101:
                    $wcData['weblink_cat_visibility'] = USER_LEVEL_MEMBER;
                    break;
                case 102:
                    $wcData['weblink_cat_visibility'] = USER_LEVEL_ADMIN;
                    break;
                case 103:
                    $wcData['weblink_cat_visibility'] = USER_LEVEL_SUPER_ADMIN;
                    break;
                default:
                    $wcData['weblink_cat_visibility'] = $wcData['weblink_cat_access'];
            }
            // Update category visibility
            $inf_updatedbrow[] = DB_WEBLINK_CATS." SET weblink_cat_visibility ='".$wcData['weblink_cat_visibility']."' WHERE weblink_cat_id='".$wcData['weblink_cat_id']."'";
            // Update entries visibility
            $inf_updatedbrow[] = DB_WEBLINKS." SET weblink_visibility='".$wcData['weblink_cat_visibility']."' WHERE weblink_cat='".$wcData['weblink_cat_id']."'";
        }
    }
}

$inf_dropcol[] = ['table' => DB_WEBLINK_CATS, 'column' => 'weblink_cat_access'];

// Insert new and old settings tables to Infusions table
$settings = get_settings('weblinks');
$check_array = [
    'links_per_page'          => 15,
    'links_extended_required' => 1,
    'links_allow_submission'  => 1
];
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'weblinks')";
    }
}

// Remove old cats link and update new path for admin link
$inf_deldbrow[] = DB_PREFIX."admin WHERE admin_link='weblink_cats.php'";

$inf_updatedbrow[] = DB_PREFIX."admin SET admin_image='../infusions/weblinks/weblink.svg', admin_link='../infusions/weblinks/weblinks_admin.php' WHERE admin_link='weblinks.php'";

$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='weblinks.php'";
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        // add new language records
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3307']."', 'infusions/weblinks/weblinks.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3310']."', 'submit.php?stype=l', ".USER_LEVEL_MEMBER.", '1', '0', '26', '1', '".$language."')";
        // drop deprecated language records
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/weblinks/weblinks.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=l' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_WEBLINKS." WHERE weblink_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_WEBLINK_CATS." WHERE weblink_cat_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3307']."', 'infusions/weblinks/weblinks.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3310']."', 'submit.php?stype=l', ".USER_LEVEL_MEMBER.", '1', '0', '26', '1', '".LANGUAGE."')";
}
