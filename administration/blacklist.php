<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blacklist.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";

if (!checkrights("B") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/blacklist.php";

if (isset($_GET['status'])) {
	if ($_GET['status'] == "del") {
		$title = $locale['400'];
		$message = "<strong>".$locale['401']."</strong>";
	}
	opentable($title);
	echo "<div style='text-align:center'>".$message."</div>\n";
	closetable();
}

		if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['blacklist_id']) && isnum($_GET['blacklist_id']))) {
	$result = dbquery("DELETE FROM ".DB_BLACKLIST." WHERE blacklist_id='".$_GET['blacklist_id']."'");
	redirect(FUSION_SELF.$aidlink."&status=del");
} else {
	if (isset($_POST['blacklist_user'])) {
		$blacklist_ip = stripinput($_POST['blacklist_ip']);
		$blacklist_email = stripinput($_POST['blacklist_email']);
		$blacklist_reason = stripinput($_POST['blacklist_reason']);
		$blacklist_ip_type = 0;
		if (strpos($blacklist_ip, ".")) {
			if (strpos($blacklist_ip, ":") === FALSE) {
				$blacklist_ip_type = 4;
			} else {
				$blacklist_ip_type = 5;
			}
		} else {
			$blacklist_ip_type = 6;
		}
		if ($blacklist_ip || $blacklist_email) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['blacklist_id']) && isnum($_GET['blacklist_id']))) {
				$result = dbquery("UPDATE ".DB_BLACKLIST." SET blacklist_ip='$blacklist_ip', blacklist_ip_type='$blacklist_ip_type', blacklist_email='$blacklist_email', blacklist_reason='$blacklist_reason' WHERE blacklist_id='".$_GET['blacklist_id']."'");
			} else {
				$result = dbquery("INSERT INTO ".DB_BLACKLIST." (blacklist_ip, blacklist_ip_type, blacklist_user_id, blacklist_email, blacklist_reason, blacklist_datestamp) VALUES ('$blacklist_ip', '$blacklist_ip_type', '".$userdata['user_id']."', '$blacklist_email', '$blacklist_reason', '".time()."')");
			}
		}
		redirect(FUSION_SELF.$aidlink);
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['blacklist_id']) && isnum($_GET['blacklist_id']))) {
		$result = dbquery("SELECT blacklist_id, blacklist_ip, blacklist_email, blacklist_reason FROM ".DB_BLACKLIST." WHERE blacklist_id='".$_GET['blacklist_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$blacklist_ip = $data['blacklist_ip'];
			$blacklist_email = $data['blacklist_email'];
			$blacklist_reason = $data['blacklist_reason'];
			$form_title = $locale['421'];
			$form_action = FUSION_SELF.$aidlink."&amp;action=edit&amp;blacklist_id=".$data['blacklist_id'];
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$blacklist_ip = "";
		$blacklist_email = "";
		$blacklist_reason = "";
		$form_title = $locale['420'];
		$form_action = FUSION_SELF.$aidlink;
	}
	opentable($form_title);
	echo "<table cellpadding='0' cellspacing='0' width='80%' class='center'>\n<tr>\n";
	echo "<td class='tbl'>".$locale['440']."\n";
	echo "<hr /></td>\n</tr>\n</table>\n";
	echo "<form name='blacklist_form' method='post' action='$form_action'>\n";
	echo "<table align='center' width='450' cellpadding='0' cellspacing='0'>\n<tr>\n";
	echo "<td class='tbl'>".$locale['441']."</td>\n";
	echo "<td class='tbl'><input type='text' name='blacklist_ip' value='".$blacklist_ip."' class='textbox' style='width:150px' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>".$locale['442']."</td>\n";
	echo "<td class='tbl'><input type='text' name='blacklist_email' value='".$blacklist_email."' class='textbox' style='width:250px' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td valign='top' class='tbl'>".$locale['443']."</td>\n";
	echo "<td class='tbl'><textarea name='blacklist_reason' cols='46' rows='3' class='textbox'>".$blacklist_reason."</textarea></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'><br />\n";
	echo "<input type='submit' name='blacklist_user' value='".$locale['444']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();

	opentable($locale['460']);
	$rows = dbcount("(blacklist_id)", DB_BLACKLIST);
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
	if ($rows != 0) {
		$result = dbquery(
			"SELECT b.blacklist_id, b.blacklist_ip, b.blacklist_email, b.blacklist_reason, b.blacklist_datestamp, u.user_id, u.user_name, u.user_status
			FROM ".DB_BLACKLIST." b
			LEFT JOIN ".DB_USERS." u ON u.user_id=b.blacklist_user_id
			ORDER BY blacklist_datestamp DESC
			LIMIT ".$_GET['rowstart'].",20"
		);
		$i = 0;
		echo "<table cellpadding='0' cellspacing='1' width='80%' class='tbl-border center'>\n<tr>\n";
		echo "<td class='tbl2'>".$locale['461']."</td>\n";
		echo "<td class='tbl2'>".$locale['467']."</td>\n";
		echo "<td class='tbl2'>".$locale['468']."</td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['462']."</td>\n";
		echo "</tr>\n";
		while ($data = dbarray($result)) {
			$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
			echo "<tr>\n";
			echo "<td class='$row_color'>".($data['blacklist_ip'] ? $data['blacklist_ip'] : $data['blacklist_email']);
			if ($data['blacklist_reason']) {
				echo "<br /><span class='small2'>".$data['blacklist_reason']."</span>";
			}
			echo "</td>\n<td class='$row_color'>".(isset($data['user_name']) ? profile_link($data['user_id'], $data['user_name'], $data['user_status']) : $locale['466'])."</td>\n";
			echo "<td class='$row_color'>".($data['blacklist_datestamp'] != 0 ? showdate("shortdate", $data['blacklist_datestamp']) : $locale['466'])."</td>\n";
			echo "<td align='center' width='1%' class='$row_color' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;blacklist_id=".$data['blacklist_id']."'>".$locale['463']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;blacklist_id=".$data['blacklist_id']."'>".$locale['464']."</a></td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
	} else {
		echo "<div style='text-align:center'><br />\n".$locale['465']."<br /><br />\n</div>\n";
	}
	closetable();
	if (($rows) > 20) { echo "<div align='center' style=';margin-top:5px;'>\n".makepagenav($_GET['rowstart'],20,$rows,3,FUSION_SELF.$aidlink."&amp;")."\n</div>\n"; }
}

require_once THEMES."templates/footer.php";
?>