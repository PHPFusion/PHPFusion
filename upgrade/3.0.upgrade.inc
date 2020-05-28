<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/upgrade/3.0.upgrade.inc
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
$inf_version = '3.0';

$inf_altertable[] = DB_FORUM_ATTACHMENTS." ADD INDEX thread_id (thread_id), ADD INDEX post_id (post_id);";
$inf_altertable[] = DB_FORUM_VOTES." ADD INDEX forum_id (forum_id), ADD INDEX thread_id (thread_id), ADD INDEX post_id (post_id);";
$inf_altertable[] = DB_FORUM_POLL_VOTERS." DROP INDEX thread_id, ADD INDEX thread_id (thread_id), ADD INDEX forum_vote_user_id (forum_vote_user_id);";
$inf_altertable[] = DB_FORUMS." ADD INDEX forum_cat (forum_cat);";
$inf_altertable[] = DB_FORUM_THREADS." ADD INDEX forum_id (forum_id);";
$inf_altertable[] = DB_FORUM_THREADS." ADD forum_show_postcount TINYINT(1) NOT NULL DEFAULT '0';";

if (db_exists(DB_PREFIX.'forum_reports')) {
    $inf_newtable[] = DB_FORUM_REPORTS." (
        report_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        report_forum_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
        report_thread_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
        report_post_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
        report_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
        report_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
        PRIMARY KEY (report_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
}
