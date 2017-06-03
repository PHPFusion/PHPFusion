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
    die("Access Denied!");
}

// Locales
if (!defined("ARTICLE_LOCALE")) {
    if (file_exists(INFUSIONS."articles/locale/".LOCALESET."articles.php")) {
        define("ARTICLE_LOCALE", INFUSIONS."articles/locale/".LOCALESET."articles.php");
    } else {
        define("ARTICLE_LOCALE", INFUSIONS."articles/locale/English/articles.php");
    }
}
if (!defined("ARTICLE_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."articles/locale/".LOCALESET."article_admin.php")) {
        define("ARTICLE_ADMIN_LOCALE", INFUSIONS."articles/locale/".LOCALESET."article_admin.php");
    } else {
        define("ARTICLE_ADMIN_LOCALE", INFUSIONS."articles/locale/English/article_admin.php");
    }
}

// Paths
if (!defined("ARTICLE_CLASS")) {
    define("ARTICLE_CLASS", INFUSIONS."articles/classes/");
}
if (!defined("IMAGES_A")) {
	define("IMAGES_A", INFUSIONS."articles/images/");
}

// Database
if (!defined("DB_ARTICLE_CATS")) {
	define("DB_ARTICLE_CATS", DB_PREFIX."article_cats");
}
if (!defined("DB_ARTICLES")) {
	define("DB_ARTICLES", DB_PREFIX."articles");
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("A", "<i class='admin-ico fa fa-fw fa-book'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("AC", "<i class='admin-ico fa fa-fw fa-book'></i>");
\PHPFusion\Admins::getInstance()->setCommentType("A", fusion_get_locale("A", LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitType("a", fusion_get_locale("A", LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitLink("a", INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s");
\PHPFusion\Admins::getInstance()->setLinkType("A", fusion_get_settings("siteurl")."infusions/articles/articles.php?article_id=%s");
\PHPFusion\Admins::getInstance()->setSubmitData('a', [
		'infusion_name' => 'articles',
		'link'          => INFUSIONS."articles/article_submit.php",
		'submit_link'   => "submit.php?stype=a",
		'submit_locale' => fusion_get_locale('A', LOCALE.LOCALESET."admin/main.php"),
		'title'         => fusion_get_locale('submit_0001', LOCALE.LOCALESET."submissions.php"),
		'admin_link'    => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
	]);
