<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/blog_setup.php
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
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."blog");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."blog_cats");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='BLC'");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='BLOG'");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='S13'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='blog.php'");
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."blog");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."blog_cats");
	if (!db_exists($db_prefix."blog")) {
		$result = dbquery("CREATE TABLE ".$db_prefix."blog (
			blog_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			blog_subject VARCHAR(200) NOT NULL DEFAULT '',
			blog_image VARCHAR(100) NOT NULL DEFAULT '',
			blog_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
			blog_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
			blog_ialign VARCHAR(15) NOT NULL DEFAULT '',
			blog_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			blog_blog TEXT NOT NULL,
			blog_extended TEXT NOT NULL,
			blog_keywords VARCHAR(250) NOT NULL DEFAULT '',
			blog_breaks CHAR(1) NOT NULL DEFAULT '',
			blog_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
			blog_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
			blog_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
			blog_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
			blog_visibility TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
			blog_reads INT(10) UNSIGNED NOT NULL DEFAULT '0',
			blog_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			blog_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			blog_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
			blog_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
			blog_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
			PRIMARY KEY (blog_id),
			KEY blog_datestamp (blog_datestamp),
			KEY blog_reads (blog_reads)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	} else {
		$fail = TRUE;
	}
	if (!db_exists($db_prefix."blog_cats")) {
		$result = dbquery("CREATE TABLE ".$db_prefix."blog_cats (
				blog_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
				blog_cat_name VARCHAR(100) NOT NULL DEFAULT '',
				blog_cat_image VARCHAR(100) NOT NULL DEFAULT '',
				blog_cat_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
				PRIMARY KEY (blog_cat_id)
				) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}
	} else {
		$fail = TRUE;
	}

	// Local inserts
	$enabled_languages = explode('.', $settings['enabled_languages']);
	for ($i = 0; $i < sizeof($enabled_languages); $i++) {
		include LOCALE.$enabled_languages[$i]."/setup.php";
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['180']."', 'bugs.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['181']."', 'downloads.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['182']."', 'games.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['183']."', 'graphics.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['184']."', 'hardware.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['185']."', 'journal.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['186']."', 'members.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['187']."', 'mods.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['188']."', 'movies.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['189']."', 'network.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['191']."', 'php-fusion.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['192']."', 'security.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['193']."', 'software.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['194']."', 'themes.gif', '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['195']."', 'windows.gif', '".$enabled_languages[$i]."')");
	}

	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('BLC', 'blog_cats.gif', '".$locale['130a']."', 'blog_cats.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('BLOG', 'blog.gif', '".$locale['130b']."', 'blog.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S13', 'settings_blog.gif', '".$locale['130b']."', 'settings_blog.php', '4')");

	// site links
	$enabled_languages = explode('.', $settings['enabled_languages']);
	for ($i = 0; $i < sizeof($enabled_languages); $i++) {
		include LOCALE.$enabled_languages[$i]."/setup.php";
		$result = dbquery("INSERT INTO ".$db_prefix."site_links (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['130b']."', 'blog.php', '0', '2', '0', '3', '".$enabled_languages[$i]."')");
	}
}
?>