<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks.php
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

if (!checkrights("W") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) redirect("../index.php");

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include LOCALE.LOCALESET."admin/weblinks.php";

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['510'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['511'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['512'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

$result = dbcount("(weblink_cat_id)", DB_WEBLINK_CATS);
if (!empty($result)) {
	if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['weblink_id']) && isnum($_GET['weblink_id']))) {
		$result = dbquery("DELETE FROM ".DB_WEBLINKS." WHERE weblink_id='".$_GET['weblink_id']."'");
		redirect(FUSION_SELF.$aidlink."&weblink_cat_id=".$_GET['weblink_cat_id']."&amp;status=del");
	}
	if (isset($_POST['save_link'])) {
		$weblink_name = stripinput($_POST['weblink_name']);
		$weblink_description = addslash($_POST['weblink_description']);
		$weblink_url = stripinput($_POST['weblink_url']);
		$weblink_cat = intval($_POST['weblink_cat']);
		if ($weblink_name) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['weblink_id']) && isnum($_GET['weblink_id']))) {
				$weblink_datestamp = isset($_POST['update_datestamp']) ? ", weblink_datestamp='".time()."'" : "";
				$result = dbquery("UPDATE ".DB_WEBLINKS." SET weblink_name='$weblink_name', weblink_description='$weblink_description', weblink_url='$weblink_url', weblink_cat='$weblink_cat'".$weblink_datestamp." WHERE weblink_id='".$_GET['weblink_id']."'");
				redirect(FUSION_SELF.$aidlink."&weblink_cat_id=$weblink_cat&amp;status=su");
			} else {
				$result = dbquery("INSERT INTO ".DB_WEBLINKS." (weblink_name, weblink_description, weblink_url, weblink_cat, weblink_datestamp, weblink_count) VALUES ('$weblink_name', '$weblink_description', '$weblink_url', '$weblink_cat', '".time()."', '0')");
				redirect(FUSION_SELF.$aidlink."&weblink_cat_id=$weblink_cat&amp;status=sn");
			}
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['weblink_id']) && isnum($_GET['weblink_id']))) {
		$result = dbquery("SELECT weblink_name, weblink_description, weblink_url, weblink_cat FROM ".DB_WEBLINKS." WHERE weblink_id='".$_GET['weblink_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$weblink_name = $data['weblink_name'];
			$weblink_description = stripslashes($data['weblink_description']);
			$weblink_url = $data['weblink_url'];
			$weblink_cat = $data['weblink_cat'];
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;weblink_id=".$_GET['weblink_id'];
			opentable($locale['501']);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$weblink_name = "";
		$weblink_description = "";
		$weblink_url = "http://";
		$weblink_cat = "";
		$formaction = FUSION_SELF.$aidlink;
		opentable($locale['500']);
	}
	$editlist = ""; $sel = "";
	$result2 = dbquery("SELECT weblink_cat_id, weblink_cat_name FROM ".DB_WEBLINK_CATS." ORDER BY weblink_cat_name");
	if (dbrows($result2) != 0) {
		while ($data2 = dbarray($result2)) {
			if (isset($_GET['action']) && $_GET['action'] == "edit") { $sel = ($weblink_cat == $data2['weblink_cat_id'] ? " selected='selected'" : ""); }
			$editlist .= "<option value='".$data2['weblink_cat_id']."'$sel>".$data2['weblink_cat_name']."</option>\n";
		}
	}
	echo "<form name='inputform' method='post' action='".$formaction."'>\n";
	echo "<table width='460' cellspacing='0' cellpadding='0' class='center'>\n<tr>\n";
	echo "<td width='80' class='tbl'>".$locale['520']."</td>\n";
	echo "<td class='tbl'><input type='text' name='weblink_name' value='".$weblink_name."' class='textbox' style='width:380px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td valign='top' width='80' class='tbl'>".$locale['521']."</td>\n";
	echo "<td class='tbl'><textarea name='weblink_description' cols='60' rows='5' class='textbox' style='width:380px;'>".$weblink_description."</textarea></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'></td><td class='tbl'>\n";
	echo display_html("inputform", "weblink_description", true)."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='80' class='tbl'>".$locale['522']."</td>\n";
	echo "<td class='tbl'><input type='text' name='weblink_url' value='".$weblink_url."' class='textbox' style='width:380px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='80' class='tbl'>".$locale['523']."</td>\n";
	echo "<td class='tbl'><select name='weblink_cat' class='textbox' style='width:150px;'>\n".$editlist."</select></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>";
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo "<input type='checkbox' name='update_datestamp' value='1'> ".$locale['524']."<br /><br />\n";
	}
	echo "<input type='submit' name='save_link' value='".$locale['525']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();

	opentable($locale['502']);
	echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n";
	$result = dbquery("SELECT weblink_cat_id, weblink_cat_name FROM ".DB_WEBLINK_CATS." ORDER BY weblink_cat_name");
	if (dbrows($result)) {
		echo "<tr>\n";
		echo "<td class='tbl2'>".$locale['531']."</td>\n";
		echo "<td align='right' class='tbl2'>".$locale['532']."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td colspan='2' height='1'></td>\n";
		echo "</tr>\n";
		while ($data = dbarray($result)) {
			if (!isset($_GET['weblink_cat_id']) || !isnum($_GET['weblink_cat_id'])) { $_GET['weblink_cat_id'] = 0; }
			if ($data['weblink_cat_id'] == $_GET['weblink_cat_id']) { $p_img = "off"; $div = ""; } else { $p_img = "on"; $div = "style='display:none'"; }
			echo "<tr>\n";
			echo "<td class='tbl2'>".$data['weblink_cat_name']."</td>\n";
			echo "<td class='tbl2' align='right'><img src='".get_image("panel_$p_img")."' alt='' name='b_".$data['weblink_cat_id']."' onclick=\"javascript:flipBox('".$data['weblink_cat_id']."')\" /></td>\n";
			echo "</tr>\n";
			$result2 = dbquery("SELECT weblink_id, weblink_name, weblink_url FROM ".DB_WEBLINKS." WHERE weblink_cat='".$data['weblink_cat_id']."' ORDER BY weblink_name");
			if (dbrows($result2)) {
				echo "<tr>\n<td colspan='2'>\n";
				echo "<div id='box_".$data['weblink_cat_id']."'".$div.">\n";
				echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
				while ($data2 = dbarray($result2)) {
					echo "<tr>\n";
					echo "<td class='tbl'><a href='".$data2['weblink_url']."' target='_blank'>".$data2['weblink_name']."</a></td>\n";
					echo "<td width='75' class='tbl'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;weblink_cat_id=".$data['weblink_cat_id']."&amp;weblink_id=".$data2['weblink_id']."'>".$locale['533']."</a> -\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;weblink_cat_id=".$data['weblink_cat_id']."&amp;weblink_id=".$data2['weblink_id']."' onclick=\"return confirm('".$locale['550']."');\">".$locale['534']."</a></td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n</div>\n</td>\n</tr>\n";
			} else {
				echo "<tr>\n<td colspan='2'>\n";
				echo "<div id='box_".$data['weblink_cat_id']."' style='display:none'>\n";
				echo "<table width='100%' cellspacing='0' cellpadding='0'>\n<tr>\n";
				echo "<td class='tbl'>".$locale['535']."</td>\n";
				echo "</tr>\n</table>\n</div>\n</td>\n</tr>\n";
			}
		}
		echo "</table>\n";
	}
	closetable();
} else {
	opentable($locale['536']);
	echo "<div style='text-align:center'>".$locale['537']."<br />\n".$locale['538']."<br />\n<br />\n";
	echo "<a href='weblink_cats.php".$aidlink."'>".$locale['539']."</a>".$locale['540']."</div>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>