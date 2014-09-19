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
	"%post_message%" => "([a-zA-Z0-9-]+)",
	"%post_id%" => "([0-9]+)",
	"%quote_id%" => "([0-9]+)",
	"%forum_id%" => "([0-9]+)",
	"%thread_id%" => "([0-9]+)",
	"%action%" => "([a-zA-Z]+)",
);

$pattern = array(
	"forum/%action%/%thread_id%/%forum_id%/%post_id%" => "forum/post.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%", // edit
	"forum/%action%/%thread_id%/%forum_id%/%post_id%/%quote_id%" => "forum/post.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%&amp;quote=%quote_id%", // quote
	"forum/viewpost/%thread_id%/%post_id%#post_%post_id%" => "forum/viewthread.php?thread_id=%thread_id%&amp;pid=%post_id%#post_%post_id%"
);

// quote is not working

$dir = FORUM;
$dbname = DB_POSTS;
$dbid = array("%post_id%" => "post_id");
$dbinfo = array("%post_message%" => "post_message");

?>
