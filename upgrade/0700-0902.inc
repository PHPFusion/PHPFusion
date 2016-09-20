<?php

/**
 * Package for All version of PHP-Fusion Below 9.02
 * Release Candidate 5
 */

/**
 * List all infusions table configurations at 0902
 * @param $localeset
 */
function get_updated_tables($localeset) {
    $table_package['articles'] = [
        'article_id' => [
            'type' => 'MEDIUMINT',
            'length' => 8,
            'auto_increment' => TRUE,
            'key' => 1,
        ],
        'article_cat' => [
            'type' => 'MEDIUMINT',
            'length' => 8,
            'unsigned' => TRUE,
            'key' => 2
        ],
        'article_subject' => [
            'type' => 'VARCHAR',
            'length' => 200
        ],
        'article_snippet' => [
            'type' => 'TEXT',
        ],
        'article_article' => [
            'type' => 'TEXT',
        ],
        'article_keywords' => [
            'type' => 'VARCHAR',
            'length' => 250
        ],
        'article_draft' => [
            'type' => 'TINYINT',
            'length' => 1
        ],
        'article_breaks' => [
            'type' => 'CHAR',
            'length' => 1
        ],
        'article_name' => [
            'type' => 'MEDIUMINT',
            'length' => 8,
            'unsigned' => TRUE,
            'default' => '0'
        ],
        'article_datestamp' => [
            'type' => 'INT',
            'length' => 10,
            'unsigned' => TRUE,
            'default' => '0',
            'key' => 2
        ],
        'article_visibility' => [
            'type' => 'TINYINT',
            'length' => 4,
            'default' => '0'
        ],
        'article_language' => [
            'type' => 'VARCHAR',
            'length' => 50,
            'default' => '0'
        ],
        'article_reads' => [
            'type' => 'MEDIUMINT',
            'length' => 8,
            'unsigned' => TRUE,
            'default' => '0',
            'key' => 2
        ],
        'article_allow_comments' => [
            'type' => 'TINYINT',
            'length' => 1,
            'unsigned' => TRUE,
            'default' => 1
        ],
        'article_allow_ratings' => [
            'type' => 'TINYINT',
            'length' => 1,
            'unsigned' => TRUE,
            'default' => 1
        ]
    ];
    $table_package['article_cats'] = [
        'article_cat_id' => [
            'type' => 'MEDIUMINT',
            'length' => 8,
            'auto_increment' => TRUE,
            'key' => 1,
        ],
        'article_cat_parent' => [
            'type' => 'MEDIUMINT',
            'length' => 8,
            'unsigned' => TRUE,
            'default' => '0'
        ],
        'article_cat_name' => [
            'type' => 'VARCHAR',
            'length' => 100
        ],
        'article_cat_description' => [
            'type' => 'TEXT'
        ],
        'article_cat_sorting' => [
            'type' => 'VARCHAR',
            'length' => 50,
            'default' => 'article_subject ASC'
        ],
        'article_cat_language' => [
            'type' => 'VARCHAR',
            'length' => 50,
            'default' => $localeset
        ]
    ];
}

/**
 * @param $localeset
 */
function get_updated_queries($localeset) {
    // push upgrades to max in infusions.
    // Force the database to UTF-8 because we'll convert to it
    upgrade_database();
    upgrade_user_table();
    upgrade_private_message();
    upgrade_multilang();

    upgrade_user_fields();
    upgrade_panels();
    install_seo();
    install_theme_engine();
    install_email_templates();
    upgrade_site_links();
    upgrade_core_settings();
    upgrade_admin_icons();


}

