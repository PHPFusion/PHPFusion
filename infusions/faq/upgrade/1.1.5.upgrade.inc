<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /faq/upgrade/1.1.5.upgrade.inc
| Upgrade FAQ to Version 9.0.2 (RC5)
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
$inf_version = '1.1.5';
require_once (INCLUDES.'infusions_include.php');
// Add multilingual support
if (!column_exists(DB_FAQS, 'faq_breaks', FALSE)) {
    $inf_altertable[] = DB_FAQS." ADD faq_breaks CHAR(1) NOT NULL DEFAULT 'y' AFTER faq_answer";
}

if (!column_exists(DB_FAQS, 'faq_name', FALSE)) {
    $inf_altertable[] = DB_FAQS." ADD faq_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1' AFTER faq_breaks";
}

if (!column_exists(DB_FAQS, 'faq_datestamp', FALSE)) {
    $inf_altertable[] = DB_FAQS." ADD faq_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER faq_name";
}

if (!column_exists(DB_FAQS, 'faq_visibility', FALSE)) {
    $inf_altertable[] = DB_FAQS." ADD faq_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER faq_datestamp";
}

if (!column_exists(DB_FAQS, 'faq_status', FALSE)) {
    $inf_altertable[] = DB_FAQS." ADD faq_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER faq_visibility";
}

if (!column_exists(DB_FAQS, 'faq_language', FALSE)) {
    $inf_altertable[] = DB_FAQS." ADD faq_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER faq_status";
}

$settings = get_settings('faq');
!isset($settings['faq_allow_submission']) ? $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('faq_allow_submission', '1', 'faq')" : "";

$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3327']."', 'submit.php?stype=q', ".USER_LEVEL_MEMBER.", '1', '0', '14', '1', '".$language."')";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3327']."', 'submit.php?stype=q', ".USER_LEVEL_MEMBER.", '1', '0', '13', '1', '".LANGUAGE."')";
}

$inf_updatedbrow[] = DB_ADMIN." SET admin_link='../infusions/faq/faq_admin.php' WHERE admin_link='faq.php'";
