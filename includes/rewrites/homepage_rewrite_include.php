<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: homepage_rewrite_include.php
| Author: Rizado (Chubatyj Vitalij)
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

$regex = array(
    "%time%"    => "([0-9]+)",
    "%section%" => "([0-9]+)",
    "%logout%"  => "(yes)",
    "%sortby%"  => "([a-zA-Z]+)"
);

$pattern = array(
    // "home"                   => "index.php", // Enable this if your main page is index.php
    "homepage"               => "home.php",
    "login"                  => "login.php",
    "logout/%logout%"        => "index.php?logout=%logout%",
    "maintenance"            => "maintenance.php",
    "edit-profile/%section%" => "edit_profile.php?section=%section%",
    "edit-profile"           => "edit_profile.php",
    "members/%sortby%"       => "members.php?sortby=%sortby%",
    "website-members"        => "members.php",
    "create/ref=%time%"      => "register.php?ref=%time%",
    "contact"                => "contact.php",
    "registration"           => "register.php",
);