<?php
// do a double control of version here
if (str_replace(".", "", $settings['version']) < "90001") { // 90001 for testing purposes
    include LOCALE.LOCALESET."setup.php";
	// Force the database to UTF-8 because we'll convert to it
	upgrade_database();
	/**
	 * 1. Upgrade all Infusions
	 */
	upgrade_articles();
	upgrade_weblinks();
	upgrade_downloads();
	upgrade_news();
	upgrade_forum();
	upgrade_gallery();
	upgrade_faq();
	upgrade_poll();
	//upgrade_eshop(); // doesn't do anything unless you have the new e-shop infusion for PHP-Fusion 9

	/**
	 * 2. Upgrade core
	 */
	upgrade_admin_icons();
	upgrade_private_message();
	upgrade_custom_page();
	upgrade_multilang();
	upgrade_user_table();
	upgrade_user_fields();
	upgrade_panels();
	install_seo();
	install_theme_engine();
	install_email_templates();
	upgrade_site_links();
	upgrade_core_settings();
}

/*
 * Infusions Upgrade Functions
 * 9 functions in total.
 * eshop is unused
 */
function upgrade_news() {
	global $locale,$settings;
	dbquery("ALTER TABLE ".DB_NEWS." ADD news_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER news_extended");
	// Add support of hierarchy to News
	dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER news_cat_id");
	// Add 9.00 news feature
	dbquery("ALTER TABLE ".DB_NEWS." ADD news_ialign VARCHAR(15) NOT NULL DEFAULT '' AFTER news_image_t2");
	// Add multilang support
	dbquery("ALTER TABLE ".DB_NEWS." ADD news_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_allow_ratings");
	dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_cat_image");
	// news settings
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_image_readmore', '1', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_image_frontpage', '0', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_thumb_ratio', '0', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_image_link', '1', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_photo_w', '1200', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_photo_h', '800', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_thumb_w', '600', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_thumb_h', '400', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_photo_max_w', '1200', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_photo_max_h', '800', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_photo_max_b', '500000', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_pagination', '15', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_extended_required', '0', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_allow_submission', '1', 'news')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('news_allow_submission_files', '1', 'news')");
	dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3205']."', 'news', '1')");
	// Remove old cats link and update to new path
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='news_cats.php'");
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='settings_news.php'");
	dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/news/news_admin.php' WHERE admin_link='news.php'");
}

function upgrade_admin_icons() {
	// Upgrade new icons
	$new_icon_array = array(
		"APWR" => "adminpass.png",
		"AD" => "administrator.png",
		"A" => "articles.png",
		"SB" => "banner.png",
		"BB" => "bbcodes.png",
		"B" => "blacklist.png",
		"BLOG" => "blog.png",
		"CP" => "c-pages.png",
		"DB" => "db_backup.png",
		"D" => "download.png",
		"MAIL" => "email.png",
		"ERRO" => "errors.png",
		"FQ" => "faq.png",
		"F" => "forums.png",
		"PH" => "gallery.png",
		"IM" => "images.png",
		"I" => "infusions.png",
		"LANG" => "language.png",
		"S1" => "settings.png",
		"M" => "members.png",
		"MI" => "migration.png",
		"S6" => "misc.png",
		"N" => "news.png",
		"P" => "panels.png",
		"PL" => "permalink.png",
		"PI" => "phpinfo.png",
		"PO" => "polls.png",
		"S7" => "pm.png",
		"S4" => "registration.png",
		"ROB" => "robots.png",
		"S12" => "security.png",
		"S" => "shout.png",
		"SL" => "sitelinks.png",
		"SM" => "smileys.png",
		"TS" => "theme.png",
		"S3" => "theme_settings.png",
		"S2" => "time.png",
		"U" => "upgrade.png",
		"UF" => "user_fields.png",
		"UG" => "user_groups.png",
		"UL" => "user_log.png",
		"S9" => "user_settings.png",
		"W" => "weblink.png",
	);
	foreach($new_icon_array as $admin_rights => $icon_file) {
		dbquery("UPDATE ".DB_ADMIN." SET admin_image='".$icon_file."' WHERE admin_rights='".$admin_rights."'");
	}
}

function upgrade_articles() {
	global $locale,$settings;
	dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER article_cat_name");
	dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER article_cat_id");
	// Option to use keywords in articles
	dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER article_article");
	// Moving access level from article categories to articles and create field for subcategories
	dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER article_datestamp");
	$result = dbquery("SELECT article_cat_id, article_cat_access FROM ".DB_ARTICLE_CATS);
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_ARTICLES." SET article_visibility='".$data['article_cat_access']."' WHERE article_cat='".$data['article_cat_id']."'");
		}
	}
	dbquery("ALTER TABLE ".DB_ARTICLE_CATS." DROP COLUMN article_cat_access");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('article_pagination', '15', 'article')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('article_extended_required', '0', 'article')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('article_allow_submission', '1', 'article')");
	dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3002']."', 'articles', '1')");
	// Remove old cats link and update new path for admin link
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='article_cats.php'");
	dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/articles/articles_admin.php' WHERE admin_link='articles.php'");
}

