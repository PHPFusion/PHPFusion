<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_submissions.php
| Author: PHP-Fusion Inc
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

opentable($locale['news_0131']);
if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {

	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$data = array(
				'news_id' => 0,
				'news_subject' => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
				'news_cat' => form_sanitizer($_POST['news_cat'], 0, 'news_cat'),
				'news_name' =>  $data['user_id'],
				'news_news' =>	addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['news_news'])),
				'news_extended' =>	addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['news_extended'])),
				'news_keywords'	=>	form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
				'news_datestamp' => form_sanitizer($_POST['news_datestamp'], time(), 'news_datestamp'),
				'news_start' => form_sanitizer($_POST['news_start'], 0, 'news_start'),
				'news_end' => form_sanitizer($_POST['news_end'], 0, 'news_end'),
				'news_visibility' => form_sanitizer($_POST['news_visibility'], 0, 'news_visibility'),
				'news_draft' => isset($_POST['news_draft']) ? "1" : "0",
				'news_sticky' => isset($_POST['news_sticky']) ? "1" : "0",
				'news_allow_comments' => 0,
				'news_allow_ratings' => 0,
				'news_language' => form_sanitizer($_POST['news_language'], '', 'news_language')
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

			// delete image if checkbox ticked
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
				dbquery_insert(DB_NEWS, $data, "save");
				$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
				if ($data['news_draft']) {
					addNotice("success", $locale['news_0147']);
				} else {
					addNotice("success", $locale['news_0146']);
				}
				redirect(clean_request("", array("submit_id"), false));
			}
		} else {
			redirect(clean_request("", array("submit_id"), false));
		}
	}
	else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("
			SELECT
			ts.submit_datestamp, ts.submit_criteria,
			FROM ".DB_SUBMISSIONS." ts
			WHERE submit_type='n' where submit_id='".intval($_GET['submit_id'])."'
		");
		if (dbrows($result)>0) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			if (!empty($submit_criteria['news_image']) && file_exists(IMAGES_N.$submit_criteria['news_image'])) {
				unlink(IMAGES_N.$submit_criteria['news_image']);
			}
			if (!empty($submit_criteria['news_image_t1']) && file_exists(IMAGES_N_T.$submit_criteria['news_image_t1'])) {
				unlink(IMAGES_N_T.$submit_criteria['news_image_t1']);
			}
			if (!empty($submit_criteria['news_image_t2']) && file_exists(IMAGES_N_T.$submit_criteria['news_image_t2'])) {
				unlink(IMAGES_N_T.$submit_criteria['news_image_t2']);
			}
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."'");
			addNotice("success",  $locale['news_0145']);
		}
		redirect(clean_request("", array("submit_id"), false));
	}
	else {

		$result = dbquery("SELECT
			ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_type='n' order by submit_datestamp desc");

		if (dbrows($result)>0) {

			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$callback_data = array(
				"news_start" => $data['submit_datestamp'],
				"news_datestamp" => $data['submit_datestamp'],
				"news_keywords" => $submit_criteria['news_keywords'],
				"news_visibility" => 0,
				"news_image" => $submit_criteria['news_image'],
				"news_image_t1" => $submit_criteria['news_image_t1'],
				"news_image_t2" => $submit_criteria['news_image_t2'],
				"news_ialign" => $submit_criteria['news_ialign'],
				"news_end" => "",
				"news_draft" => 0,
				"news_sticky" => 0,
				"news_language" => $submit_criteria['news_language'],
				"news_subject" => $submit_criteria['news_subject'],
				"news_cat" => $submit_criteria['news_cat'],
				"news_news" => phpentities(stripslashes($submit_criteria['news_snippet'])),
				"news_extended" => phpentities(stripslashes($submit_criteria['news_body'])),
				"news_breaks" => fusion_get_settings("tinyce_enabled") ? true : false,
			);
			add_to_title($locale['global_200'].$locale['503'].$locale['global_201'].$callback_data['news_subject']."?");

			if (isset($_POST['preview'])) {
				$news_news = "";
				if ($_POST['news_news']) {
					$news_news = phpentities(stripslash($_POST['news_news']));
					$news_news = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslash($_POST['news_news']));
				}

				$news_extended = "";
				if ($_POST['news_extended']) {
					$news_extended = phpentities(stripslash($_POST['news_extended']));
					$news_extended = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslash($_POST['news_extended']));
				}
				$callback_data = array(
					"news_subject" => 	form_sanitizer($_POST['news_subject'], '', 'news_subject'),
					"news_cat" =>	isnum($_POST['news_cat']) ? $_POST['news_cat'] : 0,
					"news_language"	=>	 form_sanitizer($_POST['news_language'], '', 'news_language'),
					"news_news"		=> 	form_sanitizer($news_news, "", "news_news"),
					"news_extended"	=>	form_sanitizer($news_extended, "", "news_extended"),
					"news_keywords" =>	 form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
					"news_start"	=>	(isset($_POST['news_start']) && $_POST['news_start']) ? $_POST['news_start'] : '',
					"news_end"	=>	(isset($_POST['news_end']) && $_POST['news_end']) ? $_POST['news_end'] : '',
					"news_visibility"	=>	 isnum($_POST['news_visibility']) ? $_POST['news_visibility'] : "0",
					"news_draft"	=>	isset($_POST['news_draft']) ? true : false,
					"news_sticky"	=> isset($_POST['news_sticky']) ? true : false,
					"news_datestamp" => $callback_data['news_datestamp'], // pull from db.
					"news_ialign" => isset($_POST['news_ialign']) ? $_POST['news_ialign'] : '',
					"news_image" => isset($_POST['news_image']) ? $_POST['news_image'] : '',
					"news_image_t1" => isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "",
					"news_image_t2" => isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "",
				);
				$callback_data['news_breaks'] = "";
				if (isset($_POST['news_breaks'])) {
					$callback_data['news_breaks'] = true;
					$callback_data['news_news'] = nl2br($callback_data['news_news']);
					if ($callback_data['news_extended']) {
						$callback_data['news_extended'] = nl2br($callback_data['news_extended']);
					}
				}
				if (defender::safe()) {
					echo openmodal('news_preview', $locale['news_0141']);
					echo "<h3>".$callback_data['news_subject']."</h3>\n";
					echo $callback_data['news_news'];
					echo "<hr/>\n";
					if (isset($callback_data['news_extended'])) {
						echo $callback_data['news_extended'];
					}
					echo closemodal();
				}
			}

			echo openform("publish_news", "post", FUSION_REQUEST);
			echo "<div class='well clearfix'>\n";
			echo "<div class='pull-left'>\n";
			echo display_avatar($data, "30px", "", "", "");
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo $locale['news_0132'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
			echo "Posted ".timer($data['submit_datestamp'])." - ".showdate("shortdate", $data['submit_datestamp']);
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
			echo form_text("news_subject", $locale['news_0200'], $callback_data['news_subject'], array("required"=>true, "inline"=>false));
			echo form_select('news_keywords', $locale['news_0205'], $callback_data['news_keywords'],
							 array(
								 "max_length" => 320,
								 "placeholder"=> $locale['news_0205a'],
								 "width" => "100%",
								 "error_text" => $locale['news_0255'],
								 "tags" => true,
								 "multiple" => true
							 )
			);
			echo "<div class='row m-0'>\n";
			echo "<div class='pull-left m-r-10 display-inline-block'>\n";
			echo form_datepicker('news_start', $locale['news_0206'], $callback_data['news_start'], array('placeholder' => $locale['news_0208']));
			echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
			echo form_datepicker('news_end', $locale['news_0207'], $callback_data['news_end'], array('placeholder' => $locale['news_0208']));
			echo "</div>\n</div>\n";

			openside('');
			if ($callback_data['news_image'] != "" && $callback_data['news_image_t1'] != "") {
				echo "<div class='row'>\n";
				echo "<div class='col-xs-12 col-sm-6'>\n";
				echo "<label><img class='img-responsive img-thumbnail' src='".IMAGES_N_T.$callback_data['news_image_t1']."' alt='".$locale['news_0216']."' /><br />\n";
				echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['delete']."</label>\n";
				echo "</div>\n";
				echo "<div class='col-xs-12 col-sm-6'>\n";
				$alignOptions = array(
					'pull-left' => $locale['left'],
					'news-img-center' => $locale['center'],
					'pull-right' => $locale['right']);
				echo form_select('news_ialign', $locale['news_0218'], $callback_data['news_ialign'], array("options" => $alignOptions, "inline"=>false));
				echo "</div>\n</div>\n";
				echo "<input type='hidden' name='news_image' value='".$callback_data['news_image']."' />\n";
				echo "<input type='hidden' name='news_image_t1' value='".$callback_data['news_image_t1']."' />\n";
				echo "<input type='hidden' name='news_image_t2' value='".$callback_data['news_image_t2']."' />\n";
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
				$alignOptions = array('pull-left' => $locale['left'],
					'news-img-center' => $locale['center'],
					'pull-right' => $locale['right']);
				echo form_select('news_ialign', $locale['news_0218'], $callback_data['news_ialign'], array("options" => $alignOptions));
			}
			closeside();
			$snippetSettings = array(
				"required"=>true,
				"preview"=>true,
				"html"=>true,
				"autosize"=>true,
				"placeholder" => $locale['news_0203a'],
				"form_name"=>"inputform"
			);
			if (fusion_get_settings("tinymce_enabled")) {
				$snippetSettings = array("required"=>true);
			}
			echo form_textarea('news_news', $locale['news_0203'], $callback_data['news_news'], $snippetSettings);



			echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
			openside("");
			echo form_select_tree("news_cat", $locale['news_0201'], $callback_data['news_cat'],
								  array(
									  "width" => "100%",
									  "inline"=>true,
									  "parent_value" => $locale['news_0202'],
									  "query" => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")
								  ),
								  DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent"
			);
			echo form_select('news_visibility', $locale['news_0209'], $callback_data['news_visibility'], array('options' => fusion_get_groups(),
				'placeholder' => $locale['choose'],
				'width' => '100%',
				"inline"=>true,
			));
			if (multilang_table("NS")) {
				echo form_select('news_language', $locale['global_ML100'], $callback_data['news_language'], array('options' => fusion_get_enabled_languages(),
					'placeholder' => $locale['choose'],
					'width' => '100%',
					"inline" => true,
				));
			} else {
				echo form_hidden('news_language', '', $callback_data['news_language']);
			}
			echo form_hidden('news_datestamp', '', $callback_data['news_datestamp']);
			echo form_button('preview', $locale['news_0240'], $locale['news_0240'], array('class' => 'btn-default m-r-10'));
			echo form_button('publish', $locale['news_0134'], $locale['news_0134'], array('class' => 'btn-primary m-r-10'));
			closeside();

			openside("");
			echo "<label><input type='checkbox' name='news_draft' value='1'".($callback_data['news_draft'] ? "checked='checked'" : "")." /> ".$locale['news_0210']."</label><br />\n";
			echo "<label><input type='checkbox' name='news_sticky' value='1'".($callback_data['news_sticky'] ? "checked='checked'" : "")."  /> ".$locale['news_0211']."</label><br />\n";
			if (fusion_get_settings("tinymce_enabled") != 1) {
				echo "<label><input type='checkbox' name='news_breaks' value='1'".($callback_data['news_breaks'] ? "checked='checked'" : "")." /> ".$locale['news_0212']."</label><br />\n";
			}
			closeside();
			echo "</div></div>\n";

			$extendedSettings = array();
			if (!fusion_get_settings("tinymce_enabled")) {
				$extendedSettings = array(
					"preview"=>true,
					"html"=>true,
					"autosize"=>true,
					"placeholder" => $locale['news_0203b'],
					"form_name"=>"inputform"
				);
			}
			echo form_textarea('news_extended', $locale['news_0204'], $callback_data['news_extended'], $extendedSettings);
			echo form_button('preview', $locale['news_0240'], $locale['news_0240'], array('class' => 'btn-default m-r-10'));
			echo form_button('publish', $locale['news_0134'], $locale['news_0134'], array('class' => 'btn-primary m-r-10'));
			echo form_button('delete', $locale['news_0135'], $locale['news_0135'], array('class' => 'btn-warning m-r-10'));
			echo closeform();
		}
	}
} else {
	$result = dbquery("SELECT
			ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_type='n' order by submit_datestamp desc
			");
	$rows = dbrows($result);
	if ($rows > 0) {
		echo "<div class='well'>".sprintf($locale['news_0137'], format_word($rows, $locale['fmt_submission']))."</div>\n";
		echo "<table class='table table-striped'>\n";
		echo "<tr>\n";
		echo "<th>".$locale['news_0136']."</th>\n<th>".$locale['news_0142']."</th><th>".$locale['news_0143']."</th><th>".$locale['news_0144']."</th>";
		echo "</tr>\n";
		echo "<tbody>\n";
		while ($data = dbarray($result)) {
			$submit_criteria = unserialize($data['submit_criteria']);
			echo "<tr>\n";
			echo "<td><a href='".clean_request("submit_id=".$data['submit_id'], array("section", "aid"), true)."'>".$submit_criteria['news_subject']."</a></td>\n";
			echo "<td>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
			echo "<td>".timer($data['submit_datestamp'])."</td>\n";
			echo "<td>".$data['submit_id']."</td>\n";
			echo "</tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} else {
		echo "<div class='well text-center m-t-20'>".$locale['news_0130']."</div>\n";
	}
}
closetable();