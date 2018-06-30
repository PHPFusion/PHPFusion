<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/1.0.6.upgrade.inc
| Author: PHP-Fusion Development Team
| Introduction to Support Forum Type User Reputation Points
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$locale = fusion_get_locale("",
    [
        LOCALE.LOCALESET.'setup.php',
        INFUSIONS.'forum/locale/'.LOCALESET.'/forum_tags.php'
    ]
);

$inf_version = '1.0.6';
$select = dbquery("SELECT forum_id, forum_mods FROM ".$db_prefix."forums ORDER BY forum_id ASC");
if (dbrows($select)) {
    while ($data = dbarray($select)) {
        if ($data['forum_mods']) {
            $inf_updatedbrow[] = $db_prefix."forums SET forum_mods='-".$data['forum_mods']."' WHERE forum_id='".$data['forum_id']."' AND forum_mods != 0";
        }
    }
}
$check_array = [
    'upvote_points'      => 2,
    'downvote_points'    => 1,
    'answering_points'   => 15,
    'points_to_upvote'   => 100,
    'points_to_downvote' => 100
];
foreach ($check_array as $name => $val) {
    if (!dbcount("(settings_name)", DB_SETTINGS_INF, "settings_name=:col_name", [':col_name' => $name])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$name', '$val', 'forum')";
    }
}
$check_threads = [
    'thread_bounty'             => " ADD thread_bounty SMALLINT(8) NOT NULL DEFAULT '0' AFTER thread_answered",
    'thread_bounty_description' => " ADD thread_bounty_description TEXT NOT NULL AFTER thread_bounty",
    'thread_bounty_start'       => " ADD thread_bounty_start INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_bounty_description",
    'thread_bounty_user'        => " ADD thread_bounty_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_bounty_start",
];

foreach ($check_threads as $key => $value) {
    if (!column_exists(DB_FORUM_THREADS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUM_THREADS.$value;
    }
}

if (!column_exists(DB_FORUM_POSTS, 'post_cat', FALSE)) {
    $inf_altertable[] = $db_prefix."forum_posts ADD post_answer TINYINT(1) NOT NULL DEFAULT '0' AFTER post_locked";
}

if (!column_exists(DB_FORUM_POSTS, 'post_answer', FALSE)) {
    $inf_altertable[] = $db_prefix."forum_posts ADD post_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER post_id";
}

if (!column_exists(DB_USERS, 'user_reputation', FALSE)) {
    $inf_altertable[] = $db_prefix."users ADD user_reputation INT(10) UNSIGNED NOT NULL AFTER user_status";
}

// Modify the forum votes and add primary key
if (!column_exists(DB_FORUM_VOTES, 'vote_id', FALSE)) {
    $inf_altertable[] = $db_prefix."forum_votes ADD vote_id MEDIUMINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (vote_id)";
}

// Install a new vote table
if (!db_exists(DB_FORUM_USER_REP)) {
    $inf_newtable[] = $db_prefix."forum_user_reputation (
    rep_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    rep_answer TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    points_gain SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    voter_id SMALLINT(1) UNSIGNED NOT NULL DEFAULT '0',
    user_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (rep_id),
    KEY post_id (post_id, user_id, voter_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
}
