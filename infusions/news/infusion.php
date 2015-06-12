<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: J.Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."setup.php";

// Infusion general information
$inf_title = $locale['news']['title'];
$inf_description = $locale['news']['description'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "news";

// Multilanguage table for Administration
$inf_mlt[1] = array(
	"title" => $locale['news']['title'], 
	"rights" => "NS",
);

// Create tables
$inf_newtable[1] = DB_NEWS." (
	news_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	news_subject VARCHAR(200) NOT NULL DEFAULT '',
	news_image VARCHAR(100) NOT NULL DEFAULT '',
	news_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
	news_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
	news_ialign VARCHAR(15) NOT NULL DEFAULT '',
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
	news_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	news_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	news_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (news_id),
	KEY news_datestamp (news_datestamp),
	KEY news_reads (news_reads)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_NEWS_CATS." (
	news_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	news_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	news_cat_name VARCHAR(100) NOT NULL DEFAULT '',
	news_cat_image VARCHAR(100) NOT NULL DEFAULT '',
	news_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (news_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Position these links under Content Administration
$inf_insertdbrow[1] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('NC', 'news_cats.gif', '".$locale['setup_3017']."', '".INFUSIONS."news/news_cats_admin.php', '1')";
$inf_insertdbrow[2] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('N', 'news.png', '".$locale['setup_3018']."', '".INFUSIONS."news/news_admin.php', '1')";

// Insert settings
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_image_readmore', '0', 'news')";
$inf_insertdbrow[4] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_image_frontpage', '0', 'news')";
$inf_insertdbrow[5] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_thumb_ratio', '0', 'news')";
$inf_insertdbrow[6] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_image_link', '1', 'news')";
$inf_insertdbrow[7] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_w', '400', 'news')";
$inf_insertdbrow[8] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_h', '300', 'news')";
$inf_insertdbrow[9] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_thumb_w', '100', 'news')";
$inf_insertdbrow[10] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_thumb_h', '100', 'news')";
$inf_insertdbrow[11] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_max_w', '1800', 'news')";
$inf_insertdbrow[12] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_max_h', '1600', 'news')";
$inf_insertdbrow[13] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_photo_max_b', '150000', 'news')";
$inf_insertdbrow[14] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('news_pagination', '12', 'news')";

$enabled_languages = explode('.', $settings['enabled_languages']);

// Create a link for all installed languages
if (!empty($enabled_languages)) {
$k = 15;
	for ($i = 0; $i < count($enabled_languages); $i++) {
		include LOCALE."".$enabled_languages[$i]."/setup.php";
		$inf_insertdbrow[$k] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3205']."', 'infusions/news/news.php', '0', '2', '0', '2', '".$enabled_languages[$i]."')";
		$k++;
	}
} else {
		$inf_insertdbrow[15] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3205']."', 'infusions/news/news.php', '0', '2', '0', '2', '".LANGUAGE."')";
}

// Reset locale
include LOCALE.LOCALESET."setup.php";

// Check the table and run category creation for each installed language of it is empty.
if (db_exists(DB_NEWS_CATS)) {
	$result = dbquery("SELECT * FROM ".DB_NEWS_CATS."");
	if (dbrows($result) == 0) {
		for ($i=0;$i<sizeof($enabled_languages);$i++) {
		include LOCALE."".$enabled_languages[$i]."/setup.php";
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3500']."', 'bugs.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3501']."', 'downloads.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3502']."', 'games.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3503']."', 'graphics.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3504']."', 'hardware.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3505']."', 'journal.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3506']."', 'members.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3507']."', 'mods.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3508']."', 'movies.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3509']."', 'network.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3510']."', 'news.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3511']."', 'php-fusion.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3512']."', 'security.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3513']."', 'software.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3514']."', 'themes.gif', '".$enabled_languages[$i]."')");
			dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3515']."', 'windows.gif', '".$enabled_languages[$i]."')");
		}
	}
}

// Defuse cleanup
$inf_droptable[1] = DB_NEWS;
$inf_droptable[2] = DB_NEWS_CATS;
$inf_deldbrow[2] = DB_ADMIN." WHERE admin_rights='NC'";
$inf_deldbrow[3] = DB_ADMIN." WHERE admin_rights='N'";
$inf_deldbrow[4] = DB_SETTINGS_INF." WHERE settings_inf='news'";
$inf_deldbrow[5] = DB_SITE_LINKS." WHERE link_url='infusions/news/news.php'";
$inf_deldbrow[6] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=n'";
$inf_deldbrow[7] = DB_LANGUAGE_TABLES." WHERE mlt_rights='NS'";
