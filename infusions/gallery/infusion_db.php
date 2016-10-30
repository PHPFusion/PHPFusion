<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/infusion_db.php
| Author: PHP-Fusion Development Team
| Version: 9.2 prototype
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
if (defined("ADMIN_PANEL")) {
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("PH", "<i class='admin-ico fa fa-fw fa-camera-retro'></i>");
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("S5", "<i class='admin-ico fa fa-fw fa-camera-retro'></i>");
	\PHPFusion\Admins::getInstance()->setCommentType('P', $locale['272']);
	\PHPFusion\Admins::getInstance()->setCommentType('PH', $locale['261']);
	\PHPFusion\Admins::getInstance()->setSubmitType('p', $locale['272']);
	\PHPFusion\Admins::getInstance()->setLinkType('P', fusion_get_settings("siteurl")."infusions/gallery/gallery.php?photo_id=%s");
	\PHPFusion\Admins::getInstance()->setLinkType('PH', fusion_get_settings("siteurl")."infusions/gallery/gallery.php?photo_id=%s");
}
