<?php
// News 9.0 submissions
$news_settings = get_settings("news");
include INFUSIONS."news/locale/".LOCALESET."news_admin.php";

if (iMEMBER && $news_settings['news_allow_submission']) {

	$criteriaArray = array(
		"news_subject" => "",
		"news_cat" => 0,
		"news_snippet" => "",
		"news_body" => "",
		"news_language" => LANGUAGE,
		"news_keywords" => "",
		"news_ialign" => "",
	);

	if (isset($_POST['submit_news'])) {
		$submit_info['news_news'] = nl2br(parseubb(stripinput($_POST['news_news'])));
		$submit_info['news_body'] = nl2br(parseubb(stripinput($_POST['news_body'])));
		$criteriaArray = array(
			"news_subject" => form_sanitizer($_POST['news_subject'], "", "news_subject"),
			"news_cat" => form_sanitizer($_POST['news_cat'], "", "news_cat"),
			"news_snippet" => form_sanitizer($submit_info['news_news'], "", "news_news"),
			"news_body" => form_sanitizer($submit_info['news_body'], "", "news_body"),
			"news_language" => form_sanitizer($_POST['news_language'], "", "news_language"),
			"news_keywords" => form_sanitizer($_POST['news_keywords'], "", "news_keywords"),
		);

		if ($news_settings['news_allow_submission_files']) {
			if (isset($_FILES['news_image'])) {
				$upload = form_sanitizer($_FILES['news_image'], '', 'news_image');
				if (!empty($upload)) {
					$criteriaArray['news_image'] = $upload['image_name'];
					$criteriaArray['news_image_t1'] = $upload['thumb1_name'];
					$criteriaArray['news_image_t2'] = $upload['thumb2_name'];
					$criteriaArray['news_ialign'] = (isset($_POST['news_ialign']) ? form_sanitizer($_POST['news_ialign'], "pull-left", "news_ialign") : "pull-left");
				} else {
					$criteriaArray['news_image'] = (isset($_POST['news_image']) ? $_POST['news_image'] : "");
					$criteriaArray['news_image_t1'] = (isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "");
					$criteriaArray['news_image_t2'] = (isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "");
					$criteriaArray['news_ialign'] = (isset($_POST['news_ialign']) ? form_sanitizer($_POST['news_ialign'], "pull-left", "news_ialign") : "pull-left");
				}
			}
		}

		if (defender::safe()) {
			$inputArray = array(
				"submit_type" => "n",
				"submit_user" => $userdata['user_id'],
				"submit_datestamp" => time(),
				"submit_criteria" => addslashes(serialize($criteriaArray))
			);
			dbquery_insert(DB_SUBMISSIONS, $inputArray, "save");
			addNotice("success", $locale['460']);
			redirect(clean_request("submitted=n", array("stype"), true));
		}
	}

	if (isset($_GET['submitted']) && $_GET['submitted'] == "n") {
		add_to_title($locale['global_200'].$locale['450']);
		opentable($locale['450']);
		echo "<div class='well text-center'><p><strong>".$locale['460']."</strong></p>";
		echo "<p><a href='submit.php?stype=n'>".$locale['461']."</a></p>";
		echo "<p><a href='index.php'>".$locale['412']."</a></p>\n";
		echo "</div>\n";
		closetable();
	}

	// Preview
	if (isset($_POST['preview_news'])) {
		$news_subject = stripinput($_POST['news_subject']);
		$news_cat = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
		$news_snippet = stripinput($_POST['news_news']);
		$news_body = stripinput($_POST['news_body']);
		opentable($news_subject);
		echo $locale['478']." ".nl2br(parseubb($news_snippet))."<br /><br />";
		echo $locale['472']." ".nl2br(parseubb($news_body));
		closetable();
	}

	add_to_title($locale['global_200'].$locale['450']);
	opentable("<i class='fa fa-newspaper-o fa-lg m-r-10'></i>".$locale['450']);
	echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
	echo "<div class='m-b-20 submission-guidelines'>".$locale['470']."</div>\n";
	echo openform('submit_form', 'post', (fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."submit.php?stype=n", array("enctype"=>$news_settings['news_allow_submission_files'] ? true : false));
	echo form_text('news_subject', $locale['471'], $criteriaArray['news_subject'], array("required" => true, "inline"=>true));
	if (multilang_table("NS")) {
		echo form_select('news_language', $locale['global_ML100'], $criteriaArray['news_language'],
						 array(
							 "options" => fusion_get_enabled_languages(),
							 "placeholder" => $locale['choose'],
							 "width" => "250px",
							 "inline" => true,
						 ));
	} else {
		echo form_hidden('news_language', '', $criteriaArray['news_language']);
	}

	echo form_select('news_keywords', $locale['news_0205'], $criteriaArray['news_keywords'],
					 array(
						 "max_length" => 320,
						 "inline"=>true,
						 "placeholder"=> $locale['news_0205a'],
						 "width" => "100%",
						 "error_text" => $locale['news_0255'],
						 "tags" => true,
						 "multiple" => true
					 )
	);
	echo form_select_tree("news_cat", $locale['476'], $criteriaArray['news_cat'],
						  array(
							  "width" => "250px",
							  "inline"=>true,
							  "parent_value" => $locale['news_0202'],
							  "query" => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")
						  ),
						  DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent"
	);

	if ($news_settings['news_allow_submission_files']) {
		$file_input_options = array(
			'upload_path' => IMAGES_N,
			'max_width' => $news_settings['news_photo_max_w'],
			'max_height' => $news_settings['news_photo_max_h'],
			'max_byte' => $news_settings['news_photo_max_b'],
			// set thumbnail
			'thumbnail' => 1,
			'thumbnail_w' => $news_settings['news_thumb_w'],
			'thumbnail_h' => $news_settings['news_thumb_h'],
			'thumbnail_folder' => 'thumbs',
			'delete_original' => 0,
			// set thumbnail 2 settings
			'thumbnail2' => 1,
			'thumbnail2_w' => $news_settings['news_photo_w'],
			'thumbnail2_h' => $news_settings['news_photo_h'],
			'type' => 'image',
			"inline"=> true,
		);
		echo form_fileinput("news_image", $locale['news_0216'], "", $file_input_options);

		echo "<div class='small col-sm-offset-3 m-b-10'><span class='p-l-15'>".sprintf($locale['news_0217'], parsebytesize($news_settings['news_photo_max_b']))."</span></div>\n";
		$alignOptions = array(
			'pull-left' => $locale['left'],
			'news-img-center' => $locale['center'],
			'pull-right' => $locale['right']
		);
		echo form_select('news_ialign', $locale['news_0218'], $criteriaArray['news_ialign'], array("options" => $alignOptions, "inline"=>true));
	}

	echo form_textarea('news_news', $locale['478'], $criteriaArray['news_snippet'],
					   array(
						   "required" => true,
						   "html" => true,
						   "form_name" => "submit_form",
						   "autosize"=>fusion_get_settings("tinymce_enabled") ? false : true
					   )
	);
	echo form_textarea('news_body', $locale['472'], $criteriaArray['news_body'],
					   array(
						   "required" => $news_settings['news_extended_required'] ? true : false,
						   "html" => true,
						   "form_name" => "submit_form",
						   "autosize"=>fusion_get_settings("tinymce_enabled") ? false : true)
	);
	echo fusion_get_settings("site_seo") ? "" : form_button('preview_news', $locale['474'], $locale['474'], array('class' => 'btn-primary m-r-10'));
	echo form_button('submit_news', $locale['475'], $locale['475'], array('class' => 'btn-success'));
	echo closeform();
	echo "</div>\n</div>\n";

} else {
	echo "<div class='well text-center'>".$locale['news_0138']."</div>\n";
}
closetable();