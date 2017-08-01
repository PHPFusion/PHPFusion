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
    "%sortby%"  => "([0-9a-zA-Z]+)",
    "%orderby%"  => "([a-zA-Z]+)",
    "%sort_order%"  => "([a-zA-Z]+)",
    "%search_text%"  => "([0-9a-zA-Z._]+)",
    "%search%"  => "([a-zA-Z]+)",
    "%rowstart%"         => "([0-9]+)",
);

$pattern = array(
    // "home"                   => "index.php", // Enable this if your main page is index.php
    "homepage"               => "home.php",
    "login"                  => "login.php",
    "logout/%logout%"        => "index.php?logout=%logout%",
    "maintenance"            => "maintenance.php",
    "edit-profile/%section%" => "edit_profile.php?section=%section%",
    "edit-profile"           => "edit_profile.php",
    "website-members/search-%search_text%-%search%/%orderby%/%sort_order%/"       => "members.php?search_text=%search_text%&amp;search=%search%&amp;orderby=%orderby%&amp;sort_order=%sort_order%",
    "website-members/search-%search_text%/%orderby%/%sort_order%/"       => "members.php?search_text=%search_text%&amp;orderby=%orderby%&amp;sort_order=%sort_order%",
    "website-members/%sortby%-%rowstart%"                      => "members.php?sortby=%sortby%&amp;rowstart=%rowstart%",
    "website-members/%sortby%"          => "members.php?sortby=%sortby%",
    "website-members"        => "members.php",
    "create/ref=%time%"      => "register.php?ref=%time%",
    "contact"                => "contact.php",
    "registration"           => "register.php",
);