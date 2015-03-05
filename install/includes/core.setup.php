<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: core.setup.php
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
// Installs 33 Core Tables and Inserts
//<editor-fold desc="create table sql for core tables" >
$core_tables = array("admin" => " (
		admin_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		admin_rights CHAR(4) NOT NULL DEFAULT '',
		admin_image VARCHAR(50) NOT NULL DEFAULT '',
		admin_title VARCHAR(50) NOT NULL DEFAULT '',
		admin_link VARCHAR(100) NOT NULL DEFAULT 'reserved',
		admin_page TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
		PRIMARY KEY (admin_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"mlt_tables" => " (
		mlt_rights CHAR(4) NOT NULL DEFAULT '',
		mlt_title VARCHAR(50) NOT NULL DEFAULT '',
		mlt_status VARCHAR(50) NOT NULL DEFAULT '',
		PRIMARY KEY (mlt_rights)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"admin_resetlog" => " (
		reset_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		reset_admin_id mediumint(8) unsigned NOT NULL default '1',
		reset_timestamp int(10) unsigned NOT NULL default '0',
		reset_sucess text NOT NULL,
		reset_failed text NOT NULL,
		reset_admins varchar(8) NOT NULL default '0',
		reset_reason varchar(255) NOT NULL,
		PRIMARY KEY (reset_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"bbcodes" => " (
		bbcode_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		bbcode_name VARCHAR(20) NOT NULL DEFAULT '',
		bbcode_order SMALLINT(5) UNSIGNED NOT NULL,
		PRIMARY KEY (bbcode_id),
		KEY bbcode_order (bbcode_order)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"blacklist" => " (
		blacklist_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		blacklist_user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		blacklist_ip VARCHAR(45) NOT NULL DEFAULT '',
		blacklist_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		blacklist_email VARCHAR(100) NOT NULL DEFAULT '',
		blacklist_reason TEXT NOT NULL,
		blacklist_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (blacklist_id),
		KEY blacklist_ip_type (blacklist_ip_type)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"captcha" => " (
		captcha_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		captcha_ip VARCHAR(45) NOT NULL DEFAULT '',
		captcha_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		captcha_encode VARCHAR(32) NOT NULL DEFAULT '',
		captcha_string VARCHAR(15) NOT NULL DEFAULT '',
		KEY captcha_datestamp (captcha_datestamp)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"custom_pages" => " (
		page_id MEDIUMINT(8) NOT NULL AUTO_INCREMENT,
		page_link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
		page_title VARCHAR(200) NOT NULL DEFAULT '',
		page_access TINYINT(4) NOT NULL DEFAULT '0',
		page_content TEXT NOT NULL,
		page_keywords VARCHAR(250) NOT NULL DEFAULT '',
		page_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		page_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		page_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
		PRIMARY KEY (page_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"comments" => " (
		comment_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		comment_item_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		comment_type CHAR(4) NOT NULL DEFAULT '',
		comment_cat MEDIUMINT(8) NOT NULL DEFAULT '0',
		comment_name VARCHAR(50) NOT NULL DEFAULT '',
		comment_message TEXT NOT NULL,
		comment_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		comment_ip VARCHAR(45) NOT NULL DEFAULT '',
		comment_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		comment_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (comment_id),
		KEY comment_datestamp (comment_datestamp)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"errors" => " (
		error_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		error_level smallint(5) unsigned NOT NULL,
		error_message text NOT NULL,
		error_file varchar(255) NOT NULL,
		error_line smallint(5) NOT NULL,
		error_page varchar(200) NOT NULL,
		error_user_level TINYINT(4) NOT NULL,
		error_user_ip varchar(45) NOT NULL default '',
		error_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		error_status tinyint(1) NOT NULL default '0',
		error_timestamp int(10) NOT NULL,
		PRIMARY KEY (error_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"flood_control" => " (
		flood_ip VARCHAR(45) NOT NULL DEFAULT '',
		flood_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		flood_timestamp INT(5) UNSIGNED NOT NULL DEFAULT '0',
		KEY flood_timestamp (flood_timestamp)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"infusions" => " (
		inf_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		inf_title VARCHAR(100) NOT NULL DEFAULT '',
		inf_folder VARCHAR(100) NOT NULL DEFAULT '',
		inf_version VARCHAR(10) NOT NULL DEFAULT '0',
		PRIMARY KEY (inf_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"messages" => " (
		message_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		message_to MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		message_from MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		message_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		message_subject VARCHAR(100) NOT NULL DEFAULT '',
		message_message TEXT NOT NULL,
		message_smileys CHAR(1) NOT NULL DEFAULT '',
		message_read TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		message_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		message_folder TINYINT(1) UNSIGNED NOT NULL DEFAULT  '0',
		PRIMARY KEY (message_id),
		KEY message_datestamp (message_datestamp)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"messages_options" => " (
		user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		pm_email_notify tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
		pm_save_sent tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
		pm_inbox SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL,
		pm_savebox SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL,
		pm_sentbox SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL,
		PRIMARY KEY (user_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"new_users" => " (
		user_code VARCHAR(40) NOT NULL,
		user_name VARCHAR(30) NOT NULL,
		user_email VARCHAR(100) NOT NULL,
		user_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
		user_info TEXT NOT NULL,
		KEY user_datestamp (user_datestamp)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"email_verify" => " (
		user_id MEDIUMINT(8) NOT NULL,
		user_code VARCHAR(32) NOT NULL,
		user_email VARCHAR(100) NOT NULL,
		user_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
		KEY user_datestamp (user_datestamp)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"email_templates" => " (
		template_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		template_key VARCHAR(10) NOT NULL,
		template_format VARCHAR(10) NOT NULL,
		template_active TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		template_name VARCHAR(300) NOT NULL,
		template_subject TEXT NOT NULL,
		template_content TEXT NOT NULL,
		template_sender_name VARCHAR(30) NOT NULL,
		template_sender_email VARCHAR(100) NOT NULL,
		template_language VARCHAR(50) NOT NULL,
		PRIMARY KEY (template_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"ratings" => " (
		rating_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		rating_item_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		rating_type CHAR(4) NOT NULL DEFAULT '',
		rating_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		rating_vote TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		rating_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		rating_ip VARCHAR(45) NOT NULL DEFAULT '',
		rating_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		PRIMARY KEY (rating_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"online" => " (
		online_user VARCHAR(50) NOT NULL DEFAULT '',
		online_ip VARCHAR(45) NOT NULL DEFAULT '',
		online_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		online_lastactive INT(10) UNSIGNED NOT NULL DEFAULT '0'
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"panels" => " (
		panel_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		panel_name VARCHAR(100) NOT NULL DEFAULT '',
		panel_filename VARCHAR(100) NOT NULL DEFAULT '',
		panel_content TEXT NOT NULL,
		panel_side TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
		panel_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
		panel_type VARCHAR(20) NOT NULL DEFAULT '',
		panel_access TINYINT(4) NOT NULL DEFAULT '0',
		panel_display TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		panel_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		panel_url_list TEXT NOT NULL,
		panel_restriction TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		panel_languages VARCHAR(200) NOT NULL DEFAULT '".implode('.', filter_input(INPUT_POST, 'enabled_languages', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ? : array())."',
		PRIMARY KEY (panel_id),
		KEY panel_order (panel_order)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"permalinks_alias" => " (
		alias_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		alias_url VARCHAR(200) NOT NULL DEFAULT '',
		alias_php_url VARCHAR(200) NOT NULL DEFAULT '',
		alias_type VARCHAR(10) NOT NULL DEFAULT '',
		alias_item_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (alias_id),
		KEY alias_id (alias_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"permalinks_method" => " (
		pattern_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		pattern_type INT(5) UNSIGNED NOT NULL,
		pattern_source VARCHAR(200) NOT NULL DEFAULT '',
		pattern_target VARCHAR(200) NOT NULL DEFAULT '',
		pattern_cat VARCHAR(10) NOT NULL DEFAULT '',
		PRIMARY KEY (pattern_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"permalinks_rewrites" => " (
		rewrite_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		rewrite_name VARCHAR(50) NOT NULL DEFAULT '',
		PRIMARY KEY (rewrite_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"settings" => " (
		settings_name VARCHAR(200) NOT NULL DEFAULT '',
		settings_value TEXT NOT NULL,
		PRIMARY KEY (settings_name)
		) ENGINE=MYISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"settings_inf" => " (
		settings_name VARCHAR(200) NOT NULL DEFAULT '',
		settings_value TEXT NOT NULL,
		settings_inf VARCHAR(200) NOT NULL DEFAULT '',
		PRIMARY KEY (settings_name)
		) ENGINE=MYISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"site_links" => " (
		link_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
		link_name VARCHAR(100) NOT NULL DEFAULT '',
		link_url VARCHAR(200) NOT NULL DEFAULT '',
		link_icon VARCHAR(100) NOT NULL DEFAULT '',
		link_visibility TINYINT(4) NOT NULL DEFAULT '0',
		link_position TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
		link_window TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		link_order SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
		link_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
		PRIMARY KEY (link_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"smileys" => " (
		smiley_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		smiley_code VARCHAR(50) NOT NULL,
		smiley_image VARCHAR(100) NOT NULL,
		smiley_text VARCHAR(100) NOT NULL,
		PRIMARY KEY (smiley_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"submissions" => " (
		submit_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		submit_type CHAR(1) NOT NULL,
		submit_user MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL,
		submit_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
		submit_criteria TEXT NOT NULL,
		PRIMARY KEY (submit_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"suspends" => " (
		suspend_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		suspended_user MEDIUMINT(8) UNSIGNED NOT NULL,
		suspending_admin MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
		suspend_ip VARCHAR(45) NOT NULL DEFAULT '',
		suspend_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		suspend_date INT(10) NOT NULL DEFAULT '0',
		suspend_reason TEXT NOT NULL,
		suspend_type TINYINT(1) NOT NULL DEFAULT '0',
		reinstating_admin MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
		reinstate_reason TEXT NOT NULL,
		reinstate_date INT(10) NOT NULL DEFAULT '0',
		reinstate_ip VARCHAR(45) NOT NULL DEFAULT '',
		reinstate_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		PRIMARY KEY (suspend_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"user_field_cats" => " (
		field_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
		field_cat_name TEXT NOT NULL,
		field_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		field_cat_db VARCHAR(100) NOT NULL,
		field_cat_index VARCHAR(200) NOT NULL,
		field_cat_class VARCHAR(50) NOT NULL,
		field_cat_order SMALLINT(5) UNSIGNED NOT NULL ,
		PRIMARY KEY (field_cat_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"user_fields" => " (
		field_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		field_title TEXT NOT NULL,
		field_name VARCHAR(50) NOT NULL,
		field_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
		field_type VARCHAR(25) NOT NULL,
		field_default TEXT NOT NULL,
		field_options TEXT NOT NULL,
		field_error VARCHAR(50) NOT NULL,
		field_required TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		field_log TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		field_registration TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		field_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
		field_config TEXT NOT NULL,
		PRIMARY KEY (field_id),
		KEY field_order (field_order)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"user_groups" => " (
		group_id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
		group_name VARCHAR(100) NOT NULL,
		group_description VARCHAR(200) NOT NULL,
		PRIMARY KEY (group_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"user_log" => " (
		userlog_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		userlog_user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		userlog_field VARCHAR(50) NOT NULL DEFAULT '',
		userlog_value_new TEXT NOT NULL,
		userlog_value_old TEXT NOT NULL,
		userlog_timestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (userlog_id),
		KEY userlog_user_id (userlog_user_id),
		KEY userlog_field (userlog_field)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"users" => " (
		user_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_name VARCHAR(30) NOT NULL DEFAULT '',
		user_algo VARCHAR(10) NOT NULL DEFAULT 'sha256',
		user_salt VARCHAR(40) NOT NULL DEFAULT '',
		user_password VARCHAR(64) NOT NULL DEFAULT '',
		user_admin_algo VARCHAR(10) NOT NULL DEFAULT 'sha256',
		user_admin_salt VARCHAR(40) NOT NULL DEFAULT '',
		user_admin_password VARCHAR(64) NOT NULL DEFAULT '',
		user_email VARCHAR(100) NOT NULL DEFAULT '',
		user_hide_email TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
		user_timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/London',
		user_avatar VARCHAR(100) NOT NULL DEFAULT '',
		user_posts SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
		user_threads TEXT NOT NULL,
		user_joined INT(10) UNSIGNED NOT NULL DEFAULT '0',
		user_lastvisit INT(10) UNSIGNED NOT NULL DEFAULT '0',
		user_ip VARCHAR(45) NOT NULL DEFAULT '0.0.0.0',
		user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
		user_rights TEXT NOT NULL,
		user_groups TEXT NOT NULL,
		user_level TINYINT(4) NOT NULL DEFAULT '-101',
		user_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		user_actiontime INT(10) UNSIGNED NOT NULL DEFAULT '0',
		user_theme VARCHAR(100) NOT NULL DEFAULT 'Default',
		user_location VARCHAR(50) NOT NULL DEFAULT '',
		user_birthdate DATE NOT NULL DEFAULT '0000-00-00',
		user_skype VARCHAR(100) NOT NULL DEFAULT '',
		user_aim VARCHAR(16) NOT NULL DEFAULT '',
		user_icq VARCHAR(15) NOT NULL DEFAULT '',
		user_yahoo VARCHAR(100) NOT NULL DEFAULT '',
		user_web VARCHAR(200) NOT NULL DEFAULT '',
		user_sig VARCHAR(255) NOT NULL DEFAULT '',
		user_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
		PRIMARY KEY (user_id),
		KEY user_name (user_name),
		KEY user_joined (user_joined),
		KEY user_lastvisit (user_lastvisit)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
	"theme" => " (
		theme_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		theme_name VARCHAR(50) NOT NULL,
		theme_title VARCHAR(50) NOT NULL,
		theme_file VARCHAR(200) NOT NULL,
		theme_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
		theme_user MEDIUMINT(8) UNSIGNED NOT NULL,
		theme_active TINYINT(1) UNSIGNED NOT NULL,
		theme_config TEXT NOT NULL,
		PRIMARY KEY (theme_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
//</editor-fold>
if (isset($_POST['uninstall'])) {
	// drop all custom tables.
	foreach (array('articles',
				 'blog',
				 'downloads',
				 'eshop',
				 'faqs',
				 'forums',
				 'news',
				 'photos',
				 'polls',
				 'weblinks') as $table) {
		include __DIR__.'/'.$table.'_setup.php';
	}
	foreach (array_keys($core_tables) as $table) {
		dbquery("DROP TABLE IF EXISTS ".$db_prefix.$table);
	}
} else {
	foreach ($core_tables as $table => $sql) {
		if (!dbquery("CREATE TABLE ".$db_prefix.$table.$sql)) {
			$fail = TRUE;
		}
	}
	// System Inserts
	$siteurl = rtrim(dirname(getCurrentURL()), '/').'/';
	$siteurl = str_replace('install/', '', $siteurl);
	$url = parse_url($siteurl);
	$settings_sql = "INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ";
	$settings_sql .= implode(",\n", array("('sitename', 'PHP-Fusion Powered Website')",
		"('siteurl', '".$siteurl."')",
		"('site_protocol', '".$url['scheme']."')",
		"('site_host', '".$url['host']."')",
		"('site_port', '".(isset($url['port']) ? $url['port'] : "")."')",
		"('site_path', '".(isset($url['path']) ? $url['path'] : "")."')",
		"('site_seo', '0')",
		"('sitebanner', 'images/php-fusion-logo.png')",
		"('sitebanner1', '')",
		"('sitebanner2', '')",
		"('siteemail', '".$email."')",
		"('siteusername', '".$username."')",
		"('siteintro', '<div style=\'text-align:center\'>".$locale['setup_3650']."</div>')",
		"('description', '')",
		"('keywords', '')",
		"('footer', '<div style=\'text-align:center\'>Copyright &copy; ".@date("Y")."</div>')",
		"('opening_page', 'home.php')",
		"('blog_image_readmore', '0')",
		"('blog_image_frontpage', '0')",
		"('blog_thumb_ratio', '0')",
		"('blog_image_link', '1')",
		"('blog_photo_w', '400')",
		"('blog_photo_h', '300')",
		"('blog_thumb_w', '100')",
		"('blog_thumb_h', '100')",
		"('blog_photo_max_w', '1800')",
		"('blog_photo_max_h', '1600')",
		"('blog_photo_max_b', '150000')",
		"('news_thumb_ratio', '0')",
		"('news_image_link', '1')",
		"('news_thumb_w', '100')",
		"('news_thumb_h', '100')",
		"('news_photo_max_w', '1800')",
		"('news_photo_max_h', '1600')",
		"('news_photo_max_b', '150000')",
		"('locale', '".stripinput($_POST['localeset'])."')",
		"('bootstrap', '1')",
		"('theme', 'Septenary')",
		"('admin_theme', 'Venus')",
		"('default_search', 'all')",
		"('exclude_left', '')",
		"('exclude_upper', '')",
		"('exclude_lower', '')",
		"('exclude_aupper', '')",
		"('exclude_blower', '')",
		"('exclude_right', '')",
		"('shortdate', '".$locale['setup_3700']."')",
		"('longdate', '".$locale['setup_3701']."')",
		"('forumdate', '".$locale['setup_3702']."')",
		"('newsdate', '".$locale['setup_3703']."')",
		"('subheaderdate', '".$locale['setup_3704']."')",
		"('timeoffset', 'Europe/London')",
		"('serveroffset', 'Europe/London')",
		"('numofthreads', '15')",
		"('forum_ips', '0')",
		"('attachmax', '150000')",
		"('attachmax_count', '5')",
		"('attachtypes', '.gif,.jpg,.png,.zip,.rar,.tar,.7z')",
		"('thread_notify', '1')",
		"('forum_ranks', '1')",
		"('forum_edit_lock', '0')",
		"('forum_edit_timelimit', '0')",
		"('forum_editpost_to_lastpost', '1')",
		"('forum_last_posts_reply', '10')",
		"('forum_last_post_avatar', '1')",
		"('enable_registration', '1')",
		"('email_verification', '1')",
		"('admin_activation', '0')",
		"('display_validation', '1')",
		"('enable_deactivation', '0')",
		"('deactivation_period', '365')",
		"('deactivation_response', '14')",
		"('enable_terms', '0')",
		"('license_agreement', '')",
		"('license_lastupdate', '0')",
		"('thumb_w', '200')",
		"('thumb_h', '200')",
		"('photo_w', '400')",
		"('photo_h', '400')",
		"('photo_max_w', '1800')",
		"('photo_max_h', '1600')",
		"('photo_max_b', '1512000')",
		"('thumb_compression', 'gd2')",
		"('thumbs_per_row', '4')",
		"('thumbs_per_page', '12')",
		"('photo_watermark', '1')",
		"('photo_watermark_image', 'images/watermark.png')",
		"('photo_watermark_text', '0')",
		"('photo_watermark_text_color1', 'FF6600')",
		"('photo_watermark_text_color2', 'FFFF00')",
		"('photo_watermark_text_color3', 'FFFFFF')",
		"('photo_watermark_save', '0')",
		"('tinymce_enabled', '0')",
		"('smtp_host', '')",
		"('smtp_port', '25')",
		"('smtp_username', '')",
		"('smtp_password', '')",
		"('bad_words_enabled', '1')",
		"('bad_words', '')",
		"('bad_word_replace', '****')",
		"('login_method', '0')",
		"('guestposts', '0')",
		"('comments_enabled', '1')",
		"('ratings_enabled', '1')",
		"('hide_userprofiles', '0')",
		"('userthemes', '1')",
		"('newsperpage', '12')",
		"('blogperpage', '12')",
		"('flood_interval', '15')",
		"('counter', '0')",
		"('version', '9.00.00')",
		"('maintenance', '0')",
		"('maintenance_message', '')",
		"('download_max_b', '512000')",
		"('download_types', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z')",
		"('articles_per_page', '15')",
		"('downloads_per_page', '15')",
		"('links_per_page', '15')",
		"('comments_per_page', '10')",
		"('posts_per_page', '20')",
		"('threads_per_page', '20')",
		"('comments_sorting', 'ASC')",
		"('comments_avatar', '1')",
		"('avatar_width', '100')",
		"('avatar_height', '100')",
		"('avatar_filesize', '50000')",
		"('avatar_ratio', '0')",
		"('cronjob_day', '".time()."')",
		"('cronjob_hour', '".time()."')",
		"('flood_autoban', '1')",
		"('visitorcounter_enabled', '1')",
		"('rendertime_enabled', '0')",
		"('popular_threads_timeframe', '')",
		"('maintenance_level', '102')",
		"('news_photo_w', '400')",
		"('news_photo_h', '300')",
		"('news_image_frontpage', '0')",
		"('news_image_readmore', '0')",
		"('deactivation_action', '0')",
		"('captcha', 'securimage2')",
		"('password_algorithm', 'sha256')",
		"('default_timezone', 'Europe/London')",
		"('userNameChange', '1')",
		"('download_screen_max_b', '150000')",
		"('download_screen_max_w', '1024')",
		"('download_screen_max_h', '768')",
		"('recaptcha_public', '')",
		"('recaptcha_private', '')",
		"('recaptcha_theme', 'red')",
		"('download_screenshot', '1')",
		"('download_thumb_max_w', '100')",
		"('download_thumb_max_h', '100')",
		"('multiple_logins', '0')",
		"('smtp_auth', '0')",
		"('mime_check', '0')",
		"('normalize_seo', '0')",
		"('debug_seo', '0')",
		"('privacy_policy', '')",
		"('create_og_tags', '1')",
		empty($_POST['enabled_languages']) ? "('enabled_languages', '".stripinput($_POST['localeset'])."')" : "('enabled_languages', '".stripinput($enabled_languages)."')"));
	if (!dbquery($settings_sql)) {
		$fail = TRUE;
	}
	$mlt_sql = "INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ";
	$mlt_sql .= implode(",\n", array("('AR', '".$locale['setup_3200']."', '1')",
		"('BL', '".$locale['setup_3213']."', '1')",
		"('CP', '".$locale['setup_3201']."', '1')",
		"('DL', '".$locale['setup_3202']."', '1')",
		"('ES', '".$locale['setup_3214']."', '1')",
		"('FQ', '".$locale['setup_3203']."', '1')",
		"('FO', '".$locale['setup_3204']."', '1')",
		"('FR', '".$locale['setup_3212']."', '1')",
		"('NS', '".$locale['setup_3205']."', '1')",
		"('PG', '".$locale['setup_3206']."', '1')",
		"('PO', '".$locale['setup_3207']."', '1')",
		"('ET', '".$locale['setup_3208']."', '1')",
		"('WL', '".$locale['setup_3209']."', '1')",
		"('SL', '".$locale['setup_3210']."', '1')",
		"('PN', '".$locale['setup_3211']."', '1')"));
	if (!dbquery($mlt_sql)) {
		$fail = TRUE;
	}
	$admin_sql = "INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ";
	$admin_sql .= implode(",\n", array("('AD', 'admins.gif', '".$locale['setup_3000']."', 'administrators.php', '2')",
		"('APWR', 'admin_pass.gif', '".$locale['setup_3047']."', 'admin_reset.php', '2')",
		"('SB', 'banners.gif', '".$locale['setup_3003']."', 'banners.php', '3')",
		"('BB', 'bbcodes.gif', '".$locale['setup_3004']."', 'bbcodes.php', '3')",
		"('B', 'blacklist.gif', '".$locale['setup_3005']."', 'blacklist.php', '2')",
		"('C', '', '".$locale['setup_3006']."', 'reserved', '2')",
		"('CP', 'c-pages.gif', '".$locale['setup_3007']."', 'custom_pages.php', '1')",
		"('DB', 'db_backup.gif', '".$locale['setup_3008']."', 'db_backup.php', '3')",
		"('ERRO', 'errors.png', '".$locale['setup_3048']."', 'errors.php', '3')",
		"('IM', 'images.gif', '".$locale['setup_3013']."', 'images.php', '1')",
		"('I', 'infusions.gif', '".$locale['setup_3014']."', 'infusions.php', '3')",
		"('IP', '', '".$locale['setup_3015']."', 'reserved', '3')",
		"('M', 'members.gif', '".$locale['setup_3016']."', 'members.php', '2')",
		"('P', 'panels.gif', '".$locale['setup_3019']."', 'panels.php', '3')",
		"('PL', 'permalink.gif', '".$locale['setup_3052']."', 'permalink.php', '3')",
		"('PI', 'phpinfo.gif', '".$locale['setup_3021']."', 'phpinfo.php', '3')",
		"('SL', 'site_links.gif', '".$locale['setup_3023']."', 'site_links.php', '3')",
		"('SM', 'smileys.gif', '".$locale['setup_3024']."', 'smileys.php', '3')",
		"('SU', 'submissions.gif', '".$locale['setup_3025']."', 'submissions.php', '2')",
		"('U', 'upgrade.gif', '".$locale['setup_3026']."', 'upgrade.php', '3')",
		"('TS', 'rocket.gif', '".$locale['setup_3056']."', 'theme.php', '3')",
		"('UG', 'user_groups.gif', '".$locale['setup_3027']."', 'user_groups.php', '2')",
		"('S1', 'settings.gif', '".$locale['setup_3030']."', 'settings_main.php', '4')",
		"('S2', 'settings_time.gif', '".$locale['setup_3031']."', 'settings_time.php', '4')",
		"('S4', 'registration.gif', '".$locale['setup_3033']."', 'settings_registration.php', '4')",
		"('S6', 'settings_misc.gif', '".$locale['setup_3035']."', 'settings_misc.php', '4')",
		"('S7', 'settings_pm.png', '".$locale['setup_3036']."', 'settings_messages.php', '4')",
		"('S9', 'settings_users.gif', '".$locale['setup_3041']."', 'settings_users.php', '4')",
		"('S10', 'settings_ipp.gif', '".$locale['setup_3043']."', 'settings_ipp.php', '4')",
		"('S12', 'security.gif', '".$locale['setup_3044']."', 'settings_security.php', '4')",
		"('UF', 'user_fields.gif', '".$locale['setup_3037']."', 'user_fields.php', '2')",
		"('UL', 'user_log.gif', '".$locale['setup_3049']."', 'user_log.php', '2')",
		"('ROB', 'robots.gif', '".$locale['setup_3050']."', 'robots.php', '3')",
		"('MAIL', 'email.gif', '".$locale['setup_3800']."', 'email.php', '3')",
		"('LANG', 'languages.gif', '".$locale['setup_3051']."', 'settings_languages.php', '4')"));
	if (!dbquery($admin_sql)) {
		$fail = TRUE;
	}
	if (!dbquery("INSERT INTO ".$db_prefix."messages_options (user_id, pm_email_notify, pm_save_sent, pm_inbox, pm_savebox, pm_sentbox) VALUES ('0', '0', '1', '20', '20', '20')")) {
		$fail = TRUE;
	}
	$bbcodes_sql = "INSERT INTO ".$db_prefix."bbcodes (bbcode_name, bbcode_order) VALUES ";
	$bbcodes_sql .= implode(",\n", array("('smiley', '1')",
		"('b', '2')",
		"('i', '3')",
		"('u', '4')",
		"('url', '5')",
		"('mail', '6')",
		"('img', '7')",
		"('center', '8')",
		"('small', '9')",
		"('code', '10')",
		"('quote', '11')"));
	if (!dbquery($bbcodes_sql)) {
		$fail = TRUE;
	}
	$smileys_sql = "INSERT INTO ".$db_prefix."smileys (smiley_code, smiley_image, smiley_text) VALUES ";
	$smileys_sql .= implode(",\n", array("(':)', 'smile.gif', '".$locale['setup_3620']."')",
		"(';)', 'wink.gif', '".$locale['setup_3621']."')",
		"(':(', 'sad.gif', '".$locale['setup_3622']."')",
		"(':|', 'frown.gif', '".$locale['setup_3623']."')",
		"(':o', 'shock.gif', '".$locale['setup_3624']."')",
		"(':P', 'pfft.gif', '".$locale['setup_3625']."')",
		"('B)', 'cool.gif', '".$locale['setup_3626']."')",
		"(':D', 'grin.gif', '".$locale['setup_3627']."')",
		"(':@', 'angry.gif', '".$locale['setup_3628']."')"));
	if (!dbquery($smileys_sql)) {
		$fail = TRUE;
	}
	$panels_sql = "INSERT INTO ".$db_prefix."panels (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list) VALUES ";
	$panels_sql .= implode(",\n", array("('".$locale['setup_3400']."', 'css_navigation_panel', '', '1', '1', 'file', '0', '0', '1', '')",
		"('RSS Feeds', 'rss_feeds_panel', '', '1', '2', 'file', '0', '0', '1', '')",
		"('".$locale['setup_3401']."', 'online_users_panel', '', '1', '3', 'file', '0', '0', '1', '')",
		"('".$locale['setup_3404']."', 'welcome_message_panel', '', '2', '1', 'file', '0', '0', '1', '')",
		"('".$locale['setup_3406']."', 'user_info_panel', '', '4', 1, 'file', '0', '0', '1', '')"));
	if (!dbquery($panels_sql)) {
		$fail = TRUE;
	}
	// UF 1.02
	$ufc_sql = "INSERT INTO ".$db_prefix."user_field_cats (field_cat_id, field_cat_name, field_parent, field_cat_db, field_cat_index, field_cat_class, field_cat_order) VALUES ";
	$ufc_sql .= implode(",\n", array("(1, '".$locale['setup_3640']."', 0, '', '', 'entypo user', 1)",
		"(2, '".$locale['setup_3641']."', 1, '', '', 'entypo user', 1)",
		"(3, '".$locale['setup_3642']."', 1, '', '', 'entypo user', 2)",
		"(4, '".$locale['setup_3643']."', 1, '', '', 'entypo user', 3)",
		"(5, '".$locale['setup_3644']."', 1, '', '', 'entypo user', 4)",
		"(6, '".$locale['setup_3645']."', 1, '', '', 'entypo shareable', 5)"));
	if (!dbquery($ufc_sql)) {
		$fail = TRUE;
	}
	// Install UF Modules
	$uf_sql = "INSERT INTO ".$db_prefix."user_fields (field_name, field_cat, field_type, field_required, field_order) VALUES ";
	$uf_sql .= implode(",\n", array("('user_location', '3', 'file', '0', '1')",
		"('user_birthdate', '3', 'file', '0', '2')",
		"('user_skype', '2', 'file', '0', '1')",
		"('user_aim', '2', 'file', '0', '2')",
		"('user_icq', '2', 'file', '0', '3')",
		"('user_yahoo', '2', 'file', '0', '5')",
		"('user_web', '2', 'file', '0', '6')",
		"('user_timezone', '4', 'file', '0', '1')",
		"('user_theme', '4', 'file', '0', '2')",
		"('user_sig', '4', 'file', '0', '3')",
		"('user_blacklist', '5', 'file', '0', '1')"));
	if (!dbquery($uf_sql)) {
		$fail = TRUE;
	}
	$sl_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_cat, link_icon, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ";
	$sl_sql .= implode(",\n", array_map(function ($language) {
		include LOCALE.$language."/setup.php";
		return "('".$locale['setup_3300']."', '0', '', 'index.php', '0', '2', '0', '1', '".$language."'),
				('".$locale['setup_3305']."', '0', '', 'contact.php', '0', '1', '0', '8', '".$language."'),
				('".$locale['setup_3309']."', '0', '', 'search.php', '0', '1', '0', '10', '".$language."'),
				('".$locale['setup_3315']."', '0', '', 'submissions.php', '-101', '1', '0', '10', '".$language."'),
				('---', '0', '', '---', '-101', '1', '0', '11', '".$language."')";
	}, explode('.', $enabled_languages)));
	if (!dbquery($sl_sql)) {
		$fail = TRUE;
	}
	$et_sql = "INSERT INTO ".$db_prefix."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ";
	$et_sql .= implode(",\n", array_map(function ($language) use ($username, $email) {
		include LOCALE.$language."/setup.php";
		return "('', 'PM', 'html', '0', '".$locale['setup_3801']."', '".$locale['setup_3802']."', '".$locale['setup_3803']."', '".$username."', '".$email."', '".$language."'),
				('', 'POST', 'html', '0', '".$locale['setup_3804']."', '".$locale['setup_3805']."', '".$locale['setup_3806']."', '".$username."', '".$email."', '".$language."'),
				('', 'CONTACT', 'html', '0', '".$locale['setup_3807']."', '".$locale['setup_3808']."', '".$locale['setup_3809']."', '".$username."', '".$email."', '".$language."')";
	}, explode('.', $enabled_languages)));
	if (!dbquery($et_sql)) {
		$fail = TRUE;
	}
}
