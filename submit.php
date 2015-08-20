<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submit.php
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
require_once "maincore.php";
require_once THEMES."templates/header.php";
include_once INCLUDES."bbcode_include.php";
include LOCALE.LOCALESET."submit.php";
if (!iMEMBER) {
	redirect("index.php");
}
$stype = filter_input(INPUT_GET, 'stype') ? : '';
$submit_info = array();
$modules = array(
	'n' => db_exists(DB_NEWS),
	'p' => db_exists(DB_PHOTO_ALBUMS),
	'a' => db_exists(DB_ARTICLES),
	'd' => db_exists(DB_DOWNLOADS),
	'l' => db_exists(DB_WEBLINKS),
	'b' => db_exists(DB_BLOG));
$sum = array_sum($modules);
if (!$sum or empty($modules[$stype])) {
	redirect("index.php");

} elseif ($stype === "l") {
	if (isset($_POST['submit_link'])) {
		$submit_info['link_category'] = form_sanitizer($_POST['link_category'], '', 'link_category');
		$submit_info['link_name'] = form_sanitizer($_POST['link_name'], '', 'link_name');
		$submit_info['link_url'] = form_sanitizer($_POST['link_url'], '', 'link_url');
		$submit_info['link_description'] = form_sanitizer($_POST['link_description'], '', 'link_description');
		if (!defined("FUSION_NULL")) {
			$result = dbquery("INSERT INTO ".DB_SUBMISSIONS." (submit_type, submit_user, submit_datestamp, submit_criteria) VALUES ('l', '".$userdata['user_id']."', '".time()."', '".addslashes(serialize($submit_info))."')");
			add_to_title($locale['global_200'].$locale['400']);
			opentable($locale['400']);
			echo "<div style='text-align:center'><br />\n".$locale['410']."<br /><br />\n";
			echo "<a href='submit.php?stype=l'>".$locale['411']."</a><br /><br />\n";
			echo "<a href='index.php'>".$locale['412']."</a><br /><br />\n</div>\n";
			closetable();
		}
	}
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
	closetable();
} elseif ($stype === "n") {
	include INFUSIONS."news/news_submit.php";
} elseif ($stype === "b") {
	include INFUSIONS."blog/blog_submit.php";
} elseif ($stype === "a") {
	include INFUSIONS."articles/article_submit.php";

} elseif ($stype === "p") {
	if (isset($_POST['submit_photo'])) {
		require_once INCLUDES."photo_functions_include.php";
		$error = "";
		$submit_info['photo_title'] = form_sanitizer($_POST['photo_title'], '', 'photo_title');
		$submit_info['photo_description'] = form_sanitizer($_POST['photo_description'], '', 'photo_description');
		$submit_info['album_id'] = isnum($_POST['album_id']) ? $_POST['album_id'] : "0";
		$submit_info['album_photo_file'] = form_sanitizer($_FILES['album_photo_file'], '', 'album_photo_file');
		add_to_title($locale['global_200'].$locale['570']);
		opentable($locale['570']);
		if (!defined('FUSION_NULL')) {
			$result = dbquery("INSERT INTO ".DB_SUBMISSIONS." (submit_type, submit_user, submit_datestamp, submit_criteria) VALUES ('p', '".$userdata['user_id']."', '".time()."', '".addslashes(serialize($submit_info))."')");
			echo "<div style='text-align:center'><br />\n".$locale['580']."<br /><br />\n";
			echo "<a href='submit.php?stype=p'>".$locale['581']."</a><br /><br />\n";
			echo "<a href='index.php'>".$locale['412']."</a><br /><br />\n</div>\n";
		} else {
			echo "<div style='text-align:center'><br />\n".$locale['600']."<br /><br />\n";
			echo "<br /><br />\n<a href='submit.php?stype=p'>".$locale['581']."</a><br /><br />\n</div>\n";
		}
		closetable();
	}
	$opts = "";
	add_to_title($locale['global_200'].$locale['570']);
	opentable($locale['570']);
	$result = dbquery("SELECT album_id, album_title FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess("album_access")." ORDER BY album_title");
	if (dbrows($result)) {
		$opts = array();
		while ($data = dbarray($result)) {
			$opts[$data['album_id']] = $data['album_title'];
		}
		echo openform('submit_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."submit.php?stype=p", array('enc_type' => 1,
			'max_tokens' => 1));
		echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
		echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['620']."</div>\n";
		echo form_select('album_id', $locale['625'], '', array("options" => $opts));
		echo form_text('photo_title', $locale['621'], '', array('required' => 1));
		echo form_textarea('photo_description', $locale['622'], '');
		echo sprintf($locale['624'], parsebytesize($settings['photo_max_b']), $settings['photo_max_w'], $settings['photo_max_h'])."<br/>\n";
		echo form_fileinput('photo_pic_file', $locale['623'], '', array("upload_path" => PHOTOS."submissions/",
			"type" => "image",
			"required" => TRUE));
		echo "</div>\n</div>\n";
		echo form_button('submit_photo', $locale['626'], $locale['626'], array('class' => 'btn-primary'));
		echo closeform();
	} else {
		echo "<div class='well' style='text-align:center'><br />\n".$locale['552']."<br /><br />\n</div>\n";
	}
	closetable();
} elseif ($stype === "d") {
	include INFUSIONS."downloads/download_submit.php";
}
require_once THEMES."templates/footer.php";
