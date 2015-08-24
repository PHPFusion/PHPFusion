<?php

$result = dbcount("(weblink_cat_id)", DB_WEBLINK_CATS);
if (!empty($result)) {
	$data = array(
		"weblink_id" => 0,
		"weblink_name" => "",
		"weblink_cat" => 0,
		"weblink_description" => "",
		"weblink_visibility" => iGUEST,
		"weblink_url" => "",
		"weblink_datestamp" => time(),
	);
	if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['weblink_id']) && isnum($_GET['weblink_id']))) {
		$result = dbquery("DELETE FROM ".DB_WEBLINKS." WHERE weblink_id='".$_GET['weblink_id']."'");
		addNotice("success", $locale['wl_0302']);
		redirect(FUSION_SELF.$aidlink);
	}
	if (isset($_POST['save_link'])) {
		$data = array(
			"weblink_id" => form_sanitizer($_POST['weblink_id'], 0, 'weblink_id'),
			"weblink_cat" => form_sanitizer($_POST['weblink_cat'], 0, 'weblink_cat'),
			"weblink_name" => form_sanitizer($_POST['weblink_name'], '', 'weblink_name'),
			"weblink_description" => form_sanitizer($_POST['weblink_description'], '', 'weblink_description'),
			"weblink_visibility" => form_sanitizer($_POST['weblink_visibility'], '0', 'weblink_visibility'),
			"weblink_url" => form_sanitizer($_POST['weblink_url'], '', 'weblink_url'),
			"weblink_datestamp" => form_sanitizer($_POST['weblink_datestamp'], '', 'weblink_datestamp'),
		);
		if (defender::safe()) {
			if (dbcount("(weblink_id)", DB_WEBLINKS, "weblink_id='".intval($data['weblink_id'])."'")) {
				$data['weblink_datestamp'] = isset($_POST['update_datestamp']) ? time() : $data['weblink_datestamp'];
				dbquery_insert(DB_WEBLINKS, $data, "update");
				addNotice("success", $locale['wl_0300']);
				redirect(FUSION_SELF.$aidlink);
			} else {
				dbquery_insert(DB_WEBLINKS, $data, "save");
				addNotice("success", $locale['wl_0301']);
				redirect(FUSION_SELF.$aidlink);
			}
		}
	}
	if ($weblink_edit) {
		$result = dbquery("SELECT * FROM ".DB_WEBLINKS." WHERE weblink_id='".intval($_GET['weblink_id'])."'");
		if (dbrows($result)) {
			$data = dbarray($result);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
	echo openform('inputform', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8'>\n";
	echo form_text('weblink_name', $locale['wl_0100'], $data['weblink_name'], array(
									 "placeholder" => $locale['wl_0101'],
									 "error_text" => $locale['wl_0102'],
									 "inline" => TRUE,
									 'required' => TRUE
								 ));
	echo form_text('weblink_url', $locale['wl_0104'], $data['weblink_url'], array(
		"type" => "url",
		"placeholder" => "http://",
		"required" => TRUE,
		"inline" => TRUE
	));
	echo form_textarea('weblink_description', $locale['wl_0103'], $data['weblink_description'], array(
												"inline" => TRUE,
												"html" => TRUE,
												"preview" => TRUE,
												"autosize" => TRUE,
												"form_name" => "inputform",
											));
	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-4'>\n";
	if ($weblink_edit) echo form_checkbox("update_datestamp", $locale['wl_0107'], "");
	openside("");
	echo form_select_tree("weblink_cat", $locale['wl_0105'], $data['weblink_cat'], array(
		"inline" => TRUE,
		"no_root" => 1,
		"placeholder" => $locale['choose'],
		"query" => (multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")
	), DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");
	echo form_select('weblink_visibility', $locale['wl_0106'], $data['weblink_visibility'], array(
		"inline" => TRUE,
		'options' => fusion_get_groups()
	));
	echo form_button('save_link', $locale['wl_0108'], $locale['wl_0108'], array(
		"input_id" => "savelink2",
		'class' => 'btn-primary m-t-10'
	));
	closeside();
	echo "</div>\n</div>\n";
	echo form_button('save_link', $locale['wl_0108'], $locale['wl_0108'], array('class' => 'btn-primary m-t-10'));
	echo closeform();
} else {
	echo "<div class='text-center'>\n".$locale['537']."<br />\n".$locale['538']."<br />\n<br />\n";
	echo "<a href='".INFUSIONS."weblinks/weblinks_admin.php".$aidlink."&amp;section=weblinks_category'>".$locale['539']."</a>".$locale['540']."</div>\n";
}