<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
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
$inf_title = $locale['weblinks']['title'];
$inf_description = $locale['weblinks']['description'];;
$inf_version = "1.2";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "weblinks";
$inf_image = "weblink.png";

// Multilanguage table for Administration
$inf_mlt[] = array(
    "title" => $locale['weblinks']['title'],
    "rights" => "WL",
);

// Create tables
$inf_newtable[] = DB_WEBLINKS." (
	weblink_id			MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	weblink_name		VARCHAR(100)          NOT NULL DEFAULT '',
	weblink_description	TEXT			      NOT NULL,
	weblink_url			VARCHAR(200)	      NOT NULL DEFAULT '',
	weblink_cat			MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	weblink_datestamp	INT(10)      UNSIGNED NOT NULL DEFAULT '0',
	weblink_visibility	TINYINT(4)            NOT NULL DEFAULT '0',
	weblink_status      TINYINT(1)   UNSIGNED NOT NULL DEFAULT '1',
	weblink_count		SMALLINT(5)  UNSIGNED NOT NULL DEFAULT '0',
	weblink_language	VARCHAR(50)           NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY(weblink_id),
	KEY weblink_datestamp (weblink_datestamp),
	KEY weblink_count (weblink_count)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_WEBLINK_CATS." (
	weblink_cat_id			MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	weblink_cat_parent		MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	weblink_cat_name		VARCHAR(100)          NOT NULL DEFAULT '',
	weblink_cat_description	TEXT                  NOT NULL,
	weblink_cat_status      TINYINT(1)   UNSIGNED NOT NULL DEFAULT '1',
	weblink_cat_visibility	TINYINT(4)            NOT NULL DEFAULT '0',
	weblink_cat_language	VARCHAR(50)           NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY(weblink_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('links_per_page', '15', 'weblinks')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('links_extended_required', '1', 'weblinks')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('links_allow_submission', '1', 'weblinks')";

// Position these links under Content Administration
$inf_adminpanel[] = array(
    "image" => $inf_image,
    "page" => 1,
    "rights" => "W",
    "title" => $locale['setup_3029'],
    "panel" => "weblinks_admin.php"
);

// always find and loop ALL languages
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
// Create a link for all installed languages
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        // add new language records
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3307']."', 'infusions/weblinks/weblinks.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3310']."', 'submit.php?stype=l', ".USER_LEVEL_MEMBER.", '1', '0', '15', '1', '".$language."')";

        // drop deprecated language records
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/weblinks/weblinks.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=l' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_WEBLINKS." WHERE weblink_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_WEBLINK_CATS." WHERE weblink_cat_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3307']."', 'infusions/weblinks/weblinks.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3310']."', 'submit.php?stype=l', ".USER_LEVEL_MEMBER.", '1', '0', '15', '1', '".LANGUAGE."')";
}

// Defuse cleaning
$inf_droptable[] = DB_WEBLINKS;
$inf_droptable[] = DB_WEBLINK_CATS;

$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='W'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='W'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='W'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='W'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='WC'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/weblinks/weblinks.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=l'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='WL'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='weblinks'";
