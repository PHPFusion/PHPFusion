<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/infusion_db.php
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
define("IMAGES_A", INFUSIONS."articles/images/");
define("DB_ARTICLE_CATS", DB_PREFIX."article_cats");
define("DB_ARTICLES", DB_PREFIX."articles");
if (defined("ADMIN_PANEL")) {
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("A", "<i class='admin-ico fa fa-fw fa-book'></i>");
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("AC", "<i class='admin-ico fa fa-fw fa-book'></i>");
    \PHPFusion\Admins::getInstance()->setCommentType('A', $locale['A']);
    \PHPFusion\Admins::getInstance()->setSubmitType('a', $locale['A']);
    \PHPFusion\Admins::getInstance()->setLinkType('A', fusion_get_settings("siteurl")."infusions/articles/articles.php?article_id=%s");
}
