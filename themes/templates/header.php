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
$user_level = fusion_get_userdata("user_level");
if (fusion_get_settings("maintenance") == "1") {
    if (fusion_get_settings("maintenance_level") < $user_level or empty($user_level)) {
        if (fusion_get_settings("site_seo")) {
            redirect(FUSION_ROOT.BASEDIR."maintenance.php");
        } else {
            redirect(BASEDIR."maintenance.php");
        }
    }
}

if (fusion_get_settings("site_seo")) {
    $permalink = \PHPFusion\Rewrite\Permalinks::getPermalinkInstance();
}

require_once INCLUDES."breadcrumbs.php";
require_once INCLUDES."header_includes.php";
require_once THEME."theme.php";
require_once THEMES."templates/render_functions.php";

$o_param = [
    ':user_id'   => (iMEMBER ? fusion_get_userdata('user_id') : 0),
    ':online_ip' => USER_IP,
];
// Online users database -- to core level whether panel is on or not
if (dbcount("(online_user)", DB_ONLINE, "online_user=:user_id AND online_ip=:online_ip", $o_param)) {
    dbquery("UPDATE ".DB_ONLINE." SET online_lastactive='".TIME."', online_ip='".USER_IP."' WHERE ".(iMEMBER ? "online_user='".fusion_get_userdata('user_id')."'" : "online_user='0' AND online_ip='".USER_IP."'"));
} else {
    dbquery("INSERT INTO ".DB_ONLINE." (online_user, online_ip, online_ip_type, online_lastactive) VALUES ('".$o_param[':user_id']."', '".USER_IP."', '".USER_IP_TYPE."', '".TIME."')");
}
dbquery("DELETE FROM ".DB_ONLINE." WHERE online_lastactive < :last_time", [':last_time' => (TIME - 60)]);

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

ob_start();