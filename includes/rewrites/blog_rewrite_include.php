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

$regex = array("%blog_id%" => "([0-9]+)",
  			   "%blog_title%" => "([0-9a-zA-Z._\W]+)",
			   "%blog_step%" => "([0-9]+)",
			   "%blog_rowstart%" => "([0-9]+)",
			   "%c_start%" => "([0-9]+)");

$pattern = array("blogs" => "blog.php",
				 "blogs/%blog_id%/%blog_title%" => "blog.php?readmore=%blog_id%",
				 "blogs/%blog_id%/%blog_title%#comments" => "blog.php?readmore=%blog_id%#comments",
				 "blogs/%c_start%/%blog_id%/%blog_title%" => "blog.php?readmore=%blog_id%&amp;c_start=%c_start%");

$alias_pattern = array("blogs/%alias%" => "%alias_target%",
					   "blogs/%alias%#comments" => "%alias_target%#comments",
					   "blogs/%alias%/%blog_step%/%blog_rowstart%" => "%alias_target%&amp;step=%blog_step%&amp;rowstart=%blog_rowstart%",
					   "blogs/%alias%/%blog_step%" => "%alias_target%&amp;step=%blog_step%");

$dbname = DB_BLOG;
$dbid = array("%blog_id%" => "blog_id");
$dbinfo = array("%blog_title%" => "blog_subject", "%blog_start%" => "blog_start");
?>