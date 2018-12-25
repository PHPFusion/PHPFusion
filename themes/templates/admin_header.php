<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_header.php
| Author: PHP-Fusion Development Team
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

// Check if Maintenance is Enabled
if ($settings['maintenance'] == "1" && ((iADMIN && $settings['maintenance_level'] == "1" 
	&& $userdata['user_id'] != "1") || ($settings['maintenance_level'] > $userdata['user_level'])
)) { 
	redirect(BASEDIR."maintenance.php");
 }

require_once CLASSES."PHPFusion/Admins.inc";
require_once INCLUDES."output_handling_include.php";
require_once INCLUDES."breadcrumbs.php";
require_once THEMES."templates/render_functions.php";
require_once INCLUDES."header_includes.php";

if (preg_match("/^([a-z0-9_-]){2,50}$/i", $settings['admin_theme']) && file_exists(THEMES."admin_themes/".$settings['admin_theme']."/acp_theme.php")) {
    require_once THEMES."admin_themes/".$settings['admin_theme']."/acp_theme.php";
} else {
    die('WARNING: Invalid Admin Panel Theme');
}

if (iMEMBER) {
	$result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."' WHERE user_id='".$userdata['user_id']."'");
}

\PHPFusion\Admins::getInstance()->setAdmin();

ob_start();