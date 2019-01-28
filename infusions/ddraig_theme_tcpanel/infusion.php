<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: JoiNNN
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

if (file_exists(INFUSIONS."ddraig_theme_tcpanel/locale/".LANGUAGE.".php")) {
    include INFUSIONS."ddraig_theme_tcpanel/locale/".LANGUAGE.".php";
} else {
    include INFUSIONS."ddraig_theme_tcpanel/locale/English.php";
}

include INFUSIONS."ddraig_theme_tcpanel/infusion_db.php";

// Infusion general information
$inf_title = "Ddraing Theme Control Panel";
$inf_description = "Ddraig Theme Control Panel";
$inf_version = "1.0";
$inf_developer = "JoiNNN";
$inf_email = "Spo0kye@yahoo.com";
$inf_weburl = "http://www.php-fusion.co.uk";

$inf_folder = "ddraig_theme_tcpanel";

$inf_newtable[1] = DB_DDRAIGTCP." (
   theme_maxwidth VARCHAR(4) NOT NULL DEFAULT '',
   theme_minwidth VARCHAR(4) NOT NULL DEFAULT '',
   theme_maxwidth_forum VARCHAR(4) NOT NULL DEFAULT '',
   theme_maxwidth_admin VARCHAR(4) NOT NULL DEFAULT '',
   home_icon TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
   winter_mode TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
   PRIMARY KEY (theme_maxwidth)
) ENGINE=MyISAM;";
$inf_insertdbrow[1] = DB_DDRAIGTCP." (
theme_maxwidth,
theme_minwidth,
theme_maxwidth_forum,
theme_maxwidth_admin,
home_icon,
winter_mode
) VALUES (
'1600',
'980',
'0',
'0',
'1',
'0'
)";

$inf_adminpanel[1] = [
    "title"  => "Ddraig Theme Control Panel",
    "image"  => "ddraigtcp.png",
    "panel"  => "ddraig_tcpanel_admin.php",
    "rights" => "DDCP"
];

$inf_droptable[1] = DB_DDRAIGTCP;
