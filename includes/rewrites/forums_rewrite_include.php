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
	"%forum_id%" => "([0-9]+)",
	"%parent_id%" => "([0-9]+)",
	"%forum_branch%" => "([0-9]+)",
	"%forum_name%" => "([0-9a-zA-Z._\W]+)",
	"%rowstart%" => "([0-9]+)",
	"%time%" => "([0-9]+)",
	"%type%" => "([0-9]+)",
	"%sort%" => "([0-9]+)",
	"%order%" => "([0-9]+)",
	"%filter%" => "([0-9]+)",
	"%action%" => "([a-zA-Z]+)",
    // Threads
    "%thread_name%" => "([0-9a-zA-Z._\W]+)",
    "%thread_id%" => "([0-9]+)",
    "%thread_rowstart%" => "([0-9]+)",
    "%post_id%" => "([0-9]+)",
    "%quote_id%" => "([0-9]+)",
    // Post
    "%post_message%" => "([0-9a-zA-Z._\W]+)",
);

// ID conflict, will check one by one later. Commented out similar array keys to avoid crashing the $pattern array
$pattern = array(
	"forum" => "infusions/forum/index.php",
	"forum/%forum_id%/view/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%&amp;forum_branch=%forum_branch%",
    //"forum/%forum_id%/view/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%",
	"forum/%forum_id%/browse/%rowstart%/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;rowstart=%rowstart%",
    //"forum/%forum_id%/view/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%&amp;forum_branch=%forum_branch%&amp;rowstart=%rowstart%",
	"forum/%forum_id%/filter/%time%/%type%/%sort%/%order%/%filter%/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;time=%time%&amp;type=%type%&amp;sort=%sort%&amp;order=%order%&amp;filter=%filter%", // permalink don't work, but rewrite class worked.
	"forum/%forum_id%/filter/%time%/%type%/%sort%/%order%/%filter%/%rowstart%/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;time=%time%&amp;type=%type%&amp;sort=%sort%&amp;order=%order%&amp;filter=%filter%&amp;rowstart=%rowstart%", // permalink don't work, but rewrite class worked.
    "forum/%forum_id%/post/%forum_name%/%action%" => "infusions/forum/post.php?action=%action%&amp;forum_id=%forum_id%",
    // Threads
    "thread" => "infusions/forum/viewthread.php",
    "thread/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%",
    "thread/%thread_id%/%post_id%/%thread_name%#post_%post_id%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%post_id%#post_%post_id%",
    "thread/%thread_id%/browse/%thread_rowstart%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;rowstart=%thread_rowstart%",
    "thread/%thread_id%/filter/%time%/%type%/%order%/%filter%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;time=%time%&amp;type=%type%&amp;order=%order%&amp;filter=%filter%",
    "thread/%thread_id%/filter/%time%/%type%/%order%/%filter%/%thread_rowstart%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;time=%time%&amp;type=%type%&amp;order=%order%&amp;filter=%filter%&amp;rowstart=%thread_rowstart%",
    // Post
    "forum/%action%/%thread_id%/%forum_id%" => "infusions/forum/post.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    // reply
    "forum/%action%/%thread_id%/%forum_id%/%post_id%" => "infusions/forum/post.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
    // edit
    "forum/%action%/%thread_id%/%forum_id%/%post_id%/%quote_id%" => "infusions/forum/post.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%&amp;quote=%quote_id%",
    //quote
);

// What's this?
$dir = FORUM;


$pattern_tables["%forum_id%"] = array(
    "table" => DB_FORUMS,
    "primary_key" => "forum_id",
    "id" => array("%forum_id%" => "forum_id"),
    "columns" => array(
        "%faq_name%" => "faq_name",
    )
);

$pattern_tables["%thread_id%"] = array(
    "table" => DB_FORUM_THREADS,
    "primary_key" => "thread_id",
    "id" => array("%thread_id%" => "thread_id"),
    "columns" => array(
        "%thread_name%" => "thread_subject",
    )
);

$pattern_tables["%post_id%"] = array(
    "table" => DB_FORUM_POSTS,
    "primary_key" => "post_id",
    "id" => array("%post_id%" => "post_id"),
    "columns" => array(
        "%post_message%" => "post_message",
    )
);