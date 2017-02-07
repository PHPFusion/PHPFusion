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
    array(
        LOCALE.LOCALESET.'setup.php',
        INFUSIONS.'forum/locale/'.LOCALESET.'/forum_tags.php'
    )
);

// Infusion general information
$inf_title = $locale['forums']['title'];
$inf_description = $locale['forums']['description'];
$inf_version = '1.0.6';
$inf_developer = 'PHP Fusion Development Team';
$inf_email = 'info@php-fusion.co.uk';
$inf_weburl = 'https://www.php-fusion.co.uk';
$inf_folder = 'forum';
$inf_image = 'forums.png';

$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('upvote_points', '2', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('downvote_points', '1', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('answering_points', '15', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('points_to_upvote', '100', 'forum')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('points_to_downvote', '100', 'forum')";

if (!column_exists('forum_threads', 'thread_bounty')) {
    $inf_altertable[] = $db_prefix."forum_threads ADD thread_bounty SMALLINT(8) NOT NULL DEFAULT '0' AFTER thread_answered";
}

if (!column_exists('forum_threads', 'thread_bounty_description')) {
    $inf_altertable[] = $db_prefix."forum_threads ADD thread_bounty_description TEXT NOT NULL AFTER thread_bounty";
}

if (!column_exists('forum_threads', 'thread_bounty_start')) {
    $inf_altertable[] = $db_prefix."forum_threads ADD thread_bounty_start INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_bounty_description";
}

if (!column_exists('forum_threads', 'thread_bounty_user')) {
    $inf_altertable[] = $db_prefix."forum_threads ADD thread_bounty_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER thread_bounty_start";
}

if (!column_exists('forum_posts', 'post_answer')) {
    $inf_altertable[] = $db_prefix."forum_posts ADD post_answer TINYINT(1) NOT NULL DEFAULT '0' AFTER post_locked";
}

if (!column_exists('users', 'user_reputation')) {
    $inf_altertable[] = $db_prefix."users ADD user_reputation INT(10) UNSIGNED NOT NULL AFTER user_status";
}

// Modify the forum votes and add primary key
if (!column_exists('forum_votes', 'vote_id')) {
    $inf_altertable[] = $db_prefix."forum_votes ADD vote_id MEDIUMINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (vote_id)";
}

// Install a new vote table
if (!db_exists($db_prefix."forum_user_reputation")) {
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