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
define('GALLERY_LOCALE', fusion_get_inf_locale_path('gallery.php', INFUSIONS."gallery/locale/"));
define('GALLERY_ADMIN_LOCALE', fusion_get_inf_locale_path('gallery_admin.php', INFUSIONS."gallery/locale/"));

// Paths
const GALLERY = INFUSIONS."gallery/";
const IMAGES_G = INFUSIONS."gallery/photos/";
const IMAGES_G_T = INFUSIONS."gallery/photos/thumbs/";

//  Database
const DB_PHOTO_ALBUMS = DB_PREFIX."photo_albums";
const DB_PHOTOS = DB_PREFIX."photos";

// Admin Settings
Admins::getInstance()->setAdminPageIcons("PH", "<i class='admin-ico fa fa-fw fa-camera-retro'></i>");
Admins::getInstance()->setCommentType('P', fusion_get_locale('272', LOCALE.LOCALESET."admin/main.php"));
Admins::getInstance()->setLinkType('P', fusion_get_settings("siteurl")."infusions/gallery/gallery.php?photo_id=%s");

$inf_settings = get_settings('gallery');
if (
    (!empty($inf_settings['gallery_allow_submission']) && $inf_settings['gallery_allow_submission']) &&
    (!empty($inf_settings['gallery_submission_access']) && checkgroup($inf_settings['gallery_submission_access']))
) {
    Admins::getInstance()->setSubmitData('p', [
        'infusion_name' => 'gallery',
        'link'          => INFUSIONS."gallery/photo_submit.php",
        'submit_link'   => "submit.php?stype=p",
        'submit_locale' => fusion_get_locale('272', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('gallery_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."gallery/gallery_admin.php".fusion_get_aidlink()."&section=submissions&submit_id=%s"
    ]);
}

Admins::getInstance()->setFolderPermissions('gallery', [
    'infusions/gallery/photos/'             => TRUE,
    'infusions/gallery/photos/thumbs/'      => TRUE,
    'infusions/gallery/submissions/'        => TRUE,
    'infusions/gallery/submissions/thumbs/' => TRUE
]);

Admins::getInstance()->setCustomFolder('PH', [
    [
        'path'  => IMAGES_G,
        'URL'   => fusion_get_settings('siteurl').'infusions/gallery/photos/',
        'alias' => 'gallery'
    ]
]);
