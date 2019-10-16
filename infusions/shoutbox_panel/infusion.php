<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: PHP Fusion Development Team
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

$locale = fusion_get_locale("", SHOUTBOX_LOCALE);

// Infusion general information
$inf_title = $locale['SB_title'];
$inf_description = $locale['SB_desc'];
$inf_version = "1.1";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "shoutbox_panel";
$inf_image = "shouts.svg";

// Create tables
$inf_newtable[] = DB_SHOUTBOX." (
    shout_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    shout_name VARCHAR(50) NOT NULL DEFAULT '',
    shout_message VARCHAR(200) NOT NULL DEFAULT '',
    shout_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    shout_ip VARCHAR(45) NOT NULL DEFAULT '',
    shout_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
    shout_hidden TINYINT(4) NOT NULL DEFAULT '0',
    shout_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (shout_id),
    KEY shout_datestamp (shout_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert panel
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction, panel_languages) VALUES('".fusion_get_locale("SB_title", SHOUTBOX_LOCALE)."', '".$inf_folder."', '', '4', '3', 'file', '0', '1', '1', '', '3', '".fusion_get_settings('enabled_languages')."')";

// Insert settings
$settings = [
    'visible_shouts' => 5,
    'guest_shouts'   => 0,
    'hidden_shouts'  => 0
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage table
$inf_mlt[] = [
    "title"  => $inf_title,
    "rights" => "SB"
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include INFUSIONS."shoutbox_panel/locale/".$language."/shoutbox.php";

        $mlt_adminpanel[$language][] = [
            "rights"   => "S",
            "image"    => $inf_image,
            "title"    => $locale['SB_admin1'],
            "panel"    => "shoutbox_admin.php",
            "page"     => 5,
            'language' => $language
        ];

        // Delete
        $mlt_deldbrow[$language][] = DB_SHOUTBOX." WHERE shout_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='SB' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "S",
        "image"    => $inf_image,
        "title"    => $locale['SB_admin1'],
        "panel"    => "shoutbox_admin.php",
        "page"     => 5,
        'language' => LANGUAGE
    ];
}

// Uninstallation
$inf_droptable[] = DB_SHOUTBOX;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='S'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='SB'";
