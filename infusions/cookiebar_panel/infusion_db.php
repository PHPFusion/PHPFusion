<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion_db.php
| Author: PHP-Fusion Development Team
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

if (!defined("COOKIE_LOCALE")) {
    if (file_exists(INFUSIONS."cookiebar_panel/locale/".LANGUAGE.".php")) {
        define("COOKIE_LOCALE", INFUSIONS."cookiebar_panel/locale/".LANGUAGE.".php");
    } else {
        define("COOKIE_LOCALE", INFUSIONS."cookiebar_panel/locale/English.php");
    }
}
