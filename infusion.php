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
$inf_title = $locale['faqs']['title'];
$inf_description = $locale['faqs']['description'];
$inf_version = "1.1";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "faq";
$inf_image = "faq.svg";

// Create tables
$inf_newtable[] = DB_FAQS." (
    faq_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    faq_cat_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    faq_question VARCHAR(200) NOT NULL DEFAULT '',
    faq_answer TEXT NOT NULL,
    faq_breaks CHAR(1) NOT NULL DEFAULT '',
    faq_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
    faq_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    faq_visibility CHAR(4) NOT NULL DEFAULT '0',
    faq_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    faq_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY(faq_id),
    KEY faq_cat_id (faq_cat_id),
    KEY faq_datestamp (faq_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_FAQ_CATS." (
    faq_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    faq_cat_name VARCHAR(200) NOT NULL DEFAULT '',
    faq_cat_description VARCHAR(250) NOT NULL DEFAULT '',
    faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY(faq_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('faq_allow_submission', '1', '".$inf_folder."')";

// Multilanguage table
$inf_mlt[] = [
    "title"  => $locale['setup_3011'],
    "rights" => "FQ"
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include LOCALE.$language."/setup.php";

        $mlt_adminpanel[$language][] = [
            "rights"   => "FQ",
            "image"    => $inf_image,
            "title"    => $locale['setup_3011'],
            "panel"    => "faq_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3303']."', 'infusions/".$inf_folder."/faq.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3327']."', 'submit.php?stype=q', ".USER_LEVEL_MEMBER.", '1', '0', '23', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/faq.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=q' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_FAQ_CATS." WHERE faq_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_FAQS." WHERE faq_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='FQ' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "FQ",
        "image"    => $inf_image,
        "title"    => $locale['setup_3011'],
        "panel"    => "faq_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3303']."', 'infusions/".$inf_folder."/faq.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3327']."', 'submit.php?stype=q', ".USER_LEVEL_MEMBER.", '1', '0', '23', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_FAQ_CATS;
$inf_droptable[] = DB_FAQS;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='FQ'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/faq.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=q'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FQ'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='q'";
