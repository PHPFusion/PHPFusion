<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/articles_setup.php
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
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."articles");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."article_cats");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='A'");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='AC'");
	$result = dbquery("DELETE FROM ".$db_prefix."panels WHERE panel_filename='latest_articles_panel'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='articles.php'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='submit.php?stype=a'");
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."articles");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."article_cats");

	$result = dbquery("CREATE TABLE ".$db_prefix."articles (
		article_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		article_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		article_subject VARCHAR(200) NOT NULL DEFAULT '',
		article_snippet TEXT NOT NULL,
		article_article TEXT NOT NULL,
		article_keywords VARCHAR(250) NOT NULL DEFAULT '',
		article_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		article_breaks CHAR(1) NOT NULL DEFAULT '',
		article_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
		article_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		article_visibility TINYINT(1) NOT NULL DEFAULT '0',
		article_reads MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		article_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
		article_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
		PRIMARY KEY (article_id),
		KEY article_cat (article_cat),
		KEY article_datestamp (article_datestamp),
		KEY article_reads (article_reads)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}

	$result = dbquery("CREATE TABLE ".$db_prefix."article_cats (
			article_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			article_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			article_cat_name VARCHAR(100) NOT NULL DEFAULT '',
			article_cat_description VARCHAR(200) NOT NULL DEFAULT '',
			article_cat_sorting VARCHAR(50) NOT NULL DEFAULT 'article_subject ASC',
			article_cat_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
			PRIMARY KEY (article_cat_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}

	// admin pages
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('AC', 'article_cats.gif', '".$locale['setup_3001']."', 'article_cats.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('A', 'articles.png', '".$locale['setup_3002']."', 'articles.php', '1')");
	// panel
	$result = dbquery("INSERT INTO ".$db_prefix."panels (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list) VALUES ('".$locale['setup_3403']."', 'latest_articles_panel', '', '1', '5', 'file', '0', '0', '1', '')");
	// links
	$links_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_cat, link_icon, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES \n";
	$links_sql .= implode(",\n", array_map(function ($language) use($db_prefix) {
		include LOCALE.$language."/setup.php";
		$id_sql = "(SELECT link_id FROM (SELECT link_id FROM ".$db_prefix."site_links WHERE link_url = 'submissions.php' AND link_language = '".$language."' ORDER BY link_id DESC limit 1) AS t)";
		return "('".$locale['setup_3301']."', '0', '', 'articles.php', '0', '2', '0', '2', '".$language."'),
				('".$locale['setup_3312']."', $id_sql, '', 'submit.php?stype=a', '-101', '1', '0', '12', '".$language."')";
	}, explode('.', fusion_get_settings('enabled_languages'))));
	if(!dbquery($links_sql)) {
		$fail = TRUE;
	}
}
?>