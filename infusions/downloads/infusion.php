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
$inf_title = $locale['downloads']['title'];
$inf_description = $locale['downloads']['description'];;
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "downloads";

// Multilanguage table for Administration
$inf_mlt[1] = array(
"title" => $locale['downloads']['title'], 
"rights" => "DL",
);

// Create tables
$inf_newtable[1] = DB_DOWNLOADS." (
	download_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	download_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	download_homepage VARCHAR(100) NOT NULL DEFAULT '',
	download_title VARCHAR(100) NOT NULL DEFAULT '',
	download_description_short VARCHAR(255) NOT NULL,
	download_description TEXT NOT NULL,
	download_keywords VARCHAR(250) NOT NULL DEFAULT '',
	download_image VARCHAR(100) NOT NULL DEFAULT '',
	download_image_thumb VARCHAR(100) NOT NULL DEFAULT '',
	download_url TEXT NOT NULL DEFAULT '',
	download_file VARCHAR(100) NOT NULL DEFAULT '',
	download_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	download_license VARCHAR(50) NOT NULL DEFAULT '',
	download_copyright VARCHAR(250) NOT NULL DEFAULT '',
	download_os VARCHAR(50) NOT NULL DEFAULT '',
	download_version VARCHAR(20) NOT NULL DEFAULT '',
	download_filesize VARCHAR(20) NOT NULL DEFAULT '',
	download_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	download_visibility TINYINT(4) NOT NULL DEFAULT '0',
	download_count INT(10) UNSIGNED NOT NULL DEFAULT '0',
	download_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	download_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (download_id),
	KEY download_datestamp (download_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_DOWNLOAD_CATS." (
	download_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	download_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	download_cat_name VARCHAR(100) NOT NULL DEFAULT '',
	download_cat_description TEXT NOT NULL,
	download_cat_sorting VARCHAR(50) NOT NULL DEFAULT 'download_title ASC',
	download_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (download_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Position these links under Content Administration
$inf_insertdbrow[1] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('DC', 'dl_cats.gif', '".$locale['setup_3009']."', '".INFUSIONS."downloads/download_cats_admin.php', '1')";
$inf_insertdbrow[2] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('D', 'dl.gif', '".$locale['setup_3010']."', '".INFUSIONS."downloads/downloads_admin.php', '1')";

// Insert settings
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_max_b', '512000', 'downloads')";
$inf_insertdbrow[4] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_types', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'downloads')";
$inf_insertdbrow[5] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_b', '150000', 'downloads')";
$inf_insertdbrow[6] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_w', '1024', 'downloads')";
$inf_insertdbrow[7] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_h', '768', 'downloads')";
$inf_insertdbrow[8] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screenshot', '1', 'downloads')";
$inf_insertdbrow[9] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_thumb_max_w', '100', 'downloads')";
$inf_insertdbrow[10] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_thumb_max_h', '100', 'downloads')";
$inf_insertdbrow[11] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_pagination', '15', 'downloads')";

// Create a link for all installed languages
if (!empty($settings['enabled_languages'])) {
$enabled_languages = explode('.', $settings['enabled_languages']);
$k = 12;
	for ($i = 0; $i < count($enabled_languages); $i++) {
	include LOCALE."".$enabled_languages[$i]."/setup.php";
		$inf_insertdbrow[$k] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['downloads']['title']."', 'infusions/downloads/downloads.php', '0', '2', '0', '2', '".$enabled_languages[$i]."')";
		$k++;
	}
} else {
		$inf_insertdbrow[12] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['downloads']['title']."', 'infusions/downloads/downloads.php', '0', '2', '0', '2', '".LANGUAGE."')";
}

// Defuse cleaning	
$inf_droptable[1] = DB_DOWNLOADS;
$inf_droptable[2] = DB_DOWNLOAD_CATS;
$inf_deldbrow[3] = DB_SETTINGS_INF." WHERE settings_inf='downloads'";
$inf_deldbrow[4] = DB_ADMIN." WHERE admin_rights='DC'";
$inf_deldbrow[5] = DB_ADMIN." WHERE admin_rights='D'";
$inf_deldbrow[6] = DB_SITE_LINKS." WHERE link_url='infusions/downloads/downloads.php'";
$inf_deldbrow[7] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=d'";
$inf_deldbrow[8] = DB_LANGUAGE_TABLES." WHERE mlt_rights='DL'";
