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
require_once CLASSES."UserFields.class.php";
include LOCALE.LOCALESET."user_fields.php";

if (!iMEMBER && $settings['hide_userprofiles'] == 1) { redirect(BASEDIR."login.php"); }

if (isset($_GET['lookup']) && isnum($_GET['lookup'])) {
	$user_status = " AND (user_status='0' OR user_status='3' OR user_status='7')";
	if (iADMIN) {
		$user_status = "";
	}
	$result = dbquery(
		"SELECT u.*, s.suspend_reason
		FROM ".DB_USERS." u
		LEFT JOIN ".DB_SUSPENDS." s ON u.user_id=s.suspended_user
		WHERE user_id='".$_GET['lookup']."'".$user_status."
		ORDER BY suspend_date DESC
		LIMIT 1"
	);

	if (dbrows($result)) { $user_data = dbarray($result); } else { redirect("index.php"); }
	add_to_title($locale['global_200'].$locale['u103'].$locale['global_201'].$user_data['user_name']);

	if (iADMIN && checkrights("UG") && $_GET['lookup'] != $userdata['user_id']) {
		if ((isset($_POST['add_to_group'])) && (isset($_POST['user_group']) && isnum($_POST['user_group']))) {
			if (!preg_match("(^\.{$_POST['user_group']}$|\.{$_POST['user_group']}\.|\.{$_POST['user_group']}$)", $user_data['user_groups'])) {
				$result = dbquery("UPDATE ".DB_USERS." SET user_groups='".$user_data['user_groups'].".".$_POST['user_group']."' WHERE user_id='".$_GET['lookup']."'");
			}
			redirect(FUSION_SELF."?lookup=".$user_data['user_id']);
		}
	}

	opentable($locale['u104']." ".$user_data['user_name']);
	$userFields 					= new UserFields();
	$userFields->userData 			= $user_data;
	$userFields->showAdminOptions 	= true;
	$userFields->displayOutput();
} elseif (isset($_GET['group_id']) && isnum($_GET['group_id'])) {
	$result = dbquery("SELECT group_id, group_name FROM ".DB_USER_GROUPS." WHERE group_id='".$_GET['group_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		$result = dbquery(
			"SELECT user_id, user_name, user_level, user_status
			FROM ".DB_USERS."
			WHERE user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')
			ORDER BY user_level DESC, user_name"
		);

		opentable($locale['u110']);
		echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
		echo "<td align='center' colspan='2' class='tbl1'><strong>".$data['group_name']."</strong>\n";
		echo "(".sprintf((dbrows($result) == 1 ? $locale['u111'] : $locale['u112']), dbrows($result)).")";
		echo "</td>\n</tr>\n<tr>\n";
		if (dbrows($result)) {
			echo "<td class='tbl2'><strong>".$locale['u113']."</strong></td>\n";
			echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['u114']."</strong></td>\n";
			echo "</tr>\n";
			while ($data = dbarray($result)) {
				$cell_color = ($i % 2 == 0 ? "tbl1" : "tbl2"); $i++;
				echo "<tr>\n<td class='".$cell_color."'>\n".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
				echo "<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'>".getuserlevel($data['user_level'])."</td>\n</tr>";
			}
		}
		echo "</table>\n";
	} else {
		redirect("index.php");
	}
} else {
	redirect(BASEDIR."index.php");
}
closetable();

require_once THEMES."templates/footer.php";
?>