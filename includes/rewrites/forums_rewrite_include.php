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

$regex = array("%forum_id%" => "([0-9]+)",
			   "%parent_id%" => "([0-9]+)",
			   "%forum_branch%" => "([0-9]+)",
			   "%forum_name%" => "([0-9a-zA-Z._\W]+)",
			   "%rowstart%" => "([0-9]+)",
			   "%time%" => "([0-9]+)",
			   "%type%" => "([0-9]+)",
			   "%sort%" => "([0-9]+)",
			   "%order%" => "([0-9]+)",
			   "%filter%" => "([0-9]+)",
			   "%action%" => "([a-zA-Z]+)");

$pattern = array("forum" => "forum/index.php",
				 "forum/%forum_id%/view/%forum_name%" => "forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%&amp;forum_branch=%forum_branch%",
				 "forum/%forum_id%/view/%forum_name%" => "forum/index.php?viewforum&amp;forum_id=%forum_id%",
				 "forum/%forum_id%/browse/%rowstart%/%forum_name%" => "forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;rowstart=%rowstart%",
				 "forum/%forum_id%/view/%forum_name%" => "forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%&amp;forum_branch=%forum_branch%&amp;rowstart=%rowstart%",
				 "forum/%forum_id%/filter/%time%/%type%/%sort%/%order%/%filter%/%forum_name%" => "forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;time=%time%&amp;type=%type%&amp;sort=%sort%&amp;order=%order%&amp;filter=%filter%", // permalink don't work, but rewrite class worked.
				 "forum/%forum_id%/filter/%time%/%type%/%sort%/%order%/%filter%/%rowstart%/%forum_name%" => "forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;time=%time%&amp;type=%type%&amp;sort=%sort%&amp;order=%order%&amp;filter=%filter%&amp;rowstart=%rowstart%", // permalink don't work, but rewrite class worked.
				 "forum/%forum_id%/post/%forum_name%/%action%" => "forum/post.php?action=%action%&amp;forum_id=%forum_id%"); // create new threads

$dir = FORUM;
$dbid = array("%forum_id%" => "forum_id");
$dbname = DB_FORUMS;
$dbinfo = array("%forum_name%" => "forum_name");
?>