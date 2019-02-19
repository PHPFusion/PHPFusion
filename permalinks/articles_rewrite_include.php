<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: articles_rewrite_include.php
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
    "%article_id%"       => "([0-9]+)",
    "%c_start%"          => "([0-9]+)",
    "%comment_id%"       => "([0-9]+)",
    "%comment_cat%"      => "([0-9]+)",
    "%rowstart%"         => "([0-9]+)",
    "%article_cat_id%"   => "([0-9]+)",
    "%article_title%"    => "([0-9a-zA-Z._\W]+)",
    "%article_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%filter_type%"      => "([0-9a-zA-Z]+)",
    "%type%"             => "(A)",
    "%stype%"            => "(a)",
    "%hash_stop%"        => "\#(?=\s*|)"
];

$pattern = [
    "print/%type%/%article_id%/%article_title%"                                        => "print.php?type=%type%&amp;item_id=%article_id%",
    "submit-%stype%/articles"                                                          => "submit.php?stype=%stype%",
    "submit-%stype%/articles/submitted-and-thank-you"                                  => "submit.php?stype=%stype%&amp;submitted=a",
    "articles/filter/%filter_type%"                                                    => "infusions/articles/articles.php?type=%filter_type%",
    "articles/filter/%filter_type%/rowstart/%rowstart%"                                => "infusions/articles/articles.php?type=%filter_type%&amp;rowstart=%rowstart%",
    "articles/filter/%filter_type%/category/%article_cat_id%"                          => "infusions/articles/articles.php?cat_id=%article_cat_id%&amp;type=%filter_type%",
    "articles/comments-reply-%comment_cat%/%article_id%/%article_title%"               => "infusions/articles/articles.php?article_id=%article_id%&amp;comment_reply=%comment_cat%",
    "articles/comments-reply-%comment_cat%/%article_id%/%article_title%#c%comment_id%" => "infusions/articles/articles.php?article_id=%article_id%&amp;comment_reply=%comment_cat%#c%comment_id%",
    "articles/comments-%c_start%/%article_id%/%article_title%"                         => "infusions/articles/articles.php?article_id=%article_id%&amp;c_start=%c_start%",
    "articles/comments-%c_start%/%article_id%/%article_title%#%comment_id%"            => "infusions/articles/articles.php?article_id=%article_id%&amp;c_start=%c_start%%hash_stop%#%comment_id%",
    "articles/%article_id%/%article_title%"                                            => "infusions/articles/articles.php?article_id=%article_id%",
    "articles/%article_id%-%rowstart%/%article_title%"                                 => "infusions/articles/articles.php?article_id=%article_id%&amp;rowstart=%rowstart%",
    "articles/category/%article_cat_id%/%article_cat_name%"                            => "infusions/articles/articles.php?cat_id=%article_cat_id%",
    "articles/category/%article_cat_id%/%article_id%/%article_title%"                  => "infusions/articles/articles.php?cat_id=%article_cat_id%&amp;article_id=%article_id%",
    "articles/rowstart/%rowstart%"                                                     => "infusions/articles/articles.php?rowstart=%rowstart%",
    "articles"                                                                         => "infusions/articles/articles.php"
];

$pattern_tables["%article_id%"] = [
    "table"       => DB_ARTICLES,
    "primary_key" => "article_id",
    "id"          => ["%article_id%" => "article_id"],
    "columns"     => ["%article_title%" => "article_subject"]
];

$pattern_tables["%article_cat_id%"] = [
    "table"       => DB_ARTICLE_CATS,
    "primary_key" => "article_cat_id",
    "id"          => ["%article_cat_id%" => "article_cat_id"],
    "columns"     => ["%article_cat_name%" => "article_cat_name"]
];
