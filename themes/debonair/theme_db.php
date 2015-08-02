<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme_db.php
| Author: Craig, Hien
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
$readme_file = '';
$theme_folder = 'debonair';
$theme_title = 'Debon Air';
$theme_screenshot = 'screenshot.jpg';
$theme_author = 'BabyTunes';
$theme_web = 'http://rusfusion.ru/infusions/moddb/view.php?mod_id=777';
$theme_license = 'AGPL3';
$theme_version = '1.00';
$theme_description = 'White and Green theme as well as a slider';

define('DB_DEBONAIR', DB_PREFIX."debonair");

/**
 * Theme API architecture params
 * When to install the theme ? -- Install Theme Widgets button.
*  When to delete the theme settings ? --  Uninstall  Theme Widgets button
*/

// What to customize in DebonAir?
// Banner Settings
// which page shows the master slider? use tags.

// how many slides to insert? what is the next slide? install a new table.
$theme_newtable[1] = DB_DEBONAIR." (
	banner_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	banner_subject VARCHAR(200) NOT NULL DEFAULT '',
	banner_description text not null,
	banner_link varchar(200) not null default '',
	banner_image varchar(200) not null default '',
	banner_thumb varchar(200) not null default '',
	banner_order mediumint(8) unsigned not null default '0',
	banner_datestamp int(10) unsigned not null default '0',
	banner_visibility tinyint(4) not null default '0',
	banner_language varchar(50) not nulld efault '".LANGUAGE."',
	PRIMARY KEY (banner_id),
	KEY news_datestamp (banner_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
$theme_droptable[1] = DB_DEBONAIR;

// main settings of first canvas.
$theme_insertdbrow[1] = DB_SETTINGS_THEME." (settings_name, settings_value, settings_theme) VALUES ('main_banner_url', 'home.php', '".$theme_folder."')";
$theme_insertdbrow[2] = DB_SETTINGS_THEME." (settings_name, settings_value, settings_theme) VALUES ('banner_title', '', '".$theme_folder."')";
$theme_insertdbrow[3] = DB_SETTINGS_THEME." (settings_name, settings_value, settings_theme) VALUES ('banner_description', '', '".$theme_folder."')";
$theme_insertdbrow[4] = DB_SETTINGS_THEME." (settings_name, settings_value, settings_theme) VALUES ('banner_image', '', '".$theme_folder."')";


$theme_deldbrow[1] = DB_SETTINGS_THEME." WHERE settings_theme='".$theme_folder."'";
// pull latest content from which infusions? make list of installed.

// Bottom Settings
// Columns - pick one of the following
// current members -
// latest content -
// custom html.
