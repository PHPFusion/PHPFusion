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
    // Always the last key, they cannot stack together due to \W. Will crash
    "%thread_name%" => "([0-9a-zA-Z._]+)",
    "%forum_name%" => "([0-9a-zA-Z._]+)",
    "%post_message%" => "([0-9a-zA-Z._]+)",

    "%action%" => "([0-9a-zA-Z._]+)",

    "%forum_id%" => "([0-9]+)",
	"%parent_id%" => "([0-9]+)",
    "%pid%" => "([0-9]+)",
	"%forum_branch%" => "([0-9]+)",
    "%thread_id%" => "([0-9]+)",


	"%rowstart%" => "([0-9]+)",
    "%time%" => "([0-9a-zA-Z]+)",
    "%type%" => "([a-zA-Z]+)",
    "%sort%" => "([a-zA-Z]+)",
    "%order%" => "([a-zA-Z]+)",
	"%filter%" => "([0-9]+)",

    "%thread_rowstart%" => "([0-9]+)",
    "%post_id%" => "([0-9]+)",
    "%nr%" => "([0-9]+)",
    "%quote_id%" => "([0-9]+)",
    "%error_code%" => "([0-9]+)",
    "%print_type%" => "(F)",
    "%sorting%" => "([a-zA-Z]+)",
    "%track_status%" => "([a-zA-Z]+)",
);

// ID conflict, will check one by one later. Commented out similar array keys to avoid crashing the $pattern array
// All viewforum doesn't work.
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

$pattern += array(
    "forum" => "infusions/forum/index.php", // c
    "forum/browse/%forum_id%/%parent_id%/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%",
    "print/F/%nr%/%post_id%/%thread_id%/%thread_name%" => "print.php?type=F&amp;item_id=%thread_id%&amp;post=%post_id%&amp;nr=%nr%",
    "print/F/%rowstart%/%thread_id%/%thread_name%" => "print.php?type=F&amp;item_id=%thread_id%&amp;rowstart=%rowstart%",
    // Forum Browsing
    "forum/browse/%forum_id%/%parent_id%/page-%rowstart%/%forum_name%" => "infusions/forum/index.php?viewforum&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%&amp;rowstart=%rowstart%",
    "forum/%forum_id%/%forum_name%/create-newthread" => "infusions/forum/newthread.php?forum_id=%forum_id%",
    // Quick reply form - //c
    fusion_get_settings("site_path")."infusions/forum/thread/view/%thread_id%/%thread_name%" => "../../../../infusions/forum/viewthread.php?thread_id=%thread_id%",
    "forum/thread/%action%/quick-reply-%error_code%/%post_id%/%forum_id%/%thread_id%/%thread_name%" => "../../../../../infusions/forum/postify.php?post=%action%&amp;error=%error_code%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
    // New postify -- // cc
    "forum/thread/%action%/status-%error_code%/%forum_id%/%thread_id%/%thread_name%" => "infusions/forum/postify.php?post=%action%&amp;error=%error_code%&amp;forum_id=%forum_id%&amp;parent_id=%parent_id%&amp;thread_id=%thread_id%",
    // reply Postify, edit Postify, quote postify - //cc
    "forum/thread/%action%/status-%error_code%/%post_id%/%forum_id%/%thread_id%/%thread_name%" => "infusions/forum/postify.php?post=%action%&amp;error=%error_code%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
    "forum/thread/reply/%error%/%forum_id%/%thread_id%/%post_id%" => "infusions/forum/postify.php?post=reply&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
    "forum/thread/reply-success/%thread_id%/%pid%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%pid%",
    "forum/thread/reply-success/%thread_id%/%pid%#post_%post_id%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%pid%#post_%post_id%",
    // Postify for Reply/Edit Form Path & // Post Reply/Edit button
    fusion_get_settings("site_path")."forum/thread/%action%/%forum_id%/%thread_id%/%thread_name%" => "../../../../../../infusions/forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "forum/thread/%action%/%forum_id%/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?action=%action%&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    // View thread section
    "forum/thread/oldest/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;section=oldest",
    "forum/thread/latest/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;section=latest",
    //c
    "forum/thread/track-on/%forum_id%/%thread_id%/%thread_name%" => "infusions/forum/postify.php?post=on&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "forum/thread/track-off/%forum_id%/%thread_id%/%thread_name%" => "infusions/forum/postify.php?post=off&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%",
    "forum/thread/view/%thread_id%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%", //c
    "forum/thread/view/%thread_id%/row-%thread_rowstart%/%thread_name%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;rowstart=%thread_rowstart%",
    "forum/thread/view/%thread_id%/%post_id%/%thread_name%/#post_%post_id%" => "infusions/forum/viewthread.php?thread_id=%thread_id%&amp;pid=%post_id%#post_%post_id%",
    // Buttons in every post
    "forum/thread/quote/%forum_id%/%thread_id%/%post_id%/%quote_id%/%thread_name%" => "infusions/forum/viewthread.php?action=reply&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%&amp;quote=%quote_id%",
    "forum/thread/reply/%forum_id%/%thread_id%/%post_id%/%thread_name%" => "infusions/forum/viewthread.php?action=reply&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
    "forum/thread/edit/%forum_id%/%thread_id%/%post_id%/%thread_name%" => "infusions/forum/viewthread.php?action=edit&amp;forum_id=%forum_id%&amp;thread_id=%thread_id%&amp;post_id=%post_id%",
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