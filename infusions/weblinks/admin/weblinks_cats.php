<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
pageAccess("W");
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(weblink_cat)", DB_WEBLINKS, "weblink_cat='".$_GET['cat_id']."'") || dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "weblink_cat_parent='".$_GET['cat_id']."'");
	if (!empty($result)) {
		addNotice("danger", $locale['wl_0307'].$locale['wl_0308']);
		redirect(clean_request("", array("section", "aid"), TRUE));
	} else {
		$result = dbquery("DELETE FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_id='".$_GET['cat_id']."'");
		addNotice("success", $locale['wl_0306']);
		redirect(clean_request("", array("section", "aid"), TRUE));
	}
} else {
	$cat_hidden = array();
	$data = array(
		"weblink_cat_id" => 0,
		"weblink_cat_name" => "",
		"weblink_cat_description" => "",
		"weblink_cat_language" => LANGUAGE,
		"weblink_cat_parent" => "",
		"cat_sort_by" => 2,
		"cat_sort_order" => "ASC",
	);
	if (isset($_POST['save_cat'])) {
		$data = array(
			"weblink_cat_id" => form_sanitizer($_POST['weblink_cat_id'], 0, 'weblink_cat_id'),
			"weblink_cat_name" => form_sanitizer($_POST['weblink_cat_name'], '', 'weblink_cat_name'),
			"weblink_cat_description" => form_sanitizer($_POST['weblink_cat_description'], '', 'weblink_cat_description'),
			"weblink_cat_language" => form_sanitizer($_POST['weblink_cat_language'], LANGUAGE, 'weblink_cat_language'),
			"weblink_cat_parent" => form_sanitizer($_POST['weblink_cat_parent'], 0, 'weblink_cat_parent'),
		);
		$data['cat_sort_by'] = form_sanitizer($_POST['cat_sort_by'], 2, "cat_sort_by");
		$data['cat_sort_order'] = form_sanitizer($_POST['cat_sort_order'], "ASC", "cat_sort_order");
		if (isnum($data['cat_sort_by']) && $data['cat_sort_by'] == "1") {
			$data['weblink_cat_sorting'] = "weblink_id ".($data['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $data['cat_sort_by'] == "2") {
			$data['weblink_cat_sorting'] = "weblink_name ".($data['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else if (isnum($_POST['cat_sort_by']) && $data['cat_sort_by'] == "3") {
			$data['weblink_cat_sorting'] = "weblink_datestamp ".($data['cat_sort_order'] == "ASC" ? "ASC" : "DESC");
		} else {
			$data['weblink_cat_sorting'] = "weblink_name ASC";
		}
		$categoryNameCheck = array(
			"when_updating" => "weblink_cat_name='".$data['weblink_cat_name']."' and weblink_cat_id !='".$data['weblink_cat_id']."'",
			"when_saving" => "weblink_cat_name='".$data['weblink_cat_name']."'",
		);
		if (defender::safe()) {
			if ($weblinkCat_edit && dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "weblink_cat_id='".intval($data['weblink_cat_id'])."'")) {
				if (!dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, $categoryNameCheck['when_updating'])) {
					dbquery_insert(DB_WEBLINK_CATS, $data, "update");
					addNotice("success", $locale['wl_0305']);
					redirect(clean_request("", array("section", "aid"), TRUE));
				} else {
					$defender->stop();
					addNotice("danger", $locale['wl_0309']);
				}
			} else {
				if (!dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, $categoryNameCheck['when_saving'])) {
					dbquery_insert(DB_WEBLINK_CATS, $data, "save");
					addNotice("success", $locale['wl_0304']);
					redirect(clean_request("", array("section", "aid"), TRUE));
				} else {
					$defender->stop();
					addNotice("danger", $locale['wl_0309']);
				}
			}
		}
	}
	if ($weblinkCat_edit) {
		$result = dbquery("SELECT * FROM ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."' AND" : "WHERE")." weblink_cat_id='".intval($_GET['cat_id'])."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$cat_hidden = array($data['weblink_cat_id']);
			$cat_sorting = explode(" ", $data['weblink_cat_sorting']);
			if ($cat_sorting[0] == "weblink_id") {
				$data['cat_sort_by'] = "1";
			} elseif ($cat_sorting[0] == "weblink_name") {
				$data['cat_sort_by'] = "2";
			} else {
				$data['cat_sort_by'] = "3";
			}
			$data['cat_sort_order'] = $cat_sorting[1];
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
	$wlCatTab['title'] = array($locale['wl_0710'], $locale['wl_0004']);
	$wlCatTab['id'] = array("a", "b");
	$tab_active = tab_active($wlCatTab, isset($_GET['cat_view']) ? 1 : 0);
	echo opentab($wlCatTab, $tab_active, "wlCat_tab", FALSE, "m-t-20");
	echo opentabbody($wlCatTab['title'][0], $wlCatTab['id'][0], $tab_active);
	echo openform('addcat', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
	echo form_hidden("weblink_cat_id", "", $data['weblink_cat_id']);
	echo form_text('weblink_cat_name', $locale['wl_0700'], $data['weblink_cat_name'], array(
										 'required' => TRUE,
										 "error_text" => $locale['wl_0701'],
										 "inline" => TRUE,
									 ));
	echo form_textarea('weblink_cat_description', $locale['wl_0702'], $data['weblink_cat_description'], array(
		"html" => TRUE,
		"preview" => FALSE,
		"autosize" => TRUE,
		"inline" => TRUE,
	));
	echo form_select_tree("weblink_cat_parent", $locale['wl_0703'], $data['weblink_cat_parent'], array(
												  "disable_opts" => $cat_hidden,
												  "hide_disabled" => TRUE,
												  "inline" => TRUE,
											  ), DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");
	if (multilang_table("WL")) {
		echo form_select('weblink_cat_language', $locale['global_ML100'], $data['weblink_cat_language'], array(
												   'options' => fusion_get_enabled_languages(),
												   "inline" => TRUE,
											   ));
	} else {
		echo form_hidden('weblink_cat_language', '', $data['weblink_cat_language']);
	}
	echo "<div class='row m-0'>\n";
	echo "<label class='label-control col-xs-12 col-sm-3 p-l-0'>".$locale['wl_0704']."</label>\n";
	echo "<div class='col-xs-12 col-sm-3  p-l-0'>\n";
	echo form_select('cat_sort_by', "", $data['cat_sort_by'], array(
		"inline" => TRUE,
		"width" => "100%",
		'options' => array('1' => $locale['wl_0705'], '2' => $locale['wl_0706'], '3' => $locale['wl_0707']),
		'class' => 'pull-left m-r-10'
	));
	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-2'>\n";
	echo form_select('cat_sort_order', '', $data['cat_sort_order'], array(
		"inline" => TRUE,
		"width" => "100%",
		'options' => array('ASC' => $locale['wl_0708'], 'DESC' => $locale['wl_0709']),
	));
	echo "</div>\n";
	echo "</div>\n";
	echo form_button('save_cat', $locale['wl_0711'], $locale['wl_0711'], array('class' => 'btn-primary m-t-10'));
	echo closeform();
	echo closetabbody();
	echo opentabbody($wlCatTab['title'][1], $wlCatTab['id'][1], $tab_active);
	$row_num = 0;
	echo "<table class='table table-responsive table-hover table-striped'>\n";
	showcatlist();
	if ($row_num == 0) {
		echo "<tr><td align='center' class='tbl1'>".$locale['536']."</td></tr>\n";
	}
	echo "</table>\n";
	echo closetabbody();
	echo closetab();
}
function showcatlist($parent = 0, $level = 0) {
	global $locale, $aidlink, $row_num;
	$result = dbquery("SELECT weblink_cat_id, weblink_cat_name, weblink_cat_description FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_parent='".$parent."'".(multilang_table("WL") ? " AND weblink_cat_language='".LANGUAGE."'" : "")." ORDER BY weblink_cat_name");
	if (dbrows($result) != 0) {
		while ($data = dbarray($result)) {
			$description = strip_tags(html_entity_decode(stripslashes($data['weblink_cat_description'])));
			echo "<tr>\n";
			echo "<td><strong>".str_repeat("&mdash;", $level).$data['weblink_cat_name']."</strong>\n";
			if ($data['weblink_cat_description']) {
				echo "<br />".str_repeat("&mdash;", $level)."<span class='small'>".$description."</span></td>\n";
			}
			echo "<td align='center' width='1%' style='white-space:nowrap'>\n
			<a href='".FUSION_SELF.$aidlink."&amp;section=weblinks_category&amp;action=edit&amp;cat_id=".$data['weblink_cat_id']."'>".$locale['wl_0205']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;section=weblinks_category&amp;action=delete&amp;cat_id=".$data['weblink_cat_id']."' onclick=\"return confirm('".$locale['wl_0310']."');\">".$locale['wl_0206']."</a></td>\n";
			echo "</tr>\n";
			$row_num++;
			showcatlist($data['weblink_cat_id'], $level+1);
		}
	}
}
