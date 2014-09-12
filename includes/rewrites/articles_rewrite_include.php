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
if (!defined("IN_FUSION")) { die("Access Denied"); }

$regex = array(
	"%article_id%" => "([0-9]+)",
	"%article_title%" => "([a-zA-Z0-9-_]+)"
);
    // only accept &
$pattern = array(
    "articles" => "articles.php",
	"%article_id%/article/%article_title%" => "articles.php?article_id=%article_id%",
);
$dbname = DB_ARTICLES;
$dbid = array("%article_id%" => "article_id");
$dbinfo = array(
	"%article_title%" => "article_subject"
);

?>