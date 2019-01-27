<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 1.0.5.upgrade.inc
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
$locale = fusion_get_locale("", [
    LOCALE.LOCALESET."setup.php",
    INFUSIONS."forum/locale/".LOCALESET."/forum_tags.php"
]);

//$locale = \defender::sanitize_array($locale);

// Infusion general information
$inf_title = $locale['forums']['title'];
$inf_description = $locale['forums']['description'];
$inf_version = "1.0.5";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "forum";
$inf_image = "forums.svg";

// Multilanguage table for Administration
$inf_mlt[] = [
    "title"  => $locale['forums']['title'],
    "rights" => "FO",
];
$inf_mlt[] = [
    "title"  => $locale['setup_3038'],
    "rights" => "FR",
];
/**
 * Forum Upgrades covering PHP-Fusion 7 to PHP-Fusion 9 RC4
 */
// Forum tables renaming
if (db_exists($db_prefix."posts")) {
    dbquery("RENAME TABLE `".$db_prefix."posts` TO `".$db_prefix."forum_posts`");
}
if (db_exists($db_prefix."threads")) {
    dbquery("RENAME TABLE `".$db_prefix."threads` TO `".$db_prefix."forum_threads`");
}
if (db_exists($db_prefix."thread_notify")) {
    dbquery("RENAME TABLE `".$db_prefix."thread_notify` TO `".$db_prefix."forum_thread_notify`");
}

