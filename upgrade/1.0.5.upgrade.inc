<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads/upgrade/1.0.5.upgrade.inc
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
require_once INCLUDES.'infusions_include.php';

$array_check = [
    // Option to use keywords in downloads
    'download_keywords'   => " ADD download_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER download_description",
    // Add individual download item access
    'download_visibility' => " ADD download_visibility TINYINT(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."' AFTER download_datestamp"
];
foreach ($array_check as $key => $value) {
    if (!column_exists(DB_DOWNLOADS, $key, FALSE)) {
        $inf_altertable[] = DB_DOWNLOADS.$value;
    }
}
$array_check = [
    // Add multilanguage support
    'download_cat_language' => " ADD download_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER download_cat_access",
    'download_cat_parent'   => " ADD download_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER download_cat_id"
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
            $inf_updatedbrow[] = DB_DOWNLOADS." SET download_visibility='-".$data['download_cat_access']."' WHERE download_cat='".$data['download_cat_id']."' AND download_visibility != 0";
        }
    }
}

if (column_exists(DB_DOWNLOAD_CATS, 'download_cat_access', FALSE)) {
    $inf_altertable[] = DB_DOWNLOAD_CATS." DROP COLUMN download_cat_access";
}

$array_check = [
    'download_max_b'               => 512000,
    'download_types'               => '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z',
    'download_screen_max_b'        => 153600,
    'download_screen_max_w'        => '1920',
    'download_screen_max_h'        => '1080',
    'download_screenshot'          => 1,
    'download_stats'               => 1,
    'download_thumb_max_w'         => '640',
    'download_thumb_max_h'         => '480',
    'download_pagination'          => 15,
    'download_extended_required'   => 1,
    'download_screenshot_required' => 1,
    'download_allow_submission'    => 1
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
$inf_updatedbrow[] = DB_ADMIN." SET admin_image='../infusions/downloads/download.svg', admin_link='../infusions/downloads/downloads_admin.php' WHERE admin_rights='D'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='downloads.php'";
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3302']."', 'infusions/downloads/downloads.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3314']."', 'submit.php?stype=d', ".USER_LEVEL_MEMBER.", '1', '0', '22', '1', '".$language."')";
        // drop deprecated language records
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/downloads/downloads.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=d' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_DOWNLOAD_CATS." WHERE download_cat_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3302']."', 'infusions/downloads/downloads.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3314']."', 'submit.php?stype=d', ".USER_LEVEL_MEMBER.", '1', '0', '22', '1', '".LANGUAGE."')";
}

// Move download files to new infusions folder
    // Change download path to new path
$result = dbquery("SELECT download_id, download_url FROM ".DB_DOWNLOADS." WHERE download_url!=''");
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        $url = strtr(fusion_get_settings('siteurl'), [
            'http://'  => '',
            'https://' => ''
        ]);
        if (stristr($data['download_url'], $url)) {
            if (stristr($data['download_url'], 'downloads/images/')) {
                $data['download_url'] = str_replace('downloads/images/', 'infusions/downloads/images/', $data['download_url']); // change to download file path
            } else {
                $data['download_url'] = str_replace('downloads/', 'infusions/downloads/files/', $data['download_url']); // change to download file path
            }
            $inf_updatedbrow = DB_DOWNLOADS." SET (`download_url`='".$data['download_url']."') WHERE `download_id` = ".$data['download_id']." ";
        }
    }
}

if (file_exists(BASEDIR.'downloads/')) {
	include_once BASEDIR.'upgrade/upgrade_functions_include.php';
    $files = makefilelist(BASEDIR.'downloads/', ".|..|index.php", TRUE);
    if (!empty($files)) {
        foreach ($files as $filename) {
            rename(BASEDIR.'downloads/'.$filename, INFUSIONS.'downloads/files/'.$filename);
        }
    }

    if (file_exists(BASEDIR.'downloads/images/')) {
        // Images folder
        $files = makefilelist(BASEDIR.'downloads/images/', ".|..|index.php", TRUE);
        if (!empty($files)) {
            foreach ($files as $filename) {
                rename(BASEDIR.'downloads/images/'.$filename, INFUSIONS.'downloads/images/'.$filename);
            }
        }
        rrmdir(BASEDIR.'downloads/images');
    }

    if (file_exists(BASEDIR.'downloads/submissions/')) {
        // Images folder
        $files = makefilelist(BASEDIR.'downloads/submissions/', ".|..|index.php", TRUE);
        if (!empty($files)) {
            foreach ($files as $filename) {
                rename(BASEDIR.'downloads/submissions/'.$filename, INFUSIONS.'downloads/submissions/'.$filename);
            }
            rrmdir(BASEDIR.'downloads/submissions');
        }
        if (file_exists(BASEDIR.'downloads/submissions/images/')) {
            // Images folder
            $files = makefilelist(BASEDIR.'downloads/submissions/images/', ".|..|index.php", TRUE);
            if (!empty($files)) {
                foreach ($files as $filename) {
                    rename(BASEDIR.'downloads/submissions/images/'.$filename, INFUSIONS.'downloads/submissions/images/'.$filename);
                }
            }
            rrmdir(BASEDIR.'downloads/submissions/images');
        }
    }

    rrmdir(BASEDIR.'downloads');
}

