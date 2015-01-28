<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) { die("Access Denied"); }
define("ADMIN_PANEL", TRUE);
require_once INCLUDES."breadcrumbs.php";
require_once INCLUDES."header_includes.php";
require_once THEMES."templates/render_functions.php";
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

$bootstrap_theme_css_src = '';
// Load bootstrap
if ($settings['bootstrap']) {
	define('BOOTSTRAPPED', TRUE);
	$bootstrap_theme_css_src = INCLUDES."bootstrap/bootstrap.css";
	add_to_footer("<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>");
	add_to_footer("<script type='text/javascript' src='".INCLUDES."bootstrap/holder.js'></script>");
}

if ($settings['tinymce_enabled'] == 1) {
	$tinymce_list = array();
	$image_list = makefilelist(IMAGES, ".|..|");
	$image_filter = array('png', 'PNG', 'bmp', 'BMP', 'jpg', 'JPG', 'jpeg', 'gif', 'GIF', 'tiff', 'TIFF');
	foreach ($image_list as $image_name) {
		$image_1 = explode('.', $image_name);
		$last_str = count($image_1) - 1;
		if (in_array($image_1[$last_str], $image_filter)) {
			$tinymce_list[] = array('title' => $image_name, 'value' => IMAGES . $image_name);
		}
	}
	$tinymce_list = json_encode($tinymce_list);
}
require_once THEMES."templates/panels.php";
ob_start();
add_to_breadcrumbs(array('link'=>ADMIN.'index.php'.$aidlink.'&amp;pagenum=0', 'title'=>'Admin Dashboard'));