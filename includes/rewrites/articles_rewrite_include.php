<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules for 7.03
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

$regex = array("%article_id%" => "([0-9]+)", "%comment_id%" => "([0-9]+)", "%article_title%" => "([0-9a-zA-Z._\W]+)");

// only accept &
$pattern = array("articles" => 
				"articles.php",
				"articles/%article_id%/article/%article_title%" => "articles.php?article_id=%article_id%",
				"articles/%article_id%/article/%article_title%#comments" => "articles.php?article_id=%article_id%#comments",
				"articles/%article_id%/article/%article_title%/edit-comments/%comment_id%#edit_comment" => "articles.php?article_id=%article_id%&amp;c_action=edit&amp;comment_id=%comment_id%#edit_comment",
				"articles/%article_id%/article/%article_title%/delete-comments/%comment_id%" => "articles.php?article_id=%article_id%&amp;c_action=delete&amp;comment_id=%comment_id%",);
$dbname = DB_ARTICLES;
$dbid = array("%article_id%" => "article_id");
$dbinfo = array("%article_title%" => "article_subject");
?>