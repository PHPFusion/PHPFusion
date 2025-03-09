<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: online_users_panel.php
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
defined('IN_FUSION') || exit;

require_once INFUSIONS.'online_users_panel/templates/online_users.tpl.php';

$result = dbquery("SELECT ton.online_user, tu.user_id, tu.user_name, tu.user_status
    FROM ".DB_ONLINE." ton
    LEFT JOIN ".DB_USERS." tu ON ton.online_user=tu.user_id
");

$guests = 0;
$members = [];

while ($data = dbarray($result)) {
    if ($data['online_user'] == "0") {
        $guests++;
    } else {
        $members[$data['user_id']] = [$data['user_id'], $data['user_name'], $data['user_status']];
    }
}

$newest = dbarray(dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_status='0' ORDER BY user_joined DESC LIMIT 0,1"));

$info = [
    'guests'              => $guests,
    'members'             => number_format(count($members)),
    'total_members'       => number_format(dbcount("(user_id)", DB_USERS, "user_status<='1'")),
    'online_members'      => '',
    'newest_member'       => profile_link($newest['user_id'], $newest['user_name'], $newest['user_status']),
    'unactivated_members' => ''
];

if (iADMIN && checkrights('M') && fusion_get_settings('admin_activation') == '1') {
    $info['unactivated_members'] = [
        'admin_link'    => ADMIN.'members.php'.fusion_get_aidlink().'&status=2',
        'total_members' => number_format(dbcount("(user_id)", DB_USERS, "user_status='2'"))
    ];
}

if (!empty($members)) {
    $info['online_members'] = implode(', ', array_map(function ($members) {
        return profile_link($members[0], $members[1], $members[2]);
    }, $members));
}

online_users_panel($info);
