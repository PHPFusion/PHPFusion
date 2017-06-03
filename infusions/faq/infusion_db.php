<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/infusion_db.php
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

// Locales
if (!defined("FAQ_LOCALE")) {
    if (file_exists(INFUSIONS."faq/locale/".LOCALESET."faq.php")) {
        define("FAQ_LOCALE", INFUSIONS."faq/locale/".LOCALESET."faq.php");
    } else {
        define("FAQ_LOCALE", INFUSIONS."faq/locale/English/faq.php");
    }
}

if (!defined("FAQ_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."faq/locale/".LOCALESET."faq_admin.php")) {
        define("FAQ_ADMIN_LOCALE", INFUSIONS."faq/locale/".LOCALESET."faq_admin.php");
    } else {
        define("FAQ_ADMIN_LOCALE", INFUSIONS."faq/locale/English/faq_admin.php");
    }
}

// Paths
if (!defined('FAQ_CLASS')) define('FAQ_CLASS', INFUSIONS.'faq/classes/');
// Database
if (!defined('DB_FAQS')) define('DB_FAQS', DB_PREFIX.'faqs');
if (!defined('DB_FAQ_CATS')) define('DB_FAQ_CATS', DB_PREFIX.'faq_cats');

\PHPFusion\Admins::getInstance()->setAdminPageIcons("FQ", "<i class='admin-ico fa fa-fw fa-life-buoy'></i>");
\PHPFusion\Admins::getInstance()->setCommentType("FQ", fusion_get_locale("FQ", LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitType("q", fusion_get_locale("FQ", LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitLink("q", INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s");
\PHPFusion\Admins::getInstance()->setSubmitData('q', [
		'infusion_name' => 'faqs',
		'link'          => INFUSIONS."faq/faq_submit.php",
		'submit_link'   => "submit.php?stype=q",
		'submit_locale' => fusion_get_locale('FQ', LOCALE.LOCALESET."admin/main.php"),
		'title'         => fusion_get_locale('submit_0006', LOCALE.LOCALESET."submissions.php"),
		'admin_link'    => INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
	]);
