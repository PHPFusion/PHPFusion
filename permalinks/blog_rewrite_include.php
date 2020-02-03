<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: blog_rewrite_include.php
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
    "%blog_title%"    => "([0-9a-zA-Z._\W]+)",
    "%blog_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%blog_id%"       => "([1-9]{1}[0-9]*)",
    "%comment_id%"    => "([0-9]+)",
    "%comment_cat%"   => "([0-9]+)",
    "%blog_step%"     => "([0-9]+)",
    "%rowstart%"      => "([0-9]+)",
    "%c_start%"       => "([0-9]+)",
    "%blog_year%"     => "([0-9]+)",
    "%blog_month%"    => "([0-9]+)",
    "%author%"        => "([0-9]+)",
    "%blog_cat_id%"   => "([1-9]{1}[0-9]*)",
    "%hash_stop%"     => "\#(?=\s*|)",
    "%filter_type%"   => "([0-9a-zA-Z]+)",
    "%type%"          => "(B)",
    "%stype%"         => "(b)"
];

$pattern = [
    "print/%type%/%blog_id%/%blog_title%"                                    => "print.php?type=%type%&amp;item_id=%blog_id%",
    "submit-%stype%/blog"                                                    => "submit.php?stype=%stype%",
    "submit-%stype%/blog/submitted-and-thank-you"                            => "submit.php?stype=%stype%&amp;submitted=b",
    "blog/comments-reply-%comment_cat%/%blog_id%/%blog_title%"               => "infusions/blog/blog.php?readmore=%blog_id%&amp;comment_reply=%comment_cat%",
    "blog/comments-reply-%comment_cat%/%blog_id%/%blog_title%#c%comment_id%" => "infusions/blog/blog.php?readmore=%blog_id%&amp;comment_reply=%comment_cat%#c%comment_id%",
    "blog/%blog_id%/%blog_title%"                                            => "infusions/blog/blog.php?readmore=%blog_id%",
    "blog/%blog_id%/%blog_title%#comments"                                   => "infusions/blog/blog.php?readmore=%blog_id%%hash_stop%#comments",
    "blog/%blog_id%/%blog_title%#ratings"                                    => "infusions/blog/blog.php?readmore=%blog_id%%hash_stop%#ratings",
    "blog/%blog_id%/%blog_title%/category/%blog_cat_id%"                     => "infusions/blog/blog.php?readmore=%blog_id%&amp;cat_id=%blog_cat_id%",
    "blog/%blog_id%-%rowstart%/%blog_title%"                                 => "infusions/blog/blog.php?readmore=%blog_id%&amp;rowstart=%rowstart%",
    "blog/comments-%c_start%/%blog_id%/%blog_title%"                         => "infusions/blog/blog.php?readmore=%blog_id%&amp;c_start=%c_start%",
    "blog/comments-%c_start%/%blog_id%/%blog_title%#%comment_id%"            => "infusions/blog/blog.php?readmore=%blog_id%&amp;c_start=%c_start%%hash_stop%#%comment_id%",
    "blog/filter/%filter_type%"                                              => "infusions/blog/blog.php?type=%filter_type%",
    "blog/filter/%filter_type%/rowstart/%rowstart%"                          => "infusions/blog/blog.php?type=%filter_type%&amp;rowstart=%rowstart%",
    "blog/filter/uncategorized/%filter_type%"                                => "infusions/blog/blog.php?cat_id=0&amp;type=%filter_type%",
    "blog/filter/%filter_type%/category/%blog_cat_id%"                       => "infusions/blog/blog.php?cat_id=%blog_cat_id%&amp;type=%filter_type%",
    "blog/filter/author-%author%/%filter_type%"                              => "infusions/blog/blog.php?author=%author%&amp;type=%filter_type%",
    "blog/filter/archive-%blog_year%-%blog_month%/%filter_type%"             => "infusions/blog/blog.php?archive=%blog_year%&amp;month=%blog_month%&amp;type=%filter_type%",
    "blog/archive/%blog_year%/%blog_month%"                                  => "infusions/blog/blog.php?archive=%blog_year%&amp;month=%blog_month%",
    "blog/author/%author%"                                                   => "infusions/blog/blog.php?author=%author%",
    "blog/category/uncategorized"                                            => "infusions/blog/blog.php?cat_id=0",
    "blog/category/filter/uncategorized"                                     => "infusions/blog/blog.php?cat_id=0&amp;filter=false",
    "blog/category/%blog_cat_id%/%blog_cat_name%"                            => "infusions/blog/blog.php?cat_id=%blog_cat_id%",
    "blog/rowstart/%rowstart%"                                               => "infusions/blog/blog.php?rowstart=%rowstart%",
    "blog"                                                                   => "infusions/blog/blog.php"
];

$alias_pattern = [
    "blog"                                => "infusions/blog/blog.php",
    "blog/%alias%"                        => "%alias_target%",
    "blog/%alias%#comments"               => "%alias_target%%hash_stop%#comments",
    "blog/%alias%/%blog_step%/%rowstart%" => "%alias_target%&amp;step=%blog_step%&amp;rowstart=%rowstart%",
    "blog/%alias%/%blog_step%"            => "%alias_target%&amp;step=%blog_step%"
];

$pattern_tables["%blog_id%"] = [
    "table"       => DB_BLOG,
    "primary_key" => "blog_id",
    "id"          => ["%blog_id%" => "blog_id"],
    "columns"     => [
        "%blog_title%" => "blog_subject",
        "%blog_start%" => "blog_start"
    ]
];

$pattern_tables["%blog_cat_id%"] = [
    "table"       => DB_BLOG_CATS,
    "primary_key" => "blog_cat_id",
    "id"          => ["%blog_cat_id%" => "blog_cat_id"],
    "columns"     => [
        "%blog_cat_name%" => "blog_cat_name"
    ]
];
