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
if (!defined("IN_FUSION")) { die("Access Denied"); }

$regex = array(
	"%thread_title%" => "([a-zA-Z0-9-]+)",
	"%thread_id%" => "([0-9]+)",
	"%post_id%" => "([0-9]+)",
	"%thread_rowstart%" => "([0-9]+)"
);
$pattern = array(
	"forum/thread/%thread_id%/rowstart/%thread_rowstart%/%thread_title%" => "forum/viewthread.php?thread_id=%thread_id%&amp;rowstart=%thread_rowstart%",
	"forum/thread/%thread_id%/post/%post_id%/%thread_title%#post_%post_id%" => "forum/viewthread.php?thread_id=%thread_id%&amp;pid=%post_id%#post_%post_id%",
	"forum/thread/%thread_id%/post/%post_id%/%thread_title%" => "forum/viewthread.php?thread_id=%thread_id%&amp;pid=%post_id%",
	"forum/thread/%thread_id%/%thread_title%" => "forum/viewthread.php?thread_id=%thread_id%"
);
$alias_pattern = array(
	"thread/%alias%" => "forum/%alias_target%",
	"thread/%alias%/rowstart/%thread_rowstart%" => "forum/%alias_target%&amp;rowstart=%thread_rowstart%",
	"thread/%alias%/post/%post_id%/rowstart/%thread_rowstart%" => "forum/%alias_target%&amp;pid=%post_id%&amp;rowstart=%thread_rowstart%",
	"thread/%alias%/post/%post_id%#post_%post_id%" => "forum/%alias_target%&amp;pid=%post_id%#post_%post_id%",
	"thread/%alias%/post/%post_id%" => "forum/%alias_target%&amp;pid=%post_id%"
);
$dbname = DB_THREADS;
$dbid = array("%thread_id%" => "thread_id");
$dbinfo = array(
	"%thread_title%" => "thread_subject"
);

?>