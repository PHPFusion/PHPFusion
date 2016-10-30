<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog/infusion_db.php
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
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("BLOG", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("BLC", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("S13", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>"); // Blog Settings
	\PHPFusion\Admins::getInstance()->setCommentType('B', $locale['BLOG']);
	\PHPFusion\Admins::getInstance()->setSubmitType('b', $locale['BLOG']);
	\PHPFusion\Admins::getInstance()->setLinkType('B', fusion_get_settings("siteurl")."infusions/blog/blog.php?readmore=%s");
}
