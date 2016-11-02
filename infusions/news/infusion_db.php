<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/infusion_db.php
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
if (defined("ADMIN_PANEL")) {
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("N", "<i class='admin-ico fa fa-fw fa-newspaper-o'>");
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("NC", "<i class='admin-ico fa fa-fw fa-newspaper-o'></i>");
    \PHPFusion\Admins::getInstance()->setAdminPageIcons("S8", "<i class='admin-ico fa fa-fw fa-newspaper-o'></i>");
    \PHPFusion\Admins::getInstance()->setCommentType('N', $locale['N']);
    \PHPFusion\Admins::getInstance()->setSubmitType('n', $locale['N']);
    \PHPFusion\Admins::getInstance()->setLinkType('N', fusion_get_settings("siteurl")."infusions/news/news.php?readmore=%s");
}

if (!defined("NEWS_LOCALE")) {
    if (file_exists(INFUSIONS."news/locale/".LOCALESET."news.php")) {
        define("NEWS_LOCALE", INFUSIONS."news/locale/".LOCALESET."news.php");
    } else {
        define("NEWS_LOCALE", INFUSIONS."news/locale/English/news.php");
    }
}

if (!defined("NEWS_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."news/locale/".LOCALESET."news_admin.php")) {
        define("NEWS_ADMIN_LOCALE", INFUSIONS."news/locale/".LOCALESET."news_admin.php");
    } else {
        define("NEWS_ADMIN_LOCALE", INFUSIONS."news/locale/English/news_admin.php");
    }
}

if (!defined("NEWS_CLASS")) {
    define("NEWS_CLASS", INFUSIONS."news/classes/");
}
