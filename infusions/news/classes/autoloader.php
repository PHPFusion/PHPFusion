<?php


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