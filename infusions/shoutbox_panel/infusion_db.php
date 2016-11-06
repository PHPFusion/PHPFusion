<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion_db.php
| Author: MarcusG
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
\PHPFusion\Admins::getInstance()->setAdminPageIcons("S", "<i class='fa fa-commenting fa-lg'></i>");

if (!defined("DB_SHOUTBOX")) {
    define("DB_SHOUTBOX", DB_PREFIX."shoutbox");
}

// Added Shoutbox Locale Constant
if (!defined("SHOUTBOX_LOCALE")) {
    if (file_exists(INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php")) {
        define("SHOUTBOX_LOCALE", INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php");
    } else {
        define("SHOUTBOX_LOCALE", INFUSIONS."shoutbox_panel/locale/English.php");
    }
}