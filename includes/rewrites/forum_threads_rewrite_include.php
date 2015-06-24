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

$regex = array(
	"%thread_name%" => "([0-9a-zA-Z._\W]+)",
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
	"thread" => "infusions/forum/viewthread.php",
	"thread/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%",
	"thread/%thread_id%/%post_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%post_id%",
	"thread/%thread_id%/browse/%thread_rowstart%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;rowstart=%thread_rowstart%",
	"thread/%thread_id%/filter/%time%/%type%/%order%/%filter%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;time=%time%&amp;type=%type%&amp;order=%order%&amp;filter=%filter%",
	"thread/%thread_id%/filter/%time%/%type%/%order%/%filter%/%thread_rowstart%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;time=%time%&amp;type=%type%&amp;order=%order%&amp;filter=%filter%&amp;rowstart=%thread_rowstart%",
);

$dir = FORUM;
$dbname = DB_FORUM_THREADS;
$dbid = array("%thread_id%" => "thread_id");
$dbinfo = array("%thread_name%" => "thread_subject");
