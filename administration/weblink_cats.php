<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblink_cats.php
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
if (!checkrights("WC") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) redirect("../index.php");
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/weblinks.php";
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
	if ($message) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(weblink_cat)", DB_WEBLINKS, "weblink_cat='".$_GET['cat_id']."'");
	if (!empty($result)) {
		redirect(FUSION_SELF.$aidlink."&status=deln");
	} else {
		$result = dbquery("DELETE FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_id='".$_GET['cat_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=dely");
	}
} else {
	if (isset($_POST['save_cat'])) {
		$cat_name = form_sanitizer($_POST['cat_name'], '', 'cat_name'); // stripinput($_POST['cat_name']);
		$cat_description = stripinput($_POST['cat_description']);
		$cat_language = stripinput($_POST['cat_language']);
		$cat_access = isnum($_POST['cat_access']) ? $_POST['cat_access'] : "0";
		if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "1") {
			$cat_sorting = "weblink_id ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "2") {
			$cat_sorting = "weblink_name ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "3") {
			$cat_sorting = "weblink_datestamp ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else {
			$cat_sorting = "weblink_name ASC";
		}
		if (!defined('FUSION_NULL')) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
				$result = dbquery("UPDATE ".DB_WEBLINK_CATS." SET weblink_cat_name='$cat_name', weblink_cat_description='$cat_description', weblink_cat_sorting='$cat_sorting', weblink_cat_access='$cat_access', weblink_cat_language='$cat_language' WHERE weblink_cat_id='".$_GET['cat_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			} else {
				$checkCat = dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "weblink_cat_name='".$cat_name."'");
				if ($checkCat == 0) {
					$result = dbquery("INSERT INTO ".DB_WEBLINK_CATS." (weblink_cat_name, weblink_cat_description, weblink_cat_sorting, weblink_cat_access, weblink_cat_language) VALUES ('$cat_name', '$cat_description', '$cat_sorting', '$cat_access', '$cat_language')");
					redirect(FUSION_SELF.$aidlink."&status=sn");
				} else {
					$defender->stop();
					$defender->addNotice($locale['461']);
				}
			}
		}
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
		$result = dbquery("SELECT weblink_cat_name, weblink_cat_description, weblink_cat_sorting, weblink_cat_access, weblink_cat_language FROM ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."' AND" : "WHERE")." weblink_cat_id='".$_GET['cat_id']."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$cat_name = $data['weblink_cat_name'];
			$cat_description = $data['weblink_cat_description'];
			$cat_language = $data['weblink_cat_language'];
			$cat_sorting = explode(" ", $data['weblink_cat_sorting']);
			if ($cat_sorting[0] == "weblink_id") {
				$cat_sort_by = "1";
			} elseif ($cat_sorting[0] == "weblink_name") {
				$cat_sort_by = "2";
			} else {
				$cat_sort_by = "3";
			}
			$cat_sort_order = $cat_sorting[1];
			$cat_access = $data['weblink_cat_access'];
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$_GET['cat_id'];
			$openTable = $locale['401'];
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$cat_name = "";
		$cat_description = "";
		$cat_language = LANGUAGE;
		$cat_sort_by = "weblink_name";
		$cat_sort_order = "ASC";
		$cat_access = "";
		$formaction = FUSION_SELF.$aidlink;
		$openTable = $locale['400'];
	}
	$user_groups = getusergroups();
	$access_opts = array();
	while (list($key, $user_group) = each($user_groups)) {
		$access_opts[$user_group['0']] = $user_group['1'];
	}
	opentable($openTable);
	echo openform('addcat', 'addcat', 'post', $formaction, array('downtime' => 0));
	echo "<table cellpadding='0' cellspacing='0' class='table table-responsive'>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='cat_name'>".$locale['420']."</label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_text('', 'cat_name', 'cat_name', $cat_name, array('required' => 1, 'error_text' => $locale['460']));
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='cat_description'>".$locale['421']."</label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_text('', 'cat_description', 'cat_description', $cat_description);
	echo "</tr>\n";
	if (multilang_table("WL")) {
		echo "<tr><td class='tbl'><label for='cat_language'>\n".$locale['global_ML100']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_select('', 'cat_language', 'cat_language', $language_opts, $cat_language, array('placeholder' => $locale['choose']));
		echo "</td>\n</tr>\n";
	} else {
		echo form_hidden('', 'cat_language', 'cat_language', $cat_language);
	}
	echo "<tr><td width='1%' class='tbl' style='white-space:nowrap'><label for='cat_sort_by'>".$locale['422']."</label></td>\n";
	echo "<td class='tbl'>\n";
	$array = array('1' => $locale['423'], '2' => $locale['424'], '3' => $locale['425'],);
	echo form_select('', 'cat_sort_by', 'cat_sort_by', $array, $cat_sort_by, array('placeholder' => $locale['choose'],
																				   'class' => 'pull-left m-r-10'));
	$array = array('ASC' => $locale['426'], 'DESC' => $locale['427']);
	echo form_select('', 'cat_sort_order', 'cat_sort_order', $array, $cat_sort_order, array('placeholder' => $locale['choose'],
																							'class' => 'pull-left'));
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$locale['428']."</td>\n";
	echo "<td class='tbl'>\n";
	echo form_select('', 'cat_access', 'cat_access', $access_opts, $cat_access, array('placeholder' => $locale['choose']));
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo form_button($locale['429'], 'save_cat', 'save_cat', $locale['429'], array('class' => 'btn-primary m-t-10'));
	echo "</tr>\n</table>\n";
	echo closeform();
	closetable();
	opentable($locale['402']);
	echo "<table cellpadding='0' cellspacing='1' width='400' class='table table-responsive tbl-border center'>\n<thead>\n";
	$result = dbquery("SELECT weblink_cat_id, weblink_cat_name, weblink_cat_description, weblink_cat_access, weblink_cat_language FROM ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")." ORDER BY weblink_cat_name");
	if (dbrows($result) != 0) {
		$i = 0;
		echo "<tr>\n";
		echo "<th class='tbl2'>".$locale['430']."</th>\n";
		echo "<th align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['431']."</th>\n";
		echo "<th align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['532']."</th>\n";
		echo "</tr>\n";
		echo "</thead>\n<tbody>\n";
		while ($data = dbarray($result)) {
			$cell_color = ($i%2 == 0 ? "tbl1" : "tbl2");
			echo "<tr>\n";
			echo "<td class='$cell_color'><strong>".$data['weblink_cat_name']."</strong>\n";
			echo ($data['weblink_cat_description'] ? "<br />\n<span class='small'>".trimlink($data['weblink_cat_description'], 45)."</span>" : "")."</td>\n";
			echo "<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'>".getgroupname($data['weblink_cat_access'])."</td>\n";
			echo "<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['weblink_cat_id']."'>".$locale['533']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['weblink_cat_id']."' onclick=\"return confirm('".$locale['440']."');\">".$locale['534']."</a></td>\n";
			echo "</tr>\n";
			$i++;
		}
		echo "</tbody>\n";
		echo "</table>\n";
	} else {
		echo "<tr><td align='center' class='tbl1'>".$locale['536']."</td></tr>\n</table>\n";
	}
	closetable();
}
require_once THEMES."templates/footer.php";
?>