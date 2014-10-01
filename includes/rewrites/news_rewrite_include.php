<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
$regex = array(
	"%news_id%" => "([0-9]+)",
   	"%news_title%" => "([a-zA-Z0-9-]+)",
 	"%news_step%" => "([0-9]+)",
 	"%news_rowstart%" => "([0-9]+)",
	"%c_start%" => "([0-9]+)",
	);

$pattern = array(
	"news" => "news.php",
	"news/%news_id%/%news_title%" => "news.php?readmore=%news_id%",
	"news/%news_id%/%news_title%#comments" => "news.php?readmore=%news_id%#comments",
	"news/%c_start%/%news_id%/%news_title%" => "news.php?readmore=%news_id%&amp;c_start=%c_start%"
	);

$alias_pattern = array("news/%alias%" => "%alias_target%",
					   "news/%alias%#comments" => "%alias_target%#comments",
					   "news/%alias%/%news_step%/%news_rowstart%" => "%alias_target%&amp;step=%news_step%&amp;rowstart=%news_rowstart%",
					   "news/%alias%/%news_step%" => "%alias_target%&amp;step=%news_step%
					   ");
$dbname = DB_NEWS;
$dbid = array("%news_id%" => "news_id");
$dbinfo = array("%news_title%" => "news_subject", "%news_start%" => "news_start");

?>