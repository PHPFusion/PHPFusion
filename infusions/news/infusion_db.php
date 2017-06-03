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
define("IMAGES_N", INFUSIONS."news/images/");
define("IMAGES_N_T", INFUSIONS."news/images/thumbs/");
define("IMAGES_NC", INFUSIONS."news/news_cats/");
define("DB_NEWS", DB_PREFIX."news");
define("DB_NEWS_CATS", DB_PREFIX."news_cats");
define("DB_NEWS_IMAGES", DB_PREFIX."news_gallery");

\PHPFusion\Admins::getInstance()->setAdminPageIcons("N", "<i class='admin-ico fa fa-fw fa-newspaper-o'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("NC", "<i class='admin-ico fa fa-fw fa-newspaper-o'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("S8", "<i class='admin-ico fa fa-fw fa-newspaper-o'></i>");
\PHPFusion\Admins::getInstance()->setCommentType('N', fusion_get_locale('N', LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitType('n', fusion_get_locale('N', LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitLink('n', INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s");
\PHPFusion\Admins::getInstance()->setLinkType('N', fusion_get_settings("siteurl")."infusions/news/news.php?readmore=%s");
\PHPFusion\Admins::getInstance()->setSubmitData('n', [
		'infusion_name' => 'news',
		'link'          => INFUSIONS."news/news_submit.php",
		'submit_link'   => "submit.php?stype=n",
		'submit_locale' => fusion_get_locale('N', LOCALE.LOCALESET."admin/main.php"),
		'title'         => fusion_get_locale('submit_0000', LOCALE.LOCALESET."submissions.php"),
		'admin_link'    => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
	]);

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
