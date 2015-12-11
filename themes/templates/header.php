<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: header.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Check if Maintenance is Enabled
if (fusion_get_settings("maintenance") == "1" &&
    ((iMEMBER && fusion_get_settings("maintenance_level") == USER_LEVEL_MEMBER && $userdata['user_id'] != "1") ||
        (fusion_get_settings("maintenance_level") < $userdata['user_level']))) {
    if (fusion_get_settings("site_seo")) {
        redirect(FUSION_ROOT.BASEDIR."maintenance.php");
    } else {
        redirect(BASEDIR."maintenance.php");
    }
}

if (fusion_get_settings("site_seo") == 1) {
    $permalink = \PHPFusion\Rewrite\Permalinks::getInstance();
}

require_once INCLUDES."breadcrumbs.php";

require_once INCLUDES."header_includes.php";

require_once THEME."theme.php";

require_once THEMES."templates/render_functions.php";

if (iMEMBER) {
	dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."' WHERE user_id='".$userdata['user_id']."'");
}
ob_start();

require_once THEMES."templates/panels.php";