function upgrade_news() {
    global $locale, $settings;

    // Add support for keywords in news items
    dbquery("ALTER TABLE ".DB_NEWS." ADD news_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER news_extended");

    // Add support of hierarchy to News
    dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER news_cat_id");

    // Add multilang support
    dbquery("ALTER TABLE ".DB_NEWS." ADD news_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_allow_ratings");
    dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_cat_image");


    // Make sure access table are support for new levels
    dbquery("ALTER TABLE ".DB_NEWS." CHANGE news_visibility news_visibility CHAR(4) NOT NULL DEFAULT '-101'");

    // Update new access levels for news access
    $result = dbquery("SELECT news_id, news_visibility FROM ".DB_NEWS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['news_visibility']) {
                dbquery("UPDATE ".DB_NEWS." SET news_visibility ='-".$data['news_visibility']."' WHERE news_id='".$data['news_id']."' ");
            }
        }
    }

    // Insert new and old settings tables to Infusions table
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

    // News Image Gallery Upgrade
    $inf_newtable = array();
    $inf_altertable = array();
    require_once INFUSIONS."news/infusion.php";
    dbquery("CREATE TABLE ".$inf_newtable[2]);
    // Port Photos into New Tables
    $query = "SELECT news_id, news_image, news_image_t1, news_image_t2, news_name 'news_image_user', news_datestamp 'news_image_datestamp' FROM ".DB_NEWS;
    $result = dbquery($query);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            $data += $this->default_image_data;
            $data['news_image_align'] = '';
            $data['news_full_default'] = 1;
            $data['news_front_default'] = 1;
            dbquery_insert(DB_NEWS_IMAGES, $data, 'save');
        }
    }
    // Drop existing columns
    dbquery("ALTER TABLE ".$inf_altertable[4]);
    dbquery("ALTER TABLE ".$inf_altertable[5]);
    dbquery("ALTER TABLE ".$inf_altertable[6]);
    dbquery("ALTER TABLE ".$inf_altertable[7]);

    //Add to infusions
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3205']."', 'news', '1.20')");

    // Remove old cats link and update to new path
    dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='news_cats.php'");
    dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='settings_news.php'");
    dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/news/news_admin.php' WHERE admin_link='news.php'");
}

function upgrade_articles() {
    global $locale, $settings;

    //dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER article_cat_name");
    //dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER article_cat_id");
    // Option to use keywords in articles
    dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER article_article");

    // Moving access level from article categories to articles and create field for subcategories
    dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER article_datestamp");

    // Add Multilingual support per article
    dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER article_visibility");

    $result = dbquery("SELECT article_cat_id, article_cat_access FROM ".DB_ARTICLE_CATS);
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_ARTICLES." SET article_visibility='-".$data['article_cat_access']."' WHERE article_cat='".$data['article_cat_id']."'");
        }
    }

    // Remove old cat access table
    dbquery("ALTER TABLE ".DB_ARTICLE_CATS." DROP COLUMN article_cat_access");

    // Insert new and old settings tables to Infusions table
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('article_pagination', '15', 'article')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('article_extended_required', '0', 'article')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('article_allow_submission', '1', 'article')");

    // Insert Infusion infused data
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3002']."', 'articles', '1.00')");

    // Remove old cats link and update new path for admin link
    dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='article_cats.php'");
    dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/articles/articles_admin.php' WHERE admin_link='articles.php'");
}

function upgrade_weblinks() {
    global $locale, $settings;

    // Moving access level from weblinks categories to weblinks and create field for subcategories
    dbquery("ALTER TABLE ".DB_WEBLINKS." ADD weblink_visibility CHAR(4) NOT NULL DEFAULT '-101' AFTER weblink_datestamp");
    dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER weblink_cat_id");

    // Add multilocale support
    dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER weblink_cat_sorting");

    // Set weblink visibility
    $result = dbquery("SELECT weblink_cat_id, weblink_cat_access FROM ".DB_WEBLINK_CATS);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_WEBLINKS." SET weblink_visibility='-".$data['weblink_cat_access']."' WHERE weblink_cat='".$data['weblink_cat_id']."'");
        }
    }

    // Remove old access table
    dbquery("ALTER TABLE ".DB_WEBLINK_CATS." DROP COLUMN weblink_cat_access");

    // Insert new and old settings tables to Infusions table
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('links_per_page', '15', 'weblinks')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('links_extended_required', '1', 'weblinks')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('links_allow_submission', '1', 'weblinks')");

    // Insert Infusion infused data
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3209']."', 'weblinks', '1.00')");

    // Remove old cats link and update new path for admin link
    dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='weblink_cats.php'");
    dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/weblinks/weblinks_admin.php' WHERE admin_link='weblinks.php'");
}

function upgrade_faq() {
    global $locale, $settings;

    // Add multilingual support
    dbquery("ALTER TABLE ".DB_FAQ_CATS." ADD faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER faq_cat_description");

    // Insert Infusion infused data
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3203']."', 'faq', '1.00')");

    // Update new path for admin link
    dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/faq/faq_admin.php' WHERE admin_link='faq.php'");
}

