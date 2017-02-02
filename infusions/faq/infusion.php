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
    die("Access Denied");
}

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");

// Infusion general information
$inf_title = $locale['faqs']['title'];
$inf_description = $locale['faqs']['description'];
$inf_version = "1.2";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "faq";
$inf_image = "faq.png";

// Multilanguage table for Administration
$inf_mlt[] = array(
    "title" => $locale['setup_3011'],
    "rights" => "FQ",
);

// Create tables
$inf_newtable[] = DB_FAQS." (
	faq_id          MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	faq_cat_id      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	faq_question    VARCHAR(200)          NOT NULL DEFAULT '',
	faq_answer      TEXT                  NOT NULL,
	faq_breaks      CHAR(1)               NOT NULL DEFAULT '',
	faq_name        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
	faq_datestamp   INT(10)      UNSIGNED NOT NULL DEFAULT '0',
	faq_visibility  CHAR(4)               NOT NULL DEFAULT '0',
	faq_status      TINYINT(1)   UNSIGNED NOT NULL DEFAULT '0',
	faq_language    VARCHAR(50)           NOT NULL DEFAULT '".LANGUAGE."',
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

// Position these links under Content Administration
$inf_adminpanel[] = array(
    "image" => $inf_image,
    "page" => 1,
    "rights" => "FQ",
    "title" => $locale['setup_3011'],
    "panel" => "faq_admin.php",
);

// Insert Settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('faq_allow_submission', '1', 'faq')";

$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
// Create a link for all installed languages
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3303']."', 'infusions/faq/faq.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3327']."', 'submit.php?stype=q', ".USER_LEVEL_MEMBER.", '1', '0', '14', '1', '".$language."')";
        // drop deprecated language records
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/faq/faq.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=q' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_FAQS." WHERE faq_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3303']."', 'infusions/faq/faq.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3327']."', 'submit.php?stype=q', ".USER_LEVEL_MEMBER.", '1', '0', '13', '1', '".LANGUAGE."')";
}

// Defuse cleaning
$inf_droptable[] = DB_FAQS;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='FQ'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/faq/faq.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=q'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='faq'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='FQ'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='q'";