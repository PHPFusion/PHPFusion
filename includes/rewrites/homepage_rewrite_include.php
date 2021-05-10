<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
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
defined('IN_FUSION') || exit;

$regex = [
    "%time%"        => "([0-9]+)",
    "%section%"     => "([0-9]+)",
    "%logout%"      => "(yes)",
    "%sortby%"      => "([0-9a-zA-Z]+)",
    "%orderby%"     => "([a-zA-Z]+)",
    "%sort_order%"  => "([a-zA-Z]+)",
    "%search_text%" => "([0-9a-zA-Z._]+)",
    "%search%"      => "([a-zA-Z]+)",
    "%rowstart%"    => "([0-9]+)"
];

$pattern = [
    "login"                                                            => "login.php",
    "logout/%logout%"                                                  => "index.php?logout=%logout%",
    "maintenance"                                                      => "maintenance.php",
    "edit-profile/%section%"                                           => "edit_profile.php?section=%section%",
    "edit-profile"                                                     => "edit_profile.php",
    "website-members/s/%search_text%-%search%/%orderby%/%sort_order%/" => "members.php?search_text=%search_text%&amp;search=%search%&amp;orderby=%orderby%&amp;sort_order=%sort_order%",
    "website-members/s/%search_text%/%orderby%/%sort_order%/"          => "members.php?search_text=%search_text%&amp;orderby=%orderby%&amp;sort_order=%sort_order%",
    "website-members/s/%sortby%-%rowstart%"                            => "members.php?sortby=%sortby%&amp;rowstart=%rowstart%",
    "website-members/s/%sortby%"                                       => "members.php?sortby=%sortby%",
    "website-members"                                                  => "members.php",
    "create/ref=%time%"                                                => "register.php?ref=%time%",
    "contact"                                                          => "contact.php",
    "registration"                                                     => "register.php",
    "lost-password"                                                    => "lostpassword.php"
];