function upgrade_forum() {
    global $locale, $settings;

    // Install a new votes table to handle forum votes
    dbquery("CREATE TABLE ".DB_PREFIX."forum_votes (
			forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			vote_points DECIMAL(3,0) NOT NULL DEFAULT '0',
			vote_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0'
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

    // Install a new thread tags table
    dbquery("CREATE TABLE ".DB_PREFIX."forum_thread_tags (
    tag_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	tag_title VARCHAR(100) NOT NULL DEFAULT '',
	tag_description VARCHAR(250) NOT NULL DEFAULT '',
	tag_color VARCHAR(20) NOT NULL DEFAULT '',
	tag_status SMALLINT(1) NOT NULL DEFAULT '0',
	tag_language VARCHAR(100) NOT NULL DEFAULT '',
	PRIMARY KEY (tag_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

    // Install a new mood table
    dbquery("CREATE TABLE ".DB_PREFIX."forum_post_mood (
    mood_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	mood_name TEXT NOT NULL,
	mood_description TEXT NOT NULL,
	mood_icon VARCHAR(50) NOT NULL DEFAULT '',
	mood_notify SMALLINT(4) NOT NULL DEFAULT '-101',
	mood_access SMALLINT(4) NOT NULL DEFAULT '-101',
	mood_status SMALLINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (mood_id)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

    // Insert a new post notification table
    dbquery("CREATE TABLE ".DB_PREFIX."forum_post_notify (
    post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_mood_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_sender MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
	KEY notify_datestamp (notify_datestamp)
	) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");


    // Add multilingual support to ranks
    dbquery("ALTER TABLE ".DB_FORUM_RANKS." ADD rank_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER rank_apply");

    // Add thread answered to threads
    dbquery("ALTER TABLE ".DB_PREFIX."forum_threads ADD thread_answered TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_sticky");

    // Cosmetic rename of extention to mime
    dbquery("ALTER TABLE ".DB_FORUM_ATTACHMENTS." CHANGE attach_ext attach_mime VARCHAR(20) NOT NULL DEFAULT ''");

    // Additional column insertion
    dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_branch MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER forum_cat");
    dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_type TINYINT(1) NOT NULL DEFAULT '1' AFTER forum_name");
    dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_answer_threshold TINYINT(3) NOT NULL DEFAULT '15' AFTER forum_type");
    dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_lock TINYINT(1) NOT NULL DEFAULT '0' AFTER forum_answer_threshold");
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
    dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_access forum_access TINYINT(4) DEFAULT '-101'");
    dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_reply forum_reply TINYINT(4) DEFAULT '-101'");
    dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_vote forum_vote TINYINT(4) DEFAULT '-101'");
    dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_poll forum_poll TINYINT(4) DEFAULT '-101'");
    dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_attach forum_attach TINYINT(4) DEFAULT '-101'");
    dbquery("ALTER TABLE ".DB_FORUMS." CHANGE forum_attach_download forum_attach_download TINYINT(4) DEFAULT '-101'");

    // Update new access levels for forum access
    $result = dbquery("SELECT forum_id, forum_access FROM ".DB_FORUMS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['forum_access']) {
                dbquery("UPDATE ".DB_FORUMS." SET forum_access ='-".$data['forum_access']."' WHERE forum_id='".$data['forum_id']."' ");
            }
        }
    }

    // Update new access levels for forum reply
    $result = dbquery("SELECT forum_id, forum_reply FROM ".DB_FORUMS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['forum_reply']) {
                dbquery("UPDATE ".DB_FORUMS." SET forum_reply ='-".$data['forum_reply']."' WHERE forum_id='".$data['forum_id']."' ");
            }
        }
    }

    // Update new access levels for forum vote
    $result = dbquery("SELECT forum_id, forum_vote FROM ".DB_FORUMS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['forum_vote']) {
                dbquery("UPDATE ".DB_FORUMS." SET forum_vote ='-".$data['forum_vote']."' WHERE forum_id='".$data['forum_id']."' ");
            }
        }
    }

    // Update new access levels for forum poll
    $result = dbquery("SELECT forum_id, forum_poll FROM ".DB_FORUMS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['forum_poll']) {
                dbquery("UPDATE ".DB_FORUMS." SET forum_poll ='-".$data['forum_poll']."' WHERE forum_id='".$data['forum_id']."' ");
            }
        }
    }

    // Update new access levels for forum attach
    $result = dbquery("SELECT forum_id, forum_attach FROM ".DB_FORUMS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['forum_attach']) {
                dbquery("UPDATE ".DB_FORUMS." SET forum_attach ='-".$data['forum_attach']."' WHERE forum_id='".$data['forum_id']."' ");
            }
        }
    }

    // Update new access levels for forum attach
    $result = dbquery("SELECT forum_id, forum_attach_download FROM ".DB_FORUMS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['forum_attach_download']) {
                dbquery("UPDATE ".DB_FORUMS." SET forum_attach_download ='-".$data['forum_attach_download']."' WHERE forum_id='".$data['forum_id']."' ");
            }
        }
    }


    /*
     * After upgrade all forums are categories by default
     * Change old forums already inside a group to be a forum containing threads
     * This makes all existing threads accessible both in forums and in panels after upgrade
     */
    dbquery("UPDATE ".DB_FORUMS." SET forum_type = 2 WHERE forum_cat != 0");

    // Clear old core settings if they are there regardless of current state
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

    // Insert new and old settings tables to Infusions table
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

    // Infusion insert
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3204']."', 'forum', '1.00')");

    // Remove settings_forum from the Administration
    dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='settings_forum.php'");

    // Remove forum_ranks from the Administration
    dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='forum_ranks.php'");

    // Add forum default thread tag
    dbquery("INSERT INTO ".DB_PREFIX."forum_thread_tags (tag_title, tag_description, tag_color, tag_status, tag_language) VALUES ('".fusion_get_locale('forum_tag_0110',
            FORUM_LOCALE)."', '".fusion_get_locale('forum_tag_0111',
            FORUM_LOCALE)."', '#2e8c65', '1', '".LANGUAGE."')");

    // Remove old cats link and update new path for admin link
    dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/forum/admin/forums.php' WHERE admin_link='forums.php'");
}

