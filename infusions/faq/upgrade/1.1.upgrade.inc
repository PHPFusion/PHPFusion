<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/upgrade/1.1.upgrade.inc
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
$inf_version = '1.1';
require_once INCLUDES.'infusions_include.php';

// Add multilingual support
if (!column_exists(DB_FAQ_CATS, 'faq_cat_language', FALSE)) {
    $inf_altertable[] = DB_FAQ_CATS." ADD faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER faq_cat_description";
}

$check_faq = [
    'faq_breaks'     => " ADD faq_breaks CHAR(1) NOT NULL DEFAULT 'y' AFTER faq_answer",
    'faq_name'       => " ADD faq_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '".fusion_get_userdata('user_id')."' AFTER faq_breaks",
    'faq_datestamp'  => " ADD faq_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '".time()."' AFTER faq_name",
    'faq_visibility' => " ADD faq_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER faq_datestamp",
    'faq_status'     => " ADD faq_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER faq_visibility",
    'faq_language'   => " ADD faq_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER faq_status"
];

foreach ($check_faq as $key => $value) {
    if (!column_exists(DB_FAQS, $key, FALSE)) {
        $inf_altertable[] = DB_FAQS.$value;
    }
}

$settings = get_settings('faq');
if (!isset($settings['faq_allow_submission'])) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('faq_allow_submission', '1', 'faq')";
}
$inf_updatedbrow[] = DB_ADMIN." SET admin_image='../infusions/faq/faq.svg', admin_link='../infusions/faq/faq_admin.php' WHERE admin_rights='FQ'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='faq.php'";
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");

if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3303']."', 'infusions/".$inf_folder."/faq.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3327']."', 'submit.php?stype=q', ".USER_LEVEL_MEMBER.", '1', '0', '23', '1', '".$language."')";
        // drop deprecated language records
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/faq/faq.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=q' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_FAQS." WHERE faq_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_FAQ_CATS." WHERE faq_cat_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3303']."', 'infusions/faq/faq.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3327']."', 'submit.php?stype=q', ".USER_LEVEL_MEMBER.", '1', '0', '23', '1', '".LANGUAGE."')";
}