function upgrade_weblinks() {
global $locale,$settings;
	// Moving access level from weblinks categories to weblinks and create field for subcategories
	dbquery("ALTER TABLE ".DB_WEBLINKS." ADD weblink_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER weblink_datestamp");
	dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER weblink_cat_id");
	// Add multilocale support
	dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER weblink_cat_name");
	// Set weblink visibility
	$result = dbquery("SELECT weblink_cat_id, weblink_cat_access FROM ".DB_WEBLINK_CATS);
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_WEBLINKS." SET weblink_visibility='".$data['weblink_cat_access']."' WHERE weblink_cat='".$data['weblink_cat_id']."'");
		}
	}
	dbquery("ALTER TABLE ".DB_WEBLINK_CATS." DROP COLUMN weblink_cat_access");
	// Insert new weblink settings
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('links_pagination', '15', 'weblinks')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('links_extended_required', '1', 'weblinks')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('links_allow_submission', '1', 'weblinks')");
	dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3209']."', 'weblinks', '1')");
	// Remove old cats link and update new path for admin link
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='weblink_cats.php'");
	dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/weblinks/weblinks_admin.php' WHERE admin_link='weblinks.php'");
}

function upgrade_faq() {
    global $locale,$settings;
	dbquery("ALTER TABLE ".DB_FAQ_CATS." ADD faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER faq_cat_description");
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3203']."', 'weblinks', '1')");
	// Update new path for admin link
	dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/faq/faq_admin.php' WHERE admin_link='faq.php'");
}

function upgrade_forum() {
    global $locale,$settings;
	dbquery("ALTER TABLE ".DB_FORUM_RANKS." ADD rank_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER rank_apply");
    dbquery("ALTER TABLE ".DB_FORUM_ATTACHMENTS." CHANGE attach_ext attach_mime VARCHAR(20) NOT NULL DEFAULT ''");

	// Additional column insertion
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_branch MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER forum_cat");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_type TINYINT(1) NOT NULL DEFAULT '1' AFTER forum_name");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_answer_treshold TINYINT(3) NOT NULL DEFAULT '15' AFTER forum_type");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_lock TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_answer_treshold");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_rules TEXT NOT NULL AFTER forum_description");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER forum_merge");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_allow_poll TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_reply");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_image VARCHAR(100) NOT NULL DEFAULT '' AFTER forum_vote");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_post_ratings TINYINT(4) NOT NULL DEFAULT '-101' AFTER forum_image");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_users TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_post_ratings");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_allow_attach TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_users");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_quick_edit TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_attach_download");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER forum_quick_edit");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_meta TEXT NOT NULL AFTER forum_language");
	dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_alias TEXT NOT NULL AFTER forum_meta");

	// Rename forum_moderators to forum_mods
	dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_moderators forum_mods TEXT NOT NULL");

	// Change params based on new user_level
	dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_reply forum_reply TINYINT(4) DEFAULT '-101'");
	dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_vote forum_vote TINYINT(4) DEFAULT '-101'");
	dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_poll forum_poll TINYINT(4) DEFAULT '-101'");
	dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_attach forum_attach TINYINT(4) DEFAULT '-101'");
	dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_attach_download forum_attach_download TINYINT(4) DEFAULT '-101'");

	/*
	 * After upgrade all forums are categories by default
	 * Change old forums already inside a group to be a forum containing threads
	 * This makes all existing threads accessible both in forums and in panels after upgrade
	 */
	dbquery("UPDATE ".DB_FORUMS." SET forum_type = 2 WHERE forum_cat != 0");

	// Clear old settings if they are there regardless of current state
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_ips'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_attachmax'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_attachmax_count'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_attachtypes'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='thread_notify'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_ranks'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_edit_lock'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_edit_timelimit'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='popular_threads_timeframe'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_last_posts_reply'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_last_post_avatar'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_editpost_to_lastpost'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='threads_per_page'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='posts_per_page'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='numofthreads'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_rank_style'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ips', '-103', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax', '1000000', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax_count', '5', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachtypes', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thread_notify', '1', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ranks', '1', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_lock', '0', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_timelimit', '0', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('popular_threads_timeframe', '604800', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_posts_reply', '1', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_post_avatar', '1', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_editpost_to_lastpost', '1', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('threads_per_page', '20', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('posts_per_page', '20', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('numofthreads', '16', 'forum')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_rank_style', '0', 'forum')");
	// New access rights need a larger table for forum ranks
	dbquery("ALTER TABLE ".DB_FORUM_RANKS." CHANGE rank_apply rank_apply TINYINT(4) NOT NULL DEFAULT '-101'");
	// Modify All Rank Levels
	$result = dbquery("SELECT rank_id, rank_apply FROM ".DB_FORUM_RANKS."");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_FORUM_RANKS." SET rank_apply ='-".$data['rank_apply']."' WHERE rank_id='".$data['rank_id']."' ");
		}
	}
	// Forum tables renaming
	dbquery("RENAME TABLE `".DB_PREFIX."posts` TO `".DB_PREFIX."forum_posts`");
	dbquery("RENAME TABLE `".DB_PREFIX."threads` TO `".DB_PREFIX."forum_threads`");
	dbquery("RENAME TABLE `".DB_PREFIX."thread_notify` TO `".DB_PREFIX."forum_thread_notify`");
	dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3204']."', 'forum', '1')");
	// Remove settings_forum from the Administration
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='settings_forum.php'");
	// Remove forum_ranks from the Administration
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='forum_ranks.php'");
	// Remove old cats link and update new path for admin link
	dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/forum/admin/forums.php' WHERE admin_link='forums.php'");
}

