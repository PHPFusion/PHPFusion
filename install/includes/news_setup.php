<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/news_setup.php
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
if (isset($_POST['uninstall'])) {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."news");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."news_cats");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='NC'");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='N'");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='S8'");
	dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='news.php'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='news_cats.php'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='submit.php?stype=n'");
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."news");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."news_cats");
	$result = dbquery("CREATE TABLE ".$db_prefix."news (
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
			news_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
			PRIMARY KEY (news_id),
			KEY news_datestamp (news_datestamp),
			KEY news_reads (news_reads)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."news_cats (
			news_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			news_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			news_cat_name VARCHAR(100) NOT NULL DEFAULT '',
			news_cat_image VARCHAR(100) NOT NULL DEFAULT '',
			news_cat_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
			PRIMARY KEY (news_cat_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	// Local inserts
	$links_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_cat, link_icon, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES \n";
	$links_sql .= implode(",\n", array_map(function ($language) use($db_prefix) {
		include LOCALE.$language."/setup.php";
		$id_sql = "(SELECT link_id FROM (SELECT link_id FROM ".$db_prefix."site_links WHERE link_url = 'submissions.php' AND link_language = '".$language."' ORDER BY link_id DESC limit 1) AS t)";
		return "('".$locale['setup_3205']."', '0', '', 'news.php', '0', '2', '0', '7', '".$language."'),
				('".$locale['setup_3311']."', ".$id_sql.", '', 'submit.php?stype=n', '-101', '1', '0', '13', '".$language."')";
	}, explode('.', fusion_get_settings('enabled_languages'))));
	if(!dbquery($links_sql)) {
		$fail = TRUE;
	} else {
		$links_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_cat, link_icon, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES \n";
		$links_sql .= implode(",\n", array_map(function ($language) use($db_prefix) {
			include LOCALE.$language."/setup.php";
			$id_sql = "(SELECT link_id FROM (SELECT link_id FROM ".$db_prefix."site_links WHERE link_url = 'submit.php?stype=n' AND link_language = '".$language."' ORDER BY link_id DESC limit 1) AS t)";
			return "('".$locale['setup_3205']."', '".$id_sql."', '', 'news.php', '0', '2', '0', '1', '".$language."'),
				('".$locale['setup_3306']."', ".$id_sql.", '', 'news_cats.php', '0', '2', '0', '1', '".$language."')";
		}, explode('.', fusion_get_settings('enabled_languages'))));
	}
	
	$news_cats_sql = "INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES \n";
	$news_cats_sql .= implode(",\n", array_map(function ($language) {
		include LOCALE.$language."/setup.php";
		return "('".$locale['setup_3500']."', 'bugs.gif', '".$language."'),
				('".$locale['setup_3501']."', 'downloads.gif', '".$language."'),
				('".$locale['setup_3502']."', 'games.gif', '".$language."'),
				('".$locale['setup_3503']."', 'graphics.gif', '".$language."'),
				('".$locale['setup_3504']."', 'hardware.gif', '".$language."'),
				('".$locale['setup_3505']."', 'journal.gif', '".$language."'),
				('".$locale['setup_3506']."', 'members.gif', '".$language."'),
				('".$locale['setup_3507']."', 'mods.gif', '".$language."'),
				('".$locale['setup_3508']."', 'movies.gif', '".$language."'),
				('".$locale['setup_3509']."', 'network.gif', '".$language."'),
				('".$locale['setup_3510']."', 'news.gif', '".$language."'),
				('".$locale['setup_3511']."', 'php-fusion.gif', '".$language."'),
				('".$locale['setup_3512']."', 'security.gif', '".$language."'),
				('".$locale['setup_3513']."', 'software.gif', '".$language."'),
				('".$locale['setup_3514']."', 'themes.gif', '".$language."'),
				('".$locale['setup_3515']."', 'windows.gif', '".$language."')";
	}, explode('.', fusion_get_settings('enabled_languages'))));
	if(!dbquery($news_cats_sql)) {
		$fail = TRUE;
	}

	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('NC', 'news_cats.gif', '".$locale['setup_3017']."', 'news_cats.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('N', 'news.png', '".$locale['setup_3018']."', 'news.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S8', 'settings_news.gif', '".$locale['setup_3040']."', 'settings_news.php', '4')");
}
?>