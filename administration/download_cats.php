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
pageAccess('DC');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/downloads.php";
add_breadcrumb(array('link'=>ADMIN."download_cats.php".$aidlink, 'title'=>$locale['download_0001']));

if (isset($_POST['cancel'])) {
	redirect(FUSION_SELF.$aidlink);
}
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(download_cat)", DB_DOWNLOADS, "download_cat='".$_GET['cat_id']."'") || dbcount("(download_cat_id)", DB_DOWNLOAD_CATS, "download_cat_parent='".$_GET['cat_id']."'");
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
					$defender->addNotice($locale['download_0352']);
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
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;section=dadd&amp;cat_id=".$data['download_cat_id'];
			$openTable = $locale['download_0021'];
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
		$openTable = $locale['download_0001'];

	}

	opentable($openTable);

	$message = '';
	if (isset($_GET['status'])) {
		switch($_GET['status']) {
			case 'sn':
				$message = $locale['download_0150'];
				$status = 'success';
				$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
				break;
			case 'su':
				$message = $locale['download_0151'];
				$status = 'info';
				$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
				break;
			case 'deln':
				$message = $locale['download_0152']." - ".$locale['download_0153'];
				$status = 'danger';
				$icon = "<i class='fa fa-trash fa-lg fa-fw'></i>";
				break;
			case 'dely':
				$message = $locale['download_0154'];
				$status = 'danger';
				$icon = "<i class='fa fa-trash fa-lg fa-fw'></i>";
				break;
		}
		if ($message) {
			addNotice($status, $icon.$message);
		}
	}

	$allowed_section = array('dcats', 'dadd');
	$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'dcats';
	$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;
	$tab_title['title'][] = $locale['download_0020'];
	$tab_title['id'][] = "dcats";
	$tab_title['icon'][] = '';

	$tab_title['title'][] = $edit ? $locale['download_0021'] : $locale['download_0022'];
	$tab_title['id'][] = "dadd";
	$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

	$tab_active = tab_active($tab_title, $_GET['section'], 1);

	echo opentab($tab_title, $tab_active, 'dcategory', 1);
	echo opentabbody($tab_title['title'][0], 'dcats', $tab_active,1);
	echo "<div class='list-group m-t-20'>\n";
	$row_num = 0;
	showcatlist();
	if ($row_num == 0) {
		echo "<div class='well text-center'>".$locale['download_0313']."</div>\n";
	}
	echo "</div>\n";
	echo closetabbody();
	if ($_GET['section'] == 'dadd') {
		add_breadcrumb(array('link'=>'', 'title'=>$edit ? $locale['download_0021'] : $locale['download_0022']));
		fusion_confirm_exit();
		echo opentabbody($tab_title['title'][1], 'dadd', $tab_active, 1);
		echo openform('addcat', 'post', $formaction, array('max_tokens' => 1, 'class'=>'m-t-20'));
		$array = array('1' => $locale['download_0303'], '2' => $locale['download_0304'], '3' => $locale['download_0305']);
		$array2 = array('ASC' => $locale['download_0306'], 'DESC' => $locale['download_0307']);
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		openside('');
		echo form_text('cat_name', $locale['download_0300'], $cat_name, array('required' => 1, 'error_text' => $locale['download_0351']));
		echo form_textarea('cat_description', $locale['download_0301'], $cat_description, array('resize'=>0, 'autosize'=>1));
		echo "<div class='clearfix'>\n";
		echo form_select('cat_sort_by', $locale['download_0302'], $array, $cat_sort_by, array('placeholder' => $locale['choose'], 'class' => 'pull-left m-r-10', 'width'=>'200px'));
		echo form_select('cat_sort_order', '', $array2, $cat_sort_order, array('placeholder' => $locale['choose'], 'class'=>'pull-left', 'width'=>'200px'));
		echo "</div>\n";
		closeside();
		echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
		openside('');
		echo form_select_tree("cat_parent", $locale['download_0308'], $cat_parent, array("disable_opts" => $cat_hidden, "hide_disabled" => 1, 'width'=>'100%'), DB_DOWNLOAD_CATS, "download_cat_name", "download_cat_id", "download_cat_parent");
		if (multilang_table("DL")) {
			echo form_select('cat_language', $locale['global_ML100'], $language_opts, $cat_language, array('placeholder' => $locale['choose'], 'width'=>'100%'));
		} else {
			form_hidden('', 'cat_language', 'cat_language', $cat_language);
		}
		closeside();
		echo "</div>\n</div>\n";
		echo form_button('save_cat', $locale['download_0309'], $locale['download_0309'], array('class' => 'btn-success btn-sm m-t-10', 'icon'=>'fa fa-check-square-o'));
		echo closeform();
		echo closetabbody();
	}
	echo closetab();
	closetable();
}

function showcatlist($parent = 0, $level = 0) {
	global $locale, $aidlink, $row_num;
	$result = dbquery("SELECT download_cat_id, download_cat_name, download_cat_description FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_parent='$parent'".(multilang_table("DL") ? " AND download_cat_language='".LANGUAGE."'" : "")." ORDER BY download_cat_name");
	if (dbrows($result) != 0) {
		while ($data = dbarray($result)) {
			echo "<div class='list-group-item clearfix'>\n";

			echo "<div class='btn-group pull-right m-t-5'>\n";
			echo "<a class='btn btn-sm btn-default' href='".FUSION_SELF.$aidlink."&amp;section=dadd&amp;action=edit&amp;cat_id=".$data['download_cat_id']."'><i class='fa fa-pencil fa-fw'></i> ".$locale['edit']."</a>";
			echo "<a class='btn btn-sm btn-default' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['download_cat_id']."' onclick=\"return confirm('".$locale['download_0350']."');\"><i class='fa fa-trash fa-fw'></i> ".$locale['delete']."</a>\n";
			echo "</div>\n";

			echo "<div class='overflow-hide p-r-10'>\n";
			echo "<span class='display-inline-block m-r-10 strong text-bigger'>".str_repeat("&mdash;", $level).$data['download_cat_name']."</span>";
			if ($data['download_cat_description']) {
				echo "<br />".str_repeat("&mdash;", $level)."<span class='small'>".fusion_first_words($data['download_cat_description'], 50)."</span>";
			}
			echo "</div>\n";
			echo "</div>\n";
			$row_num++;
			showcatlist($data['download_cat_id'], $level + 1);
		}
	}
}

require_once THEMES."templates/footer.php";
?>