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
defined('IN_FUSION') || exit;

$regex = [
    // Always the last key, they cannot stack together due to \W. Will crash
    "%forum_name%"   => "([0-9a-zA-Z._()\W]+)",
    "%tag_id%"       => "([0-9]+)",
    "%forum_id%"     => "([0-9]+)",
    "%thread_id%"    => "([0-9]+)",
    "%parent_id%"    => "([0-9]+)",
    "%tag_name%"     => "([0-9a-zA-Z._()\W]+)",
    "%thread_name%"  => "([0-9a-zA-Z._()\W]+)",
    "%track_status%" => "(on|off)",
    "%nr%"           => "([0-9]+)",
    "%post_id%"      => "([0-9]+)",
    "%rowstart%"     => "([0-9]+)",
    "%action%"       => "(reply|new|edit|newpoll)",
    "%sort_action%"  => "(oldest|latest|high)",
    "%section%"      => "(participated|latest|tracked|unanswered|unsolved)",
    "%quote_id%"     => "([0-9]+)",
    "%error_code%"   => "([0-9]+)",
    "%post_message%" => "([0-9a-zA-Z._]+)",
    "%pid%"          => "([0-9]+)",
    "%time%"         => "([0-9a-zA-Z]+)",
    "%type%"         => "([a-zA-Z]+)",
    "%sort%"         => "([a-zA-Z]+)",
    "%order%"        => "([a-zA-Z]+)",
    "%filter%"       => "([0-9]+)",
    "%print_type%"   => "(F)",
    "%sorting%"      => "([a-zA-Z]+)"
];

$pattern = [];

/**
 * Generate All Possible Filter Rules for SEF Installation
 */
$filter_sef_rules = [];
$forum_filterTypes = [
    "time-%time%"   => "time=%time%",
    "type-%type%"   => "type=%type%",
    "sort-%sort%"   => "sort=%sort%",
    "order-%order%" => "order=%order%"
];
$fKeyPrefix = "forum/%forum_id%";
$fKeyAppend = "/filters";
$fKeyAppend2 = "/page-%rowstart%/filters";
$fRulePrefix = "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%";

if (!function_exists("filter_implode")) {
    function filter_implode($arr, $delimiter, $temp_string, &$collect) {
        if ($temp_string != "") {
            $collect [] = $temp_string;
        }
        for ($i = 0; $i < sizeof($arr); $i++) {
            $copy = $arr;
            $elem = array_splice($copy, $i, 1); // removes and returns the i'th element
            if (sizeof($copy) > 0) {
                filter_implode($copy, $delimiter, $temp_string.$delimiter.$elem[0], $collect);
            } else {
                $collect[] = $temp_string.$delimiter.$elem[0];
            }
        }
    }
}

$_keys = [];
$_val = [];
$filter_values = [];
$filter_keys = [];
$filter_keys_with_rowstart = [];
$filter_values_with_rowstart = [];
filter_implode(array_keys($forum_filterTypes), "/", $fKeyPrefix, $_keys);
foreach ($_keys as $filter_value) {
    $filter_keys[] = $filter_value.$fKeyAppend;
    $filter_keys_with_rowstart[] = $filter_value.$fKeyAppend2;
}
filter_implode(array_values($forum_filterTypes), "&amp;", $fRulePrefix, $_val);
foreach ($_val as $filter_value) {
    $filter_values[] = $filter_value;
    $filter_values_with_rowstart[] = $filter_value.$fKeyAppend2;
}
$filter_sef_rules = array_combine($filter_keys, $filter_values);
$filter_sef_rules_rowstart = array_combine($filter_keys_with_rowstart, $filter_values_with_rowstart);
array_shift($filter_sef_rules);
array_shift($filter_sef_rules_rowstart);

// Install Thread Filters
$pattern += $filter_sef_rules;
$pattern += $filter_sef_rules_rowstart;

