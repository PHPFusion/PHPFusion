<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', WIKI_LOCALE);

// Infusion general information
$inf_title       = $locale['wiki_title'];
$inf_description = $locale['wiki_desc'];
$inf_version     = '1.0.0';
$inf_developer   = 'RobiNN';
$inf_email       = 'robinn@php-fusion.eu';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'wiki';
$inf_image       = 'wiki.svg';

// Create tables
$inf_newtable[] = DB_WIKI." (
    wiki_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    wiki_name VARCHAR(50) NOT NULL DEFAULT '',
    wiki_type VARCHAR(5) NOT NULL DEFAULT '',
    wiki_cat MEDIUMINT(8) NOT NULL DEFAULT '0',
    wiki_parent MEDIUMINT(8) NOT NULL DEFAULT '0',
    wiki_description TEXT NOT NULL,
    wiki_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    wiki_order MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
    wiki_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    wiki_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    wiki_access MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    wiki_edited MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    wiki_edited_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    wiki_versions VARCHAR(50) NOT NULL DEFAULT '',
    wiki_allow_markdown TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    wiki_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (wiki_id),
    KEY wiki_cat (wiki_cat)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_WIKI_CATS." (
    wiki_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    wiki_cat_name VARCHAR(50) NOT NULL DEFAULT '',
    wiki_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    wiki_cat_description TEXT NOT NULL,
    wiki_cat_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    wiki_cat_access MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    wiki_cat_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    wiki_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (wiki_cat_id),
    KEY wiki_cat_parent (wiki_cat_parent)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_WIKI_VERSIONS." (
    wiki_version_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    wiki_version VARCHAR(10) NOT NULL DEFAULT '',
    PRIMARY KEY (wiki_version_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$settings = [
    'wiki_allow_submission' => 1
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage table
$inf_mlt[] = [
    'title'  => $locale['wiki_title'],
    'rights' => 'WIKI'
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, '.|..', TRUE, 'folders');
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        if (file_exists(INFUSIONS.'wiki/locale/'.$language.'/wiki.php')) {
            include INFUSIONS.'wiki/locale/'.$language.'/wiki.php';
        } else {
            include INFUSIONS.'wiki/locale/English/wiki.php';
        }

        $mlt_adminpanel[$language][] = [
            'rights'   => 'WIKI',
            'image'    => $inf_image,
            'title'    => $locale['wiki_title'],
            'panel'    => 'admin.php',
            'page'     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['wiki_title']."', 'infusions/wiki/wiki.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['wiki_submit']."', 'submit.php?stype=w', ".USER_LEVEL_MEMBER.", '1', '0', '27', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/wiki/wiki.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=w' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_WIKI_CATS." WHERE wiki_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='WIKI' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        'rights'   => 'WIKI',
        'image'    => $inf_image,
        'title'    => $locale['wiki_title'],
        'panel'    => 'admin.php',
        'page'     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['wiki_title']."', 'infusions/wiki/wiki.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['wiki_submit']."', 'submit.php?stype=w', ".USER_LEVEL_MEMBER.", '1', '0', '27', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_WIKI;
$inf_droptable[] = DB_WIKI_CATS;
$inf_droptable[] = DB_WIKI_VERSIONS;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='WIKI'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/wiki/wiki.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=wiki'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='w'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='WIKI'";
$inf_delfiles[] = IMAGES_WIKI;
