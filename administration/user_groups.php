<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_groups.php
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

if (!checkrights("UG") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/user_groups.php";

if (isset($_POST['group_id']) && isnum($_POST['group_id'])) { $_GET['group_id'] = $_POST['group_id']; }

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "su") {
		$message = $locale['400'];
	} elseif ($_GET['status'] == "sn") {
		$message = $locale['401'];
	} elseif ($_GET['status'] == "remsel") {
		$message = $locale['402'];
	} elseif ($_GET['status'] == "remall") {
		$message = $locale['403'];
	} elseif ($_GET['status'] == "addsel") {
		$message = $locale['404'];
	} elseif ($_GET['status'] == "deln") {
		$message = $locale['405']."<br />\n<span class='small'>".$locale['406']."</span>";
	} elseif ($_GET['status'] == "dely") {
		$message = $locale['407'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if (isset($_POST['save_group'])) {
	$group_name = stripinput($_POST['group_name']);
	$group_description = stripinput($_POST['group_description']);
	if ($group_name) {
		if (isset($_GET['group_id']) && isnum($_GET['group_id'])) {
			$result = dbquery("UPDATE ".DB_USER_GROUPS." SET group_name='$group_name', group_description='$group_description' WHERE group_id='".$_GET['group_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=su");
		} else {
			$result = dbquery("INSERT INTO ".DB_USER_GROUPS." (group_name, group_description) VALUES ('$group_name', '$group_description')");
			redirect(FUSION_SELF.$aidlink."&status=sn");
		}
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} elseif (isset($_POST['add_sel']) && isnum($_GET['group_id'])) {
	$user_ids = ""; $check_count = 0;
	if (isset($_POST['add_check_mark'])) {
		if (is_array($_POST['add_check_mark']) && count($_POST['add_check_mark']) > 1) {
			foreach ($_POST['add_check_mark'] as $thisnum) {
				if (isnum($thisnum)) {
					$user_ids .= ($user_ids ? "," : "").$thisnum;
					$check_count++;
				}
			}
		} else {
			if (isnum($_POST['add_check_mark'][0])) {
				$user_ids = $_POST['add_check_mark'][0];
				$check_count = 1;
			}
		}

	}
	if ($check_count > 0) {
		$result = dbquery("SELECT user_id,user_name,user_groups FROM ".DB_USERS." WHERE user_id IN($user_ids)");
		while ($data = dbarray($result)) {
			$user_id = $data['user_id'];
	 		if (!preg_match("(^\.{$_GET['group_id']}$|\.{$_GET['group_id']}\.|\.{$_GET['group_id']}$)", $data['user_groups'])) {
				$user_groups = $data['user_groups'].".".$_GET['group_id'];
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_groups='$user_groups' WHERE user_id='".$data['user_id']."'");
			}
			unset($user_id);
		}
		redirect(FUSION_SELF.$aidlink."&status=addsel");
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} elseif (isset($_POST['remove_sel']) && isnum($_GET['group_id'])) {
	$user_ids = ""; $check_count = 0;
	if (isset($_POST['rem_check_mark'])) {
		if (is_array($_POST['rem_check_mark']) && count($_POST['rem_check_mark']) > 1) {
			foreach ($_POST['rem_check_mark'] as $thisnum) {
				if (isnum($thisnum)) {
					$user_ids .= ($user_ids ? "," : "").$thisnum;
					$check_count++;
				}
			}
		} else {
			if (isnum($_POST['rem_check_mark'][0])) {
				$user_ids = $_POST['rem_check_mark'][0];
				$check_count = 1;
			}
		}

	}
	if ($check_count > 0) {
		$result = dbquery("SELECT user_id,user_name,user_groups FROM ".DB_USERS." WHERE user_id IN($user_ids) AND user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')");
		while ($data = dbarray($result)) {
			$user_groups = preg_replace(array("(^\.{$_GET['group_id']}$)","(\.{$_GET['group_id']}\.)","(\.{$_GET['group_id']}$)"), array("",".",""), $data['user_groups']);
			$result2 = dbquery("UPDATE ".DB_USERS." SET user_groups='$user_groups' WHERE user_id='".$data['user_id']."'");
		}
		redirect(FUSION_SELF.$aidlink."&status=remsel");
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} elseif (isset($_POST['remove_all']) && isnum($_GET['group_id'])) {
	$result = dbquery("SELECT user_id,user_name,user_groups FROM ".DB_USERS." WHERE user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')");
	while ($data = dbarray($result)) {
		$user_groups = $data['user_groups'];
		$user_groups = preg_replace(array("(^\.{$_GET['group_id']}$)","(\.{$_GET['group_id']}\.)","(\.{$_GET['group_id']}$)"), array("",".",""), $user_groups);
		$result2 = dbquery("UPDATE ".DB_USERS." SET user_groups='$user_groups' WHERE user_id='".$data['user_id']."'");
	}
	redirect(FUSION_SELF.$aidlink."&status=remall");
} elseif (isset($_POST['delete']) && isnum($_GET['group_id'])) {
	if (dbcount("(user_id)", DB_USERS, "user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')")) {
		redirect(FUSION_SELF.$aidlink."&status=deln");
	} else {
		$result = dbquery("DELETE FROM ".DB_USER_GROUPS." WHERE group_id='".$_GET['group_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=dely");
	}
} else {
	$result = dbquery("SELECT group_id, group_name FROM ".DB_USER_GROUPS." ORDER BY group_name");
	if (dbrows($result)) {
		opentable($locale['420']);
		echo "<form name='selectform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
		echo "<div style='text-align:center'>\n<select name='group_id' class='textbox'>\n";
		$sel = "";
		while ($data = dbarray($result)) {
			if (isset($_GET['group_id']) && isnum($_GET['group_id'])) $sel = ($_GET['group_id'] == $data['group_id'] ? " selected='selected'" : "");
			echo "<option value='".$data['group_id']."'$sel>[".$data['group_id']."] ".$data['group_name']."</option>\n";
		}
		echo "</select>\n<input type='submit' name='edit' value='".$locale['421']."' class='button' />\n";
		echo "<input type='submit' name='delete' value='".$locale['422']."' onclick='return DeleteGroup();' class='button' />\n";
		echo "</div>\n</form>\n";
		closetable();
	}
	if (isset($_GET['group_id']) && isnum($_GET['group_id'])) {
		$result = dbquery("SELECT group_name, group_description FROM ".DB_USER_GROUPS." WHERE group_id='".$_GET['group_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$group_name = $data['group_name'];
			$group_description = $data['group_description'];
			$form_action = FUSION_SELF.$aidlink."&amp;group_id=".$_GET['group_id'];
			opentable($locale['430']);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$group_name = "";
		$group_description = "";
		$form_action = FUSION_SELF.$aidlink;
		opentable($locale['431']);
	}
	echo "<form name='editform' method='post' action='$form_action'>\n";
	echo "<table cellpadding='0' cellspacing='0' class='center'>\n";
	echo "<tr>\n<td class='tbl'>".$locale['432']."</td>\n";
	echo "<td class='tbl'><input type='text' name='group_name' value='$group_name' class='textbox' style='width:200px' /></td>\n";
	echo "</tr>\n<tr>\n<td class='tbl'>".$locale['433']."</td>\n";
	echo "<td class='tbl'><input type='text' name='group_description' value='$group_description' class='textbox' style='width:200px' /></td>\n";
	echo "</tr>\n<tr>\n<td align='center' colspan='2' class='tbl'><br />\n";
	echo "<input type='submit' name='save_group' value='".$locale['434']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>";
	closetable();
	if (isset($_GET['group_id']) && isnum($_GET['group_id'])) {
		opentable($locale['440']);
		if (!isset($_POST['search_users'])) {
			echo "<form name='searchform' method='post' action='".FUSION_SELF.$aidlink."&amp;group_id=".$_GET['group_id']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' width='450' class='center'>\n";
			echo "<tr>\n<td align='center' class='tbl'>".$locale['441']."<br />".$locale['442']."<br /><br />\n";
			echo "<input type='text' name='search_criteria' class='textbox' style='width:300px' />\n</td>\n";
			echo "</tr>\n<tr>\n<td align='center' class='tbl'>\n";
			echo "<label><input type='radio' name='search_type' value='user_name' checked='checked' />".$locale['444']."</label>\n";
			echo "<label><input type='radio' name='search_type' value='user_id' />".$locale['443']."</label></td>\n";
			echo "</tr>\n<tr>\n<td align='center' class='tbl'><input type='submit' name='search_users' value='".$locale['445']."' class='button' /></td>\n";
			echo "</tr>\n</table>\n</form>\n";
		}
		if (isset($_POST['search_users']) && isset($_POST['search_criteria'])) {
			$search_items = explode(",", $_POST['search_criteria']);
			$search_ids = ""; $search_names = ""; $mysql_search = "";
			foreach ($search_items as $item) {
				if ($_POST['search_type'] == "user_id" && isnum($item)) {
					$search_ids .= ($search_ids != "" ? "," : "").$item;
				} elseif ($_POST['search_type'] == "user_name" && preg_match("/^[-0-9A-Z_@\s]+$/i", $item)) {
					$search_names .= ($search_names != "" ? " OR user_name LIKE '" : "'").$item."%'";
				}
			}
			if ($_POST['search_type'] == "user_id" && $search_ids) {
				$mysql_search .= "user_id IN($search_ids) ";
			} elseif ($_POST['search_type'] == "user_name" && $search_names) {
				$mysql_search .= "user_name LIKE $search_names ";
			}
			if ($search_ids || $search_names) {
				$result = dbquery("SELECT user_id,user_name,user_groups,user_level FROM ".DB_USERS." WHERE ".$mysql_search." ORDER BY user_level DESC, user_name");
			}
			if (isset($result) && dbrows($result)) {
				echo "<form name='add_users_form' method='post' action='".FUSION_SELF.$aidlink."&amp;group_id=".$_GET['group_id']."'>\n";
				echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n";
				$i = 0; $users = "";
				while ($data = dbarray($result)) {
					if (!preg_match("(^\.{$_GET['group_id']}$|\.{$_GET['group_id']}\.|\.{$_GET['group_id']}$)", $data['user_groups'])) {
						$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2"); $i++;
						$users .= "<tr>\n<td class='$row_color'><label><input type='checkbox' name='add_check_mark[]' value='".$data['user_id']."' /> ".$data['user_name']."</label></td>\n<td align='right' width='1%' class='$row_color' style='white-space:nowrap'>".getuserlevel($data['user_level'])."</td>\n</tr>";
					}
				}
				if ($i > 0) {
					echo "<tr>\n<td class='tbl2'><strong>".$locale['446']."</strong></td>\n";
					echo "<td align='right' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['447']."</strong></td>\n</tr>\n";
					echo $users."<tr>\n<td colspan='2' class='tbl1'>\n";
					echo "<a href='#' onclick=\"javascript:setChecked('add_users_form','add_check_mark[]',1);return false;\">".$locale['448']."</a> |\n";
					echo "<a href='#' onclick=\"javascript:setChecked('add_users_form','add_check_mark[]',0);return false;\">".$locale['449']."</a>\n";
					echo "</td>\n</tr>\n<tr>\n<td align='center' colspan='2' class='tbl'>\n";
					echo "<input type='submit' name='add_sel' value='".$locale['450']."' class='button' />\n";
					echo "</td>\n</tr>\n";
				} else {
					echo "<tr>\n<td align='center' colspan='2' class='tbl'>".$locale['451']."<br /><br />\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;group_id=".$_GET['group_id']."'>".$locale['452']."</a>\n</td>\n</tr>\n";
				}
				echo "</table>\n</form>\n";
			} else {
				echo "<div style='text-align:center'><br />\n".$locale['451']."<br />\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;group_id=".$_GET['group_id']."'>".$locale['452']."</a><br />\n</div>\n";
			}
		}
		closetable();

		opentable($locale['460']);
		echo "<form name='rem_users_form' method='post' action='".FUSION_SELF.$aidlink."&amp;group_id=".$_GET['group_id']."'>\n";
		echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n";
		$rows = dbcount("(user_id)", DB_USERS, "user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')");
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
		if ($rows) {
			$i = 0;
			$result = dbquery("SELECT user_id,user_name,user_level FROM ".DB_USERS." WHERE user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$') ORDER BY user_level DESC, user_name LIMIT {$_GET['rowstart']},20");
			echo "<tr>\n<td class='tbl2'><strong>".$locale['446']."</strong></td>\n";
			echo "<td align='right' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['447']."</strong></td>\n</tr>\n";
			while ($data = dbarray($result)) {
				$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2"); $i++;
				echo "<tr>\n<td class='$row_color'><label><input type='checkbox' name='rem_check_mark[]' value='".$data['user_id']."' /> ".$data['user_name']."</td>\n<td align='right' width='1%' class='$row_color' style='white-space:nowrap'>".getuserlevel($data['user_level'])."</label></td>\n</tr>";
			}
			echo "<tr>\n<td colspan='2' class='tbl1'>\n";
			echo "<a href='#' onclick=\"javascript:setChecked('rem_users_form','rem_check_mark[]',1);return false;\">".$locale['448']."</a> |\n";
			echo "<a href='#' onclick=\"javascript:setChecked('rem_users_form','rem_check_mark[]',0);return false;\">".$locale['449']."</a>\n";
			echo "</td>\n</tr>\n<tr>\n<td align='center' colspan='3' class='tbl'>\n";
			echo "<input type='submit' name='remove_sel' value='".$locale['461']."' class='button' />\n";
			echo "<input type='submit' name='remove_all' value='".$locale['462']."' class='button' />\n";
			echo "</td>\n</tr>\n";
		} else {
			echo "<tr>\n<td align='center' colspan='2' class='tbl1'>".$locale['463']."</td>\n</tr>\n";
		}
		echo "</table>\n</form>\n";
		if ($rows > 20) { echo "<div align='center' style='margin-top:5px;'>\n".makePageNav($_GET['rowstart'],20,$rows,3,FUSION_SELF.$aidlink."&amp;group_id=".$_GET['group_id']."&amp;")."\n</div>\n"; }
		closetable();
		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "function setChecked(frmName,chkName,val) {"."\n";
		echo "dml=document.forms[frmName];"."\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {"."\n";
		echo "if(dml.elements[i].name == chkName) {"."\n"."dml.elements[i].checked = val;"."\n";
		echo "}\n}\n}\n";
		echo "/* ]]>*/\n";
		echo "</script>\n";
	}
}
echo "<script type='text/javascript'>\n";
echo "/* <![CDATA[ */\n";
echo "function DeleteGroup() {\n";
echo "return confirm('".$locale['423']."');\n}\n";
echo "/* ]]>*/\n";
echo "</script>\n";

require_once THEMES."templates/footer.php";
?>