function upgrade_gallery() {
    global $locale, $settings;

    // Option to use keywords in photos
    dbquery("ALTER TABLE ".DB_PHOTOS." ADD photo_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER photo_description");
    dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_language varchar(50) NOT NULL default '".$settings['locale']."' AFTER album_datestamp");
    dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER album_description");
    dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_image VARCHAR(200) NOT NULL DEFAULT '' AFTER album_keywords");
    dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_thumb1 VARCHAR(200) NOT NULL DEFAULT '' AFTER album_image");
    dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_thumb2 VARCHAR(200) NOT NULL DEFAULT '' AFTER album_thumb1");

    // Make sure access table are support for new levels
    dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." CHANGE album_access album_access CHAR(4) NOT NULL DEFAULT '-101'");

    // Update new access levels for album access
    $result = dbquery("SELECT album_id, album_access FROM ".DB_PHOTO_ALBUMS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['album_access']) {
                dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_access ='-".$data['album_access']."' WHERE album_id='".$data['album_id']."' ");
            }
        }
    }

    // Clear old core settings if they are there regardless of current state
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

    // Insert new and old settings tables to Infusions table
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

    // Insert Infusion infused data
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3206']."', 'gallery', '1.00')");

    // Update main gallery image from thumb as it is called on old gallery
    $result = dbquery("SELECT album_id, album_thumb FROM ".DB_PHOTO_ALBUMS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['album_thumb']) {
                dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_image='".$data['album_thumb']."' WHERE album_id='".$data['album_id']."' ");
            }
        }
    }

    // Remove old cats link and update new path for admin link
    dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/gallery/gallery_admin.php' WHERE admin_link='photoalbums.php'");
}

function upgrade_downloads() {
    global $locale, $settings;

    // Option to use keywords in downloads
    dbquery("ALTER TABLE ".DB_DOWNLOADS." ADD download_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER download_description");

    // Clear old settings from core if they are there regardless of current state
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_max_b'");
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_types'");
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_b'");
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_w'");
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_h'");
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screenshot'");
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_thumb_max_w'");
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_thumb_max_h'");
    dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_pagination'");

    // Insert new and old settings tables to Infusions table
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_max_b', '512000', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_types', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_b', '150000', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_w', '1024', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_h', '768', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screenshot', '1', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_thumb_max_w', '100', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_thumb_max_h', '100', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_pagination', '15', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_extended_required', '1', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screenshot_required', '1', 'downloads')");
    dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_allow_submission', '1', 'downloads')");

    // Add individual download item access
    dbquery("ALTER TABLE ".DB_DOWNLOADS." ADD download_visibility CHAR(4) NOT NULL DEFAULT '-101' AFTER download_datestamp");

    // Moving access level from downloads categories to downloads and create field for subcategories
    $result = dbquery("SELECT download_cat_id, download_cat_access FROM ".DB_DOWNLOAD_CATS);
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_DOWNLOADS." SET download_visibility='-".$data['download_cat_access']."' WHERE download_cat='".$data['download_cat_id']."'");
        }
    }

    // Add multilanguage support
    dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER download_cat_access");
    dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." DROP COLUMN download_cat_access");
    dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER download_cat_id");

    // Insert to infusions
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3202']."', 'downloads', '1.00')");

    // Remove old cats link and update new path for admin link
    dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='download_cats.php'");
    dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/downloads/downloads_admin.php' WHERE admin_link='downloads.php'");
}

