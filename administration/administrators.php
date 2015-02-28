<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: administrators.php
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

if (!checkrights("AD") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/admins.php";

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['400'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['401'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['402'];
	} elseif ($_GET['status'] == "pw") {
		$message = $locale['global_182'];
	}
	if ($message) { echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if (isset($_POST['cancel'])) {
	redirect(FUSION_SELF.$aidlink);
}

if (isset($_POST['add_admin']) && (isset($_POST['user_id']) && isnum($_POST['user_id']))) {
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		if (isset($_POST['all_rights']) || isset($_POST['make_super'])) {
			$admin_rights = "";
			$result = dbquery("SELECT DISTINCT admin_rights AS admin_right FROM ".DB_ADMIN." ORDER BY admin_right");
			while ($data = dbarray($result)) {
				$admin_rights .= (isset($admin_rights) ? "." : "").$data['admin_right'];
			}
			$result = dbquery("UPDATE ".DB_USERS." SET user_level='".(isset($_POST['make_super']) ? "103" : "102")."', user_rights='$admin_rights' WHERE user_id='".$_POST['user_id']."'");
		} else {
			$result = dbquery("UPDATE ".DB_USERS." SET user_level='102' WHERE user_id='".$_POST['user_id']."'");
		}
		set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		redirect(FUSION_SELF.$aidlink."&status=sn", true);
	} else {
		redirect(FUSION_SELF.$aidlink."&status=pw");
	}
}

if (isset($_GET['remove']) && (isset($_GET['remove']) && isnum($_GET['remove']) && $_GET['remove'] != 1)) {
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		$result = dbquery("UPDATE ".DB_USERS." SET user_admin_password='', user_level='101', user_rights='' WHERE user_id='".$_GET['remove']."' AND user_level>='102'");
		set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		redirect(FUSION_SELF.$aidlink."&status=del", true);
	} else {
		if (isset($_POST['confirm'])) {
			echo "<div id='close-message'><div class='admin-message'>".$locale['global_182']."</div></div>\n";
		}
		opentable($locale['470']);
		echo "<div style='text-align:center'>\n";
		echo "<form action='".FUSION_SELF.$aidlink."&amp;remove=".$_GET['remove']."' method='post'>\n";
		echo $locale['471']."<br /><br />\n<input class='textbox' type='password' name='admin_password' autocomplete='off' /><br /><br />\n";
		echo "<input class='button' type='submit' name='confirm' value='".$locale['472']."' />\n";
		echo "<input class='button' type='submit' name='cancel' value='".$locale['473']."' />\n";
		echo "</form>\n</div>\n";
		closetable();
	}
}

if (isset($_POST['update_admin']) && (isset($_GET['user_id']) && isnum($_GET['user_id']) && $_GET['user_id'] != 1)) {
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		if (isset($_POST['rights'])) {
			$user_rights = "";
			for ($i = 0;$i < count($_POST['rights']);$i++) {
				$user_rights .= ($user_rights != "" ? "." : "").stripinput($_POST['rights'][$i]);
			}
			$result = dbquery("UPDATE ".DB_USERS." SET user_rights='$user_rights' WHERE user_id='".$_GET['user_id']."' AND user_level>='102'");
		} else {
			$result = dbquery("UPDATE ".DB_USERS." SET user_rights='' WHERE user_id='".$_GET['user_id']."' AND user_level>='102'");
		}
		set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		redirect(FUSION_SELF.$aidlink."&status=su", true);
	} else {
		redirect(FUSION_SELF.$aidlink."&status=pw");
	}
}

