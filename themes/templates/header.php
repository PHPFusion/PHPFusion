<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: header.php
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
if ($settings['site_seo']) {
	// Object should be created before including output_handling_class because we are using this object in output handling
	require_once CLASSES."PermalinksDisplay.class.php";
	$permalink = new PermalinksDisplay();
	$result = dbquery("SELECT * FROM ".DB_PERMALINK_REWRITE."");
	// Manual invoke method.
	//$permalink->AddHandler('threads');
	//$permalink->AddHandler('downloads-cats');
	if (dbrows($result) > 0) {
		while ($_permalink = dbarray($result)) {
			$rewrite_handler[] = $_permalink['rewrite_name'];
			$permalink->AddHandler($_permalink['rewrite_name']);
		}
	}
}
require_once INCLUDES."header_includes.php";
require_once THEME."theme.php";
require_once THEMES."templates/render_functions.php";
if ($settings['maintenance'] == "1" && ((iMEMBER && $settings['maintenance_level'] == "1" && $userdata['user_id'] != "1") || ($settings['maintenance_level'] > $userdata['user_level']))) {
	redirect(BASEDIR."maintenance.php");
}
if (iMEMBER) {
	$result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."'
		WHERE user_id='".$userdata['user_id']."'");
}
echo "<!DOCTYPE html>\n";
//echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='".$locale['xml_lang']."' lang='".$locale['xml_lang']."'>\n"; // to delete?
echo "<head>\n<title>".$settings['sitename']."</title>\n";
echo "<meta http-equiv='Content-Type' content='text/html; charset=".$locale['charset']."' />\n";
echo "<meta name='description' content='".$settings['description']."' />\n";
echo "<meta name='keywords' content='".$settings['keywords']."' />\n";
// Load bootstrap
if ($settings['bootstrap']) {
	define('BOOTSTRAPPED', TRUE);
	echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n";
	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
	echo "<link href='".INCLUDES."bootstrap/bootstrap.css' rel='stylesheet' media='screen' />\n";
	add_to_footer("<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>");
	add_to_footer("<script type='text/javascript' src='".INCLUDES."bootstrap/holder.js'></script>");
}
// Entypo icons
echo "<link href='".INCLUDES."font/entypo/entypo.css' rel='stylesheet' media='screen' />\n";
// Default CSS styling which applies to all themes but can be overriden
echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' />\n";
// Theme CSS
echo "<link href='".THEME."styles.css' rel='stylesheet' type='text/css' media='screen' />\n";
if (file_exists(IMAGES."favicon.ico")) {
	echo "<link href='".IMAGES."favicon.ico' rel='shortcut icon' type='image/x-icon' />\n";
}
if (function_exists("get_head_tags")) {
	echo get_head_tags();
}
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>\n";
echo "</head>\n<body>\n";

require_once THEMES."templates/panels.php";
ob_start();
?>