function upgrade_poll() {
    global $locale, $settings;
    dbquery("ALTER TABLE ".DB_POLLS." ADD poll_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER poll_ended");
    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3207']."', 'member_poll_panel', '1.00')");

    // update new path for admin link
    dbquery("UPDATE ".DB_PREFIX."admin SET admin_link='../infusions/member_poll_panel/member_poll_panel_admin.php' WHERE admin_link='polls.php'");
}

function upgrade_comments() {
    dbquery("ALTER TABLE ".DB_PREFIX."comments ADD comment_cat MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER comment_type");
}




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
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'ÃŸ','ß')");
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¤','ä')");
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¼','ü')");
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¶','ö')");
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã„','Ä')");
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ãœ','Ü')");
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã–','Ö')");
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'â‚¬','€')");
                dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¥','Å')");
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
    $user_schema = array_flip(fieldgenerator(DB_PREFIX."users"));

    if (!isset($user_schema['user_inbox'])) {
        dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_inbox SMALLINT(6) unsigned not null default '0' AFTER user_status");
    }
    if (!isset($user_schema['user_outbox'])) {
        dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_outbox SMALLINT(6) unsigned not null default '0' AFTER user_inbox");
    }
    if (!isset($user_schema['user_archive'])) {
        dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_archive SMALLINT(6) unsigned not null default '0' AFTER user_outbox");
    }
    if (!isset($user_schema['user_pm_email_notify'])) {
        dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_pm_email_notify TINYINT(1) not null default '0' AFTER user_archive");
    }
    if (!isset($user_schema['user_pm_save_sent'])) {
        dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_pm_save_sent TINYINT(1) not null default '0' AFTER user_pm_email_notify");
    }

    // Drop if exists message options
    dbquery("DROP TABLE IF EXISTS ".DB_PREFIX."messages_options");

    $result = dbquery("SELECT * FROM ".DB_MESSAGES);
    if (dbrows($result) > 0) {
        // Perform data tally from 7.02.07
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_MESSAGES." SET message_user = ".$data['message_to']." WHERE message_id = ".$data['message_id']);
        }
    }
}

function upgrade_user_table($localeset) {
    // New access rights need a larger table for users
    dbquery("ALTER TABLE ".DB_USERS." CHANGE user_level user_level TINYINT(4) NOT NULL DEFAULT '-101'");
    // Modify All Users Level > 0
    $result = dbquery("SELECT user_id, user_level FROM ".DB_USERS);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['user_level']) { // will omit 0
                dbquery("UPDATE ".DB_USERS." SET user_level ='-".$data['user_level']."' WHERE user_id='".$data['user_id']."' ");
            }
        }
    }
    // Remove dropped rights, these settings have been moved to tabs and follow the Infusions rights
    $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS);
    while ($data = dbarray($result)) {
        $new_rights = str_replace(".S13", "", $data['user_rights']);
        $new_rights = str_replace(".S5", "", $new_rights);
        $new_rights = str_replace(".S11", "", $new_rights);
        $new_rights = str_replace(".SU", "", $new_rights);
        dbquery("UPDATE ".DB_USERS." SET user_rights='".$new_rights."' WHERE user_id='".$data['user_id']."'");
    }
    // Set all users theme to Default
    dbquery("UPDATE ".DB_USERS." SET user_theme ='Default'");
    // add user language fields
    $result = dbquery("ALTER TABLE ".DB_USERS." ADD user_language VARCHAR(50) NOT NULL DEFAULT '$localeset'");
    // Delete user_offset field an replace it with user_timezone
    $result = dbquery("ALTER TABLE ".DB_USERS." ADD user_timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/London' AFTER user_offset");
    $result = dbquery("ALTER TABLE ".DB_USERS." DROP COLUMN user_offset");
}


function install_theme_engine() {

    $locale = fusion_get_locale();
    // Install themes db
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

    // Give Superadmins access to new theme part
    $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
    while ($data = dbarray($result)) {
        dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".TS' WHERE user_id='".$data['user_id']."'");
    }
}

