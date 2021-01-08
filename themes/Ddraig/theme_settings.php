<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme_settings.php
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

//Check if TCP table exists
if (!DB_EXISTS(DB_PREFIX."ddraig_tcp")) {
    //If TCP is not infused use this settings
    define("TCPINFUSED", 0);
    //Lines below can be changed as an alternative to TCP
    $theme_maxwidth = 1600;
    $theme_minwidth = 986;
    $theme_maxwidth_forum = 0;
    $theme_maxwidth_admin = 0;
    $home_icon = 1;
    $winter_mode = 0;
    ////////////////////////////////////////////////////
} else {
    //If TCP is infused get settings from DB
    define("TCPINFUSED", 1);
    $result = dbquery("SELECT * FROM ".DB_PREFIX."ddraig_tcp");
    $data = dbarray($result);
    $theme_maxwidth = $data['theme_maxwidth'];
    $theme_minwidth = $data['theme_minwidth'];
    $theme_maxwidth_forum = $data['theme_maxwidth_forum'];
    $theme_maxwidth_admin = $data['theme_maxwidth_admin'];
    $home_icon = $data['home_icon'];
    $winter_mode = $data['winter_mode'];
}

//Check if different width is set for Forum
if ($theme_maxwidth_forum >= $theme_minwidth) {
    if (strpos(TRUE_PHP_SELF, '/forum/') !== FALSE) {
        $theme_maxwidth = $theme_maxwidth_forum;
    }
}
//Check if different width is set for Administration
if ($theme_maxwidth_admin >= $theme_minwidth) {
    if (strpos(TRUE_PHP_SELF, '/administration/') !== FALSE) {
        $theme_maxwidth = $theme_maxwidth_admin;
    }
}

define("THEME_MAXWIDTH", $theme_maxwidth."px");
define("THEME_MINWIDTH", $theme_minwidth."px");
define("HOME_ICON", $home_icon);
define("WINTER", $winter_mode);