if (isset($_GET['edit']) && isnum($_GET['edit']) && $_GET['edit'] != 1) {
	$result = dbquery("SELECT user_name, user_rights FROM ".DB_USERS." WHERE user_id='".$_GET['edit']."' AND user_level>='102' ORDER BY user_id");
	if (dbrows($result)) {
		$data = dbarray($result);
		$user_rights = explode(".", $data['user_rights']);
		$result2 = dbquery("SELECT admin_rights, admin_title, admin_page FROM ".DB_ADMIN." ORDER BY admin_page ASC,admin_title");
		opentable($locale['440']." [".$data['user_name']."]");
		$columns = 2; $counter = 0; $page = 1;
		$admin_page = array($locale['441'], $locale['442'], $locale['443'], $locale['449'], $locale['444']);
		$risky_rights = array("CP", "AD", "SB", "DB", "IP", "P", "S11", "S3", "ERRO");
		echo "<form name='rightsform' method='post' action='".FUSION_SELF.$aidlink."&amp;user_id=".$_GET['edit']."'>\n";
		echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n";
		echo "<tr>\n<td colspan='2' class='tbl2'><strong>".$admin_page['0']."</strong></td>\n</tr>\n<tr>\n";
		while ($data2 = dbarray($result2)) {
			if ($page != $data2['admin_page']) {
				echo ($counter % $columns == 0 ? "</tr>\n" : "<td width='50%' class='tbl1'></td>\n</tr>\n");
				echo "<tr>\n<td colspan='2' class='tbl2'><strong>".$admin_page[$page]."</strong></td>\n</tr>\n<tr>\n";
				$page++; $counter = 0;
			}
			if ($counter != 0 && ($counter % $columns == 0)) { echo "</tr>\n<tr>\n"; }
			echo "<td width='50%' class='tbl1'><label title='".$data2['admin_rights']."'><input type='checkbox' name='rights[]' value='".$data2['admin_rights']."'".(in_array($data2['admin_rights'], $risky_rights) ? " class='insecure'" : "").(in_array($data2['admin_rights'], $user_rights) ? " checked='checked'" : "")." /> ".$data2['admin_title']."</label>".(in_array($data2['admin_rights'], $risky_rights) ? "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>" : "")."</td>\n";
			$counter++;
		}
		echo "</tr>\n";
		echo "<tr>\n<td class='tbl' colspan='2' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'><span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".$locale['462']."</td>\n</tr>\n";
		echo "</table>\n";
		echo "<div style='text-align:center'><br />\n";
		echo "<input type='button' class='button' onclick=\"setChecked('rightsform','rights[]',1);\" value='".$locale['445']."' />\n";
		echo "<input type='button' class='button' onclick=\"setCheckedSecure('rightsform','rights[]',1);\" value='".$locale['450']."' />\n";
		echo "<input type='button' class='button' onclick=\"setChecked('rightsform','rights[]',0);\" value='".$locale['446']."' /><br /><br />\n";
		if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
			echo $locale['447']." <input type='password' name='admin_password' class='textbox' style='width:150px;' autocomplete='off' /><br /><br />\n";
		}
		echo "<input type='submit' name='update_admin' value='".$locale['448']."' class='button' />\n";
		echo "</div>\n</form>\n";
		closetable();
		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "function setChecked(frmName,chkName,val) {"."\n";
		echo "dml=document.forms[frmName];"."\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {"."\n";
		echo "if(dml.elements[i].name == chkName) {"."\n"."dml.elements[i].checked = val;"."\n";
		echo "}\n}\n}\n";
		echo "function setCheckedSecure(frmName,chkName,val) {"."\n";
		echo "setChecked(frmName,chkName,0);"."\n";
		echo "dml=document.forms[frmName];"."\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {"."\n";
		echo "if(dml.elements[i].name == chkName && !dml.elements[i].classList.contains('insecure')) {"."\n"."dml.elements[i].checked = val;"."\n";
		echo "}\n}\n}\n";
		echo "/* ]]>*/\n";
		echo "</script>\n";
	}
} else {
	opentable($locale['410']);
	if (!isset($_POST['search_users']) || !isset($_POST['search_criteria'])) {
		echo "<form name='searchform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
		echo "<table cellpadding='0' cellspacing='0' width='450' class='center'>\n";
		echo "<tr>\n<td align='center' class='tbl'>".$locale['411']."<br /><br />\n";
		echo "<input type='text' name='search_criteria' class='textbox' style='width:300px' />\n</td>\n";
		echo "</tr>\n<tr>\n<td align='center' class='tbl'>\n";
		echo "<label><input type='radio' name='search_type' value='user_name' checked='checked' />".$locale['413']."</label>\n";
		echo "<label><input type='radio' name='search_type' value='user_id' />".$locale['412']."</label></td>\n";
		echo "</tr>\n<tr>\n<td align='center' class='tbl'><input type='submit' name='search_users' value='".$locale['414']."' class='button' /></td>\n";
		echo "</tr>\n</table>\n</form>\n";
	} elseif (isset($_POST['search_users']) && isset($_POST['search_criteria'])) {
		$mysql_search = "";
		if ($_POST['search_type'] == "user_id" && isnum($_POST['search_criteria'])) {
			$mysql_search .= "user_id='".$_POST['search_criteria']."' ";
		} elseif ($_POST['search_type'] == "user_name" && preg_match("/^[-0-9A-Z_@\s]+$/i", $_POST['search_criteria'])) {
			$mysql_search .= "user_name LIKE '".$_POST['search_criteria']."%' ";
		}
		if ($mysql_search) {
			$result = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE ".$mysql_search." AND user_level='101' ORDER BY user_name");
		}
		if (isset($result) && dbrows($result)) {
			echo "<form name='add_users_form' method='post' action='".FUSION_SELF.$aidlink."'>\n";
			echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n";
			$i = 0; $users = "";
			while ($data = dbarray($result)) {
				$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2"); $i++;
				$users .= "<tr>\n<td class='$row_color'><label><input type='radio' name='user_id' value='".$data['user_id']."' /> ".$data['user_name']."</label></td>\n</tr>";
			}
			if ($i > 0) {
				echo "<tr>\n<td class='tbl2'><strong>".$locale['413']."</strong></td>\n</tr>\n";
				echo $users."<tr>\n<td align='center' class='tbl'>\n";
				echo "<label><input type='checkbox' name='all_rights' value='1' /> ".$locale['415']."</label><span style='color:red;font-weight:bold;margin-left:5px;'>*</span><br />\n";
				if ($userdata['user_level'] == 103) { echo "<label><input type='checkbox' name='make_super' value='1' /> ".$locale['416']."</label><span style='color:red;font-weight:bold;margin-left:5px;'>*</span><br />\n"; }
				if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
					echo $locale['447']." <input type='password' name='admin_password' class='textbox' style='width:150px;' autocomplete='off' /><br /><br />\n";
				}
				echo "<br />\n<input type='submit' name='add_admin' value='".$locale['417']."' class='button' onclick=\"return confirm('".$locale['461']."');\" />\n";
				echo "</td>\n</tr>\n";
				echo "<tr>\n<td class='tbl' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'><span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".$locale['462']."</td>\n</tr>\n";
			} else {
				echo "<tr>\n<td align='center' class='tbl'>".$locale['418']."<br /><br />\n";
				echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['419']."</a>\n</td>\n</tr>\n";
			}
			echo "</table>\n</form>\n";
		} else {
			echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n";
			echo "<tr>\n<td align='center' class='tbl'>".$locale['418']."<br /><br />\n";
			echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['419']."</a>\n</td>\n</tr>\n</table>\n";
		}
	}
	closetable();

	opentable($locale['420']);
	$i = 0;
	$result = dbquery("SELECT user_id, user_name, user_rights, user_level FROM ".DB_USERS." WHERE user_level>='102' ORDER BY user_level DESC, user_name");
	echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n<tr>\n";
	echo "<td class='tbl2'>".$locale['421']."</td>\n";
	echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['422']."</td>\n";
	echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['423']."</td>\n";
	echo "</tr>\n";
	while ($data = dbarray($result)) {
		$row_color = $i % 2 == 0 ? "tbl1" : "tbl2";
		echo "<tr>\n<td class='$row_color'><span title='".($data['user_rights'] ? str_replace(".", " ", $data['user_rights']) : "".$locale['425']."")."' style='cursor:hand;'>".$data['user_name']."</span></td>\n";
		echo "<td align='center' width='1%' class='$row_color' style='white-space:nowrap'>".getuserlevel($data['user_level'])."</td>\n";
		echo "<td align='center' width='1%' class='$row_color' style='white-space:nowrap'>\n";
		if ($data['user_level'] == "103" && $userdata['user_id'] == "1") { $can_edit = true;
		} elseif ($data['user_level'] != "103") { $can_edit = true;
		} else { $can_edit = false; }
		if ($can_edit == true && $data['user_id'] != "1") {
			echo "<a href='".FUSION_SELF.$aidlink."&amp;edit=".$data['user_id']."'>".$locale['426']."</a> |\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;remove=".$data['user_id']."' onclick=\"return confirm('".$locale['460']."');\">".$locale['427']."</a>\n";
		}
		echo "</td>\n</tr>\n";
		$i++;
	}
	echo "</table>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>