function upgrade_user_fields() {
    $locale = fusion_get_locale();
    // Update field tables
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

    // Dump old categories
    dbquery("TRUNCATE TABLE `".DB_PREFIX."user_field_cats`");

    // Install a new default set of UF cats
    $ufc_sql = "INSERT INTO ".DB_PREFIX."user_field_cats (field_cat_id, field_cat_name, field_parent, field_cat_db, field_cat_index, field_cat_class, field_cat_order) VALUES ";
    $ufc_sql .= implode(",\n", array(
        "(1, '".$locale['setup_3640']."', 0, '', '', 'entypo user', 1)",
        "(2, '".$locale['setup_3641']."', 1, '', '', 'entypo user', 1)",
        "(3, '".$locale['setup_3642']."', 1, '', '', 'entypo user', 2)",
        "(4, '".$locale['setup_3643']."', 1, '', '', 'entypo user', 3)",
        "(5, '".$locale['setup_3644']."', 1, '', '', 'entypo user', 4)",
        "(6, '".$locale['setup_3645']."', 1, '', '', 'entypo shareable', 5)"
    ));
    dbquery($ufc_sql);

    // Install User Fields
    $previous_install = array();
    $uf_to_install = array();

    $result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            $previous_install[$data['field_name']] = TRUE;
        }
    }

    if (!isset($previous_install['user_location'])) {
        $uf_to_install[] = "('user_location', '".$locale['uf_location']."', '3', 'file', '0', '1', '', '', '', '')";
    }
    if (!isset($previous_install['user_birthdate'])) {
        $uf_to_install[] = "('user_birthdate', '".$locale['uf_birthdate']."', '3', 'file', '0', '2', '1900-01-01', '', '', '')";
    }
    if (!isset($previous_install['user_skype'])) {
        $uf_to_install[] = "('user_skype', '".$locale['uf_skype']."', '2', 'file', '0', '1', '', '', '', '')";
    }
    if (!isset($previous_install['user_aim'])) {
        $uf_to_install[] = "('user_aim', '".$locale['uf_aim']."', '2', 'file', '0', '2', '', '', '', '')";
    }
    if (!isset($previous_install['user_icq'])) {
        $uf_to_install[] = "('user_icq', '".$locale['uf_icq']."', '2', 'file', '0', '3', '', '', '', '')";
    }
    if (!isset($previous_install['user_yahoo'])) {
        $uf_to_install[] = "('user_yahoo', '".$locale['uf_yahoo']."', '2', 'file', '0', '5', '', '', '', '')";
    }
    if (!isset($previous_install['user_web'])) {
        $uf_to_install[] = "('user_web', '".$locale['uf_web']."', '2', 'file', '0', '6', '', '', '', '')";
    }
    if (!isset($previous_install['user_theme'])) {
        $uf_to_install[] = "('user_theme', '".$locale['uf_theme']."', '4', 'file', '0', '2', '', '', '', '')";
    }
    if (!isset($previous_install['user_sig'])) {
        $uf_to_install[] = "('user_sig', '".$locale['uf_sig']."', '4', 'file', '0', '3', '', '', '', '')";
    }
    $uf_sql = "INSERT INTO ".DB_PREFIX."user_fields (field_name, field_title, field_cat, field_type, field_required, field_order, field_default, field_options, field_error, field_config) VALUES ";
    $uf_sql .= implode(",\n", $uf_to_install);
    dbquery($uf_sql);
}

function upgrade_custom_page() {
    $settings = fusion_get_settings();

    dbquery("CREATE TABLE ".DB_PREFIX."custom_pages_grid (
        page_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		page_grid_id MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
		page_grid_container TINYINT(1) NOT NULL DEFAULT '0',
		page_grid_column_count TINYINT(1) NOT NULL DEFAULT '0',
		page_grid_html_id VARCHAR(50) NOT NULL DEFAULT '',
		page_grid_class VARCHAR(100) NOT NULL DEFAULT '',
		page_grid_order TINYINT(5) NOT NULL DEFAULT '',
		PRIMARY KEY (page_grid_id),
		KEY page_id (page_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

    dbquery("CREATE TABLE ".DB_PREFIX."custom_pages_content (
        page_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		page_grid_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
		page_content_id MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
		page_content_type VARCHAR(50) NOT NULL DEFAULT '',
		page_content TEXT NOT NULL,
		page_content_order TINYINT(5) NOT NULL DEFAULT '0',
		page_widget VARCHAR(100) NOT NULL DEFAULT '',
		PRIMARY KEY (page_content_id),
		KEY page_id (page_id)
		KEY page_grid_id (page_grid_id)
		) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0' AFTER page_id");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0' AFTER page_cat");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER page_content");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_status SMALLINT(1) NOT NULL DEFAULT '0' AFTER page_keywords");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_user MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER page_status");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER page_user");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_language VARCHAR(255) NOT NULL DEFAULT '".$settings['locale']."' AFTER page_datestamp");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_grid_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0' AFTER page_language");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_content_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0' AFTER page_grid_id");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_left_panel TINYINT(1) NOT NULL DEFAULT '0' AFTER page_content_id");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_right_panel TINYINT(1) NOT NULL DEFAULT '0' AFTER page_left_panel");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_header_panel TINYINT(1) NOT NULL DEFAULT '0' AFTER page_right_panel");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_footer_panel TINYINT(1) NOT NULL DEFAULT '0' AFTER page_header_panel");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_top_panel TINYINT(1) NOT NULL DEFAULT '0' AFTER page_footer_panel");
    dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_bottom_panel TINYINT(1) NOT NULL DEFAULT '0' AFTER page_top_panel");
}

