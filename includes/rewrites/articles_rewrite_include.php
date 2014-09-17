<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles_rewrite_include.php
| Author: Ankur Thakur
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
$regex = array("%article_id%" => "([0-9]+)", "%comment_id%" => "([0-9]+)", "%article_title%" => "([a-zA-Z0-9-_]+)");
// only accept &
$pattern = array("articles"                                                                              => "articles.php", "articles/%article_id%/article/%article_title%" => "articles.php?article_id=%article_id%", "articles/%article_id%/article/%article_title%#comments" => "articles.php?article_id=%article_id%#comments", // not sure why not working.
                 "articles/%article_id%/article/%article_title%/edit-comments/%comment_id%#edit_comment" => "articles.php?article_id=%article_id%&amp;c_action=edit&amp;comment_id=%comment_id%#edit_comment", "articles/%article_id%/article/%article_title%/delete-comments/%comment_id%" => "articles.php?article_id=%article_id%&amp;c_action=delete&amp;comment_id=%comment_id%",);
$dbname  = DB_ARTICLES;
$dbid    = array("%article_id%" => "article_id");
$dbinfo  = array("%article_title%" => "article_subject");
//http://192.168.68.200/dev7/articles.php?article_id=8#comments
//http://192.168.68.200/dev7/articles.php?article_id=8&c_action=edit&comment_id=9#edit_comment
//http://192.168.68.200/dev7/articles.php?article_id=8&c_action=delete&comment_id=9
?>