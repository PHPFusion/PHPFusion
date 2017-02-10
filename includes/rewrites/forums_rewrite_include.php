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

$regex = array(
    // Always the last key, they cannot stack together due to \W. Will crash
    "%forum_name%" => "([0-9a-zA-Z._\W]+)",
    "%forum_id%" => "([0-9]+)",
    "%parent_id%" => "([0-9]+)",

    "%thread_id%" => "([0-9]+)",
    "%thread_name%" => "([0-9a-zA-Z._\W]+)",
    "%track_status%" => "(on|off)",
    "%nr%" => "([0-9]+)",
    "%post_id%" => "([0-9]+)",
    "%rowstart%" => "([0-9]+)",

    "%action%" => "(reply|new|edit)",
    "%section%" => "(participated|latest|tracked|unanswered|unsolved)",
    "%quote_id%" => "([0-9]+)",
    "%error_code%" => "([0-9]+)",

    "%post_message%" => "([0-9a-zA-Z._]+)",
    "%pid%" => "([0-9]+)",
    "%time%" => "([0-9a-zA-Z]+)",
    "%type%" => "([a-zA-Z]+)",
    "%sort%" => "([a-zA-Z]+)",
    "%order%" => "([a-zA-Z]+)",
    "%filter%" => "([0-9]+)",
    "%print_type%" => "(F)",
    "%sorting%" => "([a-zA-Z]+)",
);

$pattern = array();

/**
 * Generate All Possible Filter Rules for SEF Installation
 */
$filter_sef_rules = array();
$forum_filterTypes = array(
    "time-%time%" => "time=%time%",
    "type-%type%" => "type=%type%",
    "sort-%sort%" => "sort=%sort%",
    "order-%order%" => "order=%order%",
);
$fKeyPrefix = "forum/%forum_id%/%parent_id%";
$fKeyAppend = "/filters";
$fKeyAppend2 = "/page-%rowstart%/filters";
$fRulePrefix = "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%";
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
$_keys = array();
$_val = array();
$filter_values = array();
$filter_keys = array();
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

// Forum View
$pattern += array(
    "forum/create-new-thread" => "infusions/forum/newthread.php",
    "forum/browse/%forum_id%/%parent_id%/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%",
    "forum" => "infusions/forum/index.php",
);

// Thread View
$pattern += array(
    "forum/thread/view/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%",
    "forum/thread/view/%thread_id%/%thread_name%-row-%rowstart%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;rowstart=%rowstart%",
    "forum/thread/view/%forum_id%/%thread_id%/%thread_name%-row-%rowstart%" => "infusions/forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;rowstart=%rowstart%",
    "forum/thread/confirm-move/%forum_id%/%thread_id%/%thread_name%-row-%rowstart%" => "infusions/forum/viewthread.php?forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;rowstart=%rowstart%&amp;sv",

    "forum/thread/view-%pid%/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%pid%",
    // valid request for router
    "forum/thread/view-%pid%/%thread_id%/%thread_name%#post_%post_id%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%pid%#post_%post_id%",
    // this is not a valid request

    "forum/thread/oldest/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;section=oldest",
    "forum/thread/latest/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;section=latest",
    "forum/thread/%track_status%/%forum_id%/%thread_id%/%thread_name%" => "infusions/forum/postify.php?post=%track_status%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "print/F/%nr%/%post_id%/%thread_id%/%thread_name%" => "print.php?type=F&amp;item_id=%thread_id%&amp;post=%post_id%&amp;nr=%nr%",
    "print/F/%rowstart%/%thread_id%/%thread_name%" => "print.php?type=F&amp;item_id=%thread_id%&amp;rowstart=%rowstart%",
);

$pattern += array(
    "forum/%section%" => "infusions/forum/index.php?section=%section%",
);

// Buttons & Forms
$pattern += array(
    "forum/%forum_id%/%action%/post_%post_id%/thread_%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
    "forum/%forum_id%/%action%/%quote_id%/%post_id%/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%&amp;quote=%quote_id%",
    "forum/%forum_id%/%action%/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "forum/%forum_id%/%forum_name%/create-new-thread" => "infusions/forum/newthread.php?forum_id=%forum_id%",
);
// Postify Redirect
$pattern += array(
    "forum/newthread-post/%action%/%error_code%/%parent_id%/%forum_id%/%thread_id%/%thread_name%" => "infusions/forum/postify.php?post=%action%&amp;error=%error_code%&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%&amp;thread_id=%thread_id%",
    "forum/discuss-post/%action%/%post_id%/%error_code%/%forum_id%/%thread_id%/%thread_name%" => "infusions/forum/postify.php?post=%action%&amp;error=%error_code%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
);

$pattern += array(
    "forum/my-threads" => "infusions/forum_threads_list_panel/my_threads.php",
    "forum/my-post" => "infusions/forum_threads_list_panel/my_posts.php",
    "forum/my-post-rows-%rowstart%" => "infusions/forum_threads_list_panel/my_posts.php?rowstart=%rowstart%",
    "forum/new-post" => "infusions/forum_threads_list_panel/new_posts.php",
    "forum/tracked-threads" => "infusions/forum_threads_list_panel/tracked_threads.php",
    "forum/tracked-threads/%thread_id%/stop-tracking-%thread_name%" => "infusions/forum_threads_list_panel/tracked_threads.php?delete=%thread_id%",
);


$pattern_tables["%forum_id%"] = array(
    "table" => DB_FORUMS,
    "primary_key" => "forum_id",
    "id" => array("%forum_id%" => "forum_id"),
    "columns" => array(
        "%forum_name%" => "forum_name",
    ),
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