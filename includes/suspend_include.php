<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."admin/members_include.php";

$time_overdue = time() - (86400 * $settings['deactivation_period']);
$response_required = time() + (86400 * $settings['deactivation_response']);

$steps_arr = array(
	"activate", 
	"add", 
	"anonymise", 
	"ban", 
	"cancel", 
	"deactivate", 
	"delete", 
	"edit", 
	"log", 
	"sban", 
	"suspend", 
	"view"
);

function getsuspension($type, $action = false) {
	global $locale;
	
	$i = ($action ? 1 : 0);
	
	if ($type == 0) { return $locale['susp'.$i.'0']; 
	} elseif ($type == 1) { return $locale['susp'.$i.'1'];
	} elseif ($type == 2) { return $locale['susp'.$i.'2'];
	} elseif ($type == 3) { return $locale['susp'.$i.'3'];
	} elseif ($type == 4) { return $locale['susp'.$i.'4'];
	} elseif ($type == 5) { return $locale['susp'.$i.'5'];
	} elseif ($type == 6) { return $locale['susp'.$i.'6'];
	} elseif ($type == 7) { return $locale['susp'.$i.'7']; 
	} elseif ($type == 8) { return $locale['susp'.$i.'8']; 
	} else { return $locale['susp_sys']; }
}

function suspend_log($user_id, $type, $reason = "", $system = false, $time = true) {
	global $userdata;
	
	$result = dbquery(
		"INSERT INTO ".DB_SUSPENDS." (
			suspended_user, 
			suspending_admin, 
			suspend_ip, 
			suspend_ip_type, 
			suspend_date, 
			suspend_reason, 
			suspend_type
		) VALUES (
			'$user_id', 
			'".(!$system ? $userdata['user_id'] : 0)."', 
			'".(!$system ? USER_IP : 0)."', 
			'".(!$system ? USER_IP_TYPE : 0)."', 
			'".($time ? time() : 0)."', 
			'$reason',
			'$type'
		)"
	);
}

function unsuspend_log($user_id, $type, $reason = "", $system = false) {
	global $userdata;
	
	// Pre v7.01 check
	$result = dbquery(
		"SELECT suspend_id FROM ".DB_SUSPENDS." 
		WHERE suspended_user='$user_id' AND suspend_type='$type' AND reinstate_date='0'
		LIMIT 1"
	);
	if (!dbrows($result)) { suspend_log($user_id, $type, "", true, false); }
	$result = dbquery(
		"UPDATE ".DB_SUSPENDS." SET 
			reinstating_admin='".(!$system ? $userdata['user_id'] : 0)."', 
			reinstate_reason='$reason', 
			reinstate_date='".time()."', 
			reinstate_ip='".(!$system ? USER_IP : 0)."',
			reinstate_ip_type='".(!$system ? USER_IP_TYPE : 0)."'			
		WHERE 
			suspended_user='$user_id' AND suspend_type='$type' AND reinstate_date='0'"
	);
}

