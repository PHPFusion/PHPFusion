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
defined('IN_FUSION') || exit;

$tpl = \PHPFusion\Template::getInstance('online_users_panel');
$tpl->set_template(__DIR__.'/templates/online_user_panel.html');
$tpl->set_locale(fusion_get_locale());
$tpl->set_tag('openside', fusion_get_function('openside', fusion_get_locale('global_010')));
$tpl->set_tag('closeside', fusion_get_function('closeside'));

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
        // Fix doubling - insert unique key into array
        $members[$data['user_id']] = [$data['user_id'], $data['user_name'], $data['user_status']];
    }
}
$tpl->set_tag('guest', $guests);
$tpl->set_tag('members', number_format(count($members), 0));
$tpl->set_tag('total_members', number_format(dbcount("(user_id)", DB_USERS, "user_status<='1'"), 0));
if (!empty($members)) {
    $profile_link = implode(', ', array_map(function ($members) {
        return profile_link($members[0], $members[1], $members[2]);
    }, $members));
    //echo $profile_link;
    $tpl->set_block('user_link', ['plink' => $profile_link]);
}
if (iADMIN && checkrights("M") && fusion_get_settings("admin_activation") == "1") {
    $tpl->set_block('unactivated_members', [
            'admin_link'    => ADMIN."members.php".fusion_get_aidlink()."&amp;status=2",
            'total_members' => number_format(dbcount("(user_id)", DB_USERS, "user_status='2'"), 0)]
    );
}
$data = dbarray(dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_status='0' ORDER BY user_joined DESC LIMIT 0,1"));
$tpl->set_tag('latest_member_profile_link', profile_link($data['user_id'], $data['user_name'], $data['user_status']));

echo $tpl->get_output();