function upgrade_gallery() {
	global $locale,$settings;
	// Option to use keywords in photos
	dbquery("ALTER TABLE ".DB_PHOTOS." ADD photo_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER photo_description");
	dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_language varchar(50) NOT NULL default '".$settings['locale']."' AFTER album_datestamp");
	dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER album_description");
	dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_image VARCHAR(200) NOT NULL DEFAULT '' AFTER album_keywords");
	dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_thumb1 VARCHAR(200) NOT NULL DEFAULT '' AFTER album_image");
	dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_thumb2 VARCHAR(200) NOT NULL DEFAULT '' AFTER album_thumb1");
	// Clear old settings if they are there regardless of current state
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='thumb_w'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='thumb_h'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_w'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_h'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_max_w'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_max_h'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_max_b'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='thumbs_per_row'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='admin_thumbs_per_row'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_image'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_text'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_text_color1'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_text_color2'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_text_color3'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_save'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumb_w', '200', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumb_h', '200', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_w', '800', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_h', '600', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_w', '2400', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_h', '1800', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_b', '2000000', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumbs_per_row', '4', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('admin_thumbs_per_row', '6', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark', '1', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_image', 'infusions/gallery/photos/watermark.png', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text', '0', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color1', 'FF6600', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color2', 'FFFF00', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color3', 'FFFFFF', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_save', '0', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('gallery_pagination', '24', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('gallery_extended_required', '1', 'gallery')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('gallery_allow_submission', '1', 'gallery')");
	dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3206']."', 'gallery', '1')");
	// Remove old cats link and update new path for admin link
	dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/gallery/gallery_admin.php' WHERE admin_link='photoalbums.php'");
}

function upgrade_downloads() {
	 global $locale,$settings;
	// Option to use keywords in downloads
	dbquery("ALTER TABLE ".DB_DOWNLOADS." ADD download_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER download_description");
	dbquery("ALTER TABLE ".DB_DOWNLOADS." ADD download_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER download_datestamp");
	// add multilanguage support
	dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER download_cat_access");
	dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." DROP COLUMN download_cat_access");
	dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER download_cat_id");
	// Clear old settings if they are there regardless of current state
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_max_b'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_types'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_b'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_w'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_h'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screenshot'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_thumb_max_w'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_thumb_max_h'");
	dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_pagination'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_max_b', '512000', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_types', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_b', '150000', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_w', '1024', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_h', '768', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screenshot', '1', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_thumb_max_w', '100', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_thumb_max_h', '100', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_pagination', '15', 'downloads')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screenshot_required', '1', 'download')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_extended_required', '1', 'download')");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_allow_submission', '1', 'download')");
	// Moving access level from downloads categories to downloads and create field for subcategories
	$result = dbquery("SELECT download_cat_id, download_cat_access FROM ".DB_DOWNLOAD_CATS);
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_DOWNLOADS." SET download_visibility='".$data['download_cat_access']."' WHERE download_cat='".$data['download_cat_id']."'");
		}
	}
	dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3202']."', 'downloads', '1')");
	// Remove old cats link and update new path for admin link
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='download_cats.php'");
	dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/downloads/downloads_admin.php' WHERE admin_link='downloads.php'");
}

function upgrade_poll() {
    global $locale,$settings;
	dbquery("ALTER TABLE ".DB_POLLS." ADD poll_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER poll_ended");
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3207']."', 'member_poll_panel', '1.00')");
	// update new path for admin link
	dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/member_poll_panel/member_poll_panel_admin.php' WHERE admin_link='polls.php'");
}

