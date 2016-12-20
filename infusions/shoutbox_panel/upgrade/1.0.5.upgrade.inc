<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /shoutbox_panel/upgrade/1.0.5.upgrade.inc
| Upgrade Shoutbox to Version 9.0.2 (RC5)
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
$inf_version = '1.0.5';
if (column_exists(DB_SHOUTBOX, 'shout_hidden', FALSE)) {
    $inf_altertable[] = DB_SHOUTBOX." CHANGE shout_hidden shout_hidden TINYINT(4) UNSIGNED NOT NULL DEFAULT '0'";
}
