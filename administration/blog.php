<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog.php
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
require_once __DIR__.'/../maincore.php';

if (!checkrights("BLOG") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/blog.php";

if ($settings['tinymce_enabled']) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	require_once INCLUDES."html_buttons_include.php";
}

if (isset($_GET['error']) && isnum($_GET['error'])) {
	if ($_GET['error'] == 1) {
		$message = $locale['413'];
	} elseif ($_GET['error'] == 2) {
		$message = sprintf($locale['414'], parsebytesize($settings['blog_photo_max_b']));
	} elseif ($_GET['error'] == 3) {
		$message = $locale['415'];
	} elseif ($_GET['error'] == 4) {
		$message = sprintf($locale['416'], $settings['blog_photo_max_w'], $settings['blog_photo_max_h']);
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}
if (isset($_GET['status'])) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['412'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if (isset($_POST['save'])) {
	$error = "";
	$blog_subject = stripinput($_POST['blog_subject']);
	$blog_cat = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
	if (isset($_FILES['blog_image']) && is_uploaded_file($_FILES['blog_image']['tmp_name'])) {
		require_once INCLUDES."photo_functions_include.php";

		$image = $_FILES['blog_image'];
		$image_name = stripfilename(str_replace(" ", "_", strtolower(substr($image['name'], 0, strrpos($image['name'], ".")))));
		$image_ext = strtolower(strrchr($image['name'],"."));

		if ($image_ext == ".gif") { $filetype = 1;
		} elseif ($image_ext == ".jpg") { $filetype = 2;
		} elseif ($image_ext == ".png") { $filetype = 3;
		} else { $filetype = false; }

		if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $image_name)) {
			$error = 1;
		} elseif ($image['size'] > $settings['blog_photo_max_b']){
			$error = 2;
		} elseif (!$filetype) {
			$error = 3;
		} else {
			$image_t1 = image_exists(IMAGES_B_T, $image_name."_t1".$image_ext);
			$image_t2 = image_exists(IMAGES_B_T, $image_name."_t2".$image_ext);
			$image_full = image_exists(IMAGES_B, $image_name.$image_ext);

			move_uploaded_file($_FILES['blog_image']['tmp_name'], IMAGES_B.$image_full);
			if (function_exists("chmod")) { chmod(IMAGES_B.$image_full, 0644); }
			$imagefile = @getimagesize(IMAGES_B.$image_full);
			if ($imagefile[0] > $settings['blog_photo_max_w'] || $imagefile[1] > $settings['blog_photo_max_h']) {
				$error = 4;
				unlink(IMAGES_B.$image_full);
			} else {
				createthumbnail($filetype, IMAGES_B.$image_full, IMAGES_B_T.$image_t1, $settings['blog_photo_w'], $settings['blog_photo_h']);
				if ($settings['blog_thumb_ratio'] == 0) {
					createthumbnail($filetype, IMAGES_B.$image_full, IMAGES_B_T.$image_t2, $settings['blog_thumb_w'], $settings['blog_thumb_h']);
				} else {
					createsquarethumbnail($filetype, IMAGES_B.$image_full, IMAGES_B_T.$image_t2, $settings['blog_thumb_w']);
				}
			}
		}
		if (!$error) {
			$blog_image = $image_full;
			$blog_image_t1 = $image_t1;
			$blog_image_t2 = $image_t2;
		} else {
			$blog_image = "";
			$blog_image_t1 = "";
			$blog_image_t2 = "";
		}
	} else {
		$blog_image = (isset($_POST['blog_image']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_image']) ? $_POST['blog_image'] : "") : "");
		$blog_image_t1 = (isset($_POST['blog_image_t1']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "") : "");
		$blog_image_t2 = (isset($_POST['blog_image_t2']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "") : "");
	}
	$body = addslash($_POST['body']);
	if ($_POST['body2']) {
		$body2 = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['body2']));
	} else {
		$body2 = "";
	}
	$blog_start_date = 0; $blog_end_date = 0;
	if ($_POST['blog_start']['mday']!="--" && $_POST['blog_start']['mon']!="--" && $_POST['blog_start']['year']!="----") {
		$blog_start_date = mktime($_POST['blog_start']['hours'],$_POST['blog_start']['minutes'],0,$_POST['blog_start']['mon'],$_POST['blog_start']['mday'],$_POST['blog_start']['year']);
	}
	if ($_POST['blog_end']['mday']!="--" && $_POST['blog_end']['mon']!="--" && $_POST['blog_end']['year']!="----") {
		$blog_end_date = mktime($_POST['blog_end']['hours'],$_POST['blog_end']['minutes'],0,$_POST['blog_end']['mon'],$_POST['blog_end']['mday'],$_POST['blog_end']['year']);
	}
	$blog_visibility = isnum($_POST['blog_visibility']) ? $_POST['blog_visibility'] : "0";
	$blog_draft = isset($_POST['blog_draft']) ? "1" : "0";
	$blog_sticky = isset($_POST['blog_sticky']) ? "1" : "0";
	if ($settings['tinymce_enabled'] != 1) { $blog_breaks = isset($_POST['line_breaks']) ? "y" : "n"; } else { $blog_breaks = "n"; }
	$blog_comments = isset($_POST['blog_comments']) ? "1" : "0";
	$blog_ratings = isset($_POST['blog_ratings']) ? "1" : "0";
	$blog_language = stripinput($_POST['blog_language']);
	if (isset($_POST['blog_id']) && isnum($_POST['blog_id'])) {
		$result = dbquery("SELECT blog_image, blog_image_t1, blog_image_t2 FROM ".DB_BLOG." WHERE blog_id='".$_POST['blog_id']."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			if ($blog_sticky == "1") { $result = dbquery("UPDATE ".DB_BLOG." SET blog_sticky='0' WHERE blog_sticky='1'"); }
			if (isset($_POST['del_image'])) {
				if (!empty($data['blog_image']) && file_exists(IMAGES_B.$data['blog_image'])) { unlink(IMAGES_B.$data['blog_image']); }
				if (!empty($data['blog_image_t1']) && file_exists(IMAGES_B_T.$data['blog_image_t1'])) { unlink(IMAGES_B_T.$data['blog_image_t1']); }
				if (!empty($data['blog_image_t2']) && file_exists(IMAGES_B_T.$data['blog_image_t2'])) { unlink(IMAGES_B_T.$data['blog_image_t2']); }
				$blog_image = "";
				$blog_image_t1 = "";
				$blog_image_t2 = "";
			}
			$result = dbquery("UPDATE ".DB_BLOG." SET blog_subject='$blog_subject', blog_cat='$blog_cat', blog_end='$blog_end_date', blog_image='$blog_image', blog_blog='$body', blog_extended='$body2', blog_breaks='$blog_breaks',".($blog_start_date != 0 ? " blog_datestamp='$blog_start_date'," : "")." blog_start='$blog_start_date', blog_image_t1='$blog_image_t1', blog_image_t2='$blog_image_t2', blog_visibility='$blog_visibility', blog_draft='$blog_draft', blog_sticky='$blog_sticky', blog_allow_comments='$blog_comments', blog_allow_ratings='$blog_ratings' WHERE blog_id='".$_POST['blog_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=su".($error ? "&error=$error" : ""));
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		if ($blog_sticky == "1") { $result = dbquery("UPDATE ".DB_BLOG." SET blog_sticky='0' WHERE blog_sticky='1'"); }
		$result = dbquery("INSERT INTO ".DB_BLOG." (blog_subject, blog_cat, blog_blog, blog_extended, blog_breaks, blog_name, blog_datestamp, blog_start, blog_end, blog_image, blog_image_t1, blog_image_t2, blog_visibility, blog_draft, blog_sticky, blog_reads, blog_allow_comments, blog_allow_ratings, blog_language) VALUES ('$blog_subject', '$blog_cat', '$body', '$body2', '$blog_breaks', '".$userdata['user_id']."', '".($blog_start_date != 0 ? $blog_start_date : time())."', '$blog_start_date', '$blog_end_date', '$blog_image', '$blog_image_t1', '$blog_image_t2', '$blog_visibility', '$blog_draft', '$blog_sticky', '0', '$blog_comments', '$blog_ratings', '$blog_language')");
		redirect(FUSION_SELF.$aidlink."&status=sn".($error ? "&error=$error" : ""));
	}
} else if (isset($_POST['delete']) && (isset($_POST['blog_id']) && isnum($_POST['blog_id']))) {
	$result = dbquery("SELECT blog_image, blog_image_t1, blog_image_t2 FROM ".DB_BLOG." WHERE blog_id='".$_POST['blog_id']."' LIMIT 1");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!empty($data['blog_image']) && file_exists(IMAGES_B.$data['blog_image'])) { unlink(IMAGES_B.$data['blog_image']); }
		if (!empty($data['blog_image_t1']) && file_exists(IMAGES_B_T.$data['blog_image_t1'])) { unlink(IMAGES_B_T.$data['blog_image_t1']); }
		if (!empty($data['blog_image_t2']) && file_exists(IMAGES_B_T.$data['blog_image_t2'])) { unlink(IMAGES_B_T.$data['blog_image_t2']); }
		$result = dbquery("DELETE FROM ".DB_BLOG." WHERE blog_id='".$_POST['blog_id']."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$_POST['blog_id']."' and comment_type='N'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$_POST['blog_id']."' and rating_type='N'");
		redirect(FUSION_SELF.$aidlink."&status=del");
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} else {
	if (isset($_POST['preview'])) {
		$blog_subject = stripinput($_POST['blog_subject']);
		$blog_cat = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
		$blog_language = stripinput($_POST['blog_language']);
		$body = phpentities(stripslash($_POST['body']));
		$bodypreview = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslash($_POST['body']));
		if ($_POST['body2']) {
			$body2 = phpentities(stripslash($_POST['body2']));
			$body2preview = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslash($_POST['body2']));
		} else {
			$body2 = "";
		}
		if (isset($_POST['line_breaks'])) {
			$blog_breaks = " checked='checked'";
			$bodypreview = nl2br($bodypreview);
			if ($body2) { $body2preview = nl2br($body2preview); }
		} else {
			$blog_breaks = "";
		}
		$blog_start = array(
			"mday" => isnum($_POST['blog_start']['mday']) ? $_POST['blog_start']['mday'] : "--",
			"mon" => isnum($_POST['blog_start']['mon']) ? $_POST['blog_start']['mon'] : "--",
			"year" => isnum($_POST['blog_start']['year']) ? $_POST['blog_start']['year'] : "----",
			"hours" => isnum($_POST['blog_start']['hours']) ? $_POST['blog_start']['hours'] : "0",
			"minutes" => isnum($_POST['blog_start']['minutes']) ? $_POST['blog_start']['minutes'] : "0",
		);
		$blog_end = array(
			"mday" => isnum($_POST['blog_end']['mday']) ? $_POST['blog_end']['mday'] : "--",
			"mon" => isnum($_POST['blog_end']['mon']) ? $_POST['blog_end']['mon'] : "--",
			"year" => isnum($_POST['blog_end']['year']) ? $_POST['blog_end']['year'] : "----",
			"hours" => isnum($_POST['blog_end']['hours']) ? $_POST['blog_end']['hours'] : "0",
			"minutes" => isnum($_POST['blog_end']['minutes']) ? $_POST['blog_end']['minutes'] : "0",
		);
		$blog_image = (isset($_POST['blog_image']) ? $_POST['blog_image'] : "");
		$blog_image_t1 = (isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "");
		$blog_image_t2 = (isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "");
		$blog_visibility = isnum($_POST['blog_visibility']) ? $_POST['blog_visibility'] : "0";
		$blog_draft = isset($_POST['blog_draft']) ? " checked='checked'" : "";
		$blog_sticky = isset($_POST['blog_sticky']) ? " checked='checked'" : "";
		$blog_comments = isset($_POST['blog_comments']) ? " checked='checked'" : "";
		$blog_ratings = isset($_POST['blog_ratings']) ? " checked='checked'" : "";
		
		opentable($blog_subject);
		echo "$bodypreview\n";
		closetable();
		if (isset($body2preview)) {
			opentable($blog_subject);
			echo "$body2preview\n";
			closetable();
		}
	}
	$result = dbquery("SELECT blog_id, blog_subject, blog_draft FROM ".DB_BLOG." ".(multilang_table("NS") ?  "WHERE blog_language='".LANGUAGE."'" : "")." ORDER BY blog_draft DESC, blog_datestamp DESC");
	if (dbrows($result) != 0) {
		$editlist = ""; $sel = "";
		while ($data = dbarray($result)) {
			if ((isset($_POST['blog_id']) && isnum($_POST['blog_id'])) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
				$blog_id = isset($_POST['blog_id']) ? $_POST['blog_id'] : $_GET['blog_id'];
				$sel = ($blog_id == $data['blog_id'] ? " selected='selected'" : "");
			}
			$editlist .= "<option value='".$data['blog_id']."'$sel>".($data['blog_draft'] ? $locale['438']." " : "").$data['blog_subject']."</option>\n";
		}
		opentable($locale['400']);
		echo "<div style='text-align:center'>\n<form name='selectform' method='post' action='".FUSION_SELF.$aidlink."&amp;action=edit'>\n";
		echo "<select name='blog_id' class='textbox' style='width:250px'>\n".$editlist."</select>\n";
		echo "<input type='submit' name='edit' value='".$locale['420']."' class='button' />\n";
		echo "<input type='submit' name='delete' value='".$locale['421']."' onclick='return Deleteblog();' class='button' />\n";
		echo "</form>\n</div>\n";
		closetable();
	}

	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['blog_id']) && isnum($_POST['blog_id'])) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
		$result = dbquery("SELECT blog_subject, blog_cat, blog_blog, blog_extended, blog_start, blog_end, blog_image, blog_image_t1, blog_image_t2, blog_visibility, blog_draft, blog_sticky, blog_breaks, blog_allow_comments, blog_allow_ratings, blog_language FROM ".DB_BLOG." WHERE blog_id='".(isset($_POST['blog_id']) ? $_POST['blog_id'] : $_GET['blog_id'])."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$blog_subject = $data['blog_subject'];
			$blog_cat = $data['blog_cat'];
			$body = phpentities(stripslashes($data['blog_blog']));
			$body2 = phpentities(stripslashes($data['blog_extended']));
			if ($data['blog_start'] > 0) $blog_start = getdate($data['blog_start']);
			if ($data['blog_end'] > 0) $blog_end = getdate($data['blog_end']);
			$blog_image = $data['blog_image'];
			$blog_image_t1 = $data['blog_image_t1'];
			$blog_image_t2 = $data['blog_image_t2'];
			$blog_visibility = $data['blog_visibility'];
			$blog_draft = $data['blog_draft'] == "1" ? " checked='checked'" : "";
			$blog_sticky = $data['blog_sticky'] == "1" ? " checked='checked'" : "";
			$blog_breaks = $data['blog_breaks'] == "y" ? " checked='checked'" : "";
			$blog_comments = $data['blog_allow_comments'] == "1" ? " checked='checked'" : "";
			$blog_ratings = $data['blog_allow_ratings'] == "1" ? " checked='checked'" : "";
			$blog_language = $data['blog_language'];
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
	if ((isset($_POST['blog_id']) && isnum($_POST['blog_id'])) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
		opentable($locale['402']);
	} else {
		if (!isset($_POST['preview'])) {
			$blog_subject = "";
			$blog_cat = "0";
			$body = "";
			$body2 = "";
			$blog_image = "";
			$blog_image_t1 = "";
			$blog_image_t2 = "";
			$blog_visibility = 0;
			$blog_draft = "";
			$blog_sticky = "";
			$blog_breaks = " checked='checked'";
			$blog_comments = " checked='checked'";
			$blog_ratings = " checked='checked'";
			$blog_language = LANGUAGE;
		}
		opentable($locale['401']);
	}
	$result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("NS") ?  "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_name");
	$blog_cat_opts = ""; $sel = "";
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			if (isset($blog_cat)) $sel = ($blog_cat == $data['blog_cat_id'] ? " selected='selected'" : "");
			$blog_cat_opts .= "<option value='".$data['blog_cat_id']."'$sel>".$data['blog_cat_name']."</option>\n";
		}
	}
	$visibility_opts = ""; $sel = "";
	$user_groups = getusergroups();
	foreach($user_groups as $user_group) {
		$sel = ($blog_visibility == $user_group['0'] ? " selected='selected'" : "");
		$visibility_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
	}
	echo "<form name='inputform' method='post' action='".FUSION_SELF.$aidlink."' enctype='multipart/form-data' onsubmit='return ValidateForm(this);'>\n";
	echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
	echo "<td width='100' class='tbl'>".$locale['422']."</td>\n";
	echo "<td width='80%' class='tbl'><input type='text' name='blog_subject' value='".$blog_subject."' class='textbox' style='width: 250px' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='100' class='tbl'>".$locale['423']."</td>\n";
	echo "<td width='80%' class='tbl'><select name='blog_cat' class='textbox'>\n";
	echo "<option value='0'>".$locale['424']."</option>\n".$blog_cat_opts."</select></td>\n";
	echo "</tr>\n";
	if (multilang_table("NS")) { 
	echo "<tr><td class='tbl'>".$locale['global_ML100']."</td>\n";
	$opts = get_available_languages_list($selected_language = "$blog_language");
	echo "<td class='tbl'>
	<select name='blog_language' class='textbox' style='width:200px;'>
	<option value=''>".$locale['global_ML101']."</option>\n	".$opts."</select></td>\n"; 
	echo "</tr>\n"; 
	} else {
	echo "<input type='hidden' name='blog_language' value='".$blog_language."' />\n";	
	}
	echo "<tr><td class='tbl' valign='top'>".$locale['439'].":</td>\n<td class='tbl' valign='top'>";
	if ($blog_image != "" && $blog_image_t1 != "") {
		echo "<label><img src='".IMAGES_B_T.$blog_image_t2."' alt='".$locale['439']."' /><br />\n";
		echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['421']."</label>\n";
		echo "<input type='hidden' name='blog_image' value='".$blog_image."' />\n";
		echo "<input type='hidden' name='blog_image_t1' value='".$blog_image_t1."' />\n";
		echo "<input type='hidden' name='blog_image_t2' value='".$blog_image_t2."' />\n";
	} else {
		echo "<input type='file' name='blog_image' class='textbox' style='width:250px;' /><br />\n";
		echo sprintf($locale['440'], parsebytesize($settings['blog_photo_max_b']))."\n";
	}
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td valign='top' width='100' class='tbl'>".$locale['425']."</td>\n";
	echo "<td width='80%' class='tbl'><textarea name='body' cols='95' rows='10' class='textbox' style='width:98%'>".$body."</textarea></td>\n";
	echo "</tr>\n";
	if (!$settings['tinymce_enabled']) {
		echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
		echo display_html("inputform", "body", true, true, true, IMAGES_B);
		echo "</td>\n</tr>\n";
	}
	echo "<tr>\n<td valign='top' width='100' class='tbl'>".$locale['426']."</td>\n";
	echo "<td class='tbl'><textarea name='body2' cols='95' rows='10' class='textbox' style='width:98%'>".$body2."</textarea></td>\n";
	echo "</tr>\n";
	if ($settings['tinymce_enabled'] != 1) {
		echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
		echo "<input type='button' value='".$locale['441']."' class='button' onclick=\"insertText('body2', '&lt;!--PAGEBREAK--&gt;');\" />\n";
		echo display_html("inputform", "body2", true, true, true, IMAGES_B);
		echo "</td>\n</tr>\n";
	}
	echo "<tr>\n";
	echo "<td class='tbl'>".$locale['427']."</td>\n";
	echo "<td class='tbl'><select name='blog_start[mday]' class='textbox'>\n<option>--</option>\n";
	for ($i=1;$i<=31;$i++) echo "<option".(isset($blog_start['mday']) && $blog_start['mday'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> <select name='blog_start[mon]' class='textbox'>\n<option>--</option>\n";
	for ($i=1;$i<=12;$i++) echo "<option".(isset($blog_start['mon']) && $blog_start['mon'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> <select name='blog_start[year]' class='textbox'>\n<option>----</option>\n";
	for ($i=(isset($blog_start['year']) && $blog_start['year'] != "----" ? $blog_start['year'] : date('Y'));$i<=date("Y", strtotime('+10 years'));$i++) echo "<option".(isset($blog_start['year']) && $blog_start['year'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> / <select name='blog_start[hours]' class='textbox'>\n";
	for ($i=0;$i<=24;$i++) echo "<option".(isset($blog_start['hours']) && $blog_start['hours'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> : <select name='blog_start[minutes]' class='textbox'>\n";
	for ($i=0;$i<=60;$i++) echo "<option".(isset($blog_start['minutes']) && $blog_start['minutes'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> : 00 ".$locale['429']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>".$locale['428']."</td>\n";
	echo "<td class='tbl'><select name='blog_end[mday]' class='textbox'>\n<option>--</option>\n";
	for ($i=1;$i<=31;$i++) echo "<option".(isset($blog_end['mday']) && $blog_end['mday'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> <select name='blog_end[mon]' class='textbox'>\n<option>--</option>\n";
	for ($i=1;$i<=12;$i++) echo "<option".(isset($blog_end['mon']) && $blog_end['mon'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> <select name='blog_end[year]' class='textbox'>\n<option>----</option>\n";
	for ($i=(isset($blog_end['year']) && $blog_end['year'] != "----" ? $blog_end['year'] : date('Y'));$i<=date("Y", strtotime('+10 years'));$i++) echo "<option".(isset($blog_end['year']) && $blog_end['year'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> / <select name='blog_end[hours]' class='textbox'>\n";
	for ($i=0;$i<=24;$i++) echo "<option".(isset($blog_end['hours']) && $blog_end['hours'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> : <select name='blog_end[minutes]' class='textbox'>\n";
	for ($i=0;$i<=60;$i++) echo "<option".(isset($blog_end['minutes']) && $blog_end['minutes'] == $i ? " selected='selected'" : "").">$i</option>\n";
	echo "</select> : 00 ".$locale['429']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>".$locale['430']."</td>\n";
	echo "<td class='tbl'><select name='blog_visibility' class='textbox'>\n".$visibility_opts."</select></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'></td><td class='tbl'>\n";
	echo "<label><input type='checkbox' name='blog_draft' value='yes'".$blog_draft." /> ".$locale['431']."</label><br />\n";
	echo "<label><input type='checkbox' name='blog_sticky' value='yes'".$blog_sticky." /> ".$locale['432']."</label><br />\n";
	if ($settings['tinymce_enabled'] != 1) {
		echo "<label><input type='checkbox' name='line_breaks' value='yes'".$blog_breaks." /> ".$locale['433']."</label><br />\n";
	}
	echo "<label><input type='checkbox' name='blog_comments' value='yes' onclick='SetRatings();'".$blog_comments." /> ".$locale['434']."</label>";
	if ($settings['comments_enabled'] == "0") {
		echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
	}
	echo "<br />\n";
	echo "<label><input type='checkbox' name='blog_ratings' value='yes'".$blog_ratings." /> ".$locale['435']."</label>";
	if ($settings['ratings_enabled'] == "0") {
		echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
	}
	echo "</td>\n";
	echo "</tr>\n";
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" &&  $settings['ratings_enabled'] == "0") {
			$sys = $locale['455'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['453'];
		} else {
			$sys = $locale['454'];
		}
		echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
		echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['452'], $sys);
		echo "</td>\n</tr>";
	}
	echo "<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'><br />\n";
	if ((isset($_POST['edit']) && (isset($_POST['blog_id']) && isnum($_POST['blog_id']))) || (isset($_POST['preview']) && (isset($_POST['blog_id']) && isnum($_POST['blog_id']))) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
		echo "<input type='hidden' name='blog_id' value='".(isset($_POST['blog_id']) ? $_POST['blog_id'] : $_GET['blog_id'])."' />\n";
	}
	echo "<input type='submit' name='preview' value='".$locale['436']."' class='button' />\n";
	echo "<input type='submit' name='save' value='".$locale['437']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();
	echo "<script type='text/javascript'>\n"."function Deleteblog() {\n";
	echo "return confirm('".$locale['451']."');\n}\n";
	echo "function ValidateForm(frm) {\n"."if(frm.blog_subject.value=='') {\n";
	echo "alert('".$locale['450']."');\n"."return false;\n}\n}\n";
	echo "function SetRatings() {\n"."if (inputform.blog_comments.checked == false) {\n";
	echo "inputform.blog_ratings.checked = false;\n"."inputform.blog_ratings.disabled = true;\n";
	echo "} else {\n"."inputform.blog_ratings.disabled = false;\n}\n}\n</script>\n";
}

require_once THEMES."templates/footer.php";
?>