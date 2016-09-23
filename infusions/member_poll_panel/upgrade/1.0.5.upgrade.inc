<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /member_poll/upgrade/1.0.5.upgrade.inc
| Upgrade Member Poll to Version 9.0.2 (RC5)
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
if (!column_exists(DB_POLLS, 'poll_language')) {
    $inf_altertable[] = DB_POLLS." ADD poll_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER poll_ended";
}
// update new path for admin link
$inf_updatedbrow[] = DB_ADMIN." SET admin_link='../infusions/member_poll_panel/member_poll_panel_admin.php' WHERE admin_link='polls.php'";