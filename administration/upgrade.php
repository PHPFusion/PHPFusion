<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: upgrade.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
if (!checkrights("U") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
if (file_exists(LOCALE.LOCALESET."admin/upgrade.php")) {
	include LOCALE.LOCALESET."admin/upgrade.php";
} else {
	include LOCALE."English/admin/upgrade.php";
}

opentable($locale['400']);
echo "<div style='text-align:center'><br />\n";
echo "<form name='upgradeform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
if (str_replace(".", "", $settings['version']) < "90000") {
	if (!isset($_POST['stage'])) {
		echo sprintf($locale['500'], $locale['504'])."<br />\n".$locale['501']."<br /><br />\n";
		echo "<input type='hidden' name='stage' value='2'>\n";
		echo "<input type='submit' name='upgrade' value='".$locale['400']."' class='button'><br /><br />\n";
	} elseif (isset($_POST['upgrade']) && isset($_POST['stage']) && $_POST['stage'] == 2) {
		//Get locales for required language injections
		if (file_exists(LOCALE.LOCALESET."setup.php")) {
			include LOCALE.LOCALESET."setup.php";
		} else {
		include LOCALE."English/setup.php";
		}
		//Check files from earlier installations
		echo "<div style='width:550px; margin:15px auto;' class='tbl'>\n";
		echo "File check, please save and remove according to list.<br />\n";
		echo "<div class='tbl-border' style='margin-top:10px; padding: 5px; text-align:left;'>\n";
		if (file_exists(ADMIN."settings_links.php")) {
			echo "<span style='color:red;'>administration/settings_links.php </span> need to be deleted<br />";
		}
		if (file_exists(ADMIN."shoutbox.php")) {
			echo "<span style='color:red;'>administration/shoutbox.php </span> need to be deleted<br />";
		}
		if (file_exists(ADMIN."updateuser.php")) {
			echo "<span style='color:red;'>administration/updateuser.php </span> need to be deleted<br />";
		}
		if (file_exists(THEMES."templates/header_mce.php")) {
			echo "<span style='color:red;'>themes/templates/header_mce.php </span> need to be deleted<br />";
		}
		if (file_exists(THEMES."templates/admin_header_mce.php")) {
			echo "<span style='color:red;'>themes/templates/admin_header_mce.php </span> need to be deleted<br />";
		}
		if (file_exists(IMAGES."edit.gif")) {
			echo "<span style='color:red;'>images/edit.gif </span> need to be deleted<br />";
		}
		if (file_exists(IMAGES."star.gif")) {
			echo "<span style='color:red;'>images/star.gif </span> need to be deleted<br />";
		}
		if (file_exists(IMAGES."tick.gif")) {
			echo "<span style='color:red;'>images/tick.gif </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/langs/hu.js")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/langs/hu.js </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/compat2x/editor_plugin.js")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/compat2x/editor_plugin.js </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/compat2x/editor_plugin_src.js")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/compat2x/editor_plugin_src.js </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/paste/css/blank.css")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/paste/css/blank.css </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/paste/css/pasteword.css")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/paste/css/pasteword.css </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/paste/blank.htm")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/paste/blank.htm </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/safari/blank.htm")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/safari/blank.htm </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/safari/editor_plugin.js")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/safari/editor_plugin.js </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/safari/editor_plugin_src.js")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/safari/editor_plugin_src.js </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/xhtmlxtras/css/xhtmlxtras.css")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/xhtmlxtras/css/xhtmlxtras.css </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jscripts/tiny_mce/utils/mclayer.js")) {
			echo "<span style='color:red;'>includes/jscripts/tiny_mce/utils/mclayer.js </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."securimage/index.php")) {
			echo "<span style='color:red;'>The folder includes/securimage and it´s content </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."captcha_include.php")) {
			echo "<span style='color:red;'>includes/captcha_include.php </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."jquery.js")) {
			echo "<span style='color:red;'>includes/jquery.js </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."phpmailer_include.php")) {
			echo "<span style='color:red;'>includes/phpmailer_include.php </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."smtp_include.php")) {
			echo "<span style='color:red;'>includes/smtp_include.php </span> need to be deleted<br />";
		}
		if (file_exists(INCLUDES."update_profile_include.php")) {
			echo "<span style='color:red;'>includes/update_profile_include.php </span> need to be deleted<br />";
		}
		if (file_exists(CLASSES."SubCats.class.php")) {
			echo "<span style='color:red;'>includes/classes/SubCats.class.php </span> need to be deleted<br />";
		}
		if (file_exists(INFUSIONS."navigation_panel/index.php")) {
			echo "<span style='color:red;'>The folder infusions/navigation_panel </span> need to be deleted <br /> (<STRONG>Check so you use the new one before!</STRONG>)<br />";
		}
		if (file_exists(LOCALE."English/admin/shoutbox.php")) {
			echo "<span style='color:red;'>locale/English/admin/shoutbox.php </span> need to be deleted<br />";
		}
		if (file_exists(LOCALE."English/edit_profile.php")) {
			echo "<span style='color:red;'>locale/English/edit_profile.php </span> need to be deleted<br />";
		}
		if (file_exists(LOCALE."English/register.php")) {
			echo "<span style='color:red;'>locale/English/register.php </span> need to be deleted<br />";
		}
		if (file_exists(LOCALE."English/view_profile.php")) {
			echo "<span style='color:red;'>locale/English/view_profile.php </span> need to be deleted<br />";
		}
		if (file_exists(BASEDIR."readarticle.php")) {
			echo "<span style='color:red;'>readarticle.php </span> need to be deleted<br />";
		}
		echo "</div></div><br />\n";
		//Function to generate random token keys
		function createRandomToken($length = 32) {
			$chars = array("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789");
			$count = array((strlen($chars[0])-1), (strlen($chars[1])-1));
			$key = "";
			for ($i = 0; $i < $length; $i++) {
				$type = mt_rand(0, 1);
				$key .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
			}
			return $key;
		}

		$secret_key = "".createRandomToken()."";
		$secret_key_salt = "".createRandomToken()."";
		echo "<div style='width:550px; margin:15px auto;' class='tbl'>\n";
		echo "Update your config.php right after COOKIE_PREFIX, with the following : <br />\n";
		echo "<div class='tbl-border' style='margin-top:10px; padding: 5px; text-align:left;'>\n";
		echo "\$pdo_enabled = \"0\"; <br />\n";
		echo "define(\"SECRET_KEY\", \"".$secret_key."\"); <br />\n";
		echo "define(\"SECRET_KEY_SALT\", \"".$secret_key_salt."\"); <br />\n";
		echo "</div><br />";
		echo "Please note that you need to change the \$pdo_enabled = \"0\" to \$pdo_enabled = \"1\" manually in order to enable PDO</div><br />\n";
		//Add language tables to infusions and main content
		$result = dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER article_cat_access");
		$result = dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER page_allow_ratings");
		$result = dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER download_cat_access");
		$result = dbquery("ALTER TABLE ".DB_FAQ_CATS." ADD faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER faq_cat_description");
		$result = dbquery("ALTER TABLE ".DB_FORUM_RANKS." ADD rank_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER rank_apply");
		$result = dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER forum_merge");
		$result = dbquery("ALTER TABLE ".DB_NEWS." ADD news_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_allow_ratings");
		$result = dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_cat_image");
		$result = dbquery("ALTER TABLE ".DB_PANELS." ADD panel_languages VARCHAR(200) NOT NULL DEFAULT '.".$settings['locale']."' AFTER panel_restriction");
		$result = dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_language varchar(50) NOT NULL default '".$settings['locale']."' AFTER album_datestamp");
		$result = dbquery("ALTER TABLE ".DB_POLLS." ADD poll_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER poll_ended");
		$result = dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER link_order");
		$result = dbquery("ALTER TABLE ".DB_USERS." ADD user_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."'");
		$result = dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER weblink_cat_access");
		//Option to align news images
		$result = dbquery("ALTER TABLE ".DB_NEWS." ADD news_ialign VARCHAR(15) NOT NULL DEFAULT '' AFTER news_image_t2");
		//Option to use keywords in news
		$result = dbquery("ALTER TABLE ".DB_NEWS." ADD news_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER news_extended");
		//Option to use keywords in downloads
		$result = dbquery("ALTER TABLE ".DB_DOWNLOADS." ADD download_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER download_description");
		//Option to use keywords in photos
		$result = dbquery("ALTER TABLE ".DB_PHOTOS." ADD photo_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER photo_description");
		//Option to use keywords in custom_pages
		$result = dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER page_content");
		//Option to use keywords in articles
		$result = dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER article_article");
		//Login methods
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('login_method', '0')"); // New: Login method feature
		//Mime check for upload files
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('mime_check', '0')");
		//Delete user_offset field an replace it with user_timezone
		$result = dbquery("ALTER TABLE ".DB_USERS." ADD user_timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/London' AFTER user_offset");
		$result = dbquery("ALTER TABLE ".DB_USERS." DROP COLUMN user_offset");
		// Sub-categories for news
		$result = dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER news_cat_id");
		// Moving access level from article categories to articles and create field for subcategories
		$result = dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_visibility TINYINT(3) NOT NULL DEFAULT '0' AFTER article_datestamp");
		$result = dbquery("SELECT article_cat_id, article_cat_access FROM ".DB_ARTICLE_CATS);
		if (dbrows($result)) {
			while($data = dbarray($result)) {
				$result1 = dbquery("UPDATE ".DB_ARTICLES. " SET article_visibility='".$data['article_cat_access']."' WHERE article_cat='".$data['article_cat_id']."'");
			}
		}
		$result = dbquery("ALTER TABLE ".DB_ARTICLE_CATS." DROP COLUMN article_cat_access");
		$result = dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER article_cat_id");
		// Moving access level from downloads categories to downloads and create field for subcategories
		$result = dbquery("ALTER TABLE ".DB_DOWNLOADS." ADD download_visibility TINYINT(3) NOT NULL DEFAULT '0' AFTER download_datestamp");
		$result = dbquery("SELECT download_cat_id, download_cat_access FROM ".DB_DOWNLOAD_CATS);
		if (dbrows($result)) {
			while($data = dbarray($result)) {
				$result1 = dbquery("UPDATE ".DB_DOWNLOADS. " SET download_visibility='".$data['download_cat_access']."' WHERE download_cat='".$data['download_cat_id']."'");
			}
		}
		$result = dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." DROP COLUMN download_cat_access");
		$result = dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER download_cat_id");
		// Moving access level from weblinks categories to weblinks and create field for subcategories
		$result = dbquery("ALTER TABLE ".DB_WEBLINKS." ADD weblink_visibility TINYINT(3) NOT NULL DEFAULT '0' AFTER weblink_datestamp");
		$result = dbquery("SELECT weblink_cat_id, weblink_cat_access FROM ".DB_WEBLINK_CATS);
		if (dbrows($result)) {
			while($data = dbarray($result)) {
				$result1 = dbquery("UPDATE ".DB_WEBLINKS. " SET weblink_visibility='".$data['weblink_cat_access']."' WHERE weblink_cat='".$data['weblink_cat_id']."'");
			}
		}
		$result = dbquery("ALTER TABLE ".DB_WEBLINK_CATS." DROP COLUMN weblink_cat_access");
		$result = dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER weblink_cat_id");
		//Blog settings
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_image_readmore', '0')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_image_frontpage', '0')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_thumb_ratio', '0')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_image_link', '1')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_w', '400')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_h', '300')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_thumb_w', '100')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_thumb_h', '100')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_max_w', '1800')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_max_h', '1600')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_max_b', '150000')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blogperpage', '12')");
		//Enabled languages array
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('enabled_languages', '".$settings['locale']."')");
		// Language settings admin section
		$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('LANG', 'languages.gif', '".$locale['129c']."', 'settings_languages.php', '4')");
		if ($result) {
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
			while ($data = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".LANG' WHERE user_id='".$data['user_id']."'");
			}
		}
		//Create multilang tables
		$result = dbquery("CREATE TABLE ".DB_PREFIX."mlt_tables (
		mlt_rights CHAR(4) NOT NULL DEFAULT '',
		mlt_title VARCHAR(50) NOT NULL DEFAULT '',
		mlt_status VARCHAR(50) NOT NULL DEFAULT '',
		PRIMARY KEY (mlt_rights)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		//Add Multilang table rights and status
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('AR', '".$locale['MLT001']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('CP', '".$locale['MLT002']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('BL', '".$locale['MLT014']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('DL', '".$locale['MLT003']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('ES', '".$locale['MLT015']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FQ', '".$locale['MLT004']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FO', '".$locale['MLT005']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FR', '".$locale['MLT013']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('NS', '".$locale['MLT006']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PG', '".$locale['MLT007']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PO', '".$locale['MLT008']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('ET', '".$locale['MLT009']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('WL', '".$locale['MLT010']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('SL', '".$locale['MLT011']."', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PN', '".$locale['MLT012']."', '1')");
		// Blog admin section
		$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('BLC', 'blog_cats.gif', '".$locale['130a']."', 'blog_cats.php', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('BLOG', 'blog.gif', '".$locale['130b']."', 'blog.php', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S13', 'settings_blog.gif', '".$locale['130b']."', 'settings_blog.php', '4')");
		// Blog link
		$result = dbquery("INSERT INTO ".DB_PREFIX."site_links (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['130b']."', 'blog.php', '0', '2', '0', '3', '".$settings['locale']."')");
		if ($result) {
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
			while ($data = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".BLOG.BLC.S13' WHERE user_id='".$data['user_id']."'");
			}
		}
		$result = dbquery("CREATE TABLE ".DB_PREFIX."blog (
		blog_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		blog_subject VARCHAR(200) NOT NULL DEFAULT '',
		blog_image VARCHAR(100) NOT NULL DEFAULT '',
		blog_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
		blog_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
		blog_ialign VARCHAR(15) NOT NULL DEFAULT '',
		blog_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		blog_blog TEXT NOT NULL,
		blog_extended TEXT NOT NULL,
		blog_keywords VARCHAR(250) NOT NULL DEFAULT '',
		blog_breaks CHAR(1) NOT NULL DEFAULT '',
		blog_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
		blog_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		blog_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
		blog_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
		blog_visibility TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
		blog_reads INT(10) UNSIGNED NOT NULL DEFAULT '0',
		blog_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		blog_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
		blog_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
		blog_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
		blog_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."',
		PRIMARY KEY (blog_id),
		KEY blog_datestamp (blog_datestamp),
		KEY blog_reads (blog_reads)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		$result = dbquery("CREATE TABLE ".DB_PREFIX."blog_cats (
		blog_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		blog_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		blog_cat_name VARCHAR(100) NOT NULL DEFAULT '',
		blog_cat_image VARCHAR(100) NOT NULL DEFAULT '',
		blog_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."',
		PRIMARY KEY (blog_cat_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['180']."', 'bugs.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['181']."', 'downloads.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['182']."', 'games.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['183']."', 'graphics.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['184']."', 'hardware.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['185']."', 'journal.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['186']."', 'members.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['187']."', 'mods.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['188']."', 'movies.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['189']."', 'network.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['191']."', 'php-fusion.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['192']."', 'security.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['193']."', 'software.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['194']."', 'themes.gif', '".$settings['locale']."')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['195']."', 'windows.gif', '".$settings['locale']."')");

		
		//eShop section
		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop (
		id mediumint(8) unsigned NOT NULL auto_increment,
		title varchar(200) NOT NULL default '',
		cid mediumint(8) NOT NULL default '0',
		picture varchar(200) NOT NULL default '',
		thumb varchar(200) NOT NULL default '',
		thumb2 varchar(200) NOT NULL default '',
		introtext varchar(255) NOT NULL default '',
		description text NOT NULL,
		anything1 text NOT NULL,
		anything1n varchar(50) NOT NULL default '',
		anything2 text NOT NULL,
		anything2n varchar(50) NOT NULL default '',
		anything3 text NOT NULL,
		anything3n varchar(50) NOT NULL default '',
		weight varchar(10) NOT NULL default '',
		price varchar(15) NOT NULL default '',
		xprice varchar(15) NOT NULL default '',
		stock mediumint(8) NOT NULL default '0',
		version char(3) NOT NULL default '0',
		status char(1) NOT NULL default '',
		active char(1) NOT NULL default '',
		gallery_on char(1) NOT NULL default '',
		delivery varchar(250) NOT NULL default '',
		demo varchar(100) NOT NULL default '',
		cart_on char(1) NOT NULL default '',
		buynow char(1) NOT NULL default '',
		rpage varchar(20) NOT NULL default '',
		icolor text NOT NULL,
		dynf varchar(50) NOT NULL default '',
		dync text NOT NULL,
		qty char(1) NOT NULL default '',
		sellcount mediumint(8) NOT NULL default '0',
		iorder smallint(5) NOT NULL default '0',
		artno varchar(15) NOT NULL default '',
		sartno varchar(15) NOT NULL default '',
		instock mediumint(8) NOT NULL default '0',
		dmulti mediumint(8) NOT NULL default '0',
		cupons char(1) NOT NULL default '',
		access tinyint(3) NOT NULL default '0',
		campaign char(1) NOT NULL default '',
		comments char(1) NOT NULL default '',
		ratings char(1) NOT NULL default '',
		linebreaks char(1) NOT NULL default '',
		keywords varchar(255) NOT NULL default '',
		product_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['locale']."',
		dateadded int(10) unsigned NOT NULL default '1',
		PRIMARY KEY  (id),
		KEY cid (cid)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_cats (
		cid mediumint(8) unsigned NOT NULL auto_increment,
		title varchar(45) NOT NULL default '',
		access tinyint(3) NOT NULL default '0',
		image varchar(45) NOT NULL default '0',
		parentid mediumint(8) NOT NULL default '0',
		status char(1) NOT NULL default '0',
		cat_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['locale']."',
		PRIMARY KEY  (cid)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		
		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_customers (
		cuid mediumint(8) NOT NULL default '0',
		cfirstname varchar(50) NOT NULL default '',
		clastname varchar(50) NOT NULL default '',
		cdob varchar(20) NOT NULL default '',
		ccountry_code varchar(5) NOT NULL default '',
		cregion varchar(50) NOT NULL default '',
		ccity varchar(50) NOT NULL default '',
		caddress varchar(55) NOT NULL default '',
		caddress2 varchar(55) NOT NULL default '',
		cpostcode varchar(10) NOT NULL default '',
		cphone varchar(20) NOT NULL default '',
		cfax varchar(20) NOT NULL default '',
		cemail varchar(50) NOT NULL default '',
		ccupons text NOT NULL,
		PRIMARY KEY  (cuid)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_photos (
		photo_id mediumint(8) unsigned NOT NULL auto_increment,
		album_id mediumint(8) unsigned NOT NULL default '0',
		photo_title varchar(100) NOT NULL default '',
		photo_description text NOT NULL,
		photo_filename varchar(100) NOT NULL default '',
		photo_thumb1 varchar(100) NOT NULL default '',
		photo_thumb2 varchar(100) NOT NULL default '',
		photo_datestamp int(10) unsigned NOT NULL default '0',
		photo_user mediumint(8) unsigned NOT NULL default '0',
		photo_views mediumint(8) unsigned NOT NULL default '0',
		photo_order smallint(5) unsigned NOT NULL default '0',
		photo_allow_comments tinyint(1) unsigned NOT NULL default '1',
		photo_last_viewed int(10) unsigned NOT NULL default '1',
		PRIMARY KEY  (photo_id),
		KEY photo_user (photo_user)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_cart (
		tid mediumint(8) NOT NULL auto_increment,
		puid varchar(45) NOT NULL default '0',
		prid mediumint(8) unsigned NOT NULL default '0',
		artno varchar(50) NOT NULL default '',
		citem varchar(250) NOT NULL default '',
		cimage varchar(50) NOT NULL default '',
		cqty mediumint(8) NOT NULL default '0',
		cclr varchar(50) NOT NULL default '',
		cdyn varchar(50) NOT NULL default '',
		cdynt varchar(55) NOT NULL default '',
		cprice varchar(15) NOT NULL default '',
		cweight varchar(10) NOT NULL default '',
		ccupons tinyint(1) NOT NULL default '0',
		cadded int(10) NOT NULL default '0',
		PRIMARY KEY  (tid),
		KEY puid (puid)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_shippingcats (
		cid mediumint(8) NOT NULL auto_increment,
		title varchar(50) NOT NULL default '',
		image varchar(100) NOT NULL default '',
		PRIMARY KEY  (cid)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_shippingitems (
		sid mediumint(8) NOT NULL auto_increment,
		cid mediumint(8) NOT NULL default '0',
		method varchar(100) NOT NULL default '',
		dtime varchar(100) NOT NULL default '',
		destination char(1) NOT NULL default '',
		weightmin varchar(100) NOT NULL default '',
		weightmax varchar(100) NOT NULL default '',
		weightcost smallint(5) NOT NULL default '0',
		initialcost smallint(5) NOT NULL default '0',
		active char(1) NOT NULL default '',
		PRIMARY KEY  (sid)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_payments (
		pid mediumint(8) NOT NULL auto_increment,
		method text NOT NULL,
		description text NOT NULL,
		image varchar(100) NOT NULL default '',
		surcharge smallint(5) NOT NULL default '0',
		code text NOT NULL,
		cfile varchar(100) NOT NULL default '',
		active char(1) NOT NULL default '',
		PRIMARY KEY  (pid)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_orders (
		oid mediumint(8) NOT NULL auto_increment,
		ouid varchar(15) NOT NULL default '',
		oname varchar(50) NOT NULL default '',
		oitems text NOT NULL,
		oorder text NOT NULL,
		oemail varchar(50) NOT NULL default '',
		opaymethod tinyint(2) NOT NULL default '0',
		oshipmethod tinyint(2) NOT NULL default '0',
		odiscount varchar(10) NOT NULL default '',
		ovat mediumint(8) NOT NULL default '0',
		ototal varchar(50) NOT NULL default '',
		omessage varchar(255) NOT NULL default '',
		oamessage varchar(255) NOT NULL default '',
		ocompleted char(1) NOT NULL default '',
		opaid char(1) NOT NULL default '',
		odate int(10) NOT NULL default '0',
		PRIMARY KEY  (oid),
		KEY ouid (ouid)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_cupons (
		cuid VARCHAR( 15 ) NOT NULL DEFAULT  '',
		cuname varchar(50) NOT NULL default '',
		cutype char(1) NOT NULL default '',
		cuvalue smallint(5) NOT NULL default '0',
		custart INT( 10 ) NOT NULL ,
		cuend INT( 10 ) NOT NULL ,
		active CHAR( 1 ) NOT NULL DEFAULT  '',
		PRIMARY KEY ( cuid )
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_featitems (
		featitem_id mediumint(8) unsigned NOT NULL auto_increment,
		featitem_item mediumint(8) unsigned NOT NULL default '0',
		featitem_cid mediumint(8) unsigned NOT NULL default '0',
		featitem_order smallint(5) unsigned NOT NULL default '0',
		PRIMARY KEY  (featitem_id),
		KEY cid (featitem_item)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

		$result = dbquery("CREATE TABLE ".DB_PREFIX."eshop_featbanners (
		featbanner_aid mediumint(8) unsigned NOT NULL auto_increment,
		featbanner_id mediumint(8) unsigned NOT NULL default '0',
		featbanner_url varchar(100) NOT NULL default '',
		featbanner_cat mediumint(8) unsigned NOT NULL default '0',
		featbanner_banner varchar(100) NOT NULL default '',
		featbanner_cid mediumint(8) unsigned NOT NULL default '0',
		featbanner_order smallint(5) unsigned NOT NULL default '0',
		PRIMARY KEY  (featbanner_aid),
		KEY featbanner_id (featbanner_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

	//Populate shop settings
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_cats', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_cat_disp', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_nopp', '6')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_noppf', '9')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_target', '_self')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_folderlink', '0')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_selection', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_cookies', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_bclines', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_icons', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_statustext', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_closesamelevel', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_inorder', '0')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_shopmode', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_returnpage', 'ordercompleted.php')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_ppmail', '')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_ipr', '3')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_ratios', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_idisp_h', '130')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_idisp_w', '100')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_idisp_h2', '180')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_idisp_w2', '250')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_catimg_w', '100')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_catimg_h', '100')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_image_w', '6400')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_image_h', '6400')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_image_b', '9999999')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_image_tw', '150')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_image_th', '100')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_image_t2w', '250')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_image_t2h', '250')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_buynow_color', 'blue')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_checkout_color', 'green')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_cart_color', 'red')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_addtocart_color', 'magenta')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_info_color', 'orange')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_return_color', 'yellow')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_pretext', '0')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_pretext_w', '190px')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_listprice', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_currency', 'USD')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_shareing', '1')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_weightscale', 'KG')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_vat', '25')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_vat_default', '0')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_terms', '<h2> Ordering </h2><br />\r\nWhilst all efforts are made to ensure accuracy of description, specifications and pricing there may <br />be occasions where errors arise. Should such a situation occur [Company name] cannot accept your order. <br /> In the event of a mistake you will be contacted with a full explanation and a corrected offer. <br />The information displayed is considered as an invitation to treat not as a confirmed offer for sale. \r\nThe contract is confirmed upon supply of goods.\r\n<br /><br /><br />\r\n<h2>Delivery and Returns</h2><br />\r\n[Company name] returns policy has been set up to keep costs down and to make the process as easy for you as possible. You must contact us and be in receipt of a returns authorisation (RA) number before sending any item back. Any product without a RA number will not be refunded. <br /><br /><br />\r\n<h2> Exchange </h2><br />\r\n If when you receive your product(s), you are not completely satisfied you may return the items to us, within seven days of exchange or refund. Returns will take approximately 5 working days for the process once the goods have arrived. Items must be in original packaging, in all original boxes, packaging materials, manuals blank warranty cards and all accessories and documents provided by the manufacturer.<br /><br /><br />\r\n\r\nIf our labels are removed from the product â€“ the warranty becomes void.<br /><br /><br />\r\n\r\nWe strongly recommend that you fully insure your package that you are returning. We suggest the use of a carrier that can provide you with a proof of delivery. [Company name] will not be held responsible for items lost or damaged in transit.<br /><br /><br />\r\n\r\nAll shipping back to [Company name] is paid for by the customer. We are unable to refund you postal fees.<br /><br /><br />\r\n\r\nAny product returned found not to be defective can be refunded within the time stated above and will be subject to a 15% restocking fee to cover our administration costs. Goods found to be tampered with by the customer will not be replaced but returned at the customers expense. <br /><br /><br />\r\n\r\n If you are returning items for exchange please be aware that a second charge may apply. <br /><br /><br />\r\n\r\n<h2>Non-Returnable </h2><br />\r\n For reasons of hygiene and public health, refunds/exchanges are not available for used ......... (this does not apply to faulty goods â€“ faulty products will be exchanged like for like)<br /><br /><br />\r\n\r\nDiscounted or our end of line products can only be returned for repair no refunds of replacements will be made.<br /><br /><br />\r\n\r\n<h2> Incorrect/Damaged Goods </h2><br />\r\n\r\n We try very hard to ensure that you receive your order in pristine condition. If you do not receive your products ordered. Please contract us. In the unlikely event that the product arrives damaged or faulty, please contact [Company name] immediately, this will be given special priority and you can expect to receive the correct item within 72 hours. Any incorrect items received all delivery charges will be refunded back onto you credit/debit card.<br /><br /><br />\r\n\r\n<h2>Delivery service</h2><br />\r\nWe try to make the delivery process as simple as possible and our able to send your order either you home or to your place of work.<br /><br /><br />\r\n\r\nDelivery times are calculated in working days Monday to Friday. If you order after 4 pm the next working day will be considered the first working day for delivery. In case of bank holidays and over the Christmas period, please allow an extra two working days.<br /><br /><br />\r\n\r\nWe aim to deliver within 3 working days but sometimes due to high order volume certain in sales periods please allow 4 days before contacting us. We will attempt to email you if we become aware of an unexpected delay. <br /><br /><br />\r\n\r\nAll small orders are sent out via royal mail 1st packets post service, if your order is over Â£15.00 it will be sent out via royal mails recorded packet service, which will need a signature, if you are not present a card will be left to advise you to pick up your goods from the local sorting office.<br /><br /><br />\r\n\r\nEach item will be attempted to be delivered twice. Failed deliveries after this can be delivered at an extra cost to you or you can collect the package from your local post office collection point.<br /><br /><br />\r\n\r\n<h2>Export restrictions</h2><br /><br /><br />\r\n\r\nAt present [Company name] only sends goods within the [Country]. We plan to add exports to our services in the future. If however you have a special request please contact us your requirements.<br /><br /><br />\r\n\r\n<h2> Privacy Notice </h2><br />\r\n\r\nThis policy covers all users who register to use the website. It is not necessary to purchase anything in order to gain access to the searching facilities of the site.<br /><br /><br />\r\n\r\n<h2> Security </h2><br />\r\nWe have taken the appropriate measures to ensure that your personal information is not unlawfully processed. [Company name] uses industry standard practices to safeguard the confidentiality of your personal identifiable information, including â€˜firewallsâ€™ and secure socket layers. <br /><br /><br />\r\n\r\nDuring the payment process, we ask for personal information that both identifies you and enables us to communicate with you. <br /><br /><br />\r\n\r\nWe will use the information you provide only for the following purposes.<br /><br /><br />\r\n\r\n* To send you newsletters and details of offers and promotions in which we believe you will be interested. <br />\r\n* To improve the content design and layout of the website. <br />\r\n* To understand the interest and buying behavior of our registered users<br />\r\n* To perform other such general marketing and promotional focused on our products and activities. <br />\r\n\r\n<h2> Conditions Of Use </h2><br />\r\n[Company name] and its affiliates provide their services to you subject to the following conditions. If you visit our shop at [Company name] you accept these conditions. Please read them carefully, [Company name] controls and operates this site from its offices within the [Country]. The laws of [Country] relating to including the use of, this site and materials contained. <br /><br /><br />\r\n\r\nIf you choose to access from another country you do so on your own initiave and are responsible for compliance with applicable local lands. <br /><br /><br />\r\n\r\n<h2> Copyrights </h2><br />\r\nAll content includes on the site such as text, graphics logos button icons images audio clips digital downloads and software are all owned by [Company name] and are protected by international copyright laws. <br /><br /><br />\r\n\r\n<h2> License and Site Access </h2><br />\r\n[Company name] grants you a limited license to access and make personal use of this site. This license doses not include any resaleâ€™s of commercial use of this site or its contents any collection and use of any products any collection and use of any product listings descriptions or prices any derivative use of this site or its contents, any downloading or copying of account information. For the benefit of another merchant or any use of data mining, robots or similar data gathering and extraction tools.<br /><br /><br />\r\n\r\nThis site may not be reproduced duplicated copied sold â€“ resold or otherwise exploited for any commercial exploited without written consent of [Company name].<br /><br /><br />\r\n\r\n<h2> Product Descriptions </h2><br />\r\n[Company name] and its affiliates attempt to be as accurate as possible however we do not warrant that product descriptions or other content is accurate complete reliable, or error free.<br /><br /><br />\r\nFrom time to time there may be information on [Company name] that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing and availability.<br /><br /><br />\r\nWe reserve the right to correct ant errors inaccuracies or omissions and to change or update information at any time without prior notice. (Including after you have submitted your order) We apologies for any inconvenience this may cause you. <br /><br /><br />\r\n\r\n<h2> Prices </h2><br />\r\nPrices and availability of items are subject to change without notice the prices advertised on this site are for orders placed and include VAT and delivery.<br /><br /><br />\r\n<br /><br /><br />\r\nPlease review our other policies posted on this site. These policies also govern your visit to [Company name]')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_itembox_w', '200px')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_itembox_h', '300px')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_cipr', '3')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_newtime', '604800')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_freeshipsum', '0')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('eshop_coupons', '0')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('ESHP', 'eshop.gif', '".$locale['129f']."', 'settings_eshop.php', '4')");

	//Populate shipping cats with some defaults
		$result = dbquery("INSERT INTO ".DB_PREFIX."eshop_shippingcats (cid, title, image) VALUES 
				(1, 'Generic', 'generic.png'),
				(2, 'DHL', 'dhl.png'),
				(3, 'FedEX', 'fedex.png'),	
				(4, 'UPS', 'ups.png'), 
				(5, 'Post Office', 'postoffice.png'), 
				(6, 'Ptt', 'ptt.png'),
				(7, 'TNT', 'tnt.png')");
				
	//Populate shipping items with some defaults
		$result = dbquery("INSERT INTO ".DB_PREFIX."eshop_shippingitems (sid, cid, method, dtime, destination, weightmin, weightmax, weightcost, initialcost, active) VALUES
				(1, 1, 'No Shipping - Visit store', '0', '0', '0.00', '0', 0, 0, '1'),
				(2, 4, 'UPS Express', '1 Day', '2', '0.00', '150', 0, 250, '1'),
				(3, 4, 'UPS Express', '1 Day', '2', '0.00', '150', 0, 250, '1'),
				(4, 2, 'DHL Worldwide Priority Express', '1 - 2 Days', '3', '0.00', '150', 6, 69, '1'),
				(5, 2, 'DHL National Priority Express', '1 - 2 Days', '2', '0.00', '150', 0, 150, '1')");
		
	//Add some default payment method examples
		$result = dbquery("INSERT INTO ".DB_PREFIX."eshop_payments (pid, method, description, image, surcharge, code, cfile, active) VALUES
		(1, 'Invoice', 'We will send an Invoice to your adress. \r\nA credit check will be run.\r\nIn order to make a credit check we need your complete date of birth.', 'invoice.png', 2, '', 'invoice.php', '1'),
		(2, 'PayPal', 'Checkout with PayPal, It´s safe and fast. \r\nYou can use most credit cards here.', 'Paypal.png', 0, '', 'paypal.php', '1'),
		(3, 'Prepayment', 'If you select this option you will need to transfer money directly to our account from your account. \r\nSubmit this order for account details.', 'creditcards.png', 0, '', 'prepayment.php', '1'),
		(4, 'Visit store', 'If you select this option you will need to visit our store and pay your order.\r\n Please bring your OrderID.', 'cash.png', 0, '', '', '1')");

	//Add a site link
		$result = dbquery("INSERT INTO ".DB_PREFIX."site_links (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['129f']."', 'eshop.php', '0', '2', '0', '3', '".$settings['locale']."')");

	// eShop admin and rights sections
		$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('ESHP', 'eshop.gif', '".$locale['129f']."', 'eshop.php', '1')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('ESHP', 'eshop.gif', '".$locale['129f']."', 'settings_eshop.php', '4')");
		if ($result) {
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
			while ($data = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".ESHP' WHERE user_id='".$data['user_id']."'");
			}
		}
		
		//Update tables from previous installs
		$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD comments char(1) NOT NULL default '' AFTER campaign");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD ratings char(1) NOT NULL default '' AFTER comments");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD linebreaks char(1) NOT NULL default '' AFTER ratings");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD keywords varchar(255) NOT NULL default '' AFTER linebreaks");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD product_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['locale']."' AFTER keywords");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop_cats ADD cat_order MEDIUMINT(8) UNSIGNED NOT NULL AFTER status");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop_cats ADD cat_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['locale']."' AFTER cat_order");
		$result = dbquery("RENAME TABLE `".DB_PREFIX."eshop_cupons` TO `".DB_PREFIX."eshop_coupons`");		
		
		// Email templates admin section
		$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MAIL', 'email.gif', '".$locale['T001']."', 'email.php', '1')");
		if ($result) {
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
			while ($data = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".MAIL' WHERE user_id='".$data['user_id']."'");
			}
		}
		$result = dbquery("CREATE TABLE ".DB_PREFIX."email_templates (
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
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if ($result) {
			$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'PM', 'html', '0', '".$locale['T101']."', '".$locale['T102']."', '".$locale['T103']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'POST', 'html', '0', '".$locale['T201']."', '".$locale['T202']."', '".$locale['T203']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'CONTACT', 'html', '0', '".$locale['T301']."', '".$locale['T302']."', '".$locale['T303']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
		}
		//Forum's items per page
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('posts_per_page', '20')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('threads_per_page', '20')");
		// SEO tables.
		$result = dbquery("CREATE TABLE ".DB_PREFIX."permalinks_alias (
							alias_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
							alias_url VARCHAR(200) NOT NULL DEFAULT '',
							alias_php_url VARCHAR(200) NOT NULL DEFAULT '',
							alias_type VARCHAR(10) NOT NULL DEFAULT '',
							alias_item_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
							PRIMARY KEY (alias_id),
							KEY alias_id (alias_id)
							) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		$result = dbquery("CREATE TABLE ".DB_PREFIX."permalinks_method (
							pattern_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
							pattern_type INT(5) UNSIGNED NOT NULL,
							pattern_source VARCHAR(200) NOT NULL DEFAULT '',
							pattern_target VARCHAR(200) NOT NULL DEFAULT '',
							pattern_cat VARCHAR(10) NOT NULL DEFAULT '',
							PRIMARY KEY (pattern_id)
							) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		$result = dbquery("CREATE TABLE ".DB_PREFIX."permalinks_rewrites (
							rewrite_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
							rewrite_name VARCHAR(50) NOT NULL DEFAULT '',
							PRIMARY KEY (rewrite_id)
							) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		// create admin page for permalinks
		$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PL', 'permalink.gif', '".$locale['SEO']."', 'permalink.php', '3')");
		// upgrade admin rights for permalink admin
		if ($result) {
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
			while ($data = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".PL' WHERE user_id='".$data['user_id']."'");
			}
		}
		// Messages
		$result = dbquery("ALTER TABLE ".DB_PREFIX."messages ADD message_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER message_from");
		// User Fields 1.02
		$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_db VARCHAR(200) NOT NULL AFTER field_cat_name");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_index VARCHAR(200) NOT NULL AFTER field_cat_db");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_class VARCHAR(50) NOT NULL AFTER field_cat_index");
		$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_page SMALLINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER field_cat_class");
        $result = dbquery("INSERT INTO ".DB_PREFIX."user_field_cats (field_cat_id, field_cat_name, field_cat_db, field_cat_index, field_cat_class, field_cat_page, field_cat_order) VALUES (5, 'Privacy', '', '', 'entypo shareable', 1, 5)");
		$result = dbquery("INSERT INTO ".DB_PREFIX."user_fields (field_id, field_name, field_cat, field_required, field_log, field_registration, field_order) VALUES ('', 'user_blacklist', '5', '0', '0', '0', '1'");
		// Add black list table
		$result = dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_blacklist TEXT NOT NULL AFTER user_language");
		// site settings for SEO / SEF
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('site_seo', '0')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('normalize_seo', '0')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('debug_seo', '0')");
		// site settings panel exclusions for the new positons
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('exclude_aupper', '')");
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('exclude_blower', '')");
		// Admin Theme
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('admin_theme', 'Venus')");
		// Bootstrap
		$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('bootstrap', '1')");
		// Set a new default theme to prevent issues during upgrade
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Septenary' WHERE settings_name='theme'");
		//User sig issue
		$result = dbquery("ALTER TABLE ".DB_PREFIX."users CHANGE user_sig user_sig VARCHAR(255) NOT NULL DEFAULT ''");
		// enable a default error handler with .htaccess and create a backup of existing one
		if (!file_exists(BASEDIR.".htaccess")) {
			if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
				@rename(BASEDIR."_htaccess", BASEDIR.".htaccess");
			} else {
				// create a file.
				$handle = fopen(BASEDIR.".htaccess", "w");
				fclose($handle);
			}
		}
		// Wipe out all .htaccess rewrite rules and add error handler only
		$htc = "#Force utf-8 charset\r\n";
		$htc .= "AddDefaultCharset utf-8\r\n";
		$htc .= "#Security\r\n";
		$htc .= "ServerSignature Off\r\n";
		$htc .= "#secure htaccess file\r\n";
		$htc .= "<Files .htaccess>\r\n";
		$htc .= "order allow,deny\r\n";
		$htc .= "deny from all\r\n";
		$htc .= "</Files>\r\n";
		$htc .= "#protect config.php\r\n";
		$htc .= "<Files config.php>\r\n";
		$htc .= "order allow,deny\r\n";
		$htc .= "deny from all\r\n";
		$htc .= "</Files>\r\n";
		$htc .= "#Block Nasty Bots\r\n";
		$htc .= "SetEnvIfNoCase ^User-Agent$ .*(craftbot|download|extract|stripper|sucker|ninja|clshttp|webspider|leacher|collector|grabber|webpictures) HTTP_SAFE_BADBOT\r\n";
		$htc .= "SetEnvIfNoCase ^User-Agent$ .*(libwww-perl|aesop_com_spiderman) HTTP_SAFE_BADBOT\r\n";
		$htc .= "Deny from env=HTTP_SAFE_BADBOT\r\n";
		$htc .= "#Disable directory listing\r\n";
		$htc .= "Options All -Indexes\r\n";
		$htc .= "ErrorDocument 400 ".$settings['siteurl']."error.php?code=400\r\n";
		$htc .= "ErrorDocument 401 ".$settings['siteurl']."error.php?code=401\r\n";
		$htc .= "ErrorDocument 403 ".$settings['siteurl']."error.php?code=403\r\n";
		$htc .= "ErrorDocument 404 ".$settings['siteurl']."error.php?code=404\r\n";
		$htc .= "ErrorDocument 500 ".$settings['siteurl']."error.php?code=500\r\n";
		$temp = fopen(BASEDIR.".htaccess", "w");
		if (fwrite($temp, $htc)) {
			fclose($temp);
		}
		//Set the new version
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='9.00.00' WHERE settings_name='version'");
		echo "<br />".$locale['502']."<br /><br />\n";
	}
} else {
		echo "<br />".$locale['401']."<br /><br />\n";
}
echo "</form>\n</div>\n";
closetable();
require_once THEMES."templates/footer.php";
?>