function upgrade_eshop() {
// Insert shop settings if the old infusion exist
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ipn', '0', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cats', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cat_disp', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_nopp', '6', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_noppf', '9', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_target', '_self', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_folderlink', '0', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_selection', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cookies', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_bclines', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_icons', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_statustext', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_closesamelevel', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_inorder', '0', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_shopmode', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_returnpage', 'ordercompleted.php', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ppmail', '', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ipr', '3', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ratios', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_h', '130', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_w', '100', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_h2', '180', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_w2', '250', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_catimg_w', '100', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_catimg_h', '100', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_w', '6400', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_h', '6400', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_b', '9999999', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_tw', '150', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_th', '100', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_t2w', '250', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_t2h', '250', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_buynow_color', 'blue', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_checkout_color', 'green', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cart_color', 'red', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_addtocart_color', 'magenta', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_info_color', 'orange', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_return_color', 'yellow', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_pretext', '0', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_pretext_w', '190px', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_listprice', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_currency', 'USD', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_shareing', '1', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_weightscale', 'KG', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_vat', '25', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_vat_default', '0', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_terms', '<h2> Ordering </h2><br />\r\nWhilst all efforts are made to ensure accuracy of description, specifications and pricing there may <br />be occasions where errors arise. Should such a situation occur [Company name] cannot accept your order. <br /> In the event of a mistake you will be contacted with a full explanation and a corrected offer. <br />The information displayed is considered as an invitation to treat not as a confirmed offer for sale. \r\nThe contract is confirmed upon supply of goods.\r\n<br /><br /><br />\r\n<h2>Delivery and Returns</h2><br />\r\n[Company name] returns policy has been set up to keep costs down and to make the process as easy for you as possible. You must contact us and be in receipt of a returns authorisation (RA) number before sending any item back. Any product without a RA number will not be refunded. <br /><br /><br />\r\n<h2> Exchange </h2><br />\r\n
					If when you receive your product(s), you are not completely satisfied you may return the items to us, within seven days of exchange or refund. Returns will take approximately 5 working days for the process once the goods have arrived. Items must be in original packaging, in all original boxes, packaging materials, manuals blank warranty cards and all accessories and documents provided by the manufacturer.<br /><br /><br />\r\n\r\nIf our labels are removed from the product â€“ the warranty becomes void.<br /><br /><br />\r\n\r\nWe strongly recommend that you fully insure your package that you are returning. We suggest the use of a carrier that can provide you with a proof of delivery. [Company name] will not be held responsible for items lost or damaged in transit.<br /><br /><br />\r\n\r\nAll shipping back to [Company name] is paid for by the customer. We are unable to refund you postal fees.<br /><br /><br />\r\n\r\nAny product returned found not to be defective can be refunded within the time stated above and will be subject to a 15% restocking fee to cover our administration costs. Goods found to be tampered with by the customer will not be replaced but returned at the customers expense. <br /><br /><br />\r\n\r\n If you are returning items for exchange please be aware that a second charge may apply. <br /><br /><br />\r\n\r\n<h2>Non-Returnable </h2><br />\r\n For reasons of hygiene and public health, refunds/exchanges are not available for used ......... (this does not apply to faulty goods â€“ faulty products will be exchanged like for like)<br /><br /><br />\r\n\r\nDiscounted or our end of line products can only be returned for repair no refunds of replacements will be made.<br /><br /><br />\r\n\r\n<h2> Incorrect/Damaged Goods </h2><br />\r\n\r\n We try very hard to ensure that you receive your order in pristine condition. If you do not receive your products ordered. Please contract us. In the unlikely event that the product arrives damaged or faulty, please contact [Company name] immediately, this will be given special priority and you can expect to receive the correct item within 72 hours. Any incorrect items received all delivery charges will be refunded back onto you credit/debit card.<br /><br /><br />\r\n\r\n<h2>Delivery service</h2><br />\r\nWe try to make the delivery process as simple as possible and our able to send your order either you home or to your place of work.<br /><br /><br />\r\n\r\nDelivery times are calculated in working days Monday to Friday. If you order after 4 pm the next working day will be considered the first working day for delivery. In case of bank holidays and over the Christmas period, please allow an extra two working days.<br /><br /><br />\r\n\r\nWe aim to deliver within 3 working days but sometimes due to high order volume certain in sales periods please allow 4 days before contacting us. We will attempt to email you if we become aware of an unexpected delay. <br /><br /><br />\r\n\r\nAll small orders are sent out via royal mail 1st packets post service, if your order is over Â£15.00 it will be sent out via royal mails recorded packet service, which will need a signature, if you are not present a card will be left to advise you to pick up your goods from the local sorting office.<br /><br /><br />\r\n\r\nEach item will be attempted to be delivered twice. Failed deliveries after this can be delivered at an extra cost to you or you can collect the package from your local post office collection point.<br /><br /><br />\r\n\r\n<h2>Export restrictions</h2><br /><br /><br />\r\n\r\nAt present [Company name] only sends goods within the [Country]. We plan to add exports to our services in the future. If however you have a special request please contact us your requirements.<br /><br /><br />\r\n\r\n<h2> Privacy Notice </h2><br />\r\n\r\nThis policy covers all users who register to use the website. It is not necessary to purchase anything in order to gain access to the searching facilities of the site.<br /><br /><br />\r\n\r\n<h2> Security </h2><br />\r\nWe have taken the appropriate measures to ensure that your personal information is not unlawfully processed. [Company name] uses industry standard practices to safeguard the confidentiality of your personal identifiable information, including â€˜firewallsâ€™ and secure socket layers. <br /><br /><br />\r\n\r\nDuring the payment process, we ask for personal information that both identifies you and enables us to communicate with you. <br /><br /><br />\r\n\r\nWe will use the information you provide only for the following purposes.<br /><br /><br />\r\n\r\n* To send you newsletters and details of offers and promotions in which we believe you will be interested. <br />\r\n* To improve the content design and layout of the website. <br />\r\n* To understand the interest and buying behavior of our registered users<br />\r\n* To perform other such general marketing and promotional focused on our products and activities. <br />\r\n\r\n<h2> Conditions Of Use </h2><br />\r\n[Company name] and its affiliates provide their services to you subject to the following conditions. If you visit our shop at [Company name] you accept these conditions. Please read them carefully, [Company name] controls and operates this site from its offices within the [Country]. The laws of [Country] relating to including the use of, this site and materials contained. <br /><br /><br />\r\n\r\nIf you choose to access from another country you do so on your own initiave and are responsible for compliance with applicable local lands. <br /><br /><br />\r\n\r\n<h2> Copyrights </h2><br />\r\nAll content includes on the site such as text, graphics logos button icons images audio clips digital downloads and software are all owned by [Company name] and are protected by international copyright laws. <br /><br /><br />\r\n\r\n<h2> License and Site Access </h2><br />\r\n[Company name] grants you a limited license to access and make personal use of this site. This license doses not include any resaleâ€™s of commercial use of this site or its contents any collection and use of any products any collection and use of any product listings descriptions or prices any derivative use of this site or its contents, any downloading or copying of account information. For the benefit of another merchant or any use of data mining, robots or similar data gathering and extraction tools.<br /><br /><br />\r\n\r\nThis site may not be reproduced duplicated copied sold â€“ resold or otherwise exploited for any commercial exploited without written consent of [Company name].<br /><br /><br />\r\n\r\n<h2> Product Descriptions </h2><br />\r\n[Company name] and its affiliates attempt to be as accurate as possible however we do not warrant that product descriptions or other content is accurate complete reliable, or error free.<br /><br /><br />\r\nFrom time to time there may be information on [Company name] that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing and availability.<br /><br /><br />\r\nWe reserve the right to correct ant errors inaccuracies or omissions and to change or update information at any time without prior notice. (Including after you have submitted your order) We apologies for any inconvenience this may cause you. <br /><br /><br />\r\n\r\n<h2> Prices </h2><br />\r\nPrices and availability of items are subject to change without notice the prices advertised on this site are for orders placed and include VAT and delivery.<br /><br /><br />\r\n<br /><br /><br />\r\nPlease review our other policies posted on this site. These policies also govern your visit to [Company name]', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_itembox_w', '200px', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_itembox_h', '300px', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cipr', '3', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_newtime', '604800', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_freeshipsum', '0', 'eshop'");
	dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_coupons', '0', 'eshop'");
	// Update tables from previous shop installs
	dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD comments char(1) NOT NULL default '' AFTER campaign");
	dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD ratings char(1) NOT NULL default '' AFTER comments");
	dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD linebreaks char(1) NOT NULL default '' AFTER ratings");
	dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD keywords varchar(255) NOT NULL default '' AFTER linebreaks");
	dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD product_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['locale']."' AFTER keywords");
	dbquery("ALTER TABLE ".DB_PREFIX."eshop_cats ADD cat_order MEDIUMINT(8) UNSIGNED NOT NULL AFTER status");
	dbquery("ALTER TABLE ".DB_PREFIX."eshop_cats ADD cat_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['locale']."' AFTER cat_order");
	dbquery("RENAME TABLE `".DB_PREFIX."eshop_cupons` TO `".DB_PREFIX."eshop_coupons`");
}

