<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shoutbox_admin.php
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
require_once "../../maincore.php";
require_once THEMES."templates/admin_header.php";

include INFUSIONS."shoutbox_panel/infusion_db.php";

// Check if locale file is available matching the current site locale setting.
if (file_exists(INFUSIONS."shoutbox_panel/locale/".$settings['locale'].".php")) {
	// Load the locale file matching the current site locale setting.
	include INFUSIONS."shoutbox_panel/locale/".$settings['locale'].".php";
} else {
	// Load the infusion's default locale file.
	include INFUSIONS."shoutbox_panel/locale/English.php";
}

if (!checkrights("S") || !defined("iAUTH") || $_GET['aid'] != iAUTH) { redirect("../../index.php"); }

$nav = "<table cellpadding='0' cellspacing='0' class='tbl-border' align='center' style='width:300px; margin-bottom:20px; text-align:center;'>\n<tr>\n";
$nav .= "<td class='".(!isset($_GET['page']) || $_GET['page'] != "settings" ? "tbl2" : "tbl1")."'><a href='".FUSION_SELF.$aidlink."'>".$locale['SB_admin1']."</a></td>\n";
$nav .= "<td class='".(isset($_GET['page']) && $_GET['page'] == "settings" ? "tbl2" : "tbl1")."'><a href='".FUSION_SELF.$aidlink."&amp;page=settings'>".$locale['SB_settings']."</a></td>\n";
$nav .= "</tr>\n</table>\n";

include_once INCLUDES."bbcode_include.php";

