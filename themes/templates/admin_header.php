<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_header.php
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
define("ADMIN_PANEL", TRUE);
require_once INCLUDES."output_handling_include.php";
require_once INCLUDES."header_includes.php";
if ($settings['maintenance'] == "1" && !iADMIN) {
	redirect(BASEDIR."maintenance.php");
} else {
	if (file_exists(THEMES."admin_templates/".$settings['admin_theme']."/acp_theme.php") && preg_match("/^([a-z0-9_-]){2,50}$/i", $settings['admin_theme'])) {
		require_once THEMES."admin_templates/".$settings['admin_theme']."/acp_theme.php";
	}
}
if (iMEMBER) {
	$result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."' WHERE user_id='".$userdata['user_id']."'");
}
echo "<!DOCTYPE html>\n";
echo "<head>\n<title>".$settings['sitename']."</title>\n";
echo "<meta http-equiv='Content-Type' content='text/html; charset=".$locale['charset']."' />\n";
echo "<link rel='stylesheet' href='".THEMES."admin_templates/".$settings['admin_theme']."/acp_styles.css' type='text/css' media='screen' />\n";
if (file_exists(IMAGES."favicon.ico")) {
	echo "<link rel='shortcut icon' href='".IMAGES."favicon.ico' type='image/x-icon' />\n";
}
if (function_exists("get_head_tags")) {
	echo get_head_tags();
}
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-msg.js'></script>\n";
echo "</head>\n<body>\n";

require_once THEMES."templates/panels.php";
ob_start();
?>