function display_suspend_log($user_id, $type = "all", $rowstart = 0, $limit = 0) {
	global $locale;
	
	$db_type = ($type != "all" && isnum($type) ? " AND suspend_type='$type'" : "");
	
	$rows = dbcount("(suspend_id)", DB_SUSPENDS, "suspended_user='$user_id'$db_type");
	$result = dbquery(
		"SELECT sp.suspend_id, sp.suspend_ip, sp.suspend_ip_type, sp.suspend_date, sp.suspend_reason,
		sp.suspend_type, sp.reinstate_date, sp.reinstate_reason, sp.reinstate_ip, sp.reinstate_ip_type,
		a.user_name AS admin_name, b.user_name AS admin_name_b 
		FROM ".DB_SUSPENDS." sp 
		LEFT JOIN ".DB_USERS." a ON sp.suspending_admin=a.user_id 
		LEFT JOIN ".DB_USERS." b ON sp.reinstating_admin=b.user_id 
		WHERE suspended_user='$user_id'$db_type
		ORDER BY suspend_date DESC"
		.($limit > 0 ? " LIMIT $limit" : "")
	);
	$rows = dbrows($result);
	$udata = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='$user_id' LIMIT 1"));
	if ($type == "all") {
		opentable(sprintf($locale['susp100'], $udata['user_name']));
		member_nav(member_url("view", $user_id)."|".$udata['user_name'], member_url("log", $user_id)."|".$locale['susp114']);
	} else {
		opentable(sprintf($locale['susp100b'], getsuspension($type, true), $udata['user_name']));
	}
	if ($rows) {
		echo "<table width='80%' cellpadding='1' cellspacing='0' class='tbl-border center'>\n<tr>\n";
		if ($type == "all") {
			$description = sprintf($locale['susp101'], $udata['user_name']);
		} else {
			$description = sprintf($locale['susp102'], getsuspension($type), $udata['user_name']);
		}
		echo "<td class='tbl2' width='30'>".$locale['susp103']."</td>\n";
		echo "<td class='tbl2' width='120'>".$locale['susp104']."</td>\n";
		echo "<td class='tbl2' width='250'>".$locale['susp105']."</td>\n";
		echo "<td class='tbl2' width='150'>".$locale['susp106']."</td>\n";
		echo "</tr>\n";
		$i = 1;
		while ($data = dbarray($result)) {
			$class = "tbl$i";
			$suspension = ($data['suspend_type'] != 2 ? getsuspension($data['suspend_type']) : $locale['susp111']);
			$reason = ($data['suspend_reason'] ? ": ".$data['suspend_reason'] : "");
			$admin = ($data['admin_name'] ? $data['admin_name']." (".$locale['susp108'].": ".$data['suspend_ip'].")" : $locale['susp109']);
			echo "<tr><td class='$class' valign='top'>#".$data['suspend_id']."</td>\n";
			echo "<td class='$class' valign='top'>".showdate('forumdate', $data['suspend_date'])."</td>\n";
			echo "<td class='$class' valign='top'><strong>$suspension</strong>$reason</td>\n";
			echo "<td class='$class' valign='top'>$admin</td>\n";
			echo "</tr>\n<tr>\n";
			if ($data['reinstate_date']) {
				$r_reason = ($data['reinstate_reason'] ? ": ".$data['reinstate_reason'] : "");
				$admin = ($data['admin_name_b'] ? $data['admin_name_b']." (".$locale['susp112'].$data['reinstate_ip'].")" : $locale['susp109']);
				echo "<td class='$class' valign='top' align='right'>&nbsp;</td>\n";
				echo "<td class='$class' valign='top'>".showdate('forumdate', $data['reinstate_date'])."</td>\n";
				echo "<td class='$class' valign='top'>".$locale['susp113'].$r_reason."</td>\n";
				echo "<td class='$class' valign='top'>$admin</td>\n";
				echo "</tr>\n<tr>\n";
			} elseif ($data['suspend_type'] != 2) {
				echo "<td class='$class' valign='top' align='right'>&nbsp;</td>\n";
				echo "<td class='$class' valign='top'>&nbsp;</td>\n";
				echo "<td class='$class' valign='top'>&nbsp;</td>\n";
				echo "<td class='$class' valign='top'>&nbsp;</td>\n";
				echo "</tr>\n<tr>\n";
			}
			echo "<td class='tbl1' colspan='4'><hr /></td>\n";
			echo "</tr>\n";
			if ($i == 2 ? $i = 1 : $i++);
		}
		echo "</table>\n";
	} else {
		echo "<div id='close-message'><div class='admin-message'>".$locale['susp110']."</div></div>\n";
	}
	closetable();
}

function member_nav($second = "", $third = "") {
	global $locale, $aidlink;
	
	echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
	echo "<td class='tbl2'>\n";
	echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['susp115']."</a>\n";
	if ($second && $second = explode("|", $second)) { echo " &gt; <a href='".$second[0]."'>".$second[1]."</a>\n"; }
	if ($third && $third = explode("|", $third)) { echo " &gt; <a href='".$third[0]."'>".$third[1]."</a>\n"; }
	echo "</td>\n</tr>\n</table>\n";
	echo "<div style='margin:5px'></div>\n";
}

function member_url($step, $user_id) {
	global $aidlink;
	
	return FUSION_SELF.$aidlink."&amp;step=".$step.($user_id ? "&amp;user_id=$user_id" : "");
}

?>