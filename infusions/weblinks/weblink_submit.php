<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: article_submit.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$wl_settings = get_settings("weblinks");
if (file_exists(INFUSIONS."weblinks/locale/".LOCALESET."weblinks_admin.php")) {
	include INFUSIONS."weblinks/locale/".LOCALESET."weblinks_admin.php";
} else {
	include INFUSIONS."weblinks/locale/English/weblinks_admin.php";
}
add_to_title($locale['global_200'].$locale['wl_0800']);
opentable("<i class='fa fa-globe fa-lg m-r-10'></i>".$locale['wl_0800']);
if (iMEMBER && $wl_settings['links_allow_submission']) {
	//@todo: patch in TinyMCE
	$criteriaArray = array(
		"weblink_name" => "",
		"weblink_cat" => 0,
		"weblink_url" => "",
		"weblink_description" => "",
	);
	if (dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, multilang_table("WL") ? "weblink_cat_language='".LANGUAGE."'" : "")) {
		if (isset($_POST['submit_link'])) {
			$submit_info['weblink_description'] = nl2br(parseubb(stripinput($_POST['weblink_description'])));
			$criteriaArray = array(
				"weblink_name" => form_sanitizer($_POST['weblink_name'], "", "weblink_name"),
				"weblink_cat" => form_sanitizer($_POST['weblink_cat'], "", "weblink_cat"),
				"weblink_description" => form_sanitizer($submit_info['weblink_description'], "", "weblink_description"),
				"weblink_url" => form_sanitizer($_POST['weblink_url'], "", "weblink_url"),
			);
			if (defender::safe()) {
				$inputArray = array(
					"submit_type" => "l",
					"submit_user" => $userdata['user_id'],
					"submit_datestamp" => time(),
					"submit_criteria" => addslashes(serialize($criteriaArray))
				);
				dbquery_insert(DB_SUBMISSIONS, $inputArray, "save");
				addNotice("success", $locale['wl_0801']);
				redirect(clean_request("submitted=l", array("stype"), TRUE));
			}
		}
		if (isset($_GET['submitted']) && $_GET['submitted'] == "l") {
			echo "<div class='well text-center'><p><strong>".$locale['wl_0801']."</strong></p>";
			echo "<p><a href='submit.php?stype=a'>".$locale['wl_0802']."</a></p>";
			echo "<p><a href='index.php'>".$locale['wl_0803']."</a></p>\n";
			echo "</div>\n";
		} else {
			echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
			echo "<div class='m-b-20 submission-guidelines'>".$locale['wl_0804']."</div>\n";
            echo openform('submit_form', 'post', BASEDIR."submit.php?stype=l");
			echo form_select_tree("weblink_cat", $locale['wl_0805'], $criteriaArray['weblink_cat'], array(
				"inline" => TRUE,
				"no_root" => 1,
				"placeholder" => $locale['choose'],
				"query" => (multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")
			), DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");
			echo form_text('weblink_name', $locale['wl_0806'], $criteriaArray['weblink_name'], array(
				"placeholder" => $locale['wl_0101'],
				"error_text" => $locale['wl_0102'],
				"inline" => TRUE,
				'required' => TRUE
			));
			echo form_text('weblink_url', $locale['wl_0807'], $criteriaArray['weblink_url'], array(
				"type" => "url",
				"placeholder" => "http://",
				"required" => TRUE,
				"inline" => TRUE
			));
			echo form_textarea('weblink_description', $locale['wl_0808'], $criteriaArray['weblink_description'], array(
				"class" => "m-t-20",
				"inline" => TRUE,
				"html" => TRUE,
				"preview" => FALSE,
				"autosize" => TRUE,
				"required" => $wl_settings['links_extended_required'] ? TRUE : FALSE,
				"form_name" => "inputform",
			));
			echo form_button('submit_link', $locale['wl_0800'], $locale['wl_0800'], array('class' => 'btn-success'));
			echo closeform();
			echo "</div>\n</div>\n";
		}
	} else {
		echo "<div class='well text-center'>\n".$locale['537']."<br />\n".$locale['538']."</div>\n";
	}
} else {
	echo "<div class='well text-center'>".$locale['wl_0809']."</div>\n";
}
closetable();