function upgrade_panels() {
    $settings = fusion_get_settings();
    dbquery("ALTER TABLE ".DB_PANELS." ADD panel_languages VARCHAR(200) NOT NULL DEFAULT '.".$settings['locale']."' AFTER panel_restriction");
    dbquery("UPDATE ".DB_PANELS." SET panel_restriction='2' WHERE panel_side='2'");
    dbquery("UPDATE ".DB_PANELS." SET panel_language='.".$settings['locale']."'");
}

function upgrade_site_links() {
    $settings = fusion_get_settings();

    //Multilingual support
    dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER link_order");

    // Site links new admin
    dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_cat MEDIUMINT(9) NOT NULL DEFAULT '0' AFTER link_id");
    dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_icon VARCHAR(100) NOT NULL DEFAULT '' AFTER link_url");

    // Change existing link_visibility to new access levels
    $result = dbquery("ALTER TABLE ".DB_SITE_LINKS." CHANGE link_visibility link_visibility CHAR(4) NOT NULL DEFAULT ''");

    // Update all access levels for site links
    $result = dbquery("SELECT link_id, link_visibility FROM ".DB_SITE_LINKS."");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['link_visibility']) {
                dbquery("UPDATE ".DB_SITE_LINKS." SET link_visibility ='-".$data['link_visibility']."' WHERE link_id='".$data['link_id']."' ");
            }
        }
    }

    // Update all Core site links
    dbquery("UPDATE ".DB_SITE_LINKS." SET link_url ='infusions/articles/articles.php' WHERE link_url='articles.php'");
    dbquery("UPDATE ".DB_SITE_LINKS." SET link_url ='infusions/downloads/downloads.php' WHERE link_url='downloads.php'");
    dbquery("UPDATE ".DB_SITE_LINKS." SET link_url ='infusions/faq/faq.php' WHERE link_url='faq.php'");
    dbquery("UPDATE ".DB_SITE_LINKS." SET link_url ='infusions/forum/index.php' WHERE link_url='forum/index.php'");
    dbquery("UPDATE ".DB_SITE_LINKS." SET link_url ='infusions/weblinks/weblinks.php' WHERE link_url='weblinks.php'");
    dbquery("UPDATE ".DB_SITE_LINKS." SET link_url ='infusions/gallery/gallery.php' WHERE link_url='photogallery.php'");
    dbquery("DELETE FROM ".DB_SITE_LINKS."	WHERE link_url='news_cats.php'");
}


