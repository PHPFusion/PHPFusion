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

use PHPFusion\Admins;

defined('IN_FUSION') || exit;

if (!defined("IMAGES_N")) {
    define("IMAGES_N", INFUSIONS."news/images/");
}
if (!defined("IMAGES_N_T")) {
    define("IMAGES_N_T", INFUSIONS."news/images/thumbs/");
}
if (!defined("IMAGES_NC")) {
    define("IMAGES_NC", INFUSIONS."news/news_cats/");
}
if (!defined("DB_NEWS")) {
    define("DB_NEWS", DB_PREFIX."news");
}
if (!defined("DB_NEWS_CATS")) {
    define("DB_NEWS_CATS", DB_PREFIX."news_cats");
}
if (!defined("DB_NEWS_IMAGES")) {
    define("DB_NEWS_IMAGES", DB_PREFIX."news_gallery");
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

// Admin Settings
function news_admin_prop() {
    $aidlink = fusion_get_aidlink();
    $admin = Admins::getInstance();
    $news_admin_link = INFUSIONS.'news/news_admin.php'.$aidlink;

    // Admin Icons
    $admin->setAdminPageIcons("N", "layers");

    // Admin pages
    $admin->addAdminPage('N', 'Listing', 'N', $news_admin_link, 'fas fa-newspaper');
    $admin->addAdminPage('N', 'Add News', 'N__1', $news_admin_link);
    $admin->addAdminPage('N', 'Categories', 'N__2', $news_admin_link);
    $admin->addAdminPage('N', 'Series', 'N__3', $news_admin_link);
    $admin->addAdminPage('N', 'Tags', 'N__4', $news_admin_link);
    $admin->addAdminPage('N', 'Galleries', 'N__5', $news_admin_link);
    $admin->addAdminPage('N', 'Settings', 'N__6', $news_admin_link);

    // for dashboard registration
    $admin->setCommentType('N', fusion_get_locale('N', LOCALE.LOCALESET."admin/main.php"));
    $admin->setLinkType('N', fusion_get_settings("siteurl")."infusions/news/news.php?readmore=%s");
}

fusion_add_hook('admin_pages', 'news_admin_prop');


$inf_settings = get_settings('news');
if (!empty($inf_settings['news_allow_submission']) && $inf_settings['news_allow_submission']) {
    Admins::getInstance()->setSubmitData('n', [
        'infusion_name' => 'news',
        'link'          => INFUSIONS."news/news_submit.php",
        'submit_link'   => "submit.php?stype=n",
        'submit_locale' => fusion_get_locale('N', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('news_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
    ]);
}

Admins::getInstance()->setFolderPermissions('news', [
    'infusions/news/images/'        => TRUE,
    'infusions/news/images/thumbs/' => TRUE
]);
