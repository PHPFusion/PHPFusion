<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: Frederick Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

if (!defined("IN_FUSION")) { die("Access Denied"); }
include LOCALE.LOCALESET."setup.php";
// Infusion general information
$inf_title = $locale['forums']['title'];
$inf_description = $locale['forums']['description'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "forum";
// Multilanguage table for Administration
$inf_mlt[1] = array(
	"title" => $locale['forums']['title'],
	"rights" => "FO",
);
$inf_mlt[1] = array(
	"title" => $locale['setup_3038'],
	"rights" => "FR",
);
$i = 0;
// Create tables
$inf_newtable[$i++] = DB_FORUM_ATTACHMENTS." (
	attach_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	attach_name VARCHAR(100) NOT NULL DEFAULT '',
	attach_mime VARCHAR(20) NOT NULL DEFAULT '',
	attach_size INT(20) UNSIGNED NOT NULL DEFAULT '0',
	attach_count INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (attach_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[$i++] = DB_FORUM_VOTES." (
	forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	vote_points DECIMAL(3,0) NOT NULL DEFAULT '0',
	vote_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0'
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[$i++] = DB_FORUM_RANKS." (
	rank_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	rank_title VARCHAR(100) NOT NULL DEFAULT '',
	rank_image VARCHAR(100) NOT NULL DEFAULT '',
	rank_posts iNT(10) UNSIGNED NOT NULL DEFAULT '0',
	rank_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	rank_apply TINYINT(4) DEFAULT '-101',
	rank_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (rank_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[$i++] = DB_FORUM_POLL_OPTIONS." (
	thread_id MEDIUMINT(8) unsigned NOT NULL,
	forum_poll_option_id SMALLINT(5) UNSIGNED NOT NULL,
	forum_poll_option_text VARCHAR(150) NOT NULL,
	forum_poll_option_votes SMALLINT(5) UNSIGNED NOT NULL,
	KEY thread_id (thread_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[$i++] = DB_FORUM_POLL_VOTERS." (
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
	forum_vote_user_id MEDIUMINT(8) UNSIGNED NOT NULL,
	forum_vote_user_ip VARCHAR(45) NOT NULL,
	forum_vote_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
	KEY thread_id (thread_id,forum_vote_user_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[$i++] = DB_FORUM_POLLS." (
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
	forum_poll_title VARCHAR(250) NOT NULL,
	forum_poll_start INT(10) UNSIGNED DEFAULT NULL,
	forum_poll_length iNT(10) UNSIGNED NOT NULL,
	forum_poll_votes SMALLINT(5) unsigned NOT NULL,
	KEY thread_id (thread_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[$i++] = DB_FORUMS." (
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
	forum_allow_post_ratings TINYINT(1) NOT NULL DEFAULT '0',
	forum_post_ratings TINYINT(4) NOT NULL DEFAULT '-101',
	forum_users TINYINT(1) NOT NULL DEFAULT '0',
	forum_allow_attach SMALLINT(1) UNSIGNED NOT NULL DEFAULT '0',
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

$inf_newtable[$i++] = DB_FORUM_POSTS." (
	forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
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
	PRIMARY KEY (post_id),
	KEY thread_id (thread_id),
	KEY post_datestamp (post_datestamp)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[$i++] = DB_FORUM_THREADS." (
	forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	thread_subject VARCHAR(100) NOT NULL DEFAULT '',
	thread_author MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_views MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_lastpost INT(10) UNSIGNED NOT NULL DEFAULT '0',
	thread_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_lastuser MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_postcount SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	thread_poll TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	thread_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	thread_locked TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	thread_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (thread_id),
	KEY thread_postcount (thread_postcount),
	KEY thread_lastpost (thread_lastpost),
	KEY thread_views (thread_views)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[$i++] = DB_FORUM_THREAD_NOTIFY." (
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
	KEY notify_datestamp (notify_datestamp)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Reset counter for data insertions
$i = 0;
// Insert Admin Links - Something wrong not installing first row.
//$inf_insertdbrow[$i] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('F', 'forums.gif', '".$locale['setup_3012']."', '".INFUSIONS."forum/admin/forums.php', '5')";
$inf_insertdbrow[$i++] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('F', 'forums.gif', '".$locale['setup_3012']."', '".INFUSIONS."forum/admin/forums.php', '5')";
$inf_insertdbrow[$i++] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('S3', 'settings_forum.gif', '".$locale['setup_3032']."', '".INFUSIONS."forum/admin/settings_forum.php', '5')";
$inf_insertdbrow[$i++] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('FR', 'forum_ranks.gif', '".$locale['setup_3038']."', '".INFUSIONS."forum/admin/forum_ranks.php', '5')";

// Insert Panels
$inf_insertdbrow[$i++] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES('".$locale['setup_3402']."', 'forum_threads_panel', '', '1', '4', 'file', '0', '0', '1', '')";
$inf_insertdbrow[$i++] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES('".$locale['setup_3405']."', 'forum_threads_list_panel', '', '2', '2', 'file', '0', '0', '0', '')";

// Create a link for all installed languages
$enabled_languages = explode('.', $settings['enabled_languages']);
if (!empty($settings['enabled_languages'])) {
	for ($x = 0; $x < count($enabled_languages); $x++) {
		include LOCALE.$enabled_languages[$x]."/setup.php";
		$inf_insertdbrow[$i++] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3304']."', 'infusions/forum/', '0', '2', '0', '5', '".$enabled_languages[$x]."')";
	}
} else {
	$inf_insertdbrow[$i++] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3304']."', 'infusions/forum/', '0', '2', '0', '2', '".LANGUAGE."')";
}
// Install forum ranks
if (db_exists(DB_FORUM_RANKS)) {
	$forum_ranks_sql = "INSERT INTO ".DB_FORUM_RANKS." VALUES \n";
	$forum_ranks_sql .= implode(",\n", array_map(function ($language) {
		$locale = array();
		include LOCALE.$language."/setup.php";
		return "(NULL, '".$locale['setup_3600']."', 'rank_super_admin.png', 0, '1', -103, '".$language."'),
				(NULL, '".$locale['setup_3601']."', 'rank_admin.png', 0, '1', -102, '".$language."'),
				(NULL, '".$locale['setup_3602']."', 'rank_mod.png', 0, '1', -104, '".$language."'),
				(NULL, '".$locale['setup_3603']."', 'rank0.png', 0, '0', -101, '".$language."'),
				(NULL, '".$locale['setup_3604']."', 'rank1.png', 10, '0', -101, '".$language."'),
				(NULL, '".$locale['setup_3605']."', 'rank2.png', 50, '0', -101, '".$language."'),
				(NULL, '".$locale['setup_3606']."', 'rank3.png', 200, '0', -101, '".$language."'),
				(NULL, '".$locale['setup_3607']."', 'rank4.png', 500, '0', -101, '".$language."'),
				(NULL, '".$locale['setup_3608']."', 'rank5.png', 1000, '0', -101, '".$language."')";
	}, explode('.', fusion_get_settings('enabled_languages'))));
	dbquery($forum_ranks_sql);
}

// Settings for Forum
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('threads_per_page', '20', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('posts_per_page', '20', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('forum_ips', '0', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('forum_ranks', '0', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('forum_edit_lock', '0', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('forum_edit_timelimit', '0', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('forum_editpost_to_lastpost', '1', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('forum_last_posts_reply', '10', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('forum_last_post_avatar', '1', 'forum')";
$inf_insertdbrow[$i++] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('thread_notify', '1', 'forum')";

// reset counter for tables
$i = 0;
$inf_droptable[$i++] = DB_FORUMS;
$inf_droptable[$i++] = DB_FORUM_POSTS;
$inf_droptable[$i++] = DB_FORUM_THREADS;
$inf_droptable[$i++] = DB_FORUM_THREAD_NOTIFY;
$inf_droptable[$i++] = DB_FORUM_ATTACHMENTS;
$inf_droptable[$i++] = DB_FORUM_POLLS;
$inf_droptable[$i++] = DB_FORUM_POLL_OPTIONS;
$inf_droptable[$i++] = DB_FORUM_POLL_VOTERS;
$inf_droptable[$i++] = DB_FORUM_VOTES;
$inf_droptable[$i++] = DB_FORUM_POSTS;
$inf_droptable[$i++] = DB_FORUM_RANKS;

// row deletion
$i = 0;
$inf_deldbrow[$i++] = DB_ADMIN." WHERE admin_rights='F'";
$inf_deldbrow[$i++] = DB_ADMIN." WHERE admin_rights='S3'";
$inf_deldbrow[$i++] = DB_ADMIN." WHERE admin_rights='FR'";
$inf_deldbrow[$i++] = DB_PANELS." WHERE panel_name='".$locale['setup_3402']."'";
$inf_deldbrow[$i++] = DB_PANELS." WHERE panel_name='".$locale['setup_3405']."'";
$inf_deldbrow[$i++] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/'";
$inf_deldbrow[$i++] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php'";
$inf_deldbrow[$i++] = DB_SETTINGS_INF." WHERE settings_inf='forum'";
$inf_deldbrow[$i++] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FO'";
$inf_deldbrow[$i++] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FR'";