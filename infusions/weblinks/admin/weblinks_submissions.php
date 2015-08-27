<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks_submissions.php
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

if (fusion_get_settings("tinymce_enabled")) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
}

if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {

	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".intval($_GET['submit_id'])."'");
		if (dbrows($result)) {
			$data = dbarray($result);

			$data = array(
				'weblink_name' => form_sanitizer($_POST['weblink_name'], '', 'weblink_name'),
				'weblink_cat' => form_sanitizer($_POST['weblink_cat'], 0, 'weblink_cat'),
				'weblink_url' =>  form_sanitizer($_POST['weblink_url'], "", 'weblink_url'),
				'weblink_description' => form_sanitizer($_POST['weblink_description'], "", "weblink_description"),
				'weblink_datestamp' => form_sanitizer($_POST['weblink_datestamp'], time(), 'weblink_datestamp'),
				'weblink_visibility' => form_sanitizer($_POST['weblink_visibility'], 0, 'weblink_visibility'),
			);

			if (defender::safe()) {
				dbquery_insert(DB_WEBLINKS, $data, "save");
				$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."'");
				addNotice("success", $locale['wl_0508']);
				redirect(clean_request("", array("submit_id"), FALSE));
			}
		} else {
			redirect(clean_request("", array("submit_id"), FALSE));
		}
	}
	else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("
			SELECT
			ts.submit_id, ts.submit_datestamp, ts.submit_criteria
			FROM ".DB_SUBMISSIONS." ts
			WHERE submit_type='l' and submit_id='".intval($_GET['submit_id'])."'
		");
		if (dbrows($result) > 0) {
			$data = dbarray($result);
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($data['submit_id'])."'");
			addNotice("success", $locale['wl_0507']);
		}
		redirect(clean_request("", array("submit_id"), FALSE));
	}
	else {
		$result = dbquery("SELECT
			ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".intval($_GET['submit_id'])."' order by submit_datestamp desc");
		if (dbrows($result) > 0) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$callback_data = array(
				"weblink_name" => $submit_criteria['weblink_name'],
				"weblink_cat" => $submit_criteria['weblink_cat'],
				"weblink_url" => $submit_criteria['weblink_url'],
				"weblink_visibility" => 0,
				"weblink_description" => html_entity_decode(stripslashes($submit_criteria['weblink_description'])),
				"weblink_datestamp" => $data['submit_datestamp'],
			);

			//add_to_title($locale['global_200'].$locale['503'].$locale['global_201'].$callback_data['weblink_name']."");

			echo openform("publish_weblink", "post", FUSION_REQUEST);
			echo "<div class='well clearfix'>\n";
			echo "<div class='pull-left'>\n";
			echo display_avatar($data, "30px", "", "", "");
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo $locale['wl_0511'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
			echo $locale['wl_0510'].timer($data['submit_datestamp'])." - ".showdate("shortdate", $data['submit_datestamp']);
			echo "</div>\n";
			echo "</div>\n";

			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-8'>\n";
			echo form_hidden('weblink_datestamp', '', $callback_data['weblink_datestamp']);
			echo form_text('weblink_name', $locale['wl_0100'], $callback_data['weblink_name'], array(
				"placeholder" => $locale['wl_0101'],
				"error_text" => $locale['wl_0102'],
				"inline" => TRUE,
				'required' => TRUE
			));
			echo form_text('weblink_url', $locale['wl_0104'], $callback_data['weblink_url'], array(
				"type" => "url",
				"placeholder" => "http://",
				"required" => TRUE,
				"inline" => TRUE
			));
			echo form_textarea('weblink_description', $locale['wl_0103'], $callback_data['weblink_description'], array(
				"inline" => TRUE,
				"html" => fusion_get_settings("tinymce_enabled") ? FALSE: TRUE,
				"preview" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
				"autosize" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
				"form_name" => "inputform",
			));
			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-4'>\n";
			if ($weblink_edit) echo form_checkbox("update_datestamp", $locale['wl_0107'], "");
			openside("");
			echo form_select_tree("weblink_cat", $locale['wl_0105'], $callback_data['weblink_cat'], array(
				"inline" => TRUE,
				"no_root" => 1,
				"placeholder" => $locale['choose'],
				"query" => (multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")
			), DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");
			echo form_select('weblink_visibility', $locale['wl_0106'], $callback_data['weblink_visibility'], array(
				"inline" => TRUE,
				'options' => fusion_get_groups()
			));
			echo form_button('save_link', $locale['wl_0108'], $locale['wl_0108'], array(
				"input_id" => "savelink2",
				'class' => 'btn-primary m-t-10'
			));
			closeside();
			echo "</div>\n</div>\n";
			echo form_button('publish_link', $locale['wl_0108'], $locale['wl_0108'], array('class' => 'btn-primary m-t-10'));
			echo closeform();
		}
	}
}
else {

	$result = dbquery("SELECT
			ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_type='l' order by submit_datestamp desc
			");

	$rows = dbrows($result);
	if ($rows > 0) {
		echo "<div class='well'>".sprintf($locale['wl_0007'], format_word($rows, $locale['fmt_submission']))."</div>\n";
		echo "<table class='table table-striped'>\n";
		echo "<tr>\n";
		echo "<th>".$locale['wl_0503']."</th>\n<th>".$locale['wl_0504']."</th><th>".$locale['wl_0505']."</th><th>".$locale['wl_0506']."</th>";
		echo "</tr>\n";
		echo "<tbody>\n";
		while ($data = dbarray($result)) {
			$submit_criteria = unserialize($data['submit_criteria']);
			echo "<tr>\n";
			echo "<td><a href='".clean_request("submit_id=".$data['submit_id'], array(
					"section",
					"aid"
				), TRUE)."'>".$submit_criteria['weblink_name']."</a></td>\n";
			echo "<td>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
			echo "<td>".timer($data['submit_datestamp'])."</td>\n";
			echo "<td>".$data['submit_id']."</td>\n";
			echo "</tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} else {
		echo "<div class='well text-center m-t-20'>".$locale['wl_0008']."</div>\n";
	}
}