<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2009 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: online_users_panel.php
| Author: Nick Jones (Digitanium)
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

if (dbcount("(online_user)", DB_ONLINE, (iMEMBER ? "online_user='".$userdata['user_id']."'" : "online_user='0' AND online_ip='".USER_IP."'")) == 1) {
	$result = dbquery(
		"UPDATE ".DB_ONLINE." SET online_lastactive='".time()."', online_ip='".USER_IP."'
		WHERE ".(iMEMBER ? "online_user='".$userdata['user_id']."'" : "online_user='0' AND online_ip='".USER_IP."'"));
} else {
	$result = dbquery(
		"INSERT INTO ".DB_ONLINE." (online_user, online_ip, online_ip_type, online_lastactive) 
		VALUES ('".(iMEMBER ? $userdata['user_id'] : 0)."', '".USER_IP."', '".USER_IP_TYPE."', '".time()."')");
}
$result = dbquery("DELETE FROM ".DB_ONLINE." WHERE online_lastactive<".(time()-60)."");

openside($locale['global_010']);
$result = dbquery(
	"SELECT ton.online_user, tu.user_id, tu.user_name, tu.user_status FROM ".DB_ONLINE." ton
	LEFT JOIN ".DB_USERS." tu ON ton.online_user=tu.user_id"
);
$guests = 0; $members = array();
while ($data = dbarray($result)) {
	if ($data['online_user'] == "0") {
		$guests++;
	} else {
		$members[] = array($data['user_id'], $data['user_name'], $data['user_status']);
	}
}
echo THEME_BULLET." ".$locale['global_011'].": ".$guests."<br /><br />\n";
echo THEME_BULLET." ".$locale['global_012'].": ".count($members)."<br />\n";
if (count($members)) {
	$i = 1;
	while (list($key, $member) = each($members)) {
		echo "<span class='side'>".profile_link($member[0], $member[1], $member[2])."</span>";
		if ($i != count($members)) { echo ",\n"; } else { echo "<br />\n"; }
		$i++;
	}
}
echo "<br />\n".THEME_BULLET." ".$locale['global_014'].": ".number_format(dbcount("(user_id)", DB_USERS, "user_status<='1'"))."<br />\n";
if (iADMIN && checkrights("M") && $settings['admin_activation'] == "1") {
	echo THEME_BULLET." <a href='".ADMIN."members.php".$aidlink."&amp;status=2' class='side'>".$locale['global_015']."</a>";
	echo ": ".dbcount("(user_id)", DB_USERS, "user_status='2'")."<br />\n";
}
$data = dbarray(dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_status='0' ORDER BY user_joined DESC LIMIT 0,1"));
echo THEME_BULLET." ".$locale['global_016'].": <span class='side'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span>\n";
closeside();
?>