<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: download_cats.php
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

if (!checkrights("DC") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) redirect("../index.php");

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/download-cats.php";

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "deln") {
		$message = $locale['412']."<br />\n<span class='small'>".$locale['413']."</span>";
	} elseif ($_GET['status'] == "dely") {
		$message = $locale['414'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(download_cat)", DB_DOWNLOADS, "download_cat='".$_GET['cat_id']."'");
	if (!empty($result)) {
		redirect(FUSION_SELF.$aidlink."&status=deln");
	} else {
		$result = dbquery("DELETE FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id='".$_GET['cat_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=dely");
	}
} else {
	if (isset($_POST['save_cat'])) {
		$cat_name = stripinput($_POST['cat_name']);
		$cat_description = stripinput($_POST['cat_description']);
		$cat_access = isnum($_POST['cat_access']) ? $_POST['cat_access'] : "0";
		if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "1") {
			$cat_sorting = "download_id ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "2") {
			$cat_sorting = "download_title ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "3") {
			$cat_sorting = "download_datestamp ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else {
			$cat_sorting = "download_title ASC";
		}

		if ($cat_name) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
				$result = dbquery("UPDATE ".DB_DOWNLOAD_CATS." SET download_cat_name='$cat_name', download_cat_description='$cat_description', download_cat_sorting='$cat_sorting', download_cat_access='$cat_access' WHERE download_cat_id='".$_GET['cat_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			} else {
				$checkCat = dbcount("(download_cat_id)", DB_DOWNLOAD_CATS, "download_cat_name='".$cat_name."'");
				if ($checkCat == 0) {
					$result = dbquery("INSERT INTO ".DB_DOWNLOAD_CATS." (download_cat_name, download_cat_description, download_cat_sorting, download_cat_access) VALUES('$cat_name', '$cat_description', '$cat_sorting', '$cat_access')");
					redirect(FUSION_SELF.$aidlink."&status=sn");
				} else {
					$error = 2;
				}
			}
		} else {
			$error = 1;
		}
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
		$result = dbquery("SELECT * FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id='".$_GET['cat_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$cat_name = $data['download_cat_name'];
			$cat_description = $data['download_cat_description'];
			$cat_sorting = explode(" ", $data['download_cat_sorting']);
			if ($cat_sorting[0] == "download_id") { $cat_sort_by = "1"; }
			elseif ($cat_sorting[0] == "download_title") { $cat_sort_by = "2"; }
			elseif ($cat_sorting[0] == "download_datestamp") { $cat_sort_by = "3"; }
			else { $cat_sort_by = ""; }
			$cat_sort_order = $cat_sorting[1];
			$cat_access = $data['download_cat_access'];
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['download_cat_id'];
			$openTable = $locale['400'];
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$cat_name = "";
		$cat_description = "";
		$cat_sort_by = "";
		$cat_sort_order = "ASC";
		$cat_access = "";
		$formaction = FUSION_SELF.$aidlink;
		$openTable = $locale['401'];
	}
	$user_groups = getusergroups(); $access_opts = ""; $sel = "";
	while(list($key, $user_group) = each($user_groups)){
		$sel = ($cat_access == $user_group['0'] ? " selected='selected'" : "");
		$access_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
	}

	if (isset($error) && isnum($error)) {
		if ($error == 1) {
			$errorMessage = $locale['460'];
		} elseif ($error == 2) {
			$errorMessage = $locale['461'];
		}
		if ($errorMessage) { echo "<div id='close-message'><div class='admin-message'>".$errorMessage."</div></div>\n"; }
	}

	opentable($openTable);
	echo "<form name='addcat' method='post' action='$formaction'>\n";
	echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$locale['420']."</td>\n";
	echo "<td class='tbl'><input type='text' name='cat_name' value='".$cat_name."' class='textbox' style='width:200px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$locale['421']."</td>\n";
	echo "<td class='tbl'><input type='text' name='cat_description' value='".$cat_description."' class='textbox' style='width:250px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$locale['422']."</td>\n";
	echo "<td class='tbl'><select name='cat_sort_by' class='textbox'>\n";
	echo "<option value='1'".($cat_sort_by == "1" ? " selected='selected'" : "").">".$locale['423']."</option>\n";
	echo "<option value='2'".($cat_sort_by == "2" ? " selected='selected'" : "").">".$locale['424']."</option>\n";
	echo "<option value='3'".($cat_sort_by == "3" ? " selected='selected'" : "").">".$locale['425']."</option>\n";
	echo "</select> - <select name='cat_sort_order' class='textbox'>\n";
	echo "<option value='ASC'".($cat_sort_order == "ASC" ? " selected='selected'" : "").">".$locale['426']."</option>\n";
	echo "<option value='DESC'".($cat_sort_order == "DESC" ? " selected='selected'" : "").">".$locale['427']."</option>\n";
	echo "</select></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$locale['428']."</td>\n";
	echo "<td class='tbl'><select name='cat_access' class='textbox' style='width:150px;'>\n".$access_opts."</select></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo "<input type='submit' name='save_cat' value='".$locale['429']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();

	opentable($locale['402']);
	echo "<table width='400' cellspacing='1' cellpadding='0' class='tbl-border center'>\n";
	$result = dbquery("SELECT download_cat_id, download_cat_name, download_cat_description, download_cat_access FROM ".DB_DOWNLOAD_CATS." ORDER BY download_cat_name");
	if (dbrows($result) != 0) {
		$i = 0;
		echo "<tr>\n";
		echo "<td class='tbl2'>".$locale['440']."</td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['441']."</td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['442']."</td>\n";
		echo "</tr>\n";
		while ($data = dbarray($result)) {
			$cell_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
			echo "<tr>\n";
			echo "<td class='".$cell_color."'>".$data['download_cat_name']."\n";
			if ($data['download_cat_description']) { echo "<br /><span class='small'>".trimlink($data['download_cat_description'], 45)."</span>"; }
			echo "</td>\n<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'>".getgroupname($data['download_cat_access'])."</td>\n";
			echo "<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['download_cat_id']."'>".$locale['443']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['download_cat_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['444']."</a></td>\n";
			echo "</tr>\n";
			$i++;
		}
		echo "</table>\n";
	} else {
		echo "<tr><td align='center' class='tbl1'>".$locale['445']."</td></tr>\n</table>\n";
	}
	closetable();
}

require_once THEMES."templates/footer.php";
?>