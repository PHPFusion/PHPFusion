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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$regex = array(
    "%article_id%" => "([0-9]+)",
    "%c_start%" => "([0-9]+)",
    "%rowstart%" => "([0-9]+)",
    "%article_title%" => "([0-9a-zA-Z._\W]+)",
    "%article_cat_id%" => "([0-9]+)",
    "%article_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%type%" => "(A)",
    "%stype%" => "(a)",
);

$pattern = array(
    "submit/%stype%/articles" => "submit.php?stype=%stype%",
    "submit/%stype%/articles/submitted-and-thank-you" => "submit.php?stype=%stype%&amp;submitted=A",
    "articles/comments-%c_start%/%article_id%/%article_title%" => "infusions/articles/articles.php?article_id=%article_id%&amp;c_start=%c_start%",
    "articles/%article_id%/%article_title%" => "infusions/articles/articles.php?article_id=%article_id%",
    "articles/%article_id%-%rowstart%/%article_title%" => "infusions/articles/articles.php?article_id=%article_id%&amp;rowstart=%rowstart%",
    "articles/category/%article_cat_id%/%article_cat_name%" => "infusions/articles/articles.php?cat_id=%article_cat_id%",
    "print/%type%/%article_id%/%article_title%" => "print.php?type=%type%&amp;item_id=%article_id%",
    "articles" => "infusions/articles/articles.php",
);

$pattern_tables["%article_id%"] = array(
    "table" => DB_ARTICLES,
    "primary_key" => "article_id",
    "id" => array("%article_id%" => "article_id"),
    "columns" => array("%article_title%" => "article_subject",)
);

$pattern_tables["%article_cat_id%"] = array(
    "table" => DB_ARTICLE_CATS,
    "primary_key" => "article_cat_id",
    "id" => array("%article_cat_id%" => "article_cat_id"),
    "columns" => array("%article_cat_name%" => "article_cat_name",)
);
