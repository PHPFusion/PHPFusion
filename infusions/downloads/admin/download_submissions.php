<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/download_submissions.php
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
			$inputArray = dbarray($result);
			$inputArray = array(
				"download_id" => 0,
				"download_title" => form_sanitizer($_POST['download_title'], '', 'download_title'),
				"download_description" => form_sanitizer($_POST['download_description'], '', 'download_description'),
				"download_description_short" => form_sanitizer($_POST['download_description_short'], '', 'download_description_short'),
				"download_cat" => form_sanitizer($_POST['download_cat'], 0, 'download_cat'),
				"download_homepage" => form_sanitizer($_POST['download_homepage'], '', 'download_homepage'),
				"download_license" => form_sanitizer($_POST['download_license'], '', 'download_license'),
				"download_copyright" => form_sanitizer($_POST['download_copyright'], '', 'download_copyright'),
				"download_os" => form_sanitizer($_POST['download_os'], '', 'download_os'),
				"download_version" => form_sanitizer($_POST['download_version'], '', 'download_version'),
				"download_file" => form_sanitizer($_POST['download_file'], '', 'download_file'),
				"download_url" => form_sanitizer($_POST['download_url'], '', 'download_url'),
				"download_filesize" => form_sanitizer($_POST['download_filesize'], '', 'download_filesize'),
				"download_image" => form_sanitizer($_POST['download_image'], '', 'download_image'),
				"download_image_thumb" => form_sanitizer($_POST['download_image_thumb'], '', 'download_image_thumb'),
				"download_allow_comments" => isset($_POST['download_allow_comments']) ? true : false,
				"download_allow_ratings" => isset($_POST['download_allow_ratings']) ? true : false,
				"download_visibility" => form_sanitizer($_POST['download_visibility'], '', 'download_visibility'),
				"download_keywords" => form_sanitizer($_POST['download_keywords'], '', 'download_keywords'),
				"download_datestamp" => $inputArray['submit_datestamp'],
			);
			if (defender::safe()) {
				//dbquery_insert(DB_ARTICLES, $inputArray, "save");
				// move file over.
				//$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
				addNotice("success", $locale['articles_0051']);
				redirect(clean_request("", array("submit_id"), FALSE));
			}
		} else {
			redirect(clean_request("", array("submit_id"), FALSE));
		}
	}
	else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("
			SELECT ts.submit_id, ts.submit_datestamp, ts.submit_criteria
			FROM ".DB_SUBMISSIONS." ts
			WHERE submit_type='d' and submit_id='".intval($_GET['submit_id'])."'
		");
		if (dbrows($result) > 0) {
			$callback_data = dbarray($result);
			// delete all the relevant files
			$delCriteria = unserialize($callback_data['submit_criteria']);
			if (!empty($delCriteria['download_image']) && file_exists(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_image'])) {
				unlink(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_image']);
			}
			if (!empty($delCriteria['download_image_thumb']) && file_exists(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_image_thumb'])) {
				unlink(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_image_thumb']);
			}
			if (!empty($delCriteria['download_file']) && file_exists(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_file'])) {
				unlink(INFUSIONS."downloads/submisisons/".$delCriteria['download_file']);
			}
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($callback_data['submit_id'])."'");
			addNotice("success", $locale['download_0062']);
		}
		redirect(clean_request("", array("submit_id"), FALSE));
	} else {

		$result = dbquery("SELECT ts.submit_id,
			ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_type='d' order by submit_datestamp desc");
		if (dbrows($result) > 0) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$callback_data = array(
				"download_title" => $submit_criteria['download_title'],
				"download_description" => $submit_criteria['download_description'],
				"download_description_short" => $submit_criteria['download_description_short'],
				"download_cat" => $submit_criteria['download_cat'],
				"download_homepage" => $submit_criteria['download_homepage'],
				"download_license" => $submit_criteria['download_license'],
				"download_copyright" => $submit_criteria['download_copyright'],
				"download_os" => $submit_criteria['download_os'],
				"download_version" => $submit_criteria['download_version'],
				"download_file" => $submit_criteria['download_file'],
				"download_url" => $submit_criteria['download_url'],
				"download_filesize" => $submit_criteria['download_filesize'],
				"download_image" => $submit_criteria['download_image'],
				"download_image_thumb" => $submit_criteria['download_image_thumb'],
				// default to none
				"download_id" => 0,
				"download_allow_comments" => true,
				"download_allow_ratings" => true,
				"download_visibility" => iMEMBER,
				"download_keywords" => "",
				"download_datestamp" => $data['submit_datestamp'],
			);

			add_to_title($locale['global_200'].$locale['503'].$locale['global_201'].$callback_data['download_title']."?");
			echo openform("publish_download", "post", FUSION_REQUEST);
			echo "<div class='well clearfix'>\n";
			echo "<div class='pull-left'>\n";
			echo display_avatar($callback_data, "30px", "", "", "");
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo $locale['download_0056'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
			echo $locale['download_0057'].timer($data['submit_datestamp'])." - ".showdate("shortdate", $data['submit_datestamp']);
			echo "</div>\n";
			echo "</div>\n";

			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-8'>\n";
			openside('');
			echo form_hidden('submit_id', '', $data['submit_id']);
			echo form_hidden('download_datestamp', '', $callback_data['download_datestamp']);
			echo form_text('download_title', $locale['download_0200'], $callback_data['download_title'], array(
				'required' => TRUE,
				"inline" => TRUE,
				'error_text' => $locale['download_0110']
			));
			echo form_select('download_keywords', $locale['download_0203'], $callback_data['download_keywords'], array(
				"placeholder" => $locale['download_0203a'],
				'max_length' => 320,
				"inline" => TRUE,
				'width' => '100%',
				'tags' => 1,
				'multiple' => 1
			));
			echo form_textarea('download_description_short', $locale['download_0202'], $callback_data['download_description_short'], array(
				'required' => TRUE,
				"inline" => TRUE,
				'error_text' => $locale['download_0112'],
				'maxlength' => '255',
				'autosize' => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
			));
			closeside();
			echo "<div class='well'>\n";
			echo $locale['download_0204'];
			echo "</div>\n";
			/* Download file input */
			$tab_title['title'][] = "1 -".$locale['download_0214'];
			$tab_title['id'][] = 'dlf';
			$tab_title['icon'][] = 'fa fa-file-zip-o fa-fw';
			$tab_title['title'][] = "2 -".$locale['download_0215'];
			$tab_title['id'][] = 'dll';
			$tab_title['icon'][] = 'fa fa-plug fa-fw';
			$tab_active = tab_active($tab_title, 0);
			echo opentab($tab_title, $tab_active, 'downloadtab');
			echo opentabbody($tab_title['title'][0], 'dlf', $tab_active);
			if (!empty($callback_data['download_file'])) {
				echo "<div class='list-group-item m-t-10'>".$locale['download_0214']." - <a href='".DOWNLOADS."submissions/".$callback_data['download_file']."'>
				".DOWNLOADS."submissions/".$callback_data['download_file']."</a>\n";
				echo form_checkbox('del_upload', $locale['download_0216'], '', array('class' => 'm-b-0'));
				echo "</div>\n";
				echo form_hidden('download_file', '', $callback_data['download_file']);
			} else {
				$file_options = array(
					"class" => "m-t-10",
					"required" => TRUE,
					"width" => "100%",
					"upload_path" => DOWNLOADS."submissions/",
					"max_bytes" => $dl_settings['download_max_b'],
					"valid_ext" => $dl_settings['download_types'],
					"error_text" => $locale['download_0115'],
				);
				echo form_fileinput('download_file', $locale['download_0214'], "", $file_options);
				echo sprintf($locale['download_0218'], parsebytesize($dl_settings['download_max_b']), str_replace(',', ' ', $dl_settings['download_types']))."<br />\n";
				echo form_checkbox('calc_upload', $locale['download_0217'], '');
			}
			echo closetabbody();
			echo opentabbody($tab_title['title'][1], 'dll', $tab_active);
			if (empty($callback_data['download_file'])) {
				echo form_text('download_url', $locale['download_0206'], $callback_data['download_url'], array(
					"required" => TRUE,
					"class" => "m-t-10",
					"inline" => TRUE,
					"placeholder" => "http://",
					"error_text" => $locale['download_0116']
				));
			} else {
				echo form_hidden('download_url', '', $callback_data['download_url']);
			}
			echo closetabbody();
			echo closetab();
			echo "<hr/>\n";

			echo form_textarea('download_description', $locale['download_0202a'], $callback_data['download_description'], array(
				"no_resize" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
				"form_name" => "inputform",
				"html" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
				"autosize" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
				"preview" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
				"placeholder" => $locale['download_0201']
			));

			echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
			openside();
			if (fusion_get_settings('comments_enabled') == "0" || fusion_get_settings('ratings_enabled') == "0") {
				$sys = "";
				if (fusion_get_settings('comments_enabled') == "0" && fusion_get_settings('ratings_enabled') == "0") {
					$sys = $locale['comments_ratings'];
				} elseif (fusion_get_settings('comments_enabled') == "0") {
					$sys = $locale['comments'];
				} else {
					$sys = $locale['ratings'];
				}
				echo "<div class='well'>".sprintf($locale['download_0256'], $sys)."</div>\n";
			}
			echo form_select_tree("download_cat", $locale['download_0207'], $callback_data['download_cat'], array(
				"no_root" => 1,
				"placeholder" => $locale['choose'],
				'width' => '100%',
				"query" => (multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")
			), DB_DOWNLOAD_CATS, "download_cat_name", "download_cat_id", "download_cat_parent");
			echo form_select('download_visibility', $locale['download_0205'], $callback_data['download_visibility'], array(
				'options' => fusion_get_groups(),
				'placeholder' => $locale['choose'],
				'width' => '100%'
			));
			if ($dl_settings['download_screenshot']) {
				if (!empty($callback_data['download_image']) && !empty($callback_data['download_image_thumb'])) {
					echo "<div class='clearfix list-group-item m-b-20'>\n";
					echo "<div class='pull-left m-r-10'>\n";
					echo thumbnail(DOWNLOADS."submissions/images/".$callback_data['download_image_thumb'], '80px');
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<span class='text-dark strong'>".$locale['download_0220']."</span>\n";
					echo form_checkbox('del_image', $locale['download_0216'], '');
					echo form_hidden('download_image', '', $callback_data['download_image']);
					echo form_hidden('download_image_thumb', '', $callback_data['download_image_thumb']);
					echo "</div>\n</div>\n";
				} else {
					require_once INCLUDES."mimetypes_include.php";
					$file_options = array(
						'upload_path' => DOWNLOADS."images/",
						'max_width' => $dl_settings['download_screen_max_w'],
						'max_height' => $dl_settings['download_screen_max_w'],
						'max_byte' => $dl_settings['download_screen_max_b'],
						'type' => 'image',
						'delete_original' => 0,
						'thumbnail_folder' => '',
						'thumbnail' => 1,
						'thumbnail_suffix' => '_thumb',
						'thumbnail_w' => $dl_settings['download_thumb_max_w'],
						'thumbnail_h' => $dl_settings['download_thumb_max_h'],
						'thumbnail2' => 0,
						'valid_ext' => implode('.', array_keys(img_mimeTypes())),
						"width" => "100%",
						"template" => "modern",
					);
					echo form_fileinput('download_image', $locale['download_0220'], '', $file_options); // all file types.
					echo "<div class='m-b-10'>".sprintf($locale['download_0219'], parsebytesize($dl_settings['download_screen_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $dl_settings['download_screen_max_w'], $dl_settings['download_screen_max_h'])."</div>\n";
				}
			}
			echo form_button('publish', $locale['download_0061'], $locale['download_0061'], array(
				'class' => 'btn-primary m-r-10',
			));
			closeside();

			openside('');
			echo form_checkbox('download_allow_comments', $locale['download_0223'], $callback_data['download_allow_comments'], array('class' => 'm-b-0'));
			echo form_checkbox('download_allow_ratings', $locale['download_0224'], $callback_data['download_allow_ratings'], array('class' => 'm-b-0'));
			if (isset($_GET['action']) && $_GET['action'] == "edit") {
				echo form_checkbox('update_datestamp', $locale['download_0213'], '', array('class' => 'm-b-0'));
			}
			closeside();

			openside();
			echo form_text('download_license', $locale['download_0208'], $callback_data['download_license'], array('inline' => 1));
			echo form_text('download_copyright', $locale['download_0222'], $callback_data['download_copyright'], array('inline' => 1));
			echo form_text('download_os', $locale['download_0209'], $callback_data['download_os'], array('inline' => 1));
			echo form_text('download_version', $locale['download_0210'], $callback_data['download_version'], array('inline' => 1));
			echo form_text('download_homepage', $locale['download_0221'], $callback_data['download_homepage'], array('inline' => 1));
			echo form_text('download_filesize', $locale['download_0211'], $callback_data['download_filesize'], array('inline' => 1));
			closeside();
			echo "</div>\n</div>\n"; // end row.
			echo form_button('publish', $locale['download_0061'], $locale['download_0061'], array('class' => 'btn-primary m-r-10'));
			echo form_button('delete', $locale['download_0060'], $locale['download_0060'], array('class' => 'btn-warning m-r-10'));
			echo closeform();
		}
	}

} else {

	$result = dbquery("SELECT
			ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_type='d' order by submit_datestamp desc
			");
	$rows = dbrows($result);
	if ($rows > 0) {
		echo "<div class='well'>".sprintf($locale['download_0051'], format_word($rows, $locale['fmt_submission']))."</div>\n";
		echo "<table class='table table-striped'>\n";
		echo "<tr>\n";
		echo "<th>".$locale['download_0052']."</th>\n<th>".$locale['download_0053']."</th>
		<th>".$locale['download_0054']."</th><th>".$locale['download_0055']."</th>";
		echo "</tr>\n";
		echo "<tbody>\n";
		while ($callback_data = dbarray($result)) {
			$submit_criteria = unserialize($callback_data['submit_criteria']);
			echo "<tr>\n";
			echo "<td><a href='".clean_request("submit_id=".$callback_data['submit_id'], array(
					"section",
					"aid"
				), TRUE)."'>".$submit_criteria['download_title']."</a></td>\n";
			echo "<td>".profile_link($callback_data['user_id'], $callback_data['user_name'], $callback_data['user_status'])."</td>\n";
			echo "<td>".timer($callback_data['submit_datestamp'])."</td>\n";
			echo "<td>".$callback_data['submit_id']."</td>\n";
			echo "</tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} else {
		echo "<div class='well text-center m-t-20'>".$locale['download_0050']."</div>\n";
	}
}
