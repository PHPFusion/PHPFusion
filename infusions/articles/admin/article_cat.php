<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: article_cats_admin.php
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
pageAccess('AC');

if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(article_id)", DB_ARTICLES, "article_cat='".$_GET['cat_id']."'") || dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_parent='".$_GET['cat_id']."'");
	if (!empty($result)) {
		addNotice("danger", $locale['articles_0152']."<br />\n<span class='small'>".$locale['articles_0153']."</span>");
		redirect(clean_request("", array("section", "aid"), true));
	} else {
		$result = dbquery("DELETE FROM ".DB_ARTICLE_CATS." WHERE article_cat_id='".$_GET['cat_id']."'");
		addNotice("success",  $locale['articles_0154']);
		redirect(clean_request("cat_view=1", array("section", "aid"), true));
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
				addNotice("success", $locale['articles_0151']);
				redirect(clean_request("cat_view=1", array("section", "aid"), true));
			} else {
				$checkCat = dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_name='".$cat_name."'");
				if ($checkCat == 0) {
					$result = dbquery("INSERT INTO ".DB_ARTICLE_CATS." (article_cat_name, article_cat_description, article_cat_sorting, article_cat_parent, article_cat_language) VALUES ('$cat_name', '$cat_description', '$cat_sorting', '$cat_parent', '".$cat_language."')");
					addNotice("success",  $locale['articles_0150']);
					redirect(clean_request("cat_view=1", array("section", "aid"), true));
				} else {
					addNotice("danger", $locale['articles_0352']);
				}
			}
		}
	}

	$cat_name = "";
	$cat_description = "";
	$cat_language = LANGUAGE;
	$cat_sort_by = "2";
	$cat_sort_order = "ASC";
	$cat_parent = "0";
	$cat_hidden = array();

	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
		$result = dbquery("
		SELECT article_cat_id, article_cat_name, article_cat_description, article_cat_sorting, article_cat_parent, article_cat_language
		FROM ".DB_ARTICLE_CATS." WHERE article_cat_id='".intval($_GET['cat_id'])."'");
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
		} else {
			redirect(clean_request("", array("section", "aid"), true));
		}
	}

	// UI dual tab
	$articleCatTab['title'] = array($locale['articles_0027'], $locale['articles_0020']);
	$articleCatTab['id'] = array("a", "b");
	$tab_active = tab_active($articleCatTab, isset($_GET['cat_view']) ? 1 : 0);
	echo opentab($articleCatTab, $tab_active, "artCTab", FALSE, "m-t-20");
	echo opentabbody($articleCatTab['title'][0], $articleCatTab['id'][0], $tab_active);
	echo openform('addcat', 'post', FUSION_REQUEST, array('class' => "m-t-20"));
	echo form_text('cat_name', $locale['articles_0300'], $cat_name, array("inline"=>true, "required"=>true, 'error_text' => $locale['articles_0351']));
	$mce = array("html"=>true, "form_name" => "addcat", "preview"=>true, "autosize"=>true);
	if (fusion_get_settings("tinymce_enabled")) {
		echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
		$mce = array();
	}

	echo form_textarea('cat_description', $locale['articles_0301'], $cat_description, array("inline"=>true));
	echo form_select_tree("cat_parent", $locale['articles_0308'], $cat_parent, array(
										  "inline"=>true,
										  "disable_opts" => $cat_hidden,
										  "hide_disabled" => 1), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent"
	);
	if (multilang_table("AR")) {
		echo form_select('cat_language', $locale['global_ML100'], $cat_language, array(
			"inline"=>true,
			'options' => $language_opts,
			'placeholder' => $locale['choose']));
	} else {
		echo form_hidden('cat_language', '', $cat_language);
	}
	echo "<div class='row m-0'>\n";
	echo "<label class='label-control col-xs-12 col-sm-3 p-l-0'>".$locale['articles_0302']."</label>\n";

	echo "<div class='col-xs-12 col-sm-3  p-l-0'>\n";
	echo form_select('cat_sort_by', "", $cat_sort_by, array(
		"inline"=>true,
		"width" => "100%",
		'options' => array('1' => $locale['articles_0303'], '2' => $locale['articles_0304'], '3' => $locale['articles_0305']),
		'class' => 'pull-left m-r-10'));
	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-2'>\n";
	echo form_select('cat_sort_order', '', $cat_sort_order, array(
		"inline"=>true,
		"width" => "100%",
		'options' => array('ASC' => $locale['articles_0306'], 'DESC' => $locale['articles_0307']),
		'placeholder' => $locale['choose']));
	echo "</div>\n";
	echo "</div>\n";

	echo form_button('save_cat', $locale['articles_0309'], $locale['articles_0309'], array('class' => 'btn-primary',
		'inline' => 1));
	echo "</tr>\n</table>\n";
	echo closeform();
	echo closetabbody();
	echo opentabbody($articleCatTab['title'][1], $articleCatTab['id'][1], $tab_active);

	echo "<table class='table table-responsive table-hover table-striped'>\n";
	if (dbcount("(article_cat_id)", DB_ARTICLE_CATS, multilang_table("AR") ? "article_cat_language='".LANGUAGE."'" : "")) {
		showcatlist();
	} else {
		echo "<tr><td align='center' class='tbl1' colspan='2'>".$locale['articles_0342']."</td></tr>\n";
	}
	echo "</table>\n";

	echo closetabbody();
	echo closetab();
}


function showcatlist($parent = 0, $level = 0) {
	global $locale, $aidlink;
	$result = dbquery("
	SELECT article_cat_id, article_cat_name, article_cat_description
	FROM ".DB_ARTICLE_CATS."
	WHERE article_cat_parent='".$parent."'".(multilang_table("AR") ? " AND article_cat_language='".LANGUAGE."'" : "")."
	ORDER BY article_cat_name");
	$rows = dbrows($result);
	if ($rows > 0) {
		while ($data = dbarray($result)) {
			$description = strip_tags(html_entity_decode(stripslashes($data['article_cat_description'])));
			echo "<tr>\n";
			echo "<td><strong>".str_repeat("&mdash;", $level).$data['article_cat_name']."</strong>\n";
			if ($data['article_cat_description']) {
				echo "<br />".str_repeat("&mdash;", $level)."<span class='small'>".trimlink($description, 45)."</span></td>\n";
			}
			echo "<td align='center' width='1%' style='white-space:nowrap'>\n
			<a href='".clean_request("action=edit&cat_id=".$data['article_cat_id'], array("section", "aid"), true)."'>".$locale['edit']."</a> -\n";
			echo "<a href='".clean_request("action=delete&cat_id=".$data['article_cat_id'], array("section", "aid"), true)."' onclick=\"return confirm('".$locale['articles_0350']."');\">".$locale['delete']."</a></td>\n";
			echo "</tr>\n";
			showcatlist($data['article_cat_id'], $level+1);
		}
	}
}