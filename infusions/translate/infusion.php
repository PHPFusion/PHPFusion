<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: translate/infusion.php
| Author: Frederick MC Chan
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

$locale = fusion_get_locale('', TRANSLATE_LOCALE);

// Infusion general information
$inf_title = $locale['translate_0000'];
$inf_description = $locale['translate_0001'];;
$inf_version = "1.00";
$inf_developer = "Frederick MC Chan";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "translate";
$inf_image = "translate.svg";
$inf_rights = 'TS';
// Multilanguage table for Administration
$inf_mlt[] = array(
    "title"  => $locale['translate_0000'],
    "rights" => $inf_rights,
);
// Create tables
$inf_newtable[] = DB_TRANSLATE." (
	translate_id			BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	translate_file_id   BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	translate_locale_key		VARCHAR(100)          NOT NULL DEFAULT '',
	translate_locale_value	TEXT			      NOT NULL,	
	translate_message			TEXT	      NOT NULL,		
	translate_language			VARCHAR(50)	      NOT NULL DEFAULT '',		
	translate_datestamp	INT(10)      UNSIGNED NOT NULL DEFAULT '0',	
	translate_status TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY(translate_id),
	KEY translate_locale_key (translate_locale_key),
	KEY translate_language (translate_language)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_TRANSLATE_PACKAGE." (
	package_id			MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,	
	package_name	TEXT			      NOT NULL,		
	package_meta	TEXT			      NOT NULL,		
	package_description	TEXT			      NOT NULL,		
	package_datestamp      INT(10) UNSIGNED NOT NULL DEFAULT '0',
	package_status TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY(package_id),
	KEY package_name (package_name, package_meta, package_description)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_TRANSLATE_FILES." (
	file_id			MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	file_parent MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
	file_package MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
	file_name	TEXT			      NOT NULL,
	file_path	TEXT			      NOT NULL,
	file_message	TEXT			      NOT NULL,
	file_language			VARCHAR(50)	      NOT NULL DEFAULT '',
	file_datestamp      INT(10) UNSIGNED NOT NULL DEFAULT '0',
	file_status TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY(file_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('max_translations_accept', '3', 'translate')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('translate_allow_submission', '1', 'translate')";

// Position these links under Content Administration
$inf_adminpanel[] = array(
    "image"  => $inf_image,
    "page"   => 5,
    "rights" => $inf_rights,
    "title"  => $locale['translate_0000'],
    "panel"  => "translate_admin.php"
);

// Defuse cleaning
$inf_droptable[] = DB_TRANSLATE;
$inf_droptable[] = DB_TRANSLATE_FILES;
$inf_droptable[] = DB_TRANSLATE_PACKAGE;

$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='translate'";