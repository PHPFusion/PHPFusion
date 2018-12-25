<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: acp_theme.php
| Author: PHP-Fusion Inc.
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

define("THEME_BULLET", "<img src='".THEME."images/bullet.gif' class='bullet' alt='&raquo;' border='0' />");
define('BOOTSTRAP', TRUE);
define('ENTYPO', TRUE);

// Uncomment to enable/disable styles

// Enable Fontawesome
// define('FONTAWESOME', TRUE);

// Disable Load CCS From your current theme.
// define('NO_THEME_CSS', TRUE);

// Disable Load Default CCS
// define('NO_DEFAULT_CSS', TRUE);

// Disable Load Global CCS
// define('NO_GLOBAL_CSS', TRUE);

require_once INCLUDES."theme_functions_include.php";
require_once THEMES."admin_themes/Venus/includes/functions.php";

// Post password check
if (iADMIN && $userdata['user_admin_password']) {
	if (isset($_POST['admin_password'])) {
		$login_error = $locale['global_182'];
		$admin_password = stripinput($_POST['admin_password']);
		if (!defined("FUSION_NULL")) {
			set_admin_pass($admin_password);
			redirect(FUSION_SELF.$aidlink."&amp;pagenum=0");
		}
	}
} 

\PHPFusion\Admins::getInstance()->setAdminBreadcrumbs();
