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
if (!checkrights("FQ") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
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
	if ($message) {
		echo "<div id='close-message'><div class='alert alert-info m-t-10 admin-message'>".$message."</div></div>\n";
	}
}
$faq_cat_name = "";
$faq_cat_description = "";
$cat_language = LANGUAGE;
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
	$faq_cat_name = form_sanitizer($_POST['faq_cat_name'], '', 'faq_cat_name');
	$faq_cat_description = stripinput($_POST['faq_cat_description']);
	$cat_language = stripinput($_POST['cat_language']);
	$checkCat = dbcount("(faq_cat_id)", DB_FAQ_CATS, "faq_cat_name='".$faq_cat_name."'");
	if (!defined("FUSION_NULL")) {
		if ($checkCat == 0) {
			if ($faq_cat_name) {
				if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['faq_cat_id']) && isnum($_GET['faq_cat_id'])) && (isset($_GET['t']) && $_GET['t'] == "cat")) {
					$result = dbquery("UPDATE ".DB_FAQ_CATS." SET faq_cat_name='$faq_cat_name', faq_cat_description='$faq_cat_description', faq_cat_language = '$cat_language' WHERE faq_cat_id='".$_GET['faq_cat_id']."'");
					redirect(FUSION_SELF.$aidlink."&status=scu");
				} else {
					$result = dbquery("INSERT INTO ".DB_FAQ_CATS." (faq_cat_name, faq_cat_description, faq_cat_language) VALUES('$faq_cat_name', '$faq_cat_description', '$cat_language')");
					redirect(FUSION_SELF.$aidlink."&status=scn");
				}
			}
		} else {
			$defender->stop();
			$defender->addNotice($locale['461']);
		}
	}
} elseif (isset($_POST['save_faq'])) {
	$faq_cat = intval($_POST['faq_cat']);
	$faq_question = form_sanitizer($_POST['faq_question'], '', 'faq_question'); //stripinput($_POST['faq_question']);
	$faq_answer = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['faq_answer']));
	if (!defined('FUSION_NULL')) {
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['faq_id']) && isnum($_GET['faq_id'])) && (isset($_GET['t']) && $_GET['t'] == "faq")) {
			$result = dbquery("UPDATE ".DB_FAQS." SET faq_cat_id='$faq_cat', faq_question='$faq_question', faq_answer='$faq_answer' WHERE faq_id='".$_GET['faq_id']."'");
			redirect(FUSION_SELF.$aidlink."&faq_cat_id=$faq_cat&status=su");
		} else {
			$result = dbquery("INSERT INTO ".DB_FAQS." (faq_cat_id, faq_question, faq_answer) VALUES ('$faq_cat', '$faq_question', '$faq_answer')");
			redirect(FUSION_SELF.$aidlink."&faq_cat_id=$faq_cat&status=sn");
		}
	} else {
		$defender->stop();
		$defender->addNotice($locale['462']);
	}
} elseif (isset($_GET['action']) && $_GET['action'] == "edit") {
	if ((isset($_GET['faq_cat_id']) && isnum($_GET['faq_cat_id'])) && (isset($_GET['t']) && $_GET['t'] == "cat")) { // edit cat
		$result = dbquery("SELECT faq_cat_id, faq_cat_name, faq_cat_description, faq_cat_language FROM ".DB_FAQ_CATS." WHERE faq_cat_id='".$_GET['faq_cat_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$faq_cat_id = $data['faq_cat_id'];
			$faq_cat_name = $data['faq_cat_name'];
			$faq_cat_description = $data['faq_cat_description'];
			$cat_language = $data['faq_cat_language'];
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
			$cat_language = "";;
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
	opentable($faq_cat_title);
	echo openform('add_faq_cat', 'add_faq_cat', 'post', $faq_cat_action, array('downtime' => 1));
	echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tr>\n";
	echo "<td class='tbl'><label for='faq_cat_name'>".$locale['420']."</label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_text('', 'faq_cat_name', 'faq_cat_name', $faq_cat_name, array('error_text' => $locale['460'], 'required' => 1));
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='130' class='tbl'><label for='faq_cat_description'>".$locale['421']."</label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_text('', 'faq_cat_description', 'faq_cat_description', $faq_cat_description);
	echo "</td>\n";
	echo "</tr>\n";
	if (multilang_table("FQ")) {
		echo "<tr><td class='tbl'><label for='cat_language'>".$locale['global_ML100']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_select('', 'cat_language', 'cat_language', $language_opts, $cat_language, array('placeholder' => $locale['choose']));
		echo "</td>\n";
		echo "</tr>\n";
	} else {
		echo form_hidden('', 'cat_language', 'cat_language', $cat_language);
	}
	echo "<tr><td align='center' colspan='2' class='tbl'>\n";
	echo form_button($locale['422'], 'save_cat', 'save_cat', $locale['422'], array('class' => 'btn-primary m-t-10'));
	echo "</td>\n";
	echo "</tr>\n</table>\n";
	echo closeform();
	closetable();
}
echo "<hr>\n";
if (!isset($_GET['t']) || $_GET['t'] != "cat") {
	$cat_opts = array();
	$result2 = dbquery("SELECT faq_cat_id, faq_cat_name, faq_cat_language FROM ".DB_FAQ_CATS." ".(multilang_table("FQ") ? "WHERE faq_cat_language='".LANGUAGE."'" : "")." ORDER BY faq_cat_name");
	if (dbrows($result2) != 0) {
		while ($data2 = dbarray($result2)) {
			$cat_opts[$data2['faq_cat_id']] = $data2['faq_cat_name'];
		}
		opentable($faq_title);
		echo openform('inputform', 'inputform', 'post', $faq_action, array('downtime' => 1, 'notice' => 0));
		echo "<table cellpadding='0' cellspacing='0' class='center table table-responsive'>\n<tr>\n";
		echo "<td class='tbl'><label for='faq_cat'>".$locale['520']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_select('', 'faq_cat', 'faq_cat', $cat_opts, isset($_GET['faq_cat_id']) && isnum($_GET['faq_cat_id']) ? $_GET['faq_cat_id'] : 0, array('placeholder' => $locale['choose']));
		echo "</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td class='tbl'><label for='faq_question'>".$locale['521']."</label> <span class='required'>*</span></td>\n";
		echo "<td class='tbl'>\n";
		echo form_text('', 'faq_question', 'faq_question', $faq_question, array('required' => 1));
		echo "</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td valign='top' class='tbl'><label for='faq_answer'>".$locale['522']."</label> <span class='required'>*</span></td>\n";
		echo "<td class='tbl'>\n";
		echo form_textarea('', 'faq_answer', 'faq_answer', $faq_answer, array('required' => 1));
		echo "</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td class='tbl'></td><td class='tbl'>\n";
		echo display_html("inputform", "faq_answer")."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td align='center' colspan='2' class='tbl'><br />\n";
		echo form_button($locale['523'], 'save_faq', 'save_faq', $locale['523'], array('class' => 'btn-primary m-t-10'));
		echo "</td>\n";
		echo "</tr>\n</table>\n</form>\n";
		closetable();
	}
}
opentable($locale['502']);
$result = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS." ".(multilang_table("FQ") ? "WHERE faq_cat_language='".LANGUAGE."'" : "")." ORDER BY faq_cat_name");
if (dbrows($result) != 0) {
	echo "<table cellpadding='0' cellspacing='0' width='400' class='table table-responsive table-striped center'>\n<thead><tr>\n";
	echo "<th>".$locale['540']."</th>\n";
	echo "<th style='width:20%;' class='text-right'>".$locale['541']."</th>\n";
	echo "</tr>\n";
	echo "</thead>\n<tbody>\n";
	while ($data = dbarray($result)) {
		if (!isset($_GET['faq_cat_id']) || !isnum($_GET['faq_cat_id'])) {
			$_GET['faq_cat_id'] = 0;
		}
		if ($data['faq_cat_id'] == $_GET['faq_cat_id']) {
			$p_img = "off";
			$div = "";
		} else {
			$p_img = "on";
			$div = "style='display:none'";
		}
		echo "<tr>\n";
		echo "<td class='tbl2'><img src='".get_image("panel_$p_img")."' name='b_".$data['faq_cat_id']."' alt='' onclick=\"javascript:flipBox('".$data['faq_cat_id']."')\" /> ".$data['faq_cat_name']."</td>\n";
		echo "<td class='tbl2' align='right' style='font-weight:normal;'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;faq_cat_id=".$data['faq_cat_id']."&amp;t=cat'>".$locale['542']."</a> -\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;faq_cat_id=".$data['faq_cat_id']."&amp;t=cat' onclick=\"return confirm('".$locale['546']."');\">".$locale['543']."</a></td>\n";
		echo "</tr>\n";
		$result2 = dbquery("SELECT faq_id, faq_question, faq_answer FROM ".DB_FAQS." WHERE faq_cat_id='".$data['faq_cat_id']."' ORDER BY faq_id");
		if (dbrows($result2) != 0) {
			echo "<tr>\n<td colspan='2'>\n";
			echo "<div class='panel panel-default' id='box_".$data['faq_cat_id']."'".$div.">\n";
			echo "<div class='panel-body'>\n";
			echo "<table cellpadding='0' cellspacing='0' class='table table-responsive' width='100%'>\n";
			while ($data2 = dbarray($result2)) {
				echo "<tr>\n";
				echo "<td class='tbl'><strong>".$data2['faq_question']."</strong></td>\n";
				echo "<td align='right' class='tbl'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;faq_cat_id=".$data['faq_cat_id']."&amp;faq_id=".$data2['faq_id']."&amp;t=faq'>".$locale['542']."</a> -\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;faq_cat_id=".$data['faq_cat_id']."&amp;faq_id=".$data2['faq_id']."&amp;t=faq' onclick=\"return confirm('".$locale['547']."');\">".$locale['543']."</a></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td colspan='2' class='tbl'>".trim_text($data2['faq_answer'], 60)."</td>\n";
				echo "</tr>\n";
			}
			echo "</tbody>\n</table>\n</div>\n</div>\n</td>\n</tr>\n";
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
