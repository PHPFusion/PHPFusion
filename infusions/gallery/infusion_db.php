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
define("IMAGES_G", INFUSIONS."gallery/photos/");
define("IMAGES_G_T", INFUSIONS."gallery/photos/thumbs/");
define("DB_PHOTO_ALBUMS", DB_PREFIX."photo_albums");
define("DB_PHOTOS", DB_PREFIX."photos");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("PH", "<i class='admin-ico fa fa-fw fa-camera-retro'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("S5", "<i class='admin-ico fa fa-fw fa-camera-retro'></i>");
\PHPFusion\Admins::getInstance()->setCommentType('P', fusion_get_locale('272', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setCommentType('PH', fusion_get_locale('261', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setSubmitType('p', fusion_get_locale('272', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setSubmitLink('p', INFUSIONS."gallery/gallery_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s");
\PHPFusion\Admins::getInstance()->setLinkType('P', fusion_get_settings("siteurl")."infusions/gallery/gallery.php?photo_id=%s");
\PHPFusion\Admins::getInstance()->setLinkType('PH', fusion_get_settings("siteurl")."infusions/gallery/gallery.php?photo_id=%s");
