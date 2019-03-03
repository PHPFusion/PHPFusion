<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/infusion_db.php
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

// Locales
if (!defined("FAQ_LOCALE")) {
    if (file_exists(INFUSIONS."faq/locale/".LOCALESET."faq.php")) {
        define("FAQ_LOCALE", INFUSIONS."faq/locale/".LOCALESET."faq.php");
    } else {
        define("FAQ_LOCALE", INFUSIONS."faq/locale/English/faq.php");
    }
}

// Paths
if (!defined('FAQ_CLASS')) {
    define('FAQ_CLASS', INFUSIONS.'faq/classes/');
}
// Database
if (!defined('DB_FAQS')) {
    define('DB_FAQS', DB_PREFIX.'faqs');
}
if (!defined('DB_FAQ_CATS')) {
    define('DB_FAQ_CATS', DB_PREFIX.'faq_cats');
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("FQ", "<i class='admin-ico fa fa-fw fa-life-buoy'></i>");

$inf_settings = get_settings('faq');
if (!empty($inf_settings['faq_allow_submission']) && $inf_settings['faq_allow_submission']) {
    \PHPFusion\Admins::getInstance()->setSubmitData('q', [
        'infusion_name' => 'faq',
        'link'          => INFUSIONS."faq/faq_submit.php",
        'submit_link'   => "submit.php?stype=q",
        'submit_locale' => fusion_get_locale('FQ', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('faq_submit', FAQ_LOCALE),
        'admin_link'    => INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
    ]);
}
