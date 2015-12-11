<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/faq_cats.php
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
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(faq_cat_id)", DB_FAQS, "faq_cat_id='".intval($_GET['cat_id'])."'");
	if (!empty($result)) {
		addNotice('danger', sprintf($locale['faq_0207'], $result));
		redirect(FUSION_SELF.$aidlink);
	} else {
		$result = dbquery("DELETE FROM ".DB_FAQ_CATS." WHERE faq_cat_id='".intval($_GET['cat_id'])."'");
		addNotice("success", $locale['faq_0206']);
		redirect(FUSION_SELF.$aidlink);
	}
}
$data = array(
	"faq_cat_id" => 0,
	"faq_cat_name" => "",
	"faq_cat_description" => "",
	"faq_cat_language" => LANGUAGE,
);
if ($faqCat_edit) {
	$result = dbquery("select * from ".DB_FAQ_CATS." WHERE faq_cat_id='".intval($_GET['cat_id'])."'");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
if (isset($_POST['save_cat'])) {
	$data = array(
		"faq_cat_id" => form_sanitizer($_POST['faq_cat_id'], 0, "faq_cat_id"),
		"faq_cat_name" => form_sanitizer($_POST['faq_cat_name'], "", "faq_cat_name"),
		"faq_cat_description" => form_sanitizer($_POST['faq_cat_description'], "", "faq_cat_description"),
		"faq_cat_language" => form_sanitizer($_POST['faq_cat_language'], "", "faq_cat_language"),
	);
	if (defender::safe()) {
		if (dbcount("(faq_cat_id)", DB_FAQ_CATS, "faq_cat_id='".$data['faq_cat_id']."'")) {
			dbquery_insert(DB_FAQ_CATS, $data, "update");
			addNotice("success", $locale['faq_0205']);
		} else {
			dbquery_insert(DB_FAQ_CATS, $data, "save");
			addNotice("success", $locale['faq_0204']);
		}
		redirect(FUSION_SELF.$aidlink);
	}
}
echo openform('faqCat_form', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
echo form_hidden('faq_cat_id', "", $data['faq_cat_id']);
echo form_text('faq_cat_name', $locale['faq_0200'], $data['faq_cat_name'], array(
	'error_text' => $locale['faq_0201'],
	'required' => 1
));
echo form_text('faq_cat_description', $locale['faq_0202'], $data['faq_cat_description']);
if (multilang_table("FQ")) {
	echo form_select("faq_cat_language", $locale['global_ML100'], $data['faq_cat_language'], array('options' => fusion_get_enabled_languages()));
} else {
	echo form_hidden("faq_cat_language", '', LANGUAGE);
}
echo form_button('save_cat', $locale['faq_0203'], $locale['faq_0203'], array('class' => 'btn-primary m-t-10'));
echo closeform();