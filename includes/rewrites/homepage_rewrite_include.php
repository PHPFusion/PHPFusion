<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules for 9.00
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

$regex = array(
    "%lang%" => "(.*)\'(?=\s*|)",
    "%wildcard%" => "(.*?)",
    "%time%" => "([0-9]+)"
);

$pattern = array(
    "homepage" => "home.php",
    "home/%lang%" => "%wildcard%?lang=%lang%",
    "login-to-website" => "login.php", "edit-profile" => "edit_profile.php",
    "website-members" => "members.php", "private-messages" => "messages.php",
    "logout-from-website" => "index.php?logout=yes",
    "home" => "index.php",
    "create/ref=%time%" => "register.php?ref=%time%",
    "submit" => "submit.php",
);