// Install a forum voting table
if (!db_exists($db_prefix.'forum_votes')) {
    $table_query = $db_prefix."forum_votes (
    forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    vote_points DECIMAL(3,0) NOT NULL DEFAULT '0',
    vote_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
    dbquery("CREATE TABLE ".$table_query);
}
// Install a new thread tags table
if (!db_exists($db_prefix.'forum_thread_tags')) {
    $table_query = $db_prefix."forum_thread_tags (
    tag_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    tag_title VARCHAR(100) NOT NULL DEFAULT '',
    tag_description VARCHAR(250) NOT NULL DEFAULT '',
    tag_color VARCHAR(20) NOT NULL DEFAULT '',
    tag_status SMALLINT(1) NOT NULL DEFAULT '0',
    tag_language VARCHAR(100) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (tag_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
    dbquery("CREATE TABLE ".$table_query);
}

// Install a new mood table
if (!db_exists($db_prefix."forum_post_mood")) {
    $table_query = $db_prefix."forum_post_mood (
    mood_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    mood_name TEXT NOT NULL,
    mood_description TEXT NOT NULL,
    mood_icon VARCHAR(50) NOT NULL DEFAULT '',
    mood_notify SMALLINT(4) NOT NULL DEFAULT ".USER_LEVEL_MEMBER.",
    mood_access SMALLINT(4) NOT NULL DEFAULT ".USER_LEVEL_MEMBER.",
    mood_status SMALLINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (mood_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
    dbquery("CREATE TABLE ".$table_query);
}

// Install a new post notification table
if (!db_exists($db_prefix."forum_post_notify")) {
    $table_query = $db_prefix."forum_post_notify (
    post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_mood_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_sender MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
    KEY notify_datestamp (notify_datestamp)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
    dbquery("CREATE TABLE ".$table_query);
}

/*
 * Modify Forum Table
 */
$check_forums = [
    'forum_branch'           => " ADD forum_branch MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER forum_cat",
    'forum_type'             => " ADD forum_type TINYINT(1) NOT NULL DEFAULT '1' AFTER forum_name",
    'forum_answer_threshold' => " ADD forum_answer_threshold TINYINT(3) NOT NULL DEFAULT '15' AFTER forum_type",
    'forum_lock'             => " ADD forum_lock TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_answer_threshold",
    'forum_rules'            => " ADD forum_rules TEXT NOT NULL AFTER forum_description",
    'forum_language'         => " ADD forum_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER forum_merge",
    'forum_meta'             => " ADD forum_meta TEXT NOT NULL AFTER forum_language",
    'forum_alias'            => " ADD forum_alias TEXT NOT NULL AFTER forum_meta",
    'forum_allow_poll'       => " ADD forum_allow_poll TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_reply",
    'forum_image'            => " ADD forum_image VARCHAR(100) NOT NULL DEFAULT '' AFTER forum_vote",
    'forum_post_ratings'     => " ADD forum_post_ratings TINYINT(4) NOT NULL DEFAULT ".USER_LEVEL_MEMBER." AFTER forum_image",
    'forum_users'            => " ADD forum_users TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_post_ratings",
    'forum_allow_attach'     => " ADD forum_allow_attach TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_users",
    'forum_quick_edit'       => " ADD forum_quick_edit TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_attach_download",
    'forum_lastpostid'       => " ADD forum_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER forum_quick_edit"
];

foreach ($check_forums as $key => $value) {
    if (!column_exists(DB_FORUMS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUMS.$value;
    }
}

$change_forums = [
    'forum_moderators'      => " CHANGE forum_moderators forum_mods TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_access'          => " CHANGE forum_access forum_access TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_reply'           => " CHANGE forum_reply forum_reply TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_vote'            => " CHANGE forum_vote forum_vote TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_poll'            => " CHANGE forum_poll forum_poll TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_attach'          => " CHANGE forum_attach forum_attach TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_attach_download' => " CHANGE forum_attach_download forum_attach_download TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'"
];

foreach ($change_forums as $key => $value) {
    if (column_exists(DB_FORUMS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUMS.$value;
    }
}

// Update existing records and change level accesses to new user groups values
$forum_index = dbquery_tree($db_prefix.'forums', 'forum_id', 'forum_cat');
$result = dbquery("SELECT * FROM ".$db_prefix."forums");
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        // Update new access levels for forum_post
        if ($data['forum_post']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_post ='-".$data['forum_post']."' WHERE forum_id='".$data['forum_id']."' AND forum_post != 0";
        }
        // Update new access levels for forum_access
        if ($data['forum_access']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_access ='-".$data['forum_access']."' WHERE forum_id='".$data['forum_id']."' AND forum_access != 0";
        }
        // Update new access levels for forum reply
        if ($data['forum_reply']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_reply ='-".$data['forum_reply']."' WHERE forum_id='".$data['forum_id']."' AND forum_reply != 0";
        }
        // Update new access levels for forum vote
        if ($data['forum_vote']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_vote ='-".$data['forum_vote']."' WHERE forum_id='".$data['forum_id']."' AND forum_vote != 0";
        }
        // Update new access levels for forum poll
        if ($data['forum_poll']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_poll ='-".$data['forum_poll']."' WHERE forum_id='".$data['forum_id']."' AND forum_poll != 0";
        }
        // Update new access levels for forum attach
        if ($data['forum_attach']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_attach ='-".$data['forum_attach']."' WHERE forum_id='".$data['forum_id']."' AND forum_attach != 0";
        }
        // Update new access levels for forum attach
        if ($data['forum_attach_download']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_attach_download ='-".$data['forum_attach_download']."' WHERE forum_id='".$data['forum_id']."' AND forum_attach_download != 0";
        }
        // Update forum_branch as base branch if forum_cat is 0
        if (!$data['forum_cat']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_branch='".$data['forum_id']."' WHERE forum_id='".$data['forum_id']."'";
        } else {
            $root = get_root($forum_index, $data['forum_id']);
            $inf_updatedbrow[] = $db_prefix."forums SET forum_branch='".$root."' WHERE forum_id='".$data['forum_id']."'";
        }

        // Update new access levels for forum moderators
    }

    /*
     * After upgrade all forums are categories by default
     * Change old forums already inside a group to be a forum containing threads
     * This makes all existing threads accessible both in forums and in panels after upgrade
     */
    $inf_updatedbrow[] = $db_prefix."forums SET forum_type='1' WHERE forum_cat='0'";
    $inf_updatedbrow[] = $db_prefix."forums SET forum_type='2' WHERE forum_cat>'0'";
}

// Update the forums with lastpost information
$select = dbquery("SELECT * FROM ".$db_prefix."forums ORDER BY forum_id ASC");
if (dbrows($select) > 0) {
    while ($data = dbarray($select)) {
        $select2 = dbquery("SELECT * FROM ".$db_prefix."forum_threads WHERE forum_id='".$data['forum_id']."' ORDER BY thread_lastpost DESC LIMIT 1");
        if (dbrows($select2)) {
            while ($subData = dbarray($select2)) {
                $inf_updatedbrow[] = $db_prefix."forums SET forum_lastpost='".$subData['thread_lastpost']."', forum_lastpostid='".$subData['thread_lastpostid']."', forum_lastuser='".$subData['thread_lastuser']."' WHERE forum_id='".$subData['forum_id']."'";
            }
        }
    }
}

/*
 * Modify Forum Ranks Table
 */
if (column_exists('forum_ranks', 'rank_apply')) {
    $inf_altertable[] = $db_prefix."forum_ranks CHANGE rank_apply rank_apply TINYINT(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'";
}
// Add multilingual support to ranks
if (!column_exists('forum_ranks', 'rank_language')) {
    $inf_altertable[] = $db_prefix."forum_ranks ADD rank_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER rank_apply";
}
// Modify All Rank Levels
$result = dbquery("SELECT rank_id, rank_apply FROM ".$db_prefix."forum_ranks");
if (dbrows($result) > 0) {
    while ($data = dbarray($result)) {
        $inf_updatedbrow[] = $db_prefix."forum_ranks SET rank_apply ='-".$data['rank_apply']."' WHERE rank_id='".$data['rank_id']."'";
    }
}
/*
 * Modify Forum Threads Table
 */
$forum_threads = [
    'thread_tags'        => " ADD thread_tags TEXT NOT NULL AFTER thread_id",
    'thread_tags_old'    => " ADD thread_tags_old TEXT NOT NULL AFTER thread_tags",
    'thread_tags_change' => " ADD thread_tags_change INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_tags_old",
    'thread_answered'    => " ADD thread_answered TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_sticky"
];

foreach ($forum_threads as $key => $value) {
    if (!column_exists(DB_FORUM_THREADS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUM_THREADS.$value;
    }
}
/*
 * Modify Forum Attachment Table
 */
if (!column_exists('forum_attachments', 'attach_mime') && column_exists('forum_attachments', 'attach_ext')) {
    $inf_altertable[] = $db_prefix."forum_attachments CHANGE attach_ext attach_mime VARCHAR(20) NOT NULL DEFAULT ''";
}
// Clear old core settings if they are there regardless of current state
$settings = fusion_get_settings();
$del_forumsettings = [
    0  => 'forum_ips',
    1  => 'forum_attachmax',
    2  => 'forum_attachmax_count',
    3  => 'forum_attachtypes',
    4  => 'forum_notify',
    5  => 'forum_ranks',
    6  => 'forum_edit_lock',
    7  => 'forum_edit_timelimit',
    8  => 'forum_threads_timeframe',
    9  => 'forum_last_posts_reply',
    10 => 'forum_last_post_avatar',
    11 => 'forum_editpost_to_lastpost',
    12 => 'threads_per_page',
    13 => 'posts_per_page',
    14 => 'numofthreads',
    15 => 'forum_rank_style'
];

foreach ($del_forumsettings as $key => $value) {
    if (isset($settings[$value])) {
        $inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='$value'";;
    }
}

$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='settings_forum.php'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='forum_ranks.php'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='forums.php'";
// Insert new and old settings tables to Infusions table
$check_array = [
    'forum_ips'                  => -103,
    'forum_attachmax'            => 1000000,
    'forum_attachmax_count'      => 5,
    'forum_attachtypes'          => '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z',
    'thread_notify'              => 1,
    'forum_ranks'                => 1,
    'forum_edit_lock'            => 0,
    'forum_edit_timelimit'       => 0,
    'popular_threads_timeframe'  => 604800,
    'forum_last_posts_reply'     => 1,
    'forum_last_post_avatar'     => 1,
    'forum_editpost_to_lastpost' => 1,
    'threads_per_page'           => 20,
    'posts_per_page'             => 20,
    'numofthreads'               => 16,
    'forum_rank_style'           => 0,
];
foreach ($check_array as $name => $val) {
    if (!dbcount("(settings_name)", DB_SETTINGS_INF, "settings_name=:col_name", [':col_name' => $name])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$name', '$val', 'forum')";
    }
}

/*// Add forum default thread tag //FORUM_LOCALE
if (db_exists(DB_FORUM_TAGS)) {
    if (!dbcount("(tag_title)", DB_FORUM_TAGS)) {
        $inf_insertdbrow[] = DB_FORUM_TAGS." (tag_title, tag_description, tag_color, tag_status, tag_language) VALUES ('".$locale['forum_tag_0110']."', '".$locale['forum_tag_0111']."', '#2e8c65', '1', '".fusion_get_settings('locale')."')";
    }
}
*/

// Delete the old link
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='forum/index.php'";

$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale("", [LOCALE.$language."/setup.php", INFUSIONS."forum/locale/".$language."/forum_tags.php"]);

        $sl_name_check = [
            $locale['setup_3304'] => DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3304']."', 'infusions/forum/index.php', '0', '2', '0', '5', '1', '".$language."')",
            $locale['setup_3324'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3324']."', 'infusions/forum/newthread.php', ".USER_LEVEL_MEMBER.", '2', '0', '1', '1', '".$language."')",
            $locale['setup_3319'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3319']."', 'infusions/forum/index.php?section=latest', '0', '2', '0', '2', '1', '".$language."')",
            $locale['setup_3320'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3320']."', 'infusions/forum/index.php?section=participated', ".USER_LEVEL_MEMBER.", '2', '0', '3', '1', '".$language."')",
            $locale['setup_3321'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3321']."', 'infusions/forum/index.php?section=tracked', ".USER_LEVEL_MEMBER.", '2', '0', '4', '1', '".$language."')",
            $locale['setup_3322'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3322']."', 'infusions/forum/index.php?section=unanswered', '0', '2', '0', '5', '1', '".$language."')",
            $locale['setup_3323'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3323']."', 'infusions/forum/index.php?section=unsolved', '0', '2', '0', '6', '1', '".$language."')"
        ];

        foreach ($sl_name_check as $link_name => $_sql) {
            if (!dbcount("(link_id)", DB_SITE_LINKS, "link_name='".$link_name."'")) {
                $mlt_insertdbrow[$language][] = $_sql;
            }
        }
        // Ranks
        $rank_name_check = [
            $locale['setup_3600'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3600']."', 'rank_super_admin.png', 0, '1', ".USER_LEVEL_SUPER_ADMIN.", '".$language."')",
            $locale['setup_3601'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3601']."', 'rank_admin.png', 0, '1', ".USER_LEVEL_ADMIN.", '".$language."')",
            $locale['setup_3602'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3602']."', 'rank_mod.png', 0, '1', '-104', '".$language."')",
            $locale['setup_3603'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3603']."', 'rank0.png', 0, '0', ".USER_LEVEL_MEMBER.", '".$language."')",
            $locale['setup_3604'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3604']."', 'rank1.png', 10, '0', ".USER_LEVEL_MEMBER.", '".$language."')",
            $locale['setup_3605'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3605']."', 'rank2.png', 50, '0', ".USER_LEVEL_MEMBER.", '".$language."')",
            $locale['setup_3606'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3606']."', 'rank3.png', 200, '0', ".USER_LEVEL_MEMBER.", '".$language."')",
            $locale['setup_3607'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3607']."', 'rank4.png', 500, '0', ".USER_LEVEL_MEMBER.", '".$language."')",
            $locale['setup_3608'] => DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3608']."', 'rank5.png', 1000, '0', ".USER_LEVEL_MEMBER.", '".$language."')",
        ];
        foreach ($rank_name_check as $rank_name => $_sql) {
            if (!dbcount("(rank_id)", DB_FORUM_RANKS, "rank_title='".$rank_name."'")) {
                $mlt_insertdbrow[$language][] = $_sql;
            }
        }

        // Tags
        if (!dbcount("(tag_id)", DB_FORUM_TAGS, "tag_title='".$locale['forum_tag_0110']."'")) {
            $mlt_insertdbrow[$language][] = DB_FORUM_TAGS." (tag_title, tag_description, tag_color, tag_status, tag_language) VALUES ('".$locale['forum_tag_0110']."', '".$locale['forum_tag_0111']."', '#2e8c65', '1', '".$language."')";
        }

        // Delete rows
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/newthread.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=latest' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=participated' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=tracked' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=unanswered' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=unsolved' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_FORUMS." WHERE forum_language='".$language."'"; // associated thread also need to be deprecated. Bug. unless register everything.
        $mlt_deldbrow[$language][] = DB_FORUM_RANKS." WHERE rank_language='".$language."'";
    }
} else {
    $sl_name_check = [
        $locale['setup_3304'] => DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3304']."', 'infusions/forum/index.php', '0', '2', '0', '5', '1', '".LANGUAGE."')",
        $locale['setup_3324'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3324']."', 'infusions/forum/newthread.php', ".USER_LEVEL_MEMBER.", '2', '0', '1', '1', '".LANGUAGE."')",
        $locale['setup_3319'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3319']."', 'infusions/forum/index.php?section=latest', '0', '2', '0', '2', '1', '".LANGUAGE."')",
        $locale['setup_3320'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3320']."', 'infusions/forum/index.php?section=participated', ".USER_LEVEL_MEMBER.", '2', '0', '3', '1', '".LANGUAGE."')",
        $locale['setup_3321'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3321']."', 'infusions/forum/index.php?section=tracked', ".USER_LEVEL_MEMBER.", '2', '0', '4', '1', '".LANGUAGE."')",
        $locale['setup_3322'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3322']."', 'infusions/forum/index.php?section=unanswered', '0', '2', '0', '5', '1', '".LANGUAGE."')",
        $locale['setup_3323'] => DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3323']."', 'infusions/forum/index.php?section=unsolved', '0', '2', '0', '6', '1', '".LANGUAGE."')"
    ];

    foreach ($sl_name_check as $link_name => $_sql) {
        if (!dbcount("(link_id)", DB_SITE_LINKS, "link_name='".$link_name."'")) {
            $inf_insertdbrow[] = $_sql;
        }
    }
}

