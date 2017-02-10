<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: news_rewrite_include.php
| Author: Chan (Frederick MC Chan)
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

$regex = array(
    "%news_id%" => "([0-9]+)",
    "%news_title%" => "([0-9a-zA-Z._\W]+)",
    "%news_step%" => "([0-9]+)",
    "%news_rowstart%" => "([0-9]+)",
    "%c_start%" => "([0-9]+)",
    "%type%" => "(B)",
    "%news_cat_id%" => "([0-9]+)",
    "%news_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%stype%" => "(n)",
    "%filter_type%" => "([0-9a-zA-Z]+)",
    "%hash_stop%" => "\#(?=\s*|)",
);

$pattern = array(
    "submit/%stype%/news" => "submit.php?stype=%stype%",
    "submit/%stype%/news/submitted-and-thank-you" => "submit.php?stype=%stype%&amp;submitted=n",
    "news/%news_id%/%news_title%" => "infusions/news/news.php?readmore=%news_id%",
    "news/comments-%c_start%/%news_id%/%news_title%" => "infusions/news/news.php?readmore=%news_id%&amp;c_start=%c_start%",
    "news/comments-%c_start%/%news_id%/%news_title%#%comment_id%" => "infusions/news/news.php?readmore=%news_id%&amp;c_start=%c_start%%hash_stop%#%comment_id%",
    "print/%type%/%news_id%/%news_title%" => "print.php?type=%type%&amp;item_id=%news_id%",
    "news/filter/%filter_type%" => "infusions/news/news.php?type=%filter_type%",
    "news/category/uncategorized" => "infusions/news/news.php?cat_id=0&amp;filter=false",
    "news/category/%news_cat_id%/%news_cat_name%" => "infusions/news/news.php?cat_id=%news_cat_id%",
    "news" => "infusions/news/news.php",
);

$alias_pattern = array(
    "news/%alias%" => "%alias_target%",
    "news/%alias%#comments" => "%alias_target%%hash_stop%#comments",
    "news/%alias%/%news_step%/%news_rowstart%" => "%alias_target%&amp;step=%news_step%&amp;rowstart=%news_rowstart%",
    "news/%alias%/%news_step%" => "%alias_target%&amp;step=%news_step%",
);

$pattern_tables["%news_id%"] = array(
    "table" => DB_NEWS,
    "primary_key" => "news_id",
    "id" => array("%news_id%" => "news_id"),
    "columns" => array(
        "%news_title%" => "news_subject",
        "%news_start%" => "news_start",
    )
);

$pattern_tables["%news_cat_id%"] = array(
    "table" => DB_NEWS_CATS,
    "primary_key" => "news_cat_id",
    "id" => array("%news_cat_id%" => "news_cat_id"),
    "columns" => array(
        "%news_cat_name%" => "news_cat_name"
    )
);