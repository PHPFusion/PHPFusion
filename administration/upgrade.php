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
if (str_replace(".", "", $settings['version']) < "70300") {
	if (!isset($_POST['stage'])) {
		echo sprintf($locale['500'], $locale['504'])."<br />\n".$locale['501']."<br /><br />\n";
		echo "<input type='hidden' name='stage' value='2'>\n";
		echo "<input type='submit' name='upgrade' value='".$locale['400']."' class='button'><br /><br />\n";
	} elseif (isset($_POST['upgrade']) && isset($_POST['stage']) && $_POST['stage'] == 2) {
		//Get locales for required language injections
		if (file_exists(LOCALE.LOCALESET."setup.php")) {
			include LOCALE.LOCALESET."setup.php";
		} else {
			include LOCALE."setup.php";
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
		echo "Please note that you need to change the \$pdo_enabled = \"0\" to \$pdo_enabled = \"1\" manually in order to enable PDO</div>\n";
		//Add language tables
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
		//Login methods
		$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('login_method', '0')"); // New: Login method feature
		//Mime check for upload files
		$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('mime_check', '0')");
		//Enabled languages array
		$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('enabled_languages', '".$_POST['enabled_languages']."')");
		// Language settings admin section
		$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('LANG', 'languages.gif', '".$locale['129c']."', 'settings_languages.php', '4')");
		if ($result) {
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
			while ($data = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".LANG' WHERE user_id='".$data['user_id']."'");
			}
		}
		//Create guest language session tables
		$result = dbquery("CREATE TABLE ".$db_prefix."language_sessions (
		user_ip VARCHAR(20) NOT NULL DEFAULT '0.0.0.0',
		user_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."',
		user_datestamp INT(10) NOT NULL default '0'   
		) ENGINE=MYISAM;");
		//Create multilang tables
		$result = dbquery("CREATE TABLE ".$db_prefix."mlt_tables (
		mlt_rights CHAR(4) NOT NULL DEFAULT '',
		mlt_title VARCHAR(50) NOT NULL DEFAULT '',
		mlt_status VARCHAR(50) NOT NULL DEFAULT '',
		PRIMARY KEY (mlt_rights)
		) ENGINE=MYISAM;");
		//Add Multilang table rights and status
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('AR', '".$locale['MLT001']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('CP', '".$locale['MLT002']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('DL', '".$locale['MLT003']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FQ', '".$locale['MLT004']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FO', '".$locale['MLT005']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FR', '".$locale['MLT013']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('NS', '".$locale['MLT006']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PG', '".$locale['MLT007']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PO', '".$locale['MLT008']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('ET', '".$locale['MLT009']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('WL', '".$locale['MLT010']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('SL', '".$locale['MLT011']."', '1')");
		$result = dbquery("INSERT INTO ".$db_prefix."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PN', '".$locale['MLT012']."', '1')");
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
		) ENGINE=MyISAM;");
		if ($result) {
			$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email) VALUES ('', 'PM', 'html', '0', '".$locale['T101']."', '".$locale['T102']."', '".$locale['T103']."', '".$settings['siteusername']."', '".$settings['siteemail']."')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email) VALUES ('', 'POST', 'html', '0', '".$locale['T201']."', '".$locale['T202']."', '".$locale['T203']."', '".$settings['siteusername']."', '".$settings['siteemail']."')");
			$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email) VALUES ('', 'CONTACT', 'html', '0', '".$locale['T301']."', '".$locale['T302']."', '".$locale['T303']."', '".$settings['siteusername']."', '".$settings['siteemail']."')");
		}
		
		//Forum's items per page
		$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('posts_per_page', '20')");
		$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('threads_per_page', '20')");

		// SEO tables.
		$result = dbquery("CREATE TABLE ".$db_prefix."permalinks_alias (
							alias_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
							alias_url VARCHAR(200) NOT NULL DEFAULT '',
							alias_php_url VARCHAR(200) NOT NULL DEFAULT '',
							alias_type VARCHAR(10) NOT NULL DEFAULT '',
							alias_item_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
							PRIMARY KEY (alias_id),
							KEY alias_id (alias_id)
							) ENGINE=MYISAM;");

		$result = dbquery("CREATE TABLE ".$db_prefix."permalinks_method (
							pattern_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
							pattern_type INT(5) UNSIGNED NOT NULL,
							pattern_source VARCHAR(200) NOT NULL DEFAULT '',
							pattern_target VARCHAR(200) NOT NULL DEFAULT '',
							pattern_cat VARCHAR(10) NOT NULL DEFAULT '',
							PRIMARY KEY (pattern_id)
							) ENGINE=MYISAM;");

		$result = dbquery("CREATE TABLE ".$db_prefix."permalinks_rewrites (
							rewrite_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
							rewrite_name VARCHAR(50) NOT NULL DEFAULT '',
							PRIMARY KEY (rewrite_id)
							) ENGINE=MYISAM;");
		// server settings for seo.
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='0' WHERE settings_name='site_seo'");
		// create admin page for permalinks
		$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PL', 'permalink.gif', '".$locale['SEO']."', 'permalink.php', '3')");
		// upgrade admin rights for permalink admin
		if ($result) {
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
			while ($data = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".PL' WHERE user_id='".$data['user_id']."'");
			}
		}
		// User Fields 1.02
		$result = dbquery("ALTER TABLE ".$db_prefix."user_field_cats ADD field_cat_db VARCHAR(100) NOT NULL panel_languages VARCHAR(200),
		 field_cat_index VARCHAR(100) NOT NULL, field_cat_class VARHCAR(50) NOT NULL, field_cat_page SMALLINT(1) NOT NULL UNSIGNED AFTER field_cat_name");
		$result = dbquery("INSERT INTO ".$db_prefix."user_field_cats (field_cat_id, field_cat_name, field_cat_db, field_cat_index, field_cat_class, field_cat_page, field_cat_order) VALUES (5, '".$locale['UF']."', '', '', 'entypo shareable', 1, 5)");
		$result = dbquery("INSERT INTO ".$db_prefix."user_fields (field_id, field_name, field_cat, field_required, field_log, field_registration, field_order) VALUES ('', 'user_blacklist', '5', '0', '0', '0', '1'");
		$result = dbquery("ALTER TABLE ".$db_prefix."users ADD user_blacklist TEXT NOT NULL AFTER user_language");
		// Admin Theme
		$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('admin_theme', 'Venus')");
		// Bootstrap
		$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('bootstrap', '1')");
		// Set a new default theme to prevent issues during upgrade
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Septenary' WHERE settings_name='theme'");
		//User sig issue
		$result = dbquery("ALTER TABLE ".$db_prefix."users CHANGE user_sig user_sig VARCHAR(500) NOT NULL DEFAULT ''");
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
		$htc = "ErrorDocument 400 ".$settings['siteurl']."error.php?code=400\r\n";
		$htc .= "ErrorDocument 401 ".$settings['siteurl']."error.php?code=401\r\n";
		$htc .= "ErrorDocument 403 ".$settings['siteurl']."error.php?code=403\r\n";
		$htc .= "ErrorDocument 404 ".$settings['siteurl']."error.php?code=404\r\n";
		$htc .= "ErrorDocument 500 ".$settings['siteurl']."error.php?code=500\r\n";
		$temp = fopen(BASEDIR.".htaccess", "w");
		if (fwrite($temp, $htc)) {
			fclose($temp);
		}
		
		//Set the new version
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='7.03.00' WHERE settings_name='version'");
		echo $locale['502']."<br /><br />\n";
	}
} else {
	echo $locale['401']."<br /><br />\n";
}
echo "</form>\n</div>\n";
closetable();
require_once THEMES."templates/footer.php";
?>