// Admin links
$inf_adminpanel[] = [
    "image"  => $inf_image,
    "page"   => 1,
    "rights" => "F",
    "title"  => $locale['setup_3012'],
    "panel"  => "admin/forums.php"
];

// Repairs manually linked forum image attachments to new updated paths
if (db_exists(DB_FORUM_POSTS)) {
    if (!function_exists("forum_img_replace_callback")) {
        function forum_img_replace_callback($matches) {
            if (substr($matches[3], -1, 1) != "/") {
                if (!stristr($matches[3], 'infusions/forum')) {
                    $matches[3] = str_replace('forum', 'infusions/forum', $matches[3]);
                }

                return "[img]".$matches[1].str_replace(["?", "&amp;", "&", "="], "", $matches[3]).$matches[4]."[/img]";
            } else {
                return $matches[0];
            }
        }
    }
    $regex = '#\[img\]((http|ftp|https|ftps)://|/)(.*?)(\.(jpg|jpeg|gif|png|JPG|JPEG|GIF|PNG))\[/img\]#si';
    $result = dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE post_message LIKE '%[img]".fusion_get_settings('siteurl')."%' ORDER BY post_id ASC");
    if (dbrows($result)) {
        $i = 1;
        while ($data = dbarray($result)) {
            $data['post_message'] = preg_replace_callback($regex, "forum_img_replace_callback", $data['post_message']);
            dbquery("UPDATE ".DB_FORUM_POSTS." SET post_message=:post WHERE post_id=:post_id", [':post' => $data['post_message'], ':post_id' => $data['post_id']]);
            $i++;
        }
    }
}
// Move all forum attachments
include_once BASEDIR.'upgrade/upgrade_functions_include.php';
if (file_exists(BASEDIR."forum/attachments/")) {
    $attachment_files = makefilelist(BASEDIR."forum/attachments/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(BASEDIR."forum/attachments/".$file, INFUSIONS."forum/attachments/".$file);
    }

    // Remove the whole old dir including rouge files
    rrmdir(BASEDIR.'forum/attachments');
}

if (file_exists(IMAGES."ranks/")) {
    $attachment_files = makefilelist(IMAGES."ranks/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(IMAGES."ranks/".$file, INFUSIONS."forum/ranks/".$file);
    }

    // Remove the whole old dir including rouge files
    rrmdir(IMAGES.'ranks/');
}

rrmdir(BASEDIR.'forum');
