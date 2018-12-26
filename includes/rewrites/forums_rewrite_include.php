<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: forums_rewrite_include.php
| Author: Chan (Frederick MC Chan)
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

$regex = [
    // Always the last key, they cannot stack together due to \W. Will crash
    "%forum_name%"   => "([0-9a-zA-Z._()\W]+)",
    "%cat_id%"       => "([0-9]+)",
    "%forum_id%"     => "([0-9]+)",
    "%thread_id%"    => "([0-9]+)",
    "%thread_name%"  => "([0-9a-zA-Z._()\W]+)",
    "%track_status%" => "(on|off)",
    "%nr%"           => "([0-9]+)",
    "%post_id%"      => "([0-9]+)",
    "%rowstart%"     => "([0-9]+)",
    "%action%"       => "(reply|new|edit|newpoll|post|previewreply)",
    "%quote_id%"     => "([0-9]+)",
    "%error_code%"   => "([0-9]+)",
    "%post_message%" => "([0-9a-zA-Z._]+)",
    "%pid%"          => "([0-9]+)",
    "%time%"         => "([0-9a-zA-Z]+)",
    "%type%"         => "([a-zA-Z]+)",
    "%print_type%"   => "(F)",
    "%post_id%"      => "([0-9]+)",
    "%quote_id%"     => "([0-9]+)",
    "%forum_id%"     => "([0-9]+)",
    "%thread_id%"    => "([0-9]+)",
    "%hash_stop%"    => "\#(?=\s*|)",
];

$pattern = [];

// Forum Browse
$pattern += [
    "forum"                                                     => "forum/index.php",
    "forum/browse/%forum_id%/%forum_name%"                      => "forum/index.php?cat=%cat_id%",
    "forum/browse/%forum_id%/%forum_name%"                      => "forum/viewforum.php?forum_id=%forum_id%",
    "forum/browse/%forum_id%/page/%rowstart%/view/%forum_name%" => "forum/viewforum.php?forum_id=%forum_id%&amp;rowstart=%rowstart%",
];

$pattern += [
    "forum/%forum_id%/%forum_name%/create-new-thread" => "forum/post.php?action=%action%&amp;forum_id=%forum_id%",
];

// Thread Views
$pattern += [
    "forum/thread/view/%thread_id%/%thread_name%"                                   => "forum/viewthread.php?thread_id=%thread_id%",
    "forum/thread/view/%thread_id%/%thread_name%-row-%rowstart%"                    => "forum/viewthread.php?thread_id=%thread_id%&amp;rowstart=%rowstart%",
    "forum/thread/view/%forum_id%/%thread_id%/%thread_name%-row-%rowstart%"         => "forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;rowstart=%rowstart%",
    "forum/thread/view/%forum_id%/%thread_id%/%thread_name%#post_%post_id%"         => "forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%#post_%post_id%",
    "forum/thread/view/%forum_id%/%thread_id%/%thread_name%#top"                    => "forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%#top",
    "forum/thread/confirm-move/%forum_id%/%thread_id%/%thread_name%-row-%rowstart%" => "forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;rowstart=%rowstart%&amp;sv",
    "forum/thread/view-%pid%/%thread_id%/%thread_name%"                             => "forum/viewthread.php?thread_id=%thread_id%&amp;pid=%pid%",
    "forum/thread/view-%pid%/%thread_id%/%thread_name%#post_%post_id%"              => "forum/viewthread.php?thread_id=%thread_id%&amp;pid=%pid%#post_%post_id%",
    "forum/thread/view/%thread_id%/%thread_name%"                                   => "forum/viewthread.php?thread_id=%thread_id%",
];


// Actions
$pattern += [
    "forum/%forum_id%/%action%/post_%post_id%/thread_%thread_id%/%thread_name%" => "forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
    "forum/%forum_id%/%action%/%quote_id%/%post_id%/%thread_id%/%thread_name%"  => "forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%&amp;quote=%quote_id%",
    "forum/%forum_id%/%action%/%thread_id%/%thread_name%"                       => "forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "forum/thread/%track_status%/%forum_id%/%thread_id%/%thread_name%"          => "forum/postify.php?post=%track_status%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "print/F/%nr%/%post_id%/%thread_id%/%thread_name%"                          => "print.php?type=F&amp;item_id=%thread_id%&amp;post=%post_id%&amp;nr=%nr%",
    "print/F/%rowstart%/%thread_id%/%thread_name%"                              => "print.php?type=F&amp;item_id=%thread_id%&amp;rowstart=%rowstart%",
];

// Forum Threads List Panel
$pattern += [
    "forum/my-threads"                                              => "infusions/forum_threads_list_panel/my_threads.php",
    "forum/my-post"                                                 => "infusions/forum_threads_list_panel/my_posts.php",
    "forum/my-post-rows-%rowstart%"                                 => "infusions/forum_threads_list_panel/my_posts.php?rowstart=%rowstart%",
    "forum/new-post"                                                => "infusions/forum_threads_list_panel/new_posts.php",
    "forum/tracked-threads"                                         => "infusions/forum_threads_list_panel/my_tracked_threads.php",
    "forum/tracked-threads/%thread_id%/stop-tracking-%thread_name%" => "infusions/forum_threads_list_panel/tracked_threads.php?delete=%thread_id%",
];

$pattern_tables["%forum_id%"] = [
    "table"       => DB_FORUMS,
    "primary_key" => "forum_id",
    "id"          => ["%forum_id%" => "forum_id"],
    "columns"     => ["%forum_name%" => "forum_name",
    ],
];

$pattern_tables["%thread_id%"] = [
    "table"       => DB_THREADS,
    "primary_key" => "thread_id",
    "id"          => ["%thread_id%" => "thread_id"],
    "columns"     => [
        "%thread_name%" => "thread_subject",
    ]
];

$pattern_tables["%post_id%"] = [
    "table"       => DB_POSTS,
    "primary_key" => "post_id",
    "id"          => ["%post_id%" => "post_id"],
    "columns"     => ["%post_message%" => "post_message",
    ]
];

