<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: header.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Rewrite\Permalinks;

defined('IN_FUSION') || exit;

$settings = fusion_get_settings();
$userdata = fusion_get_userdata();

// Check if Maintenance is Enabled
if ($settings['maintenance'] == "1" &&
    ((iMEMBER && $settings['maintenance_level'] == USER_LEVEL_MEMBER && $userdata['user_id'] != "1") ||
        ($settings['maintenance_level'] < $userdata['user_level']))
) {
    if ($settings['site_seo']) {
        redirect(FUSION_ROOT.BASEDIR."maintenance.php");
    } else {
        redirect(BASEDIR."maintenance.php");
    }
}

if ($settings['site_seo']) {
    $permalink = Permalinks::getPermalinkInstance();
}

require_once INCLUDES."breadcrumbs.php";
if (file_exists(INCLUDES."header_includes.php")) {
    require_once INCLUDES."header_includes.php";
}

require_once THEME."theme.php";

require_once INCLUDES."deprecated.php";

require_once INCLUDES."theme_functions_include.php";

require_once INCLUDES."twig_includes.php";

// for compatibility
if (!defined('THEME_BULLET')) {
    define('THEME_BULLET', '&middot;');
}

$o_param = [
    ':user_id'   => (iMEMBER ? $userdata['user_id'] : 0),
    ':online_ip' => USER_IP,
];
// Online users database -- to core level whether panel is on or not
if (dbcount("(online_user)", DB_ONLINE, "online_user=:user_id AND online_ip=:online_ip", $o_param)) {
    dbquery("UPDATE ".DB_ONLINE." SET online_lastactive='".time()."', online_ip='".USER_IP."' WHERE ".(iMEMBER ? "online_user='".$userdata['user_id']."'" : "online_user='0' AND online_ip='".USER_IP."'"));
} else {
    dbquery("INSERT INTO ".DB_ONLINE." (online_user, online_ip, online_ip_type, online_lastactive) VALUES ('".$o_param[':user_id']."', '".USER_IP."', '".USER_IP_TYPE."', '".time()."')");
}
dbquery("DELETE FROM ".DB_ONLINE." WHERE online_lastactive < :last_time", [':last_time' => (time() - 60)]);

if (iMEMBER) {
    $result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit=:time, user_ip=:ip, user_ip_type=:ip_type WHERE user_id=:user_id",
        [
            ':time'    => time(),
            ':ip'      => USER_IP,
            ':ip_type' => USER_IP_TYPE,
            ':user_id' => $userdata['user_id']
        ]
    );
}

ob_start();
