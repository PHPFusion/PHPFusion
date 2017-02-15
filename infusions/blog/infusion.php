<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: J.Falk (Falk)
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

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");

// Infusion general information
$inf_title = $locale['blog']['title'];
$inf_description = $locale['blog']['description'];
$inf_version = "1.1";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "blog";
$inf_image = "blog.png";

// Multilanguage table for Administration
$inf_mlt[] = array(
    "title" => $locale['blog']['title'],
    "rights" => "BL",
);
// Create tables
$inf_newtable[] = DB_BLOG." (
	blog_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	blog_subject VARCHAR(200) NOT NULL DEFAULT '',
	blog_image VARCHAR(100) NOT NULL DEFAULT '',
	blog_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
	blog_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
	blog_ialign VARCHAR(15) NOT NULL DEFAULT '',
	blog_cat VARCHAR(50) NOT NULL DEFAULT '0',
	blog_blog TEXT NOT NULL,
	blog_extended TEXT NOT NULL,
	blog_keywords VARCHAR(250) NOT NULL DEFAULT '',
	blog_breaks CHAR(1) NOT NULL DEFAULT '',
	blog_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
	blog_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	blog_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
	blog_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
	blog_visibility TINYINT(4) NOT NULL DEFAULT '0',
	blog_reads INT(10) UNSIGNED NOT NULL DEFAULT '0',
	blog_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	blog_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	blog_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	blog_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	blog_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (blog_id),
	KEY blog_datestamp (blog_datestamp),
	KEY blog_reads (blog_reads)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_BLOG_CATS." (
	blog_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	blog_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	blog_cat_name VARCHAR(100) NOT NULL DEFAULT '',
	blog_cat_image VARCHAR(100) NOT NULL DEFAULT '',
	blog_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (blog_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
// Automatic enable the archives panel
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES('Blog archive panel', 'blog_archive_panel', '', '1', '5', 'file', '0', '0', '1', '', '0')";
// Position these links under Content Administration
$inf_adminpanel[] = array(
    "image" => $inf_image,
    "page" => 1,
    "rights" => "BLOG",
    "title" => $locale['setup_3055'],
    "panel" => "blog_admin.php"
);

// Insert settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_thumb_ratio', '0', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_image_link', '1', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_photo_w', '400', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_photo_h', '300', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_thumb_w', '100', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_thumb_h', '100', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_photo_max_w', '1800', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_photo_max_h', '1600', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_photo_max_b', '150000', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_pagination', '12', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_allow_submission', '1', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_allow_submission_files', '1', 'blog')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('blog_extended_required', '0', 'blog')";

// always find and loop ALL languages
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
// Create a link for all installed languages
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {

        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");

        // add new language records
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3055']."', 'infusions/blog/blog.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3317']."', 'submit.php?stype=b', ".USER_LEVEL_MEMBER.", '1', '0', '14', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3500']."', 'bugs.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3501']."', 'downloads.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3502']."', 'games.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3503']."', 'graphics.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3504']."', 'hardware.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3505']."', 'journal.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3506']."', 'members.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3507']."', 'mods.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3509']."', 'network.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3511']."', 'php-fusion.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3512']."', 'security.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3513']."', 'software.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3514']."', 'themes.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['setup_3515']."', 'windows.svg', '".$language."')";

        // drop deprecated language records
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/blog/blog.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=b' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_BLOG_CATS." WHERE blog_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_BLOG." WHERE blog_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3055']."', 'infusions/blog/blog.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3317']."', 'submit.php?stype=b', ".USER_LEVEL_MEMBER.", '1', '0', '14', '1', '".LANGUAGE."')";
}

// Defuse cleanup
$inf_droptable[] = DB_BLOG;
$inf_droptable[] = DB_BLOG_CATS;

$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='B'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='B'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='B'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='blog_archive_panel'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='BLOG'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='blog'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/blog/blog.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=b'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='BL'";

$inf_delfiles[] = IMAGES_B_T;
$inf_delfiles[] = IMAGES_B;
