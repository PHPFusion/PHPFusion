<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 2.2.0.upgrade.inc
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$inf_version = '2.2.0';

$new_settings = [
    'bounty_points'  => 50,
    'min_rep_points' => 50,
    'picture_style'  => 'image',
];

$settings = get_settings('forum');
foreach ($new_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'forum')";
    }
}

$check_ranks_array = [
    'rank_color' => " ADD rank_color VARCHAR(20) NOT NULL DEFAULT '' AFTER rank_type",
    'rank_icon'  => " ADD rank_icon VARCHAR(50) NOT NULL DEFAULT '' AFTER rank_color",
];

foreach ($check_ranks_array as $key => $value) {
    if (!column_exists(DB_FORUM_RANKS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUM_RANKS.$value;
    }
}

$check_tags_array = [
    'tag_icon' => " ADD tag_icon VARCHAR(50) NOT NULL DEFAULT '' AFTER tag_color"
];

foreach ($check_tags_array as $key => $value) {
    if (!column_exists(DB_FORUM_TAGS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUM_TAGS.$value;
    }
}

$check_forum_array = [
    'forum_icon' => " ADD forum_icon VARCHAR(50) NOT NULL DEFAULT '' AFTER forum_vote"
];

foreach ($check_forum_array as $key => $value) {
    if (!column_exists(DB_FORUMS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUMS.$value;
    }
}

$change_forums = [
    'forum_access'          => " CHANGE forum_access forum_access VARCHAR(50) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_post'            => " CHANGE forum_post forum_post VARCHAR(50) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_reply'           => " CHANGE forum_reply forum_reply VARCHAR(50) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_post_ratings'    => " CHANGE forum_post_ratings forum_post_ratings VARCHAR(50) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_poll'            => " CHANGE forum_poll forum_poll VARCHAR(50) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_vote'            => " CHANGE forum_vote forum_vote VARCHAR(50) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_attach'          => " CHANGE forum_attach forum_attach VARCHAR(50) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_attach_download' => " CHANGE forum_attach_download forum_attach_download VARCHAR(50) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'",
    'forum_mods'            => " CHANGE forum_mods forum_mods VARCHAR(50) NOT NULL DEFAULT ''"
];

foreach ($change_forums as $key => $value) {
    if (column_exists(DB_FORUMS, $key, FALSE)) {
        $inf_altertable[] = DB_FORUMS.$value;
    }
}
