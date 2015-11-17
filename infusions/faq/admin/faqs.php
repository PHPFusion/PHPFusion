<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/admin/faqs.php
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
pageAccess('FQ');
if (fusion_get_settings("tinymce_enabled")) {
	$fusion_mce = array("required"=>true);
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	$fusion_mce = array("preview"=>true, "html"=>true, "autosize"=>true, "form_name" => "inputform", "required"=>true);
}

if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['faq_id']) && isnum($_GET['faq_id'])
&& isset($_GET['faq_cat_id']) && isnum($_GET['faq_cat_id'])
) {
	if (dbcount("(faq_id)", DB_FAQS, "faq_id='".intval($_GET['faq_id'])."'")) {
		dbquery("delete from ".DB_FAQS." WHERE faq_id='".intval($_GET['faq_id'])."'");
		addNotice("success", $locale['faq_0307']);
		$total_faqs = dbcount("(faq_id)", DB_FAQS, "faq_cat_id='".$_GET['faq_cat_id']."'");
		$faq_start = ($total_faqs > $show_faqs) ? floor($total_faqs/$show_faqs)*$show_faqs : 0;
		redirect(FUSION_SELF.$aidlink."&amp;show_faq=".$_GET['faq_cat_id']."&amp;faq_start=".$faq_start);
	}
}

$data = array(
	"faq_id" => 0,
	"faq_cat_id" => 0,
	"faq_question" => "",
	"faq_answer" => "",
);
if ($faq_edit) {
	$result = dbquery("select * from ".DB_FAQS." where faq_id='".intval($_GET['faq_id'])."'");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
if (isset($_POST['save_faq'])) {
	$data = array(
		"faq_id" => form_sanitizer($_POST['faq_id'], 0, "faq_id"),
		"faq_cat_id" => form_sanitizer($_POST['faq_cat_id'], 0, "faq_cat_id"),
		"faq_question" => form_sanitizer($_POST['faq_question'], "", "faq_question"),
		"faq_answer" => form_sanitizer($_POST['faq_answer'], "", "faq_answer"),
	);
	if (defender::safe()) {
		if (dbcount("(faq_id)", DB_FAQS, "faq_id='".$data['faq_id']."'")) {
			dbquery_insert(DB_FAQS, $data, "update");
			addNotice("success", $locale['faq_0306']);
		} else {
			dbquery_insert(DB_FAQS, $data, "save");
			addNotice("success", $locale['faq_0305']);
		}
		// it's 15 limiter in show_faq function
		// 5, 10, 15.
		// 17/5 = 3.4*5 = 15
		$total_faqs = dbcount("(faq_id)", DB_FAQS, "faq_cat_id='".$data['faq_cat_id']."'");
		$faq_start = ($total_faqs > $show_faqs) ? floor($total_faqs/$show_faqs)*$show_faqs : 0;
		redirect(FUSION_SELF.$aidlink."&amp;show_faq=".$data['faq_cat_id']."&amp;faq_start=".$faq_start);
	}
}
$cat_opts = array();
$result2 = dbquery("SELECT faq_cat_id, faq_cat_name, faq_cat_language
	FROM ".DB_FAQ_CATS." ".(multilang_table("FQ") ? "WHERE faq_cat_language='".LANGUAGE."'" : "")." ORDER BY faq_cat_name");
if (dbrows($result2) != 0) {
	while ($data2 = dbarray($result2)) {
		$cat_opts[$data2['faq_cat_id']] = $data2['faq_cat_name'];
	}
	echo openform('inputform', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8'>\n";
	openside("");
	echo form_hidden("faq_id", "", $data['faq_id']);
	echo form_text('faq_question', $locale['faq_0301'], $data['faq_question'], array('required' => TRUE));
	echo form_textarea('faq_answer', $locale['faq_0302'], $data['faq_answer'], $fusion_mce);
	closeside();
	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-4'>\n";
	openside("");
	echo form_select("faq_cat_id", $locale['faq_0300'], $data['faq_cat_id'], array('options' => $cat_opts, "width"=>"100%"));
	closeside();
	echo "</div>\n";
	echo "</div>\n";
	echo form_button('save_faq', $locale['faq_0303'], $locale['faq_0303'], array('class' => 'btn-primary m-t-10'));
	echo closeform();
} else {
	echo "<div class='well text-center m-t-20'>\n";
    echo str_replace(array("[LINK]", "[/LINK]"),
                     array("<a href='".clean_request("section=faq-category", array("aid"), TRUE)."'>", "</a>"),
                     $locale['faq_0304']);
	echo "</div>\n";
}