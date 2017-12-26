<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/classes/autoloader.php
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
require_once INCLUDES."infusions_include.php";

spl_autoload_register(function ($className) {
    $autoload_register_paths = [
        "PHPFusion\\News\\NewsServer"           => NEWS_CLASS."server.php",
        "PHPFusion\\News\\NewsView"             => NEWS_CLASS."news/news_view.php",
        "PHPFusion\\News\\News_Preview"         => NEWS_CLASS."admin/controllers/news_preview.php",
        "PHPFusion\\News\\News"                 => NEWS_CLASS."news/news.php",
        "PHPFusion\\News\\NewsAdminView"        => NEWS_CLASS."admin/news_admin_view.php",
        "PHPFusion\\News\\NewsAdminModel"       => NEWS_CLASS."admin/news_admin_model.php",
        "PHPFusion\\News\\NewsCategoryAdmin"    => NEWS_CLASS."admin/controllers/news_cat.php",
        "PHPFusion\\News\\NewsSettingsAdmin"    => NEWS_CLASS."admin/controllers/news_settings.php",
        "PHPFusion\\News\\NewsSubmissionsAdmin" => NEWS_CLASS."admin/controllers/news_submissions.php",
        "PHPFusion\\News\\NewsAdmin"            => NEWS_CLASS."admin/controllers/news.php",
        "PHPFusion\\OpenGraphNews"              => NEWS_CLASS."news/OpenGraphNews.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
