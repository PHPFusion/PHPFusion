<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: article_cats.php
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
if (!checkRights("AC") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/articles.php";
if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['articles_0150'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['articles_0151'];
	} elseif ($_GET['status'] == "deln") {
		$message = $locale['articles_0152']."<br />\n<span class='small'>".$locale['articles_0153']."</span>";
	} elseif ($_GET['status'] == "dely") {
		$message = $locale['articles_0154'];
	}
	if ($message) {
		echo "<div id='close-message'><div class='alert alert-info m-t-10 admin-message'>".$message."</div></div>\n";
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(article_id)", DB_ARTICLES, "article_cat='".$_GET['cat_id']."'") || dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_parent='".$_GET['cat_id']."'");
	if (!empty($result)) {
		redirect(FUSION_SELF.$aidlink."&status=deln");
	} else {
		$result = dbquery("DELETE FROM ".DB_ARTICLE_CATS." WHERE article_cat_id='".$_GET['cat_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=dely");
	}
} else {
	if (isset($_POST['save_cat'])) {
		$cat_name = form_sanitizer($_POST['cat_name'], '', 'cat_name');
		$cat_description = form_sanitizer($_POST['cat_description'], '', 'cat_description');
		$cat_parent = isnum($_POST['cat_parent']) ? $_POST['cat_parent'] : "0";
		$cat_language = stripinput(trim($_POST['cat_language']));
		if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "1") {
			$cat_sorting = "article_id ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "2") {
			$cat_sorting = "article_subject ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "3") {
			$cat_sorting = "article_datestamp ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else {
			$cat_sorting = "article_subject ASC";
		}
		if ($cat_name && !defined('FUSION_NULL')) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
				$result = dbquery("UPDATE ".DB_ARTICLE_CATS." SET article_cat_name='$cat_name', article_cat_description='$cat_description', article_cat_sorting='$cat_sorting', article_cat_parent='$cat_parent', article_cat_language='$cat_language'  WHERE article_cat_id='".$_GET['cat_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			} else {
				$checkCat = dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_name='".$cat_name."'");
				if ($checkCat == 0) {
					$result = dbquery("INSERT INTO ".DB_ARTICLE_CATS." (article_cat_name, article_cat_description, article_cat_sorting, article_cat_parent, article_cat_language) VALUES ('$cat_name', '$cat_description', '$cat_sorting', '$cat_parent', '".$cat_language."')");
					redirect(FUSION_SELF.$aidlink."&status=sn");
				} else {
					// method to validate.
					$defender->stop();
					$defender->addNotice($locale['articles_0352']);
				}
			}
		}
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
		$result = dbquery("SELECT article_cat_name, article_cat_description, article_cat_sorting, article_cat_parent, article_cat_language FROM ".DB_ARTICLE_CATS." WHERE article_cat_id='".$_GET['cat_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$cat_name = $data['article_cat_name'];
			$cat_description = $data['article_cat_description'];
			$cat_language = $data['article_cat_language'];
			$cat_sorting = explode(" ", $data['article_cat_sorting']);
			if ($cat_sorting[0] == "article_id") {
				$cat_sort_by = "1";
			}
			if ($cat_sorting[0] == "article_subject") {
				$cat_sort_by = "2";
			}
			if ($cat_sorting[0] == "article_datestamp") {
				$cat_sort_by = "3";
			}
			$cat_sort_order = $cat_sorting[1];
			$cat_parent = $data['article_cat_parent'];
			$cat_hidden = array($_GET['cat_id']);
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$_GET['cat_id'];
			$openTable = $locale['articles_0022'];
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$cat_name = "";
		$cat_description = "";
		$cat_language = LANGUAGE;
		$cat_sort_by = "2";
		$cat_sort_order = "ASC";
		$cat_parent = "0";
		$cat_hidden = array();
		$formaction = FUSION_SELF.$aidlink;
		$openTable = $locale['articles_0021'];
	}
	opentable($openTable);
	echo openform('addcat', 'addcat', 'post', $formaction, array('downtime' => 1));
	echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='cat_name'>".$locale['articles_0300']." <span class='required'>*</span></label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_text('', 'cat_name', 'cat_name', $cat_name, array('required' => 1, 'error_text' => $locale['articles_0351']));
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='cat_description'>".$locale['articles_0301']."</label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_text('', 'cat_description', 'cat_description', $cat_description);
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='cat_parent'>".$locale['articles_0308']."</label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_select_tree("", "cat_parent", "cat_parent", $cat_parent, array("disable_opts" => $cat_hidden, "hide_disabled" => 1), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");
	echo "</td>\n";
	echo "</tr>\n";
	if (multilang_table("AR")) {
		echo "<tr><td class='tbl'><label for='cat_language'>".$locale['global_ML100']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_select('', 'cat_language', 'cat_language', $language_opts, $cat_language, array('placeholder' => $locale['choose']));
		echo "</tr>\n";
	} else {
		echo form_hidden('', 'cat_language', 'cat_language', $cat_language);
	}
	echo "<tr><td width='1%' class='tbl' style='white-space:nowrap'><label for='cat_sort_by'>".$locale['articles_0302']."</label></td>\n";
	echo "<td class='tbl'>\n";
	$array = array('1' => $locale['articles_0303'], '2' => $locale['articles_0304'], '3' => $locale['articles_0305']);
	echo form_select('', 'cat_sort_by', 'cat_sort_by', $array, $cat_sort_by, array('placeholder' => $locale['choose'], 'class' => 'pull-left m-r-10'));
	$array = array('ASC' => $locale['articles_0306'], 'DESC' => $locale['articles_0307']);
	echo form_select('', 'cat_sort_order', 'cat_sort_order', $array, $cat_sort_order, array('placeholder' => $locale['choose']));
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo form_button($locale['articles_0309'], 'save_cat', 'save_cat', $locale['articles_0309'], array('class' => 'btn-primary', 'inline' => 1));
	echo "</tr>\n</table>\n";
	echo closeform();
	closetable();

	opentable($locale['articles_0020']);
	echo "<table cellpadding='0' cellspacing='1' class='table table-responsive tbl-border center'>\n";

	$row_num = 0;
	
	showcatlist();

	if ($row_num == 0) {
		echo "<tr><td align='center' class='tbl1' colspan='2'>".$locale['articles_0342']."</td></tr>\n";
	}
	echo "</table>\n";

	closetable();
}

function showcatlist($parent = 0, $level = 0) {
	global $locale, $aidlink, $row_num;

	$result = dbquery("SELECT article_cat_id, article_cat_name, article_cat_description FROM ".DB_ARTICLE_CATS." WHERE article_cat_parent='".$parent."'".(multilang_table("AR") ? " AND article_cat_language='".LANGUAGE."'" : "")." ORDER BY article_cat_name");

	if (dbrows($result) != 0) {
		while ($data = dbarray($result)) {
			$cell_color = ($row_num%2 == 0 ? "tbl1" : "tbl2");
			echo "<tr>\n";
			echo "<td class='$cell_color'><strong>".str_repeat("&mdash;", $level).$data['article_cat_name']."</strong>\n";
			if ($data['article_cat_description']) {
				echo "<br />".str_repeat("&mdash;", $level)."<span class='small'>".trimlink($data['article_cat_description'], 45)."</span></td>\n";
			}
			echo "<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['article_cat_id']."'>".$locale['edit']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['article_cat_id']."' onclick=\"return confirm('".$locale['articles_0350']."');\">".$locale['delete']."</a></td>\n";
			echo "</tr>\n";
			$row_num++;
			showcatlist($data['article_cat_id'], $level + 1);
		}
	}
}

require_once THEMES."templates/footer.php";
?>