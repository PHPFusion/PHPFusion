<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: download_cats.php
| Author: PHP-Fusion Development Team
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
	if ($message) {
		echo "<div id='close-message'><div class='admin-message alert alert-warning m-t-10'>".$message."</div></div>\n";
	}
}
if (isset($_POST['cancel'])) {
	redirect(FUSION_SELF.$aidlink);
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
		$cat_name = form_sanitizer($_POST['cat_name'], '', 'cat_name');
		$cat_description = stripinput($_POST['cat_description']);
		$cat_language = stripinput($_POST['cat_language']);
		$cat_parent = isnum($_POST['cat_parent']) ? $_POST['cat_parent'] : "0";
		if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "1") {
			$cat_sorting = "download_id ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "2") {
			$cat_sorting = "download_title ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $_POST['cat_sort_by'] == "3") {
			$cat_sorting = "download_datestamp ".($_POST['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else {
			$cat_sorting = "download_title ASC";
		}
		if ($cat_name && !defined('FUSION_NULL')) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
				$result = dbquery("UPDATE ".DB_DOWNLOAD_CATS." SET download_cat_parent = '$cat_parent', download_cat_name='$cat_name', download_cat_description='$cat_description', download_cat_sorting='$cat_sorting', download_cat_language='$cat_language' WHERE download_cat_id='".$_GET['cat_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			} else {
				$checkCat = dbcount("(download_cat_id)", DB_DOWNLOAD_CATS, "download_cat_name='".$cat_name."'");
				if ($checkCat == 0) {
					$result = dbquery("INSERT INTO ".DB_DOWNLOAD_CATS." (download_cat_parent, download_cat_name, download_cat_description, download_cat_sorting, download_cat_language) VALUES('$cat_parent', '$cat_name', '$cat_description', '$cat_sorting', '$cat_language')");
					redirect(FUSION_SELF.$aidlink."&status=sn");
				} else {
					$defender->stop();
					$defender->addNotice($locale['461']);
				}
			}
		}
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
		$result = dbquery("SELECT * FROM ".DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." download_cat_id='".$_GET['cat_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$cat_parent = $data['download_cat_parent'];
			$cat_hidden = array($data['download_cat_id']);
			$cat_name = $data['download_cat_name'];
			$cat_description = $data['download_cat_description'];
			$cat_language = $data['download_cat_language'];
			$cat_sorting = explode(" ", $data['download_cat_sorting']);
			if ($cat_sorting[0] == "download_id") {
				$cat_sort_by = "1";
			} elseif ($cat_sorting[0] == "download_title") {
				$cat_sort_by = "2";
			} elseif ($cat_sorting[0] == "download_datestamp") {
				$cat_sort_by = "3";
			} else {
				$cat_sort_by = "";
			}
			$cat_sort_order = $cat_sorting[1];
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['download_cat_id'];
			$openTable = $locale['400'];
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$cat_parent = "0";
		$cat_hidden = array();
		$cat_name = "";
		$cat_description = "";
		$cat_language = LANGUAGE;
		$cat_sort_by = "";
		$cat_sort_order = "ASC";
		$formaction = FUSION_SELF.$aidlink;
		$openTable = $locale['401'];
	}

	opentable($openTable);

	$tab_title['title'][] = "Category Listing";
	$tab_title['id'][] = "dcats";
	$tab_title['icon'][] = '';

	$tab_title['title'][] = "Add Category";
	$tab_title['id'][] = "dadd";
	$tab_title['icon'][] = '';

	$tab_active = tab_active($tab_title, 0);

	echo opentab($tab_title, $tab_active, 'dcategory');

	echo opentabbody($tab_title['title'][0], 'dcats', $tab_active);
	echo "<div class='list-group m-t-20'>\n";
	$result = dbquery("SELECT download_cat_id, download_cat_name, download_cat_description FROM ".DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")." ORDER BY download_cat_name");
	if (dbrows($result) != 0) {
		$i = 0;
		while ($data = dbarray($result)) {
			echo "<div class='list-group-item clearfix'>\n";

			echo "<div class='btn-group pull-right m-t-5'>\n";
			echo "<a class='btn btn-sm btn-default' href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['download_cat_id']."'>".$locale['443']."</a>";
			echo "<a class='btn btn-sm btn-default' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['download_cat_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['444']."</a>\n";
			echo "</div>\n";

			echo "<div class='overflow-hide p-r-10'>\n";
			echo "<span class='display-inline-block m-r-10 strong text-bigger'>".$data['download_cat_name']."</span>";
			if ($data['download_cat_description']) {
				echo "<br /><span class='small'>".trim_word($data['download_cat_description'], 50)."</span>";
			}
			echo "</div>\n";
			echo "</div>\n";
		}
	} else {
		echo "<div class='well text-center'>".$locale['445']."</div>\n";
	}
	echo "</div>\n";
	echo closetabbody();

	echo opentabbody($tab_title['title'][1], 'dadd', $tab_active);
	echo openform('addcat', 'addcat', 'post', $formaction, array('downtime' => 0, 'class'=>'m-t-20'));
	echo form_text($locale['420'], 'cat_name', 'cat_name', $cat_name, array('required' => 1, 'error_text' => $locale['460']));
	echo form_textarea($locale['421'], 'cat_description', 'cat_description', $cat_description, array('resize'=>0));
	echo form_select_tree($locale['428'], "cat_parent", "cat_parent", $cat_parent, array("disable_opts" => $cat_hidden, "hide_disabled" => 1), DB_DOWNLOAD_CATS, "download_cat_name", "download_cat_id", "download_cat_parent");
	if (multilang_table("DL")) {
		echo form_select($locale['global_ML100'], 'cat_language', 'cat_language', $language_opts, $cat_language, array('placeholder' => $locale['choose']));
	} else {
		form_hidden('', 'cat_language', 'cat_language', $cat_language);
	}
	$array = array('1' => $locale['423'], '2' => $locale['424'], '3' => $locale['425']);
	$array2 = array('ASC' => $locale['426'], 'DESC' => $locale['427']);
	echo "<div class='clearfix'>\n";
	echo form_select($locale['422'], 'cat_sort_by', 'cat_sort_by', $array, $cat_sort_by, array('placeholder' => $locale['choose'], 'class' => 'pull-left m-r-10'));
	echo form_select('', 'cat_sort_order', 'cat_sort_order', $array2, $cat_sort_order, array('placeholder' => $locale['choose']));
	echo "</div>\n";
	echo form_button($locale['cancel'], 'cancel', 'cancel', $locale['cancel'], array('class' => 'btn-default btn-sm m-t-10 m-r-10'));
	echo form_button($locale['429'], 'save_cat', 'save_cat', $locale['429'], array('class' => 'btn-primary btn-sm m-t-10'));
	echo closeform();
	echo closetabbody();
	echo closetab();
	closetable();
}
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['cat_id'])) {
	add_to_jquery("
		// change the name of the second tab and activate it.
		$('#tab-daddAdd-Category').text('Edit Category');
		$('#dcategory a:last').tab('show');
		");
}
require_once THEMES."templates/footer.php";
?>