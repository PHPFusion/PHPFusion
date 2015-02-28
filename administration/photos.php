<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: photos.php
| Author: Nick Jones (Digitanium)
| Co-Author: Robert Gaudyn (Wooya)
+--------------------------------------------------------+
| Mass-Upload by
| Author: MarcusG
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
include LOCALE.LOCALESET."admin/photos.php";

if (!isset($_GET['album_id']) || !isnum($_GET['album_id'])) { redirect("photoalbums.php".$aidlink); }

if (function_exists('gd_info')) {

	define("SAFEMODE", @ini_get("safe_mode") ? true : false);
	define("PHOTODIR", PHOTOS.(!SAFEMODE ? "album_".$_GET['album_id']."/" : ""));

	if (isset($_GET['status']) && !isset($message)) {
		if ($_GET['status'] == "sn") {
			$message = $locale['410'];
		} elseif ($_GET['status'] == "su") {
			$message = $locale['411'];
		} elseif ($_GET['status'] == "se") {
			$message = $locale['414']."<br />\n<span class='small'>";
			if ($_GET['error'] == 1) { $message .= $locale['415']."</span>"; }
			elseif ($_GET['error'] == 2) { $message .= sprintf($locale['416'], parsebytesize($settings['photo_max_b']))."</span>"; }
			elseif ($_GET['error'] == 3) { $message .= $locale['417']."</span>"; }
			elseif ($_GET['error'] == 4) { $message .= sprintf($locale['418'], $settings['photo_max_w'], $settings['photo_max_h'])."</span>"; }
			elseif ($_GET['error'] == 5) { $message .= $locale['421']."</span>"; }
			elseif ($_GET['error'] == 6) { $message .= $locale['422']."</span>"; }
		} elseif ($_GET['status'] == "delt") {
			$message = $locale['412'];
		} elseif ($_GET['status'] == "del") {
			$message = $locale['413'];
		} elseif ($_GET['status'] == "mov") {
			$message = $locale['419'];
		}
		if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
	}

	if (isset($_POST['cancel'])) {
		redirect(FUSION_SELF.$aidlink."&album_id=".$_GET['album_id']);
	} elseif (isset($_POST['move_photo']) && (isset($_POST['move_album_id']) && isnum($_POST['move_album_id'])) && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) {
		$result = dbquery("SELECT MAX(photo_order)+1 as last_order FROM ".DB_PHOTOS." WHERE album_id='".$_POST['move_album_id']."' GROUP BY album_id");
		if (dbrows($result)) {
			$data = dbarray($result);
			$last_order = $data['last_order'];
		} else {
			$last_order = 1;
		}
		if (!SAFEMODE) {
			$result2 = dbquery("SELECT photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE photo_id='".$_GET['photo_id']."'");
			if (dbrows($result2)) {
				$data2 = dbarray($result2);
				$old_image = $data2['photo_filename'];
				$old_thumb1 = $data2['photo_thumb1'];
				$old_thumb2 = $data2['photo_thumb2'];
				$new_image = image_exists(PHOTOS."album_".$_POST['move_album_id']."/", $old_image);
				$file_name = explode(".", $new_image);
				$new_thumb1 = $file_name[0]."_t1.".$file_name[1];
				$new_thumb2 = $file_name[0]."_t2.".$file_name[1];
				unset($file_name);
				if ($data2['photo_filename']) { @rename (PHOTODIR.$old_image, PHOTOS."album_".$_POST['move_album_id']."/".$new_image); }
				if ($data2['photo_thumb1']) { @rename (PHOTODIR.$old_thumb1, PHOTOS."album_".$_POST['move_album_id']."/".$new_thumb1); }
				if ($data2['photo_thumb2']) { @rename (PHOTODIR.$old_thumb2, PHOTOS."album_".$_POST['move_album_id']."/".$new_thumb2); }
				if ($old_image != $new_image) {
					$result3 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."', photo_filename='".$new_image."', photo_thumb1='".$new_thumb1."', photo_thumb2='".$new_thumb2."' WHERE photo_id='".$_GET['photo_id']."'");
				} else {
					$result3 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."' WHERE photo_id='".$_GET['photo_id']."'");
				}
			} else {
				redirect(FUSION_SELF.$aidlink."&album_id=".$_GET['album_id']);
			}
		} else {
			$result3 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."' WHERE photo_id='".$_GET['photo_id']."'");
		}
		$k = 1;
		$result2 = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."' ORDER BY photo_order");
		if (dbrows($result2)) {
			while ($data2 = dbarray($result2)) {
				$result3 = dbquery("UPDATE ".DB_PHOTOS." SET photo_order='".$k."' WHERE photo_id='".$data2['photo_id']."'");
				$k++;
			}
		}
		redirect (FUSION_SELF.$aidlink."&album_id=".$_POST['move_album_id']."&amp;status=mov");
	} elseif (isset($_POST['move_sel_photos']) && (isset($_POST['move_album_id']) && isnum($_POST['move_album_id']))) {
		$result = dbquery("SELECT MAX(photo_order)+1 as last_order FROM ".DB_PHOTOS." WHERE album_id='".$_POST['move_album_id']."' GROUP BY album_id");
		if (dbrows($result)) {
			$data = dbarray($result);
			$last_order = $data['last_order'];
		} else {
			$last_order = 1;
		}
		$check_count = 0; $photo_ids = "";
		if (is_array($_POST['sel_photo']) && count($_POST['sel_photo']) > 0) {
			foreach ($_POST['sel_photo'] as $this_photo) {
				if (isnum($this_photo)) { $photo_ids .= ($photo_ids ? "," : "").$this_photo; }
				$check_count++;
			}
		}
		if ($check_count > 0) {
			$result = dbquery("SELECT photo_id, photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."' AND photo_id IN (".$photo_ids.") ORDER BY photo_order");
			$rows = dbrows($result);
			if ($rows) {
				$i = 0;
				while ($data = dbarray($result)) {
					if (!SAFEMODE) {
						$old_image = $data['photo_filename'];
						$old_thumb1 = $data['photo_thumb1'];
						$old_thumb2 = $data['photo_thumb2'];
						$new_image = image_exists(PHOTOS."album_".$_POST['move_album_id']."/", $old_image);
						$file_name = explode(".", $new_image);
						$new_thumb1 = $file_name[0]."_t1.".$file_name[1];
						$new_thumb2 = $file_name[0]."_t2.".$file_name[1];
						unset($file_name);
						if ($data['photo_filename']) { @rename (PHOTODIR.$old_image, PHOTOS."album_".$_POST['move_album_id']."/".$new_image); }
						if ($data['photo_thumb1']) { @rename (PHOTODIR.$old_thumb1, PHOTOS."album_".$_POST['move_album_id']."/".$new_thumb1); }
						if ($data['photo_thumb2']) { @rename (PHOTODIR.$old_thumb2, PHOTOS."album_".$_POST['move_album_id']."/".$new_thumb2); }
						if ($old_image != $new_image) {
							$result2 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."', photo_filename='".$new_image."', photo_thumb1='".$new_thumb1."', photo_thumb2='".$new_thumb2."' WHERE photo_id='".$data['photo_id']."'");
						} else {
							$result2 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."' WHERE photo_id='".$data['photo_id']."'");
						}
					} else {
						$result2 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."' WHERE photo_id='".$data['photo_id']."'");
					}
					$last_order++;
				}
				$k = 1;
				$result2 = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."' ORDER BY photo_order");
				if (dbrows($result2)) {
					while ($data2 = dbarray($result2)) {
						$result3 = dbquery("UPDATE ".DB_PHOTOS." SET photo_order='".$k."' WHERE photo_id='".$data2['photo_id']."'");
						$k++;
					}
				}
				redirect (FUSION_SELF.$aidlink."&album_id=".$_POST['move_album_id']."&amp;status=mov");
			} else {
				redirect (FUSION_SELF.$aidlink."&album_id=".$_GET['album_id']);
			}
		} else {
			redirect (FUSION_SELF.$aidlink."&album_id=".$_GET['album_id']);
		}
	} elseif (isset($_POST['move_all_photos']) && (isset($_POST['move_album_id']) && isnum($_POST['move_album_id']))) {
		$result = dbquery("SELECT MAX(photo_order)+1 as last_order FROM ".DB_PHOTOS." WHERE album_id='".$_POST['move_album_id']."' GROUP BY album_id");
		if (dbrows($result)) {
			$data = dbarray($result);
			$last_order = $data['last_order'];
		} else {
			$last_order = 1;
		}
		$result = dbquery("SELECT photo_id, photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."' ORDER BY photo_order");
		$rows = dbrows($result);
		if ($rows) {
			while ($data = dbarray($result)) {
				if (!SAFEMODE) {
					$old_image = $data['photo_filename'];
					$old_thumb1 = $data['photo_thumb1'];
					$old_thumb2 = $data['photo_thumb2'];
					$new_image = image_exists(PHOTOS."album_".$_POST['move_album_id']."/", $old_image);
					$file_name = explode(".", $new_image);
					$new_thumb1 = $file_name[0]."_t1.".$file_name[1];
					$new_thumb2 = $file_name[0]."_t2.".$file_name[1];
					unset($file_name);
					if ($data['photo_filename']) @rename (PHOTODIR.$old_image, PHOTOS."album_".$_POST['move_album_id']."/".$new_image);
					if ($data['photo_thumb1']) @rename (PHOTODIR.$old_thumb1, PHOTOS."album_".$_POST['move_album_id']."/".$new_thumb1);
					if ($data['photo_thumb2']) @rename (PHOTODIR.$old_thumb2, PHOTOS."album_".$_POST['move_album_id']."/".$new_thumb2);
					if ($old_image != $new_image) {
						$result2 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."', photo_filename='".$new_image."', photo_thumb1='".$new_thumb1."', photo_thumb2='".$new_thumb2."' WHERE photo_id='".$data['photo_id']."'");
					} else {
						$result2 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."' WHERE photo_id='".$data['photo_id']."'");
					}
				} else {
					$result2 = dbquery("UPDATE ".DB_PHOTOS." SET album_id='".$_POST['move_album_id']."', photo_order='".$last_order."' WHERE photo_id='".$data['photo_id']."'");
				}
				$last_order++;
			}
			redirect (FUSION_SELF.$aidlink."&album_id=".$_POST['move_album_id']."&amp;status=mov");
		} else {
			redirect (FUSION_SELF.$aidlink."&album_id=".$_GET['album_id']);
		}
	} elseif ((isset($_GET['action']) && $_GET['action'] == "deletepic") && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) {
		$data = dbarray(dbquery("SELECT photo_filename,photo_thumb1,photo_thumb2 FROM ".DB_PHOTOS." WHERE photo_id='".$_GET['photo_id']."'"));
		$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_filename='', photo_thumb1='', photo_thumb2='' WHERE photo_id='".$_GET['photo_id']."'");
		@unlink(PHOTODIR.$data['photo_filename']);
		@unlink(PHOTODIR.$data['photo_thumb1']);
		if ($data['photo_thumb2']) { @unlink(PHOTODIR.$data['photo_thumb2']); }
		redirect(FUSION_SELF.$aidlink."&status=delt&album_id=".$_GET['album_id']."");
	} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) {
		$data = dbarray(dbquery("SELECT album_id,photo_filename,photo_thumb1,photo_thumb2,photo_order FROM ".DB_PHOTOS." WHERE photo_id='".$_GET['photo_id']."'"));
		$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=(photo_order-1) WHERE photo_order>'".$data['photo_order']."' AND album_id='".$_GET['album_id']."'");
		$result = dbquery("DELETE FROM ".DB_PHOTOS." WHERE photo_id='".$_GET['photo_id']."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_item_id='".$_GET['photo_id']."' and comment_type='P'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$_GET['photo_id']."' and rating_type='P'");
		if ($data['photo_filename']) { @unlink(PHOTODIR.$data['photo_filename']); }
		if ($data['photo_thumb1']) { @unlink(PHOTODIR.$data['photo_thumb1']); }
		if ($data['photo_thumb2']) { @unlink(PHOTODIR.$data['photo_thumb2']); }
		redirect(FUSION_SELF.$aidlink."&status=del&album_id=".$_GET['album_id']."");
	} elseif ((isset($_GET['action']) && $_GET['action']=="mup") && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) {
		$data = dbarray(dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."' AND photo_order='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=photo_order+1 WHERE photo_id='".$data['photo_id']."'");
		$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=photo_order-1 WHERE photo_id='".$_GET['photo_id']."'");
		$rowstart = $_GET['order'] > $settings['thumbs_per_page'] ? ((ceil($_GET['order'] / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
		redirect(FUSION_SELF.$aidlink."&album_id=".$_GET['album_id']."&rowstart=$rowstart");
	} elseif ((isset($_GET['action']) && $_GET['action']=="mdown") && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) {
		$data = dbarray(dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."' AND photo_order='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=photo_order-1 WHERE photo_id='".$data['photo_id']."'");
		$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=photo_order+1 WHERE photo_id='".$_GET['photo_id']."'");
		$rowstart = $_GET['order'] > $settings['thumbs_per_page'] ? ((ceil($_GET['order'] / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
		redirect(FUSION_SELF.$aidlink."&album_id=".$_GET['album_id']."&rowstart=$rowstart");

		//Photo-Mass Upload start
        } elseif (isset($_POST['btn_upload_dir']) || isset($_GET['btn_upload_dir'])) {
		$error = "";
		if (isset($_POST['upload_dir']) || isset($_GET['upload_dir'])) {
			$upload_dir = BASEDIR."ftp_upload/" . (isset($_GET['upload_dir']) ? $_GET['upload_dir'] : $_POST['upload_dir']) . "/";
			if (!is_writable($upload_dir)) {redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=6&amp;album_id=".$_GET['album_id']);}
			$files1 = makefilelist($upload_dir, ".|..|index.php", true, "files", "php|js");
			if (empty($files1)) {redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=5&amp;album_id=".$_GET['album_id']);}
			$files = array_reverse($files1);
			$photo_comments = (isset($_GET['photo_comments']) ? $_GET['photo_comments'] : (isset($_POST['photo_comments']) ? "1" : "0"));
			$photo_ratings = (isset($_GET['photo_ratings']) ? $_GET['photo_ratings'] : (isset($_POST['photo_ratings']) ? "1" : "0"));
			$photo_file = ""; $photo_thumb1 = ""; $photo_thumb2 = ""; $photo_order = "";
			$photo_types = array(".gif",".jpg",".jpeg",".png");
			$i = 0;
			foreach ($files as $image) {
				($i == 20 ? redirect(FUSION_SELF.$aidlink."&amp;btn_upload_dir=true&amp;album_id=".$_GET['album_id']."&amp;upload_dir=".(isset($_GET['upload_dir']) ? $_GET['upload_dir'] : $_POST['upload_dir'])."&amp;photo_comments=".$photo_comments."&amp;photo_ratings=".$photo_ratings) : "");
				$photo_pic = $image;
				$photo_size = filesize($upload_dir.$photo_pic);
				$photo_name = stripfilename(str_replace(" ", "_", strtolower(substr($photo_pic, 0, strrpos($photo_pic, ".")))));
				$photo_name = substr($photo_name, 0, 30);
				$photo_ext = strtolower(strrchr($photo_pic,"."));
				$photo_dest = PHOTODIR;
				if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $photo_name)) {
				$error = 1;
				} elseif ($photo_size > $settings['photo_max_b']){
					$error = 2;
				} elseif (!in_array($photo_ext, $photo_types)) {
					$error = 3;
				} else {
					$photo_file = image_exists($photo_dest, $photo_name.$photo_ext);
					if (isset($photo_pic) && copy($upload_dir.$photo_pic, $photo_dest.$photo_file)) {
						chmod($photo_dest.$photo_file, 0666);
						$imagefile = @getimagesize($photo_dest.$photo_file);
						if ($imagefile[0] > $settings['photo_max_w'] || $imagefile[1] > $settings['photo_max_h']) {
							$error = 4;
							unlink($photo_dest.$photo_file);
						} else {
							$photo_thumb1 = image_exists($photo_dest, $photo_name."_t1".$photo_ext);
							createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb1, $settings['thumb_w'], $settings['thumb_h']);
							if ($imagefile[0] > $settings['photo_w'] || $imagefile[1] > $settings['photo_h']) {
								$photo_thumb2 = image_exists($photo_dest, $photo_name."_t2".$photo_ext);
								createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb2, $settings['photo_w'], $settings['photo_h']);
							}
							@unlink($upload_dir.$photo_pic);
						}
					}
				}
				if (!$error) {
					$photo_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."'"), 0) + 1;
					$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=(photo_order+1) WHERE photo_order>='$photo_order' AND album_id='".$_GET['album_id']."'");
					$result = dbquery("INSERT INTO ".DB_PHOTOS." (album_id, photo_title, photo_description, photo_filename, photo_thumb1, photo_thumb2, photo_datestamp, photo_user, photo_views, photo_order, photo_allow_comments, photo_allow_ratings) VALUES ('".$_GET['album_id']."', '', '', '".$photo_file."', '".$photo_thumb1."', '".$photo_thumb2."', '".time()."', '".$userdata['user_id']."', '0', '".$photo_order."', '".$photo_comments."', '".$photo_ratings."')");
				}
				$i++;
			}

		} else {
			$error = 5;
		}
		if ($error) {
			redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=".$error."&amp;album_id=".$_GET['album_id']);
		}
		// make sure the folder is empty before deleting
		$files_to_delete = makefilelist($upload_dir, ".|..", true, "files");
		foreach($files_to_delete as $delete_file) {
			@unlink($upload_dir.$delete_file);
		}
		@rmdir($upload_dir);
		$rowstart = $photo_order > $settings['thumbs_per_page'] ? ((ceil($photo_order / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
		redirect(ADMIN."photos.php".$aidlink."&amp;status=sn&amp;album_id=".$_GET['album_id']."&amp;rowstart=$rowstart");

	} elseif (isset($_POST['btn_multi_upload']) || isset($_GET['btn_multi_upload'])) {
		$error = "";
		if (isset($_POST['multi_image']) || isset($_GET['multi_image'])) {
			$upload_dir = BASEDIR."ftp_upload/";
			if (!is_writable($upload_dir)) {redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=6&amp;album_id=".$_GET['album_id']);}
			$multi_image = (isset($_GET['multi_image']) ? explode("|", $_GET['multi_image']) : $_POST['multi_image']);
			$photo_comments = (isset($_GET['photo_comments']) ? $_GET['photo_comments'] : (isset($_POST['photo_comments']) ? "1" : "0"));
			$photo_ratings = (isset($_GET['photo_ratings']) ? $_GET['photo_ratings'] : (isset($_POST['photo_ratings']) ? "1" : "0"));
			$photo_file = ""; $photo_thumb1 = ""; $photo_thumb2 = ""; $photo_order = "";
			$photo_types = array(".gif",".jpg",".jpeg",".png");
			$i = 0;
			foreach ($multi_image as $image) {
				($i == 20 ? redirect(FUSION_SELF.$aidlink."&amp;btn_multi_upload=true&amp;album_id=".$_GET['album_id']."&amp;multi_image=".implode("|", $multi_image)."&amp;photo_comments=".$photo_comments."&amp;photo_ratings=".$photo_ratings) : "");
				$photo_pic = $image;
				$photo_size = filesize($upload_dir.$photo_pic);
				$photo_name = stripfilename(str_replace(" ", "_", strtolower(substr($photo_pic, 0, strrpos($photo_pic, ".")))));
				$photo_name = substr($photo_name, 0, 30);
				$photo_ext = strtolower(strrchr($photo_pic,"."));
				$photo_dest = PHOTODIR;
				if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $photo_name)) {
				$error = 1;
				} elseif ($photo_size > $settings['photo_max_b']){
					$error = 2;
				} elseif (!in_array($photo_ext, $photo_types)) {
					$error = 3;
				} else {
					$photo_file = image_exists($photo_dest, $photo_name.$photo_ext);
					if (copy($upload_dir.$photo_pic, $photo_dest.$photo_file)) {
						chmod($photo_dest.$photo_file, 0666);
						$imagefile = @getimagesize($photo_dest.$photo_file);
						if ($imagefile[0] > $settings['photo_max_w'] || $imagefile[1] > $settings['photo_max_h']) {
							$error = 4;
							unlink($photo_dest.$photo_file);
						} else {
							$photo_thumb1 = image_exists($photo_dest, $photo_name."_t1".$photo_ext);
							createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb1, $settings['thumb_w'], $settings['thumb_h']);
							if ($imagefile[0] > $settings['photo_w'] || $imagefile[1] > $settings['photo_h']) {
								$photo_thumb2 = image_exists($photo_dest, $photo_name."_t2".$photo_ext);
								createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb2, $settings['photo_w'], $settings['photo_h']);
							}
							@unlink($upload_dir.$photo_pic);
						}
					}

				}
				if (!$error) {
					$photo_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."'"), 0) + 1;
					$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=(photo_order+1) WHERE photo_order>='$photo_order' AND album_id='".$_GET['album_id']."'");
					$result = dbquery("INSERT INTO ".DB_PHOTOS." (album_id, photo_title, photo_description, photo_filename, photo_thumb1, photo_thumb2, photo_datestamp, photo_user, photo_views, photo_order, photo_allow_comments, photo_allow_ratings) VALUES ('".$_GET['album_id']."', '', '', '".$photo_file."', '".$photo_thumb1."', '".$photo_thumb2."', '".time()."', '".$userdata['user_id']."', '0', '".$photo_order."', '".$photo_comments."', '".$photo_ratings."')");
				}
				$i++;
				$x = array_search($image, $multi_image);
				unset($multi_image[$x]);
			}

		} else {
			$error = 5;
		}
		if ($error) {
			redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=".$error."&amp;album_id=".$_GET['album_id']);
		}

		$rowstart = $photo_order > $settings['thumbs_per_page'] ? ((ceil($photo_order / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
		redirect(ADMIN."photos.php".$aidlink."&amp;status=sn&amp;album_id=".$_GET['album_id']."&amp;rowstart=".$rowstart);


        //Photo-Mass Upload End

	} elseif (isset($_POST['save_photo'])) {
		$error="";
		$photo_title = stripinput($_POST['photo_title']);
		$photo_description = stripinput($_POST['photo_description']);
		$photo_order = isnum($_POST['photo_order']) ? $_POST['photo_order'] : "";
		$photo_comments = isset($_POST['photo_comments']) ? "1" : "0";
		$photo_ratings = isset($_POST['photo_ratings']) ? "1" : "0";
		$photo_file = ""; $photo_thumb1 = ""; $photo_thumb2 = "";
		if (!empty($_FILES['photo_pic_file']['name'])) {
			if (is_uploaded_file($_FILES['photo_pic_file']['tmp_name'])) {
				$photo_types = array(".gif",".jpg",".jpeg",".png");
				$photo_pic = $_FILES['photo_pic_file'];
				$photo_name = stripfilename(str_replace(" ", "_", strtolower(substr($photo_pic['name'], 0, strrpos($photo_pic['name'], ".")))));
				$photo_ext = strtolower(strrchr($photo_pic['name'],"."));
				$photo_dest = PHOTODIR;
				if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $photo_name)) {
					$error = 1;
				} elseif ($photo_pic['size'] > $settings['photo_max_b']){
					$error = 2;
				} elseif (!in_array($photo_ext, $photo_types)) {
					$error = 3;
				} else {
					$photo_file = image_exists($photo_dest, $photo_name.$photo_ext);
					move_uploaded_file($photo_pic['tmp_name'], $photo_dest.$photo_file);
					chmod($photo_dest.$photo_file, 0666);
					$imagefile = @getimagesize($photo_dest.$photo_file);
					if ($imagefile[0] > $settings['photo_max_w'] || $imagefile[1] > $settings['photo_max_h']) {
						$error = 4;
						unlink($photo_dest.$photo_file);
					} else {
						$photo_thumb1 = image_exists($photo_dest, $photo_name."_t1".$photo_ext);
						createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb1, $settings['thumb_w'], $settings['thumb_h']);
						if ($imagefile[0] > $settings['photo_w'] || $imagefile[1] > $settings['photo_h']) {
							$photo_thumb2 = image_exists($photo_dest, $photo_name."_t2".$photo_ext);
							createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb2, $settings['photo_w'], $settings['photo_h']);
						}
					}
				}
			}
		} else {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) {
				$error = "";
			} else {
				$error = 5;
			}
		}
		if (!$error) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) {
				$old_photo_order = dbresult(dbquery("SELECT photo_order FROM ".DB_PHOTOS." WHERE photo_id='".$_GET['photo_id']."'"),0);
				if ($photo_order > $old_photo_order) {
					$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=(photo_order-1) WHERE photo_order>'$old_photo_order' AND photo_order<='$photo_order' AND album_id='".$_GET['album_id']."'");
				} elseif ($photo_order < $old_photo_order) {
					$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=(photo_order+1) WHERE photo_order<'$old_photo_order' AND photo_order>='$photo_order' AND album_id='".$_GET['album_id']."'");
				}
				$update_photos = $photo_file ? "photo_filename='$photo_file', photo_thumb1='$photo_thumb1', photo_thumb2='$photo_thumb2', " : "";
				$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_title='$photo_title', photo_description='$photo_description', ".$update_photos."photo_datestamp='".time()."', photo_order='$photo_order', photo_allow_comments='$photo_comments', photo_allow_ratings='$photo_ratings' WHERE photo_id='".$_GET['photo_id']."'");
				$rowstart = $photo_order > $settings['thumbs_per_page'] ? ((ceil($photo_order / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
				redirect(FUSION_SELF.$aidlink."&status=su&album_id=".$_GET['album_id']."&rowstart=$rowstart");
			} else {
				if (!$photo_order) { $photo_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."'"), 0) + 1; }
				$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_order=(photo_order+1) WHERE photo_order>='$photo_order' AND album_id='".$_GET['album_id']."'");
				$result = dbquery("INSERT INTO ".DB_PHOTOS." (album_id, photo_title, photo_description, photo_filename, photo_thumb1, photo_thumb2, photo_datestamp, photo_user, photo_views, photo_order, photo_allow_comments, photo_allow_ratings) VALUES ('".$_GET['album_id']."', '$photo_title', '$photo_description', '$photo_file', '$photo_thumb1', '$photo_thumb2', '".time()."', '".$userdata['user_id']."', '0', '$photo_order', '$photo_comments', '$photo_ratings')");
				$rowstart = $photo_order > $settings['thumbs_per_page'] ? ((ceil($photo_order / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
				redirect(FUSION_SELF.$aidlink."&status=sn&album_id=".$_GET['album_id']."&rowstart=$rowstart");
			}
		}
		if ($error) {
			redirect(FUSION_SELF.$aidlink."&status=se&error=$error&album_id=".$_GET['album_id']);
		}
	}else{
		$data3 = dbarray(dbquery("SELECT album_title FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'"));
		$album_title = $data3['album_title'];
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) {
			$result = dbquery("SELECT photo_title, photo_description, photo_filename, photo_thumb1, photo_thumb2, photo_order, photo_allow_comments, photo_allow_ratings FROM ".DB_PHOTOS." WHERE photo_id='".$_GET['photo_id']."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				$photo_id = $_GET['photo_id'];
				$photo_title = $data['photo_title'];
				$photo_description = $data['photo_description'];
				$photo_filename = $data['photo_filename'];
				$photo_thumb1 = $data['photo_thumb1'];
				$photo_thumb2 = $data['photo_thumb2'];
				$photo_order = $data['photo_order'];
				$photo_comments = $data['photo_allow_comments'] == "1" ? " checked='checked'" : "";
				$photo_ratings = $data['photo_allow_ratings'] == "1" ? " checked='checked'" : "";
				$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;album_id=".$_GET['album_id']."&amp;photo_id=".$_GET['photo_id'];
				add_to_title($locale['global_200'].$locale['401'].$locale['global_201'].$photo_title);
				opentable($album_title.": ".$locale['401']." - ($photo_id - $photo_title)");
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		} else {
			$photo_title = "";
			$photo_description = "";
			$photo_filename = "";
			$photo_thumb1 = "";
			$photo_thumb2 = "";
			$photo_order = "";
			$photo_comments = " checked='checked'";
			$photo_ratings = " checked='checked'";
			$formaction = FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."";
			opentable($album_title.": ".$locale['400']);
		}
		if (!isset($_GET['action'])) {
		echo "<div class='tbl2' id='show_singleform' style='font-weight:bold;cursor:pointer;margin-bottom:2px;'>".$locale['493']."</div>";
		}
		echo "<div id='single_upload' class='image_upload' style='padding:15px 0;'>";
		echo "<form name='inputform' method='post' action='".$formaction."' enctype='multipart/form-data'>\n";
		echo "<table cellspacing='0' cellpadding='2' class='tbl-border center' style='width:500px;'>\n";
		if (isset($_GET['action']) && $_GET['action'] == "edit") {
			$result2 = dbquery("SELECT album_id, album_title FROM ".DB_PHOTO_ALBUMS." WHERE album_id!='".$_GET['album_id']."'");
			if (dbrows($result2)) {
				echo "<tr>\n<td colspan='2' class='tbl1' style='text-align:center'>\n";
				echo $locale['430'].": <select class='textbox' name='move_album_id'>\n";
				echo "<option value=''>-- ".$locale['473']." --</option>\n";
				while ($data2 = dbarray($result2)) {
					echo "<option value='".$data2['album_id']."'>".$data2['album_title']."</option>\n";
				}
				echo "</select>\n";
				echo "<input class='button' type='submit' name='move_photo' value='".$locale['431']."' />";
				echo "</td>\n</tr>\n";
			}
		}
		echo "<tr>\n<td class='tbl1'>".$locale['432']."</td>\n";
		echo "<td class='tbl1'><input type='text' name='photo_title' value='".$photo_title."' maxlength='100' class='textbox' style='width:330px;' /></td>\n";
		echo "</tr>\n";
		echo "<tr>\n<td class='tbl1' style='vertical-align:top;'>".$locale['433']."</td>\n";
		echo "<td class='tbl1'><textarea name='photo_description' cols='60' rows='5' class='textbox' style='width:330px;'>".$photo_description."</textarea><br />\n";
		echo display_bbcodes("300px", "photo_description", "inputform", "b|i|u|center|small|url|mail|img|quote")."</td>\n";
		echo "</tr>\n";
		echo "<tr>\n<td class='tbl1'>".$locale['434']."</td>\n";
		echo "<td class='tbl1'><input type='text' name='photo_order' value='".$photo_order."' maxlength='5' class='textbox' style='width:40px;' /></td>\n";
		echo "</tr>\n";
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && ($photo_thumb1 && file_exists(PHOTODIR.$photo_thumb1))) {
			echo "<tr>\n<td class='tbl1' style='vertical-align:top;'>".$locale['435']."</td>\n";
			echo "<td class='tbl1'><img src='".PHOTODIR.$photo_thumb1."' border='1' alt='".$photo_thumb1."' /></td>\n";
			echo "</tr>\n";
		}
		echo "<tr>\n<td class='tbl1' style='vertical-align:top;'>".$locale['436'];
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && ($photo_thumb2 && file_exists(PHOTODIR.$photo_thumb2))) {
			echo "<br /><br />\n<a class='small' href='".FUSION_SELF.$aidlink."&amp;action=deletepic&amp;album_id=".$_GET['album_id']."&amp;photo_id=".$_GET['photo_id']."'>".$locale['455']."</a></td>\n";
			echo "<td class='tbl1'><img src='".PHOTODIR.$photo_thumb2."' border='1' alt='".$photo_thumb2."' />";
		} elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && ($photo_filename && file_exists(PHOTODIR.$photo_filename))) {
			echo "<br /><br />\n<a class='small' href='".FUSION_SELF.$aidlink."&amp;action=deletepic&amp;album_id=".$_GET['album_id']."&amp;photo_id=".$_GET['photo_id']."'>".$locale['455']."</a></td>\n";
			echo "<td class='tbl1'><img src='".PHOTODIR.$photo_filename."' border='1' alt='".$photo_filename."' />";
		} else {
			echo "</td>\n<td class='tbl1'><input type='file' name='photo_pic_file' class='textbox' style='width:250px;' />\n";
		}
		echo "</td>\n</tr>\n";
		echo "<tr>\n<td class='tbl1' style='text-align:right;'></td>\n";
		echo "<td class='tbl1' style='text-align:left;'>\n";
		echo "<label><input type='checkbox' name='photo_comments' value='yes'".$photo_comments." /> ".$locale['437']."</label>";
		if ($settings['comments_enabled'] == "0") {
			echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
		}
		echo "<br />\n";
		echo "<label><input type='checkbox' name='photo_ratings' value='yes'".$photo_ratings." /> ".$locale['438']."</label>\n";
		if ($settings['ratings_enabled'] == "0") {
			echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
		}
		echo "</td></tr>\n";
		if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
			$sys = "";
			if ($settings['comments_enabled'] == "0" &&  $settings['ratings_enabled'] == "0") {
				$sys = $locale['523'];
			} elseif ($settings['comments_enabled'] == "0") {
				$sys = $locale['521'];
			} else {
				$sys = $locale['522'];
			}
			echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
			echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['520'], $sys);
			echo "</td>\n</tr>";
		}
		echo "<tr>\n<td class='tbl1' colspan='2' style='text-align:center;'><br />";
		echo "<input type='submit' name='save_photo' value='".$locale['439']."' class='button' />\n";
		if (isset($_GET['action']) && $_GET['action'] == "edit") {
			echo "<input type='submit' name='cancel' value='".$locale['440']."' class='button' />\n";
		}
		echo "</td></tr>\n</table></form>\n";
		echo "</div>";
		//Photo-Mass Upload start
		if (!isset($_GET['action'])) {
			echo "<div class='tbl2' id='show_folderform' style='font-weight:bold;cursor:pointer;margin-bottom:2px;'>".$locale['494']."</div>";
			echo "<div id='folder_upload' class='image_upload' style='padding:15px 0;'>";
			$upload_dir = BASEDIR."ftp_upload/";
			$can_upload = (is_writable($upload_dir) ? true : false);
			$gallery_dir = makefilelist($upload_dir,  ".|..|index.php", true, "folders");
			$folder_opts = makefileopts($gallery_dir);
			if ($can_upload == true) {
				echo "<form name='folderuploadform' method='post' action='".FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."' enctype='multipart/form-data'>\n";
				echo "<table class='tbl-border center' cellpadding='2' cellspacing='0' style='width:500px;'>\n";
				echo ($folder_opts != "" ? "<tr>\n<td class='tbl1' colspan='2' style='text-align:center;'>".$locale['496']."</td>\n</tr>\n" : "");
				echo "<tr>\n<td class='tbl1' colspan='2' style='text-align:left;'>";
				echo sprintf($locale['497'], $upload_dir)."<br />";
				echo sprintf($locale['498'], $album_title)."<br />";
				echo "</td>\n</tr>\n";
				if ($folder_opts != "") {
					echo "<tr><td class='tbl1' style='text-align:right;vertical-align:top;width:20%;'>".$locale['499']."</td>\n";
					echo "<td class='tbl1'><select name='upload_dir' size='5' class='textbox' style='width:250px;' >".$folder_opts."</select></td>\n";
					echo "</tr>\n<tr>\n";
					echo "<td class='tbl1' style='text-align:right;'></td>\n";
					echo "<td class='tbl1' style='text-align:left;'>";
					echo "<label><input type='checkbox' name='photo_comments' value='yes' checked='checked' /> ".$locale['437']."</label>";
					if ($settings['comments_enabled'] == "0") {
						echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
					}
					echo "<br />\n";
					echo "<label><input type='checkbox' name='photo_ratings' value='yes' checked='checked' /> ".$locale['438']."</label>\n";
					if ($settings['ratings_enabled'] == "0") {
						echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
					}
					echo "</td>\n</tr>\n";
					if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
						$sys = "";
						if ($settings['comments_enabled'] == "0" &&  $settings['ratings_enabled'] == "0") {
							$sys = $locale['523'];
						} elseif ($settings['comments_enabled'] == "0") {
							$sys = $locale['521'];
						} else {
							$sys = $locale['522'];
						}
						echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
						echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['520'], $sys);
						echo "</td>\n</tr>";
					}
					echo "<tr><td class='tbl1' colspan='2' style='text-align:center;'><br />";
					echo "<input type='submit' name='btn_upload_dir' value='".$locale['500']."' class='button' />";
					echo "</td>\n</tr>\n";
				} else {
					echo "<tr>\n<td class='tbl1' colspan='2' style='text-align:center;'>".$locale['501']."<br /><br /><input type='submit' class='button' value='".$locale['504']."' /></td>\n</tr>\n";
				}
				echo "</table></form>\n";
			} else {
				echo "<div class='admin-message'>\n";
				echo "<form action='".$formaction."' method='post'>\n";
				echo "<span style='color:red;font-weight:bold;'>".sprintf($locale['502'], $upload_dir)."</span><br />".$locale['503']."<br />";
				echo "<input type='submit' class='button' value='".$locale['504']."' />";
				echo "</form>\n";
				echo "</div>\n";
			}
			echo "</div>";

			echo "<div class='tbl2' id='show_multiform' style='font-weight:bold;cursor:pointer;margin-bottom:2px;'>".$locale['495']."</div>";
			echo "<div id='multi_upload' class='image_upload' style='padding:15px 0;'>";
			$multi_files = makefilelist($upload_dir, ".|..|index.php", true, "files", "php|js");
			$multi_opts = makefileopts($multi_files);
			if ($can_upload == true) {
				echo "<form name='multiform' method='post' action='".FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."' enctype='multipart/form-data'>\n";
				echo "<table class='tbl-border center' cellpadding='2' cellspacing='0' style='width:500px;'>\n";
				echo ($multi_opts != "" ? "<tr>\n<td class='tbl1' colspan='2' style='text-align:center;'>".$locale['496']."</td>\n</tr>\n" : "");
				echo "<tr>\n<td class='tbl1' colspan='2' style='text-align:left;'>";
				echo sprintf($locale['505'], $upload_dir)."<br />";
				echo sprintf($locale['506'], $album_title)."<br />";
				echo $locale['507']."<br />";
				echo "</td>\n</tr>\n";
				if ($multi_opts != "") {
					echo "<tr>\n<td class='tbl1' style='text-align:right;vertical-align:top;width:20%;'>".$locale['508']."</td>\n";
					echo "<td class='tbl1'>";
					echo "<select size='10' multiple name='multi_image[]' class='textbox' style='width:90%;'>".$multi_opts."</select>";
					echo "</td>\n</tr>\n";
					echo "<tr>\n<td class='tbl1' style='text-align:right;'></td>\n";
					echo "<td class='tbl1' style='text-align:left;'>";
					echo "<label><input type='checkbox' name='photo_comments' value='yes' checked='checked' /> ".$locale['437']."</label>";
					if ($settings['comments_enabled'] == "0") {
						echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
					}
					echo "<br />\n";
					echo "<label><input type='checkbox' name='photo_ratings' value='yes' checked='checked' /> ".$locale['438']."</label>\n";
					if ($settings['ratings_enabled'] == "0") {
						echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
					}
					echo "</td>\n</tr>\n";
					if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
						$sys = "";
						if ($settings['comments_enabled'] == "0" &&  $settings['ratings_enabled'] == "0") {
							$sys = $locale['523'];
						} elseif ($settings['comments_enabled'] == "0") {
							$sys = $locale['521'];
						} else {
							$sys = $locale['522'];
						}
						echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
						echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['520'], $sys);
						echo "</td>\n</tr>";
					}
					echo "<tr>\n<td class='tbl1' colspan='2' style='text-align:center;'><br />\n";
					echo "<input type='submit' class='button' name='btn_multi_upload' value='".$locale['509']."' />\n";
					echo "</td>\n</tr>\n";
				} else {
					echo "<tr>\n<td class='tbl1' colspan='2' style='text-align:center;'>".$locale['510']."<br /><br />\n";
					echo "<input type='submit' class='button' value='".$locale['504']."' /></td>\n</tr>\n";
				}

				echo "</table>";
				echo "</form>\n";
			} else {
				echo "<div class='admin-message'>\n";
				echo "<form action='".$formaction."' method='post'>\n";
				echo "<span style='color:red;font-weight:bold;'>".sprintf($locale['502'], $upload_dir)."</span><br />".$locale['503']."<br />";
				echo "<input type='submit' class='button' value='".$locale['504']."' />";
				echo "</form>\n";
				echo "</div>\n";
			}
			echo "</div>";

			echo "<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo "jQuery(document).ready(function(){
					jQuery('.image_upload:not(#single_upload)').hide();
				});
				jQuery(function() {
					jQuery('#show_folderform').click(function() {
						jQuery('#folder_upload').slideDown('slow');
						jQuery('.image_upload:not(#folder_upload)').slideUp('slow');
					});
					jQuery('#show_singleform').click(function() {
						jQuery('#single_upload').slideDown('slow');
						jQuery('.image_upload:not(#single_upload)').slideUp('slow');
					});
					jQuery('#show_multiform').click(function() {
						jQuery('#multi_upload').slideDown('slow');
						jQuery('.image_upload:not(#multi_upload)').slideUp('slow');
					});
				});\n";
			echo "/* ]]>*/\n";
			echo "</script>\n";
			//Photo-Mass Upload End
		}
		closetable();
	}

	opentable($album_title.": ".$locale['402']);
	$rows = dbcount("(photo_id)", DB_PHOTOS, "album_id='".$_GET['album_id']."'");
	if ($rows) {
		if (!isset($_GET['rowstart']) || isset($_GET['rowstart']) && !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
		$result = dbquery(
			"SELECT tp.photo_id, tp.photo_title, tp.photo_thumb1, tp.photo_datestamp, tp.photo_views, tp.photo_order, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_PHOTOS." tp
			LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
			WHERE album_id='".$_GET['album_id']."' ORDER BY photo_order
			LIMIT ".$_GET['rowstart'].",".$settings['thumbs_per_page']
		);
		$counter = 0; $k = ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart'] + 1);
		echo "<form name='move_form' method='post' action='".FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."'>\n";
		echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
		if ($rows > $settings['thumbs_per_page']) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."&amp;")."\n</div>\n";
		}
		$move = dbcount("(album_id)", DB_PHOTO_ALBUMS, "album_id!='".$_GET['album_id']."'");
		while ($data = dbarray($result)) {
			$up = ""; $down = "";
			if ($rows != 1){
				$orderu = $data['photo_order'] - 1;
				$orderd = $data['photo_order'] + 1;
				if ($k == 1) {
					$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."&amp;rowstart=".$_GET['rowstart']."&amp;action=mdown&amp;order=$orderd&amp;photo_id=".$data['photo_id']."'><img src='".get_image("right")."' alt='".$locale['453']."' title='".$locale['453']."' style='border:0px;vertical-align:middle' /></a>\n";
				} elseif ($k < $rows){
					$up = "<a href='".FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."&amp;rowstart=".$_GET['rowstart']."&amp;action=mup&amp;order=$orderu&amp;photo_id=".$data['photo_id']."'><img src='".get_image("left")."' alt='".$locale['452']."' title='".$locale['452']."' style='border:0px;vertical-align:middle' /></a> &middot;\n";
					$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."&amp;rowstart=".$_GET['rowstart']."&amp;action=mdown&amp;order=$orderd&amp;photo_id=".$data['photo_id']."'><img src='".get_image("right")."' alt='".$locale['453']."' title='".$locale['453']."' style='border:0px;vertical-align:middle' /></a>\n";
				} else {
					$up = "<a href='".FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."&amp;rowstart=".$_GET['rowstart']."&amp;action=mup&amp;order=$orderu&amp;photo_id=".$data['photo_id']."'><img src='".get_image("left")."' alt='".$locale['452']."' title='".$locale['452']."' style='border:0px;vertical-align:middle' /></a> &middot;\n";
				}
			}
			if ($counter != 0 && ($counter % $settings['thumbs_per_row'] == 0)) { echo "</tr>\n<tr>\n"; }
			echo "<td align='center' valign='top' class='tbl1'>\n";
			echo "<label>";
			if ($move) {
				echo "<input type='checkbox' name='sel_photo[]' value='".$data['photo_id']."' />&nbsp;";
			}
			echo "<strong>".$data['photo_order']." ".$data['photo_title']."</strong></label><br /><br />\n";
			if ($data['photo_thumb1'] && file_exists(PHOTODIR.$data['photo_thumb1'])){
				echo "<img src='".PHOTODIR.$data['photo_thumb1']."' alt='".$locale['451']."' style='border:0px' />";
			} else {
				echo $locale['450'];
			}
			echo "<br /><br />\n<span class='small'>".$up;
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;album_id=".$_GET['album_id']."&amp;photo_id=".$data['photo_id']."'>".$locale['454']."</a> &middot;\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;album_id=".$_GET['album_id']."&amp;photo_id=".$data['photo_id']."'>".$locale['455']."</a> ".$down;
			echo "<br /><br />\n".$locale['456'].showdate("shortdate", $data['photo_datestamp'])."<br />\n";
			echo $locale['457'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br />\n";
			echo $locale['458'].$data['photo_views']."<br />\n";
			echo $locale['459'].dbcount("(comment_id)", DB_COMMENTS, "comment_type='P' AND comment_item_id='".$data['photo_id']."'")."</span><br />\n";
			echo "</td>\n";
			$counter++; $k++;
		}
		$result = dbquery("SELECT album_id, album_title FROM ".DB_PHOTO_ALBUMS." WHERE album_id!='".$_GET['album_id']."'");
		echo "</tr>\n<tr>\n";
		echo "<td align='center' colspan='".$settings['thumbs_per_row']."' class='tbl2'>\n";
		if (dbrows($result)) {
			echo "<a href='#' onclick=\"javascript:setChecked('move_form','sel_photo[]',1);return false;\">".$locale['470']."</a> ::\n";
			echo "<a href='#' onclick=\"javascript:setChecked('move_form','sel_photo[]',0);return false;\">".$locale['471']."</a> ::\n";
			echo $locale['472'].": <select class='textbox' name='move_album_id'>\n";
			echo "<option value=''>-- ".$locale['473']." --</option>\n";
			while ($data = dbarray($result)) {
				echo "<option value='".$data['album_id']."'>".$data['album_title']."</option>\n";
			}
			echo "</select><br />\n<input class='button' type='submit' name='move_sel_photos' value='".$locale['474']."' onclick=\"javascript:return ConfirmMove(0);\" />\n";
			echo "<input class='button' type='submit' name='move_all_photos' value='".$locale['475']."' onclick=\"javascript:return ConfirmMove(1);\" />\n";
		}
		echo "<input class='button' type='button' value='".$locale['476']."' onclick=\"location.href='photoalbums.php".$aidlink."';\" />\n";
		echo "</td>\n</tr>\n</table>\n</form>\n";
		if (dbrows($result)) {
			echo "<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo "function setChecked(frmName,chkName,val) {\n";
			echo "dml=document.forms[frmName];\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {\n";
			echo "if(dml.elements[i].name == chkName) {"."\n"."dml.elements[i].checked = val;\n";
			echo "}\n}\n}\n"."function ConfirmMove(moveType) {\n";
			echo "if(moveType==0) {"."\n"."return confirm('".$locale['481']."')\n";
			echo "}else{\n"."return confirm('".$locale['482']."')\n";
			echo "}\n}\n";
			echo "/* ]]>*/\n";
			echo "</script>\n";
		}
		if ($rows > $settings['thumbs_per_page']) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."&amp;")."\n</div>\n";
		}
	}else{
		echo "<div style='text-align:center'>".$locale['480']."</div>\n";
	}
	closetable();
} else {
	opentable($locale['403']);
	echo "<div id='close-message'><div class='admin-message'>".$locale['420']."</div></div>\n";
	closetable();
}


require_once THEMES."templates/footer.php";
?>
