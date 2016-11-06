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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
define("DOWNLOADS", INFUSIONS."downloads/");
define("IMAGES_D", INFUSIONS."downloads/images/");
define("DB_DOWNLOAD_CATS", DB_PREFIX."download_cats");
define("DB_DOWNLOADS", DB_PREFIX."downloads");

if (defined("ADMIN_PANEL")) {
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("D", "<i class='admin-ico fa fa-fw fa-cloud-download'></i>");
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("DC", "<i class='admin-ico fa fa-fw fa-cloud-download'></i>");
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("S11", "<i class='admin-ico fa fa-fw fa-cloud-download'></i>");
    \PHPFusion\Admins::getInstance()->setCommentType('D', $locale['D']);
    \PHPFusion\Admins::getInstance()->setSubmitType('d', $locale['D']);
    \PHPFusion\Admins::getInstance()->setLinkType('D', fusion_get_settings("siteurl")."infusions/downloads/downloads.php?download_id=%s");
}
