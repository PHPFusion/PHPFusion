<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/photo_setup.php
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
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."photo_albums");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."photos");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='PH'");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='S5'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='photogallery.php'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='submit.php?stype=p'");
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."photo_albums");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."photos");
	$result = dbquery("CREATE TABLE ".$db_prefix."photo_albums (
			album_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			album_title VARCHAR(100) NOT NULL DEFAULT '',
			album_description TEXT NOT NULL,
			album_thumb VARCHAR(100) NOT NULL DEFAULT '',
			album_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			album_access TINYINT(1) NOT NULL DEFAULT '0',
			album_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
			album_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
			album_language varchar(50) NOT NULL default '".$_POST['localeset']."',
			PRIMARY KEY (album_id),
			KEY album_order (album_order),
			KEY album_datestamp (album_datestamp)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."photos (
			photo_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			album_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			photo_title VARCHAR(100) NOT NULL DEFAULT '',
			photo_description TEXT NOT NULL,
			photo_keywords VARCHAR(250) NOT NULL DEFAULT '',
			photo_filename VARCHAR(100) NOT NULL DEFAULT '',
			photo_thumb1 VARCHAR(100) NOT NULL DEFAULT '',
			photo_thumb2 VARCHAR(100) NOT NULL DEFAULT '',
			photo_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
			photo_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			photo_views INT(10) UNSIGNED NOT NULL DEFAULT '0',
			photo_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
			photo_allow_comments tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
			photo_allow_ratings tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
			PRIMARY KEY (photo_id),
			KEY photo_order (photo_order),
			KEY photo_datestamp (photo_datestamp)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) $fail = TRUE;

	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PH', 'photoalbums.gif', '".$locale['setup_3020']."', 'photoalbums.php', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S5', 'photoalbums.gif', '".$locale['setup_3034']."', 'settings_photo.php', '4')");
	if (!$result) $fail = TRUE;

	$links_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_cat, link_icon, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES \n";
	$links_sql .= implode(",\n", array_map(function ($language) use($db_prefix) {
		include LOCALE.$language."/setup.php";
		$id_sql = "(SELECT link_id FROM (SELECT link_id FROM ".$db_prefix."site_links WHERE link_url = 'submissions.php' AND link_language = '".$language."' ORDER BY link_id DESC limit 1) AS t)";
		return "('".$locale['setup_3308']."', '0', '', 'photogallery.php', '0', '1', '0', '9', '".$language."'),
				('".$locale['setup_3313']."', $id_sql, '', 'submit.php?stype=p', '-101', '1', '0', '15', '".$language."')";
	}, explode('.', fusion_get_settings('enabled_languages'))));
	if(!dbquery($links_sql)) {
		$fail = TRUE;
	}
}
?>