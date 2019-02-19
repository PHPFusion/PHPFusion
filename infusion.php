<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery/infusion.php
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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");

// Infusion general information
$inf_title = $locale['photos']['title'];
$inf_description = $locale['photos']['description'];
$inf_version = "1.1";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "gallery";
$inf_image = "gallery.svg";

// Create tables
$inf_newtable[] = DB_PHOTO_ALBUMS." (
    album_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    album_title VARCHAR(100) NOT NULL DEFAULT '',
    album_description TEXT NOT NULL,
    album_keywords VARCHAR(250) NOT NULL DEFAULT '',
    album_image VARCHAR(200) NOT NULL DEFAULT '',
    album_thumb1 VARCHAR(200) NOT NULL DEFAULT '',
    album_thumb2 VARCHAR(200) NOT NULL DEFAULT '',
    album_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    album_access TINYINT(4) NOT NULL DEFAULT '0',
    album_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    album_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    album_language varchar(50) NOT NULL default '".LANGUAGE."',
    PRIMARY KEY (album_id),
    KEY album_order (album_order),
    KEY album_datestamp (album_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_PHOTOS." (
    photo_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    album_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    photo_title VARCHAR(100) NOT NULL DEFAULT '',
    photo_description TEXT NOT NULL,
    photo_keywords VARCHAR(250) NOT NULL DEFAULT '',
    photo_filename VARCHAR(100) NOT NULL DEFAULT '',
    photo_thumb1 VARCHAR(100) NOT NULL DEFAULT '',
    photo_thumb2 VARCHAR(100) NOT NULL DEFAULT '',
    photo_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    photo_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    photo_views INT(10) UNSIGNED NOT NULL DEFAULT '0',
    photo_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    photo_allow_comments tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
    photo_allow_ratings tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
    PRIMARY KEY (photo_id),
    KEY photo_order (photo_order),
    KEY photo_datestamp (photo_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$settings = [
    'thumb_w'                     => 200,
    'thumb_h'                     => 200,
    'photo_w'                     => 800,
    'photo_h'                     => 600,
    'photo_max_w'                 => 2800,
    'photo_max_h'                 => 2600,
    'photo_max_b'                 => 15728640,
    'gallery_pagination'          => 24,
    'photo_watermark'             => 1,
    'photo_watermark_image'       => 'infusions/gallery/photos/watermark.png',
    'photo_watermark_text'        => 0,
    'photo_watermark_text_color1' => 'FF6600',
    'photo_watermark_text_color2' => 'FFFF00',
    'photo_watermark_text_color3' => 'FFFFFF',
    'photo_watermark_save'        => 0,
    'gallery_allow_submission'    => 1,
    'gallery_extended_required'   => 1,
    'gallery_file_types'          => '.pdf,.gif,.jpg,.png,.svg,.zip,.rar,.tar,.bz2,.7z'
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage table
$inf_mlt[] = [
    "title"  => $locale['setup_3308'],
    "rights" => "PG"
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include LOCALE.$language."/setup.php";

        $mlt_adminpanel[$language][] = [
            "rights"   => "PH",
            "image"    => $inf_image,
            "title"    => $locale['setup_3308'],
            "panel"    => "gallery_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3308']."', 'infusions/".$inf_folder."/gallery.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3313']."', 'submit.php?stype=p', ".USER_LEVEL_MEMBER.", '1', '0', '24', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/gallery.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=p' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_PHOTO_ALBUMS." WHERE album_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='PH' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "PH",
        "image"    => $inf_image,
        "title"    => $locale['setup_3308'],
        "panel"    => "gallery_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3308']."', 'infusions/".$inf_folder."/gallery.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3313']."', 'submit.php?stype=p', ".USER_LEVEL_MEMBER.", '1', '0', '24', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_PHOTO_ALBUMS;
$inf_droptable[] = DB_PHOTOS;
$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='P'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='P'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='p'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='PH'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/gallery.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=p'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='PG'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_delfiles[] = IMAGES_G;
$inf_delfiles[] = IMAGES_G_T;
