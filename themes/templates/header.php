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

use PHPFusion\PermalinksDisplay;

// Check if Maintenance is Enabled
if ($settings['maintenance'] == "1" && ((iMEMBER && $settings['maintenance_level'] == "1" && $userdata['user_id'] != "1") || ($settings['maintenance_level'] > $userdata['user_level']))) {
	redirect(BASEDIR."maintenance.php");
}

if ($settings['site_seo']) {
	$permalink = PermalinksDisplay::getInstance();
	$result = dbquery("SELECT * FROM ".DB_PERMALINK_REWRITE);
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

require_once INCLUDES."breadcrumbs.php";
require_once INCLUDES."header_includes.php";
require_once THEME."theme.php";
require_once THEMES."templates/render_functions.php";

// where did entypo go???
add_to_head("<link rel='stylesheet' href='".INCLUDES."font/font-awesome/css/font-awesome.min.css' type='text/css' />");

if (iMEMBER) {
	dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."'
		WHERE user_id='".$userdata['user_id']."'");
} 

$bootstrap_theme_css_src = '';
// Load bootstrap
if ($settings['bootstrap']) {
	define('BOOTSTRAPPED', TRUE);
	// ok now there is a theme at play here.
	// at maincore, lets load atom.
	$theme_name = isset($userdata['user_theme']) && $userdata['user_theme'] !== 'Default' ? $userdata['user_theme'] : fusion_get_settings('theme');
	$theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));
	$theme_css = INCLUDES.'bootstrap/bootstrap.min.css';
	if (!empty($theme_data)) {
		$theme_css = THEMES.$theme_data['theme_file'];
	}
	add_to_head("<link rel='stylesheet' href='".$theme_css."' type='text/css' />");
	add_to_footer("<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>");
	add_to_footer("<script type='text/javascript' src='".INCLUDES."bootstrap/holder.js'></script>");
}
require_once THEMES."templates/panels.php";
ob_start();