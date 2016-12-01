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
if (!column_exists(DB_POLL_VOTES, 'vote_user_ip', FALSE)) {
    $inf_altertable[] = DB_POLL_VOTES." ADD vote_user_ip VARCHAR(45) NOT NULL DEFAULT '' AFTER vote_user";
}
if (!column_exists(DB_POLL_VOTES, 'vote_user_ip_type', FALSE)) {
    $inf_altertable[] = DB_POLL_VOTES." ADD vote_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4' AFTER vote_user_ip";
}
if (!column_exists(DB_POLLS, 'poll_opt', FALSE)) {
    $inf_altertable[] = DB_POLLS." ADD poll_opt TEXT NOT NULL AFTER poll_title";
}
if (!column_exists(DB_POLLS, 'poll_visibility', FALSE)) {
    $inf_altertable[] = DB_POLLS." ADD poll_visibility TINYINT(4) NOT NULL DEFAULT '0' AFTER poll_ended";
}
if (column_exists(DB_POLLS, 'poll_language', FALSE)) {
    $inf_altertable[] = DB_POLLS." DROP poll_language";
}

// update new path for admin link
$inf_updatedbrow[] = DB_ADMIN." SET `admin_image`='../infusions/member_poll_panel/polls.png', `admin_link`='../infusions/member_poll_panel/poll_admin.php' WHERE admin_link='poll_admin.php'";
