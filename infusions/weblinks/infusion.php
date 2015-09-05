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
$inf_title = $locale['weblinks']['title'];
$inf_description = $locale['weblinks']['description'];;
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "weblinks";

// Multilanguage table for Administration
$inf_mlt[1] = array(
"title" => $locale['weblinks']['title'], 
"rights" => "WL",
);

// Create tables
$inf_newtable[1] = DB_WEBLINKS." (
	weblink_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	weblink_name VARCHAR(100) NOT NULL DEFAULT '',
	weblink_description TEXT NOT NULL,
	weblink_url VARCHAR(200) NOT NULL DEFAULT '',
	weblink_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	weblink_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	weblink_visibility TINYINT(4) NOT NULL DEFAULT '0',
	weblink_count SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY(weblink_id),
	KEY weblink_datestamp (weblink_datestamp),
	KEY weblink_count (weblink_count)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_WEBLINK_CATS." (
	weblink_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	weblink_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	weblink_cat_name VARCHAR(100) NOT NULL DEFAULT '',
	weblink_cat_description TEXT NOT NULL,
	weblink_cat_sorting VARCHAR(50) NOT NULL DEFAULT 'weblink_name ASC',
	weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY(weblink_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Settings
$inf_insertdbrow[1] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('links_per_page', '15', 'weblinks')";
$inf_insertdbrow[2] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('links_extended_required', '1', 'weblinks')";
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('links_allow_submission', '1', 'weblinks')";

// Position these links under Content Administration
$inf_insertdbrow[4] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('W', 'wl.gif', '".$locale['setup_3029']."', '".INFUSIONS."weblinks/weblinks_admin.php', '1')";

$enabled_languages = explode('.', fusion_get_settings('enabled_languages'));

// Create a link for all installed languages
if (!empty($enabled_languages)) {
$k = 5;
	for ($i = 0; $i < count($enabled_languages); $i++) {
		include LOCALE."".$enabled_languages[$i]."/setup.php";
		$inf_insertdbrow[$k] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['weblinks']['title']."', 'infusions/weblinks/weblinks.php', '0', '2', '0', '2', '".$enabled_languages[$i]."')";
		$k++;
	}
} else {
		$inf_insertdbrow[5] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['weblinks']['title']."', 'infusions/weblinks/weblinks.php', '0', '2', '0', '2', '".LANGUAGE."')";
}

// Defuse cleaning	
$inf_droptable[1] = DB_WEBLINKS;
$inf_droptable[2] = DB_WEBLINK_CATS;

$inf_deldbrow[1] = DB_COMMENTS." WHERE comment_type='W'";
$inf_deldbrow[2] = DB_RATINGS." WHERE rating_type='W'";
$inf_deldbrow[3] = DB_ADMIN." WHERE admin_rights='WC'";
$inf_deldbrow[4] = DB_ADMIN." WHERE admin_rights='W'";
$inf_deldbrow[5] = DB_SITE_LINKS." WHERE link_url='infusions/weblinks/weblinks.php'";
$inf_deldbrow[6] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=l'";
$inf_deldbrow[7] = DB_LANGUAGE_TABLES." WHERE mlt_rights='WL'";
$inf_deldbrow[8] = DB_SETTINGS_INF." WHERE settings_inf='weblinks'";
$inf_deldbrow[9] = DB_SUBMISSIONS." WHERE submit_type='W'";
