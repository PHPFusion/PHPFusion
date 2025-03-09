<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: autoloader.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once INCLUDES."infusions_include.php";

spl_autoload_register(function ($className) {
    $autoload_register_paths = [
        "PHPFusion\\News\\NewsServer"           => NEWS_CLASSES."server.php",
        "PHPFusion\\News\\NewsView"             => NEWS_CLASSES."news/news_view.php",
        "PHPFusion\\News\\News"                 => NEWS_CLASSES."news/news.php",
        "PHPFusion\\News\\NewsAdminView"        => NEWS_CLASSES."admin/news_admin_view.php",
        "PHPFusion\\News\\NewsAdminModel"       => NEWS_CLASSES."admin/news_admin_model.php",
        "PHPFusion\\News\\NewsCategoryAdmin"    => NEWS_CLASSES."admin/controllers/news_cat.php",
        "PHPFusion\\News\\NewsSettingsAdmin"    => NEWS_CLASSES."admin/controllers/news_settings.php",
        "PHPFusion\\News\\NewsSubmissionsAdmin" => NEWS_CLASSES."admin/controllers/news_submissions.php",
        "PHPFusion\\News\\NewsAdmin"            => NEWS_CLASSES."admin/controllers/news.php",
        "PHPFusion\\OpenGraphNews"              => NEWS_CLASSES."news/OpenGraphNews.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
