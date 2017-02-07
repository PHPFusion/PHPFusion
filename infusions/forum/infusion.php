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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$locale = fusion_get_locale("",
                            array(
                                LOCALE.LOCALESET."setup.php",
                                INFUSIONS."forum/locale/".LOCALESET."/forum_tags.php"
                            )
);


// Infusion general information
$inf_title = $locale['forums']['title'];
$inf_description = $locale['forums']['description'];
$inf_version = '1.0.6';
$inf_developer = 'PHP Fusion Development Team';
$inf_email = 'info@php-fusion.co.uk';
$inf_weburl = 'https://www.php-fusion.co.uk';
$inf_folder = 'forum';
$inf_image = 'forums.png';

// Multilanguage table for Administration
$inf_mlt[] = array(
    'title' => $locale['forums']['title'],
    'rights' => 'FO',
);
$inf_mlt[] = array(
    'title' => $locale['setup_3038'],
    'rights' => 'FR',
);

// Create tables
$inf_newtable[] = DB_FORUM_ATTACHMENTS." (
	attach_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	attach_name VARCHAR(100) NOT NULL DEFAULT '',
	attach_mime VARCHAR(20) NOT NULL DEFAULT '',
	attach_size INT(20) UNSIGNED NOT NULL DEFAULT '0',
	attach_count INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (attach_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_VOTES." (
    vote_id MEDIUMINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	vote_points DECIMAL(3,0) NOT NULL DEFAULT '0',
	vote_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (vote_id)	
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_RANKS." (
	rank_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	rank_title VARCHAR(100) NOT NULL DEFAULT '',
	rank_image VARCHAR(100) NOT NULL DEFAULT '',
	rank_posts iNT(10) UNSIGNED NOT NULL DEFAULT '0',
	rank_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	rank_apply TINYINT(4) DEFAULT '-101',
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
	KEY thread_id (thread_id,forum_vote_user_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_POLLS." (
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
	forum_poll_title VARCHAR(250) NOT NULL,
	forum_poll_start INT(10) UNSIGNED DEFAULT NULL,
	forum_poll_length iNT(10) UNSIGNED NOT NULL,
	forum_poll_votes SMALLINT(5) unsigned NOT NULL,
	KEY thread_id (thread_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUMS." (
	forum_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	forum_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	forum_branch MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	forum_name VARCHAR(50) NOT NULL DEFAULT '',
	forum_type TINYINT(1) NOT NULL DEFAULT '1',
	forum_answer_threshold TINYINT(3) NOT NULL DEFAULT '15',
	forum_lock TINYINT(1) NOT NULL DEFAULT '0',
	forum_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	forum_description TEXT NOT NULL,
	forum_rules TEXT NOT NULL,
	forum_mods TEXT NOT NULL,
	forum_access TINYINT(4) NOT NULL DEFAULT '0',
	forum_post TINYINT(4) DEFAULT '-101',
	forum_reply TINYINT(4) DEFAULT '-101',
	forum_allow_poll TINYINT(1) NOT NULL DEFAULT '0',
	forum_poll TINYINT(4) NOT NULL DEFAULT '-101',
	forum_vote TINYINT(4) NOT NULL DEFAULT '-101',
	forum_image VARCHAR(100) NOT NULL DEFAULT '',
	forum_post_ratings TINYINT(4) NOT NULL DEFAULT '-101',
	forum_users TINYINT(1) NOT NULL DEFAULT '0',
	forum_allow_attach TINYINT(1) NOT NULL DEFAULT '0',
	forum_attach TINYINT(4) NOT NULL DEFAULT '-101',
	forum_attach_download TINYINT(4) NOT NULL DEFAULT '-101',
	forum_quick_edit TINYINT(1) NOT NULL DEFAULT '0',
	forum_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	forum_lastpost INT(10) UNSIGNED NOT NULL DEFAULT '0',
	forum_postcount MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	forum_threadcount MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	forum_lastuser MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	forum_merge TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	forum_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	forum_meta TEXT NOT NULL,
	forum_alias VARCHAR(50) NOT NULL DEFAULT '',
	PRIMARY KEY (forum_id),
	KEY forum_order (forum_order),
	KEY forum_lastpostid (forum_lastpostid),
	KEY forum_postcount (forum_postcount),
	KEY forum_threadcount (forum_threadcount)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_POSTS." (
	forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	post_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_message TEXT NOT NULL,
	post_showsig TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	post_smileys TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	post_author MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	post_ip VARCHAR(45) NOT NULL DEFAULT '',
	post_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
	post_edituser MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_edittime INT(10) UNSIGNED NOT NULL DEFAULT '0',
	post_editreason TEXT NOT NULL,
	post_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	post_locked TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	post_answer TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (post_id),
	KEY thread_id (thread_id),
	KEY post_datestamp (post_datestamp)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FORUM_THREADS." (
	forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	thread_tags TEXT NOT NULL,
	thread_tags_old TEXT NOT NULL,
	thread_tags_change INT(10) UNSIGNED NOT NULL DEFAULT '0',
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
	KEY thread_postcount (thread_postcount),
	KEY thread_lastpost (thread_lastpost),
	KEY thread_views (thread_views)
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
	tag_language VARCHAR(100) NOT NULL DEFAULT '',
	PRIMARY KEY (tag_id)
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
	mood_notify SMALLINT(4) NOT NULL DEFAULT '-101',
	mood_access SMALLINT(4) NOT NULL DEFAULT '-101',
	mood_status SMALLINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (mood_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_POST_NOTIFY." (
	post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_mood_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_sender MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
	KEY notify_datestamp (notify_datestamp)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

if (!column_exists('users', 'user_reputation')) {
    $inf_altertable[] = $db_prefix."users ADD user_reputation INT(10) UNSIGNED NOT NULL AFTER user_status";
}
// Admin links
$inf_adminpanel[] = array(
    "image" => $inf_image,
    "page" => 1,
    "rights" => "F",
    "title" => $locale['setup_3012'],
    "panel" => "admin/forums.php"
);

// Insert Forum Settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ips', '".USER_LEVEL_SUPER_ADMIN."', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax', '1000000', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax_count', '5', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachtypes', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thread_notify', '1', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ranks', '1', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_lock', '0', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_timelimit', '0', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('popular_threads_timeframe', '604800', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_posts_reply', '1', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_post_avatar', '1', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_editpost_to_lastpost', '1', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('threads_per_page', '20', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('posts_per_page', '20', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('numofthreads', '16', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_rank_style', '0', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('upvote_points', '2', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('downvote_points', '1', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('answering_points', '15', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('points_to_upvote', '100', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('points_to_downvote', '100', 'forum')";

// Insert Panels
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES ('".$locale['setup_3402']."', 'forum_threads_panel', '', '1', '4', 'file', '0', '0', '1', '', '0')";
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES ('".$locale['setup_3405']."', 'forum_threads_list_panel', '', '2', '1', 'file', '0', '0', '1', '', '2')";

if (function_exists("fusion_get_enabled_languages")) {
    $enabled_languages = array_keys(fusion_get_enabled_languages());
} else {
    $enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
}

// Create a link for all installed languages
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {

        $locale = fusion_get_locale("", LOCALE.$language."/setup.php");
        $locale += fusion_get_locale("", INFUSIONS."forum/locale/".LOCALESET."/forum_tags.php");

        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3304']."', 'infusions/forum/index.php', '0', '2', '0', '5', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3324']."', 'infusions/forum/newthread.php', '0', '2', '-101', '1', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3319']."', 'infusions/forum/index.php?section=latest', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3320']."', 'infusions/forum/index.php?section=participated', '0', '2', '-101', '3', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3321']."', 'infusions/forum/index.php?section=tracked', '0', '2', '-101', '4', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3322']."', 'infusions/forum/index.php?section=unanswered', '0', '2', '0', '5', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_cat, link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('{last_id}', '".$locale['setup_3323']."', 'infusions/forum/index.php?section=unsolved', '0', '2', '0', '6', '1', '".$language."')";

        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3600']."', 'rank_super_admin.png', 0, '1', '-103', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3601']."', 'rank_admin.png', 0, '1', '-102', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3602']."', 'rank_mod.png', 0, '1', '-104', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3603']."', 'rank0.png', 0, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3604']."', 'rank1.png', 10, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3605']."', 'rank2.png', 50, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3606']."', 'rank3.png', 200, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3607']."', 'rank4.png', 500, '0', '-101', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['setup_3608']."', 'rank5.png', 1000, '0', '-101', '".$language."')";

        $mlt_insertdbrow[$language][] = DB_FORUM_TAGS." (tag_title, tag_description, tag_color, tag_status, tag_language) VALUES ('".$locale['forum_tag_0110']."', '".$locale['forum_tag_0111']."', '#2e8c65', '1', '".$language."')";

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

// Defuse clean up
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
$inf_droptable[] = DB_POST_NOTIFY;
$inf_droptable[] = DB_FORUM_USER_REP;

$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='F'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='FR'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='forum_threads_panel'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='forum_threads_list_panel'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='forum'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FO'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FR'";

$inf_delfiles[] = INFUSIONS."forum/attachments/";
$inf_delfiles[] = INFUSIONS."forum/images/thumbnail/";
$inf_delfiles[] = INFUSIONS."forum/images/";