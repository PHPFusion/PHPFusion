<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/autoloader.php
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

spl_autoload_register(function ($className) {

    $autoload_register_paths = array(
        "PHPFusion\\News\\NewsServer"  => NEWS_CLASS."/server.php",
        "PHPFusion\\News\\NewsView"  => NEWS_CLASS."/news/news_view.php",
        "PHPFusion\\News\\News"  => NEWS_CLASS."/news/news.php"
    );

    $fullPath = $autoload_register_paths[$className];

    if (is_file($fullPath)) {
        require $fullPath;
    }

});