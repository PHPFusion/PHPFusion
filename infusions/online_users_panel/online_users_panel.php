<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: online_users_panel.php
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

$locale = fusion_get_locale();

openside("<i class='fa fa-clock-o fa-fw'></i> ".$locale['global_010']);

$user_online_query = "
SELECT ton.online_user, tu.user_id, tu.user_name, tu.user_status FROM ".DB_ONLINE." ton
LEFT JOIN ".DB_USERS." tu ON ton.online_user=tu.user_id
";

$result = dbquery($user_online_query);

$guests = 0;
$members = array();
while ($data = dbarray($result)) {
    if ($data['online_user'] == "0") {
        $guests++;
    } else {
        $members[] = array($data['user_id'], $data['user_name'], $data['user_status']);
    }
}

echo "<strong>".$locale['global_011'].":</strong> ".$guests."<br /><br />\n";
echo "<strong>".$locale['global_012'].":</strong> ".count($members)."<br />\n";

if (count($members)) {
    $i = 1;
    while (list($key, $member) = each($members)) {
        echo "<span class='side'>".profile_link($member[0], $member[1], $member[2])."</span>";
        if ($i != count($members)) {
            echo ",\n";
        } else {
            echo "<br />\n";
        }
        $i++;
    }
}
echo "<br />\n".THEME_BULLET." ".$locale['global_014'].": ".number_format(dbcount("(user_id)", DB_USERS, "user_status<='1'"))."<br />\n";

if (iADMIN && checkrights("M") && fusion_get_settings("admin_activation") == "1") {
    echo THEME_BULLET." <a href='".ADMIN."members.php".fusion_get_aidlink()."&amp;status=2' class='side'>".$locale['global_015']."</a>: ";
    echo dbcount("(user_id)", DB_USERS, "user_status='2'")."<br />\n";
}

$data = dbarray(dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_status='0' ORDER BY user_joined DESC LIMIT 0,1"));
echo THEME_BULLET." ".$locale['global_016'].": <span class='side'>".profile_link($data['user_id'], $data['user_name'],
                                                                                 $data['user_status'])."</span>\n";
closeside();