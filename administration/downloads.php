<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads.php
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
require_once "../maincore.php";

if (!checkrights("D") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include LOCALE.LOCALESET."admin/downloads.php";

// initalize vars
$download_user = $userdata['user_id'];
$download_homepage = "";
$download_title = "";
$download_description_short = "";
$download_description = "";
$download_image = "";
$download_image_thumb = "";
$download_url = "";
$download_file = "";
$download_license = "";
$download_copyright = "";
$download_os = "";
$download_version = "";
$download_filesize = "";
$downloadComments = " checked='checked'";
$downloadRatings = " checked='checked'";
$formaction = FUSION_SELF.$aidlink;
$message = "";

$result = dbcount("(download_cat_id)", DB_DOWNLOAD_CATS);
if (!empty($result)) {
	$download_file = "";
	$download_filesize = "";
	$download_image = "";
	$download_image_thumb = "";
	$download_url = "";
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
	} elseif (isset($_POST['save_download'])) {
		$error = 0;
		$download_title = stripinput($_POST['download_title']);
		if (!$download_title) {$error = 5;}
		$download_description_short = stripinput($_POST['download_description_short']);
		$download_description = stripinput($_POST['download_description']);
		if (!$download_description_short) {$error = 6;}
		$download_filesize = stripinput($_POST['download_filesize']);
		if (isset($_POST['del_upload']) && isset($_GET['download_id']) && isnum($_GET['download_id'])) {
			$result = dbquery("SELECT download_file FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				if (!empty($data['download_file']) && file_exists(DOWNLOADS.$data['download_file'])) {
					@unlink(DOWNLOADS.$data['download_file']);
				}
			}
			$download_file = "";
			$download_filesize = "";
		} elseif (!empty($_FILES['download_file']['name']) && is_uploaded_file($_FILES['download_file']['tmp_name'])) {
			$download_url = "";
			require_once INCLUDES."infusions_include.php";
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
			if ($upload['error'] == 1) {
				// Maximum file size exceeded
				$error = 2;
			} elseif ($upload['error'] == 2) {
			    // Invalid file extension
				$error = 3;
			} elseif ($upload['error'] == 3) {
			    // Invalid query string
				$error = 7;
			} elseif ($upload['error'] == 4) {
			    // File not uploaded
				$error = 8;
			} else {
			    // Successful upload!
				$download_file = $upload['target_file'];
				if ($download_filesize == "" || isset($_POST['calc_upload'])) {
					$download_filesize = parsebytesize($upload['source_size']);
				}
			}
		} elseif (isset($_POST['download_file']) && $_POST['download_file'] != "") {
			$download_file = $_POST['download_file'];
		} elseif ((isset($_POST['download_url']) && $_POST['download_url'] != "")) {
			$download_url = (isset($_POST['download_url']) ? stripinput($_POST['download_url']) : "");
			$download_file = "";
		} else {
			$error = 4;
		}
		if (isset($_POST['del_image']) && isset($_GET['download_id']) && isnum($_GET['download_id'])) {
			$result = dbquery("SELECT download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				if (!empty($data['download_image']) && file_exists(DOWNLOADS."images/".$data['download_image'])) {
					@unlink(DOWNLOADS."images/".$data['download_image']);
				}
				if (!empty($data['download_image_thumb']) && file_exists(DOWNLOADS."images/".$data['download_image_thumb'])) {
					@unlink(DOWNLOADS."images/".$data['download_image_thumb']);
				}
			}
			$download_image = "";
			$download_image_thumb = "";
		} elseif (!empty($_FILES['download_image']['name']) && is_uploaded_file($_FILES['download_image']['tmp_name'])) {
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

			$upload = upload_image($image, $name, $folder, $width, $height, $size, false, true, false, 1, $folder, "_thumb", $settings['download_thumb_max_w'], $settings['download_thumb_max_h']);

			if ($upload['error'] != 0) {
				switch ($upload['error']) {
					case 1:
					// Invalid file size
					$error = 9;
					break;
					case 2:
					// Unsupported image type
					$error = 10;
					break;
					case 3:
					// Invalid image resolution
					$error = 11;
					break;
					case 4:
					// Invalid query string
					$error = 12;
					break;
					case 5:
					// Image not uploaded
					$error = 13;
					break;
				}
				$download_image = (isset($_POST['download_image']) ? $_POST['download_image'] : "");
				$download_image_thumb = (isset($_POST['download_image_thumb']) ? $_POST['download_image_thumb'] : "");
				if (isset($_POST['download_file']) && $_POST['download_file'] != "") {
					$download_file = $_POST['download_file'];
				} elseif ((isset($_POST['download_url']) && $_POST['download_url'] != "")) {
					$download_url = (isset($_POST['download_url']) ? stripinput($_POST['download_url']) : "");
					$download_file = "";
				} else {
					@unlink(DOWNLOADS.$download_file);
					$download_file = "";
				}
			} else {
				// Successful upload!
				$download_image = $upload['image_name'];
				$download_image_thumb = $upload['thumb1_name'];
			}

		} elseif (isset($_POST['download_image']) && $_POST['download_image'] != "") {
			$download_image = $_POST['download_image'];
			$download_image_thumb = $_POST['download_image_thumb'];
		}
		$download_user = stripinput($_POST['download_user']);
		$download_homepage = stripinput($_POST['download_homepage']);
		$download_cat = intval($_POST['download_cat']);
		$download_license = stripinput($_POST['download_license']);
		$download_copyright = stripinput($_POST['download_copyright']);
		$download_os = stripinput($_POST['download_os']);
		$download_version = stripinput($_POST['download_version']);
		$download_allow_comments = (isset($_POST['download_allow_comments']) ? "1" : "0");
		$download_allow_ratings = (isset($_POST['download_allow_ratings']) ? "1" : "0");
		if ($download_title && $error == 0) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
				$download_datestamp = isset($_POST['update_datestamp']) ? ", download_datestamp='".time()."'" : "";
				$result = dbquery("UPDATE ".DB_DOWNLOADS." SET
					download_user='".$download_user."',
					download_homepage='".$download_homepage."',
					download_title='".$download_title."',
					download_description_short='".$download_description_short."',
					download_description='".$download_description."',
					download_image='".$download_image."',
					download_image_thumb='".$download_image_thumb."',
					download_url='".$download_url."',
					download_file='".$download_file."',
					download_cat='".$download_cat."',
					download_license='".$download_license."',
					download_copyright='".$download_copyright."',
					download_os='".$download_os."',
					download_version='".$download_version."',
					download_filesize='".$download_filesize."',
					download_allow_comments='".$download_allow_comments."',
					download_allow_ratings='".$download_allow_ratings."'
					".$download_datestamp." WHERE download_id='".$_GET['download_id']."'");
				redirect(FUSION_SELF.$aidlink."&download_cat_id=".$download_cat."&status=su");
			} else {
				$result = dbquery("INSERT INTO ".DB_DOWNLOADS." SET
				download_user = '".$download_user."',
				download_homepage = '".$download_homepage."',
				download_title = '".$download_title."',
				download_description_short = '".$download_description_short."',
				download_description = '".$download_description."',
				download_image = '".$download_image."',
				download_image_thumb = '".$download_image_thumb."',
				download_url = '".$download_url."',
				download_file = '".$download_file."',
				download_cat = '".$download_cat."',
				download_license = '".$download_license."',
				download_copyright = '".$download_copyright."',
				download_os = '".$download_os."',
				download_version = '".$download_version."',
				download_filesize = '".$download_filesize."',
				download_allow_comments = '".$download_allow_comments."',
				download_allow_ratings = '".$download_allow_ratings."',
				download_datestamp = '".time()."',
				download_count = '0'");
				redirect(FUSION_SELF.$aidlink."&download_cat_id=".$download_cat."&status=sn");
			}
		} else {
			switch($error) {
				case 0: $message .= $locale['417']."</span>"; break;
				case 1: $message .= $locale['414']."</span>"; break;
				case 2: $message .= sprintf($locale['415'], parsebytesize($settings['download_max_b']))."</span>"; break;
				case 3: $message .= sprintf($locale['416'], str_replace(',', ' ', $settings['download_types']))."</span>"; break;
				case 4: $message .= $locale['418']."</span>"; break;
				case 5: $message .= $locale['417']."</span>"; break;
				case 6: $message .= $locale['419']."</span>"; break;
				case 7: $message .= $locale['419a']."</span>"; break;
				case 8: $message .= $locale['419a']."</span>"; break;
				case 9: $message .= sprintf($locale['415a'], parsebytesize($settings['download_screen_max_b']))."</span>"; break;
				case 10: $message .= sprintf($locale['416a'], ".gif .jpg .png")."</span>"; break;
				case 11: $message .= sprintf($locale['415b'], $settings['download_screen_max_w']." x ".$settings['download_screen_max_h'])."</span>"; break;
				case 12: $message .= $locale['419a']."</span>"; break;
				case 13: $message .= $locale['419a']."</span>";break;
			}
		}
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
		$result = dbquery("SELECT download_user, download_homepage, download_title, download_description_short, download_description, download_image, download_image_thumb, download_url, download_file, download_cat, download_license, download_copyright, download_os, download_version, download_filesize, download_allow_comments, download_allow_ratings FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$download_user = $data['download_user'];
			$download_homepage = $data['download_homepage'];
			$download_title = $data['download_title'];
			$download_description_short = stripinput($data['download_description_short']);
			$download_description = stripinput($data['download_description']);
			$download_image = $data['download_image'];
			$download_image_thumb = $data['download_image_thumb'];
			$download_url = $data['download_url'];
			$download_file = $data['download_file'];
			$download_license = $data['download_license'];
			$download_copyright = $data['download_copyright'];
			$download_os = $data['download_os'];
			$download_version = $data['download_version'];
			$download_filesize = $data['download_filesize'];
			$downloadComments = ($data['download_allow_comments'] == "1" ? " checked='checked'" : "");
			$downloadRatings = ($data['download_allow_ratings'] == "1" ? " checked='checked'" : "");
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;download_cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id'];
			opentable($locale['401']);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		opentable($locale['400']);
	}

	if (isset($_GET['status']) && $message == "") {
		if ($_GET['status'] == "sn") {
			$message .= $locale['410'];
		} elseif ($_GET['status'] == "su") {
			$message .= $locale['411'];
		} elseif ($_GET['status'] == "del") {
			$message .= $locale['412'];
		}
	}
	if ($message != "") {  echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }

	$editlist = ""; $sel = "";
	$result2 = dbquery("SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS." ORDER BY download_cat_name");
	if (dbrows($result2) != 0) {
		while ($data2 = dbarray($result2)) {
			if (isset($_GET['action']) && $_GET['action'] == "edit") { $sel = ($data['download_cat'] == $data2['download_cat_id'] ? " selected='selected'" : ""); }
			$editlist .= "<option value='".$data2['download_cat_id']."'$sel>".$data2['download_cat_name']."</option>\n";
		}
	}
	require_once INCLUDES."bbcode_include.php";
	echo "<form id='inputform' name='inputform' method='post' action='".$formaction."' enctype='multipart/form-data'>\n";
	echo "<table cellpadding='0' cellspacing='0' class='center' style='width:500px;'>\n<tr>\n";
	echo "<td class='tbl1' style='width:80px;'>".$locale['420']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='download_title' value='".$download_title."' class='textbox' style='width:380px;' /></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;vertical-align:top;'>".$locale['421b']."<br /><br />";
	echo "<span id='shortdesc_display' style='padding: 1px 3px 1px 3px; border:1px solid; display:none;'>";
	echo "<strong>".(255 - mb_strlen($download_description_short))."</strong>";
	echo "</span>";
	echo "</td>\n";
	echo "<td class='tbl1'><textarea name='download_description_short' cols='60' rows='4' class='textbox' style='width:380px;' onkeydown=\"shortdesc_counter(this,'shortdesc_display',255);\" onkeyup=\"shortdesc_counter(this,'shortdesc_display',255);\">".$download_description_short."</textarea></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px; vertical-align:top;'>".$locale['421']."</td>\n";
	echo "<td class='tbl1'><textarea name='download_description' cols='60' rows='5' class='textbox' style='width:380px;'>".$download_description."</textarea></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1'></td><td class='tbl1'>\n";
	echo display_bbcodes("100%", "download_description", "inputform")."</td>\n";
	echo "</tr>\n";
	if (empty($download_file) || !empty($download_url)) {
		echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['422']."</td>\n";
		echo "<td class='tbl1'><input type='text' name='download_url' value='".$download_url."' class='textbox' style='width:380px;' /></td>\n";
		echo "</tr>\n";
	} else {
		echo "<tr><td class='tbl1'><input type='hidden' name='download_url' value='".$download_url."' /></td>\n</tr>\n";
	}
	echo "<tr>\n";
	echo "<td class='tbl1' style='width:80px; vertical-align:top;'>".$locale['430']."</td>\n<td class='tbl1' style='vertical-align:top;'>\n";
	if (!empty($download_file)) {
		echo "<a href='".DOWNLOADS.$download_file."'>".DOWNLOADS.$download_file."</a><br />\n";
		echo "<label><input type='checkbox' name='del_upload' value='1' /> ".$locale['431']."</label>\n";
		echo "<input type='hidden' name='download_file' value='".$download_file."' />";
	} else {
		echo "<input type='file' name='download_file' class='textbox' style='width:150px;' /><br />\n";
		echo sprintf($locale['433'], parsebytesize($settings['download_max_b']), str_replace(',', ' ', $settings['download_types']))."<br />\n";
		echo "<label><input type='checkbox' name='calc_upload' id='calc_upload' value='1' /> ".$locale['432']."</label>\n";
	}
	echo "</td>\n</tr>\n";
	if ($settings['download_screenshot']) {
		echo "<tr>\n";
		echo "<td class='tbl1' style='width:80px; vertical-align:top;'>".$locale['434']."</td>\n<td class='tbl1' style='vertical-align:top;'>\n";
		if (!empty($download_image) && !empty($download_image_thumb)) {
			echo "<img src='".DOWNLOADS."images/".$download_image_thumb."' /><br />\n";
			echo "<label><input type='checkbox' name='del_image' value='1' /> ".$locale['431']."</label>\n";
			echo "<input type='hidden' name='download_image' value='".$download_image."' />";
			echo "<input type='hidden' name='download_image_thumb' value='".$download_image_thumb."' />";
		} else {
			echo "<input type='file' name='download_image' class='textbox' style='width:150px;' /><br />\n";
			echo sprintf($locale['433b'], parsebytesize($settings['download_screen_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $settings['download_screen_max_w'], $settings['download_screen_max_h'])."<br />\n";
		}
		echo "</td>\n</tr>\n";
	}
	echo "<tr>\n";
	echo "<td class='tbl1' style='width:80px;'>".$locale['423']."</td>\n";
	echo "<td class='tbl1'><select name='download_cat' class='textbox'>\n".$editlist."</select></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['424']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='download_license' value='".$download_license."' class='textbox' style='width:150px;' /></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['425']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='download_os' value='".$download_os."' class='textbox' style='width:150px;' /></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['426']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='download_version' value='".$download_version."' class='textbox' style='width:150px;' /></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['435']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='download_homepage' value='".$download_homepage."' class='textbox' style='width:380px;' /></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['436']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='download_copyright' value='".$download_copyright."' class='textbox' style='width:380px;' /></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['427']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='download_filesize' id='download_filesize' value='".$download_filesize."' class='textbox' style='width:150px;' /></td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['437']."</td>\n";
	echo "<td class='tbl1'><input type='checkbox' name='download_allow_comments' value='1'".$downloadComments." />";
	if ($settings['comments_enabled'] == "0") {
		echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
	}
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['438']."</td>\n";
	echo "<td class='tbl1'><input type='checkbox' name='download_allow_ratings' value='1'".$downloadRatings." />";
	if ($settings['ratings_enabled'] == "0") {
		echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
	}
	echo "</td>\n";
	echo "</tr>\n";
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" &&  $settings['ratings_enabled'] == "0") {
			$sys = $locale['464'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['462'];
		} else {
			$sys = $locale['463'];
		}
		echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
		echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['461'], $sys);
		echo "</td>\n</tr>";
	}
	echo "<tr>\n<td style='text-align:center;' colspan='2' class='tbl1'>";
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo "<label><input type='checkbox' name='update_datestamp' value='1' /> ".$locale['429']."</label><br /><br />\n";
	}
	echo "<input type='hidden' name='download_user' value='".$download_user."' />";
	echo "<input type='submit' name='save_download' value='".$locale['428']."' class='button' />";
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo "&nbsp;<input type='reset' name='reset' value='".$locale['439']."' class='button' onclick=\"location.href='".FUSION_SELF.$aidlink."';\"/>";
	}
	echo "</td>\n</tr>\n</table>\n</form>\n";
	closetable();

	opentable($locale['402']);
	echo "<table cellpadding='0' cellspacing='0' class='center' style='width:400px;'>\n";
	$result = dbquery("SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS." ORDER BY download_cat_name");
	if (dbrows($result)) {
		echo "<tr>\n";
		echo "<td class='tbl2'>".$locale['440']."</td>\n";
		echo "<td style='text-align:right;' class='tbl2'>".$locale['441']."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td colspan='2' height='1'></td>\n";
		echo "</tr>\n";
		while ($data = dbarray($result)) {
			if (!isset($_GET['download_cat_id']) || !isnum($_GET['download_cat_id'])) { $_GET['download_cat_id'] = 0; }
			if ($data['download_cat_id'] == $_GET['download_cat_id']) { $p_img = "off"; $div = ""; } else { $p_img = "on"; $div = "style='display:none'"; }
			echo "<tr>\n";
			echo "<td class='tbl2'>".$data['download_cat_name']."</td>\n";
			echo "<td class='tbl2' style='text-align:right;'><img src='".get_image("panel_$p_img")."' name='b_".$data['download_cat_id']."' alt='' onclick=\"javascript:flipBox('".$data['download_cat_id']."')\" /></td>\n";
			echo "</tr>\n";
			$result2 = dbquery("SELECT download_id, download_title, download_url, download_file FROM ".DB_DOWNLOADS." WHERE download_cat='".$data['download_cat_id']."' ORDER BY download_title");
			if (dbrows($result2) != 0) {
				echo "<tr>\n<td colspan='2'>\n";
				echo "<div id='box_".$data['download_cat_id']."'".$div.">\n";
				echo "<table cellpadding='0' cellspacing='0' style='width:100%;'>\n";
				while ($data2 = dbarray($result2)) {
					if (!empty($data2['download_file']) && file_exists(DOWNLOADS.$data2['download_file'])) {
						$download_url = DOWNLOADS.$data2['download_file'];
					} elseif (!strstr($data2['download_url'],"http://") && !strstr($data2['download_url'],"../")) {
						$download_url = BASEDIR.$data2['download_url'];
					} else {
						$download_url = $data2['download_url'];
					}
					echo "<tr>\n<td class='tbl1'><a href='".$download_url."' target='_blank'>".$data2['download_title']."</a></td>\n";
					echo "<td class='tbl1' style='text-align:right;width:100px;'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."'>".$locale['442']."</a> -\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."' onclick=\"return confirm('".$locale['460']."');\">".$locale['443']."</a></td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n</div>\n</td>\n</tr>\n";
			}
		}
		echo "</table>\n";
	} else {
		echo "<tr>\n<td style='text-align:center;'><br />\n";
		echo $locale['450']."<br /><br /></td>\n";
		echo "</tr>\n</table>\n";
	}
	closetable();
} else {
	opentable($locale['402']);
	echo "<div style='text-align:center'>".$locale['451']."<br />\n".$locale['452']."<br /><br />\n";
	echo "<a href='download_cats.php".$aidlink."'>".$locale['453']."</a>".$locale['454']."</div>\n";
	closetable();
}

echo "<script language='JavaScript' type='text/javascript'>\n";
echo "/* <![CDATA[ */\n";
echo "jQuery(document).ready(function() {
	jQuery('#shortdesc_display').show();
	jQuery('#calc_upload').click(
	function() {
		if (jQuery('#calc_upload').attr('checked')) {
			jQuery('#download_filesize').attr('readonly', 'readonly');
			jQuery('#download_filesize').val('');
			jQuery('#calc_upload').attr('checked', 'checked');
		} else {
			jQuery('#download_filesize').removeAttr('readonly');
			jQuery('#calc_upload').removeAttr('checked');
		}
	});
});

function shortdesc_counter(textarea, counterID, maxLen) {
cnt = document.getElementById(counterID);
if (textarea.value.length >= maxLen)
{
textarea.value = textarea.value.substring(0,maxLen);
}
cnt.innerHTML = maxLen - textarea.value.length;
}";
echo "/* ]]>*/\n";
echo "</script>\n";

require_once THEMES."templates/footer.php";
?>