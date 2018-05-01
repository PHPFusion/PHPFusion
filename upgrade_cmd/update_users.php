<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: update_users.php
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
require_once dirname(__FILE__).'/../maincore.php';
require_once THEMES.'templates/header.php';
ini_set('post_max_size','750M');
ini_set('upload_max_filesize', '750M');
ini_set('upload_max_filesize', '750M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '540');
ini_set('memory_limit', '1000M');
$result = dbquery("SELECT user_id, user_level FROM ".DB_USERS." ORDER BY user_id ASC");
if (dbrows($result)) {
    // Column Upgrade
    dbquery("ALTER TABLE ".DB_USERS." ALTER COLUMN `user_level` TINYINT(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_timezone` VARCHAR(50) DEFAULT 'Europe/London' AFTER `user_hide_email`");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_reputation` INT(10) UNSIGNED DEFAULT '0' AFTER `user_status`");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_inbox` SMALLINT(6) UNSIGNED DEFAULT '0' AFTER `user_reputation`");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_outbox` SMALLINT(6) UNSIGNED DEFAULT '0' AFTER `user_inbox`");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_archive` SMALLINT(6) UNSIGNED DEFAULT '0' AFTER `user_outbox`");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_pm_email_notify` TINYINT(1) DEFAULT '0' AFTER `user_archive`");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_pm_save_sent` TINYINT(1) DEFAULT '0' AFTER `user_pm_email_notify`");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_actiontime` INT(10) UNSIGNED DEFAULT '0' AFTER `user_pm_save_sent`");
    dbquery("ALTER TABLE ".DB_USERS." ADD COLUMN `user_language` VARCHAR(50) DEFAULT 'English'");
    while ($data = dbarray($result)) {
        // Change user level status
        if ($data['user_level']) {
            dbquery("UPDATE ".DB_USERS." SET `user_level`=:level WHERE `user_id`=:user_id", [':level'=>'-'.$data['user_level'], ':user_id'=>$data['user_id']]);
        }
    }
    addNotice('success', 'Upgrade Users Successful');
}
require_once THEMES.'templates/footer.php';