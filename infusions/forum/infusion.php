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

$inf_mlt[2] = array(
	"title" => $locale['setup_3038'],
	"rights" => "FR",
);

// Create tables
$inf_newtable[1] = DB_FORUM_ATTACHMENTS." (
	attach_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	attach_name VARCHAR(100) NOT NULL DEFAULT '',
	attach_mime VARCHAR(20) NOT NULL DEFAULT '',
	attach_size INT(20) UNSIGNED NOT NULL DEFAULT '0',
	attach_count INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (attach_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_FORUM_VOTES." (
	forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	vote_points DECIMAL(3,0) NOT NULL DEFAULT '0',
	vote_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0'
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[3] = DB_FORUM_RANKS." (
	rank_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	rank_title VARCHAR(100) NOT NULL DEFAULT '',
	rank_image VARCHAR(100) NOT NULL DEFAULT '',
	rank_posts iNT(10) UNSIGNED NOT NULL DEFAULT '0',
	rank_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	rank_apply TINYINT(4) DEFAULT '-101',
	rank_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (rank_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[4] = DB_FORUM_POLL_OPTIONS." (
	thread_id MEDIUMINT(8) unsigned NOT NULL,
	forum_poll_option_id SMALLINT(5) UNSIGNED NOT NULL,
	forum_poll_option_text VARCHAR(150) NOT NULL,
	forum_poll_option_votes SMALLINT(5) UNSIGNED NOT NULL,
	KEY thread_id (thread_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[5] = DB_FORUM_POLL_VOTERS." (
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
	forum_vote_user_id MEDIUMINT(8) UNSIGNED NOT NULL,
	forum_vote_user_ip VARCHAR(45) NOT NULL,
	forum_vote_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
	KEY thread_id (thread_id,forum_vote_user_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[6] = DB_FORUM_POLLS." (
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
	forum_poll_title VARCHAR(250) NOT NULL,
	forum_poll_start INT(10) UNSIGNED DEFAULT NULL,
	forum_poll_length iNT(10) UNSIGNED NOT NULL,
	forum_poll_votes SMALLINT(5) unsigned NOT NULL,
	KEY thread_id (thread_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[7] = DB_FORUMS." (
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

$inf_newtable[8] = DB_FORUM_POSTS." (
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

$inf_newtable[9] = DB_FORUM_THREADS." (
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
	thread_answered TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	thread_locked TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	thread_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (thread_id),
	KEY thread_postcount (thread_postcount),
	KEY thread_lastpost (thread_lastpost),
	KEY thread_views (thread_views)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[10] = DB_FORUM_THREAD_NOTIFY." (
	thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
	KEY notify_datestamp (notify_datestamp)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";


/**
 * List of Settings Column for Forum Infusion and default values
 * numofthreads - 15 --- threads per page
 * numofposts - 15 --- threads per page
 * forum_ips - -103
 * forum_attachmax = 1mb ---- 1,000,000 bytes (one million)
 * forum_attachmax_count = 5
 * forum_attachtypes .pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z
 * thread_notify = 1
 * forum_ranks = 1
 * forum_edit_lock = 0
 * forum_edit_timelimit = 0
 * popular_threads_timeframe = 604800
 * forum_last_posts_reply = 1
 * forum_last_post_avatar = 1
 * forum_editpost_to_lastpost = 0
 * forum_rank_style = 0
 */


// Insert Forum Settings
$inf_insertdbrow[1] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ips', '-103', 'forum')";
$inf_insertdbrow[2] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax', '1000000', 'forum')";
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax_count', '5', 'forum')";
$inf_insertdbrow[4] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachtypes', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'forum')";
$inf_insertdbrow[5] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thread_notify', '1', 'forum')";
$inf_insertdbrow[6] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ranks', '1', 'forum')";
$inf_insertdbrow[7] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_lock', '0', 'forum')";
$inf_insertdbrow[8] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_timelimit', '0', 'forum')";
$inf_insertdbrow[9] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('popular_threads_timeframe', '604800', 'forum')";
$inf_insertdbrow[10] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_posts_reply', '1', 'forum')";
$inf_insertdbrow[11] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_post_avatar', '1', 'forum')";
$inf_insertdbrow[12] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_editpost_to_lastpost', '1', 'forum')";
$inf_insertdbrow[13] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('threads_per_page', '20', 'forum')";
$inf_insertdbrow[14] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('posts_per_page', '20', 'forum')";
$inf_insertdbrow[15] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('numofthreads', '16', 'forum')";
$inf_insertdbrow[16] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_rank_style', '0', 'forum')";

// Admin links
$inf_insertdbrow[17] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('F', 'forums.gif', '".$locale['setup_3012']."', '".INFUSIONS."forum/admin/forums.php', '1')";
// This might get tabbed and this text is a reminder to do so
//$inf_insertdbrow[17] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('SF', 'settings_forum.gif', '".$locale['setup_3032']."', '".INFUSIONS."forum/admin/settings_forum.php', '1')";
//$inf_insertdbrow[18] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('FR', 'forum_ranks.gif', '".$locale['setup_3038']."', '".INFUSIONS."forum/admin/forum_ranks.php', '1')";

// Insert Panels
$inf_insertdbrow[18] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES ('".$locale['setup_3402']."', 'forum_threads_panel', '', '1', '4', 'file', '0', '0', '1', '', '')";
$inf_insertdbrow[19] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES ('".$locale['setup_3405']."', 'forum_threads_list_panel', '', '2', '1', 'file', '0', '0', '1', '', '')";
																																																		
$enabled_languages = explode('.', fusion_get_settings('enabled_languages'));

// Create a link for all installed languages
if (!empty($enabled_languages)) {
$k = 20;
	for ($i = 0; $i < count($enabled_languages); $i++) {
	include LOCALE."".$enabled_languages[$i]."/setup.php";
		$inf_insertdbrow[$k] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3304']."', 'infusions/forum/', '0', '2', '0', '5', '".$enabled_languages[$i]."')";
		$k++;
	}
} else {
		$inf_insertdbrow[20] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3304']."', 'infusions/forum/', '0', '2', '0', '5', '".LANGUAGE."')";
}

// Reset locale
include LOCALE.LOCALESET."setup.php";

// Check the table and run Forum Ranks inserts for each installed language of it is empty.
if (db_exists(DB_FORUM_RANKS)) {
	$result = dbquery("SELECT * FROM ".DB_FORUM_RANKS);
	if (dbrows($result) == 0) {
		for ($i=0;$i<sizeof($enabled_languages );$i++) {
			include LOCALE."".$enabled_languages[$i]."/setup.php";
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3600']."', 'rank_super_admin.png', 0, '1', -103, '".$enabled_languages[$i]."')");
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3601']."', 'rank_admin.png', 0, '1', -102, '".$enabled_languages[$i]."')");
			// -104 < Moderator, why is it not recognized?
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3602']."', 'rank_mod.png', 0, '1', -104, '".$enabled_languages[$i]."')");
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3603']."', 'rank0.png', 0, '0', -101, '".$enabled_languages[$i]."')");
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3604']."', 'rank1.png', 10, '0', -101, '".$enabled_languages[$i]."')");
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3605']."', 'rank2.png', 50, '0', -101, '".$enabled_languages[$i]."')");
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3606']."', 'rank3.png', 200, '0', -101, '".$enabled_languages[$i]."')");
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3607']."', 'rank4.png', 500, '0', -101, '".$enabled_languages[$i]."')");
			$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['setup_3608']."', 'rank5.png', 1000, '0', -101, '".$enabled_languages[$i]."')");
		}
	}
}
			
