<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/infusion_db.php
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
if (!defined("WEBLINK_LOCALE")) {
    if (file_exists(INFUSIONS."weblinks/locale/".LOCALESET."weblinks.php")) {
        define("WEBLINK_LOCALE", INFUSIONS."weblinks/locale/".LOCALESET."weblinks.php");
    } else {
        define("WEBLINK_LOCALE", INFUSIONS."weblinks/locale/English/weblinks.php");
    }
}

if (!defined("WEBLINK_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."weblinks/locale/".LOCALESET."weblinks_admin.php")) {
        define("WEBLINK_ADMIN_LOCALE", INFUSIONS."weblinks/locale/".LOCALESET."weblinks_admin.php");
    } else {
        define("WEBLINK_ADMIN_LOCALE", INFUSIONS."weblinks/locale/English/weblinks_admin.php");
    }
}

// Paths
if (!defined("WEBLINKS_CLASS")) {
    define("WEBLINKS_CLASS", INFUSIONS."weblinks/classes/");
}

// Database
if (!defined("DB_WEBLINK_CATS")) {
	define("DB_WEBLINK_CATS", DB_PREFIX."weblink_cats");
}
if (!defined("DB_WEBLINKS")) {
	define("DB_WEBLINKS", DB_PREFIX."weblinks");
}
// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("W", "<i class='admin-ico fa fa-fw fa-sitemap'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("WC", "<i class='admin-ico fa fa-fw fa-sitemap'></i>");
//\PHPFusion\Admins::getInstance()->setSubmitType('l', fusion_get_locale('271', LOCALE.LOCALESET."admin/main.php"));
//\PHPFusion\Admins::getInstance()->setSubmitLink('l', INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s");
\PHPFusion\Admins::getInstance()->setSubmitData('l', [
		'infusion_name' => 'weblinks',
		'link'          => INFUSIONS."weblinks/weblink_submit.php",
		'submit_link'   => "submit.php?stype=l",
		'submit_locale' => fusion_get_locale('271', LOCALE.LOCALESET."admin/main.php"),
		'title'         => fusion_get_locale('submit_0004', LOCALE.LOCALESET."submissions.php"),
		'admin_link'    => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
	]);
