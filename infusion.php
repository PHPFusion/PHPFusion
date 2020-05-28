<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/infusion.php
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
defined('IN_FUSION') || exit;


//@todo: ALTER TABLE `xxxx_forum_posts` ADD FULLTEXT(`post_message`); on new infusion API

$locale = fusion_get_locale('', [
    LOCALE.LOCALESET."setup.php",
    INFUSIONS."forum/locale/".LOCALESET."forum.php",
    INFUSIONS."forum/locale/".LOCALESET."/forum_tags.php"
]);

// Infusion general information
$inf_title = $locale['forums']['title'];
$inf_description = $locale['forums']['description'];
$inf_version = '3.0';
$inf_developer = 'PHP Fusion Development Team';
$inf_email = 'info@php-fusion.co.uk';
$inf_weburl = 'https://www.php-fusion.co.uk';
$inf_folder = 'forum';
$inf_image = 'forums.svg';

// Moderator Discussions
\PHPFusion\Admins::getInstance()->setAdminPageIcons("FR", "<i class='admin-ico fas fa-comment-alt fa-fw'></i>");
\PHPFusion\Admins::getInstance()->setCommentType("FR", $locale['forum_0667']);
\PHPFusion\Admins::getInstance()->setLinkType("FR", fusion_get_settings("siteurl")."infusions/forum/index.php?ref=news/news.php?readmore=moderator&amp;id=%s");

// Create tables
$inf[DB_FORUM_ATTACHMENTS] = [
    'attach_id'      => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 1, 'auto_increment' => TRUE],
    'thread_id'      => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'post_id'        => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'post_user'      => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'attach_name'    => ['type' => 'VARCHAR', 'length' => 150],
    'attach_t1_name' => ['type' => 'VARCHAR', 'length' => 150],
    'attach_t2_name' => ['type' => 'VARCHAR', 'length' => 150],
    'attach_t3_name' => ['type' => 'VARCHAR', 'length' => 150],
    'attach_t4_name' => ['type' => 'VARCHAR', 'length' => 150],
    'attach_mime'    => ['type' => 'VARCHAR', 'length' => 20],
    'attach_size'    => ['type' => 'INT', 'length' => 20, 'unsigned' => TRUE],
    'attach_count'   => ['type' => 'INT', 'length' => 10, 'unsigned' => TRUE],
];

