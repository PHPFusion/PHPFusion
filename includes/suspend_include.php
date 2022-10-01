<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: suspend_include.php
| Author: Hans Kristian Flaatten (Starefossen)
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

/**
 * Get suspension.
 *
 * @param int  $type
 * @param bool $action
 *
 * @return mixed
 */
function getsuspension($type, $action = FALSE) {
    $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/members_include.php");

    $i = ($action ? 1 : 0);
    return $type > 8 ? $locale['susp_sys'] : $locale['susp'.$i.$type];
}

/**
 * Save suspendation to log.
 *
 * @param int    $user_id
 * @param int    $type
 * @param string $reason
 * @param false  $system
 * @param bool   $time
 */
function suspend_log($user_id, $type, $reason = "", $system = FALSE, $time = TRUE) {
    $userdata = fusion_get_userdata();
    $savesuspend = [
        'suspend_id'       => '',
        'suspended_user'   => $user_id,
        'suspending_admin' => (!$system ? $userdata['user_id'] : 0),
        'suspend_ip'       => (!$system ? USER_IP : 0),
        'suspend_ip_type'  => (!$system ? USER_IP_TYPE : 0),
        'suspend_date'     => ($time ? time() : 0),
        'suspend_reason'   => $reason,
        'suspend_type'     => $type
    ];

    dbquery_insert(DB_SUSPENDS, $savesuspend, 'save');
}

/**
 * Unsuspend user.
 *
 * @param int    $user_id
 * @param int    $type
 * @param string $reason
 * @param bool   $system
 */
function unsuspend_log($user_id, $type, $reason = "", $system = FALSE) {
    $userdata = fusion_get_userdata();

    $result = dbquery("SELECT suspend_id FROM ".DB_SUSPENDS." WHERE suspended_user=:userid AND suspend_type=:suspendtype AND reinstate_date=:reinstatedate LIMIT 1", [':userid' => $user_id, ':suspendtype' => $type, ':reinstatedate' => 0]);
    if (!dbrows($result)) {
        suspend_log($user_id, $type, "", TRUE, FALSE);
    }

    dbquery("UPDATE ".DB_SUSPENDS." SET
        reinstating_admin='".(!$system ? $userdata['user_id'] : 0)."',
        reinstate_reason='".$reason."',
        reinstate_date='".time()."',
        reinstate_ip='".(!$system ? USER_IP : 0)."',
        reinstate_ip_type='".(!$system ? USER_IP_TYPE : 0)."'
        WHERE suspended_user='".$user_id."' AND suspend_type='".$type."' AND reinstate_date='0'
    ");
}

/**
 * Display suspend log.
 *
 * @param int    $user_id
 * @param string $type
 * @param int    $rowstart
 * @param int    $limit
 */
function display_suspend_log($user_id, $type = "all", $rowstart = 0, $limit = 0) {
    $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/members_include.php");

    $db_type = ($type != "all" && isnum($type) ? " AND suspend_type='".$type."'" : "");

    $result = dbquery("SELECT sp.suspend_id, sp.suspend_ip, sp.suspend_ip_type, sp.suspend_date, sp.suspend_reason,
        sp.suspend_type, sp.reinstate_date, sp.reinstate_reason, sp.reinstate_ip, sp.reinstate_ip_type,
        a.user_name AS admin_name, b.user_name AS admin_name_b
        FROM ".DB_SUSPENDS." AS sp
        LEFT JOIN ".DB_USERS." AS a ON sp.suspending_admin=a.user_id
        LEFT JOIN ".DB_USERS." AS b ON sp.reinstating_admin=b.user_id
        WHERE suspended_user=:userid$db_type
        ORDER BY suspend_date DESC".($limit > 0 ? " LIMIT $limit" : ""), [':userid' => $user_id]);
    $rows = dbrows($result);
    $udata = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id=:userid LIMIT 1", [':userid' => $user_id]));

    if ($type == "all") {
        opentable(sprintf($locale['susp100'], $udata['user_name']));
        member_nav(member_url("view", $user_id)."|".$udata['user_name'], member_url("log", $user_id)."|".$locale['susp114']);
    } else {
        opentable(sprintf($locale['susp100b'], getsuspension($type, TRUE), $udata['user_name']));
    }

    if ($rows) {
        echo "<div class='row'>";
        echo "<div class='col-xs-12 col-sm-1 content'>".$locale['susp103']."</div>";
        echo "<div class='col-xs-12 col-sm-2 content'>".$locale['susp104']."</div>";
        echo "<div class='col-xs-12 col-sm-6 content'>".$locale['susp105']."</div>";
        echo "<div class='col-xs-12 col-sm-3 content'>".$locale['susp106']."</div>";
        echo "</div>";
        while ($data = dbarray($result)) {

            $suspension = ($data['suspend_type'] != 2 ? getsuspension($data['suspend_type']) : $locale['susp111']);
            $reason = ($data['suspend_reason'] ? ": ".$data['suspend_reason'] : "");
            $admin = ($data['admin_name'] ? $data['admin_name']." (".$locale['susp108'].": ".$data['suspend_ip'].")" : $locale['susp109']);
            echo "<div class='row'><div class='item'>";
            echo "<div class='col-xs-12 col-sm-1'>#".$data['suspend_id']."</div>";
            echo "<div class='col-xs-12 col-sm-2'>".showdate('forumdate', $data['suspend_date'])."</div>";
            echo "<div class='col-xs-12 col-sm-6'><strong>".$suspension."</strong>".$reason."</div>";
            echo "<div class='col-xs-12 col-sm-3'>".$admin."</div>";
            echo "</div></div>";

            if ($data['reinstate_date']) {
                $r_reason = ($data['reinstate_reason'] ? ": ".$data['reinstate_reason'] : "");
                $admin = ($data['admin_name_b'] ? $data['admin_name_b']." (".$locale['susp112'].$data['reinstate_ip'].")" : $locale['susp109']);
                echo "<div class='row m-b-10'>";
                echo "<div class='col-xs-12 col-sm-1'>&nbsp;</div>";
                echo "<div class='col-xs-12 col-sm-2'>".showdate('forumdate', $data['reinstate_date'])."</div>";
                echo "<div class='col-xs-12 col-sm-6'>".$locale['susp113'].$r_reason."</div>";
                echo "<div class='col-xs-12 col-sm-3'>".$admin."</div>";
                echo "</div>";
            }
        }
    } else {
        echo "<div class='well text-center'>".$locale['susp110']."</div>\n";
    }
    closetable();
}

/**
 * @param string $second
 * @param string $third
 */
function member_nav($second = "", $third = "") {
    $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/members_include.php");

    echo "<div class='breadcrumb'>\n";
    echo "<li class='crumb'>\n";
    echo "<a href='".FUSION_SELF.fusion_get_aidlink()."'>".$locale['susp115']."</a>\n";
    echo "</li>";

    if ($second && $second = explode("|", $second)) {
        echo "<li class='crumb'><a href='".$second[0]."'>".$second[1]."</a>\n</li>\n";
    }
    if ($third && $third = explode("|", $third)) {
        echo "<li class='crumb'>".$third[1]."</li>\n";
    }
    echo "</div>\n";
}

/**
 * @param $step
 * @param $user_id
 *
 * @return string
 */
function member_url($step, $user_id) {
    return FUSION_SELF.fusion_get_aidlink()."&ref=".$step.($user_id ? "&lookup=$user_id" : "");
}
