<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion_db.php
| Author: Core Development Team
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

use PHPFusion\Admins;

// Locales
define("FAQ_LOCALE", fusion_get_inf_locale_path('faq.php', INFUSIONS.'faq/locale/'));

// Paths
const FAQ = INFUSIONS.'faq/';
const FAQ_CLASSES = INFUSIONS.'faq/classes/';

// Database
const DB_FAQS = DB_PREFIX.'faqs';
const DB_FAQ_CATS = DB_PREFIX.'faq_cats';

// Admin Settings
Admins::getInstance()->setAdminPageIcons("FQ", "<i class='admin-ico fa fa-fw fa-life-buoy'></i>");

$inf_settings = get_settings('faq');
if (
    (!empty($inf_settings['faq_allow_submission']) && $inf_settings['faq_allow_submission']) &&
    (!empty($inf_settings['faq_submission_access']) && checkgroup($inf_settings['faq_submission_access']))
) {
    Admins::getInstance()->setSubmitData('q', [
        'infusion_name' => 'faq',
        'link'          => INFUSIONS."faq/faq_submit.php",
        'submit_link'   => "submit.php?stype=q",
        'submit_locale' => fusion_get_locale('FQ', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('faq_submit', FAQ_LOCALE),
        'admin_link'    => INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&section=submissions&submit_id=%s"
    ]);
}
