<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery/infusion.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");
// Infusion general information
$inf_title = $locale['photos']['title'];
$inf_description = $locale['photos']['description'];
$inf_version = "1.1";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "gallery";
$inf_image = "gallery.png";

// Multilanguage table for Administration
$inf_mlt[] = array(
    "title" => $locale['setup_3308'],
    "rights" => "PG",
);

// Create tables
$inf_newtable[] = DB_PHOTO_ALBUMS." (
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

$inf_newtable[] = DB_PHOTOS." (
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

// Position these links under Content Administration
$inf_adminpanel[] = array(
    "image" => $inf_image,
    "page" => 1,
    "rights" => "PH",
    "title" => $locale['setup_3308'],
    "panel" => "gallery_admin.php"
);

// Gallery settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumb_w', '200', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumb_h', '200', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_w', '800', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_h', '600', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_w', '2400', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_h', '1800', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_b', '2000000', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('gallery_pagination', '24', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark', '1', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_image', 'infusions/gallery/photos/watermark.png', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text', '0', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color1', 'FF6600', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color2', 'FFFF00', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color3', 'FFFFFF', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_save', '0', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('gallery_allow_submission', '1', 'gallery')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('gallery_extended_required', '1', 'gallery')";

// always find and loop ALL languages
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
// Create a link for all installed languages
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3308']."', 'infusions/".$inf_folder."/gallery.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3313']."', 'submit.php?stype=p', ".USER_LEVEL_MEMBER.", '1', '0', '15', '1', '".$language."')";

        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/gallery.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=p' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_PHOTO_ALBUMS." WHERE album_language='".$language."'"; // bug again, will not be able to delete photos tied to it.
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3308']."', 'infusions/".$inf_folder."/gallery.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3313']."', 'submit.php?stype=p', ".USER_LEVEL_MEMBER.", '1', '0', '15', '1', '".$enabled_languages[$i]."')";
}

// Defuse cleaning	
$inf_droptable[] = DB_PHOTO_ALBUMS;
$inf_droptable[] = DB_PHOTOS;

$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='P'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='P'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='P'";

$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='PH'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/gallery.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=p'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='PG'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='gallery'";

//$inf_delfiles[] = IMAGES_G_T;
//$inf_delfiles[] = IMAGES_G;
