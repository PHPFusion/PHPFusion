<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads.php
| Author: Nick Jones (Digitanium)
| Co-Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";

if (!checkrights("D") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include LOCALE.LOCALESET."admin/downloads.php";
add_to_breadcrumbs(array('link'=>FUSION_SELF.$aidlink, 'title'=>$locale['400b']));
$_GET['download_cat_id'] = isset($_GET['download_cat_id']) && isnum($_GET['download_cat_id']) ? $_GET['download_cat_id'] : 0;

$message = '';
if (isset($_GET['status'])) {
	if ($_GET['status'] == "sn") {
		$message .= $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message .= $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message .= $locale['412'];
	}
}
if ($message != "") {
	echo "<div id='close-message'><div class='alert alert-info m-t-10 admin-message'>".$message."</div></div>\n";
}
	// master template
	$master_tab_title['title'][] = $locale['402'];
	$master_tab_title['id'][] = "downloads";
	$master_tab_title['icon'][] = "";

	$master_tab_title['title'][] = isset($_GET['action']) ? $locale['401'] : $locale['400'];
	$master_tab_title['id'][] = "dlopts";
	$master_tab_title['icon'][] = "";

	$master_tab_active = isset($_GET['status']) ? tab_active($master_tab_title, 0) : tab_active($master_tab_title, 1);

	opentable($locale['400b']);
	echo opentab($master_tab_title, $master_tab_active, 'download-master');
	echo opentabbody($master_tab_title['title'][0], 'downloads', $master_tab_active);
	echo "<div class='m-t-20'>\n";
	download_listing();
	echo "</div>\n";
	echo closetabbody();
	echo opentabbody($master_tab_title['title'][1], 'dlopts', $master_tab_active);
	echo "<div class='m-t-20'>\n";
	download_form();
	echo "</div>\n";
	echo closetabbody();
	echo closetab();
	closetable();


/* Download Form */
function download_form() {
	global $locale, $settings, $userdata, $aidlink, $defender;

	$data = array();
	/* save */
	if (isset($_POST['save_download'])) {
		$error = 0;
		$data['download_id'] = isset($_POST['download_id']) && isnum($_POST['download_id']) ? $_POST['download_id'] : 0;
		$data['download_user'] = $userdata['user_id'];
		$data['download_homepage'] = isset($_POST['download_homepage']) ? form_sanitizer($_POST['download_homepage'], '', 'download_homepage') : '';
		$data['download_title'] = isset($_POST['download_title']) ? form_sanitizer($_POST['download_title'], '', 'download_title') : '';
		$data['download_cat'] = isset($_POST['download_cat']) ? form_sanitizer($_POST['download_cat'], '', 'download_cat') : '';
		$data['download_description_short'] = isset($_POST['download_description_short']) ? form_sanitizer($_POST['download_description_short'], '', 'download_description_short') : '';
		$data['download_description'] = isset($_POST['download_description']) ? form_sanitizer($_POST['download_description'], '', 'download_description') : '';
		$data['download_keywords'] = isset($_POST['download_keywords']) ? form_sanitizer($_POST['download_keywords'], '', 'download_keywords') : '';
		$data['download_image_thumb'] = isset($_POST['download_image_thumb']) ? form_sanitizer($_POST['download_image_thumb'], '', 'download_image_thumb') : '';
		$data['download_url'] = isset($_POST['download_url']) ? form_sanitizer($_POST['download_url'], '', 'download_url') : '';
		$data['download_file'] = isset($_POST['download_file']) ? form_sanitizer($_POST['download_file'], '', 'download_file') : '';
		$data['download_license'] = isset($_POST['download_license']) ? form_sanitizer($_POST['download_license'], '', 'download_license') : '';
		$data['download_copyright'] = isset($_POST['download_copyright']) ? form_sanitizer($_POST['download_copyright'], '', 'download_copyright') : '';
		$data['download_os'] = isset($_POST['download_os']) ? form_sanitizer($_POST['download_os'], '', 'download_os') : '';
		$data['download_version'] = isset($_POST['download_version']) ? form_sanitizer($_POST['download_version'], '', 'download_version') : '';
		$data['download_filesize'] = isset($_POST['download_filesize']) ? form_sanitizer($_POST['download_filesize'], '', 'download_filesize') : '';
		$data['download_allow_comments'] = isset($_POST['download_allow_comments']) ? 1 : 0;
		$data['download_allow_ratings'] = isset($_POST['download_allow_ratings']) ? 1 : 0;

		if (isset($_POST['del_upload']) && isset($_GET['download_id']) && isnum($_GET['download_id'])) {
			$result2 = dbquery("SELECT download_file FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
			if (dbrows($result2)) {
				$data2 = dbarray($result2);
				if (!empty($data2['download_file']) && file_exists(DOWNLOADS.$data2['download_file'])) {
					@unlink(DOWNLOADS.$data2['download_file']);
				}
			}
			$data['download_file'] = '';
			$data['download_filesize'] = '';
		}

		elseif (!empty($_FILES['download_file']['name']) && is_uploaded_file($_FILES['download_file']['tmp_name'])) {

			require_once INCLUDES."infusions_include.php";
			$data['download_url'] = '';
			// Name of $_FILE key which holds the upload
			$source_file = "download_file";
			// Left blank to use the filename as it is
			$target_file = $_FILES['download_file']['name'];
			// Upload folder
			$target_folder = DOWNLOADS;
			// Valid file extensions
			// $valid_ext = explode(",", $settings['download_types']);
			// $valid_ext = implode("|", $valid_ext);
			// Maximum file size in bytes
			$max_size = $settings['download_max_b'];
			$upload = upload_file($source_file, $target_file, $target_folder, $settings['download_types'], $max_size);
			if ($upload['error'] !=0) {

				$defender->stop();
				if ($upload['error'] == 1) {
					// Maximum file size exceeded
					$defender->addNotice(sprintf($locale['415'], $max_size));
				} elseif ($upload['error'] == 2) {
					// Invalid file extension
					$defender->addNotice(sprintf($locale['416'], $settings['download_types']));
				} elseif ($upload['error'] == 3) {
					// Invalid query string
					$defender->addNotice($locale['419a']);
				} elseif ($upload['error'] == 4) {
					// File not uploaded
					$defender->addNotice($locale['418']);
				}
			}
			else {
				// Successful upload!
				$data['download_file'] = $upload['target_file'];
				if ($data['download_filesize'] == "" || isset($_POST['calc_upload'])) {
					$data['download_filesize'] = parsebytesize($upload['source_size']);
				}
			}
		}

		elseif (isset($_POST['download_file']) && $_POST['download_file'] != "") {
			$data['download_file'] = $_POST['download_file'];
		}

		elseif ((isset($_POST['download_url']) && $_POST['download_url'] != "")) {
			$data['download_url'] = (isset($_POST['download_url']) ? stripinput($_POST['download_url']) : "");
			$data['download_file'] = '';
		}

		else {
			$defender->stop();
			$defender->addNotice($locale['418']);
		}


		if (isset($_POST['del_image']) && isset($_GET['download_id']) && isnum($_GET['download_id'])) {
			$result = dbquery("SELECT download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
			if (dbrows($result)) {
				$data += dbarray($result);
				if (!empty($data['download_image']) && file_exists(DOWNLOADS."images/".$data['download_image'])) {
					@unlink(DOWNLOADS."images/".$data['download_image']);
				}
				if (!empty($data['download_image_thumb']) && file_exists(DOWNLOADS."images/".$data['download_image_thumb'])) {
					@unlink(DOWNLOADS."images/".$data['download_image_thumb']);
				}
			}
			$data['download_image'] = '';
			$data['download_image_thumb'] = '';
		}
		elseif (!empty($_FILES['download_image']['name']) && is_uploaded_file($_FILES['download_image']['tmp_name'])) {
			require_once INCLUDES."infusions_include.php";
			// Name of $_FILE key which holds the uploaded image
			$image = "download_image";
			// Left blank to use the image name as it is
			$name = $_FILES['download_image']['name'];
			// Upload folder
			$folder = DOWNLOADS."images/";
			// Maximum image width in pixels
			$width = $settings['download_screen_max_w'];
			// Maximum image height in pixels
			$height = $settings['download_screen_max_w'];
			// Maximum file size in bytes
			$size = $settings['download_screen_max_b'];
			$upload = upload_image($image, $name, $folder, $width, $height, $size, FALSE, TRUE, FALSE, 1, $folder, "_thumb", $settings['download_thumb_max_w'], $settings['download_thumb_max_h']);
			if ($upload['error'] > 0) {
				$defender->stop();
				switch ($upload['error']) {
					case 1:
						// Invalid file size
						$defender->addNotice(sprintf($locale['415'], $settings['download_screen_max_b']));
						break;
					case 2:
						// Unsupported image type
						$error = 10;
						$defender->addNotice(sprintf($locale['416a'], '.jpg,.png,.gif'));
						break;
					case 3:
						// Invalid image resolution
						$error = 11;
						$defender->addNotice(sprintf($locale['415'], "".$width." x ".$height.""));
						break;
					case 4:
						// Invalid query string
						$error = 12;
						$defender->addNotice($locale['419a']);
						break;
					case 5:
						// Image not uploaded
						//$error = 13;
						$defender->addNotice($locale['419a']);
						break;
				}
				/*
				$data['download_image'] = (isset($_POST['download_image']) ? $_POST['download_image'] : "");
				$data['download_image_thumb'] = (isset($_POST['download_image_thumb']) ? $_POST['download_image_thumb'] : "");
				if (isset($_POST['download_file']) && $_POST['download_file'] != "") {
					$data['download_file'] = $_POST['download_file'];
				} elseif ((isset($_POST['download_url']) && $_POST['download_url'] != "")) {
					$data['download_url'] = (isset($_POST['download_url']) ? stripinput($_POST['download_url']) : "");
					$data['download_file'] = "";
				} else {
					@unlink(DOWNLOADS.$data['download_file']);
					$data['download_file'] = '';
				} */
			} else {
				// Successful upload!
				$data['download_image'] = $upload['image_name'];
				$data['download_image_thumb'] = $upload['thumb1_name'];
			}
		}
		elseif (isset($_POST['download_image']) && $_POST['download_image'] != "") {
			$data['download_image'] = $_POST['download_image'];
			$data['download_image_thumb'] = $_POST['download_image_thumb'];
		}

		if (!defined('FUSION_NULL')) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
				$data['download_datestamp'] = isset($_POST['update_datestamp']) ? time() : '';
				dbquery_insert(DB_DOWNLOADS, $data, 'update');
				redirect(FUSION_SELF.$aidlink."&status=su");
			} else {
				dbquery_insert(DB_DOWNLOADS, $data, 'save');
				redirect(FUSION_SELF.$aidlink."&status=sn");
			}
		} else {
			echo "FUSION_NULL DECLARED";
		}
	}
	/* delete */
	if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
		$result = dbquery("SELECT download_file, download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			if (!empty($data['download_file']) && file_exists(DOWNLOADS.$data['download_file'])) {
				@unlink(DOWNLOADS.$data['download_file']);
			}
			if (!empty($data['download_image']) && file_exists(DOWNLOADS."images/".$data['download_image'])) {
				@unlink(DOWNLOADS."images/".$data['download_image']);
			}
			if (!empty($data['download_image_thumb']) && file_exists(DOWNLOADS."images/".$data['download_image_thumb'])) {
				@unlink(DOWNLOADS."images/".$data['download_image_thumb']);
			}
			$result = dbquery("DELETE FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		}
		redirect(FUSION_SELF.$aidlink."&download_cat_id=".intval($_GET['download_cat_id'])."&status=del");
	}
	/* edit */
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
		$result = dbquery("SELECT * FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$data['download_user'] = isset($_POST['download_user']) ? form_sanitizer($_POST['download_user'], '', 'download_user') : $data['download_user'];
			$data['download_homepage'] = isset($_POST['download_homepage']) ? form_sanitizer($_POST['download_homepage'], '', 'download_homepage') : $data['download_homepage'];
			$data['download_title'] = isset($_POST['download_title']) ? form_sanitizer($_POST['download_title'], '', 'download_title') : $data['download_title'];
			$data['download_description_short'] = isset($_POST['download_description_short']) ? form_sanitizer($_POST['download_description_short'], '', 'download_description_short') : $data['download_description_short'];
			$data['download_description'] = isset($_POST['download_description']) ? form_sanitizer($_POST['download_description'], '', 'download_description') : $data['download_description'];
			$data['download_keywords'] = isset($_POST['download_keywords']) ? form_sanitizer($_POST['download_keywords'], '', 'download_keywords') : $data['download_keywords'];
			$data['download_image'] = isset($_POST['download_image']) ? form_sanitizer($_POST['download_image'], '', 'download_image') : $data['download_image'];
			$data['download_image_thumb'] = isset($_POST['download_image_thumb']) ? form_sanitizer($_POST['download_image_thumb'], '', 'download_image_thumb') : $data['download_image_thumb'];
			$data['download_url'] = isset($_POST['download_url']) ? form_sanitizer($_POST['download_url'], '', 'download_url') : $data['download_url'];
			$data['download_file'] = isset($_POST['download_file']) ? form_sanitizer($_POST['download_file'], '', 'download_file') : $data['download_file'];
			$data['download_license'] = isset($_POST['download_license']) ? form_sanitizer($_POST['download_license'], '', 'download_license') : $data['download_license'];
			$data['download_copyright'] = isset($_POST['download_copyright']) ? form_sanitizer($_POST['download_copyright'], '', 'download_copyright') : $data['download_copyright'];
			$data['download_os'] = isset($_POST['download_os']) ? form_sanitizer($_POST['download_os'], '', 'download_os') : $data['download_os'];
			$data['download_version'] = isset($_POST['download_version']) ? form_sanitizer($_POST['download_version'], '', 'download_version') : $data['download_version'];
			$data['download_allow_comments'] = isset($_POST['download_allow_comments']) ? form_sanitizer($_POST['download_allow_comments'], 0, 'download_allow_comments') : $data['download_allow_comments'];
			$data['download_allow_ratings'] = isset($_POST['download_allow_ratings']) ? form_sanitizer($_POST['download_allow_ratings'], 0, 'download_allow_ratings') : $data['download_allow_ratings'];
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;download_cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id'];

		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
	/* Init */
	else {
		$data['download_id'] = '';
		$data['download_user'] = $userdata['user_id'];
		$data['download_keywords'] = isset($_POST['download_keywords']) ? form_sanitizer($_POST['download_keywords'], '', 'download_keywords') : '';
		$data['download_homepage'] = isset($_POST['download_homepage']) ? form_sanitizer($_POST['download_homepage'], '', 'download_homepage') : '';
		$data['download_title'] = isset($_POST['download_title']) ? form_sanitizer($_POST['download_title'], '', 'download_title') : '';
		$data['download_cat'] = isset($_POST['download_cat']) ? form_sanitizer($_POST['download_cat'], '', 'download_cat') : '';
		$data['download_description_short'] = isset($_POST['download_description_short']) ? form_sanitizer($_POST['download_description_short'], '', 'download_description_short') : '';
		$data['download_description'] = isset($_POST['download_description']) ? form_sanitizer($_POST['download_description'], '', 'download_description') : '';
		$data['download_keywords'] = isset($_POST['download_keywords']) ? form_sanitizer($_POST['download_keywords'], '', 'download_keywords') : '';
		$data['download_image_thumb'] = isset($_POST['download_image_thumb']) ? form_sanitizer($_POST['download_image_thumb'], '', 'download_image_thumb') : '';
		$data['download_url'] = isset($_POST['download_url']) ? form_sanitizer($_POST['download_url'], '', 'download_url') : '';
		$data['download_file'] = isset($_POST['download_file']) ? form_sanitizer($_POST['download_file'], '', 'download_file') : '';
		$data['download_license'] = isset($_POST['download_license']) ? form_sanitizer($_POST['download_license'], '', 'download_license') : '';
		$data['download_copyright'] = isset($_POST['download_copyright']) ? form_sanitizer($_POST['download_copyright'], '', 'download_copyright') : '';
		$data['download_os'] = isset($_POST['download_os']) ? form_sanitizer($_POST['download_os'], '', 'download_os') : '';
		$data['download_version'] = isset($_POST['download_version']) ? form_sanitizer($_POST['download_version'], '', 'download_version') : '';
		$data['download_filesize'] = isset($_POST['download_filesize']) ? form_sanitizer($_POST['download_filesize'], '', 'download_filesize') : '';
		$data['download_allow_comments'] = isset($_POST['download_allow_comments']) ? 1 : 0;
		$data['download_allow_ratings'] = isset($_POST['download_allow_ratings']) ? 1 : 0;
		$formaction = FUSION_SELF.$aidlink;
	}

	$editlist = array();
	$result2 = dbquery("
	SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS."
	".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")." ORDER BY download_cat_name
	");
	if (dbrows($result2) != 0) {
		while ($data2 = dbarray($result2)) {
			$editlist[$data2['download_cat_id']] = $data2['download_cat_name'];
		}
	}

	echo openform('inputform', 'inputform', 'post', $formaction, array('downtime' => 0, 'enctype' => 1));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8 col-md-7 col-lg-8'>\n";
	echo form_text($locale['420'], 'download_title', 'download_title', $data['download_title'], array('required' => 1, 'error_text'=>$locale['417']));
	echo form_select($locale['421c'], 'download_keywords', 'download_keywords', array(), $data['download_keywords'], array('tags'=>1, 'width'=>'100%'));
	echo "<hr/>\n";
	echo "</div>\n<div class='col-xs-12 col-sm-4 col-md-5 col-lg-4'>\n";
	openside();
	echo form_select($locale['423'], 'download_cat', 'download_cat', $editlist, $data['download_cat'], array('placeholder' => $locale['choose'], 'width' => '100%'));
	echo form_hidden('', 'download_id', 'download_id', $data['download_id']);
	echo form_button($locale['428'], 'save_download', 'save_download', $locale['428'], array('class' => 'btn-primary m-r-10'));
	closeside();
	echo "</div>\n</div>\n"; // end row.
	// second row
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8 col-md-7 col-lg-8'>\n";
	$tab_title['title'][] = $locale['430'];
	$tab_title['id'][] = 'dlf';
	$tab_title['icon'][] = '';
	$tab_title['title'][] = $locale['430b'];
	$tab_title['id'][] = 'dll';
	$tab_title['icon'][] = '';
	$tab_active = tab_active($tab_title, 0);
	echo "<p class='strong pull-right'>".$locale['421d']." <span class='required'>*</span></p>";
	echo opentab($tab_title, $tab_active, 'downloadtab');
	echo opentabbody($tab_title['title'][0], 'dlf', $tab_active);
	if (!empty($data['download_file'])) {
		echo "<div class='list-group-item m-t-10'>".$locale['430']." - <a href='".DOWNLOADS.$data['download_file']."'>".DOWNLOADS.$data['download_file']."</a>\n";
		echo "<hr/>\n";
		echo form_checkbox($locale['431'], 'del_upload', 'del_upload', '', array('class'=>'m-b-0'));
		echo "</div>\n";
		echo form_hidden('', 'download_file', 'download_file', $data['download_file']);
	} else {
		echo "<div class='list-group m-t-10'><div class='list-group-item'>\n";
		echo form_fileinput($locale['430'], 'download_file', 'download_file', DOWNLOADS, ''); // all file types.
		echo sprintf($locale['433'], parsebytesize($settings['download_max_b']), str_replace(',', ' ', $settings['download_types']))."<br />\n";
		echo "</div>\n";
		echo "<div class='list-group-item'>\n";
		echo "<input type='checkbox' name='calc_upload' id='calc_upload' value='1' /> <label for='calc_upload'>".$locale['432']."</label>\n";
		echo "</div>\n";
		echo "</div>\n";
	}
	echo closetabbody();

	echo opentabbody($tab_title['title'][1], 'dll', $tab_active);
	if (empty($data['download_file'])) {
		echo "<div class='list-group m-t-10'><div class='list-group-item'>\n";
		echo form_text($locale['422'], 'download_url', 'download_url', $data['download_url']);
		echo "</div>\n</div>\n";
	} else {
		echo "<div class='alert alert-info m-t-10'>\n";
		echo "There is a download file attached. Remove it to change to url type";
		echo "</div>\n";
		echo form_hidden('', 'download_url', 'download_url', $data['download_url']);
	}
	echo closetabbody();
	echo closetab();
	echo "<hr/>\n";
	echo "<div class='row'>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
	echo form_text($locale['424'], 'download_license', 'download_license', $data['download_license'], array('inline' => 1));
	echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
	echo form_text($locale['436'], 'download_copyright', 'download_copyright', $data['download_copyright'], array('inline' => 1));
	echo "</div></div>\n";
	echo "<hr/>\n";
	echo "</div>\n<div class='col-xs-12 col-sm-4 col-md-5 col-lg-4'>\n";
	openside('Additional Info');
	echo form_text($locale['425'], 'download_os', 'download_os', $data['download_os'], array('inline' => 1));
	echo form_text($locale['426'], 'download_version', 'download_version', $data['download_version'], array('inline' => 1));
	echo form_text($locale['435'], 'download_homepage', 'download_homepage', $data['download_homepage'], array('inline' => 1));
	echo form_text($locale['427'], 'download_filesize', 'download_filesize', $data['download_filesize'], array('inline' => 1));
	closeside();
	echo "</div>\n</div>\n"; // end row.
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8 col-md-7 col-lg-8'>\n";
	echo form_textarea($locale['421b'], 'download_description_short', 'download_description_short', $data['download_description_short'], array('required'=>1, 'error_text'=>$locale['419'], 'no_resize' => '1', 'maxlength' => '255', 'form_name' => 'inputform', 'html' => 1, 'autosize' => 1, 'preview' => 1));
	echo form_textarea($locale['421'], 'download_description', 'download_description', $data['download_description'], array('no_resize' => '1', 'form_name' => 'inputform', 'html' => 1, 'autosize' => 1, 'preview' => 1));

	// go for multiple.
	if ($settings['download_screenshot']) {
		if (!empty($data['download_image']) && !empty($data['download_image_thumb'])) {
			echo "<img src='".DOWNLOADS."images/".$data['download_image_thumb']."' /><br />\n";
			echo "<label><input type='checkbox' name='del_image' value='1' /> ".$locale['431']."</label>\n";
			echo "<input type='hidden' name='download_image' value='".$data['download_image']."' />";
			echo "<input type='hidden' name='download_image_thumb' value='".$data['download_image_thumb']."' />";
		} else {
			echo form_fileinput($locale['434'], 'download_image', 'download_image', DOWNLOADS, '', array('type' => 'image')); // all file types.
			echo sprintf($locale['433b'], parsebytesize($settings['download_screen_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $settings['download_screen_max_w'], $settings['download_screen_max_h'])."<br />\n";
		}
	}
	echo "</div>\n<div class='col-xs-12 col-sm-4 col-md-5 col-lg-4'>\n";
	openside('Options');
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
			$sys = $locale['464'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['462'];
		} else {
			$sys = $locale['463'];
		}
		echo "<div class='well'>".sprintf($locale['461'], $sys)."</div>\n";
	}
	echo form_checkbox($locale['437'], 'download_allow_comments', 'download_allow_comments', $data['download_allow_comments']);
	echo form_checkbox($locale['438'], 'download_allow_ratings', 'download_allow_ratings', $data['download_allow_ratings']);

	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo form_checkbox($locale['429'], 'update_datestamp', 'update_datestamp', '');
	}
	closeside();
	echo "</div>\n</div>\n"; // end row.
	echo form_hidden('', 'download_user', 'download_user', $userdata['user_id']);
	echo "<div class='m-t-20'>\n";
	echo form_button($locale['428'], 'save_download', 'save_download2', $locale['428'], array('class' => 'btn-primary m-r-10'));
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo "<button type='reset' name='reset' value='".$locale['439']."' class='button btn btn-default' onclick=\"location.href='".FUSION_SELF.$aidlink."';\"/>".$locale['439']."</button>";
	}
	echo "</div>\n";
	echo closeform();
}

/* Download Listing */
function download_listing() {
	global $aidlink, $locale;

	$result = dbcount("(download_cat_id)", DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")."");
	if (!empty($result)) {
		opentable($locale['402']);
		$result = dbquery("SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS." ORDER BY download_cat_name");
		if (dbrows($result)) {
			echo opencollapse('download-list');
			while ($data = dbarray($result)) {
				$result2 = dbquery("SELECT download_id, download_title, download_description_short, download_url, download_file, download_image FROM ".DB_DOWNLOADS." WHERE download_cat='".$data['download_cat_id']."' ORDER BY download_title");
				$rows = dbrows($result2);
				echo "<div class='panel panel-default'>\n";
				/* Panel Heading */
				echo "<div class='panel-heading'>\n";
					echo "<div class='row'>\n";
					echo "<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
					echo "<span class='display-inline-block strong'><a ".collapse_header_link('download-list', $data['download_cat_id'], '0', 'm-r-10').">".$data['download_cat_name']."</a></span>\n";
					echo "<span class='badge'>".number_format($rows)."</span>\n";
				echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
				// edit link
				echo "<div class='btn-group pull-right'>\n";
				echo "<a class='btn btn-default btn-xs' href='".ADMIN."download_cats.php".$aidlink."&amp;action=edit&cat_id=".$data['download_cat_id']."'>".$locale['442']."</a>\n";
				echo "<a class='btn btn-default btn-xs' href='".ADMIN."download_cats.php".$aidlink."&amp;action=delete&cat_id=".$data['download_cat_id']."'>".$locale['443']."</a>\n";
				echo "</div>\n";
				echo "</div>\n</div>\n"; // end row
				echo "</div>\n"; // end panel-heading


				if (dbrows($result2) != 0) {
					echo "<div ".collapse_footer_link('download-list', $data['download_cat_id'], '0').">\n";
					echo "<div class='list-group p-15'>\n";
					while($data2 = dbarray($result2)) {
						if (!empty($data2['download_file']) && file_exists(DOWNLOADS.$data2['download_file'])) {
							$download_url = DOWNLOADS.$data2['download_file'];
						} elseif (!strstr($data2['download_url'], "http://") && !strstr($data2['download_url'], "../")) {
							$download_url = BASEDIR.$data2['download_url'];
						} else {
							$download_url = $data2['download_url'];
						}
						echo "<div class='list-group-item clearfix'>\n";
							echo "<div class='pull-left m-r-10'>\n";
							echo ($data2['download_image'] && file_exists(DOWNLAODS."images/".$data2['download_image'])) ? thumbnail(DOWNLOADS.'images'.$data2['download_image'], '50px') : thumbnail(IMAGES.'imagenotfound.jpg', '50px');
							echo "</div>\n";
						echo "<div class='overflow-hide'>\n";

						echo "<div class='overflow-hide'>\n";
						echo "<span class='strong text-dark'>".$data2['download_title']."</span><br/>\n";
						echo nl2br(parseubb($data2['download_description_short']));
						echo "<div>\n";
						echo "<a class='m-r-10' href='$download_url'>".$locale['400b']."</a>\n";
						echo "<a class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."'>".$locale['442']."</a>\n";
						echo "<a  class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."' onclick=\"return confirm('".$locale['460']."');\">".$locale['443']."</a>\n";
						echo "</div>\n";

						echo "</div>\n";
						echo "</div>\n";
						echo "</div>\n";
					}
					echo "</div>\n";
					echo "</div>\n";
				}
				echo "</div>\n";
			}
			echo closecollapse();
		} else {
			echo "<div class='well text-center'>".$locale['450']."</div>\n";
		}
		closetable();
	} else {
		echo "<div class='well text-center'>\n";
		echo "".$locale['451']."<br />\n".$locale['452']."<br />\n";
		echo "<a href='download_cats.php".$aidlink."'>".$locale['453']."</a>".$locale['454']."</div>\n";
		echo "</div>\n";
	}
}

add_to_jquery("
    $('#shortdesc_display').show();
    $('#calc_upload').bind('click', function() {
        if ($('#calc_upload').attr('checked')) {
            $('#download_filesize').attr('readonly', 'readonly');
            $('#download_filesize').val('');
        } else {
           $('#download_filesize').removeAttr('readonly');
        }
    });
    ");

require_once THEMES."templates/footer.php";
?>
