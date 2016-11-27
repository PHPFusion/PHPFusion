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
$inf_version = "1.1";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "forum";
$inf_image = "forums.png";


/**
 * Forum Upgrades covering PHP-Fusion 7 to PHP-Fusion 9 RC4
 */

// Forum tables renaming
if (db_exists(DB_PREFIX."posts")) {
    dbquery("RENAME TABLE `".DB_PREFIX."posts` TO `".DB_PREFIX."forum_posts`");
}
if (db_exists(DB_PREFIX."threads")) {
    dbquery("RENAME TABLE `".DB_PREFIX."threads` TO `".DB_PREFIX."forum_threads`");
}
if (db_exists(DB_PREFIX."thread_notify")) {
    dbquery("RENAME TABLE `".DB_PREFIX."thread_notify` TO `".DB_PREFIX."forum_thread_notify`");
}

// Install a forum voting table
if (!db_exists(DB_FORUM_VOTES)) {
    $inf_newtable[] = DB_FORUM_VOTES." (
			forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			vote_points DECIMAL(3,0) NOT NULL DEFAULT '0',
			vote_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0'
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
}
// Install a new thread tags table
if (!db_exists(DB_FORUM_TAGS)) {
    $inf_newtable[] = DB_FORUM_TAGS." (
    tag_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	tag_title VARCHAR(100) NOT NULL DEFAULT '',
	tag_description VARCHAR(250) NOT NULL DEFAULT '',
	tag_color VARCHAR(20) NOT NULL DEFAULT '',
	tag_status SMALLINT(1) NOT NULL DEFAULT '0',
	tag_language VARCHAR(100) NOT NULL DEFAULT '',
	PRIMARY KEY (tag_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
}

// Install a new mood table
if (!db_exists(DB_FORUM_MOODS)) {
    $inf_newtable[] = DB_FORUM_MOODS." (
    mood_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	mood_name TEXT NOT NULL,
	mood_description TEXT NOT NULL,
	mood_icon VARCHAR(50) NOT NULL DEFAULT '',
	mood_notify SMALLINT(4) NOT NULL DEFAULT '-101',
	mood_access SMALLINT(4) NOT NULL DEFAULT '-101',
	mood_status SMALLINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (mood_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
}
// Install a new post notification table
if (!db_exists(DB_POST_NOTIFY)) {
    $inf_newtable[] = DB_POST_NOTIFY." (
    post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_mood_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_sender MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
	KEY notify_datestamp (notify_datestamp)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
}

// Add multilingual support to ranks
if (!column_exists(DB_FORUM_RANKS, 'rank_language')) {
    $inf_altertable[] = DB_FORUM_RANKS." ADD rank_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('localeset')."' AFTER rank_apply";
}

// Add thread answered to threads -- add column
if (!column_exists(DB_FORUM_THREADS, 'thread_answered')) {
    $inf_altertable[] = DB_FORUM_THREADS." ADD thread_answered TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_sticky";
}
// Rename of extension to mime
if (!column_exists(DB_FORUM_ATTACHMENTS, 'attach_mime') && column_exists(DB_FORUM_ATTACHMENTS, 'attach_ext')) {
    $inf_altertable[] = DB_FORUM_ATTACHMENTS." CHANGE attach_ext attach_mime VARCHAR(20) NOT NULL DEFAULT ''";
}
if (!column_exists(DB_FORUMS, 'forum_branch')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_branch MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER forum_cat";
}

if (!column_exists(DB_FORUMS, 'forum_type')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_type TINYINT(1) NOT NULL DEFAULT '1' AFTER forum_name";
}
if (!column_exists(DB_FORUMS, 'forum_answer_threshold')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_answer_threshold TINYINT(3) NOT NULL DEFAULT '15' AFTER forum_type";
}
if (!column_exists(DB_FORUMS, 'forum_lock')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_lock TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_answer_threshold";
}
if (!column_exists(DB_FORUMS, 'forum_rules')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_rules TEXT NOT NULL AFTER forum_description";
}
if (!column_exists(DB_FORUMS, 'forum_language')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER forum_merge";
}
if (!column_exists(DB_FORUMS, 'forum_meta')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_meta TEXT NOT NULL AFTER forum_language";
}
if (!column_exists(DB_FORUMS, 'forum_alias')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_alias TEXT NOT NULL AFTER forum_meta";
}
if (!column_exists(DB_FORUMS, 'forum_allow_poll')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_allow_poll TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_reply";
}
if (!column_exists(DB_FORUMS, 'forum_image')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_image VARCHAR(100) NOT NULL DEFAULT '' AFTER forum_vote";
}
if (!column_exists(DB_FORUMS, 'forum_post_ratings')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_post_ratings TINYINT(4) NOT NULL DEFAULT '-101' AFTER forum_image";
}
if (!column_exists(DB_FORUMS, 'forum_users')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_users TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_post_ratings";
}
if (!column_exists(DB_FORUMS, 'forum_allow_attach')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_allow_attach TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_users";
}
if (!column_exists(DB_FORUMS, 'forum_quick_edit')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_quick_edit TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_attach_download";
}
if (!column_exists(DB_FORUMS, 'forum_lastpostid')) {
    $inf_altertable[] = DB_FORUMS." ADD forum_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER forum_quick_edit";
}

// Change Attributes to New User Groups Level
if (column_exists(DB_FORUMS, 'forum_moderators')) {
    $inf_altertable[] = DB_FORUMS." CHANGE forum_moderators forum_mods TEXT NOT NULL";
}
if (column_exists(DB_FORUM_RANKS, 'rank_apply')) {
    $inf_altertable[] = DB_FORUM_RANKS." CHANGE rank_apply rank_apply TINYINT(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists(DB_FORUMS, 'forum_access')) {
    $inf_altertable[] = DB_FORUMS." CHANGE forum_access forum_access TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists(DB_FORUMS, 'forum_reply')) {
    $inf_altertable[] = DB_FORUMS." CHANGE forum_reply forum_reply TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists(DB_FORUMS, 'forum_vote')) {
    $inf_altertable[] = DB_FORUMS." CHANGE forum_vote forum_vote TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists(DB_FORUMS, 'forum_poll')) {
    $inf_altertable[] = DB_FORUMS." CHANGE forum_poll forum_poll TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists(DB_FORUMS, 'forum_attach')) {
    $inf_altertable[] = DB_FORUMS." CHANGE forum_attach forum_attach TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}
if (column_exists(DB_FORUMS, 'forum_attach_download')) {
    $inf_altertable[] = DB_FORUMS." CHANGE forum_attach_download forum_attach_download TINYINT(4) DEFAULT '".USER_LEVEL_MEMBER."'";
}



// Update existing records and change level accesses to new user groups values
$result = dbquery("SELECT forum_id, forum_access, forum_reply, forum_vote, forum_poll, forum_attach, forum_attach_download FROM ".DB_FORUMS);
if (dbrows($result) > 0) {

    while ($data = dbarray($result)) {

        // Update new access levels for forum_access
        if ($data['forum_access']) {
            $inf_updatedbrows[] = DB_FORUMS." SET forum_access ='-".$data['forum_access']."' WHERE forum_id='".$data['forum_id']."'";
        }

        // Update new access levels for forum reply
        if ($data['forum_reply']) {
            $inf_updatedbrows[] = DB_FORUMS." SET forum_reply ='-".$data['forum_reply']."' WHERE forum_id='".$data['forum_id']."'";
        }

        // Update new access levels for forum vote
        if ($data['forum_vote']) {
            $inf_updatedbrows[] = DB_FORUMS." SET forum_vote ='-".$data['forum_vote']."' WHERE forum_id='".$data['forum_id']."'";
        }

        // Update new access levels for forum poll
        if ($data['forum_poll']) {
            $inf_updatedbrows[] = DB_FORUMS." SET forum_poll ='-".$data['forum_poll']."' WHERE forum_id='".$data['forum_id']."'";
        }

        // Update new access levels for forum attach
        if ($data['forum_attach']) {
            $inf_updatedbrows[] = DB_FORUMS." SET forum_attach ='-".$data['forum_attach']."' WHERE forum_id='".$data['forum_id']."'";
        }
        // Update new access levels for forum attach
        if ($data['forum_attach_download']) {
            $inf_updatedbrows[] = DB_FORUMS." SET forum_attach_download ='-".$data['forum_attach_download']."' WHERE forum_id='".$data['forum_id']."'";
        }
    }

    /*
     * After upgrade all forums are categories by default
     * Change old forums already inside a group to be a forum containing threads
     * This makes all existing threads accessible both in forums and in panels after upgrade
     */

    $inf_updatedbrows[] = DB_FORUMS." SET forum_type = 2 WHERE forum_cat != 0";
}

// Modify All Rank Levels
$result = dbquery("SELECT rank_id, rank_apply FROM ".DB_FORUM_RANKS);
if (dbrows($result) > 0) {
    while ($data = dbarray($result)) {
        $inf_updatedbrows[] = DB_FORUM_RANKS." SET rank_apply ='-".$data['rank_apply']."' WHERE rank_id='".$data['rank_id']."'";
    }
}


// Clear old core settings if they are there regardless of current state
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_ips'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_attachmax'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_attachmax_count'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_attachtypes'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_notify'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_ranks'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_edit_lock'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_edit_timelimit'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_threads_timeframe'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_last_posts_reply'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_last_post_avatar'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_editpost_to_lastpost'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='threads_per_page'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='posts_per_page'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='numofthreads'";
$inf_deldbrows[] = DB_SETTINGS." WHERE settings_name='forum_rank_style'";

$inf_deldbrows[] = DB_PREFIX."admin WHERE admin_link='settings_forum.php'";
$inf_deldbrows[] = DB_PREFIX."admin WHERE admin_link='forum_ranks.php'";

// Insert new and old settings tables to Infusions table
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ips', '-103', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax', '1000000', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax_count', '5', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachtypes', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thread_notify', '1', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ranks', '1', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_lock', '0', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_timelimit', '0', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('popular_threads_timeframe', '604800', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_posts_reply', '1', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_post_avatar', '1', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_editpost_to_lastpost', '1', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('threads_per_page', '20', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('posts_per_page', '20', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('numofthreads', '16', 'forum')";
$inf_insertdbrows[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_rank_style', '0', 'forum')";

// Add forum default thread tag //FORUM_LOCALE
$inf_insertdbrows[] = DB_FORUM_TAGS." (tag_title, tag_description, tag_color, tag_status, tag_language) VALUES ('".$locale['forum_tag_0110']."', '".$locale['forum_tag_0111']."', '#2e8c65', '1', '".fusion_get_settings('localset')."')";

// Admin links
$inf_adminpanel[] = array(
    "image" => $inf_image,
    "page" => 1,
    "rights" => "F",
    "title" => $locale['setup_3012'],
    "panel" => "admin/forums.php"
);