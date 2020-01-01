<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 9.03.00.upgrade.inc
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
$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");
$settings = fusion_get_settings();

$update_settings_tbl = [
    'fontawesome' => 1,
    'theme'       => 'Magazine',
    'version'     => '9.03.00',
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

$insert_settings_tbl = [
    'user_name_ban'     => '',
    'database_sessions' => 1,
    'domain_server'     => '',
    'form_tokens'       => 5,
    'gateway'           => 1,
    'devmode'           => 0,
    'link_grouping'     => 8
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
    }
}

if (db_exists(DB_PREFIX.'custom_pages')) {
    if (!column_exists(DB_CUSTOM_PAGES, 'page_breaks', FALSE)) {
        $inf_altertable[] = DB_CUSTOM_PAGES." ADD page_breaks CHAR(1) NOT NULL DEFAULT '' AFTER page_status";
    }
}

if (db_exists(DB_PREFIX.'faqs')) {
    $new_icon_array = ['FQ' => '../infusions/faq/faq.svg'];
}

if (db_exists(DB_PREFIX.'polls')) {
    $new_icon_array = ['PO' => '../infusions/member_poll_panel/polls.svg'];
}

if (db_exists(DB_PREFIX.'shoutbox')) {
    $new_icon_array = ['S' => '../infusions/shoutbox_panel/shouts.svg'];
}

foreach ($new_icon_array as $admin_rights => $icon_file) {
    $inf_updatedbrow[] = DB_ADMIN." SET admin_image='".$icon_file."' WHERE admin_rights='".$admin_rights."'";
}

if (db_exists(DB_PREFIX.'sessions')) {
    $inf_newtable[] = DB_SESSIONS." (
        session_id VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL,
        session_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
        session_data TEXT COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (session_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
}

dbquery("ALTER TABLE ".DB_USERS." ADD FULLTEXT(`user_name`)");

if (db_exists(DB_PREFIX.'messages_options')) {
    $inf_droptable[] = DB_PREFIX.'messages_options';
}

$result = dbquery("SELECT user_id, user_rights, user_level FROM ".DB_USERS." WHERE user_rights !=''");
while ($data = dbarray($result)) {
    $current_rights = explode('.', $data['user_rights']);
    $isset_rights = array_flip($current_rights);

    if ($data['user_level'] === USER_LEVEL_SUPER_ADMIN || $data['user_level'] === '-103') {
        $new_rights = ['FM'];
        foreach ($new_rights as $key => $add) {
            if (!in_array($add, $current_rights)) {
                $current_rights[] = $add;
            }
        }
    }

    $final_rights = implode('.', $current_rights);
    $inf_updatedbrow[] = DB_USERS." SET user_rights='".$final_rights."' WHERE user_id='".$data['user_id']."'";
}

if (db_exists(DB_ADMIN)) {
    if (!column_exists(DB_ADMIN, 'admin_language', FALSE)) {
        $inf_altertable[] = DB_ADMIN." ADD admin_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER admin_page";
    }

    dbquery("UPDATE ".DB_ADMIN." SET admin_image='comments.png', admin_link='comments.php', admin_page='1' WHERE admin_rights='C'");
    dbquery("UPDATE ".DB_ADMIN." SET admin_link='reserved' WHERE admin_rights='U'");

    // Add new core administration links
    $check_array = [
        'FM' => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('FM', 'file_manager.png', '".$locale['setup_3059']."', 'file_manager.php', '1')"
    ];

    foreach ($check_array as $key => $value) {
        $count = dbcount("('admin_rights')", DB_ADMIN, "admin_rights='$key'");
        if (empty($count)) {
            $inf_insertdbrow[] = $value;
        }
    }
}

$result = dbquery("SELECT * FROM ".DB_SMILEYS);
if (dbrows($result)) {
    $arr_defaults = [
        'smile.png' => 'smile.svg',
        'wink.png'  => 'wink.svg',
        'sad.png'   => 'sad.svg',
        'frown.png' => 'frown.svg',
        'shock.png' => 'shock.svg',
        'pfft.png'  => 'pfft.svg',
        'cool.png'  => 'cool.svg',
        'grin.png'  => 'grin.svg',
        'angry.png' => 'angry.svg',
        'like.png'  => 'like.svg'
    ];

    while ($data = dbarray($result)) {
        $data['smiley_image'] = strtr($data['smiley_image'], $arr_defaults);
        dbquery_insert(DB_SMILEYS, $data, 'update', ['keep_session' => TRUE]);
    }
}

$result = dbquery("SHOW TABLES");
while ($table = dbarraynum($result)) {
    if (preg_match("/^".DB_PREFIX."/i", $table[0])) {
        dbquery("ALTER TABLE ".$table[0]." CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    }
}
