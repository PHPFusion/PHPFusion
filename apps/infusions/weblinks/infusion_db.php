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

// Locales
define('WEBLINK_LOCALE', fusion_get_inf_locale_path('weblinks.php', INFUSIONS.'weblinks/locale/'));
define('WEBLINK_ADMIN_LOCALE', fusion_get_inf_locale_path('weblinks_admin.php', INFUSIONS.'weblinks/locale/'));

// Paths
const WEBLINKS = INFUSIONS."weblinks/";
const WEBLINKS_CLASSES = INFUSIONS."weblinks/classes/";

// Database
const DB_WEBLINKS = DB_PREFIX."weblinks";
const DB_WEBLINK_CATS = DB_PREFIX."weblink_cats";

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("W", "<i class='admin-ico fa fa-fw fa-link'></i>");

$inf_settings = get_settings('weblinks');
if (
    (!empty($inf_settings['links_allow_submission']) && $inf_settings['links_allow_submission']) &&
    (!empty($inf_settings['links_submission_access']) && checkgroup($inf_settings['links_submission_access']))
) {
    \PHPFusion\Admins::getInstance()->setSubmitData('l', [
        'infusion_name' => 'weblinks',
        'link'          => INFUSIONS."weblinks/weblink_submit.php",
        'submit_link'   => "submit.php?stype=l",
        'submit_locale' => fusion_get_locale('271', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('weblink_submit', WEBLINK_ADMIN_LOCALE),
        'admin_link'    => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink()."&section=submissions&submit_id=%s"
    ]);
}