/**
 * Core Upgrade Functions
 * 12 functions
 */
function upgrade_database() {
	dbquery("SET NAMES 'utf8'");
	$result = dbquery("SHOW TABLES");
	while ($row = dbarray($result)) {
		foreach ($row as $key => $table) {
			dbquery("ALTER TABLE ".$table." CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci");
			$result2 = dbquery("SHOW COLUMNS FROM ".$table);
			// We must change all data like find/replace in columns of broken chars, this may differ for each locales.
			// Please help to complete this list if you know what´s missing with your locale set
			while ($column = dbarray($result2)) {
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¥','Å')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¤','Ä')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¶','Ö')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'ð', 'ğ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'ý', 'ı')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'þ', 'ş')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ð', 'Ğ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ý', 'İ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Þ', 'Ş')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã‰','É')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'â€œ','\"')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'â€','\"')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã‡','Ç')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ãƒ','Ã')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¥','Å')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¤','Ä')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¶','Ö')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã ','À')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ãº','ú')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'â€¢','-')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã˜','Ø')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ãµ','õ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã­','í')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¢','â')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã£','ã')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ãª','ê')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¡','á')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã©','é')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã³','ó')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'â€“','–')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã§','ç')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Âª','ª')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Âº','º')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã ','à')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ccedil;','ç')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&atilde;','ã')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&aacute;','á')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&acirc;','â')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&eacute;','é')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&iacute;','í')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&otilde;','õ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&uacute;','ú')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ccedil;','ç')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Aacute;','Á')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Acirc;','Â')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Eacute;','É')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Iacute;','Í')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Otilde;','Õ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Uacute;','Ú')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Ccedil;','Ç')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Atilde;','Ã')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Agrave;','À')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Ecirc;','Ê')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Oacute;','Ó')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Ocirc;','Ô')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Uuml;','Ü')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&atilde;','ã')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&agrave;','à')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ecirc;','ê')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&oacute;','ó')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ocirc;','ô')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&uuml;','ü')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&amp;','&')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&gt;','>')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lt;','<')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&circ;','ˆ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&tilde;','˜')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&uml;','¨')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cute;','´')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cedil;','¸')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&quot;','\"')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ldquo;','“')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&rdquo;','”')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lsquo;','‘')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&rsquo;','’')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lsaquo;','‹')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&rsaquo;','›')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&laquo;','«')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&raquo;','»')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ordm;','º')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ordf;','ª')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ndash;','–')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&mdash;','—')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&macr;','¯')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&hellip;','…')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&brvbar;','¦')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&bull;','•')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&para;','¶')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sect;','§')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sup1;','¹')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sup2;','²')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sup3;','³')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&frac12;','½')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&frac14;','¼')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&frac34;','¾')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&#8539;','⅛')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&#8540;','⅜')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&#8541;','⅝')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&#8542;','⅞')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&gt;','>')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lt;','<')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&plusmn;','±')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&minus;','−')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&times;','×')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&divide;','÷')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lowast;','∗')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&frasl;','⁄')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&permil;','‰')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&int;','∫')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sum;','∑')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&prod;','∏')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&radic;','√')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&infin;','∞')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&asymp;','≈')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cong;','≅')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&prop;','∝')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&equiv;','≡')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ne;','≠')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&le;','≤')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ge;','≥')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&there4;','∴')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sdot;','⋅')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&middot;','·')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&part;','∂')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&image;','ℑ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&real;','ℜ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&prime;','′')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Prime;','″')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&deg;','°')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ang;','∠')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&perp;','⊥')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&nabla;','∇')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&oplus;','⊕')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&otimes;','⊗')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&alefsym;','ℵ')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&oslash;','ø')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Oslash;','Ø')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&isin;','∈')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&notin;','∉')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cap;','∩')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cup;','∪')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sub;','⊂')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sup;','⊃')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sube;','⊆')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&supe;','⊇')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&exist;','∃')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&forall;','∀')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&empty;','∅')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&not;','¬')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&and;','∧')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&or;','∨')");
				dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&crarr;','↵')");
			}
		}
	}
}

