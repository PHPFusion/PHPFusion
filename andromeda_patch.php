<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: andromeda_patch.php
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

$settings = fusion_get_settings();

echo "<h1>Andromeda Upgrade Patch 1.02</h1>\n";
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

$insert_settings_tbl = [
    'database_sessions' => 0,
    'form_tokens'       => 5,
    'user_name_ban'     => '',
    'domain_server'     => '',
    'gateway'           => 1,
    'devmode'           => 0,
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        dbquery("INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')");
        $changed = TRUE;
    }
}

if (!column_exists(DB_ADMIN, 'admin_language', FALSE)) {
    dbquery("ALTER TABLE ".DB_ADMIN." ADD admin_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER admin_page");
    $changed = TRUE;
}

if ($changed === TRUE) {
    addNotice("success", "You have successfully upgraded to latest Andromeda Patch 1.02");
}

dbquery("UPDATE ".DB_ADMIN." SET admin_image='comments.png', admin_link='comments.php', admin_page='1' WHERE admin_rights='C'");

require_once THEMES.'templates/footer.php';
