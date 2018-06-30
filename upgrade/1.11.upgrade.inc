<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/upgrade/1.11.upgrade.inc
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
$inf_version = '1.11';
require_once INCLUDES."infusions_include.php";

$check_array = [
    // Add support for keywords in news items
    'news_keywords'            => " ADD news_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER news_extended",
    'news_language'            => " ADD news_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER news_allow_ratings",
    'news_image_align'         => " ADD news_image_align VARCHAR(15) NOT NULL DEFAULT '' AFTER news_sticky",
    'news_image_full_default'  => " ADD news_image_full_default MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER news_image_align",
    'news_image_front_default' => " ADD news_image_front_default MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER news_image_full_default",
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_NEWS, $key)) {
        $alter_table = DB_NEWS.$value;
        dbquery("ALTER TABLE ".$alter_table);
    }
}

$check_array = [
    // Add support of hierarchy to News
    'news_cat_parent'     => " ADD news_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER news_cat_id",
    'news_cat_visibility' => " ADD news_cat_visibility TINYINT(4) NOT NULL DEFAULT '0' AFTER news_cat_image",
    'news_cat_draft'      => " ADD news_cat_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER news_cat_visibility",
    'news_cat_sticky'     => " ADD news_cat_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER news_cat_draft",
    'news_cat_language'   => " ADD news_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER news_cat_sticky",
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_NEWS_CATS, $key)) {
        $inf_altertable[] = DB_NEWS_CATS.$value;
    }
}

// Change to new user group levels
if (column_exists(DB_NEWS, 'news_visibility')) {
    $inf_altertable[] = DB_NEWS." CHANGE news_visibility news_visibility TINYINT(4) NOT NULL DEFAULT '0'";
}

// Update new access levels for news access
$result = dbquery("SELECT news_id, news_visibility FROM ".DB_NEWS);
if (dbrows($result) > 0) {
    while ($data = dbarray($result)) {
        $inf_updatedbrow[] = DB_NEWS." SET news_visibility ='-".$data['news_visibility']."' WHERE news_id='".$data['news_id']."' AND news_visibility != 0";
    }
}

// Insert new and old settings tables to Infusions table
$check_array = [
    'news_image_readmore'         => 1,
    'news_image_frontpage'        => 0,
    'news_thumb_ratio'            => 0,
    'news_image_link'             => 1,
    'news_photo_w'                => 1920,
    'news_photo_h'                => 1080,
    'news_thumb_w'                => 600,
    'news_thumb_h'                => 400,
    'news_photo_max_w'            => 1920,
    'news_photo_max_h'            => 1080,
    'news_photo_max_b'            => 3 * 1024 * 1024,
    'news_pagination'             => 15,
    'news_extended_required'      => 0,
    'news_allow_submission'       => 1,
    'news_allow_submission_files' => 1,
    'news_file_types'             => '.pdf,.gif,.jpg,.png,.svg,.zip,.rar,.tar,.bz2,.7z',
];
$settings = get_settings('news');

foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'news')";
    }
}

if (!db_exists(DB_NEWS_IMAGES)) {
    $create_table = DB_NEWS_IMAGES." (
    news_image_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    news_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    news_image VARCHAR(100) NOT NULL DEFAULT '',
    news_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
    news_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
    news_image_user MEDIUMINT(9) NOT NULL DEFAULT '0',
    news_image_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (news_image_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
    dbquery("CREATE TABLE ".$create_table);
}

// Port Photos into New Tables
$query = "SELECT news_id, news_image, news_image_t1, news_image_t2, news_name 'news_image_user', news_datestamp 'news_image_datestamp' FROM ".DB_NEWS." WHERE news_image !='' ORDER BY news_id ASC";
$result = dbquery($query);
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        if (!empty($data['news_image'])) {
            $id = dbquery_insert(DB_NEWS_IMAGES, $data, 'save', ['keep_session' => TRUE]);
            $cdata = [
                'news_id'                  => $data['news_id'],
                'news_image_align'         => 'news-img-center',
                'news_image_full_default'  => $id,
                'news_image_front_default' => $id,
            ];
            dbquery_insert(DB_NEWS, $cdata, 'update', ['keep_session' => TRUE]);
            // delay the process by 0.3 seconds to prevent redirection before process completes
            //sleep(0.3);
        }
    }
}
include_once BASEDIR.'upgrade/upgrade_functions_include.php';

