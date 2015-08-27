<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks_submit.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (isset($_POST['submit_link'])) {
	$submit_info['link_category'] = form_sanitizer($_POST['link_category'], '', 'link_category');
	$submit_info['link_name'] = form_sanitizer($_POST['link_name'], '', 'link_name');
	$submit_info['link_url'] = form_sanitizer($_POST['link_url'], '', 'link_url');
	$submit_info['link_description'] = form_sanitizer($_POST['link_description'], '', 'link_description');
	$result = dbquery("INSERT INTO ".DB_SUBMISSIONS." (submit_type, submit_user, submit_datestamp, submit_criteria) VALUES ('l', '".$userdata['user_id']."', '".time()."', '".addslashes(serialize($submit_info))."')");
	redirect(clean_request("submitted=l", array("stype"), TRUE));
}

if (isset($_GET['submitted']) && $_GET['submitted'] == "l") {
	add_to_title($locale['global_200'].$locale['400']);
	opentable($locale['400']);
	echo "<div style='text-align:center'><br />\n".$locale['410']."<br /><br />\n";
	echo "<a href='submit.php?stype=l'>".$locale['411']."</a><br /><br />\n";
	echo "<a href='index.php'>".$locale['412']."</a><br /><br />\n</div>\n";
	closetable();
	} else {
		add_to_title($locale['global_200'].$locale['400']);
		opentable($locale['400']);
		$result = dbquery("SELECT weblink_cat_id, weblink_cat_name FROM ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")." ORDER BY weblink_cat_name");
	if (dbrows($result) > 0) {
		$opts = array();
		while ($data = dbarray($result)) {
			$opts[$data['weblink_cat_id']] = $data['weblink_cat_name'];
		}
		echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
		echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['420']."</div>\n";
		echo openform('submit_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."submit.php?stype=l", array('max_tokens' => 1));
		echo form_select('link_category', $locale['421'], isset($_POST['link_category']) ? $_POST['link_category'] : '', array("options" => $opts,
			"required" => TRUE));
		echo form_text('link_name', $locale['422'], isset($_POST['link_name']) ? $_POST['link_name'] : '', array("required" => TRUE));
		echo form_text('link_url', $locale['423'], isset($_POST['link_url']) ? $_POST['link_url'] : '', array("required" => TRUE,
			'placeholder' => 'http://'));
		echo form_text('link_description', $locale['424'], isset($_POST['link_description']) ? $_POST['link_description'] : '', array("required" => TRUE,
			'max_length' => '200'));
		echo form_button('submit_link', $locale['425'], $locale['425'], array('class' => 'btn-primary'));
		echo closeform();
		echo "</div>\n</div>\n";
	} else {
		echo "<div class='well' style='text-align:center'><br />\n".$locale['551']."<br /><br />\n</div>\n";
	}
}
closetable();