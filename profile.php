<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: profile.php
| Author: Hans Kristian Flaatten {Starefossen}
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."user_fields.php";

$settings = fusion_get_settings();

if (!iMEMBER && $settings['hide_userprofiles'] == 1) {
    redirect(BASEDIR."index.php");
}

require_once THEMES."templates/global/profile.php";

if (isset($_GET['lookup']) && isnum($_GET['lookup'])) {
    $user_status = " AND (user_status='0' OR user_status='3' OR user_status='7')";
    if (iADMIN) {
        $user_status = "";
    }
    $user_data = array();
    $result = dbquery("SELECT u.*, s.suspend_reason
		FROM ".DB_USERS." u
		LEFT JOIN ".DB_SUSPENDS." s ON u.user_id=s.suspended_user
		WHERE user_id='".$_GET['lookup']."'".$user_status."
		ORDER BY suspend_date DESC
		LIMIT 1");
    if (dbrows($result)) {
        $user_data = dbarray($result);
    } else {
        redirect("index.php");
    }

    set_title($user_data['user_name'].$locale['global_200'].$locale['u103']);

    if (iADMIN && checkrights("UG") && $_GET['lookup'] != $user_data['user_id']) {
        if ((isset($_POST['add_to_group'])) && (isset($_POST['user_group']) && isnum($_POST['user_group']))) {
            if (!preg_match("(^\.{$_POST['user_group']}$|\.{$_POST['user_group']}\.|\.{$_POST['user_group']}$)", $user_data['user_groups'])) {
                $result = dbquery("UPDATE ".DB_USERS." SET user_groups='".$user_data['user_groups'].".".$_POST['user_group']."' WHERE user_id='".$_GET['lookup']."'");
            }
            redirect(FUSION_SELF."?lookup=".$_GET['lookup']);
        }
    }
    $userFields = new PHPFusion\UserFields();
    $userFields->userData = $user_data;
    $userFields->showAdminOptions = TRUE;
    $userFields->method = 'display';
    $userFields->plugin_folder = INCLUDES."user_fields/";
    $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";

    $info = $userFields->get_profile_output();
    render_userprofile($info);

} elseif (isset($_GET['group_id']) && isnum($_GET['group_id'])) {

    $_GET['rowstart'] = (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) ? 0 : $_GET['rowstart'];
    // Need to MV this part.
    $result = dbquery("SELECT group_id, group_name, group_icon
       FROM ".DB_USER_GROUPS."
       WHERE group_id='".$_GET['group_id']."'");
    if (dbrows($result)) {
        $data = dbarray($result);
        $rows = dbcount("(user_id)", DB_USERS,
                        (iADMIN ? "user_status>='0'" : "user_status='0'")." AND user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')");

        $result0 = dbquery("SELECT user_id, user_name, user_level, user_status, user_language, user_joined, user_avatar
         FROM ".DB_USERS."
         WHERE ".(iADMIN ? "user_status>='0'" : "user_status='0'")." AND user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')
         ORDER BY user_level DESC, user_name ASC
         LIMIT ".intval($_GET['rowstart']).",20");

        $user_group_title['title'][] = $data['group_name']." ".format_word($rows, $locale['fmt_member']);
        $user_group_title['id'][] = 'group';
        $user_group_title['icon'][] = $data['group_icon'];

        opentable("<i class='fa fa-group m-r-10'></i>".$locale['u110']);

        echo opentab($user_group_title, 'group', "user_group", FALSE);
        if (dbrows($result0)) {
            echo "<table id='unread_tbl' class='table table-responsive table-hover'>\n";
            echo "<tr>\n";
            echo "<td class='col-xs-1'>".$locale['u062']."</td>\n";
            echo "<td class='col-xs-1'>".$locale['u113']."</td>\n";
            echo "<td class='col-xs-1'>".$locale['u114']."</td>\n";
            echo "<td class='col-xs-1'>".$locale['u115']."</td>\n";
            echo "<td class='col-xs-1'>".$locale['status']."</td>\n";
            echo "</tr>\n";
            while ($data1 = dbarray($result0)) {
                echo "<tr>\n";
                echo "<td class='col-xs-1'>".display_avatar($data1, '50px', '', FALSE, 'img-rounded')."</td>\n";
                echo "<td class='col-xs-1'>".profile_link($data1['user_id'], $data1['user_name'], $data1['user_status'])."</td>\n";
                echo "<td class='col-xs-1'>".getuserlevel($data1['user_level'])."</td>\n";
                echo "<td class='col-xs-1'>".translate_lang_names($data1['user_language'])."</td>\n";
                echo "<td class='col-xs-1'>".getuserstatus($data1['user_status'])."</td>\n";
                echo "</tr>\n";
            }
        }
        echo "</table>\n";

        echo closetab();

        echo $rows > 20 ? "<div class='pull-right m-r-10'>".makepagenav($_GET['rowstart'], 20, $rows, 3,
                                                                        FUSION_SELF."?group_id=".$data['group_id']."&amp;")."</div>\n" : "";

        closetable();
    } else {
        redirect("index.php");
    }
} else {
    redirect(BASEDIR."index.php");
}
require_once THEMES."templates/footer.php";
