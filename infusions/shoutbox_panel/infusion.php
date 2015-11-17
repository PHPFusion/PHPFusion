<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: PHP Fusion Development Team
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
include INFUSIONS."shoutbox_panel/infusion_db.php";
// Check if locale file is available matching the current site locale setting.
if (file_exists(INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php")) {
	// Load the locale file matching the current site locale setting.
	include INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php";
} else {
	// Load the infusion's default locale file.
	include INFUSIONS."shoutbox_panel/locale/English.php";
}
// Infusion general information
$inf_title = $locale['SB_title'];
$inf_description = $locale['SB_desc'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "shoutbox_panel"; // The folder in which the infusion resides.
//Administration panel
$inf_adminpanel[] = array(
	"title" => $locale['SB_admin1'],
	"image" => "shout.png",
	"panel" => "shoutbox_admin.php",
	"rights" => "S",
	"page" => 5
);
//Multilanguage table for Administration
$inf_mlt[] = array(
	"title" => $locale['SB_title'],
	"rights" => "SB"
);
// Delete any items not required below.
$inf_newtable[] = DB_SHOUTBOX." (
    shout_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    shout_name VARCHAR(50) NOT NULL DEFAULT '',
    shout_message VARCHAR(200) NOT NULL DEFAULT '',
    shout_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    shout_ip VARCHAR(45) NOT NULL DEFAULT '',
    shout_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
    shout_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    shout_language VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY (shout_id),
    KEY shout_datestamp (shout_datestamp)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// shoutbox deletion of MLT shouts
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
	foreach($enabled_languages as $language) {
		include LOCALE.$language."/setup.php";
		$mlt_deldbrow[$language][] = DB_SHOUTBOX." WHERE shout_language='".$language."'";
	}
}

//Infuse insertations
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES('".$locale['SB_title']."', 'shoutbox_panel', '', '4', '3', 'file', '0', '1', '1', '', '0')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('visible_shouts', '5', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('guest_shouts', '0', '".$inf_folder."')";

//Defuse cleaning
$inf_droptable[] = DB_SHOUTBOX;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='S'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='SB'";