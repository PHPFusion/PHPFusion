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
defined('IN_FUSION') || exit;

$regex = [
    "%news_id%"       => "([1-9]{1}[0-9]*)",
    "%news_title%"    => "([0-9a-zA-Z._\W]+)",
    "%news_step%"     => "([0-9]+)",
    "%news_rowstart%" => "([0-9]+)",
    "%comment_id%"    => "([0-9]+)",
    "%comment_cat%"   => "([0-9]+)",
    "%c_start%"       => "([0-9]+)",
    "%rowstart%"      => "([0-9]+)",
    "%news_cat_id%"   => "([1-9]{1}[0-9]*)",
    "%news_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%type%"          => "(N)",
    "%stype%"         => "(n)",
    "%filter_type%"   => "([0-9a-zA-Z]+)",
    "%hash_stop%"     => "\#(?=\s*|)"
];

$pattern = [
    "print/%type%/%news_id%/%news_title%"                                    => "print.php?type=%type%&amp;item_id=%news_id%",
    "submit-%stype%/news"                                                    => "submit.php?stype=%stype%",
    "submit-%stype%/news/submitted-and-thank-you"                            => "submit.php?stype=%stype%&amp;submitted=n",
    "news/%news_id%/%news_title%"                                            => "infusions/news/news.php?readmore=%news_id%",
    "news/%news_id%/%news_title%#comments"                                   => "infusions/news/news.php?readmore=%news_id%#comments",
    "news/%news_id%/%news_title%#postrating"                                 => "infusions/news/news.php?readmore=%news_id%#postrating",
    "news/%news_id%-%rowstart%/%news_title%"                                 => "infusions/news/news.php?readmore=%news_id%&amp;rowstart=%rowstart%",
    "news/comments-reply-%comment_cat%/%news_id%/%news_title%"               => "infusions/news/news.php?readmore=%news_id%&amp;comment_reply=%comment_cat%",
    "news/comments-reply-%comment_cat%/%news_id%/%news_title%#c%comment_id%" => "infusions/news/news.php?readmore=%news_id%&amp;comment_reply=%comment_cat%#c%comment_id%",
    "news/comments-%c_start%/%news_id%/%news_title%"                         => "infusions/news/news.php?readmore=%news_id%&amp;c_start=%c_start%",
    "news/comments-%c_start%/%news_id%/%news_title%#%comment_id%"            => "infusions/news/news.php?readmore=%news_id%&amp;c_start=%c_start%%hash_stop%#%comment_id%",
    "news/filter/%filter_type%"                                              => "infusions/news/news.php?type=%filter_type%",
    "news/filter/%filter_type%/rowstart/%rowstart%"                          => "infusions/news/news.php?type=%filter_type%&amp;rowstart=%rowstart%",
    "news/category/uncategorized"                                            => "infusions/news/news.php?cat_id=0",
    "news/category/filter/uncategorized"                                     => "infusions/news/news.php?cat_id=0&amp;filter=false",
    "news/category/%news_cat_id%/%news_cat_name%"                            => "infusions/news/news.php?cat_id=%news_cat_id%",
    "news/category/%news_cat_id%/filter/%filter_type%"                       => "infusions/news/news.php?cat_id=%news_cat_id%&amp;type=%filter_type%",
    "news/rowstart/%rowstart%"                                               => "infusions/news/news.php?rowstart=%rowstart%",
    "news"                                                                   => "infusions/news/news.php"
];

$alias_pattern = [
    "news/%alias%"                             => "%alias_target%",
    "news/%alias%#comments"                    => "%alias_target%%hash_stop%#comments",
    "news/%alias%/%news_step%/%news_rowstart%" => "%alias_target%&amp;step=%news_step%&amp;rowstart=%news_rowstart%",
    "news/%alias%/%news_step%"                 => "%alias_target%&amp;step=%news_step%"
];

$pattern_tables["%news_id%"] = [
    "table"       => DB_NEWS,
    "primary_key" => "news_id",
    "id"          => ["%news_id%" => "news_id"],
    "columns"     => [
        "%news_title%" => "news_subject",
        "%news_start%" => "news_start"
    ]
];

$pattern_tables["%news_cat_id%"] = [
    "table"       => DB_NEWS_CATS,
    "primary_key" => "news_cat_id",
    "id"          => ["%news_cat_id%" => "news_cat_id"],
    "columns"     => [
        "%news_cat_name%" => "news_cat_name"
    ]
];