$inf_newtable[] = DB_FORUM_VOTES." (
    vote_id MEDIUMINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    vote_points DECIMAL(3,0) NOT NULL DEFAULT '0',
    vote_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (vote_id),
    KEY forum_id (forum_id),
    KEY thread_id (thread_id),
    KEY post_id (post_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_RANKS." (
    rank_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    rank_title VARCHAR(100) NOT NULL DEFAULT '',
    rank_image VARCHAR(100) NOT NULL DEFAULT '',
    rank_posts iNT(10) UNSIGNED NOT NULL DEFAULT '0',
    rank_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    rank_apply TINYINT(4) DEFAULT ".USER_LEVEL_MEMBER.",
    rank_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (rank_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_POLL_OPTIONS." (
    thread_id MEDIUMINT(8) unsigned NOT NULL,
    forum_poll_option_id SMALLINT(5) UNSIGNED NOT NULL,
    forum_poll_option_text VARCHAR(150) NOT NULL,
    forum_poll_option_votes SMALLINT(5) UNSIGNED NOT NULL,
    KEY thread_id (thread_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_POLL_VOTERS." (
    thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
    forum_vote_user_id MEDIUMINT(8) UNSIGNED NOT NULL,
    forum_vote_user_ip VARCHAR(45) NOT NULL,
    forum_vote_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
    KEY thread_id (thread_id),
    KEY forum_vote_user_id (forum_vote_user_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_POLLS." (
    thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
    forum_poll_title VARCHAR(250) NOT NULL,
    forum_poll_start INT(10) UNSIGNED DEFAULT NULL,
    forum_poll_length iNT(10) UNSIGNED NOT NULL,
    forum_poll_votes SMALLINT(5) unsigned NOT NULL,
    KEY thread_id (thread_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf[DB_FORUMS] = [
    'forum_id'               => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 1, 'auto_increment' => TRUE],
    'forum_cat'              => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'forum_branch'           => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'forum_name'             => ['type' => 'VARCHAR', 'length' => 70, 'key' => 2],
    'forum_meta'             => ['type' => 'TEXT'],
    'forum_language'         => ['type' => 'VARCHAR', 'length' => 70, 'default' => LANGUAGE],
    'forum_alias'            => ['type' => 'VARCHAR', 'length' => 100],
    'forum_image'            => ['type' => 'VARCHAR', 'length' => 200],
    'forum_type'             => ['type' => 'TINYINT', 'length' => 1, 'default' => 1],
    'forum_answer_threshold' => ['type' => 'TINYINT', 'length' => 3, 'default' => 15, 'unsigned' => TRUE],
    'forum_lock'             => ['type' => 'TINYINT', 'length' => 1],
    'forum_order'            => ['type' => 'SMALLINT', 'length' => 5, 'unsigned' => TRUE, 'key' => 2],
    'forum_description'      => ['type' => 'TEXT'],
    'forum_rules'            => ['type' => 'TEXT'],
    'forum_mods'             => ['type' => 'TEXT'],
    'forum_access'           => ['type' => 'TINYINT', 'length' => 4],
    'forum_users'            => ['type' => 'TINYINT', 'length' => 1],
    'forum_merge'            => ['type' => 'TINYINT', 'length' => 1],
    'forum_quick_edit'       => ['type' => 'TINYINT', 'length' => 1],
    'forum_allow_comments'   => ['type' => 'TINYINT', 'length' => 1],
    'forum_allow_attach'     => ['type' => 'TINYINT', 'length' => 1],
    'forum_post'             => ['type' => 'TINYINT', 'length' => 4, 'default' => USER_LEVEL_MEMBER],
    'forum_reply'            => ['type' => 'TINYINT', 'length' => 4, 'default' => USER_LEVEL_MEMBER],
    'forum_poll'             => ['type' => 'TINYINT', 'length' => 4, 'default' => USER_LEVEL_MEMBER],
    'forum_allow_poll'       => ['type' => 'TINYINT', 'length' => 1],
    'forum_vote'             => ['type' => 'TINYINT', 'length' => 4, 'default' => USER_LEVEL_MEMBER],
    'forum_post_ratings'     => ['type' => 'TINYINT', 'length' => 4, 'default' => USER_LEVEL_MEMBER],
    'forum_attach'           => ['type' => 'TINYINT', 'length' => 4, 'default' => USER_LEVEL_MEMBER],
    'forum_attach_download'  => ['type' => 'TINYINT', 'length' => 4, 'default' => USER_LEVEL_MEMBER],
    'forum_lastpostid'       => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'forum_lastuser'         => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'forum_lastpost'         => ['type' => 'INT', 'length' => 10, 'unsigned' => TRUE, 'key' => 2],
    'forum_postcount'        => ['type' => 'MEDIUMINT', 'length' => 10, 'unsigned' => TRUE],
    'forum_threadcount'      => ['type' => 'MEDIUMINT', 'length' => 10, 'unsigned' => TRUE],
    'forum_show_postcount'   => ['type' => 'TINYINT', 'length' => 1],
];
$inf[DB_FORUM_POSTS] = [
    'forum_id'        => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'thread_id'       => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'post_id'         => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 1, 'auto_increment' => TRUE],
    'post_message'    => ['type' => 'TEXT'],
    'post_showsig'    => ['type' => 'TINYINT', 'length' => 1],
    'post_smileys'    => ['type' => 'TINYINT', 'length' => 1, 'default' => 1],
    'post_author'     => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'post_datestamp'  => ['type' => 'INT', 'length' => 10, 'unsigned' => TRUE, 'key' => 2],
    'post_ip'         => ['type' => 'VARCHAR', 'length' => 45, 'key' => 2],
    'post_ip_type'    => ['type' => 'TINYINT', 'length' => 1, 'default' => 4],
    'post_edituser'   => ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'key' => 2],
    'post_edittime'   => ['type' => 'INT', 'length' => 10, 'unsigned' => TRUE, 'key' => 2],
    'post_editreason' => ['type' => 'TEXT'],
    'post_hidden'     => ['type' => 'TINYINT', 'length' => 1],
    'post_locked'     => ['type' => 'TINYINT', 'length' => 1],
    'post_answer'     => ['type' => 'TINYINT', 'length' => 1],
];

$inf_newtable[] = DB_FORUM_THREADS." (
    forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    thread_tags TEXT NOT NULL,
    thread_subject VARCHAR(100) NOT NULL DEFAULT '',
    thread_author MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_views MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_lastpost INT(10) UNSIGNED NOT NULL DEFAULT '0',
    thread_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_lastuser MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_postcount SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    thread_poll TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    thread_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    thread_answered TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    thread_bounty SMALLINT(8) NOT NULL,
    thread_bounty_description TEXT NOT NULL,
    thread_bounty_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
    thread_bounty_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    thread_locked TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    thread_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (thread_id),
    KEY forum_id (forum_id),
    KEY thread_postcount (thread_postcount),
    KEY thread_lastpost (thread_lastpost),
    KEY thread_views (thread_views),
    FULLTEXT (thread_subject)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_THREAD_NOTIFY." (
    thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
    KEY notify_datestamp (notify_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_TAGS." (
    tag_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    tag_title VARCHAR(100) NOT NULL DEFAULT '',
    tag_description VARCHAR(250) NOT NULL DEFAULT '',
    tag_color VARCHAR(20) NOT NULL DEFAULT '',
    tag_status SMALLINT(1) NOT NULL DEFAULT '0',
    tag_language VARCHAR(100) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (tag_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_REPORTS." (
    report_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    report_post_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    report_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    report_comment TEXT NOT NULL,
    report_mod_comment TEXT NOT NULL,
    report_status SMALLINT(1) NOT NULL DEFAULT '0',
    report_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    report_updated INT(10) UNSIGNED NOT NULL DEFAULT '0',
    report_archive TEXT NOT NULL,
    PRIMARY KEY (report_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_USER_REP." (
    rep_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    rep_answer TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    points_gain SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    voter_id SMALLINT(1) UNSIGNED NOT NULL DEFAULT '0',
    user_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (rep_id),
    KEY post_id (post_id, user_id, voter_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_MOODS." (
    mood_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    mood_name TEXT NOT NULL,
    mood_description TEXT NOT NULL,
    mood_icon VARCHAR(50) NOT NULL DEFAULT '',
    mood_notify SMALLINT(4) NOT NULL DEFAULT ".USER_LEVEL_MEMBER.",
    mood_access SMALLINT(4) NOT NULL DEFAULT ".USER_LEVEL_MEMBER.",
    mood_status SMALLINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (mood_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_POST_NOTIFY." (
    post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_mood_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_sender MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
    KEY notify_datestamp (notify_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Logging of an action on the thread or post.
$inf_newtable[] = DB_FORUM_THREAD_LOGS." (
    thread_log_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    thread_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    post_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    thread_log_action SMALLINT(5) UNSIGNED NOT NULL,
    thread_log_item_type SMALLINT(5) UNSIGNED NOT NULL,
    thread_log_new_value VARCHAR(70) NOT NULL,
    thread_log_old_value VARCHAR(70) NOT NULL,
    thread_log_user BIGINT(20) UNSIGNED NOT NULL,
    thread_log_visibility SMALLINT(5) UNSIGNED NOT NULL,
    thread_log_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    KEY thread_id (thread_id),
    KEY post_id (post_id),
    PRIMARY KEY (thread_log_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";


if (!column_exists('users', 'user_reputation')) {
    $inf_altertable[] = DB_USERS." ADD user_reputation INT(10) UNSIGNED NOT NULL AFTER user_status";
}

// Insert Forum Settings
$settings = [
    'forum_ips'                  => USER_LEVEL_SUPER_ADMIN,
    'forum_attachmax'            => 1048576,
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
    'upvote_points'              => 2,
    'downvote_points'            => 1,
    'answering_points'           => 15,
    'points_to_upvote'           => 100,
    'points_to_downvote'         => 100,
    'forum_show_lastpost'        => 1,
    'forum_enabled_userfields'   => 0,
    'forum_show_reputation'      => 1
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Insert Forum Panels
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction, panel_languages) VALUES ('".$locale['setup_3402']."', 'forum_threads_panel', '', '1', '4', 'file', '0', '1', '1', '', '3', '".fusion_get_settings('enabled_languages')."')";
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction, panel_languages) VALUES ('".$locale['setup_3405']."', 'forum_threads_list_panel', '', '2', '1', 'file', '0', '1', '1', '".fusion_get_settings('opening_page')."', '2', '".fusion_get_settings('enabled_languages')."')";

// Multilanguage table
$inf_mlt[] = [
    'title'  => $locale['forums']['title'],
    'rights' => 'FO'
];
$inf_mlt[] = [
    'title'  => $locale['setup_3038'],
    'rights' => 'FR'
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include LOCALE.$language."/setup.php";

        if (file_exists(FORUM.'locale/'.$language.'/forum_tags.php')) {
            include FORUM.'locale/'.$language.'/forum_tags.php';
        } else {
            include FORUM.'locale/English/forum_tags.php';
        }

        $mlt_adminpanel[$language][] = [
            "rights"   => "F",
            "image"    => $inf_image,
            "title"    => $locale['setup_3012'],
            "panel"    => "admin/forums.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3304']."', 'infusions/forum/index.php', '0', '2', '0', '5', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3324']."', 'infusions/forum/newthread.php', ".USER_LEVEL_MEMBER.", '2', '0', '1', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3319']."', 'infusions/forum/index.php?section=latest', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3320']."', 'infusions/forum/index.php?section=participated', ".USER_LEVEL_MEMBER.", '2', '0', '3', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3321']."', 'infusions/forum/index.php?section=tracked', ".USER_LEVEL_MEMBER.", '2', '0', '4', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3322']."', 'infusions/forum/index.php?section=unanswered', '0', '2', '0', '5', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3323']."', 'infusions/forum/index.php?section=unsolved', '0', '2', '0', '6', '1', '".$language."')";

        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3600']."', 'rank_super_admin.png', 0, '1', ".USER_LEVEL_SUPER_ADMIN.", '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3601']."', 'rank_admin.png', 0, '1', ".USER_LEVEL_ADMIN.", '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3602']."', 'rank_mod.png', 0, '1', '-104', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3603']."', 'rank0.png', 0, '0', ".USER_LEVEL_MEMBER.", '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3604']."', 'rank1.png', 10, '0', ".USER_LEVEL_MEMBER.", '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3605']."', 'rank2.png', 50, '0', ".USER_LEVEL_MEMBER.", '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3606']."', 'rank3.png', 200, '0', ".USER_LEVEL_MEMBER.", '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3607']."', 'rank4.png', 500, '0', ".USER_LEVEL_MEMBER.", '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3608']."', 'rank5.png', 1000, '0', ".USER_LEVEL_MEMBER.", '".$language."')";

        $mlt_insertdbrow[$language][] = DB_FORUM_TAGS." (tag_title, tag_description, tag_color, tag_status, tag_language) VALUES ('".$locale['setup_3660']."', '".$locale['setup_3661']."', '#2e8c65', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/newthread.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=latest' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=participated' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=tracked' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=unanswered' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/forum/index.php?section=unsolved' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_FORUMS." WHERE forum_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_FORUM_RANKS." WHERE rank_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='F' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "F",
        "image"    => $inf_image,
        "title"    => $locale['setup_3012'],
        "panel"    => "admin/forums.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3304']."', 'infusions/forum/index.php', '0', '2', '0', '5', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3324']."', 'infusions/forum/newthread.php', ".USER_LEVEL_MEMBER.", '2', '0', '1', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3319']."', 'infusions/forum/index.php?section=latest', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3320']."', 'infusions/forum/index.php?section=participated', ".USER_LEVEL_MEMBER.", '2', '0', '3', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3321']."', 'infusions/forum/index.php?section=tracked', ".USER_LEVEL_MEMBER.", '2', '0', '4', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3322']."', 'infusions/forum/index.php?section=unanswered', '0', '2', '0', '5', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3323']."', 'infusions/forum/index.php?section=unsolved', '0', '2', '0', '6', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_dropcol[] = ['table' => DB_USERS, 'column' => 'user_reputation'];