function upgrade_multilang($localeset) {

    $locale = fusion_get_locale('', LOCALE.$localeset.'/setup.php');

    $settings = [];
    $set_result = dbquery("SELECT * FROM ".DB_PREFIX."settings");
    if (dbrows($set_result)>0) {
        while ($row = dbarray($set_result)) {
            $settings[$row['settings_name']] = $row['settings_value'];
        }
    }

    // Enabled languages array
    if (!isset($settings['enabled_languages'])) {
        dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('enabled_languages', '$localeset')");
    }

    // Add an Adminstration link for language handling
    if (!dbcount("admin_rights", DB_PREFIX."admin", "admin_rights='LANG'")) {
        dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('LANG', 'language.png', '".$locale['setup_3051']."', 'settings_languages.php', '4')");
    }

    // Add Lang rights to Super Administrator
    $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".LANG' WHERE user_id='".$data['user_id']."'");
        }
    }

    // Add Multilang table rights and status
    $mlt_check_array = [
        'AR'=>[
            'mlt_db'=>'articles',
            'mlt_title' => $locale['setup_3200'],
            'mlt_status' => 1
            ],
        'CP'=>[
            'mlt_db'=>'custom_pages',
            'mlt_title' => $locale['setup_3201'],
            'mlt_status' => 1
        ],
        'BL'=>[
            'mlt_db'=>'blog',
            'mlt_title' => $locale['setup_3213'],
            'mlt_status' => 1
        ],
        'DL' => [
            'mlt_db'=>'downloads',
            'mlt_title' => $locale['setup_3202'],
            'mlt_status' => 1
        ],
        'FQ' => [
            'mlt_db'=>'faq',
            'mlt_title' => $locale['setup_3203'],
            'mlt_status' => 1
        ],
        'FO' => [
            'mlt_db'=>'forums',
            'mlt_title' => $locale['setup_3204'],
            'mlt_status' => 1
        ],
        'NS' => [
            'mlt_db'=>'news',
            'mlt_title' => $locale['setup_3205'],
            'mlt_status' => 1
        ],
        'PG' => [
            'mlt_db'=>'photos',
            'mlt_title' => $locale['setup_3206'],
            'mlt_status' => 1
        ],
        'PO' => [
            'mlt_db'=>'polls',
            'mlt_title' => $locale['setup_3207'],
            'mlt_status' => 1
        ],
        'WL' => [
            'mlt_db'=>'weblinks',
            'mlt_title' => $locale['setup_3209'],
            'mlt_status' => 1
        ],
        'SL' => [
            'mlt_db'=>'sitelinks',
            'mlt_title' => $locale['setup_3210'],
            'mlt_status' => 1
        ],
        'PN' => [
            'mlt_db'=>'what_table_is_this',
            'mlt_title' => $locale['setup_3211'],
            'mlt_status' => 1
        ]
    ];

    $mlt_pattern = "INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('{%mlt_rights%}', '{%mlt_title%}', '{%mlt_status%}')";
    $result = dbquery("SELECT mlt_rights FROM ".DB_PREFIX."mlt_tables");
    if (dbrows($result)>0) {
        $mlt_result = [];
        while ($data = dbarray($result)) {
            $mlt_result[] = $data['mlt_rights'];
        }
        $mlt_result = array_flip($mlt_result);
        foreach($mlt_check_array as $key => $value) {
            if (!isset($mlt_result[$key])) {
                dbquery(strtr($mlt_pattern, [
                    '{%mlt_rights%}' => $key,
                    '{%mlt_title%}' => $mlt_check_array[$key]['mlt_title'],
                    '{%mlt_status%}' => $mlt_check_array[$key]['mlt_status']
                ]));
            }
        }
    } else {
        foreach ($mlt_check_array as $key => $value) {
            dbquery(strtr($mlt_pattern, [
                '{%mlt_rights%}' => $key,
                '{%mlt_title%}' => $mlt_check_array[$key]['mlt_title'],
                '{%mlt_status%}' => $mlt_check_array[$key]['mlt_status']
            ]));
        }
    }
}

function install_email_templates() {
    global $locale, $settings;

    // Email templates admin section
    $result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MAIL', 'email.png', '".$locale['setup_3800']."', 'email.php', '3')");
    if ($result) {
        $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".MAIL' WHERE user_id='".$data['user_id']."'");
        }
    }

    // Add Email template tables
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
    dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('PM', 'html', '0', '".$locale['setup_3801']."', '".$locale['setup_3802']."', '".$locale['setup_3803']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
    dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('POST', 'html', '0', '".$locale['setup_3804']."', '".$locale['setup_3805']."', '".$locale['setup_3806']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
    dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('CONTACT', 'html', '0', '".$locale['setup_3807']."', '".$locale['setup_3808']."', '".$locale['setup_3809']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
}

function install_seo() {
    $locale = fusion_get_locale();

    // Initiate SEO tables
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
    dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PL', 'permalink.png', '".$locale['setup_3052']."', 'permalink.php', '3')");

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

    // Comments Upgrade
    dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('comments_jquery', '1')");

    // Set a new default theme to prevent issues during upgrade
    dbquery("UPDATE ".DB_SETTINGS." SET settings_value='FusionTheme' WHERE settings_name='theme'");

    // Set a new default to PHP Execution
    dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('allow_php_exe', '0')");

    // Update server time offset´s to work with new function
    dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Europe/London' WHERE settings_name='timeoffset'");
    dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Europe/London' WHERE settings_name='serveroffset'");

    // Update opening page to contact since it is bound to be there
    dbquery("UPDATE ".DB_SETTINGS." SET settings_value='contact.php' WHERE settings_name='opening_page'");

    // Remove user field cats setting
    dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='user_field_cats.php'");

    // Add privacy policy setting
    dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('privacy_policy', '')");

    // Add OG tags setting
    dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('create_og_tags', '1')");

    // Add index url bbcode setting
    dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('index_url_bbcode', '1')");

    // Add index url userweb setting
    dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('index_url_userweb', '1')");

}

// Upgrade new icons
function upgrade_admin_icons() {
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
    foreach ($new_icon_array as $admin_rights => $icon_file) {
        dbquery("UPDATE ".DB_ADMIN." SET admin_image='".$icon_file."' WHERE admin_rights='".$admin_rights."'");
    }
}
