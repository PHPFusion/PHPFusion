<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 0902.upgrade.inc
| Forum upgrade from Version 7.02.xx to 9.02
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
$locale = fusion_get_locale("",
    array(
        LOCALE.LOCALESET."setup.php",
        INFUSIONS."forum/locale/".LOCALESET."/forum_tags.php"
    )
);

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
$inf_mlt[] = array(
    "title"  => $locale['forums']['title'],
    "rights" => "FO",
);
$inf_mlt[] = array(
    "title"  => $locale['setup_3038'],
    "rights" => "FR",
);
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
	tag_language VARCHAR(100) NOT NULL DEFAULT '',
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
	mood_notify SMALLINT(4) NOT NULL DEFAULT '-101',
	mood_access SMALLINT(4) NOT NULL DEFAULT '-101',
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
if (!column_exists('forums', 'forum_branch')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_branch MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER forum_cat";
}
if (!column_exists('forums', 'forum_type')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_type TINYINT(1) NOT NULL DEFAULT '1' AFTER forum_name";
}
if (!column_exists('forums', 'forum_answer_threshold')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_answer_threshold TINYINT(3) NOT NULL DEFAULT '15' AFTER forum_type";
}
if (!column_exists('forums', 'forum_lock')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_lock TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_answer_threshold";
}
if (!column_exists('forums', 'forum_rules')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_rules TEXT NOT NULL AFTER forum_description";
}
if (!column_exists('forums', 'forum_language')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER forum_merge";
}
if (!column_exists('forums', 'forum_meta')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_meta TEXT NOT NULL AFTER forum_language";
}
if (!column_exists('forums', 'forum_alias')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_alias TEXT NOT NULL AFTER forum_meta";
}
if (!column_exists('forums', 'forum_allow_poll')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_allow_poll TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_reply";
}
if (!column_exists('forums', 'forum_image')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_image VARCHAR(100) NOT NULL DEFAULT '' AFTER forum_vote";
}
if (!column_exists('forums', 'forum_post_ratings')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_post_ratings TINYINT(4) NOT NULL DEFAULT '-101' AFTER forum_image";
}
if (!column_exists('forums', 'forum_users')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_users TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_post_ratings";
}
if (!column_exists('forums', 'forum_allow_attach')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_allow_attach TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_users";
}
if (!column_exists('forums', 'forum_quick_edit')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_quick_edit TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_attach_download";
}
if (!column_exists('forums', 'forum_lastpostid')) {
    $inf_altertable[] = $db_prefix."forums ADD forum_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER forum_quick_edit";
}
if (column_exists('forums', 'forum_moderators')) {
    $inf_altertable[] = $db_prefix."forums CHANGE forum_moderators forum_mods TEXT NOT NULL";
}
if (column_exists('forums', 'forum_access')) {
    $inf_altertable[] = $db_prefix."forums CHANGE forum_access forum_access TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists('forums', 'forum_reply')) {
    $inf_altertable[] = $db_prefix."forums CHANGE forum_reply forum_reply TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists('forums', 'forum_vote')) {
    $inf_altertable[] = $db_prefix."forums CHANGE forum_vote forum_vote TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists('forums', 'forum_poll')) {
    $inf_altertable[] = $db_prefix."forums CHANGE forum_poll forum_poll TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists('forums', 'forum_attach')) {
    $inf_altertable[] = $db_prefix."forums CHANGE forum_attach forum_attach TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists('forums', 'forum_attach_download')) {
    $inf_altertable[] = $db_prefix."forums CHANGE forum_attach_download forum_attach_download TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}

// Update existing records and change level accesses to new user groups values
$forum_index = dbquery_tree($db_prefix.'forums', 'forum_id', 'forum_cat');
$result = dbquery("SELECT forum_id, forum_cat, forum_type, forum_post, forum_access, forum_reply, forum_vote, forum_poll, forum_attach, forum_attach_download, forum_mods FROM ".$db_prefix."forums");
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        // Update new access levels for forum_post
        if ($data['forum_post']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_post ='-".$data['forum_post']."' WHERE forum_id='".$data['forum_id']."'";
        }
        // Update new access levels for forum_access
        if ($data['forum_access']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_access ='-".$data['forum_access']."' WHERE forum_id='".$data['forum_id']."'";
        }
        // Update new access levels for forum reply
        if ($data['forum_reply']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_reply ='-".$data['forum_reply']."' WHERE forum_id='".$data['forum_id']."'";
        }
        // Update new access levels for forum vote
        if ($data['forum_vote']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_vote ='-".$data['forum_vote']."' WHERE forum_id='".$data['forum_id']."'";
        }
        // Update new access levels for forum poll
        if ($data['forum_poll']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_poll ='-".$data['forum_poll']."' WHERE forum_id='".$data['forum_id']."'";
        }
        // Update new access levels for forum attach
        if ($data['forum_attach']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_attach ='-".$data['forum_attach']."' WHERE forum_id='".$data['forum_id']."'";
        }
        // Update new access levels for forum attach
        if ($data['forum_attach_download']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_attach_download ='-".$data['forum_attach_download']."' WHERE forum_id='".$data['forum_id']."'";
        }
        // Update forum_branch as base branch if forum_cat is 0
        if (!$data['forum_cat']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_branch='".$data['forum_id']."' WHERE forum_id='".$data['forum_id']."'";
        } else {
            $root = get_root($forum_index, $data['forum_id']);
            $inf_updatedbrow[] = $db_prefix."forums SET forum_branch='".$root."' WHERE forum_id='".$data['forum_id']."'";
        }

        // Update new access levels for forum moderators
        if (!empty($data['forum_mods'])) {
            $current_mods = array();
            $data['forum_mods'] = explode('.', $data['forum_mods']);
            foreach ($data['forum_mods'] as $mods) {
                switch ($mods) {
                    case '103':
                        $current_mods[] = USER_LEVEL_SUPER_ADMIN;
                        break;
                    case '102':
                        $current_mods[] = USER_LEVEL_ADMIN;
                        break;
                    case '101':
                        $current_mods[] = USER_LEVEL_PUBLIC;
                        break;
                    default:
                        $current_mods[] = $mods;
                }
            }
            $data['forum_mods'] = implode('.', $current_mods);
            $inf_updatedbrow[] = $db_prefix."forums SET forum_mods='".$data['forum_mods']."' WHERE forum_id='".$data['forum_id']."'";
        }
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
$select = dbquery("SELECT forum_id FROM ".$db_prefix."forums ORDER BY forum_id ASC");
if (dbrows($select)) {
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
    $inf_altertable[] = $db_prefix."forum_ranks ADD rank_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('localeset')."' AFTER rank_apply";
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
if (!column_exists('forum_threads', 'thread_tags')) {
    $inf_altertable[] = $db_prefix."forum_threads ADD thread_tags TEXT NOT NULL AFTER thread_id";
}
if (!column_exists('forum_threads', 'thread_tags_old')) {
    $inf_altertable[] = $db_prefix."forum_threads ADD thread_tags_old TEXT NOT NULL AFTER thread_tags";
}
if (!column_exists('forum_threads', 'thread_tags_change')) {
    $inf_altertable[] = $db_prefix."forum_threads ADD thread_tags_change INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_tags_old";
}
// Add thread answered to threads -- add column
if (!column_exists('forum_threads', 'thread_answered')) {
    $inf_altertable[] = $db_prefix."forum_threads ADD thread_answered TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_sticky";
}
/*
 * Modify Forum Attachment Table
 */
if (!column_exists('forum_attachments', 'attach_mime') && column_exists('forum_attachments', 'attach_ext')) {
    $inf_altertable[] = $db_prefix."forum_attachments CHANGE attach_ext attach_mime VARCHAR(20) NOT NULL DEFAULT ''";
}
// Clear old core settings if they are there regardless of current state
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_ips'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_attachmax'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_attachmax_count'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_attachtypes'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_notify'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_ranks'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_edit_lock'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_edit_timelimit'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_threads_timeframe'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_last_posts_reply'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_last_post_avatar'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_editpost_to_lastpost'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='threads_per_page'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='posts_per_page'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='numofthreads'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='forum_rank_style'";
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

// Add forum default thread tag //FORUM_LOCALE
if (db_exists(DB_FORUM_TAGS)) {
    if (!dbcount("(tag_title)", DB_FORUM_TAGS)) {
        $inf_insertdbrow[] = DB_FORUM_TAGS." (tag_title, tag_description, tag_color, tag_status, tag_language) VALUES ('".$locale['forum_tag_0110']."', '".$locale['forum_tag_0111']."', '#2e8c65', '1', '".fusion_get_settings('localset')."')";
    }
}

// Delete the old link
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='forum/index.php'";
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale("", [LOCALE.$language."/setup.php", INFUSIONS."forum/locale/".LOCALESET."/forum_tags.php"]);
        // Link
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3304']."', 'infusions/forum/index.php', '0', '2', '0', '5', '1', '".$language."')";

        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3324']."', 'infusions/forum/newthread.php', '0', '2', '-101', '1', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3319']."', 'infusions/forum/index.php?section=latest', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3320']."', 'infusions/forum/index.php?section=participated', '0', '2', '-101', '3', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3321']."', 'infusions/forum/index.php?section=tracked', '0', '2', '-101', '4', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3322']."', 'infusions/forum/index.php?section=unanswered', '0', '2', '0', '5', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3323']."', 'infusions/forum/index.php?section=unsolved', '0', '2', '0', '6', '1', '".$language."')";

        // Ranks
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3600']."', 'rank_super_admin.png', 0, '1', '-103', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3601']."', 'rank_admin.png', 0, '1', '-102', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3602']."', 'rank_mod.png', 0, '1', '-104', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3603']."', 'rank0.png', 0, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3604']."', 'rank1.png', 10, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3605']."', 'rank2.png', 50, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3606']."', 'rank3.png', 200, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3607']."', 'rank4.png', 500, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3608']."', 'rank5.png', 1000, '0', '-101', '".$language."')";

        // Tags
        $mlt_insertdbrow[$language][] = DB_FORUM_TAGS." (tag_title, tag_description, tag_color, tag_status, tag_language) VALUES ('".$locale['forum_tag_0110']."', '".$locale['forum_tag_0111']."', '#2e8c65', '1', '".$language."')";

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
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3304']."', 'infusions/forum/index.php', '0', '2', '0', '5', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('{last_id}', '".$locale['setup_3324']."', 'infusions/forum/newsthread.php', '0', '2', '-101', '1', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('{last_id}', '".$locale['setup_3319']."', 'infusions/forum/index.php?section=latest', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('{last_id}', '".$locale['setup_3319']."', 'infusions/forum/index.php?section=participated', '0', '2', '-101', '3', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('{last_id}', '".$locale['setup_3321']."', 'infusions/forum/index.php?section=tracked', '0', '2', '-101', '4', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('{last_id}', '".$locale['setup_3322']."', 'infusions/forum/index.php?section=unanswered', '0', '2', '0', '5', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('{last_id}', '".$locale['setup_3323']."', 'infusions/forum/index.php?section=unsolved', '0', '2', '0', '6', '1', '".LANGUAGE."')";
}

// Admin links
$inf_adminpanel[] = array(
    "image"  => $inf_image,
    "page"   => 1,
    "rights" => "F",
    "title"  => $locale['setup_3012'],
    "panel"  => "admin/forums.php"
);