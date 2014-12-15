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
			news_visibility TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
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
	$enabled_languages = explode('.', $settings['enabled_languages']);
	for ($i = 0; $i < sizeof($enabled_languages); $i++) {
		include LOCALE.$enabled_languages[$i]."/setup.php";
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['180']."', 'bugs.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['181']."', 'downloads.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['182']."', 'games.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['183']."', 'graphics.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['184']."', 'hardware.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['185']."', 'journal.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['186']."', 'members.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['187']."', 'mods.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['188']."', 'movies.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['189']."', 'network.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['190']."', 'news.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['191']."', 'php-fusion.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['192']."', 'security.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['193']."', 'software.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['194']."', 'themes.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		$result = dbquery("INSERT INTO ".$db_prefix."news_cats (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['195']."', 'windows.gif', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		// links
		$result = dbquery("INSERT INTO ".$db_prefix."site_links (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['136']."', 'news_cats.php', '0', '2', '0', '7', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
		// submits
		$result = dbquery("INSERT INTO ".$db_prefix."site_links (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['141']."', 'submit.php?stype=n', '101', '1', '0', '13', '".$enabled_languages[$i]."')");
		if (!$result) $fail = TRUE;
	}

	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('NC', 'news_cats.gif', '".$locale['097']."', 'news_cats.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('N', 'news.gif', '".$locale['098']."', 'news.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S8', 'settings_news.gif', '".$locale['121']."', 'settings_news.php', '4')");
}
?>