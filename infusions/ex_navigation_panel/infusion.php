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

include INFUSIONS."ex_navigation_panel/infusion_db.php";

// Check if locale file is available matching the current site locale setting.
if (file_exists(INFUSIONS."ex_navigation_panel/locale/".$settings['locale'].".php")) {
	// Load the locale file matching the current site locale setting.
	include INFUSIONS."ex_navigation_panel/locale/".$settings['locale'].".php";
} else {
	// Load the infusion's default locale file.
	include INFUSIONS."ex_navigation_panel/locale/English.php";
}

// Infusion general information
$inf_title = $locale['ENP_title'];
$inf_description = $locale['ENP_desc'];
$inf_version = "1.00";
$inf_developer = "Dialektika";
$inf_email = "stanislawbeh@gmail.com";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "ex_navigation_panel"; // The folder in which the infusion resides.

//Administration panel
$inf_adminpanel[1] = array("title" => $locale['ENP_admin1'], "image" => "shout.gif", "panel" => "ex_navigation_admin.php","rights" => "ENP");

//Multilanguage table for Administration
$inf_mlt[1] = array("title" => $locale['ENP_title'], "rights" => "ENP");

// Delete any items not required below.
$inf_newtable[1] = DB_EXNAVPANEL." (
    exlink_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    exlink_name VARCHAR(100) NOT NULL DEFAULT '',
    exlink_url VARCHAR(200) NOT NULL DEFAULT '',
    exlink_position TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    exlink_window TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    exlink_page SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
    exlink_language VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY (exlink_id)
    ) ENGINE=MyISAM;";

//Infuse insertations
$inf_insertdbrow[1] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status) VALUES('".$locale['ENP_title']."', 'ex_navigation_panel', '', '4', '3', 'file', '0', '0', '1')";
$inf_insertdbrow[2] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('visible_exlinks', '5', '".$inf_folder."')";
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('guest_exlinks', '0', '".$inf_folder."')";

//Defuse cleaning	
$inf_droptable[1] = DB_EXNAVPANEL;
$inf_deldbrow[1] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[2] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
?>