$inf_droptable[] = DB_FORUMS;
$inf_droptable[] = DB_FORUM_POSTS;
$inf_droptable[] = DB_FORUM_THREADS;
$inf_droptable[] = DB_FORUM_THREAD_NOTIFY;
$inf_droptable[] = DB_FORUM_ATTACHMENTS;
$inf_droptable[] = DB_FORUM_POLLS;
$inf_droptable[] = DB_FORUM_POLL_OPTIONS;
$inf_droptable[] = DB_FORUM_POLL_VOTERS;
$inf_droptable[] = DB_FORUM_VOTES;
$inf_droptable[] = DB_FORUM_RANKS;
$inf_droptable[] = DB_FORUM_TAGS;
$inf_droptable[] = DB_FORUM_MOODS;
$inf_droptable[] = DB_FORUM_POST_NOTIFY;
$inf_droptable[] = DB_FORUM_USER_REP;

$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='F'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='FR'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='forum_threads_panel'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='forum_threads_list_panel'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/newthread.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php?section=latest'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php?section=participated'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php?section=tracked'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php?section=unanswered'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php?section=unsolved'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FO'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FR'";

$inf_delfiles[] = INFUSIONS."forum/attachments/";
$inf_delfiles[] = INFUSIONS."forum/images/thumbnail/";
$inf_delfiles[] = INFUSIONS."forum/images/";
