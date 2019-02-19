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
defined('IN_FUSION') || exit;

\PHPFusion\Admins::getInstance()->setAdminPageIcons("S", "<i class='admin-ico fa fa-fw fa-commenting'></i>");

if (!defined("DB_SHOUTBOX")) {
    define("DB_SHOUTBOX", DB_PREFIX."shoutbox");
}

// Added Shoutbox Locale Constant
if (!defined("SHOUTBOX_LOCALE")) {
    if (file_exists(INFUSIONS."shoutbox_panel/locale/".LOCALESET."shoutbox.php")) {
        define("SHOUTBOX_LOCALE", INFUSIONS."shoutbox_panel/locale/".LOCALESET."shoutbox.php");
    } else {
        define("SHOUTBOX_LOCALE", INFUSIONS."shoutbox_panel/locale/English/shoutbox.php");
    }
}
