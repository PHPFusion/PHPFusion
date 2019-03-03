<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: User Search Results for Form Select User
| Filename: users.json.php
| Author : Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'../../../../../maincore.php';

$user_opts = [];

$q = isset($_GET['q']) ? stripinput($_GET['q']) : '';
// since search is on user_name.
$result = dbquery("SELECT user_id, user_name, user_avatar, user_level, MATCH(user_name) AGAINST (:Q1) 'score'
    FROM ".DB_USERS." WHERE ".(blacklist('user_id') ? blacklist('user_id').' AND' : '')." user_status=:status AND
    user_name LIKE :Q ".(!isset($_GET['allow_self']) ? "AND user_id !='".fusion_get_userdata('user_id')."'" : "")."
    ORDER BY score DESC, user_name ASC, user_level DESC", [
    ':status' => 0,
    ':Q'      => "$q%",
    ':Q1'     => "$q%"
]);

if (dbrows($result)) {
    while ($udata = dbarray($result)) {
        $user_id = $udata['user_id'];
        $user_text = $udata['user_name'];
        $user_avatar = ($udata['user_avatar'] && file_exists(IMAGES."avatars/".$udata['user_avatar'])) ? $udata['user_avatar'] : "no-avatar.jpg";
        $user_name = $udata['user_name'];
        $user_level = getuserlevel($udata['user_level']);
        $user_opts[] = ['id' => "$user_id", 'text' => "$user_name", 'avatar' => "$user_avatar", "level" => "$user_level"];
    }
} else {
    $user_opts[] = ['id' => '', 'text' => fusion_get_locale("500", LOCALE.LOCALESET."search.php"), 'avatar' => '', 'level' => ''];
}

header('Content-Type: application/json');

echo json_encode($user_opts);
