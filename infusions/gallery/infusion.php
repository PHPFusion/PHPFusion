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
$inf_title = $locale['photos']['title'];
$inf_description = $locale['photos']['description'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "gallery";

// Multilanguage table for Administration
$inf_mlt[1] = array(
"title" => $locale['photos']['title'], 
"rights" => "PG",
);

// Create tables
$inf_newtable[1] = DB_PHOTO_ALBUMS." (
	album_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	album_title VARCHAR(100) NOT NULL DEFAULT '',
	album_description TEXT NOT NULL,
	album_keywords VARCHAR(250) NOT NULL DEFAULT '',
	album_image VARCHAR(200) NOT NULL DEFAULT '',
	album_thumb1 VARCHAR(200) NOT NULL DEFAULT '',
	album_thumb2 VARCHAR(200) NOT NULL DEFAULT '',
	album_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	album_access TINYINT(4) NOT NULL DEFAULT '0',
	album_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	album_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	album_language varchar(50) NOT NULL default '".LANGUAGE."',
	PRIMARY KEY (album_id),
	KEY album_order (album_order),
	KEY album_datestamp (album_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_PHOTOS." (
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
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Gallery settings
$inf_insertdbrow[1] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumb_w', '200', 'gallery')";
$inf_insertdbrow[2] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumb_h', '200', 'gallery')";
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_w', '400', 'gallery')";
$inf_insertdbrow[4] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_h', '400', 'gallery')";
$inf_insertdbrow[5] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_w', '1800', 'gallery')";
$inf_insertdbrow[6] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_h', '1600', 'gallery')";
$inf_insertdbrow[7] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_b', '15120000', 'gallery')";
$inf_insertdbrow[11] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('gallery_pagination', '24', 'gallery')";
$inf_insertdbrow[12] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark', '1', 'gallery')";
$inf_insertdbrow[13] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_image', 'infusions/gallery/albums/watermark.png', 'gallery')";
$inf_insertdbrow[14] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text', '0', 'gallery')";
$inf_insertdbrow[15] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color1', 'FF6600', 'gallery')";
$inf_insertdbrow[16] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color2', 'FFFF00', 'gallery')";
$inf_insertdbrow[17] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color3', 'FFFFFF', 'gallery')";
$inf_insertdbrow[18] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_save', '0', 'gallery')";

// Position the link under Content Administration
$inf_insertdbrow[19] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('PH', 'photoalbums.gif', '".$locale['photos']['title']."', '".INFUSIONS."gallery/gallery_admin.php', '1')";

$enabled_languages = explode('.', fusion_get_settings('enabled_languages'));
		
// Create a link for all installed languages
if (!empty($enabled_languages)) {
$k = 20;
	for ($i = 0; $i < count($enabled_languages); $i++) {
		include LOCALE."".$enabled_languages[$i]."/setup.php";
		$inf_insertdbrow[$k] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['photos']['title']."', 'infusions/gallery/gallery.php', '0', '2', '0', '2', '".$enabled_languages[$i]."')";
		$k++;
	}
} else {
		$inf_insertdbrow[20] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['photos']['title']."', 'infusions/gallery/gallery.php', '0', '2', '0', '2', '".LANGUAGE."')";
}

// Defuse cleaning	
$inf_droptable[1] = DB_PHOTO_ALBUMS;
$inf_droptable[2] = DB_PHOTOS;
$inf_deldbrow[1] = DB_ADMIN." WHERE admin_rights='PH'";
$inf_deldbrow[2] = DB_SITE_LINKS." WHERE link_url='infusions/gallery/gallery.php'";
$inf_deldbrow[3] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=p'";
$inf_deldbrow[4] = DB_LANGUAGE_TABLES." WHERE mlt_rights='PG'";
$inf_deldbrow[5] = DB_SETTINGS_INF." WHERE settings_inf='gallery'";
