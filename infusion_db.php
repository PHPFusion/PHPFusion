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
defined('IN_FUSION') || exit;

if (!defined("BLOG_LOCALE")) {
    if (file_exists(INFUSIONS."blog/locale/".LOCALESET."blog.php")) {
        define("BLOG_LOCALE", INFUSIONS."blog/locale/".LOCALESET."blog.php");
    } else {
        define("BLOG_LOCALE", INFUSIONS."blog/locale/English/blog.php");
    }
}
if (!defined("BLOG_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."blog/locale/".LOCALESET."blog_admin.php")) {
        define("BLOG_ADMIN_LOCALE", INFUSIONS."blog/locale/".LOCALESET."blog_admin.php");
    } else {
        define("BLOG_ADMIN_LOCALE", INFUSIONS."blog/locale/English/blog_admin.php");
    }
}

if (!defined("IMAGES_B")) {
    define("IMAGES_B", INFUSIONS."blog/images/");
}
if (!defined("IMAGES_B_T")) {
    define("IMAGES_B_T", INFUSIONS."blog/images/thumbs/");
}
if (!defined("IMAGES_BC")) {
    define("IMAGES_BC", INFUSIONS."blog/blog_cats/");
}
if (!defined("DB_BLOG")) {
    define("DB_BLOG", DB_PREFIX."blog");
}
if (!defined("DB_BLOG_CATS")) {
    define("DB_BLOG_CATS", DB_PREFIX."blog_cats");
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("BLOG", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("BLC", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("S13", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
\PHPFusion\Admins::getInstance()->setCommentType('B', fusion_get_locale('BLOG', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setLinkType('B', fusion_get_settings("siteurl")."infusions/blog/blog.php?readmore=%s");

$inf_settings = get_settings('blog');
if (!empty($inf_settings['blog_allow_submission']) && $inf_settings['blog_allow_submission']) {
    \PHPFusion\Admins::getInstance()->setSubmitData('b', [
        'infusion_name' => 'blog',
        'link'          => INFUSIONS."blog/blog_submit.php",
        'submit_link'   => "submit.php?stype=b",
        'submit_locale' => fusion_get_locale('BLOG', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('blog_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."blog/blog_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
    ]);
}

\PHPFusion\Admins::getInstance()->setFolderPermissions('blog', [
    'infusions/blog/images/'        => TRUE,
    'infusions/blog/images/thumbs/' => TRUE
]);
