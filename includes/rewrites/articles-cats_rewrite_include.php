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

$regex = array("%article_cat_id%" => "([0-9]+)", "%article_cat_title%" => "([0-9a-zA-Z._\W]+)");
$pattern = array("article-category/%article_cat_id%/%article_cat_title%" => "articles.php?cat_id=%article_cat_id%");

$dir_path = ROOT;
$dbname = DB_ARTICLE_CATS;
$dbid = array("%article_cat_id%" => "article_cat_id");
$dbinfo = array("%article_cat_title%" => "article_cat_name");
?>