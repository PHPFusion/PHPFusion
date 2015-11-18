<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

$regex = array(
	"%blog_id%" => "([0-9]+)",
	"%blog_title%" => "([0-9a-zA-Z._\W]+)",
	"%blog_step%" => "([0-9]+)",
	"%blog_rowstart%" => "([0-9]+)",
	"%c_start%" => "([0-9]+)",
	"%blog_year%" => "([0-9]+)",
    "%blog_month%" => "([0-9]+)",
    "%author%" => "([0-9]+)",
    "%type%" => "(B)",
);

$pattern = array(
    "blogs" => "infusions/blog/blog.php",
	"blogs/%blog_id%/%blog_title%" => "infusions/blog/blog.php?readmore=%blog_id%",
	"blogs/%blog_id%/%blog_title%#comments" => "infusions/blog/blog.php?readmore=%blog_id%#comments",
	"blogs/%c_start%/%blog_id%/%blog_title%" => "infusions/blog/blog.php?readmore=%blog_id%&amp;c_start=%c_start%",
    "print/%type%/%blog_id%/%blog_title%" => "print.php?type=%type%&amp;item_id=%blog_id%",
    "blogs/most-recent" => "infusions/blog/blog.php?type=recent",
    "blogs/most-commented" => "infusions/blog/blog.php?type=comment",
    "blogs/most-rated" => "infusions/blog/blog.php?type=rating",
    "blogs/archive/%blog_year%/%blog_month%" => "infusions/blog/blog.php?archive=%blog_year%&amp;month=%blog_month%",
    "blogs/author/%author%" => "infusions/blog/blog.php?author=%author%",
    fusion_get_settings("site_path")."blogs/%blog_id%/%blog_title%/comment-form" => "../../infusions/blog/blog.php?readmore=%blog_id%",
);

// did not install
$alias_pattern = array(
    "blogs" => "infusions/blog/blog.php",
	"blogs/%alias%" => "%alias_target%",
    "blogs/most-recent" => "infusions/blog/blog.php?type=recent",
    "blogs/most-commented" => "infusions/blog/blog.php?type=comment",
    "blogs/most-rated" => "infusions/blog/blog.php?type=rating",
    "blogs/%blog_year%/%blog_month%" => "infusions/blog/blog.php?archive=%blog_year%&amp;month=%blog_month",
    "blogs/%author%" => "infusions/blog/blog.php?author=%author",
	"blogs/%alias%#comments" => "%alias_target%#comments",
	"blogs/%alias%/%blog_step%/%blog_rowstart%" => "%alias_target%&amp;step=%blog_step%&amp;rowstart=%blog_rowstart%",
	"blogs/%alias%/%blog_step%" => "%alias_target%&amp;step=%blog_step%",
);

$dbname = DB_BLOG;
$dbid = array("%blog_id%" => "blog_id");
$dbinfo = array("%blog_title%" => "blog_subject", "%blog_start%" => "blog_start");