function upgrade_private_message() {
	$schema = array_flip(fieldgenerator(DB_PREFIX."messages"));
	if (!isset($schema['message_user'])) {
		dbquery("ALTER TABLE ".DB_PREFIX."messages ADD message_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER message_from");
	}
	// Alter user table to support a more global wide pm support.
	// Each user logs in once. We do not need to worry whether user have a DB_MESSAGE_OPTIONS config or not.
	// Set 0 for for iMEMBER to use core settings. And you can offer premium user upgrade solution easily by altering the table.
	// drop if exist DB_MESSAGE_OPTIONS. This table is a resource hog.
	$user_schema = array_flip(fieldgenerator(DB_PREFIX."users"));
	if (!isset($user_schema['user_inbox'])) dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_inbox SMALLINT(6) unsigned not null default '0' AFTER user_status");
	if (!isset($user_schema['user_outbox'])) dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_outbox SMALLINT(6) unsigned not null default '0' AFTER user_inbox");
	if (!isset($user_schema['user_archive'])) dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_archive SMALLINT(6) unsigned not null default '0' AFTER user_outbox");
	if (!isset($user_schema['user_pm_email_notify'])) dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_pm_email_notify TINYINT(1) not null default '0' AFTER user_archive");
	if (!isset($user_schema['user_pm_save_sent'])) dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_pm_save_sent TINYINT(1) not null default '0' AFTER user_pm_email_notify");
	// drop if exists
	dbquery("DROP TABLE IF EXISTS ".DB_PREFIX."messages_options");
    $result = dbquery("SELECT * FROM ".DB_MESSAGES);
    if (dbrows($result)>0) {
        // perform data tally from 7.02.07
        while ($data = dbarray($result)) {
            $data['message_user'] = $data['message_to'];
            dbquery_insert(DB_MESSAGES, $data, "update");
        }
    }
}

