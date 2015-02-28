<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: photoalbums.php
| Author: Nick Jones (Digitanium)
| Co-Author: Robert Gaudyn (Wooya)
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

if (function_exists('gd_info')) {

	define("SAFEMODE", @ini_get("safe_mode") ? true : false);

	if (isset($_GET['action']) && $_GET['action'] == "refresh") {
		$i = 1; $k = 1;
		$result = dbquery("SELECT album_id FROM ".DB_PHOTO_ALBUMS." ORDER BY album_order");
		while ($data = dbarray($result)) {
			$result2 = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order='$i' WHERE album_id='".$data['album_id']."'");
			$result2 = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE album_id='".$data['album_id']."' ORDER BY photo_order");
			while ($data2 = dbarray($result2)) {
				$result3 = dbquery("UPDATE ".DB_PHOTOS." SET photo_order='$k' WHERE photo_id='".$data2['photo_id']."'");
				$k++;
			}
			$i++; $k = 1;
		}
		redirect(FUSION_SELF.$aidlink);
	}

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
		} elseif ($_GET['status'] == "delt") {
			$message = $locale['412'];
		} elseif ($_GET['status'] == "dely") {
			$message = $locale['413'];
		} elseif ($_GET['status'] == "deln") {
			$message = $locale['419'];
		}
		if ($message) { echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
	}

	if (isset($_POST['cancel'])) {
		redirect(FUSION_SELF.$aidlink);
	} elseif ((isset($_GET['action']) && $_GET['action'] == "deletethumb") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		$data = dbarray(dbquery("SELECT album_thumb,album_order FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'"));
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_thumb='' WHERE album_id='".$_GET['album_id']."'");
		@unlink(PHOTOS.$data['album_thumb']);
		redirect(FUSION_SELF.$aidlink."&status=delt&album_id=".$_GET['album_id']);
	} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		if (dbcount("(photo_id)", DB_PHOTOS, "album_id='".$_GET['album_id']."'")) {
			if (!isset($_POST['confirm_password'])) {
				opentable($locale['430']);
				echo "<div style='text-align:center'>\n";
				echo "<form action='".FUSION_SELF.$aidlink."&amp;action=delete&amp;album_id=".$_GET['album_id']."' method='post'>\n";
				echo $locale['431']."<br /><br />\n<input class='textbox' type='password' name='admin_passwd' autocomplete='off' /><br /><br />\n";
				echo "<input class='button' type='submit' name='confirm_password' value='".$locale['432']."' />\n";
				echo "<input class='button' type='submit' name='cancel' value='".$locale['433']."' />\n";
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
								if ($data['photo_thumb1']) { @unlink(PHOTOS."album_".$data['album_id']."/".$data['photo_thumb1']); }
								if ($data['photo_thumb2']) { @unlink(PHOTOS."album_".$data['album_id']."/".$data['photo_thumb2']); }
							} else {
								@unlink(PHOTOS.$data['photo_filename']);
								if ($data['photo_thumb1']) { @unlink(PHOTOS.$data['photo_thumb1']); }
								if ($data['photo_thumb2']) { @unlink(PHOTOS.$data['photo_thumb2']); }
							}
						}
						$result = dbquery("DELETE FROM ".DB_PHOTOS." WHERE album_id='".$_GET['album_id']."'");
				}
				$data = dbarray(dbquery("SELECT album_thumb,album_order,album_id FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'"));
				$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order-1) WHERE album_order>'".$data['album_order']."'");
				$result = dbquery("DELETE FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'");
					if ($data['album_thumb']) { @unlink(PHOTOS.$data['album_thumb']); }
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
			$data = dbarray(dbquery("SELECT album_thumb,album_order FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'"));
			$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order-1) WHERE album_order>'".$data['album_order']."'");
			$result = dbquery("DELETE FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'");
			if ($data['album_thumb']) { @unlink(PHOTOS.$data['album_thumb']); }
			if (!SAFEMODE) {
				@unlink(PHOTOS."album_".$_GET['album_id']."/index.php");
				rmdir(PHOTOS."album_".$_GET['album_id']);
			}
			redirect(FUSION_SELF.$aidlink."&status=dely");
		}
	} elseif ((isset($_GET['action']) && $_GET['action'] == "mup") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		$data = dbarray(dbquery("SELECT album_id FROM ".DB_PHOTO_ALBUMS." WHERE album_order='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=album_order+1 WHERE album_id='".$data['album_id']."'");
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=album_order-1 WHERE album_id='".$_GET['album_id']."'");
		$rowstart = $_GET['order'] > $settings['thumbs_per_page'] ? ((ceil($_GET['order'] / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
		redirect(FUSION_SELF.$aidlink."&rowstart=$rowstart");
	} elseif ((isset($_GET['action']) && $_GET['action'] == "mdown") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
		$data = dbarray(dbquery("SELECT album_id FROM ".DB_PHOTO_ALBUMS." WHERE album_order='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=album_order-1 WHERE album_id='".$data['album_id']."'");
		$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=album_order+1 WHERE album_id='".$_GET['album_id']."'");
		$rowstart = $_GET['order'] > $settings['thumbs_per_page'] ? ((ceil($_GET['order'] / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
		redirect(FUSION_SELF.$aidlink."&rowstart=$rowstart");
	} elseif (isset($_POST['save_album']) && isset($_POST['album_title']) && $_POST['album_title'] != "") {
		$error = "";
		$album_title = stripinput($_POST['album_title']);
		$album_description = stripinput($_POST['album_description']);
		$album_access = isnum($_POST['album_access']) ? $_POST['album_access'] : "0";
		$album_order = isnum($_POST['album_order']) ? $_POST['album_order'] : "";
		if (!SAFEMODE && (!isset($_GET['action']) || $_GET['action'] != "edit")) {
			$result = dbarray(dbquery("SHOW TABLE STATUS LIKE '".DB_PHOTO_ALBUMS."'"));
			$album_id = $result['Auto_increment'];
			@mkdir(PHOTOS."album_".$album_id, 0777);
			@copy(IMAGES."index.php", PHOTOS."album_".$album_id."/index.php");
		}
		if (isset($_FILES) && count($_FILES) && is_uploaded_file($_FILES['album_pic_file']['tmp_name'])) {
			$album_types = array(".gif",".jpg",".jpeg",".png");
			$album_pic = $_FILES['album_pic_file'];
			$album_name = stripfilename($album_pic['name']);
			$album_name = stripfilename(str_replace(" ", "_", strtolower(substr($album_pic['name'], 0, strrpos($album_pic['name'], ".")))));
			$album_ext = strtolower(strrchr($album_pic['name'],"."));
			if (!preg_match("/^[-0-9A-Z_\.\[\]\s]+$/i", $album_name)) {
				$error = 1;
			} elseif ($album_pic['size'] > $settings['photo_max_b']){
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
		if (!$error) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
				$old_album_order = dbresult(dbquery("SELECT album_order FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'"), 0);
				if ($album_order > $old_album_order) {
					$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order-1) WHERE album_order>'$old_album_order' AND album_order<='$album_order'");
				} elseif ($album_order < $old_album_order) {
					$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order+1) WHERE album_order<'$old_album_order' AND album_order>='$album_order'");
				}
				$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_title='$album_title', album_description='$album_description',".(isset($album_thumb)?" album_thumb='$album_thumb',":"")." album_user='".$userdata['user_id']."', album_access='$album_access', album_order='$album_order' WHERE album_id='".$_GET['album_id']."'");
				$rowstart = $album_order > $settings['thumbs_per_page'] ? ((ceil($album_order / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
				redirect(FUSION_SELF.$aidlink."&status=su&rowstart=$rowstart");
			} else {
				if (!$album_order) { $album_order = dbresult(dbquery("SELECT MAX(album_order) FROM ".DB_PHOTO_ALBUMS.""), 0) + 1; }
				$result = dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order=(album_order+1) WHERE album_order>='$album_order'");
				$result = dbquery("INSERT INTO ".DB_PHOTO_ALBUMS." (album_title, album_description, album_thumb, album_user, album_access, album_order, album_datestamp) VALUES ('$album_title', '$album_description', '".(isset($album_thumb) ? $album_thumb : "")."', '".$userdata['user_id']."', '$album_access', '$album_order', '".time()."')");
				$rowstart = $album_order > $settings['thumbs_per_page'] ? ((ceil($album_order / $settings['thumbs_per_page'])-1)*$settings['thumbs_per_page']) : "0";
				redirect(FUSION_SELF.$aidlink."&status=sn&rowstart=$rowstart");
			}
		} else {
			redirect(FUSION_SELF.$aidlink."&status=se&error=$error");
		}
	} else {
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['album_id']) && isnum($_GET['album_id']))) {
			$result = dbquery("SELECT album_title, album_description, album_thumb, album_access, album_order FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				$album_title = $data['album_title'];
				$album_description = $data['album_description'];
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
			$album_thumb = "";
			$album_access = "";
			$album_order = "";
			$formaction = FUSION_SELF.$aidlink;
			opentable($locale['400']);
		}
		$access_opts = ""; $sel = "";
		$user_groups = getusergroups();
		while(list($key, $user_group) = each($user_groups)){
			$sel = ($album_access == $user_group['0'] ? " selected='selected'" : "");
			$access_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
		}
		echo "<form name='inputform' method='post' action='$formaction' enctype='multipart/form-data'>\n";
		echo "<table cellspacing='0' cellpadding='0' class='center'>\n<tr>\n";
		echo "<td class='tbl'>".$locale['440']."</td>\n";
		echo "<td class='tbl'><input type='text' name='album_title' value='".$album_title."' maxlength='100' class='textbox' style='width:330px;' /></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td valign='top' class='tbl'>".$locale['441']."</td>\n";
		echo "<td class='tbl'><textarea name='album_description' cols='60' rows='5' class='textbox' style='width:330px;'>".$album_description."</textarea><br />\n";
		echo display_bbcodes("300px", "album_description", "inputform", "b|i|u|center|small|url|mail|img|quote")."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td class='tbl'>".$locale['442']."</td>\n";
		echo "<td class='tbl'><select name='album_access' class='textbox' style='width:150px;'>\n".$access_opts."</select>\n";
		echo $locale['443']."<input type='text' name='album_order' value='".$album_order."' maxlength='4' class='textbox' style='width:40px;' />\n";
		echo "</td>\n</tr>\n<tr>\n";
		echo "<td valign='top' class='tbl'>".$locale['444'];
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && ($album_thumb && file_exists(PHOTOS.$album_thumb))) {
			echo "<br /><br />\n<a class='small' href='".FUSION_SELF.$aidlink."&amp;action=deletethumb&amp;album_id=".$_GET['album_id']."'>".$locale['469']."</a></td>\n";
			echo "<td class='tbl'><img src='".PHOTOS.$album_thumb."' alt='album_thumb' />";
		} else {
			echo "</td>\n<td class='tbl'><input type='file' name='album_pic_file' class='textbox' style='width:250px;' />";
		}
		echo "</td>\n</tr>\n<tr>\n";
		echo "<td colspan='2' align='center' class='tbl'><br />\n";
		echo "<input type='submit' name='save_album' value='".$locale['445']."' class='button' />\n";
		if (isset($_GET['action']) && $_GET['action'] == "edit") {
			echo "<input type='submit' name='cancel' value='".$locale['433']."' class='button' />\n";
		}
		echo "</td>\n</tr>\n</table>\n</form>\n";
		closetable();
	}

	opentable($locale['402']);
	$rows = dbcount("(album_id)", DB_PHOTO_ALBUMS);
	if ($rows) {
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
		$result = dbquery(
			"SELECT ta.album_id, ta.album_title, ta.album_thumb, ta.album_access, ta.album_order, ta.album_datestamp, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_PHOTO_ALBUMS." ta
			LEFT JOIN ".DB_USERS." tu ON ta.album_user=tu.user_id
			ORDER BY album_order LIMIT ".$_GET['rowstart'].",".$settings['thumbs_per_page']
		);
		$counter = 0; $k = ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart'] + 1);
		echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
		if ($rows > $settings['thumbs_per_page']) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, FUSION_SELF.$aidlink."&amp;")."\n</div>\n"; }
		while ($data = dbarray($result)) {
			$up = ""; $down = "";
			if ($rows != 1){
				$orderu = $data['album_order'] - 1;
				$orderd = $data['album_order'] + 1;
				if ($k == 1){
					$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;action=mdown&amp;order=$orderd&amp;album_id=".$data['album_id']."'><img src='".get_image("right")."' alt='".$locale['467']."' title='".$locale['468']."' style='border:0px;vertical-align:middle' /></a>\n";
				}elseif ($k < $rows){
					$up = "<a href='".FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;action=mup&amp;order=$orderu&amp;album_id=".$data['album_id']."'><img src='".get_image("left")."' alt='".$locale['467']."' title='".$locale['466']."' style='border:0px;vertical-align:middle' /></a> &middot;\n";
					$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;action=mdown&amp;order=$orderd&amp;album_id=".$data['album_id']."'><img src='".get_image("right")."' alt='".$locale['467']."' title='".$locale['468']."' style='border:0px;vertical-align:middle' /></a>\n";
				} else {
					$up = "<a href='".FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;action=mup&amp;order=$orderu&amp;album_id=".$data['album_id']."'><img src='".get_image("left")."' alt='".$locale['467']."' title='".$locale['466']."' style='border:0px;vertical-align:middle' /></a> &middot;\n";
				}
			}
			if ($counter != 0 && ($counter % $settings['thumbs_per_row'] == 0)) { echo "</tr>\n<tr>\n"; }
			echo "<td align='center' valign='top' class='tbl'>\n";
			echo "<strong>".$data['album_title']."</strong><br /><br />\n<a href='photos.php".$aidlink."&amp;album_id=".$data['album_id']."'>";
			if ($data['album_thumb'] && file_exists(PHOTOS.$data['album_thumb'])){
				echo "<img src='".PHOTOS.rawurlencode($data['album_thumb'])."' alt='".$locale['460']."' style='border:0px' />";
			} else {
				echo $locale['461'];
			}
			echo "</a><br /><br />\n<span class='small'>".$up;
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;album_id=".$data['album_id']."'>".$locale['468']."</a> &middot;\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;album_id=".$data['album_id']."' onclick=\"return PhotosWarning('".dbcount("(album_id)", DB_PHOTOS, "album_id='".$data['album_id']."'")."');\">".$locale['469']."</a> ".$down;
			echo "<br /><br />\n".$locale['462'].showdate("shortdate", $data['album_datestamp'])."<br />\n";
			echo $locale['463'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br />\n";
			echo $locale['464'].getgroupname($data['album_access'])."<br />\n";
			echo $locale['465'].dbcount("(photo_id)", DB_PHOTOS, "album_id='".$data['album_id']."'")."</span><br />\n";
			echo "</td>\n";
			$counter++; $k++;
		}
		echo "</tr>\n<tr>\n<td align='center' colspan='".$settings['thumbs_per_row']."' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;action=refresh'>".$locale['470']."</a></td>\n</tr>\n</table>\n";
		if ($rows > $settings['thumbs_per_page']) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, FUSION_SELF.$aidlink."&amp;")."\n</div>\n"; }
	} else {
		echo "<div style='text-align:center'>".$locale['471']."</div>\n";
	}

	echo "<script type='text/javascript'>\n"."function PhotosWarning(value) {\n";
	echo "return confirm ('".$locale['500']."');\n}\n</script>";

	closetable();
} else {
	opentable($locale['403']);
	echo "<div id='close-message'><div class='admin-message'>".$locale['420']."</div></div>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>
