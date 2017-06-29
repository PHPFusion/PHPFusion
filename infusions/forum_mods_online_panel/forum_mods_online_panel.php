<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_mods_online_panel.php
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

if (file_exists(INFUSIONS."forum_mods_online_panel/locale/".LANGUAGE.".php")) {
    $locale_path = INFUSIONS."forum_mods_online_panel/locale/".LANGUAGE.".php";
} else {
    $locale_path = INFUSIONS."forum_mods_online_panel/locale/English.php";
}
$locale = fusion_get_locale("", $locale_path);

if (!infusion_exists('forum')) {
	$info['no_forum'] = $locale['fmp_0103'];
} else {
	include_once INFUSIONS."forum_mods_online_panel/templates.php";
    $moderator_groups = array();
    $mod_group = array();
    $group_sql = "";
    $forum_mods_query = "SELECT forum_id, forum_mods FROM ".DB_FORUMS." WHERE forum_type=2 OR forum_type=3";
    $forum_mods_result = dbquery($forum_mods_query);
    if (dbrows($forum_mods_result) > 0) {
        while ($mods = dbarray($forum_mods_result)) {
            if (!empty($mods['forum_mods'])) {
                $mod_groups = explode(".", $mods['forum_mods']);
                foreach ($mod_groups as $mod_group) {
                    if (checkgroup($mod_group)) {
                        $moderator_groups[$mod_group] = getgroupname($mod_group);
                    }
                }
            }
        }
        $group_sql = "(user_level <= ".iMEMBER." AND user_groups !='') OR ";
        $mod_group = array_flip($moderator_groups);
    }

    $user_column_select = "u.user_id, u.user_name, u.user_avatar, u.user_status, u.user_level, u.user_groups";

    $site_admins_query = "SELECT $user_column_select FROM ".DB_USERS." u
    INNER JOIN ".DB_ONLINE." online ON online.online_user = u.user_id
    WHERE $group_sql (user_level <= ".USER_LEVEL_ADMIN.") GROUP BY user_id ASC";

    $site_admin_result = dbquery($site_admins_query);

    if (dbrows($site_admin_result) > 0) {
    $info['admin']['openside'] = "<i class='fa fa-legal fa-fw'></i> ".$locale['fmp_0100'];
    $info['member']['openside'] = "<i class='fa fa-legal fa-fw'></i> ".$locale['fmp_0102'];
        while ($user = dbarray($site_admin_result)) {
            $current_user_groups = array_flip(explode(".", $user['user_groups']));
            if ($user['user_level'] <= USER_LEVEL_ADMIN) {
            	$output['user_title'] = $locale['fmp_0101'];
            	$output['user_avatar'] = display_avatar($user, "35px", "", TRUE, "img-rounded m-r-5");
            	$output['user_profil'] = profile_link($user['user_id'], ucfirst($user['user_name']), $user['user_status']);
                $info['admin']['item'][] = $output;
            } elseif ($key = array_intersect_key($moderator_groups, $current_user_groups)) {
            	$output['user_title'] = reset($key);
            	$output['user_avatar'] = display_avatar($user, "35px", "", TRUE, "img-rounded m-r-5");
            	$output['user_profil'] = profile_link($user['user_id'], ucfirst($user['user_name']), $user['user_status']);
                $info['member']['item'][] = $output;
            }
        }
	render_forum_mods($info);
    }
}
