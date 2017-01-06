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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Infusion general information
$locale = fusion_get_locale("", SHOUTBOX_LOCALE);

$inf_title = $locale['SB_title'];
$inf_description = $locale['SB_desc'];
$inf_version = "1.0.5";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "shoutbox_panel"; // The folder in which the infusion resides.
$inf_image = "shouts.png";

//Administration panel
$inf_adminpanel[] = array(
    "title" => $locale['SB_admin1'],
    "image" => $inf_image,
    "panel" => "shoutbox_admin.php",
    "rights" => "S",
    "page" => 5
);

//Multilanguage table for Administration
$inf_mlt[] = array(
    "title" => $inf_title,
    "rights" => "SB"
);
// Delete any items not required below.
$inf_newtable[] = DB_SHOUTBOX." (
    shout_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    shout_name VARCHAR(50) NOT NULL DEFAULT '',
    shout_message VARCHAR(200) NOT NULL DEFAULT '',
    shout_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    shout_ip VARCHAR(45) NOT NULL DEFAULT '',
    shout_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
    shout_hidden TINYINT(4) NOT NULL DEFAULT '0',
    shout_language VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY (shout_id),
    KEY shout_datestamp (shout_datestamp)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// shoutbox deletion of MLT shouts
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        $mlt_deldbrow[$language][] = DB_SHOUTBOX." WHERE shout_language='".$language."'";
    }
}

//Infuse insertations
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction, panel_languages) VALUES('".fusion_get_locale("SB_title",
                                                                                                                                                                                                                            SHOUTBOX_LOCALE)."', 'shoutbox_panel', '', '4', '3', 'file', '0', '1', '1', '', '3', '".fusion_get_settings('enabled_languages')."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('visible_shouts', '5', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('guest_shouts', '0', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('hidden_shouts', '0', '".$inf_folder."')";

//Defuse cleaning
$inf_droptable[] = DB_SHOUTBOX;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='S'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='SB'";
