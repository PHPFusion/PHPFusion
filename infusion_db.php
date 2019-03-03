<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads/infusion_db.php
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

if (!defined("DOWNLOAD_LOCALE")) {
    if (file_exists(INFUSIONS."downloads/locale/".LOCALESET."downloads.php")) {
        define("DOWNLOAD_LOCALE", INFUSIONS."downloads/locale/".LOCALESET."downloads.php");
    } else {
        define("DOWNLOAD_LOCALE", INFUSIONS."downloads/locale/English/downloads.php");
    }
}

if (!defined("DOWNLOAD_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."downloads/locale/".LOCALESET."downloads_admin.php")) {
        define("DOWNLOAD_ADMIN_LOCALE", INFUSIONS."downloads/locale/".LOCALESET."downloads_admin.php");
    } else {
        define("DOWNLOAD_ADMIN_LOCALE", INFUSIONS."downloads/locale/English/downloads_admin.php");
    }
}

if (!defined("DOWNLOADS")) {
    define("DOWNLOADS", INFUSIONS."downloads/");
}
if (!defined("IMAGES_D")) {
    define("IMAGES_D", INFUSIONS."downloads/images/");
}
if (!defined("DB_DOWNLOAD_CATS")) {
    define("DB_DOWNLOAD_CATS", DB_PREFIX."download_cats");
}
if (!defined("DB_DOWNLOADS")) {
    define("DB_DOWNLOADS", DB_PREFIX."downloads");
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("D", "<i class='admin-ico fa fa-fw fa-cloud-download'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("DC", "<i class='admin-ico fa fa-fw fa-cloud-download'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("S11", "<i class='admin-ico fa fa-fw fa-cloud-download'></i>");
\PHPFusion\Admins::getInstance()->setCommentType('D', fusion_get_locale('D', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setLinkType('D', fusion_get_settings("siteurl")."infusions/downloads/downloads.php?download_id=%s");

$inf_settings = get_settings('downloads');
if (!empty($inf_settings['download_allow_submission']) && $inf_settings['download_allow_submission']) {
    \PHPFusion\Admins::getInstance()->setSubmitData('d', [
        'infusion_name' => 'downloads',
        'link'          => INFUSIONS."downloads/download_submit.php",
        'submit_link'   => "submit.php?stype=d",
        'submit_locale' => fusion_get_locale('D', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('download_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."downloads/downloads_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
    ]);
}

\PHPFusion\Admins::getInstance()->setFolderPermissions('downloads', [
    'infusions/downloads/files/'              => TRUE,
    'infusions/downloads/images/'             => TRUE,
    'infusions/downloads/submissions/'        => TRUE,
    'infusions/downloads/submissions/images/' => TRUE
]);
