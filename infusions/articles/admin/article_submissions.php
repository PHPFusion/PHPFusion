<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/article_submissions.php
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
if (fusion_get_settings("tinymce_enabled")) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
}

if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$data = array(
				'article_id' => 0,
				'article_subject' => form_sanitizer($_POST['article_subject'], '', 'article_subject'),
				'article_cat' => form_sanitizer($_POST['article_cat'], 0, 'article_cat'),
				'article_name' => $data['user_id'],
				'article_snippet' => addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['article_snippet'])),
				'article_article' => addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['article_article'])),
				'article_keywords' => form_sanitizer($_POST['article_keywords'], '', 'article_keywords'),
				'article_datestamp' => form_sanitizer($_POST['article_datestamp'], time(), 'article_datestamp'),
				'article_visibility' => form_sanitizer($_POST['article_visibility'], 0, 'article_visibility'),
				'article_draft' => isset($_POST['article_draft']) ? "1" : "0",
				'article_allow_comments' => 0,
				'article_allow_ratings' => 0,
				'article_language' => form_sanitizer($_POST['article_language'], '', 'article_language')
			);
			if (fusion_get_settings('tinymce_enabled') != 1) {
				$data['article_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
			} else {
				$data['article_breaks'] = "n";
			}
			if (defender::safe()) {
				dbquery_insert(DB_ARTICLES, $data, "save");
				$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
				if ($data['article_draft']) {
					addNotice("success", $locale['articles_0051']);
				} else {
					addNotice("success", $locale['articles_0050']);
				}
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
			WHERE submit_type='a' and submit_id='".intval($_GET['submit_id'])."'
		");
		if (dbrows($result) > 0) {
			$data = dbarray($result);
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($data['submit_id'])."'");
			addNotice("success", $locale['articles_0049']);
		}
		redirect(clean_request("", array("submit_id"), FALSE));
	} else {
		$result = dbquery("SELECT
			ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_type='a' order by submit_datestamp desc");
		if (dbrows($result) > 0) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$callback_data = array(
				"article_subject" => $submit_criteria['article_subject'],
				"article_cat" => $submit_criteria['article_cat'],
				"article_keywords" => $submit_criteria['article_keywords'],
				"article_visibility" => 0,
				"article_language" => $submit_criteria['article_language'],
				"article_snippet" => html_entity_decode(stripslashes($submit_criteria['article_snippet'])),
				"article_article" => html_entity_decode(stripslashes($submit_criteria['article_article'])),
				"article_breaks" => !fusion_get_settings("tinyce_enabled") ? TRUE : FALSE,
				"article_draft" => FALSE,
				"article_datestamp" => $data['submit_datestamp'],
			);
			
			add_to_title($locale['global_200'].$locale['503'].$locale['global_201'].$callback_data['article_subject']."?");
			if (isset($_POST['preview'])) {
				$article_snippet = "";
				if ($_POST['article_snippet']) {
					$article_snippet = html_entity_decode(stripslashes($_POST['article_snippet']));
					$article_snippet = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, $article_snippet);
				}
				$article_article = "";
				if ($_POST['article_article']) {
					$article_article = html_entity_decode(stripslashes($_POST['article_article']));
					$article_article = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, $article_article);
				}
				$callback_data = array(
					"article_subject" => form_sanitizer($_POST['article_subject'], '', 'article_subject'),
					"article_cat" => isnum($_POST['article_cat']) ? $_POST['article_cat'] : 0,
					"article_language" => form_sanitizer($_POST['article_language'], '', 'article_language'),
					"article_snippet" => form_sanitizer($article_snippet, "", "article_snippet"),
					"article_article" => form_sanitizer($article_article, "", "article_article"),
					"article_keywords" => form_sanitizer($_POST['article_keywords'], '', 'article_keywords'),
					"article_visibility" => isnum($_POST['article_visibility']) ? $_POST['article_visibility'] : "0",
					"article_draft" => isset($_POST['article_draft']) ? TRUE : FALSE,
					"article_datestamp" => $callback_data['article_datestamp'], // pull from db.
				);

				$callback_data['article_breaks'] = "";
				$callback_data['article_snippet'] = html_entity_decode(stripslashes($callback_data['article_snippet']));
				$callback_data['article_article'] = html_entity_decode(stripslashes($callback_data['article_article']));
				if (isset($_POST['article_breaks'])) {
					$callback_data['article_breaks'] = TRUE;
					$callback_data['article_snippet'] = nl2br($callback_data['article_snippet']);
					if ($callback_data['article_article']) {
						$callback_data['article_article'] = nl2br($callback_data['article_article']);
					}
				}

				if (defender::safe()) {
					echo openmodal('article_preview', $locale['articles_0240']);
					echo "<h3>".$callback_data['article_snippet']."</h3>\n";
					echo $callback_data['article_snippet'];
					echo "<hr/>\n";
					if (isset($callback_data['article_article'])) {
						echo $callback_data['article_article'];
					}
					echo closemodal();
				}
			}
			echo openform("publish_article", "post", FUSION_REQUEST);
			echo "<div class='well clearfix'>\n";
			echo "<div class='pull-left'>\n";
			echo display_avatar($data, "30px", "", "", "");
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo $locale['articles_0052'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
			echo $locale['articles_0053'].timer($data['submit_datestamp'])." - ".showdate("shortdate", $data['submit_datestamp']);
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
			echo form_text("article_subject", $locale['articles_0200'], $callback_data['article_subject'], array(
				"required" => TRUE,
				"inline" => FALSE
			));
			echo form_select('article_keywords', $locale['articles_0204'], $callback_data['article_keywords'], array(
				"max_length" => 320,
				"placeholder" => $locale['articles_0204a'],
				"width" => "100%",
				"error_text" => $locale['articles_0257'],
				"tags" => TRUE,
				"multiple" => TRUE
			));

			$snippetSettings = array(
				"required" => TRUE,
				"preview" => TRUE,
				"html" => TRUE,
				"autosize" => TRUE,
				"form_name" => "inputform"
			);
			if (fusion_get_settings("tinymce_enabled")) {
				$snippetSettings = array("required" => TRUE);
			}
			echo form_textarea('article_snippet', $locale['articles_0202'], $callback_data['article_snippet'], $snippetSettings);
			echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
			openside("");
			echo form_select_tree("article_cat", $locale['articles_0201'], $callback_data['article_cat'], array(
												   "width" => "100%",
												   "inline" => TRUE,
												   "no_root" => TRUE,
												   "query" => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")
											   ), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");
			echo form_select('article_visibility', $locale['articles_0211'], $callback_data['article_visibility'], array(
													 'options' => fusion_get_groups(),
													 'placeholder' => $locale['choose'],
													 'width' => '100%',
													 "inline" => TRUE,
												 ));
			if (multilang_table("AR")) {
				echo form_select('article_language', $locale['global_ML100'], $callback_data['article_language'], array(
													   'options' => fusion_get_enabled_languages(),
													   'width' => '100%',
													   "inline" => TRUE,
												   ));
			} else {
				echo form_hidden('article_language', '', $callback_data['article_language']);
			}
			echo form_hidden('article_datestamp', '', $callback_data['article_datestamp']);
			echo form_button('preview', $locale['articles_0240'], $locale['articles_0240'], array('class' => 'btn-default m-r-10'));
			echo form_button('publish', $locale['articles_0242'], $locale['articles_0242'], array('class' => 'btn-primary m-r-10'));
			closeside();
			openside("");
			echo "<label><input type='checkbox' name='article_draft' value='1'".($callback_data['article_draft'] ? "checked='checked'" : "")." /> ".$locale['articles_0205']."</label><br />\n";
			if (fusion_get_settings("tinymce_enabled") != 1) {
				echo "<label><input type='checkbox' name='article_breaks' value='1'".($callback_data['article_breaks'] ? "checked='checked'" : "")." /> ".$locale['articles_0206']."</label><br />\n";
			}
			closeside();
			echo "</div></div>\n";
			$extendedSettings = array();
			if (!fusion_get_settings("tinymce_enabled")) {
				$extendedSettings = array(
					"preview" => TRUE,
					"html" => TRUE,
					"autosize" => TRUE,
					"placeholder" => $locale['articles_0426b'],
					"form_name" => "inputform"
				);
			}
			echo form_textarea('article_article', $locale['articles_0203'], $callback_data['article_article'], $extendedSettings);
			echo form_button('preview', $locale['articles_0240'], $locale['articles_0240'], array('class' => 'btn-default m-r-10'));
			echo form_button('publish', $locale['articles_0242'], $locale['articles_0242'], array('class' => 'btn-primary m-r-10'));
			echo form_button('delete', $locale['articles_0243'], $locale['articles_0243'], array('class' => 'btn-warning m-r-10'));
			echo closeform();
		}
	}
} else {
	$result = dbquery("SELECT
			ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_type='a' order by submit_datestamp desc
			");
	$rows = dbrows($result);
	if ($rows > 0) {
		echo "<div class='well'>".sprintf($locale['articles_0044'], format_word($rows, $locale['fmt_submission']))."</div>\n";
		echo "<table class='table table-striped'>\n";
		echo "<tr>\n";
		echo "<th>".$locale['articles_0041']."</th>\n<th>".$locale['articles_0046']."</th><th>".$locale['articles_0047']."</th><th>".$locale['articles_0048']."</th>";
		echo "</tr>\n";
		echo "<tbody>\n";
		while ($data = dbarray($result)) {
			$submit_criteria = unserialize($data['submit_criteria']);
			echo "<tr>\n";
			echo "<td><a href='".clean_request("submit_id=".$data['submit_id'], array(
					"section",
					"aid"
				), TRUE)."'>".$submit_criteria['article_subject']."</a></td>\n";
			echo "<td>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
			echo "<td>".timer($data['submit_datestamp'])."</td>\n";
			echo "<td>".$data['submit_id']."</td>\n";
			echo "</tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} else {
		echo "<div class='well text-center m-t-20'>".$locale['articles_0042']."</div>\n";
	}
}
