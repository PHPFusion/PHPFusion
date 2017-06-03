<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery/infusion_db.php
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
//  Define Paths
define("IMAGES_G", INFUSIONS."gallery/photos/");
define("IMAGES_G_T", INFUSIONS."gallery/photos/thumbs/");

//  Define Tables
define("DB_PHOTO_ALBUMS", DB_PREFIX."photo_albums");
define("DB_PHOTOS", DB_PREFIX."photos");

//  Define Locale
if (file_exists(INFUSIONS."gallery/locale/".LOCALESET."gallery.php")) {
    define('GALLERY_LOCALE', INFUSIONS."gallery/locale/".LOCALESET."gallery.php");
} else {
    define('GALLERY_LOCALE', INFUSIONS."gallery/locale/English/gallery.php");
}

if (file_exists(INFUSIONS."gallery/locale/".LOCALESET."gallery_admin.php")) {
    define('GALLERY_ADMIN_LOCALE', INFUSIONS."gallery/locale/".LOCALESET."gallery_admin.php");
} else {
    define('GALLERY_ADMIN_LOCALE', INFUSIONS."gallery/locale/English/gallery_admin.php");
}

//  Administration & Dashboard
\PHPFusion\Admins::getInstance()->setAdminPageIcons("PH", "<i class='admin-ico fa fa-fw fa-camera-retro'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("S5", "<i class='admin-ico fa fa-fw fa-camera-retro'></i>");
\PHPFusion\Admins::getInstance()->setCommentType('P', fusion_get_locale('272', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setCommentType('PH', fusion_get_locale('261', LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitType('p', fusion_get_locale('272', LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitLink('p', INFUSIONS."gallery/gallery_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s");
\PHPFusion\Admins::getInstance()->setLinkType('P', fusion_get_settings("siteurl")."infusions/gallery/gallery.php?photo_id=%s");
\PHPFusion\Admins::getInstance()->setLinkType('PH', fusion_get_settings("siteurl")."infusions/gallery/gallery.php?photo_id=%s");
\PHPFusion\Admins::getInstance()->setSubmitData('p', [
		'infusion_name' => 'gallery',
		'link'          => INFUSIONS."gallery/photo_submit.php",
		'submit_link'   => "submit.php?stype=p",
		'submit_locale' => fusion_get_locale('272', LOCALE.LOCALESET."admin/main.php"),
		'title'         => fusion_get_locale('submit_0003', LOCALE.LOCALESET."submissions.php"),
		'admin_link'    => INFUSIONS."gallery/gallery_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
	]);
