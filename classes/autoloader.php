<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/classes/autoloader.php
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
        "PHPFusion\\Articles\\ArticlesServer"           => ARTICLE_CLASS."server.php",
        "PHPFusion\\Articles\\ArticlesView"             => ARTICLE_CLASS."articles/articles_view.php",
        "PHPFusion\\Articles\\Articles"                 => ARTICLE_CLASS."articles/articles.php",
        "PHPFusion\\Articles\\ArticlesAdminView"        => ARTICLE_CLASS."admin/articles_admin_view.php",
        "PHPFusion\\Articles\\ArticlesAdminModel"       => ARTICLE_CLASS."admin/articles_admin_model.php",
        "PHPFusion\\Articles\\ArticlesCategoryAdmin"    => ARTICLE_CLASS."admin/controllers/articles_cat.php",
        "PHPFusion\\Articles\\ArticlesSettingsAdmin"    => ARTICLE_CLASS."admin/controllers/articles_settings.php",
        "PHPFusion\\Articles\\ArticlesSubmissionsAdmin" => ARTICLE_CLASS."admin/controllers/articles_submissions.php",
        "PHPFusion\\Articles\\ArticlesAdmin"            => ARTICLE_CLASS."admin/controllers/articles.php",
        "PHPFusion\\OpenGraphArticles"                  => ARTICLE_CLASS."articles/OpenGraphArticles.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
