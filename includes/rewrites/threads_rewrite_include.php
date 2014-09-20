<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: threads_rewrite_include.php
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
$regex = array(
	"%thread_name%" => "([a-zA-Z0-9-]+)",
   	"%thread_id%" => "([0-9]+)",
   	"%thread_rowstart%" => "([0-9]+)",
 	"%time%" => "([0-9]+)",
 	"%type%" => "([0-9]+)",
 	"%order%" => "([0-9]+)",
 	"%filter%" => "([0-9]+)",
 	"%post_id%" => "([0-9]+)",
 	"%quote_id%" => "([0-9]+)",
 	"%forum_id%" => "([0-9]+)",
 	"%action%" => "([a-zA-Z]+)",
   	);

$pattern = array(
	"thread" => "forum/viewthread.php",
	"thread/%thread_id%/%thread_name%" => "forum/viewthread.php?thread_id=%thread_id%",
	"thread/%thread_id%/%post_id%/%thread_name%" => "forum/viewthread.php?thread_id=%thread_id%&amp;pid=%post_id%",
	"thread/%thread_id%/browse/%thread_rowstart%/%thread_name%" => "forum/viewthread.php?thread_id=%thread_id%&amp;rowstart=%thread_rowstart%",
	"thread/%thread_id%/filter/%time%/%type%/%order%/%filter%/%thread_name%" => "forum/viewthread.php?thread_id=%thread_id%&amp;time=%time%&amp;type=%type%&amp;order=%order%&amp;filter=%filter%",
	"thread/%thread_id%/filter/%time%/%type%/%order%/%filter%/%thread_rowstart%/%thread_name%" => "forum/viewthread.php?thread_id=%thread_id%&amp;time=%time%&amp;type=%type%&amp;order=%order%&amp;filter=%filter%&amp;rowstart=%thread_rowstart%",
);

/* $alias_pattern = array("thread/%alias%" => "forum/%alias_target%",
					   "thread/%alias%/rowstart/%thread_rowstart%" => "forum/%alias_target%&amp;rowstart=%thread_rowstart%",
					   "thread/%alias%/post/%post_id%/rowstart/%thread_rowstart%" => "forum/%alias_target%&amp;pid=%post_id%&amp;rowstart=%thread_rowstart%",
					   "thread/%alias%/post/%post_id%#post_%post_id%" => "forum/%alias_target%&amp;pid=%post_id%#post_%post_id%",
					   "thread/%alias%/post/%post_id%" => "forum/%alias_target%&amp;pid=%post_id%"); */
$dir = FORUM;
$dbname = DB_THREADS;
$dbid = array("%thread_id%" => "thread_id");
$dbinfo = array("%thread_name%" => "thread_subject");

?>