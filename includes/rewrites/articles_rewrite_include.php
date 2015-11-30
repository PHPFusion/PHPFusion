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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$regex = array(
    "%article_id%"     => "([0-9]+)",
    "%comment_id%"     => "([0-9]+)",
    "%rowstart%"       => "([0-9]+)",
    "%article_title%"  => "([0-9a-zA-Z._\W]+)",
    "%article_cat_id%" => "([0-9]+)",
    "%article_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%type%"           => "(A)",
    "%s_type%"         => "(a)",
    "%hash_stop%"      => "\#(?=\s*|)",
);

$pattern = array(
    "articles"                                                                                 => "infusions/articles/articles.php",
    "articles/%article_id%/%article_title%"                                                    => "infusions/articles/articles.php?article_id=%article_id%",
    "articles/%article_id%-%rowstart%/%article_title%"                                         => "infusions/articles/articles.php?article_id=%article_id%&amp;rowstart=%rowstart%",
    "articles/%article_id%/%article_title%#comments"                                           => "infusions/articles/articles.php?article_id=%article_id%%hash_stop%#comments",
    "articles/%article_id%-%rowstart%/%article_title%#comments"                                => "infusions/articles/articles.php?article_id=%article_id%&amp;rowstart=%rowstart%%hash_stop%#comments",
    "articles/%article_id%/%article_title%/edit-comments/%comment_id%#edit_comment"            => "infusions/articles/articles.php?article_id=%article_id%&amp;c_action=edit&amp;comment_id=%comment_id%#edit_comment",
    "articles/%article_id%-%rowstart%/%article_title%/edit-comments/%comment_id%#edit_comment" => "infusions/articles/articles.php?article_id=%article_id%&amp;rowstart=%rowstart%&amp;c_action=edit&amp;comment_id=%comment_id%%hash_stop%#edit_comment",
    "articles/%article_id%/%article_title%/delete-comments/%comment_id%"                       => "infusions/articles/articles.php?article_id=%article_id%&amp;c_action=delete&amp;comment_id=%comment_id%",
    "articles/%article_id%-%rowstart%/%article_title%/delete-comments/%comment_id%"            => "infusions/articles/articles.php?article_id=%article_id%&amp;rowstart=%rowstart%&amp;c_action=delete&amp;comment_id=%comment_id%",
    "articles/category/%article_cat_id%/%article_cat_name%"                                    => "infusions/articles/articles.php?cat_id=%article_cat_id%",
    "print/%type%/%article_id%/%article_title%"                                                => "print.php?type=%type%&amp;item_id=%article_id%",
    "submit/articles"                                                                          => "submit.php?stype=%s_type%",
);


$pattern_tables["%article_id%"] = array(
    "table"   => DB_ARTICLES,
    "primary_key" => "article_id",
    "id"      => array("%article_id%" => "article_id"),
    "columns" => array("%article_title%" => "article_subject",)
);

$pattern_tables["%article_cat_id%"] = array(
    "table"   => DB_ARTICLE_CATS,
    "primary_key" => "article_cat_id",
    "id"      => array("%article_cat_id%" => "article_cat_id"),
    "columns" => array("%article_cat_name%" => "article_cat_name",)
);
