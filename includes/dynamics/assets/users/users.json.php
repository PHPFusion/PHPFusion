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

require_once dirname(__FILE__).'../../../../../maincore.php';
if (!defined("IN_FUSION")) {die("Access Denied");}

$q = stripinput($_GET['q']);
// since search is on user_name.
$result = dbquery("SELECT user_id, user_name, user_avatar, user_level 
    FROM ".DB_USERS." WHERE ".(blacklist('user_id') ? blacklist('user_id').' AND' : '')." user_status=:status AND
    user_name LIKE :Q ".(!isset($_GET['allow_self']) ? "AND user_id !='".fusion_get_userdata('user_id')."'" : "")."
    ORDER BY user_level DESC, user_name ASC", [
    ':status' => 0,
    ':Q'      => "$q%"
]);

if (dbrows($result)) {
	while ($udata = dbarray($result)) {
		$user_id = $udata['user_id'];
		$user_text = $udata['user_name'];
		$user_avatar = ($udata['user_avatar'] && file_exists(IMAGES."avatars/".$udata['user_avatar'])) ? $udata['user_avatar'] : "noavatar50.png";
		$user_name = $udata['user_name'];
		$user_level = getuserlevel($udata['user_level']);
		$user_opts[] = array('id' => "$user_id", 'text' => "$user_name", 'avatar' => "$user_avatar", "level" => "$user_level");
	}
} else {
	$user_opts[] = array('id' => '', 'text' => "No Results Found..", 'avatar' => '', 'level' => '');
}
echo json_encode($user_opts);