if (!isset($_GET['page']) || $_GET['page'] != "settings") {
	if (isset($_GET['status']) && !isset($message)) {
		if ($_GET['status'] == "su") {
			$message = $locale['SB_shout_updated'];
		} elseif ($_GET['status'] == "del") {
			$message = $locale['SB_shout_deleted'];
		}
		if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
	} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
		$result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".$_GET['shout_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=del");
	} else {
		if (isset($_POST['saveshout']) && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
			$shout_message = str_replace("\n", " ", $_POST['shout_message']);
			$shout_message = preg_replace("/^(.{255}).*$/", "$1", $shout_message);
			$shout_message = preg_replace("/([^\s]{25})/", "$1\n", $shout_message);
			$shout_message = stripinput($shout_message);
			$shout_message = str_replace("\n", "<br />", $shout_message);
			if ($shout_message) {
				$result = dbquery("UPDATE ".DB_SHOUTBOX." SET shout_message='$shout_message' WHERE shout_id='".$_GET['shout_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		}
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
			$result = dbquery("SELECT shout_id, shout_message FROM ".DB_SHOUTBOX." WHERE shout_id='".$_GET['shout_id']."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				opentable($locale['SB_edit_shout']);
				echo "<form name='editform' method='post' action='".FUSION_SELF.$aidlink."&amp;shout_id=".$data['shout_id']."'>\n";
				echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
				echo "<td class='tbl'>".$locale['SB_message']."</td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td class='tbl'><textarea name='shout_message' cols='60' rows='3' class='textbox' style='width:250px;'>".str_replace("<br />", "", $data['shout_message'])."</textarea></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td class='tbl' align='center'>".display_bbcodes("150px;", "shout_message", "editform", "smiley|b|u|url|color")."</td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td align='center' class='tbl'><input type='submit' name='saveshout' value='".$locale['SB_save_shout']."' class='button' /></td>\n";
				echo "</tr>\n</table>\n\n</form>";
				closetable();
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		}
		opentable($locale['SB_edit_shout']);
		echo $nav;
		$result = dbquery("SELECT * FROM ".DB_SHOUTBOX);
		$rows = dbrows($result);
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
		if ($rows != 0) {
			$i = 0;
			$result = dbquery(
				"SELECT s.shout_id, s.shout_name, s.shout_message, s.shout_datestamp, s.shout_ip, u.user_id, u.user_name, u.user_status
				FROM ".DB_SHOUTBOX." s
				LEFT JOIN ".DB_USERS." u ON s.shout_name=u.user_id
				ORDER BY shout_datestamp DESC LIMIT ".$_GET['rowstart'].",20"
			);
			echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border center'>\n";
			while ($data = dbarray($result)) {
				echo "<tr>\n<td class='".($i % 2 == 0 ? "tbl1" : "tbl2")."'><span class='comment-name'>";
				if ($data['user_name']) {
					echo "<span class='slink'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span>";
				} else {
					echo $data['shout_name'];
				}
				echo "</span>\n<span class='small'>".$locale['SB_on_date'].showdate("longdate", $data['shout_datestamp'])."</span><br />\n";
				echo str_replace("<br />", "", parseubb(parsesmileys($data['shout_message']), "b|i|u|url|color"))."<br />\n";
				echo "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;shout_id=".$data['shout_id']."'>".$locale['SB_edit']."</a> -\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;shout_id=".$data['shout_id']."' onclick=\"return confirm('".$locale['SB_warning_shout']."');\">".$locale['SB_delete']."</a> -\n";
				echo "<strong>".$locale['SB_userip'].$data['shout_ip']."</strong></span></td>\n";
				echo "</tr>\n";
				$i++;
			}
			echo "</table>\n";
		} else {
			echo "<div style='text-align:center'><br />\n".$locale['SB_no_msgs']."<br /><br />\n</div>\n";
		}
		echo "<div align='center' style='margin-top:5px;'>\n".makePageNav($_GET['rowstart'],20,$rows,3,FUSION_SELF.$aidlink."&amp;")."\n</div>\n";
		closetable();
	}
} else {
	include INCLUDES."infusions_include.php";
	if (isset($_POST['sb_settings'])) {
		if (isset($_POST['visible_shouts']) && isnum($_POST['visible_shouts'])) {
			$setting = set_setting("visible_shouts", $_POST['visible_shouts'], "shoutbox_panel");
		}
		if (isset($_POST['guest_shouts']) && ($_POST['guest_shouts'] == 1 || $_POST['guest_shouts'] == 0)) {
			$setting = set_setting("guest_shouts", $_POST['guest_shouts'], "shoutbox_panel");
		}
		redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=update_ok");
	}

	if (isset($_POST['sb_delete_old']) && isset($_POST['num_days']) && isnum($_POST['num_days'])) {
		$deletetime = time() - ($_POST['num_days'] * 86400);
		$numrows = dbcount("(shout_id)", DB_SHOUTBOX, "shout_datestamp < '".$deletetime."'");
		$result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_datestamp < '".$deletetime."'");
		redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=delall&numr=$numrows");
	}


	if (isset($_GET['status'])) {
		if ($_GET['status'] == "delall" && isset($_GET['numr']) && isnum($_GET['numr'])) {
			$message = number_format(intval($_GET['numr']))." ".$locale['SB_shouts_deleted'];
		} elseif ($_GET['status'] == "update_ok") {
			$message = $locale['SB_update_ok'];
		}
	}
	if (isset($message) && $message != "") {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }

	$inf_settings = get_settings("shoutbox_panel");
	opentable($locale['SB_settings']);
	echo $nav;
	echo "<form method='post' action='".FUSION_SELF.$aidlink."&amp;page=settings'>\n";
	echo "<div style='width:300px; text-align:center; margin:0 auto; padding:4px;' class='tbl-border tbl1'>\n";
	echo $locale['SB_delete_old']." <select name='num_days' class='textbox' style='width:50px'>\n";
	echo "<option value=''>---</option>\n";
	echo "<option value='90'>90</option>\n";
	echo "<option value='60'>60</option>\n";
	echo "<option value='30'>30</option>\n";
	echo "<option value='20'>20</option>\n";
	echo "<option value='10'>10</option>\n";
	echo "</select>".$locale['SB_days']." <br />";
	echo "<span style='margin:4px; display:block;'><input type='submit' name='sb_delete_old' value='".$locale['SB_submit']."' onclick=\"return confirm('".$locale['SB_warning_shouts']."');\" class='button' /></span>";
	echo "</div>\n</form>\n";
	echo "<form method='post' action='".FUSION_SELF.$aidlink."&amp;page=settings'>\n";
	echo "<table cellpadding='0' cellspacing='0' align='center' class='tbl-border' style='width:300px; margin-top:20px;'>\n";
	echo "<tr>\n";
	echo "<td class='tbl1'>".$locale['SB_visible_shouts']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='visible_shouts' class='textbox' value='".$inf_settings['visible_shouts']."' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>".$locale['SB_guest_shouts']."</td>\n";
	echo "<td class='tbl1'><select name='guest_shouts' size='1' class='textbox'>";
	echo "<option value='1' ".($inf_settings['guest_shouts'] == 1 ? "selected='selected'" : "").">".$locale['SB_yes']."</option>\n";
	echo "<option value='0'".($inf_settings['guest_shouts'] == 0 ? "selected='selected'" : "").">".$locale['SB_no']."</option>\n";
	echo "</select></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' colspan='2' style='text-align:center;'><input type='submit' name='sb_settings' value='".$locale['SB_submit']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n";
	echo "</form>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>