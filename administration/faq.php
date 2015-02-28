<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq.php
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

if (!checkrights("FQ") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include LOCALE.LOCALESET."admin/faq.php";

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "scn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "scu") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "delcn") {
		$message = $locale['412']."<br />\n<span class='small'>".$locale['413']."</span>";
	} elseif ($_GET['status'] == "delcy") {
		$message = $locale['414'];
	} elseif ($_GET['status'] == "sn") {
		$message = $locale['510'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['511'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['512'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

$faq_cat_name = "";
$faq_cat_description = "";
$faq_cat_title = $locale['400'];
$faq_cat_action = FUSION_SELF.$aidlink;
$faq_question = "";
$faq_answer = "";
$faq_title = $locale['500'];
$faq_action = FUSION_SELF.$aidlink;
$errorMessage = "";

if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['faq_cat_id']) && isnum($_GET['faq_cat_id'])) && (isset($_GET['t']) && $_GET['t'] == "cat")) {
	$result = dbcount("(faq_cat_id)", DB_FAQS, "faq_cat_id='".$_GET['faq_cat_id']."'");
	if (!empty($result)) {
		redirect(FUSION_SELF.$aidlink."&status=delcn");
	} else {
		$result = dbquery("DELETE FROM ".DB_FAQ_CATS." WHERE faq_cat_id='".$_GET['faq_cat_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=delcy");
	}
} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['faq_id']) && isnum($_GET['faq_id'])) && (isset($_GET['t']) && $_GET['t'] == "faq")) {
	$faq_count = dbcount("(faq_id)", DB_FAQS, "faq_id='".$_GET['faq_id']."'");
	$result = dbquery("DELETE FROM ".DB_FAQS." WHERE faq_id='".$_GET['faq_id']."'");
	if ($faq_count) {
		redirect(FUSION_SELF.$aidlink."&faq_cat_id=".intval($_GET['faq_cat_id'])."&status=del");
	} else {
		redirect(FUSION_SELF.$aidlink."&status=del");
	}
} elseif (isset($_POST['save_cat'])) {
	$faq_cat_name = stripinput($_POST['faq_cat_name']);
	$faq_cat_description = stripinput($_POST['faq_cat_description']);
	$checkCat = dbcount("(faq_cat_id)", DB_FAQ_CATS, "faq_cat_name='".$faq_cat_name."'");
	if ($checkCat == 0) {
		if ($faq_cat_name) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['faq_cat_id']) && isnum($_GET['faq_cat_id'])) && (isset($_GET['t']) && $_GET['t'] == "cat")) {
				$result = dbquery("UPDATE ".DB_FAQ_CATS." SET faq_cat_name='$faq_cat_name', faq_cat_description='$faq_cat_description' WHERE faq_cat_id='".$_GET['faq_cat_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=scu");
			} else {
				$result = dbquery("INSERT INTO ".DB_FAQ_CATS." (faq_cat_name, faq_cat_description) VALUES('$faq_cat_name', '$faq_cat_description')");
				redirect(FUSION_SELF.$aidlink."&status=scn");
			}
		} else {
			$error = 1;
		}
	} else {
		$error = 2;
	}
} elseif (isset($_POST['save_faq'])) {
	$faq_cat = intval($_POST['faq_cat']);
	$faq_question = stripinput($_POST['faq_question']);
	$faq_answer = addslash($_POST['faq_answer']);
	if ($faq_question && $faq_answer) {
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['faq_id']) && isnum($_GET['faq_id'])) && (isset($_GET['t']) && $_GET['t'] == "faq")) {
			$result = dbquery("UPDATE ".DB_FAQS." SET faq_cat_id='$faq_cat', faq_question='$faq_question', faq_answer='$faq_answer' WHERE faq_id='".$_GET['faq_id']."'");
			redirect(FUSION_SELF.$aidlink."&faq_cat_id=$faq_cat&status=su");
		} else {
			$result = dbquery("INSERT INTO ".DB_FAQS." (faq_cat_id, faq_question, faq_answer) VALUES ('$faq_cat', '$faq_question', '$faq_answer')");
			redirect(FUSION_SELF.$aidlink."&faq_cat_id=$faq_cat&status=sn");
		}
	} else {
		$error = 3;
	}
} elseif (isset($_GET['action']) && $_GET['action'] == "edit") {
	if ((isset($_GET['faq_cat_id']) && isnum($_GET['faq_cat_id'])) && (isset($_GET['t']) && $_GET['t'] == "cat")) {
		$result = dbquery("SELECT faq_cat_id, faq_cat_name, faq_cat_description FROM ".DB_FAQ_CATS." WHERE faq_cat_id='".$_GET['faq_cat_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$faq_cat_id = $data['faq_cat_id'];
			$faq_cat_name = $data['faq_cat_name'];
			$faq_cat_description = $data['faq_cat_description'];
			$faq_cat_title = $locale['401'];
			$faq_cat_action = FUSION_SELF.$aidlink."&amp;action=edit&amp;faq_cat_id=".$data['faq_cat_id']."&amp;t=cat";
			// --------------------- //
			$faq_question = "";
			$faq_answer = "";
			$faq_title = $locale['400'];
			$faq_action = FUSION_SELF.$aidlink;
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} elseif ((isset($_GET['faq_id']) && isnum($_GET['faq_id'])) && (isset($_GET['t']) && $_GET['t'] == "faq")) {
		$result = dbquery("SELECT faq_id, faq_question, faq_answer FROM ".DB_FAQS." WHERE faq_id='".$_GET['faq_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$faq_cat_name = "";
			$faq_cat_description = "";
			$faq_cat_title = $locale['420'];
			$faq_cat_action = FUSION_SELF.$aidlink;
			// --------------------- //
			$faq_id = $data['faq_id'];
			$faq_question = $data['faq_question'];
			$faq_answer = stripslashes($data['faq_answer']);
			$faq_title = $locale['501'];
			$faq_action = FUSION_SELF.$aidlink."&amp;action=edit&amp;faq_id=".$data['faq_id']."&amp;t=faq";
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}

if (!isset($_GET['t']) || $_GET['t'] != "faq") {
	if (isset($error) && isnum($error)) {
		if ($error == 1) {
			$errorMessage = $locale['460'];
		} elseif ($error == 2) {
			$errorMessage = $locale['461'];
		} elseif ($error == 3) {
			$errorMessage = $locale['462'];
		}
		if (isset($errorMessage) && $errorMessage != "") { echo "<div id='close-message'><div class='admin-message'>".$errorMessage."</div></div>\n"; }
	}
	opentable($faq_cat_title);
	echo "<form name='add_faq_cat' method='post' action='".$faq_cat_action."'>\n";
	echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
	echo "<td class='tbl'>".$locale['420']."</td>\n";
	echo "<td class='tbl'><input type='text' name='faq_cat_name' value='".$faq_cat_name."' class='textbox' style='width:210px' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='130' class='tbl'>".$locale['421']."</td>\n";
	echo "<td class='tbl'><input type='text' name='faq_cat_description' value='".$faq_cat_description."' class='textbox' style='width:250px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'><input type='submit' name='save_cat' value='".$locale['422']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();
}
if (!isset($_GET['t']) || $_GET['t'] != "cat") {
	$cat_opts = ""; $sel = "";
	$result2 = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS." ORDER BY faq_cat_name");
	if (dbrows($result2) != 0) {
		while ($data2 = dbarray($result2)) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['faq_cat_id']) && isnum($_GET['faq_cat_id'])) && $_GET['t'] == "faq") { $sel = ($data2['faq_cat_id'] == $_GET['faq_cat_id'] ? " selected" : ""); }
			$cat_opts .= "<option value='".$data2['faq_cat_id']."'$sel>".$data2['faq_cat_name']."</option>\n";
		}
		opentable($faq_title);
		echo "<form name='inputform' method='post' action='".$faq_action."'>\n";
		echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
		echo "<td class='tbl'>".$locale['520']."</td>\n";
		echo "<td class='tbl'><select name='faq_cat' class='textbox' style='width:250px;'>\n".$cat_opts."</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td class='tbl'>".$locale['521']."</td>\n";
		echo "<td class='tbl'><input type='text' name='faq_question' value='".$faq_question."' class='textbox' style='width:330px' /></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td valign='top' class='tbl'>".$locale['522']."</td>\n";
		echo "<td class='tbl'><textarea name='faq_answer' cols='60' rows='5' class='textbox' style='width:330px;'>".phpentities(stripslashes($faq_answer))."</textarea></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td class='tbl'></td><td class='tbl'>\n";
		echo display_html("inputform", "faq_answer")."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td align='center' colspan='2' class='tbl'><br />\n";
		echo "<input type='submit' name='save_faq' value='".$locale['523']."' class='button' /></td>\n";
		echo "</tr>\n</table>\n</form>\n";
		closetable();
	}
}
opentable($locale['502']);
$result = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS." ORDER BY faq_cat_name");
if (dbrows($result) != 0) {
	echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
	echo "<td class='tbl2'>".$locale['540']."</td>\n";
	echo "<td align='right' class='tbl2'>".$locale['541']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' height='1'></td>\n";
	echo "</tr>\n";
	while ($data = dbarray($result)) {
		if (!isset($_GET['faq_cat_id']) || !isnum($_GET['faq_cat_id'])) { $_GET['faq_cat_id'] = 0; }
		if ($data['faq_cat_id'] == $_GET['faq_cat_id']) { $p_img = "off"; $div = ""; } else { $p_img = "on"; $div = "style='display:none'"; }
		echo "<tr>\n";
		echo "<td class='tbl2'><img src='".get_image("panel_$p_img")."' name='b_".$data['faq_cat_id']."' alt='' onclick=\"javascript:flipBox('".$data['faq_cat_id']."')\" /> ".$data['faq_cat_name']."</td>\n";
		echo "<td class='tbl2' align='right' width='80' style='font-weight:normal;'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;faq_cat_id=".$data['faq_cat_id']."&amp;t=cat'>".$locale['542']."</a> -\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;faq_cat_id=".$data['faq_cat_id']."&amp;t=cat' onclick=\"return confirm('".$locale['546']."');\">".$locale['543']."</a></td>\n";
		echo "</tr>\n";
		$result2 = dbquery("SELECT faq_id, faq_question, faq_answer FROM ".DB_FAQS." WHERE faq_cat_id='".$data['faq_cat_id']."' ORDER BY faq_id");
		if (dbrows($result2) != 0) {
			echo "<tr>\n<td colspan='2'>\n";
			echo "<div id='box_".$data['faq_cat_id']."'".$div.">\n";
			echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
			while ($data2 = dbarray($result2)) {
				echo "<tr>\n";
				echo "<td class='tbl'><strong>".$data2['faq_question']."</strong></td>\n";
				echo "<td align='right' class='tbl' width='80'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;faq_cat_id=".$data['faq_cat_id']."&amp;faq_id=".$data2['faq_id']."&amp;t=faq'>".$locale['542']."</a> -\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;faq_cat_id=".$data['faq_cat_id']."&amp;faq_id=".$data2['faq_id']."&amp;t=faq' onclick=\"return confirm('".$locale['547']."');\">".$locale['543']."</a></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td colspan='2' class='tbl'>".trimlink(stripinput($data2['faq_answer']), 60)."</td>\n";
				echo "</tr>\n";
			}
			echo "</table>\n</div>\n</td>\n</tr>\n";
		} else {
			echo "<tr>\n<td colspan='2'>\n";
			echo "<div id='box_".$data['faq_cat_id']."' style='display:none'>\n";
			echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
			echo "<tr>\n<td class='tbl'>".$locale['544']."</td>\n</tr>\n";
			echo "</table>\n</div>\n</td>\n</tr>\n";
		}
	}
	echo "</table>\n";
} else {
	echo "<div style='text-align:center'>".$locale['545']."<br />\n</div>\n";
}
closetable();

require_once THEMES."templates/footer.php";
?>