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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");

// Infusion general information
$inf_title = $locale['weblinks']['title'];
$inf_description = $locale['weblinks']['description'];
$inf_version = "1.2";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "weblinks";
$inf_image = "weblink.svg";

// Create tables
$inf_newtable[] = DB_WEBLINKS." (
    weblink_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    weblink_name VARCHAR(100) NOT NULL DEFAULT '',
    weblink_description TEXT NOT NULL,
    weblink_url VARCHAR(200) NOT NULL DEFAULT '',
    weblink_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    weblink_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    weblink_visibility TINYINT(4) NOT NULL DEFAULT '0',
    weblink_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    weblink_count SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    weblink_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (weblink_id),
    KEY weblink_datestamp (weblink_datestamp),
    KEY weblink_count (weblink_count)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_WEBLINK_CATS." (
    weblink_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    weblink_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    weblink_cat_name VARCHAR(100) NOT NULL DEFAULT '',
    weblink_cat_description TEXT NOT NULL,
    weblink_cat_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    weblink_cat_visibility TINYINT(4) NOT NULL DEFAULT '0',
    weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (weblink_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$settings = [
    'links_per_page'          => 15,
    'links_extended_required' => 1,
    'links_allow_submission'  => 1
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage table
$inf_mlt[] = [
    "title"  => $locale['weblinks']['title'],
    "rights" => "WL"
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include LOCALE.$language."/setup.php";

        $mlt_adminpanel[$language][] = [
            "rights"   => "W",
            "image"    => $inf_image,
            "title"    => $locale['setup_3029'],
            "panel"    => "weblinks_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3307']."', 'infusions/".$inf_folder."/weblinks.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3310']."', 'submit.php?stype=l', ".USER_LEVEL_MEMBER.", '1', '0', '26', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/weblinks.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=l' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_WEBLINKS." WHERE weblink_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_WEBLINK_CATS." WHERE weblink_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='W' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "W",
        "image"    => $inf_image,
        "title"    => $locale['setup_3029'],
        "panel"    => "weblinks_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3307']."', 'infusions/".$inf_folder."/weblinks.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3310']."', 'submit.php?stype=l', ".USER_LEVEL_MEMBER.", '1', '0', '26', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_WEBLINKS;
$inf_droptable[] = DB_WEBLINK_CATS;
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='l'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='W'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='WC'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/weblinks.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=l'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='WL'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
