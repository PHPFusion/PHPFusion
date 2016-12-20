<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
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
    die("Access Denied!");
}

// Locale
$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");

// Main Informations
$inf_title = $locale['articles']['title'];
$inf_description = $locale['articles']['description'];
$inf_version = "1.2";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "articles";
$inf_image = "articles.png";

// Tables
$inf_newtable[] = DB_ARTICLES." (
	article_id             MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	article_subject        VARCHAR(200)          NOT NULL DEFAULT '',
	article_cat            MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	article_snippet        TEXT                  NOT NULL,
	article_article        TEXT                  NOT NULL,
	article_keywords       VARCHAR(250)          NOT NULL DEFAULT '',
	article_breaks         CHAR(1)               NOT NULL DEFAULT '',
	article_name           MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
	article_datestamp      INT(10)      UNSIGNED NOT NULL DEFAULT '0',
	article_visibility     CHAR(4)               NOT NULL DEFAULT '0',
	article_reads          INT(10)      UNSIGNED NOT NULL DEFAULT '0',
	article_draft          TINYINT(1)   UNSIGNED NOT NULL DEFAULT '0',
	article_allow_comments TINYINT(1)   UNSIGNED NOT NULL DEFAULT '1',
	article_allow_ratings  TINYINT(1)   UNSIGNED NOT NULL DEFAULT '1',
	article_language       VARCHAR(50)           NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (article_id),
	KEY article_cat (article_cat),
	KEY article_datestamp (article_datestamp),
	KEY article_reads (article_reads)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_ARTICLE_CATS." (
	article_cat_id          MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	article_cat_parent      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	article_cat_name        VARCHAR(100)          NOT NULL DEFAULT '',
	article_cat_description TEXT                  NOT NULL,
	article_cat_visibility  CHAR(4)               NOT NULL DEFAULT '0',
	article_cat_status      TINYINT(1)   UNSIGNED NOT NULL DEFAULT '1',
	article_cat_language    VARCHAR(50)           NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (article_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Adminlink
$inf_adminpanel[] = array(
    "image" => "articles.png",
    "page" => 1,
    "rights" => "A",
    "title" => $locale['setup_3002'],
    "panel" => "articles_admin.php",
);

// Insert Settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('article_pagination', '15', 'article')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('article_allow_submission', '1', 'article')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('article_extended_required', '0', 'article')";

// Insert Panel
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES('".$locale['setup_3325']."', 'latest_articles_panel', '', '1', '5', 'file', '0', '0', '1', '', '0')";

// Insert Multilanguage
$inf_mlt[] = array(
    "title" => $locale['articles']['title'],
    "rights" => "AR",
);

// Multilanguage Sitelinks
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale("", LOCALE.$language."/setup.php");
		
        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3002']."', 'infusions/articles/articles.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3312']."', 'submit.php?stype=a', ".USER_LEVEL_MEMBER.", '1', '0', '14', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/articles/articles.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=a' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ARTICLE_CATS." WHERE article_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ARTICLES." WHERE article_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3002']."', 'infusions/articles/articles.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3312']."', 'submit.php?stype=a', ".USER_LEVEL_MEMBER.", '1', '0', '13', '1', '".LANGUAGE."')";
}

// Deinstallation
$inf_droptable[] = DB_ARTICLES;
$inf_droptable[] = DB_ARTICLE_CATS;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='A'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='AC'";
$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='A'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='AR'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='A'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='latest_articles_panel'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='article'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/articles/articles.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=a'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='a'";
$inf_delfiles[] = IMAGES_A;