// Defuse clean up
$inf_droptable[1] = DB_FORUMS;
$inf_droptable[2] = DB_FORUM_POSTS;
$inf_droptable[3] = DB_FORUM_THREADS;
$inf_droptable[4] = DB_FORUM_THREAD_NOTIFY;
$inf_droptable[5] = DB_FORUM_ATTACHMENTS;
$inf_droptable[6] = DB_FORUM_POLLS;
$inf_droptable[7] = DB_FORUM_POLL_OPTIONS;
$inf_droptable[8] = DB_FORUM_POLL_VOTERS;
$inf_droptable[9] = DB_FORUM_VOTES;
$inf_droptable[10] = DB_FORUM_POSTS;
$inf_droptable[11] = DB_FORUM_RANKS;
$inf_deldbrow[1] = DB_ADMIN." WHERE admin_rights='F'";
$inf_deldbrow[2] = DB_ADMIN." WHERE admin_rights='S3'";
$inf_deldbrow[3] = DB_ADMIN." WHERE admin_rights='FR'";
$inf_deldbrow[4] = DB_PANELS." WHERE panel_name='".$locale['setup_3402']."'";
$inf_deldbrow[5] = DB_PANELS." WHERE panel_name='".$locale['setup_3405']."'";
$inf_deldbrow[6] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/'";
$inf_deldbrow[7] = DB_SITE_LINKS." WHERE link_url = 'infusions/forum/index.php'";
$inf_deldbrow[8] = DB_SETTINGS_INF." WHERE settings_inf='forum'";
$inf_deldbrow[9] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FO'";
$inf_deldbrow[10] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FR'";
