<?php


/*
 * if (isset($_POST['submit_photo'])) {
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
 */