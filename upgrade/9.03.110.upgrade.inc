<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.03.110.upgrade.inc
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
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version' => '9.03.110'
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

$insert_settings_tbl = [
    'username_change' => !empty($settings['userNameChange']) ? $settings['userNameChange'] : '',
    'username_ban'    => !empty($settings['user_name_ban']) ? $settings['user_name_ban'] : ''
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
    }
}

$delete_settings_tbl = [
    'userNameChange',
    'user_name_ban'
];

foreach ($delete_settings_tbl as $value) {
    $inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='$value'";
}

if (db_exists(DB_PREFIX.'captcha')) {
    dbquery("DROP TABLE ".DB_PREFIX.'captcha');
}

/**
 * Delete all unused files
 */
$deleted_files = [
    INFUSIONS.'articles/templates/articles.php',
    INFUSIONS.'blog/templates/blog.php',
    INFUSIONS.'downloads/templates/downloads.php',
    INFUSIONS.'faq/templates/faq_info.html',
    INFUSIONS.'faq/templates/faq_submissions.html',
    INFUSIONS.'faq/templates/main_faq.html',
    INFUSIONS.'faq/templates.php',
    INFUSIONS.'forum/templates/forum.css',
    INFUSIONS.'forum/templates/templates.php',
    INFUSIONS.'forum_mods_online_panel/templates/forum_mods.html',
    INFUSIONS.'forum_mods_online_panel/templates.php',
    INFUSIONS.'forum_threads_panel/templates/threads.html',
    INFUSIONS.'forum_threads_panel/templates.php',
    INFUSIONS.'latest_articles_panel/templates/latest_articles.html',
    INFUSIONS.'latest_articles_panel/templates.php',
    INFUSIONS.'latest_comments_panel/templates/latest_comments.html',
    INFUSIONS.'latest_comments_panel/templates.php',
    INFUSIONS.'latest_downloads_panel/templates/latest_downloads.html',
    INFUSIONS.'latest_downloads_panel/templates.php',
    INFUSIONS.'online_user_panel/templates/online_user_panel.html',
    INFUSIONS.'user_info_panel/templates/default.php',
    INFUSIONS.'gallery/css/',
    INFUSIONS.'gallery/templates/gallery.php',
    INFUSIONS.'member_poll_panel/templates/poll.html',
    INFUSIONS.'member_poll_panel/templates/poll.php',
    INFUSIONS.'news/templates/html/',
    INFUSIONS.'news/templates/news.php',
    INFUSIONS.'news/templates/submissions.php',
    INFUSIONS.'weblinks/templates/main_weblinks.html',
    INFUSIONS.'weblinks/templates/weblinks_info.html',
    INFUSIONS.'weblinks/templates/weblinks_submissions.html',
    INFUSIONS.'weblinks/templates.php',
    THEMES.'templates/global/contact.php',
    THEMES.'templates/global/css/',
    THEMES.'templates/global/tpl/',
    THEMES.'templates/global/custompage.php',
    THEMES.'templates/global/error.php',
    THEMES.'templates/global/groups.php',
    THEMES.'templates/global/homepage.php',
    THEMES.'templates/global/login.php',
    THEMES.'templates/global/maintenance.php',
    THEMES.'templates/global/members.php',
    THEMES.'templates/global/messages.php',
    THEMES.'templates/global/profile.php',
    THEMES.'templates/global/register.php',
    ADMIN.'includes/sitelinks/sitelinks.php',
    INCLUDES.'includes/dynamics/assets/switch/css/',
    INCLUDES.'includes/dynamics/assets/switch/js/',
];

foreach ($deleted_files as $file) {
    if (is_file($file)) {
        @unlink($file);
    } else {
        if (is_dir($file)) {
            rrmdir($file);
        }
    }
}
