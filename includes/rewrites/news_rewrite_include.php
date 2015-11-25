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
	"%news_id%" => "([0-9]+)",
	"%news_title%" => "([0-9a-zA-Z._\W]+)",
	"%news_step%" => "([0-9]+)",
	"%news_rowstart%" => "([0-9]+)",
	"%c_start%" => "([0-9]+)",
    "%type%" => "(B)",
    "%news_cat_id%" => "([0-9]+)",
    "%news_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%s_type%" => "(n)"
);

$pattern = array(
	"news" => "infusions/news/news.php",
	"news/%news_id%/%news_title%" => "infusions/news/news.php?readmore=%news_id%",
	"news/%news_id%/%news_title%#comments" => "infusions/news/news.php?readmore=%news_id%#comments",
    "news/%news_id%/%news_title%#ratings"                          => "infusions/news/news.php?readmore=%news_id%#ratings",
	"news/%c_start%/%news_id%/%news_title%" => "infusions/news/news.php?readmore=%news_id%&amp;c_start=%c_start%",
    "print/%type%/%news_id%/%news_title%"                          => "print.php?type=%type%&amp;item_id=%news_id%",
    "news/most-recent"                                             => "infusions/news/news.php?type=recent",
    "news/most-commented"                                          => "infusions/news/news.php?type=comment",
    "news/most-rated"                                              => "infusions/news/news.php?type=rating",
    fusion_get_settings("site_path")."news/%news_id%/%news_title%" => "../../infusions/news/news.php?readmore=%news_id%",
    "news/category/uncategorized" => "infusions/news/news.php?cat_id=0&amp;filter=false",
    "news/category/%news_cat_id%/%news_cat_name%" => "infusions/news/news.php?cat_id=%news_cat_id%",
    "submit/news" => "submit.php?stype=%s_type%"
);

$alias_pattern = array(
	"news/%alias%" => "%alias_target%",
	"news/%alias%#comments" => "%alias_target%#comments",
	"news/%alias%/%news_step%/%news_rowstart%" => "%alias_target%&amp;step=%news_step%&amp;rowstart=%news_rowstart%",
	"news/%alias%/%news_step%" => "%alias_target%&amp;step=%news_step%",
);

$pattern_tables["%news_id%"] = array(
    "table" => DB_NEWS,
    "primary_key" => "news_id",
    "id" => array("%news_id%" => "news_id"),
    "columns" => array(
        "%blog_title%" => "news_subject",
        "%blog_start%" => "news_start",
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