$pattern = [
    "forum/post-%action%/error-%error_code%/forum-%forum_id%/thread-%thread_id%"                => "infusions/forum/postify.php?post=%action%&amp;error=%error_code%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "forum/post-%action%/error-%error_code%/forum-%forum_id%/thread-%thread_id%/post-%post_id%" => "infusions/forum/postify.php?post=%action%&amp;error=%error_code%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",

    "forum/viewforum/%forum_id%/%forum_name%"                                                           => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%",
    "forum/viewforum/%forum_id%/%forum_name%/parent-%parent_id%"                                        => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%",
    "forum/viewforum/%forum_id%/activity"                                                               => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;view=activity",
    "forum/viewforum/%forum_id%/people"                                                                 => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;view=people",
    "forum/viewforum/%forum_id%/type/%type%"                                                            => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;type=%type%",
    "forum/create-new-thread"                                                                           => "infusions/forum/newthread.php",
    "forum/create-new-thread/%forum_id%/%forum_name%"                                                   => "infusions/forum/newthread.php?forum_id=%forum_id%",
    "forum/tags/%tag_id%/%tag_name%"                                                                    => "infusions/forum/tags.php?tag_id=%tag_id%",
    "forum/tags"                                                                                        => "infusions/forum/tags.php",
    "forum"                                                                                             => "infusions/forum/index.php",

    // Thread View  viewthread.php
    "forum/%forum_id%/action/%action%/post_%post_id%/thread_%thread_id%/%thread_name%"                  => "infusions/forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
    "forum/%forum_id%/action/%action%/post_%post_id%/thread_%thread_id%/%thread_name%/quote/%quote_id%" => "infusions/forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%&amp;quote=%quote_id%",
    "forum/%forum_id%/action/%action%/%thread_id%/%thread_name%"                                        => "infusions/forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "forum/action/%action%/%thread_id%/%thread_name%"                                                   => "infusions/forum/viewthread.php?action=%action%&amp;thread_id=%thread_id%",
    "forum/thread/view/%thread_id%/%thread_name%"                                                       => "infusions/forum/viewthread.php?thread_id=%thread_id%",
    "forum/thread/view/%thread_id%/%thread_name%/sort-by/%sort_action%"                                 => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;sort_post=%sort_action%",
    "forum/thread/view/%thread_id%/%thread_name%/rows-%rowstart%"                                       => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;rowstart=%rowstart%",
    "forum/thread/%thread_id%/%thread_name%/view-%pid%"                                                 => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%pid%",
    "forum/thread/%thread_id%/%thread_name%/view-%pid%#post_%post_id%"                                  => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%pid%#post_%post_id%",
    "forum/thread/view/%forum_id%/%thread_id%/%thread_name%/view-%pid%#post_%post_id%"                  => "infusions/forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;pid=%pid%#post_%post_id%",
    "forum/thread/view/%forum_id%/%thread_id%/%thread_name%-row-%rowstart%"                             => "infusions/forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;rowstart=%rowstart%",
    "forum/thread/confirm-move/%forum_id%/%thread_id%/%thread_name%-row-%rowstart%"                     => "infusions/forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;rowstart=%rowstart%&amp;sv",
    // valid request for router
    "forum/thread/%track_status%/forum-%forum_id%/%thread_id%/%thread_name%"                            => "infusions/forum/postify.php?post=%track_status%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "print/F/%nr%/%post_id%/%thread_id%/%thread_name%"                                                  => "print.php?type=F&amp;item_id=%thread_id%&amp;post=%post_id%&amp;nr=%nr%",
    "print/F/%rowstart%/%thread_id%/%thread_name%"                                                      => "print.php?type=F&amp;item_id=%thread_id%&amp;rowstart=%rowstart%",

    "forum/%section%"                 => "infusions/forum/index.php?section=%section%",
    "forum/%section%/rows-%rowstart%" => "infusions/forum/index.php?section=%section%&amp;rowstart=%rowstart%",

    "forum/my-threads"                                                 => "infusions/forum_threads_list_panel/my_threads.php",
    "forum/my-threads/rows-%rowstart%"                                 => "infusions/forum_threads_list_panel/my_threads.php?rowstart=%rowstart%",
    "forum/my-post"                                                    => "infusions/forum_threads_list_panel/my_posts.php",
    "forum/my-post/rows-%rowstart%"                                    => "infusions/forum_threads_list_panel/my_posts.php?rowstart=%rowstart%",
    "forum/my-new-posts"                                               => "infusions/forum_threads_list_panel/new_posts.php",
    "forum/my-new-posts/rows-%rowstart%"                               => "infusions/forum_threads_list_panel/new_posts.php?rowstart=%rowstart%",
    "forum/my-tracked-threads"                                         => "infusions/forum_threads_list_panel/my_tracked_threads.php",
    "forum/my-tracked-threads/rows-%rowstart%"                         => "infusions/forum_threads_list_panel/my_tracked_threads.php?rowstart=%rowstart%",
    "forum/my-tracked-threads/%thread_id%/stop-tracking-%thread_name%" => "infusions/forum_threads_list_panel/tracked_threads.php?delete=%thread_id%"
];

$pattern_tables["%forum_id%"] = [
    "table"       => DB_FORUMS,
    "primary_key" => "forum_id",
    "id"          => ["%forum_id%" => "forum_id"],
    "columns"     => [
        "%forum_name%" => "forum_name"
    ],
];

$pattern_tables["%thread_id%"] = [
    "table"       => DB_FORUM_THREADS,
    "primary_key" => "thread_id",
    "id"          => ["%thread_id%" => "thread_id"],
    "columns"     => [
        "%thread_name%" => "thread_subject"
    ]
];

$pattern_tables["%post_id%"] = [
    "table"       => DB_FORUM_POSTS,
    "primary_key" => "post_id",
    "id"          => ["%post_id%" => "post_id"],
    "columns"     => [
        "%post_message%" => "post_message"
    ]
];

$pattern_tables["%tag_id%"] = [
    "table"       => DB_FORUM_TAGS,
    "primary_key" => "tag_id",
    "id"          => ["%tag_id%" => "tag_id"],
    "columns"     => [
        "%tag_name%" => "tag_title"
    ]
];
