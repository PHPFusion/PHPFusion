<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forums_rewrite_include.php
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
	"%forum_id%" => "([0-9]+)",
	"%forum_title%" => "([a-zA-Z0-9-]+)"
);
$pattern = array(
	"forum" => "forum/index.php",
	"forum/" => "forum/index.php",
	"forum/%forum_id%/%forum_title%" => "forum/viewforum.php?forum_id=%forum_id%"
);
$dbid = array("%forum_id%" => "forum_id");
$dbname = DB_FORUMS;
$dbinfo = array(
	"%forum_title%" => "forum_name"
);

?>