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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$regex = [
    "%blog_id%"       => "([1-9]{1}[0-9]*)",
    "%blog_title%"    => "([0-9a-zA-Z._\W]+)",
    "%blog_step%"     => "([0-9]+)",
    "%blog_rowstart%" => "([0-9]+)",
    "%comment_id%"    => "([0-9]+)",
    "%comment_cat%"   => "([0-9]+)",
    "%c_start%"       => "([0-9]+)",
    "%stype%"         => "b",
    "%cat_id%"        => "([1-9]{1}[0-9]*)",
    "%blog_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%stype%"         => "(b)",
    "%filter_type%"   => "([0-9a-zA-Z]+)",
    "%hash_stop%"     => "\#(?=\s*|)",
];

$pattern = [
    "print/%stype%/%blog_id%/%blog_title%"                        => "print.php?type=%stype%&amp;item_id=%blog_id%",
    "submit/%stype%/blog"                                         => "submit.php?stype=%stype%",
    "submit/%stype%/blog/submitted-and-thank-you"                 => "submit.php?stype=%stype%&amp;submitted=b",
    "blog/%blog_id%/%blog_title%"                                 => "blog.php?readmore=%blog_id%",
    "blog/%blog_id%/%blog_title%#comments"                        => "blog.php?readmore=%blog_id%#comments",
    "blog/comments-%c_start%/%blog_id%/%blog_title%"              => "blog.php?readmore=%blog_id%&amp;c_start=%c_start%",
    "blog/comments-%c_start%/%blog_id%/%blog_title%#%comment_id%" => "blog.php?readmore=%blog_id%&amp;c_start=%c_start%%hash_stop%#%comment_id%",
    "blog/filter/%filter_type%"                                   => "blog.php?type=%filter_type%",
    "blog/category/uncategorized"                                 => "blog_cats.php?cat_id=0",
    "blog/category/filter/uncategorized"                          => "blog_cats.php?cat_id=0&amp;filter=false",
    "blog/category/%cat_id%/%blog_cat_name%"                      => "blog_cats.php?cat_id=%cat_id%",
    "blog/categories"                                             => "blog_cats.php",
    "blog"                                                        => "blog.php",
];

$alias_pattern = [
    "blog/%alias%"                             => "%alias_target%",
    "blog/%alias%#comments"                    => "%alias_target%%hash_stop%#comments",
    "blog/%alias%/%blog_step%/%blog_rowstart%" => "%alias_target%&amp;step=%blog_step%&amp;rowstart=%blog_rowstart%",
    "blog/%alias%/%blog_step%"                 => "%alias_target%&amp;step=%blog_step%",
];

$pattern_tables["%blog_id%"] = [
    "table"       => DB_BLOG,
    "primary_key" => "blog_id",
    "id"          => ["%blog_id%" => "blog_id"],
    "columns"     => [
        "%blog_title%" => "blog_subject",
        "%blog_start%" => "blog_start",
    ]
];

$pattern_tables["%cat_id%"] = [
    "table"       => DB_BLOG_CATS,
    "primary_key" => "blog_cat_id",
    "id"          => ["%cat_id%" => "cat_id"],
    "columns"     => [
        "%blog_cat_name%" => "blog_cat_name"
    ]
];
