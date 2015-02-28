<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: Marcus Gottschalk (MarcusG)
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
if (file_exists(INFUSIONS."shoutbox_panel/locale/".$settings['locale'].".php")) {
	// Load the locale file matching the current site locale setting.
	include INFUSIONS."shoutbox_panel/locale/".$settings['locale'].".php";
} else {
	// Load the infusion's default locale file.
	include INFUSIONS."shoutbox_panel/locale/English.php";
}

// Infusion general information
$inf_title = $locale['SB_title'];
$inf_description = $locale['SB_desc'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion DEV Team";
$inf_email = "";
$inf_weburl = "http://dev.php-fusion.co.uk";

$inf_folder = "shoutbox_panel"; // The folder in which the infusion resides.

// Delete any items not required below.
$inf_newtable[1] = DB_SHOUTBOX." (
shout_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
shout_name VARCHAR(50) NOT NULL DEFAULT '',
shout_message VARCHAR(200) NOT NULL DEFAULT '',
shout_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
shout_ip VARCHAR(45) NOT NULL DEFAULT '',
shout_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
shout_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (shout_id),
KEY shout_datestamp (shout_datestamp)
) ENGINE=MyISAM;";

$inf_insertdbrow[1] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status) VALUES('".$locale['SB_title']."', 'shoutbox_panel', '', '4', '3', 'file', '0', '0', '1')";
$inf_insertdbrow[2] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('visible_shouts', '5', '".$inf_folder."')";
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('guest_shouts', '0', '".$inf_folder."')";

$inf_droptable[1] = DB_SHOUTBOX;

$inf_deldbrow[1] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[2] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";

$inf_adminpanel[1] = array(
	"title" => $locale['SB_admin1'],
	"image" => "shout.gif",
	"panel" => "shoutbox_admin.php",
	"rights" => "S"
);
?>