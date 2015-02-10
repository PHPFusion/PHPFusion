<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/downloads_setup.php
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
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."download_cats");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."downloads");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='DC'");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='D'");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='S11'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='downloads.php'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='submit.php?stype=d'");
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."download_cats");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."downloads");

	$result = dbquery("CREATE TABLE ".$db_prefix."download_cats (
				download_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
				download_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
				download_cat_name VARCHAR(100) NOT NULL DEFAULT '',
				download_cat_description TEXT NOT NULL,
				download_cat_sorting VARCHAR(50) NOT NULL DEFAULT 'download_title ASC',
				download_cat_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
				PRIMARY KEY (download_cat_id)
				) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}

	$result = dbquery("CREATE TABLE ".$db_prefix."downloads (
				download_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
				download_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
				download_homepage VARCHAR(100) NOT NULL DEFAULT '',
				download_title VARCHAR(100) NOT NULL DEFAULT '',
				download_description_short VARCHAR(255) NOT NULL,
				download_description TEXT NOT NULL,
				download_keywords VARCHAR(250) NOT NULL DEFAULT '',
				download_image VARCHAR(100) NOT NULL DEFAULT '',
				download_image_thumb VARCHAR(100) NOT NULL DEFAULT '',
				download_url VARCHAR(200) NOT NULL DEFAULT '',
				download_file VARCHAR(100) NOT NULL DEFAULT '',
				download_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
				download_license VARCHAR(50) NOT NULL DEFAULT '',
				download_copyright VARCHAR(250) NOT NULL DEFAULT '',
				download_os VARCHAR(50) NOT NULL DEFAULT '',
				download_version VARCHAR(20) NOT NULL DEFAULT '',
				download_filesize VARCHAR(20) NOT NULL DEFAULT '',
				download_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
				download_visibility TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
				download_count INT(10) UNSIGNED NOT NULL DEFAULT '0',
				download_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
				download_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
				PRIMARY KEY (download_id),
				KEY download_datestamp (download_datestamp)
				) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) { $fail = TRUE; }

	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('DC', 'dl_cats.gif', '".$locale['setup_3009']."', 'download_cats.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('D', 'dl.gif', '".$locale['setup_3010']."', 'downloads.php', '1')");
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S11', 'settings_dl.gif', '".$locale['setup_3042']."', 'settings_dl.php', '4')");

	$links_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_cat, link_icon, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES \n";
	$links_sql .= implode(",\n", array_map(function ($language) use($db_prefix) {
		include LOCALE.$language."/setup.php";
		$id_sql = "(SELECT link_id FROM (SELECT link_id FROM ".$db_prefix."site_links WHERE link_url = 'submissions.php' AND link_language = '".$language."' ORDER BY link_id DESC limit 1) AS t)";
		return "('".$locale['setup_3302']."', '0', '', 'downloads.php', '0', '2', '0', '3', '".$language."'),
				('".$locale['setup_3314']."', $id_sql, '', 'submit.php?stype=d', '101', '1', '0', '16', '".$language."')";
	}, explode('.', fusion_get_settings('enabled_languages'))));
	if(!dbquery($links_sql)) {
		$fail = TRUE;
	}
}
?>