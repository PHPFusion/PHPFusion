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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

define("ADMIN_PANEL", TRUE);
$settings = fusion_get_settings();
$locale = fusion_get_locale();

if ($settings['maintenance'] == "1" && ((iMEMBER && $settings['maintenance_level'] == USER_LEVEL_MEMBER && $userdata['user_id'] != "1") || ($settings['maintenance_level'] < $userdata['user_level']))) {
    redirect(BASEDIR."maintenance.php");
}

require_once INCLUDES."breadcrumbs.php";
require_once INCLUDES."header_includes.php";
require_once THEMES."templates/render_functions.php";

if (preg_match("/^([a-z0-9_-]){2,50}$/i",
               $settings['admin_theme']) && file_exists(THEMES."admin_themes/".$settings['admin_theme']."/acp_theme.php")
) {
    require_once THEMES."admin_themes/".$settings['admin_theme']."/acp_theme.php";
} else {
    die('WARNING: Invalid Admin Panel Theme'); // TODO: improve this
}

if (iMEMBER) {
    $result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit=:time, user_ip=:ip, user_ip_type=:ip_type WHERE user_id=:user_id",
        [
            ':time'    => TIME,
            ':ip'      => USER_IP,
            ':ip_type' => USER_IP_TYPE,
            ':user_id' => fusion_get_userdata('user_id')
        ]
    );
}

$bootstrap_theme_css_src = '';
// Load bootstrap
if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    define('BOOTSTRAPPED', TRUE);
    $bootstrap_theme_css_src = INCLUDES."bootstrap/bootstrap.min.css";
    add_to_footer("<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>");
    add_to_footer("<script type='text/javascript' src='".INCLUDES."bootstrap/holder.min.js'></script>");
}

ob_start();

@list($title) = dbarraynum(dbquery("SELECT admin_title FROM ".DB_ADMIN." WHERE admin_link=:base_url", array(':base_url' => FUSION_SELF)));
\PHPFusion\OutputHandler::setTitle($GLOBALS['locale']['global_123'].$GLOBALS['locale']['global_201'].($title ? $title.$GLOBALS['locale']['global_201'] : ""));

// If the user is not logged in as admin then don't parse the administration page
// otherwise it could result in bypass of the admin password and one could do
// changes to the system settings without even being logged into Admin Panel.
// After relogin the user can simply click back in browser and their input will
// still be there so nothing is lost
if (!check_admin_pass('')) {
    // If not admin, also must check if user_id is exist due to session time out.
    $user_id = fusion_get_userdata("user_id");
    if (empty($user_id)) {
        redirect(BASEDIR."index.php");
    }
    require_once "footer.php";
    exit;
}