function install_theme_engine() {
	global $locale,$settings;
	// Install themes db.
	dbquery("CREATE TABLE ".DB_PREFIX."theme (
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
	// Install theme widget db
	dbquery("CREATE TABLE ".DB_PREFIX."settings_theme (
									settings_name VARCHAR(200) NOT NULL DEFAULT '',
									settings_value TEXT NOT NULL,
									settings_theme VARCHAR(200) NOT NULL DEFAULT '',
									PRIMARY KEY (settings_name)
						) ENGINE=MYISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	// Insert theme global settings
	dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S3', 'rocket.gif', '".$locale['setup_3058']."', 'settings_theme.php', '4')");
	// Insert theme template settings
	dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('TS', 'rocket.gif', '".$locale['setup_3056']."', 'theme.php', '3')");
}

function upgrade_user_fields() {
	dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_parent MEDIUMINT(8) NOT NULL AFTER field_cat_name");
	dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_db VARCHAR(200) NOT NULL AFTER field_parent");
	dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_index VARCHAR(200) NOT NULL AFTER field_cat_db");
	dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_class VARCHAR(50) NOT NULL AFTER field_cat_index");
	dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_title VARCHAR(50) NOT NULL AFTER field_id");
	dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_type VARCHAR(25) NOT NULL AFTER field_cat");
	dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_default TEXT NOT NULL AFTER field_type");
	dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_options TEXT NOT NULL AFTER field_default");
	dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_error VARCHAR(50) NOT NULL AFTER field_options");
	dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_config TEXT NOT NULL AFTER field_order");
}

function upgrade_custom_page() {
	global $settings;
	// Option to use keywords in custom_pages
	dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER page_content");
	dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0' AFTER page_id");
	dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_language VARCHAR(255) NOT NULL DEFAULT '".$settings['locale']."' AFTER page_allow_ratings");
}

function upgrade_panels() {
	global $settings;
	dbquery("ALTER TABLE ".DB_PANELS." ADD panel_languages VARCHAR(200) NOT NULL DEFAULT '.".$settings['locale']."' AFTER panel_restriction");
    dbquery("UPDATE ".DB_PANELS." SET panel_restriction='2' WHERE panel_side='2'");
        dbquery("UPDATE ".DB_PANELS." SET panel_language='.".$settings['locale']."'");
}

function upgrade_site_links() {
	global $settings;
	dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER link_order");
	// Site links new admin
	dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_cat MEDIUMINT(9) NOT NULL DEFAULT '0' AFTER link_id");
	dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_icon VARCHAR(100) NOT NULL DEFAULT '' AFTER link_url");
	$result = dbquery("SELECT link_id, link_visibility FROM ".DB_SITE_LINKS."");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			if ($data['link_visibility']) {
				dbquery("UPDATE ".DB_SITE_LINKS." SET user_visibility ='-".$data['link_visibility']."' WHERE link_id='".$data['link_id']."' ");
			}
		}
	}
}

function upgrade_user_table() {
	global $settings;
	// New access rights need a larger table for users
	dbquery("ALTER TABLE ".DB_USERS." CHANGE user_level user_level TINYINT(4) NOT NULL DEFAULT '-101'");
	// Modify All Users Level > 0
	$result = dbquery("SELECT user_id, user_level FROM ".DB_USERS."");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			if ($data['user_level']) { // will omit 0
				dbquery("UPDATE ".DB_USERS." SET user_level ='-".$data['user_level']."' WHERE user_id='".$data['user_id']."' ");
			}
		}
	}
	// Remove dropped rights, these settings have been moved to tabs and follow the Infusions rights
	$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS."");
	// We still lack some of the old infusions rights that have been merged here
	while ($data = dbarray($result)) {
		// might still need this : $new_rights = str_replace(".S8", "", $new_rights);
		$new_rights = str_replace(".S13", "", $data['user_rights']);
		$new_rights = str_replace(".S5", "", $new_rights);
		$new_rights = str_replace(".S11", "", $new_rights);
		$new_rights = str_replace(".SU", "", $new_rights);
		dbquery("UPDATE ".DB_USERS." SET user_rights='".$new_rights."' WHERE user_id='".$data['user_id']."'");
	}
	// Change existing link_visibility to new access levels
	$result = dbquery("ALTER TABLE ".DB_SITE_LINKS." CHANGE link_visibility link_visibility CHAR(4) NOT NULL DEFAULT ''");
	// add user language fields
	$result = dbquery("ALTER TABLE ".DB_USERS." ADD user_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."'");
	// Delete user_offset field an replace it with user_timezone
	$result = dbquery("ALTER TABLE ".DB_USERS." ADD user_timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/London' AFTER user_offset");
	$result = dbquery("ALTER TABLE ".DB_USERS." DROP COLUMN user_offset");
}

