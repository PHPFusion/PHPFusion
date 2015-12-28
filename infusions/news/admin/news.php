<?php
$formaction = FUSION_REQUEST;
$data = array(
	'news_id' => 0,
	'news_draft' => 0,
	'news_sticky' => 0,
	'news_news' => '',
	'news_datestamp' => time(),
	'news_extended' => '',
	'news_keywords' => '',
	'news_breaks' => 'n',
	'news_allow_comments' => 1,
	'news_allow_ratings' => 1,
	'news_language' => LANGUAGE,
	'news_visibility' => 0,
	'news_subject' => '',
	'news_start' => '',
	'news_end' => '',
	'news_cat' => 0,
	'news_image' => '',
	'news_ialign' => 'pull-left',
);
if (fusion_get_settings("tinymce_enabled")) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
	$data['news_breaks'] = 'n';
} else {
	require_once INCLUDES."html_buttons_include.php";
	$data['news_breaks'] = 'y';
}
if (isset($_POST['save'])) {

    $news_news = "";
	if ($_POST['news_news']) {
		$news_news = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslashes($_POST['news_news']));
		$news_news = parse_textarea($news_news);
	}

    $news_extended = "";
	if ($_POST['news_extended']) {
		$news_extended = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslashes($_POST['news_extended']));
		$news_extended = parse_textarea($news_extended);
	}

	$data = array(
		'news_id' => form_sanitizer($_POST['news_id'], 0, 'news_id'),
		'news_subject' => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
		'news_cat' => form_sanitizer($_POST['news_cat'], 0, 'news_cat'),
		'news_news' => form_sanitizer($news_news, "", "news_news"),
		'news_extended' => form_sanitizer($news_extended, "", "news_extended"),
		'news_keywords' => form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
		'news_datestamp' => form_sanitizer($_POST['news_datestamp'], '', 'news_datestamp'),
		'news_start' => form_sanitizer($_POST['news_start'], 0, 'news_start'),
		'news_end' => form_sanitizer($_POST['news_end'], 0, 'news_end'),
		'news_visibility' => form_sanitizer($_POST['news_visibility'], 0, 'news_visibility'),
		'news_draft' => isset($_POST['news_draft']) ? "1" : "0",
		'news_sticky' => isset($_POST['news_sticky']) ? "1" : "0",
		'news_allow_comments' => isset($_POST['news_allow_comments']) ? "1" : "0",
		'news_allow_ratings' => isset($_POST['news_allow_ratings']) ? "1" : "0",
		'news_language' => form_sanitizer($_POST['news_language'], '', 'news_language'),
		'news_image' => "",
		'news_ialign' => "",
		'news_image_t1' => "",
		'news_image_t2' => "",
	);

	if (isset($_FILES['news_image'])) { // when files is uploaded.
		$upload = form_sanitizer($_FILES['news_image'], '', 'news_image');
		if (!empty($upload) && !$upload['error']) {
			$data['news_image'] = $upload['image_name'];
			$data['news_image_t1'] = $upload['thumb1_name'];
			$data['news_image_t2'] = $upload['thumb2_name'];
			$data['news_ialign'] = (isset($_POST['news_ialign']) ? form_sanitizer($_POST['news_ialign'], "pull-left", "news_ialign") : "pull-left");
		}
	} else { // when files not uploaded. but there should be exist check.
		$data['news_image'] = (isset($_POST['news_image']) ? $_POST['news_image'] : "");
		$data['news_image_t1'] = (isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "");
		$data['news_image_t2'] = (isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "");
		$data['news_ialign'] = (isset($_POST['news_ialign']) ? form_sanitizer($_POST['news_ialign'], "pull-left", "news_ialign") : "pull-left");
	}

    if (fusion_get_settings('tinymce_enabled') != 1) {
		$data['news_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
	} else {
		$data['news_breaks'] = "n";
	}

    if ($data['news_sticky'] == "1") $result = dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'"); // reset other sticky

	// delete image
	if (isset($_POST['del_image'])) {
		if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
			unlink(IMAGES_N.$data['news_image']);
		}
		if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
			unlink(IMAGES_N_T.$data['news_image_t1']);
		}
		if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
			unlink(IMAGES_N_T.$data['news_image_t2']);
		}
		$data['news_image'] = "";
		$data['news_image_t1'] = "";
		$data['news_image_t2'] = "";
	}

	if (defender::safe()) {

		if (dbcount("('news_id')", DB_NEWS, "news_id='".$data['news_id']."'")) {

			dbquery_insert(DB_NEWS, $data, 'update');

            addNotice('success', $locale['news_0101']);

            redirect(FUSION_SELF.$aidlink);
		} else {

            $data['news_name'] = $userdata['user_id'];

            dbquery_insert(DB_NEWS, $data, 'save');

            addNotice('success', $locale['news_0100']);

            redirect(FUSION_SELF.$aidlink);
		}
	}
} elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
	$result = dbquery("SELECT * FROM ".DB_NEWS." WHERE news_id='".(isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'])."'");
	if (dbrows($result)) {
		$data = dbarray($result);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
$result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
$news_cat_opts = array();
$news_cat_opts['0'] = $locale['news_0202'];
if (dbrows($result)) {
	while ($odata = dbarray($result)) {
		$news_cat_opts[$odata['news_cat_id']] = $odata['news_cat_name'];
	}
}
if (isset($_POST['preview'])) {
	$news_news = "";
	if ($_POST['news_news']) {
		$news_news = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslashes($_POST['news_news']));
		$news_news = parse_textarea($news_news);
	}
	$news_extended = "";
	if ($_POST['news_extended']) {
		$news_extended = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslashes($_POST['news_extended']));
		$news_extended = parse_textarea($news_extended);
	}
	$data = array(
		"news_id" => form_sanitizer($_POST['news_id'], 0, 'news_id'),
		"news_subject" => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
		"news_cat" => isnum($_POST['news_cat']) ? $_POST['news_cat'] : 0,
		"news_language" => form_sanitizer($_POST['news_language'], '', 'news_language'),
		"news_news" => form_sanitizer($news_news, "", "news_news"),
		"news_extended" => form_sanitizer($news_extended, "", "news_extended"),
		"news_keywords" => form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
		"news_start" => (isset($_POST['news_start']) && $_POST['news_start']) ? $_POST['news_start'] : '',
		"news_end" => (isset($_POST['news_end']) && $_POST['news_end']) ? $_POST['news_end'] : '',
		"news_image" => isset($_POST['news_image']) ? $_POST['news_image'] : '',
		"news_image_t1" => isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "",
		"news_image_t2" => isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "",
		"news_ialign" => (isset($_POST['news_ialign']) ? $_POST['news_ialign'] : "pull-left"),
		"news_visibility" => isnum($_POST['news_visibility']) ? $_POST['news_visibility'] : "0",
		"news_draft" => isset($_POST['news_draft']) ? TRUE : FALSE,
		"news_sticky" => isset($_POST['news_sticky']) ? TRUE : FALSE,
		"news_allow_comments" => isset($_POST['news_allow_comments']) ? TRUE : FALSE,
		"news_allow_ratings" => isset($_POST['news_allow_ratings']) ? TRUE : FALSE,
		"news_datestamp" => form_sanitizer($_POST['news_datestamp'], "", "news_datestamp"),
	);
	$data['news_breaks'] = "";
	if (isset($_POST['news_breaks'])) {
		$data['news_breaks'] = TRUE;
		$data['news_news'] = nl2br($news_news);
		if ($data['news_extended']) {
			$data['news_extended'] = nl2br($news_extended);
		}
	}
	if (defender::safe()) {
		echo openmodal('news_preview', $locale['news_0141']);
		echo parse_textarea($data['news_news']);
		if (isset($data['news_extended'])) {
			echo parse_textarea($data['news_extended']);
		}
		echo closemodal();
	}
}
echo "<div class='m-t-20'>\n";
echo openform('inputform', 'post', $formaction, array('enctype' => 1, 'max_tokens' => 1));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
echo form_hidden('news_id', "", $data['news_id']);
echo form_text('news_subject', $locale['news_0200'], $data['news_subject'], array(
	'required' => 1,
	'max_length' => 200,
	'error_text' => $locale['news_0250']
));
echo form_select('news_keywords', $locale['news_0205'], $data['news_keywords'], array(
	"max_length" => 320,
	"placeholder" => $locale['news_0205a'],
	"width" => "100%",
	"error_text" => $locale['news_0255'],
	"tags" => TRUE,
	"multiple" => TRUE
));
echo "<div class='pull-left m-r-10 display-inline-block'>\n";
echo form_datepicker('news_start', $locale['news_0206'], $data['news_start'],
                     array(
                         'placeholder' => $locale['news_0208'],
                         "join_to_id" => "news_end"
                     )
);

echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
echo form_datepicker('news_end', $locale['news_0207'], $data['news_end'],
                     array(
                         'placeholder' => $locale['news_0208'],
                         "join_from_id" => "news_start"
                     )
);

echo "</div>\n";
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
openside('');
echo form_select_tree("news_cat", $locale['news_0201'], $data['news_cat'], array(
	"width" => "100%",
	"inline" => TRUE,
	"parent_value" => $locale['news_0202'],
	"query" => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")
), DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent");
echo form_select('news_visibility', $locale['news_0209'], $data['news_visibility'], array(
	'options' => fusion_get_groups(),
	'placeholder' => $locale['choose'],
	'width' => '100%',
	"inline" => TRUE,
));
if (multilang_table("NS")) {
	echo form_select('news_language', $locale['global_ML100'], $data['news_language'], array(
		'options' => fusion_get_enabled_languages(),
		'placeholder' => $locale['choose'],
		'width' => '100%',
		"inline" => TRUE,
	));
} else {
	echo form_hidden('news_language', '', $data['news_language']);
}
echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-r-10'));
echo form_button('save', $locale['news_0241'], $locale['news_0241'], array(
	'class' => 'btn-success',
	'icon' => 'fa fa-square-check-o'
));
closeside();
echo "</div>\n</div>\n";
$snippetSettings = array(
    "required" => TRUE,
    "preview" => TRUE,
    "html" => TRUE,
    "autosize" => TRUE,
    "placeholder" => $locale['news_0203a'],
    "form_name" => "inputform"
);
if (fusion_get_settings("tinymce_enabled")) {
    $snippetSettings = array("required" => TRUE, "type"=>"tinymce", "tinymce"=>"advanced");
}
echo form_textarea('news_news', $locale['news_0203'], $data['news_news'], $snippetSettings);
if (!fusion_get_settings("tinymce_enabled")) {
    $extendedSettings = array(
        "preview" => TRUE,
        "html" => TRUE,
        "autosize" => TRUE,
        "placeholder" => $locale['news_0203b'],
        "form_name" => "inputform"
    );
} else {
    $extendedSettings = array("type"=>"tinymce", "tinymce"=>"advanced");
}
echo form_textarea('news_extended', $locale['news_0204'], $data['news_extended'], $extendedSettings);
// second row
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
openside('');
if ($data['news_image'] != "" && $data['news_image_t1'] != "") {
    $image_thumb = get_news_image_path($data['news_image'], $data['news_image_t1'], $data['news_image_t2']);
    if (!$image_thumb) {
        $image_thumb = IMAGES."imagenotfound70.jpg";
    }
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-6'>\n";
    echo "<label><img class='img-responsive img-thumbnail' src='".$image_thumb."' alt='".$locale['news_0216']."' /><br />\n";
	echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['delete']."</label>\n";
	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-6'>\n";
	$alignOptions = array(
		'pull-left' => $locale['left'],
		'news-img-center' => $locale['center'],
		'pull-right' => $locale['right']
	);
	echo form_select('news_ialign', $locale['news_0218'], $data['news_ialign'], array(
		"options" => $alignOptions,
		"inline" => FALSE
	));
	echo "</div>\n</div>\n";
	echo "<input type='hidden' name='news_image' value='".$data['news_image']."' />\n";
	echo "<input type='hidden' name='news_image_t1' value='".$data['news_image_t1']."' />\n";
	echo "<input type='hidden' name='news_image_t2' value='".$data['news_image_t2']."' />\n";
} else {
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
		'type' => 'image'
	);
	echo form_fileinput("news_image", $locale['news_0216'], "", $file_input_options);
	echo "<div class='small m-b-10'>".sprintf($locale['news_0217'], parsebytesize($news_settings['news_photo_max_b']))."</div>\n";
	$alignOptions = array(
		'pull-left' => $locale['left'],
		'news-img-center' => $locale['center'],
		'pull-right' => $locale['right']
	);
	echo form_select('news_ialign', $locale['news_0218'], $data['news_ialign'], array("options" => $alignOptions));
}
closeside();
openside('');
echo "<label><input type='checkbox' name='news_draft' value='yes'".($data['news_draft'] ? "checked='checked'" : "")." /> ".$locale['news_0210']."</label><br />\n";
echo "<label><input type='checkbox' name='news_sticky' value='yes'".($data['news_sticky'] ? "checked='checked'" : "")."  /> ".$locale['news_0211']."</label><br />\n";
echo form_hidden('news_datestamp', '', $data['news_datestamp']);
if (fusion_get_settings("tinymce_enabled") != 1) {
	echo "<label><input type='checkbox' name='line_breaks' value='yes'".($data['news_breaks'] ? "checked='checked'" : "")." /> ".$locale['news_0212']."</label><br />\n";
}
closeside();
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
openside("");
if (!fusion_get_settings("comments_enabled") || !fusion_get_settings("ratings_enabled")) {
	$sys = "";
	if (!fusion_get_settings("comments_enabled") && !fusion_get_settings("ratings_enabled")) {
		$sys = $locale['comments_ratings'];
	} elseif (!fusion_get_settings("comments_enabled")) {
		$sys = $locale['comments'];
	} else {
		$sys = $locale['ratings'];
	}
	echo "<div class='alert alert-warning'>".sprintf($locale['news_0253'], $sys)."</div>\n";
}
echo "<label><input type='checkbox' name='news_allow_comments' value='yes' onclick='SetRatings();'".($data['news_allow_comments'] ? "checked='checked'" : "")." /> ".$locale['news_0213']."</label><br/>";
echo "<label><input type='checkbox' name='news_allow_ratings' value='yes'".($data['news_allow_ratings'] ? "checked='checked'" : "")." /> ".$locale['news_0214']."</label>";
closeside();
echo "</div>\n</div>\n";
echo form_button('preview', $locale['news_0240'], $locale['news_0240'], array('class' => 'btn-default m-r-10'));
echo form_button('save', $locale['news_0241'], $locale['news_0241'], array(
	'class' => 'btn-success',
	'icon' => 'fa fa-square-check-o'
));
echo closeform();
echo "</div>\n";
