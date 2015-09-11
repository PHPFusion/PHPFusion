<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/faq.php
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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
require_once THEMES."templates/header.php";
include INFUSIONS."faq/locale/".LOCALESET."faq.php";
include "templates/faq.php";
add_to_title($locale['global_203']);
if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
	$result = dbquery("SELECT *	FROM ".DB_FAQ_CATS." ".(multilang_table("FQ") ? "WHERE faq_cat_language='".LANGUAGE."' AND" : "WHERE")." faq_cat_id='".intval($_GET['cat_id'])."'");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
		add_to_title($locale['global_201'].$data['faq_cat_name']);
		$data['faq_link'] = INFUSIONS."faq/faq.php?cat_id=".$data['faq_cat_id'];
		$info = $data;
		if (dbcount("(faq_id)", DB_FAQS, "faq_cat_id='".intval($_GET['cat_id'])."'")) {
			$result = dbquery("SELECT faq_id, faq_question, faq_answer from ".DB_FAQS." WHERE faq_cat_id='".intval($_GET['cat_id'])."' ORDER BY faq_question");
			while ($data = dbarray($result)) {
				$info['items'][$data['faq_id']] = $data;
			}
		} else {
			$info['nofaq_items'] = $locale['411'];
		}
		render_faq_item($info);
	} else {
		redirect(FUSION_SELF);
	}
} else {
	$result = dbquery("
				SELECT fc.faq_cat_id, fc.faq_cat_name, fc.faq_cat_description, fc.faq_cat_language,
				count(f.faq_id) 'faq_count'
	 			FROM ".DB_FAQ_CATS." fc
	 			LEFT JOIN ".DB_FAQS." f using (faq_cat_id)
	 			".(multilang_table("FQ") ? "WHERE faq_cat_language='".LANGUAGE."'" : "")."
	 			group by fc.faq_cat_id
	 			ORDER BY faq_cat_name
	 			");
	$info['faq_title'] = $locale['400'];
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			$data['faq_link'] = INFUSIONS."faq/faq.php?cat_id=".$data['faq_cat_id'];
			$info['items'][$data['faq_cat_id']] = $data;
		}
	} else {
		$info['nofaqs'] = $locale['410'];
	}
	render_faq($info);
}
require_once THEMES."templates/footer.php";