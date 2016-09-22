<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /news/upgrade/1.0.5.php
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

$check_array = [
    // Add support for keywords in news items
    'news_keywords' => "ADD news_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER news_extended",
    // Add multilang support
    'news_cat_parent' => "ADD news_cat_parent VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER news_allow_ratings",
    // News gallery
    'news_full_default' => "ADD news_full_default VARCHAR(15) MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER news_image_align",
    'news_front_default' => "ADD news_front_default VARCHAR(15) MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER news_full_default",
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_NEWS, $key)) {
        $inf_altertable[] = DB_NEWS.$value;
    }
}

$check_array = [
    // Add support of hierarchy to News
    'news_cat_parent' => " ADD news_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER news_cat_id",
    // Add multilang support
    'news_cat_language' => " ADD news_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER news_cat_image",
    'news_image_align' => " ADD news_image_align VARCHAR(15) NOT NULL DEFAULT '' AFTER news_cat_sticky",
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_NEWS_CATS, $key)) {
        $inf_altertable[] = DB_NEWS_CATS.$value;
    }
}

// Change to new user group levels
if (column_exists(DB_NEWS, 'news_visibility')) {
    $inf_altertable[] = DB_NEWS." CHANGE news_visibility news_visibility CHAR(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'";
}

// Update new access levels for news access
$result = dbquery("SELECT news_id, news_visibility FROM ".DB_NEWS);
if (dbrows($result) > 0) {
    while ($data = dbarray($result)) {
        if ($data['news_visibility']) {
            $inf_updatedbrow[] = DB_NEWS." SET news_visibility ='-".$data['news_visibility']."' WHERE news_id='".$data['news_id']."'";
        }
    }
}

// Insert new and old settings tables to Infusions table
$check_array = [
    'news_image_readmore' => 1,
    'news_image_frontpage' => 0,
    'news_thumb_ratio' => 0,
    'news_image_link' => 1,
    'news_photo_w' => 1920,
    'news_photo_h' => 1080,
    'news_thumb_w' => 600,
    'news_thumb_h' => 400,
    'news_photo_max_w' => 1920,
    'news_photo_max_h' => 1080,
    'news_photo_max_b' => 3 * 1024 * 1024,
    'news_pagination' => 15,
    'news_extended_required' => 0,
    'news_allow_submission' => 1,
    'news_allow_submission_files' => 1
];
$settings = \PHPFusion\News\NewsServer::get_news_settings();
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'news')";
    }
}
if (!db_exists(DB_NEWS_IMAGES)) {
    $inf_newtable[] = DB_NEWS_IMAGES." (
    news_image_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    news_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    news_image VARCHAR(100) NOT NULL DEFAULT '',
    news_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
    news_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
    news_image_user MEDIUMINT(9) NOT NULL DEFAULT '0',
    news_image_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (news_image_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

    // Port Photos into New Tables
    $query = "SELECT news_id, news_image, news_image_t1, news_image_t2, news_name 'news_image_user', news_datestamp 'news_image_datestamp' FROM ".DB_NEWS;
    $result = dbquery($query);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            $inf_insertdbrow[] = DB_NEWS." (news_id, news_image, news_image_t1, news_image_t2, news_image_user, news_image_datestamp) VALUES (
            '".$data['news_id']."',
            '".$data['news_image']."',
            '".$data['news_image_t1']."',
            '".$data['news_image_t2']."',
            '".$data['news_image_user']."',
            '".$data['news_image_datestamp']."'
            )";
        }
    }
}

// Drop existing columns
$check_array = [
    'news_image' => 1,
    'news_image_t1' => 1,
    'news_image_t2' => 1
];
foreach ($check_array as $key => $value) {
    if (column_exists(DB_NEWS, $key)) {
        $inf_altertable[] = DB_NEWS." DROP $key";
    }
}

// Delete rows
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='news_cats.php'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='settings_news.php'";
$inf_updatedbrow[] = DB_ADMIN." SET admin_link='../infusions/news/news_admin.php' WHERE admin_link='news.php'";