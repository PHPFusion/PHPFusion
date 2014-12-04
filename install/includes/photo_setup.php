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
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."photo_albums");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."photos");
	$result = dbquery("CREATE TABLE ".$db_prefix."photo_albums (
			album_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			album_title VARCHAR(100) NOT NULL DEFAULT '',
			album_description TEXT NOT NULL,
			album_thumb VARCHAR(100) NOT NULL DEFAULT '',
			album_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			album_access SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
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
	if (!$result) {
		$fail = TRUE;
	}
}


?>