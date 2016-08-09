<?php

if (file_exists(INFUSIONS."forum_mods_online_panel/locale/".LANGUAGE.".php")) {
    $locale_path = INFUSIONS."forum_mods_online_panel/locale/".LANGUAGE.".php";
} else {
    $locale_path = INFUSIONS."forum_mods_online_panel/locale/English.php";
}
$locale = fusion_get_locale("", $locale_path);

if (!db_exists(DB_FORUMS)) {
    echo $locale['fmp_0103'];
} else {

    $moderator_groups = array();
    $mod_group = array();
    $group_sql = "";
    $forum_mods_query = "SELECT forum_id, forum_mods FROM ".DB_FORUMS." WHERE forum_type=2 OR forum_type=3";
    $forum_mods_result = dbquery($forum_mods_query);
    if (dbrows( $forum_mods_result ) > 0) {
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

    $admin_user = array();
    $member_user = array();

    $user_column_select = "u.user_id, u.user_name, u.user_avatar, u.user_status, u.user_level, u.user_groups";

    $site_admins_query = "SELECT $user_column_select FROM ".DB_USERS." u
INNER JOIN ".DB_ONLINE." online ON online.online_user = u.user_id
WHERE $group_sql (user_level <= ".USER_LEVEL_ADMIN.") GROUP BY user_id ASC";

    $site_admin_result = dbquery( $site_admins_query );

    if (dbrows( $site_admin_result ) > 0) {
        while ($user = dbarray($site_admin_result)) {
            $current_user_groups = array_flip( explode(".", $user['user_groups']) );
            if ($user['user_level'] <= USER_LEVEL_ADMIN) {
                $user['user_title'] = $locale['fmp_0101'];
                $admin_user[$user['user_id']] = $user;
            } elseif ($key = array_intersect_key($moderator_groups, $current_user_groups)) {
                $user['user_title'] = reset($key);
                $member_user[$user['user_id']] = $user;
            }
        }
    }

    if (!empty($admin_user)) {
        openside("<i class='fa fa-legal fa-fw'></i> ".$locale['fmp_0100']);
        ?>
        <ul>
            <?php foreach($admin_user as $user_id => $user_data) : ?>
                <li>
                    <div class="pull-left m-t-5">
                        <?php echo display_avatar($user_data, "35px", "", TRUE, "img-rounded") ?>
                    </div>
                    <div class="overflow-hide">
                        <div class="display-block strong"><?php echo profile_link($user_data['user_id'], ucfirst($user_data['user_name']), $user_data['user_status']) ?></div>
                        <span class="text-lighter"><?php echo $user_data['user_title'] ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        closeside();
    }

    if (!empty($member_user)) {
        openside("<i class='fa fa-legal fa-fw'></i> ".$locale['fmp_0102']);
        ?>
        <ul>
            <?php foreach($member_user as $user_id => $user_data) : ?>
                <li>
                    <div class="pull-left m-t-5">
                        <?php echo display_avatar($user_data, "35px", "", TRUE, "img-rounded") ?>
                    </div>
                    <div class="overflow-hide">
                        <div class="display-block strong"><?php echo profile_link($user_data['user_id'], ucfirst($user_data['user_name']), $user_data['user_status']) ?></div>
                        <span class="text-lighter"><?php echo $user_data['user_title'] ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        closeside();
    }

}