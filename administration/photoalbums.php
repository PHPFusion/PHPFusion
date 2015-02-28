<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: photoalbums.php
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
require_once "../maincore.php";

if (!checkrights("PH") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."photo_functions_include.php";
require_once INCLUDES."bbcode_include.php";
include LOCALE.LOCALESET."admin/photoalbums.php";
/**
 * New Fusion Gallery
 */


/**
 * Step 1 - Initialize the Class
 * The below statement binds the whole AdminUI engine into 1 single string, in this example (the `$eshop_gallery`)
 */
$eshop_gallery = new PHPFusion\Gallery\AdminUI();

/**
 * Step 2 - Set your upload rules settings.
 */
$eshop_gallery->setUploadSettings(
	array(
		'thumbnail_folder'=>'thumbs',
		'thumbnail' => 1,
		'thumbnail_w' =>  fusion_get_settings('thumb_w'),
		'thumbnail_h' =>  fusion_get_settings('thumb_h'),
		'thumbnail_suffix' =>'_t1',
		'thumbnail2'=>1,
		'thumbnail2_w' 	=>  fusion_get_settings('photo_w'),
		'thumbnail2_h' 	=>  fusion_get_settings('photo_h'),
		'thumbnail2_suffix' => '_t2',
		'delete_original' => 1,
		'max_width'		=>	fusion_get_settings('photo_max_w'),
		'max_height'	=>	fusion_get_settings('photo_max_h'),
		'max_byte'		=>	fusion_get_settings('photo_max_b'),
		'multiple' => 0,
	)
);

/**
 * Step 3 - Setup System Variables
 * a. Set up your Image Upload Path in the System (Relative to BASEDIR)
 * b. Set up your PHOTO_ALBUM database table
 * c. Set up your PHOTO database table
 * d. set up photo comments - true or false
 * e. set up photo ratings - true or false
 */
$eshop_gallery->setImageUploadDir(IMAGES."photoalbum");
$eshop_gallery->setPhotoCatDb(DB_PHOTO_ALBUMS);
$eshop_gallery->setPhotoDb(DB_PHOTOS);
$eshop_gallery->setGalleryRights('PH');
$eshop_gallery->setEnableComments(true);
$eshop_gallery->setEnableRatings(true);
$eshop_gallery->setAllowComments('comments_enabled');
$eshop_gallery->setAllowRatings('ratings_enabled');

$eshop_gallery->boot();

/*
if (function_exists('gd_info')) {
	define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
	if (isset($_GET['action']) && $_GET['action'] == "refresh") {
		$i = 1;
		$k = 1;
		$result = dbquery("SELECT album_id FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."'" : "")." ORDER BY album_order");
		while ($data = dbarray($result)) {
			$result2 = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order='$i' ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$data['album_id']."'");
			$result2 = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE album_id='".$data['album_id']."' ORDER BY photo_order");
			while ($data2 = dbarray($result2)) {
				$result3 = dbquery("UPDATE ".DB_PHOTOS." SET photo_order='$k' WHERE photo_id='".$data2['photo_id']."'");
				$k++;
			}
			$i++;
			$k = 1;
		}
		redirect(FUSION_SELF.$aidlink);
	}
	if (isset($_GET['status']) && !isset($message)) {
		if ($_GET['status'] == "sn") {
			$message = $locale['410'];
		} elseif ($_GET['status'] == "su") {
			$message = $locale['411'];
		} elseif ($_GET['status'] == "delt") {
			$message = $locale['412'];
		} elseif ($_GET['status'] == "dely") {
			$message = $locale['413'];
		} elseif ($_GET['status'] == "deln") {
			$message = $locale['419'];
		}
		if ($message) {
			echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
		}
	}
	if (isset($_POST['cancel'])) {
		redirect(FUSION_SELF.$aidlink);
	} elseif ((isset($_GET['action']) && $_GET['action'] == "deletethumb") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		$data = dbarray(dbquery("SELECT album_thumb,album_order FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'"));
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_thumb='' WHERE album_id='".$_GET['album_id']."'");
		@unlink(PHOTOS.$data['album_thumb']);
		redirect(FUSION_SELF.$aidlink."&status=delt&album_id=".$_GET['album_id']);
	}
	elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		if (dbcount("(photo_id)", DB_PHOTOS, "album_id='".$_GET['album_id']."'")) {
			if (!isset($_POST['confirm_password'])) {
				opentable($locale['430']);
				echo "<div style='text-align:center'>\n";
				echo openform('aform', 'aform', 'post', FUSION_SELF.$aidlink."&amp;action=delete&amp;album_id=".$_GET['album_id'], array('downtime' => 1, 'notice' => 0));
				echo form_text($locale['431'], 'admin_passwd', 'admin_passwd', '', array('password' => 1, 'class' => 'm-b-10'));
				echo form_button($locale['432'], 'confirm_password', 'confirm_password', $locale['432'], array('class' => 'btn-primary m-r-10'));
				echo form_button($locale['433'], 'cancel', 'cancel', $locale['433'], array('class' => 'btn-primary m-r-10'));
				echo "</form>\n</div>\n";
				closetable();
				require_once THEMES."templates/footer.php";
				exit;
			} else {
				if (check_admin_pass(isset($_POST['admin_passwd']) ? stripinput($_POST['admin_passwd']) : "")) {
					if (dbcount("(album_id)", DB_PHOTOS, "album_id='".$_GET['album_id']."'")) {
						$result = dbquery("SELECT album_id, photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."'");
						while ($data = dbarray($result)) {
							if (!SAFEMODE) {
								@unlink(PHOTOS."album_".$data['album_id']."/".$data['photo_filename']);
								if ($data['photo_thumb1']) {
									@unlink(PHOTOS."album_".$data['album_id']."/".$data['photo_thumb1']);
								}
								if ($data['photo_thumb2']) {
									@unlink(PHOTOS."album_".$data['album_id']."/".$data['photo_thumb2']);
								}
							} else {
								@unlink(PHOTOS.$data['photo_filename']);
								if ($data['photo_thumb1']) {
									@unlink(PHOTOS.$data['photo_thumb1']);
								}
								if ($data['photo_thumb2']) {
									@unlink(PHOTOS.$data['photo_thumb2']);
								}
							}
						}
						$result = dbquery("DELETE FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."'");
					}
					$data = dbarray(dbquery("SELECT album_thumb,album_order,album_id FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$_GET['album_id']."'"));
					$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order-1) ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_order>'".$data['album_order']."'");
					$result = dbquery("DELETE FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'");
					if ($data['album_thumb']) {
						@unlink(PHOTOS.$data['album_thumb']);
					}
					if (!SAFEMODE) {
						unlink(PHOTOS."album_".$data['album_id']."/index.php");
						rmdir(PHOTOS."album_".$data['album_id']);
					}
					redirect(FUSION_SELF.$aidlink."&status=dely");
				} else {
					redirect(FUSION_SELF.$aidlink."&status=deln");
				}
			}
		} else {
			$data = dbarray(dbquery("SELECT album_thumb,album_order FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$_GET['album_id']."'"));
			$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order-1) ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_order>'".$data['album_order']."'");
			$result = dbquery("DELETE FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'");
			if ($data['album_thumb']) {
				@unlink(PHOTOS.$data['album_thumb']);
			}
			if (!SAFEMODE) {
				@unlink(PHOTOS."album_".$_GET['album_id']."/index.php");
				rmdir(PHOTOS."album_".$_GET['album_id']);
			}
			redirect(FUSION_SELF.$aidlink."&status=dely");
		}
	}
	elseif ((isset($_GET['action']) && $_GET['action'] == "mup") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		$data = dbarray(dbquery("SELECT album_id FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_order='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=album_order+1 ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$data['album_id']."'");
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=album_order-1 ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$_GET['album_id']."'");
		$rowstart = $_GET['order'] > $settings['thumbs_per_page'] ? ((ceil($_GET['order']/$settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
		redirect(FUSION_SELF.$aidlink."&rowstart=$rowstart");
	}
	elseif ((isset($_GET['action']) && $_GET['action'] == "mdown") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		$data = dbarray(dbquery("SELECT album_id FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_order='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=album_order-1 ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$data['album_id']."'");
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=album_order+1 ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$_GET['album_id']."'");
		$rowstart = $_GET['order'] > $settings['thumbs_per_page'] ? ((ceil($_GET['order']/$settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
		redirect(FUSION_SELF.$aidlink."&rowstart=$rowstart");
	}
	elseif (isset($_POST['save_album'])) {
		$error = "";
		$album_title = form_sanitizer($_POST['album_title'], '', 'album_title');
		$album_description = form_sanitizer($_POST['album_description'], '', 'album_description');
		$album_language = form_sanitizer($_POST['album_language'], '', 'album_language');
		$album_access = isnum($_POST['album_access']) ? $_POST['album_access'] : "0";
		$album_order = isnum($_POST['album_order']) ? $_POST['album_order'] : "";
		if (!SAFEMODE && (!isset($_GET['action']) || $_GET['action'] != "edit")) {
			$result = dbarray(dbquery("SHOW TABLE STATUS LIKE '".DB_PHOTO_ALBUMS."'"));
			$album_id = $result['Auto_increment'];
			@mkdir(PHOTOS."album_".$album_id, 0777);
			@copy(IMAGES."index.php", PHOTOS."album_".$album_id."/index.php");
		}
		if (isset($_FILES) && count($_FILES) && is_uploaded_file($_FILES['album_pic_file']['tmp_name'])) {
			$album_types = array(".gif", ".jpg", ".jpeg", ".png");
			$album_pic = $_FILES['album_pic_file'];
			$album_name = stripfilename($album_pic['name']);
			$album_name = stripfilename(str_replace(" ", "_", strtolower(substr($album_pic['name'], 0, strrpos($album_pic['name'], ".")))));
			$album_ext = strtolower(strrchr($album_pic['name'], "."));
			if (!preg_match("/^[-0-9A-Z_\.\[\]\s]+$/i", $album_name)) {
				$error = 1;
			} elseif ($album_pic['size'] > $settings['photo_max_b']) {
				$error = 2;
			} elseif (!in_array($album_ext, $album_types)) {
				$error = 3;
			} else {
				// @unlink(PHOTOS."temp".$album_ext);
				move_uploaded_file($album_pic['tmp_name'], PHOTOS."temp".$album_ext);
				chmod(PHOTOS."temp".$album_ext, 0644);
				$imagefile = @getimagesize(PHOTOS."temp".$album_ext);
				if ($imagefile[0] > $settings['photo_max_w'] || $imagefile[1] > $settings['photo_max_h']) {
					$error = 4;
					@unlink(PHOTOS."temp".$album_ext);
				} else {
					$album_thumb = image_exists(PHOTOS, $album_name.$album_ext);
					createthumbnail($imagefile[2], PHOTOS."temp".$album_ext, PHOTOS.$album_thumb, $settings['thumb_w'], $settings['thumb_h']);
					@unlink(PHOTOS."temp".$album_ext);
				}
			}
		}
		if (!$error && !defined('FUSION_NULL')) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
				$old_album_order = dbresult(dbquery("SELECT album_order FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$_GET['album_id']."'"), 0);
				if ($album_order > $old_album_order) {
					$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order-1) ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_order>'$old_album_order' AND album_order<='$album_order'");
				} elseif ($album_order < $old_album_order) {
					$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order+1) ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_order<'$old_album_order' AND album_order>='$album_order'");
				}
				$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_title='$album_title', album_description='$album_description',".(isset($album_thumb) ? " album_thumb='$album_thumb'," : "")." album_user='".$userdata['user_id']."', album_access='$album_access', album_order='$album_order', album_language='$album_language' WHERE album_id='".$_GET['album_id']."'");
				$rowstart = $album_order > $settings['thumbs_per_page'] ? ((ceil($album_order/$settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
				redirect(FUSION_SELF.$aidlink."&status=su&rowstart=$rowstart");
			} else {
				if (!$album_order) {
					$album_order = dbresult(dbquery("SELECT MAX(album_order) FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."'" : "").""), 0)+1;
				}
				$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order+1) ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_order>='$album_order'");
				$result = dbquery("INSERT INTO ".DB_PHOTO_ALBUMS." (album_title, album_description, album_thumb, album_user, album_access, album_order, album_datestamp, album_language) VALUES ('$album_title', '$album_description', '".(isset($album_thumb) ? $album_thumb : "")."', '".$userdata['user_id']."', '$album_access', '$album_order', '".time()."', '$album_language')");
				$rowstart = $album_order > $settings['thumbs_per_page'] ? ((ceil($album_order/$settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
				redirect(FUSION_SELF.$aidlink."&status=sn&rowstart=$rowstart");
			}
		} else {
			if ($error == 1) {
				$message = $locale['415']."</span>";
			} elseif ($error == 2) {
				$message = sprintf($locale['416'], parsebytesize($settings['photo_max_b']))."</span>";
			} elseif ($error == 3) {
				$message = $locale['417']."</span>";
			} elseif ($error == 4) {
				$message = sprintf($locale['418'], $settings['photo_max_w'], $settings['photo_max_h'])."</span>";
			}
			$defender->stop();
			$defender->addNotice($message);
		}
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		$result = dbquery("SELECT album_title, album_description, album_thumb, album_access, album_order, album_language FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$_GET['album_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$album_title = $data['album_title'];
			$album_description = $data['album_description'];
			$album_language = $data['album_language'];
			$album_thumb = $data['album_thumb'];
			$album_access = $data['album_access'];
			$album_order = $data['album_order'];
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;album_id=".$_GET['album_id'];
			opentable($locale['401']);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$album_id = "";
		$album_title = "";
		$album_description = "";
		$album_language = LANGUAGE;
		$album_thumb = "";
		$album_access = "";
		$album_order = "";
		$formaction = FUSION_SELF.$aidlink;
		opentable($locale['400']);
	}
	$access_opts = array();
	$user_groups = getusergroups();
	while (list($key, $user_group) = each($user_groups)) {
		$access_opts[$user_group['0']] = $user_group['1'];
	}
	echo "<div class='panel panel-default'><div class='panel-body'>\n";
	echo openform('input_form', 'input_form', 'post', $formaction, array('downtime' => 1, 'enctype' => '1'));
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && ($album_thumb && file_exists(PHOTOS.$album_thumb))) {
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo "<div class='panel panel-default'>\n";
		echo "<img class='img-responsive' src='".PHOTOS.$album_thumb."' alt='album_thumb' />";
		echo "<div class='panel-body'>\n";
		echo "<a class='btn btn-block btn-primary button' href='".FUSION_SELF.$aidlink."&amp;action=deletethumb&amp;album_id=".$_GET['album_id']."'>".$locale['469']."</a>\n";
		echo "</div>\n</div>\n";
		echo "</div>\n<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
	} else {
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
	}
	echo form_text($locale['440'], 'album_title', 'album_title', $album_title, array('max_length' => 100, 'required' => 1, 'error_text' => $locale['409']));
	if (multilang_table("PG")) {
		echo form_select($locale['global_ML100'], 'album_language', 'album_language', $language_opts, $album_language, array('placeholder' => 1));
	} else {
		echo form_hidden('', 'album_language', 'album_language', $album_language);
	}
	echo form_textarea($locale['441'], 'album_description', 'album_description', $album_description, array('bbcodes' => 1));
	echo form_select($locale['442'], 'album_access', 'album_access', $access_opts, $album_access, array('placeholder' => 1, 'class' => 'pull-left m-r-10'));
	echo form_text($locale['443'], 'album_order', 'album_order', $album_order, array('number' => 1, 'width' => '100px'));
	if (!isset($_GET['action'])) {
		echo form_fileinput($locale['444'], 'album_pic_file', 'album_pic_file', IMAGES."photoalbum/", '', array('image'=>1, 'thumbnail_path'=>1, 'thumbnail_db'=>DB_PHOTO_ALBUMS));
	}
	echo form_button($locale['445'], 'save_album', 'save_album', $locale['445'], array('class' => 'btn-primary m-t-10'));
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo form_button($locale['433'], 'cancel', 'cancel', $locale['433'], array('class' => 'm-l-10 btn-primary m-t-10'));
	}
	echo "</div>\n</div>\n";
	echo closeform();
	echo "</div>\n</div>\n";
	closetable();

	opentable($locale['402']);
	$rows = dbcount("(album_id)", "".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."'" : "")."");
	if ($rows) {
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
			$_GET['rowstart'] = 0;
		}
		$result = dbquery("SELECT ta.album_id, ta.album_title, ta.album_thumb, ta.album_access, ta.album_order, ta.album_datestamp, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_PHOTO_ALBUMS." ta
			LEFT JOIN ".DB_USERS." tu ON ta.album_user=tu.user_id
			".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."'" : "")."
			ORDER BY album_order LIMIT ".$_GET['rowstart'].",".$settings['thumbs_per_page']);
		$counter = 0;
		$k = ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart']+1);
		echo "<div class='row'>\n";
		if ($rows > $settings['thumbs_per_page']) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, FUSION_SELF.$aidlink."&amp;")."\n</div>\n";
		}
		while ($data = dbarray($result)) {
			$up = "";
			$down = "";
			if ($rows != 1) {
				$orderu = $data['album_order']-1;
				$orderd = $data['album_order']+1;
				if ($k == 1) {
					$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;action=mdown&amp;order=$orderd&amp;album_id=".$data['album_id']."'><img src='".get_image("right")."' alt='".$locale['467']."' title='".$locale['468']."' style='border:0px;vertical-align:middle' /></a>\n";
				} elseif ($k < $rows) {
					$up = "<a href='".FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;action=mup&amp;order=$orderu&amp;album_id=".$data['album_id']."'><img src='".get_image("left")."' alt='".$locale['467']."' title='".$locale['466']."' style='border:0px;vertical-align:middle' /></a> &middot;\n";
					$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;action=mdown&amp;order=$orderd&amp;album_id=".$data['album_id']."'><img src='".get_image("right")."' alt='".$locale['467']."' title='".$locale['468']."' style='border:0px;vertical-align:middle' /></a>\n";
				} else {
					$up = "<a href='".FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;action=mup&amp;order=$orderu&amp;album_id=".$data['album_id']."'><img src='".get_image("left")."' alt='".$locale['467']."' title='".$locale['466']."' style='border:0px;vertical-align:middle' /></a> &middot;\n";
				}
			}
			if ($counter != 0 && ($counter%$settings['thumbs_per_row'] == 0)) {
				echo "</div>\n<div class='row'>\n";
			}
			echo "<div class='col-xs-12 col-sm-".floor(12/$settings['thumbs_per_row'])." col-md-".floor(12/$settings['thumbs_per_row'])." col-lg-".floor(12/$settings['thumbs_per_row'])."'>\n";
			echo "<div class='panel panel-default'>\n";
			echo "<div class='img-container text-center' style='overflow:hidden; max-height:100px;'>\n";
			if ($data['album_thumb'] && file_exists(PHOTOS.$data['album_thumb'])) {
				echo "<img class='img-center img-responsive' src='".PHOTOS.rawurlencode($data['album_thumb'])."' alt='".$locale['460']."' style='width:200px; border:0px' />";
			} else {
				echo "<img class='img-responsive' src='holder.js/200x100/text:".$locale['460']."/grey' alt='".$locale['460']."' style='border:0px' />";
			}
			echo "</div>\n";
			echo "<div class='panel-body'>\n";
			echo "<a href='photos.php".$aidlink."&amp;album_id=".$data['album_id']."'><strong>".$data['album_title']."</strong></a>\n";
			echo "<br /><br />\n".$locale['462'].showdate("shortdate", $data['album_datestamp'])."<br />\n";
			echo $locale['463'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br />\n";
			echo $locale['464'].getgroupname($data['album_access'])."<br />\n";
			echo $locale['465'].dbcount("(photo_id)", DB_PHOTOS, "album_id='".$data['album_id']."'")."</span><br />\n";
			echo "</div>\n<div class='panel-footer'>\n";
			echo $up;
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;album_id=".$data['album_id']."'>".$locale['468']."</a> &middot;\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;album_id=".$data['album_id']."' onclick=\"return PhotosWarning('".dbcount("(album_id)", DB_PHOTOS, "album_id='".$data['album_id']."'")."');\">".$locale['469']."</a> ".$down;
			echo "</div></div>\n";
			echo "</div>\n"; // end col
			$counter++;
			$k++;
		}
		echo "</div>\n<div>\n";
		echo "<a class='m-t-20 btn btn-block btn-primary' href='".FUSION_SELF.$aidlink."&amp;action=refresh'>".$locale['470']."</a>\n";
		if ($rows > $settings['thumbs_per_page']) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, FUSION_SELF.$aidlink."&amp;")."\n</div>\n";
		}
	} else {
		echo "<div style='text-align:center'>".$locale['471']."</div>\n";
	}
	echo "<script type='text/javascript'>\n"."function PhotosWarning(value) {\n";
	echo "return confirm ('".$locale['500']."');\n}\n</script>";
	closetable();
} else {
	opentable($locale['403']);
	echo "<div id='close-message'><div class='admin-message alert alert-warning m-t-20'>".$locale['420']."</div></div>\n";
	closetable();
}
*/
require_once THEMES."templates/footer.php";