function upgrade_multilang() {
	global $locale,$settings;
	// Create guest visitors language session tables
	dbquery("CREATE TABLE ".DB_PREFIX."language_sessions (
	user_ip VARCHAR(20) NOT NULL DEFAULT '0.0.0.0',
	user_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."',
	user_datestamp INT(10) NOT NULL default '0'
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci;");
	// Enabled languages array
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('enabled_languages', '".$settings['locale']."')");
	// Language settings admin section
	$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('LANG', 'languages.gif', '".$locale['129c']."', 'settings_languages.php', '4')");
	// Add Lang rights to Super Administrator
	$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".LANG' WHERE user_id='".$data['user_id']."'");
		}
	}
	// Create multilang tables
	dbquery("CREATE TABLE ".DB_PREFIX."mlt_tables (
			mlt_rights CHAR(4) NOT NULL DEFAULT '',
			mlt_title VARCHAR(50) NOT NULL DEFAULT '',
			mlt_status VARCHAR(50) NOT NULL DEFAULT '',
			PRIMARY KEY (mlt_rights)) 
			ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
// Add Multilang table rights and status
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('AR', '".$locale['MLT001']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('CP', '".$locale['MLT002']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('BL', '".$locale['MLT014']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('DL', '".$locale['MLT003']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('ES', '".$locale['MLT015']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FQ', '".$locale['MLT004']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FO', '".$locale['MLT005']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FR', '".$locale['MLT013']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('NS', '".$locale['MLT006']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PG', '".$locale['MLT007']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PO', '".$locale['MLT008']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('ET', '".$locale['MLT009']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('WL', '".$locale['MLT010']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('SL', '".$locale['MLT011']."', '1')");
	dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PN', '".$locale['MLT012']."', '1')");
}

function install_email_templates() {
	 global $locale,$settings;
	// Email templates admin section
	$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MAIL', 'email.gif', '".$locale['T001']."', 'email.php', '3')");
	if ($result) {
		$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".MAIL' WHERE user_id='".$data['user_id']."'");
		}
	}
	dbquery("CREATE TABLE ".DB_PREFIX."email_templates (
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
	dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('PM', 'html', '0', '".$locale['T101']."', '".$locale['T102']."', '".$locale['T103']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
	dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('POST', 'html', '0', '".$locale['T201']."', '".$locale['T202']."', '".$locale['T203']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
	dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('CONTACT', 'html', '0', '".$locale['T301']."', '".$locale['T302']."', '".$locale['T303']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
}

function install_seo() {
    global $locale;
	// SEO tables.
	dbquery("CREATE TABLE ".DB_PREFIX."permalinks_alias (
									alias_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
									alias_url VARCHAR(200) NOT NULL DEFAULT '',
									alias_php_url VARCHAR(200) NOT NULL DEFAULT '',
									alias_type VARCHAR(10) NOT NULL DEFAULT '',
									alias_item_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
									PRIMARY KEY (alias_id),
									KEY alias_id (alias_id)
									) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	dbquery("CREATE TABLE ".DB_PREFIX."permalinks_method (
									pattern_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
									pattern_type INT(5) UNSIGNED NOT NULL,
									pattern_source VARCHAR(200) NOT NULL DEFAULT '',
									pattern_target VARCHAR(200) NOT NULL DEFAULT '',
									pattern_cat VARCHAR(10) NOT NULL DEFAULT '',
									PRIMARY KEY (pattern_id)
									) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	dbquery("CREATE TABLE ".DB_PREFIX."permalinks_rewrites (
									rewrite_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
									rewrite_name VARCHAR(50) NOT NULL DEFAULT '',
									PRIMARY KEY (rewrite_id)
									) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	// Create admin page for permalinks
	$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PL', 'permalink.gif', '".$locale['SEO']."', 'permalink.php', '3')");
	// Upgrade admin rights for permalink admin
	$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".PL' WHERE user_id='".$data['user_id']."'");
		}
	}
	// Site settings for SEO / SEF
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('site_seo', '0')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('normalize_seo', '0')");
	$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('debug_seo', '0')");
}

function upgrade_core_settings() {
	// Login methods
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('login_method', '0')");
	// Mime check for upload files
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('mime_check', '0')");
	//Remove settings_ipp from the Administration
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='settings_ipp.php'");
	//Remove submissions from the Administration
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='submissions.php'");
	// Site settings panel exclusions for the new positons
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('exclude_aupper', '')");
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('exclude_blower', '')");
	// Admin Theme settings
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('admin_theme', 'Venus')");
	// Bootstrap settings
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('bootstrap', '1')");
	// Entypo settings
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('entypo', '1')");
	// Font-Awesome settings
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('fontawesome', '1')");
	// Set a new default theme to prevent issues during upgrade
	dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Septenary' WHERE settings_name='theme'");
	// Set a new default to PHP Execution
	dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('allow_php_exe', '0')");
	// Update server time offset´s to work with new function
	dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Europe/London' WHERE settings_name='timeoffset'");
	dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Europe/London' WHERE settings_name='serveroffset'");
	// Update opening page to home for stability
	dbquery("UPDATE ".DB_SETTINGS." SET settings_value='infusions/news/news.php' WHERE settings_name='opening_page'");
	// Remove user field cats setting
	dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='user_field_cats.php'");
}
