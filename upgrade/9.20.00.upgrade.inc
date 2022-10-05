<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.10.20.upgrade.inc
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

$locale = fusion_get_locale('', LOCALE.LOCALESET.'setup.php');
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version'                  => '9.20.00',
    // Authentication Settings
    'auth_login_enabled'       => '1',
    'auth_login_expiry'        => 300,  // 5 minutes
    'auth_login_length'        => 6,
    'auth_login_attempts'      => 3,
    'privacy_lastupdate'       => 0,
    // Login Settings
    'login_session_expiry'     => 43200, // 6 hours
    'login_session_ext_expiry' => 86400, // 1 day duration
    'admin_session_expiry'     => 43200, // 6 hours
    'admin_session_ext_expiry' => 86400, // 1 day duration
    'license'                  => 'agpl',
    'license_key'              => '',
];

if (!column_exists(DB_USERS, 'user_auth', FALSE)) {
    $inf_altertable[] = DB_USERS." ADD user_auth TINYINT(1) NOT NULL DEFAULT '0' AFTER user_session";
    $inf_altertable[] = DB_USERS." ADD user_auth_pin VARCHAR(10) NOT NULL DEFAULT '' AFTER user_auth";
    $inf_altertable[] = DB_USERS." ADD user_auth_actiontime INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER user_auth_pin";
}

if (!column_exists(DB_USER_GROUPS, 'group_user_count', FALSE)) {
    $inf_altertable[] = DB_USER_GROUPS." ADD group_user_count MEDIUMINT(11) NOT NULL DEFAULT '0' AFTER group_icon";
    $result = dbquery("SELECT group_id FROM ".DB_USER_GROUPS);
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $id = $data["group_id"];
            $user_count = dbcount("(user_id)", DB_USERS, "user_groups REGEXP('^\\\.$id$|\\\.$id\\\.|\\\.$id$')");
            $inf_updatedbrow[] = DB_USER_GROUPS." SET group_user_count='$user_count' WHERE group_id='$id'";
        }
    }
}


// add multilingual 2fa email templates
$enabled_languages = (function_exists("fusion_get_enabled_languages")) ? array_keys(fusion_get_enabled_languages()) : makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = [];
        include (is_file(LOCALE.$language.'/admin/members_email.php')) ? LOCALE.$language.'/admin/members_email.php' : LOCALE.'English/admin/members_email.php';

        $email_tpl = [
            'L_2FA'         => "('L_2FA', 'html', '1', '".$locale['email_2fa_name']."', '".$locale['email_2fa_subject']."',  '".stripinput($locale['email_2fa_message'])."', '', '', '".$language."')",
            'U_CREATE'      => "('U_CREATE', 'html', '1', '".$locale['email_create_name']."', '".$locale['email_create_subject']."',  '".stripinput($locale['email_create_message'])."', '', '', '".$language."')",
            'U_VERIFY'      => "('U_VERIFY', 'html', '1', '".$locale['email_verify_name']."', '".$locale['email_verify_subject']."',  '".stripinput($locale['email_verify_message'])."', '', '', '".$language."')",
            'U_ACTIVE'      => "('U_ACTIVE', 'html', '1', '".$locale['email_activate_name']."', '".$locale['email_activate_subject']."',  '".stripinput($locale['email_activate_message'])."', '', '', '".$language."')",
            'U_LOSTP'       => "('U_LOSTP', 'html', '1', '".$locale['email_pass_name']."', '".$locale['email_pass_subject']."',  '".stripinput($locale['email_pass_message'])."', '', '', '".$language."')",
            'U_SENDP'       => "('U_SENDP', 'html', '1', '".$locale['email_pass_name']."', '".$locale['email_pass_subject']."',  '".stripinput($locale['email_pass_notify'])."', '', '', '".$language."')",
            'U_EMAIL'       => "('U_EMAIL', 'html', '1', '".$locale['email_change_name']."', '".$locale['email_change_subject']."',  '".stripinput($locale['email_change_message'])."', '', '', '".$language."')",
            'U_PASS'        => "('U_PASS', 'html', '1', '".$locale['email_passchange_name']."', '".$locale['email_passchange_subject']."',  '".stripinput($locale['email_passchange_message'])."', '', '', '".$language."')",
            'U_SECBAN'      => "('U_SECBAN', 'html', '1', '".$locale['email_secban_name']."', '".$locale['email_secban_subject']."',  '".stripinput($locale['email_secban_message'])."', '', '', '".$language."')",
            'U_REACTIVATED' => "('U_REACTIVATED', 'html', '1', '".$locale['email_reactivated_name']."', '".$locale['email_reactivated_subject']."',  '".stripinput($locale['email_reactivated_message'])."', '', '', '".$language."')",
            'U_UNSUSPEND'   => "('U_UNSUSPEND', 'html', '1', '".$locale['email_unsuspend_name']."', '".$locale['email_unsuspend_subject']."',  '".stripinput($locale['email_unsuspend_message'])."', '', '', '".$language."')",
        ];

        foreach ($email_tpl as $key => $values) {
            if (!dbcount("(template_key)", DB_EMAIL_TEMPLATES, "template_key=:key AND template_language=:lang", [":key" => $key, ":lang" => $language])) {

                $mlt_insertdbrow[$language][] = DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ".$values;
            }
        }
    }
}

foreach ($update_settings_tbl as $col_name => $col_value) {
    if (!isset($settings[$col_name])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$col_name', '$col_value')";
    }
}
