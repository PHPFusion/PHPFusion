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

$regex = array(
    "%blog_title%" => "([0-9a-zA-Z._\W]+)",
    "%blog_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%blog_id%" => "([0-9]+)",
    "%comment_id%" => "([0-9]+)",
    "%blog_step%" => "([0-9]+)",
    "%blog_rowstart%" => "([0-9]+)",
    "%c_start%" => "([0-9]+)",
    "%blog_year%" => "([0-9]+)",
    "%blog_month%" => "([0-9]+)",
    "%author%" => "([0-9]+)",
    "%type%" => "(B)",
    "%blog_cat_id%" => "([0-9]+)",
    "%hash_stop%" => "\#(?=\s*|)",
    "%filter_type%" => "([0-9a-zA-Z]+)",
    "%stype%" => "(b)",
);

$pattern = array(

    "submit/%stype%/blogs" => "submit.php?stype=%stype%",
    "submit/%stype%/blogs/submitted-and-thank-you" => "submit.php?stype=%stype%&amp;submitted=b",
    "blogs/%blog_id%/%blog_title%" => "infusions/blog/blog.php?readmore=%blog_id%",
    "blogs/%blog_id%/%blog_title%#comments" => "infusions/blog/blog.php?readmore=%blog_id%%hash_stop%#comments",
    "blogs/%blog_id%/%blog_title%#ratings" => "infusions/blog/blog.php?readmore=%blog_id%%hash_stop%#ratings",
    "blogs/comments-%c_start%/%blog_id%/%blog_title%" => "infusions/blog/blog.php?readmore=%blog_id%&amp;c_start=%c_start%",
    "blogs/comments-%c_start%/%blog_id%/%blog_title%#%comment_id%" => "infusions/blog/blog.php?readmore=%blog_id%&amp;c_start=%c_start%%hash_stop%#%comment_id%",
    "print/%type%/%blog_id%/%blog_title%" => "print.php?type=%type%&amp;item_id=%blog_id%",
    "blogs/filter/%filter_type%" => "infusions/blog/blog.php?type=%filter_type%",
    "blogs/filter/uncategorized/%filter_type%" => "infusions/blog/blog.php?cat_id=0&amp;type=%filter_type%",
    "blogs/filter/category-%blog_cat_id%/%filter_type%" => "infusions/blog/blog.php?cat_id=%blog_cat_id%&amp;type=%filter_type%",
    "blogs/filter/author-%author%/%filter_type%" => "infusions/blog/blog.php?author=%author%&amp;type=%filter_type%",
    "blogs/filter/archive-%blog_year%-%blog_month%/%filter_type%" => "infusions/blog/blog.php?archive=%blog_year%&amp;month=%blog_month%&amp;type=%filter_type%",
    "blogs/archive/%blog_year%/%blog_month%" => "infusions/blog/blog.php?archive=%blog_year%&amp;month=%blog_month%",
    "blogs/author/%author%" => "infusions/blog/blog.php?author=%author%",
    "blogs/category/uncategorized" => "infusions/blog/blog.php?cat_id=0&amp;filter=false",
    "blogs/category/%blog_cat_id%/%blog_cat_name%" => "infusions/blog/blog.php?cat_id=%blog_cat_id%",
    "blogs" => "infusions/blog/blog.php",
);

$alias_pattern = array(
    "blogs" => "infusions/blog/blog.php",
    "blogs/%alias%" => "%alias_target%",
    "blogs/%alias%#comments" => "%alias_target%%hash_stop%#comments",
    "blogs/%alias%/%blog_step%/%blog_rowstart%" => "%alias_target%&amp;step=%blog_step%&amp;rowstart=%blog_rowstart%",
    "blogs/%alias%/%blog_step%" => "%alias_target%&amp;step=%blog_step%",
);

$pattern_tables["%blog_id%"] = array(
    "table" => DB_BLOG,
    "primary_key" => "blog_id",
    "id" => array("%blog_id%" => "blog_id"),
    "columns" => array(
        "%blog_title%" => "blog_subject",
        "%blog_start%" => "blog_start",
    )
);

$pattern_tables["%blog_cat_id%"] = array(
    "table" => DB_BLOG_CATS,
    "primary_key" => "blog_cat_id",
    "id" => array("%blog_cat_id%" => "blog_cat_id"),
    "columns" => array(
        "%blog_cat_name%" => "blog_cat_name"
    )
);