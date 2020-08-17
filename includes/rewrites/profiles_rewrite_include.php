<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: profiles_rewrite_include.php
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
    "%user_id%"   => "([0-9]+)",
    "%group_id%"  => "([0-9]+)",
    "%section%"   => "([0-9]+)",
    "%user_name%" => "([0-9a-zA-Z._\W]+)"
];

$pattern = [
    "profile-section-%section%/%user_id%/%user_name%" => "profile.php?lookup=%user_id%&amp;section=%section%",
    "profile-%user_id%/%user_name%"                   => "profile.php?lookup=%user_id%",
    "profile-group-%group_id%"                        => "profile.php?group_id=%group_id%"

];

$pattern_tables["%user_id%"] = [
    "table"       => DB_USERS,
    "primary_key" => "user_id",
    "id"          => ["%user_id%" => "user_id"],
    "columns"     => [
        "%user_name%" => "user_name"
    ]
];