if (file_exists(IMAGES."news/thumbs/")) {
    $attachment_files = makefilelist(IMAGES."news/thumbs/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(IMAGES."news/thumbs/".$file, INFUSIONS."news/thumbs/".$file);
    }
    // Remove the whole old dir including rouge files
}

if (file_exists(IMAGES."news/")) {
    $attachment_files = makefilelist(IMAGES."news/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(IMAGES."news/".$file, INFUSIONS."news/images/".$file);
    }
    // Remove the whole old dir including rouge files
}

if (file_exists(IMAGES."news_cats/")) {
    $attachment_files = makefilelist(IMAGES."news_cats/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(IMAGES."news_cats/".$file, INFUSIONS."news/news_cats/".$file);
    }
    // Remove the whole old dir including rouge files
}

rrmdir(IMAGES.'news/thumbs');
rrmdir(IMAGES.'news');
rrmdir(IMAGES.'news_cats');

// Drop existing columns
$check_array = [
    'news_image'    => 1,
    'news_image_t1' => 1,
    'news_image_t2' => 1,
    'news_ialign'   => 1,
];
foreach ($check_array as $key => $value) {
    if (column_exists(DB_NEWS, $key)) {
        $inf_altertable[] = DB_NEWS." DROP $key";
    }
}
if (file_exists(IMAGES_N)) {
    $inf_delfiles[] = IMAGES_N;
}
if (file_exists(IMAGES_N_T)) {
    $inf_delfiles[] = IMAGES_N_T;
}

// Delete rows
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='news_cats.php'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='settings_news.php'";

$inf_updatedbrow[] = DB_ADMIN." SET admin_image='../infusions/news/news.svg', admin_link='../infusions/news/news_admin.php' WHERE admin_link='news.php'";

/*
 * Alter news category image
 */
$result = dbquery("SELECT * FROM ".DB_NEWS_CATS);
if (dbrows($result)) {
    $arr_defaults = [
        'bugs.gif'       => 'bugs.svg',
        'downloads.gif'  => 'downloads.svg',
        'games.gif'      => 'games.svg',
        'graphics.gif'   => 'graphics.svg',
        'hardware.gif'   => 'hardware.svg',
        'journal.gif'    => 'journal.svg',
        'members.gif'    => 'members.svg',
        'mods.gif'       => 'members.svg',
        'network.gif'    => 'members.svg',
        'news.gif'       => 'news.svg',
        'php-fusion.gif' => 'php-fusion.svg',
        'security.gif'   => 'security.svg',
        'software.gif'   => 'software.svg',
        'themes.gif'     => 'themes.svg',
        'windows.gif'    => 'windows.svg',
    ];
    while ($data = dbarray($result)) {
        $data['news_cat_image'] = strtr($data['news_cat_image'], $arr_defaults);
        dbquery_insert(DB_NEWS_CATS, $data, 'update', ['keep_session' => TRUE]);
    }
}

$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='news.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='news_cats.php'";
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        // add new language records
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3205']."', 'infusions/news/news.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3311']."', 'submit.php?stype=n', ".USER_LEVEL_MEMBER.", '1', '0', '25', '1', '".$language."')";

        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3500']."', 'bugs.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3501']."', 'downloads.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3502']."', 'games.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3503']."', 'graphics.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3504']."', 'hardware.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3505']."', 'journal.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3506']."', 'members.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3507']."', 'mods.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3509']."', 'network.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3510']."', 'news.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3511']."', 'php-fusion.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3512']."', 'security.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3513']."', 'software.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3514']."', 'themes.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3515']."', 'windows.svg', '".$language."')";

        // drop deprecated language records
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/news/news.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=n' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_NEWS_CATS." WHERE news_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_NEWS." WHERE news_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3205']."', 'infusions/news/news.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3311']."', 'submit.php?stype=n', ".USER_LEVEL_MEMBER.", '1', '0', '25', '1', '".LANGUAGE."')";
}

