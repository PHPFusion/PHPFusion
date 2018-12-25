<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 8
| Copyright (C) 2002 - 2013 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: User Search Results for Form Select User V8
| Filename: users.json.php
| Author: PHP-Fusion Development Team
| Coded by : Frederick MC Chan (Hien)
| Version : 8.0.0 (please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__)."../../../../maincore.php";
if (!defined("IN_FUSION")) { die("Access Denied"); }
$q = isset($_GET['q']) ? stripinput($_GET['q']) : '';

header("Cache-control: max-age=290304000, public");
$tsstring = gmdate('D, d Y H:i:s', time()) . 'GMT';
$etag = LANGUAGE.time();
$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;
if ((($if_none_match && $if_none_match == $etag) || (!$if_none_match)) &&
    ($if_modified_since && $if_modified_since == $tsstring)) {
    header('HTTP/1.1 304 Not Modified');
    exit();
} else {
    header("Last-Modified: $tsstring");
    header("ETag: \"{$etag}\"");
}

$result = dbquery("SELECT user_id, user_name, user_avatar, user_level FROM ".DB_USERS." 
				WHERE user_status='0' 
				AND user_name LIKE '$q%' 
				AND user_id !='".$userdata['user_id']."'
				ORDER BY user_level DESC, user_name ASC");
if (dbrows($result) > 0) {
	while ($udata = dbarray($result)) {
		$user_id = $udata['user_id'];
		$user_text = $udata['user_name'];
		$user_avatar = ($udata['user_avatar'] && file_exists(IMAGES."avatars/".$udata['user_avatar'])) ? $udata['user_avatar'] : "noavatar50.png";
		$user_name = $udata['user_name'];
		$user_level = getuserlevel($udata['user_level']);
		$user_opts[] = array('id' => "$user_id", 'text' => "$user_name", 'avatar' => "$user_avatar", "level" => "$user_level");
	}
} else {
	$user_opts[] = array('id' => '', 'text' => $locale['notfound'], 'avatar' => '', 'level' => '');
}
echo json_encode($user_opts);