<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: csrf_andromeda_patch.php
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

require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';

echo "<h1>Andromeda Security Settings Upgrade Patch 1.00</h1>\n";
$changed = FALSE;
if (!db_exists(DB_SESSIONS)) {
    // Create tables
    dbquery("CREATE TABLE `".DB_SESSIONS."` (
    session_id VARCHAR(32) NOT NULL DEFAULT '',
    session_start INT(10) NOT NULL DEFAULT '0',
    session_data TEXT NOT NULL,    
    PRIMARY KEY (session_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
    $changed = TRUE;
}
if (empty(fusion_get_settings('database_sessions'))) {
    dbquery("INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ('database_sessions', 0)");
    $changed = TRUE;
}
if (empty(fusion_get_settings('form_tokens'))) {
    dbquery("INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ('form_tokens', 5)");
    $changed = TRUE;
}
if (!fusion_get_settings('form_tokens')) {
    dbquery("INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ('user_name_ban', '')");
    $changed = TRUE;
}
if ($changed === TRUE) {
    addNotice("success", "You have successfully upgraded to Andromeda's New Sessions Management Patch 1.0");
}

require_once THEMES.'templates/footer.php';