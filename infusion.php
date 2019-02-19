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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");

// Infusion general information
$inf_title = $locale['news']['title'];
$inf_description = $locale['news']['description'];
$inf_version = "1.12";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "news";
$inf_image = "news.svg";

// Create tables
$inf_newtable[] = DB_NEWS." (
    news_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    news_subject VARCHAR(200) NOT NULL DEFAULT '',
    news_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    news_news TEXT NOT NULL,
    news_extended TEXT NOT NULL,
    news_keywords VARCHAR(250) NOT NULL DEFAULT '',
    news_breaks CHAR(1) NOT NULL DEFAULT '',
    news_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
    news_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    news_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
    news_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
    news_visibility TINYINT(4) NOT NULL DEFAULT '0',
    news_reads INT(10) UNSIGNED NOT NULL DEFAULT '0',
    news_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    news_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    news_image_align VARCHAR(15) NOT NULL DEFAULT '',
    news_image_full_default MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    news_image_front_default MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    news_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    news_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    news_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (news_id),
    KEY news_datestamp (news_datestamp),
    KEY news_reads (news_reads)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_NEWS_IMAGES." (
    news_image_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    news_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    submit_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    news_image VARCHAR(100) NOT NULL DEFAULT '',
    news_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
    news_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
    news_image_user MEDIUMINT(9) NOT NULL DEFAULT '0',
    news_image_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (news_image_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_NEWS_CATS." (
    news_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    news_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    news_cat_name VARCHAR(100) NOT NULL DEFAULT '',
    news_cat_image VARCHAR(100) NOT NULL DEFAULT '',
    news_cat_visibility TINYINT(4) NOT NULL DEFAULT '0',
    news_cat_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    news_cat_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    news_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (news_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$settings = [
    'news_image_readmore'         => 1,
    'news_image_frontpage'        => 0,
    'news_thumb_ratio'            => 0,
    'news_image_link'             => 1,
    'news_photo_w'                => 1920,
    'news_photo_h'                => 1080,
    'news_thumb_w'                => 800,
    'news_thumb_h'                => 640,
    'news_photo_max_w'            => 2048,
    'news_photo_max_h'            => 1365,
    'news_photo_max_b'            => 15728640,
    'news_pagination'             => 12,
    'news_allow_submission'       => 1,
    'news_allow_submission_files' => 1,
    'news_extended_required'      => 0,
    'news_file_types'             => '.pdf,.gif,.jpg,.png,.svg,.zip,.rar,.tar,.bz2,.7z'
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage table
$inf_mlt[] = [
    "title"  => $locale['news']['title'],
    "rights" => "NS"
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include LOCALE.$language."/setup.php";

        $mlt_adminpanel[$language][] = [
            "rights"   => "N",
            "image"    => $inf_image,
            "title"    => $locale['setup_3018'],
            "panel"    => "news_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3205']."', 'infusions/".$inf_folder."/news.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3311']."', 'submit.php?stype=n', ".USER_LEVEL_MEMBER.", '1', '0', '25', '1', '".$language."')";

        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3500']."', 'bugs.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3501']."', 'downloads.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3502']."', 'games.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3503']."', 'graphics.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3504']."', 'hardware.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3505']."', 'journal.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3506']."', 'members.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3507']."', 'mods.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3509']."', 'network.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3510']."', 'news.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3511']."', 'php-fusion.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3512']."', 'security.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3513']."', 'software.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3514']."', 'themes.svg', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['setup_3515']."', 'windows.svg', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/news.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=n' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_NEWS_CATS." WHERE news_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_NEWS." WHERE news_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='N' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "N",
        "image"    => $inf_image,
        "title"    => $locale['setup_3018'],
        "panel"    => "news_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3205']."', 'infusions/".$inf_folder."/news.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3311']."', 'submit.php?stype=n', ".USER_LEVEL_MEMBER.", '1', '0', '25', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_NEWS;
$inf_droptable[] = DB_NEWS_CATS;
$inf_droptable[] = DB_NEWS_IMAGES;
$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='N'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='N'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='N'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/news.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=n'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='NS'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='n'";
