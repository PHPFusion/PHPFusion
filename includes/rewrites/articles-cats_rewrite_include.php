<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) 2002 - 2011 Nick Jones
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: articles_rewrite_include.php
    | Author: Ankur Thakur
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

    $regex = array("%article_cat_id%" => "([0-9]+)", "%article_cat_title%" => "([a-zA-Z0-9-_]+)");
    $pattern = array(//"articles" => "articles.php",
                     "%article_cat_id%/articles/%article_cat_title%" => "articles.php?cat_id=%article_cat_id%");

    /* $alias_pattern = array(
        "articles/%alias%" => "%alias_target%",
        "articles/%alias%#comments" => "%alias_target%#comments",
        "articles/%alias%/%news_step%/%news_rowstart%" => "%alias_target%&amp;step=%news_step%&amp;rowstart=%news_rowstart%",
        "articles/%alias%/%news_step%" => "%alias_target%&amp;step=%news_step%"
    ); */

    $dir_path = ROOT;
    $dbname = DB_ARTICLE_CATS;
    $dbid = array("%article_cat_id%" => "article_cat_id");
    $dbinfo = array("%article_cat_title%" => "article_cat_name");
    // http://192.168.68.200/dev7/articles.php?cat_id=3
?>
