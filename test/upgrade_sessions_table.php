<?php
// run upgrade @install.php or to create this table

require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

if (!db_exists(DB_SESSIONS)) {
    $sql = "CREATE TABLE ".DB_SESSIONS." (
      `session_id` VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL,
      `session_start` INT(10) UNSIGNED NOT NULL DEFAULT '0',
      `session_data` TEXT COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`session_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    dbquery($sql);
}

require_once THEMES.'templates/footer.php';