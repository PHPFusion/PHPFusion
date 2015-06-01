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
$inf_title = $locale['polls']['title'];
$inf_description = $locale['polls']['description'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "member_poll_panel";

// Multilanguage table for Administration
$inf_mlt[1] = array(
"title" => $locale['setup_3207'], 
"rights" => "PO",
);

// Create tables
$inf_newtable[1] = DB_POLL_VOTES." (
	vote_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	vote_opt SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
	poll_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (vote_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_POLLS." (
	poll_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	poll_title VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_0 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_1 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_2 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_3 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_4 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_5 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_6 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_7 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_8 VARCHAR(200) NOT NULL DEFAULT '',
	poll_opt_9 VARCHAR(200) NOT NULL DEFAULT '',
	poll_started INT(10) UNSIGNED NOT NULL DEFAULT '0',
	poll_ended INT(10) UNSIGNED NOT NULL DEFAULT '0',
	poll_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (poll_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Automatic enable of the latest articles panel
$inf_insertdbrow[1] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES('".$locale['setup_3407']."', 'member_poll_panel', '', '1', '5', 'file', '0', '0', '1', '', '')";

// Position these links under Content Administration
$inf_insertdbrow[2] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PO', 'polls.gif', '".$locale['setup_3022']."', '".INFUSIONS."member_poll_panel/member_poll_panel_admin.php', '1')";

// Defuse cleaning	
$inf_droptable[1] = DB_POLLS;
$inf_droptable[2] = DB_POLL_VOTES;
$inf_deldbrow[1] = DB_PANELS." WHERE panel_filename='".$locale['setup_3407']."'";
$inf_deldbrow[2] = DB_ADMIN." WHERE admin_rights='PO'";
