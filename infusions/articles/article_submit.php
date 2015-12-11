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
$article_settings = get_settings("article");
include INFUSIONS."articles/locale/".LOCALESET."articles_admin.php";
opentable("<i class='fa fa-commenting-o fa-lg m-r-10'></i>".$locale['articles_0060']);
if (iMEMBER && $article_settings['article_allow_submission']
	&& dbcount("(article_cat_id)", DB_ARTICLE_CATS, multilang_table("AR") ? "article_cat_language='".LANGUAGE."'" : "")) {
	//@todo: patch in TinyMCE
	$criteriaArray = array(
		"article_subject" => "",
		"article_cat" => 0,
		"article_snippet" => "",
		"article_article" => "",
		"article_language" => LANGUAGE,
		"article_keywords" => "",
	);
	if (isset($_POST['submit_article'])) {
		$submit_info['article_snippet'] = nl2br(parseubb(stripinput($_POST['article_snippet'])));
		$submit_info['article_article'] = nl2br(parseubb(stripinput($_POST['article_article'])));
		$criteriaArray = array(
			"article_subject" => form_sanitizer($_POST['article_subject'], "", "article_subject"),
			"article_cat" => form_sanitizer($_POST['article_cat'], "", "article_cat"),
			"article_snippet" => form_sanitizer($submit_info['article_snippet'], "", "article_snippet"),
			"article_article" => form_sanitizer($submit_info['article_article'], "", "article_article"),
			"article_language" => form_sanitizer($_POST['article_language'], "", "article_language"),
			"article_keywords" => form_sanitizer($_POST['article_keywords'], "", "article_keywords"),
		);
		if (defender::safe()) {
			$inputArray = array(
				"submit_type" => "a",
				"submit_user" => $userdata['user_id'],
				"submit_datestamp" => time(),
				"submit_criteria" => addslashes(serialize($criteriaArray))
			);
			dbquery_insert(DB_SUBMISSIONS, $inputArray, "save");
			addNotice("success", $locale['articles_0061']);
			redirect(clean_request("submitted=a", array("stype"), TRUE));
		}
	}
	if (isset($_GET['submitted']) && $_GET['submitted'] == "a") {
		add_to_title($locale['global_200'].$locale['articles_0040']);
		echo "<div class='well text-center'><p><strong>".$locale['articles_0061']."</strong></p>";
		echo "<p><a href='submit.php?stype=a'>".$locale['articles_0062']."</a></p>";
        echo "<p><a href='index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"),
                                                   $locale['articles_0064'])."</a></p>\n";
		echo "</div>\n";
	} else {
		// Preview
		if (isset($_POST['preview_article'])) {
			$article_snippet = "";
			if ($_POST['article_snippet']) {
				$article_snippet = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, parseubb(stripslashes($_POST['article_snippet'])));
				$article_snippet = parse_textarea($article_snippet);
			}
			$article_article = "";
			if ($_POST['article_article']) {
				$article_article = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, parseubb(stripslashes($_POST['article_article'])));
				$article_article = parse_textarea($article_article);
			}

			$criteriaArray = array(
				"article_subject" => form_sanitizer($_POST['article_subject'], "", "article_subject"),
				"article_cat" => form_sanitizer($_POST['article_cat'], 0, "article_cat"),
				"article_snippet" => form_sanitizer($article_snippet, "", "article_snippet"),
				"article_article" => form_sanitizer($article_article, "", "article_article"),
				"article_keywords" => form_sanitizer($_POST['article_keywords'], "", "article_keywords"),
				"article_language" => form_sanitizer($_POST['article_language'], "", "article_language"),
			);
			$criteriaArray['article_snippet'] = parse_textarea($article_snippet);
			$criteriaArray['article_article'] = parse_textarea($article_article);

			opentable($criteriaArray['article_subject']);
			echo "<p class='text-bigger'>".$criteriaArray['article_snippet']."</p>";
			echo $criteriaArray['article_article'];
			closetable();
		}
		add_to_title($locale['global_200'].$locale['articles_0060']);
		echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
        echo "<div class='alert alert-info m-b-20 submission-guidelines'>".str_replace("[SITENAME]", fusion_get_settings("sitename"),
                                                                      $locale['articles_0063'])."</div>\n";
        echo openform('submit_form', 'post', BASEDIR."submit.php?stype=a");
		echo form_text('article_subject', $locale['articles_0304'], $criteriaArray['article_subject'], array(
											"required" => TRUE,
											"inline" => TRUE
										));
		if (multilang_table("AR")) {
			echo form_select('article_language', $locale['global_ML100'], $criteriaArray['article_language'], array(
												   "options" => fusion_get_enabled_languages(),
												   "placeholder" => $locale['choose'],
												   "width" => "250px",
												   "inline" => TRUE,
											   ));
		} else {
			echo form_hidden('article_language', '', $criteriaArray['article_language']);
		}
		echo form_select('article_keywords', $locale['articles_0204'], $criteriaArray['article_keywords'], array(
											   "max_length" => 320,
											   "inline" => TRUE,
											   "placeholder" => $locale['articles_0204a'],
											   "width" => "100%",
											   "error_text" => $locale['articles_0204a'],
											   "tags" => TRUE,
											   "multiple" => TRUE
										   ));
		echo form_select_tree("article_cat", $locale['articles_0201'], $criteriaArray['article_cat'], array(
											   "width" => "250px",
											   "inline" => TRUE,
											   "no_root" => TRUE,
											   "query" => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")
										   ), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");
		echo form_textarea('article_snippet', $locale['articles_0202'], $criteriaArray['article_snippet'], array(
												"required" => TRUE,
												"html" => TRUE,
												"form_name" => "submit_form",
												"autosize" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE
											));
		echo form_textarea('article_article', $locale['articles_0203'], $criteriaArray['article_article'], array(
												"required" => $article_settings['article_extended_required'] ? TRUE : FALSE,
												"html" => TRUE,
												"form_name" => "submit_form",
												"autosize" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE
											));
		echo fusion_get_settings("site_seo") ? "" : form_button('preview_article', $locale['articles_0240'], $locale['articles_0240'], array('class' => 'btn-primary m-r-10'));
		echo form_button('submit_article', $locale['articles_0060'], $locale['articles_0060'], array('class' => 'btn-success'));
		echo closeform();
		echo "</div>\n</div>\n";
	}
} else {
	echo "<div class='well text-center'>".$locale['articles_0043']."</div>\n";
}
closetable();