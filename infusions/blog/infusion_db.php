<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog/infusion_db.php
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

define("IMAGES_B", INFUSIONS."blog/images/");
define("IMAGES_B_T", INFUSIONS."blog/images/thumbs/");
define("IMAGES_BC", INFUSIONS."blog/blog_cats/");
define("DB_BLOG", DB_PREFIX."blog");
define("DB_BLOG_CATS", DB_PREFIX."blog_cats");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("BLOG", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("BLC", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("S13", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
\PHPFusion\Admins::getInstance()->setCommentType('B', fusion_get_locale('BLOG', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setSubmitType('b', fusion_get_locale('BLOG', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setSubmitLink('b', INFUSIONS."blog/blog_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s");
\PHPFusion\Admins::getInstance()->setLinkType('B', fusion_get_settings("siteurl")."infusions/blog/blog.php?readmore=%s");
