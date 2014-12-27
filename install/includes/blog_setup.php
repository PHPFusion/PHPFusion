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
				blog_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
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
	$links_sql = "INSERT INTO ".$db_prefix."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES \n";
	$links_sql .= implode(",\n", array_map(function ($language) {
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
			('".$locale['setup_3511']."', 'php-fusion.gif', '".$language."'),
			('".$locale['setup_3512']."', 'security.gif', '".$language."'),
			('".$locale['setup_3513']."', 'software.gif', '".$language."'),
			('".$locale['setup_3514']."', 'themes.gif', '".$language."'),
			('".$locale['setup_3515']."', 'windows.gif', '".$language."')";
	}, explode('.', $settings['enabled_languages'])));
	if(!dbquery($links_sql)) {
		$fail = TRUE;
	}

	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('BLC', 'blog_cats.gif', '".$locale['setup_3054']."', 'blog_cats.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('BLOG', 'blog.gif', '".$locale['setup_3055']."', 'blog.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S13', 'settings_blog.gif', '".$locale['setup_3055']."', 'settings_blog.php', '4')");

	// site links
	$links_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ";
	$links_sql .= implode(",\n", array_map(function ($language) {
		include LOCALE.$language."/setup.php";
		return "('".$locale['setup_3055']."', 'blog.php', '0', '2', '0', '3', '".$language."')";
	}, explode('.', $settings['enabled_languages'])));
	if(!dbquery($links_sql)) {
		$fail = TRUE;
	}
}
?>