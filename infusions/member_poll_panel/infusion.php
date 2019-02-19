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
$inf_title = $locale['polls']['title'];
$inf_description = $locale['polls']['description'];
$inf_version = "1.1";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "member_poll_panel";
$inf_image = "polls.svg";

// Create tables
$inf_newtable[] = DB_POLL_VOTES." (
    vote_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    vote_user_ip VARCHAR(45) NOT NULL DEFAULT '',
    vote_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
    vote_opt SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
    poll_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (vote_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_POLLS." (
    poll_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    poll_title VARCHAR(200) NOT NULL DEFAULT '',
    poll_opt TEXT NOT NULL,
    poll_started INT(10) UNSIGNED NOT NULL DEFAULT '0',
    poll_ended INT(10) UNSIGNED NOT NULL DEFAULT '0',
    poll_visibility TINYINT(4) NOT NULL DEFAULT '0',
    PRIMARY KEY (poll_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert panel
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction, panel_languages) VALUES ('".$locale['setup_3407']."', '".$inf_folder."', '', '1', '5', 'file', '0', '1', '1', '', '3', '".fusion_get_settings('enabled_languages')."')";

// Multilanguage table
$inf_mlt[] = [
    "title"  => $locale['setup_3207'],
    "rights" => "PO"
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include LOCALE.$language."/setup.php";

        $mlt_adminpanel[$language][] = [
            "rights"   => "PO",
            "image"    => $inf_image,
            "title"    => $locale['setup_3022'],
            "panel"    => "poll_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3022']."', 'infusions/".$inf_folder."/polls_archive.php', '0', '1', '0', '2', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/polls_archive.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='PO' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "PO",
        "image"    => $inf_image,
        "title"    => $locale['setup_3022'],
        "panel"    => "poll_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3022']."', 'infusions/".$inf_folder."/polls_archive.php', '0', '1', '0', '2', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_POLLS;
$inf_droptable[] = DB_POLL_VOTES;
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url = 'infusions/".$inf_folder."/polls_archive.php'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='PO'";
