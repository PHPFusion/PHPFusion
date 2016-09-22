<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: J.Falk (Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");
// Infusion general information
$inf_title = $locale['news']['title'];
$inf_description = $locale['news']['description'];
$inf_version = "1.3";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "news";
$inf_image = "news.png";

// Create tables
$inf_newtable[1] = DB_NEWS." (
	news_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	news_subject VARCHAR(200) NOT NULL DEFAULT '',
	news_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	news_news TEXT NOT NULL,
	news_extended TEXT NOT NULL,
	news_keywords VARCHAR(250) NOT NULL DEFAULT '',
	news_breaks CHAR(1) NOT NULL DEFAULT '',
	news_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
	news_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	news_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
	news_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
	news_visibility TINYINT(4) NOT NULL DEFAULT '0',
	news_reads INT(10) UNSIGNED NOT NULL DEFAULT '0',
	news_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	news_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	news_image_align VARCHAR(15) NOT NULL DEFAULT '',
	news_image_full_default MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    news_image_front_default MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	news_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	news_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	news_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (news_id),
	KEY news_datestamp (news_datestamp),
	KEY news_reads (news_reads)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_NEWS_IMAGES." (
    news_image_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    news_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    news_image VARCHAR(100) NOT NULL DEFAULT '',
    news_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
    news_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
    news_image_user MEDIUMINT(9) NOT NULL DEFAULT '0',
    news_image_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (news_image_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[3] = DB_NEWS_CATS." (
	news_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	news_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	news_cat_name VARCHAR(100) NOT NULL DEFAULT '',
	news_cat_image VARCHAR(100) NOT NULL DEFAULT '',
	news_cat_visibility TINYINT(4) NOT NULL DEFAULT '0',
	news_cat_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	news_cat_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	news_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (news_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_altertable[1] = DB_NEWS_CATS." ADD news_cat_visibility TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' AFTER news_cat_image";
$inf_altertable[2] = DB_NEWS_CATS." ADD news_cat_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER news_cat_visibility";
$inf_altertable[3] = DB_NEWS_CATS." ADD news_cat_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER news_cat_draft";

$inf_altertable[4] = DB_NEWS_CATS." ADD news_image_align VARCHAR(15) NOT NULL DEFAULT '' AFTER news_cat_sticky";

$inf_altertable[5] = DB_NEWS." ADD news_full_default VARCHAR(15) MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER news_image_align";
$inf_altertable[6] = DB_NEWS." ADD news_front_default VARCHAR(15) MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER news_full_default";

$inf_altertable[7] = DB_NEWS." DROP news_image";
$inf_altertable[8] = DB_NEWS." DROP news_image_t1";
$inf_altertable[9] = DB_NEWS." DROP news_image_t2";

// Position these links under Content Administration
$inf_adminpanel[] = array(
    "image" => $inf_image,
    "page" => 1,
    "rights" => "N",
    "title" => $locale['setup_3018'],
    "panel" => "news_admin.php",
);

// Insert settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_image_readmore', '1', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_image_frontpage', '0', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_thumb_ratio', '0', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_image_link', '1', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_w', '800', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_h', '600', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_thumb_w', '400', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_thumb_h', '300', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_max_w', '1800', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_max_h', '1600', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_max_b', '500000', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_pagination', '12', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_allow_submission', '1', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_allow_submission_files', '1', 'news')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_extended_required', '0', 'news')";

// Multilanguage table for Administration
$inf_mlt[] = array(
    "title" => $locale['news']['title'],
    "rights" => "NS",
);

// always find and loop ALL languages
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
// Create a link for all installed languages
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include LOCALE.$language."/setup.php";
        // add new language records
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['setup_3205']."', 'infusions/news/news.php', '0', '2', '0', '2', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['setup_3311']."', 'submit.php?stype=n', ".USER_LEVEL_MEMBER.", '1', '0', '13', '".$language."')";

        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3500']."', 'bugs.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3501']."', 'downloads.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3502']."', 'games.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3503']."', 'graphics.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3504']."', 'hardware.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3505']."', 'journal.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3506']."', 'members.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3507']."', 'mods.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3509']."', 'network.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3510']."', 'news.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3511']."', 'php-fusion.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3512']."', 'security.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3513']."', 'software.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3514']."', 'themes.gif', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3515']."', 'windows.gif', '".$language."')";

        // drop deprecated language records
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/news/news.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=n' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_NEWS_CATS." WHERE news_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_NEWS." WHERE news_language='".$language."'";
    }
} else {
    // Additions
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3205']."', 'infusions/news/news.php', '0', '2', '0', '2', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['setup_3311']."', 'submit.php?stype=n', ".USER_LEVEL_MEMBER.", '1', '0', '13', '".LANGUAGE."')";
}

// Defuse cleanup
$inf_droptable[] = DB_NEWS;
$inf_droptable[] = DB_NEWS_CATS;
$inf_droptable[] = DB_NEWS_IMAGES;

$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='N'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='N'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='N'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='news'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/news/news.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=n'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='NS'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='N'";

if (file_exists(IMAGES_N)) {
    $inf_delfiles[] = IMAGES_N;
}
if (file_exists(IMAGES_N_T)) {
    $inf_delfiles[] = IMAGES_N_T;
}