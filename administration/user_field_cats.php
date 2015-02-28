<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: field_cats.php
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

if (!checkrights("UFC") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/user_fields-cats.php";

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['412'];
	} elseif ($_GET['status'] == "deln") {
		$message = $locale['413']."<br />\n<span class='small'>".$locale['414']."</span>";
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if (isset($_GET['action']) && $_GET['action'] == "refresh") {
	$i = 1;
	$result = dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." ORDER BY field_cat_order");
	while ($data = dbarray($result)) {
		$result2 = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order='$i' WHERE field_cat_id='".$data['field_cat_id']."'");
		$i++;
	}
	redirect(FUSION_SELF.$aidlink);
} elseif ((isset($_GET['action']) && $_GET['action'] == "moveup") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$data = dbarray(dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_cat_order='".intval($_GET['order'])."'"));
	$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$data['field_cat_id']."'");
	$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$_GET['cat_id']."'");
	redirect(FUSION_SELF.$aidlink);
} elseif ((isset($_GET['action']) && $_GET['action'] == "movedown") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$data = dbarray(dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_cat_order='".intval($_GET['order'])."'"));
	$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$data['field_cat_id']."'");
	$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$_GET['cat_id']."'");
	redirect(FUSION_SELF.$aidlink);
} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	if (!dbcount("(field_id)", DB_USER_FIELDS, "field_cat='".$_GET['cat_id']."'")) {
		$data = dbarray(dbquery("SELECT field_cat_order FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$_GET['cat_id']."'"));
		$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_order>'".$data['field_cat_order']."'");
		$result = dbquery("DELETE FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$_GET['cat_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=del");
	} else {
		redirect(FUSION_SELF.$aidlink."&status=deln");
	}
} else {
	if (isset($_POST['savecat'])) {
		$cat_name = stripinput($_POST['cat_name']);
		$cat_order = isnum($_POST['cat_order']) ? $_POST['cat_order'] : 0;
		if ($cat_name != "") {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
				$old_cat_order = dbresult(dbquery("SELECT field_cat_order FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$_GET['cat_id']."'"), 0);
				if ($cat_order > $old_cat_order) {
					$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_order>'".$old_cat_order."' AND field_cat_order<='".$cat_order."'");
				} elseif ($cat_order < $old_cat_order) {
					$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_order<'".$old_cat_order."' AND field_cat_order>='".$cat_order."'");
				}
				$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_name='".$cat_name."', field_cat_order='$cat_order' WHERE field_cat_id='".$_GET['cat_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			} else {
				if ($cat_order == 0) { $cat_order = dbresult(dbquery("SELECT MAX(field_cat_order) FROM ".DB_USER_FIELD_CATS.""), 0) + 1; }
				$result = dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_order>='".$cat_order."'");
				$result = dbquery("INSERT INTO ".DB_USER_FIELD_CATS." (field_cat_name, field_cat_order) VALUES ('".$cat_name."', '".$cat_order."')");
				redirect(FUSION_SELF.$aidlink."&status=sn");
			}
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
		$result = dbquery("SELECT field_cat_id, field_cat_name, field_cat_order FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$_GET['cat_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$cat_name = $data['field_cat_name'];
			$cat_order = $data['field_cat_order'];
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['field_cat_id'];
			opentable($locale['401']);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$cat_name = "";
		$cat_order = "";
		$formaction = FUSION_SELF.$aidlink;
		opentable($locale['400']);
	}
	echo "<form name='layoutform' method='post' action='".$formaction."'>\n";
	echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
	echo "<td class='tbl'>".$locale['420'].":</td>\n";
	echo "<td class='tbl'><input type='text' name='cat_name' value='".$cat_name."' maxlength='100' class='textbox' style='width:240px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>".$locale['421'].":</td>\n";
	echo "<td class='tbl'><input type='text' name='cat_order'  value='".$cat_order."' maxlength='2' class='textbox' style='width:40px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo "<input type='submit' name='savecat' value='".$locale['422']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();

	opentable($locale['402']);
	echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n<tr>\n";
	echo "<td class='tbl2'><strong>".$locale['420']."</strong></td>\n";
	echo "<td align='center' colspan='2' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['430']."</strong></td>\n";
	echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['431']."</strong></td>\n";
	echo "</tr>\n";
	$result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." ORDER BY field_cat_order");
	if (dbrows($result)) {
		$i = 0; $k = 1;
		while($data = dbarray($result)) {
			$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
			echo "<tr>\n<td class='".$row_color."'>".$data['field_cat_name']."</td>\n";
			echo "<td align='center' width='1%' class='".$row_color."' style='white-space:nowrap'>".$data['field_cat_order']."</td>\n";
			echo "<td align='center' width='1%' class='".$row_color."' style='white-space:nowrap'>\n";
			if (dbrows($result) != 1) {
				$up = $data['field_cat_order'] - 1;
				$down = $data['field_cat_order'] + 1;
				if ($k == 1) {
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=movedown&amp;order=$down&amp;cat_id=".$data['field_cat_id']."'><img src='".get_image("down")."' alt='".$locale['441']."' title='".$locale['443']."' style='border:0px;' /></a>\n";
				} elseif ($k < dbrows($result)) {
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=moveup&amp;order=$up&amp;cat_id=".$data['field_cat_id']."'><img src='".get_image("up")."' alt='".$locale['440']."' title='".$locale['442']."' style='border:0px;' /></a>\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=movedown&amp;order=$down&amp;cat_id=".$data['field_cat_id']."'><img src='".get_image("down")."' alt='".$locale['441']."' title='".$locale['443']."' style='border:0px;' /></a>\n";
				} else {
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=moveup&amp;order=$up&amp;cat_id=".$data['field_cat_id']."'><img src='".get_image("up")."' alt='".$locale['440']."' title='".$locale['442']."' style='border:0px;' /></a>\n";
				}
			}
			$k++;
			echo "</td>\n";
			echo "<td align='center' width='1%' class='".$row_color."' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['field_cat_id']."'>".$locale['432']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['field_cat_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['433']."</a></td>\n";
			echo "</tr>\n";
			$i++;
		}
	} else {
		echo "<tr>\n<td align='center' colspan='3' class='tbl1'>".$locale['434']."</td>\n</tr>\n";
	}
	echo "</table>\n";
	if (dbrows($result)) { echo "<div style='text-align:center;margin-top:5px'>[ <a href='".FUSION_SELF.$aidlink."&amp;action=refresh'>".$locale['444']."</a> ]</div>\n"; }
	closetable();
}

require_once THEMES."templates/footer.php";
?>
