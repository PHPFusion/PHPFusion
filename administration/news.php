<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news.php
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
if (!checkrights("N") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/news.php";
if ($settings['tinymce_enabled']) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	require_once INCLUDES."html_buttons_include.php";
}
if (isset($_GET['status'])) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['412'];
	}
	if ($message) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}
if (isset($_POST['save'])) {
	$error = "";
	$news_subject = form_sanitizer($_POST['news_subject'], '', 'news_subject');
	$news_cat = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
	if (isset($_FILES['news_image']) && is_uploaded_file($_FILES['news_image']['tmp_name'])) {
		require_once INCLUDES."photo_functions_include.php";
		$image = $_FILES['news_image'];
		$image_name = stripfilename(str_replace(" ", "_", strtolower(substr($image['name'], 0, strrpos($image['name'], ".")))));
		$image_ext = strtolower(strrchr($image['name'], "."));
		if ($image_ext == ".gif") {
			$filetype = 1;
		} elseif ($image_ext == ".jpg") {
			$filetype = 2;
		} elseif ($image_ext == ".png") {
			$filetype = 3;
		} else {
			$filetype = FALSE;
		}
		if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $image_name)) {
			$defender->stop();
			$defender->addNotice($locale['413']);
			$error = 1;
		} elseif ($image['size'] > $settings['news_photo_max_b']) {
			$error = 2;
			$defender->stop();
			$defender->addNotice(sprintf($locale['414'], parsebytesize($settings['news_photo_max_b'])));
		} elseif (!$filetype) {
			$error = 3;
			$defender->stop();
			$defender->addNotice($locale['415']);
		} else {
			$image_t1 = image_exists(IMAGES_N_T, $image_name."_t1".$image_ext);
			$image_t2 = image_exists(IMAGES_N_T, $image_name."_t2".$image_ext);
			$image_full = image_exists(IMAGES_N, $image_name.$image_ext);
			move_uploaded_file($_FILES['news_image']['tmp_name'], IMAGES_N.$image_full);
			if (function_exists("chmod")) {
				chmod(IMAGES_N.$image_full, 0644);
			}
			$imagefile = @getimagesize(IMAGES_N.$image_full);
			if ($imagefile[0] > $settings['news_photo_max_w'] || $imagefile[1] > $settings['news_photo_max_h']) {
				$error = 4;
				$defender->stop();
				$defender->addNotice(sprintf($locale['416'], $settings['news_photo_max_w'], $settings['news_photo_max_h']));
				unlink(IMAGES_N.$image_full);
			} else {
				createthumbnail($filetype, IMAGES_N.$image_full, IMAGES_N_T.$image_t1, $settings['news_photo_w'], $settings['news_photo_h']);
				if ($settings['news_thumb_ratio'] == 0) {
					createthumbnail($filetype, IMAGES_N.$image_full, IMAGES_N_T.$image_t2, $settings['news_thumb_w'], $settings['news_thumb_h']);
				} else {
					createsquarethumbnail($filetype, IMAGES_N.$image_full, IMAGES_N_T.$image_t2, $settings['news_thumb_w']);
				}
			}
		}
		if (!$error) {
			$news_image = $image_full;
			$news_image_t1 = $image_t1;
			$news_image_t2 = $image_t2;
		} else {
			$news_image = "";
			$news_image_t1 = "";
			$news_image_t2 = "";
		}
	} else {
		$news_image = (isset($_POST['news_image']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['news_image']) ? $_POST['news_image'] : "") : "");
		$news_image_t1 = (isset($_POST['news_image_t1']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['news_image_t1']) ? $_POST['news_image_t1'] : "") : "");
		$news_image_t2 = (isset($_POST['news_image_t2']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['news_image_t2']) ? $_POST['news_image_t2'] : "") : "");
	}
	$body = addslash($_POST['body']);
	if ($_POST['body2']) {
		$body2 = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['body2']));
	} else {
		$body2 = "";
	}
	$news_start_date = 0;
	$news_end_date = 0;
	$news_start = isset($_POST['news_start']) && $_POST['news_start'] ? explode('-', $_POST['news_start']) : '';
	$news_start_date = (!empty($news_start)) ? mktime(0, 0, 0, $news_start[1], $news_start[0], $news_start[2]) : '';
	$news_end = isset($_POST['news_end']) && ($_POST['news_end']) ? explode('-', $_POST['news_end']) : '';
	$news_end_date = (!empty($news_end)) ? mktime(0, 0, 0, $news_end[1], $news_end[0], $news_end[2]) : '';
	$news_visibility = isnum($_POST['news_visibility']) ? $_POST['news_visibility'] : "0";
	$news_draft = isset($_POST['news_draft']) ? "1" : "0";
	$news_sticky = isset($_POST['news_sticky']) ? "1" : "0";
	if ($settings['tinymce_enabled'] != 1) {
		$news_breaks = isset($_POST['line_breaks']) ? "y" : "n";
	} else {
		$news_breaks = "n";
	}
	$news_comments = isset($_POST['news_comments']) ? "1" : "0";
	$news_ratings = isset($_POST['news_ratings']) ? "1" : "0";
	$news_language = stripinput($_POST['news_language']);

	if (isset($_POST['news_id']) && isnum($_POST['news_id']) && !defined('FUSION_NULL')) {
		$result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS." WHERE news_id='".$_POST['news_id']."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			if ($news_sticky == "1") {
				$result = dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
			}
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
				$news_image = "";
				$news_image_t1 = "";
				$news_image_t2 = "";
			}
			$result = dbquery("UPDATE ".DB_NEWS." SET news_subject='$news_subject', news_cat='$news_cat', news_end='$news_end_date', news_image='$news_image', news_news='$body', news_extended='$body2', news_breaks='$news_breaks',".($news_start_date != 0 ? " news_datestamp='$news_start_date'," : "")." news_start='$news_start_date', news_image_t1='$news_image_t1', news_image_t2='$news_image_t2', news_visibility='$news_visibility', news_draft='$news_draft', news_sticky='$news_sticky', news_allow_comments='$news_comments', news_allow_ratings='$news_ratings', news_language='$news_language' WHERE news_id='".$_POST['news_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=su");
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} elseif (!defined('FUSION_NULL')) {
		if ($news_sticky == "1") {
			$result = dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
		}
		$result = dbquery("INSERT INTO ".DB_NEWS." (news_subject, news_cat, news_news, news_extended, news_breaks, news_name, news_datestamp, news_start, news_end, news_image, news_image_t1, news_image_t2, news_visibility, news_draft, news_sticky, news_reads, news_allow_comments, news_allow_ratings, news_language) VALUES ('$news_subject', '$news_cat', '$body', '$body2', '$news_breaks', '".$userdata['user_id']."', '".($news_start_date != 0 ? $news_start_date : time())."', '$news_start_date', '$news_end_date', '$news_image', '$news_image_t1', '$news_image_t2', '$news_visibility', '$news_draft', '$news_sticky', '0', '$news_comments', '$news_ratings', '$news_language')");
		redirect(FUSION_SELF.$aidlink."&status=sn");
	}
} else if (isset($_POST['delete']) && (isset($_POST['news_id']) && isnum($_POST['news_id']))) {
	$result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS." WHERE news_id='".$_POST['news_id']."' LIMIT 1");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
			unlink(IMAGES_N.$data['news_image']);
		}
		if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
			unlink(IMAGES_N_T.$data['news_image_t1']);
		}
		if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
			unlink(IMAGES_N_T.$data['news_image_t2']);
		}
		$result = dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='".$_POST['news_id']."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$_POST['news_id']."' and comment_type='N'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$_POST['news_id']."' and rating_type='N'");
		redirect(FUSION_SELF.$aidlink."&status=del");
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
if (isset($_POST['preview'])) {
	$news_subject = form_sanitizer($_POST['news_subject'], '', 'news_subject');
	$news_cat = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
	$news_language = form_sanitizer($_POST['news_language'], '', 'news_language');
	$body = phpentities(stripslash($_POST['body']));
	$bodypreview = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslash($_POST['body']));
	if ($_POST['body2']) {
		$body2 = phpentities(stripslash($_POST['body2']));
		$body2preview = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslash($_POST['body2']));
	} else {
		$body2 = "";
	}
	if (isset($_POST['line_breaks'])) {
		$news_breaks = " checked='checked'";
		$bodypreview = nl2br($bodypreview);
		if ($body2) {
			$body2preview = nl2br($body2preview);
		}
	} else {
		$news_breaks = "";
	}
	$news_start = (isset($_POST['news_start']) && $_POST['news_start']) ? $_POST['news_start'] : '';
	$news_end = (isset($_POST['news_end']) && $_POST['news_end']) ? $_POST['news_end'] : '';
	$news_image = (isset($_POST['news_image']) ? $_POST['news_image'] : "");
	$news_image_t1 = (isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "");
	$news_image_t2 = (isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "");
	$news_visibility = isnum($_POST['news_visibility']) ? $_POST['news_visibility'] : "0";
	$news_draft = isset($_POST['news_draft']) ? " checked='checked'" : "";
	$news_sticky = isset($_POST['news_sticky']) ? " checked='checked'" : "";
	$news_comments = isset($_POST['news_comments']) ? " checked='checked'" : "";
	$news_ratings = isset($_POST['news_ratings']) ? " checked='checked'" : "";
	if (!defined('FUSION_NULL')) {
		opentable($news_subject);
		echo "$bodypreview\n";
		echo "<hr/>\n";
		if (isset($body2preview)) {
			echo "$body2preview\n";
		}
		closetable();
	}
}
$result = dbquery("SELECT news_id, news_subject, news_draft FROM ".DB_NEWS." ".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."'" : "")." ORDER BY news_draft DESC, news_datestamp DESC");
if (dbrows($result) != 0) {
	$editlist = array();
	while ($data = dbarray($result)) {
		if ((isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
			$news_id = isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'];
			$sel = ($news_id == $data['news_id'] ? " selected='selected'" : "");
		}
		$editlist[$data['news_id']] = "".($data['news_draft'] ? $locale['438']." " : "").$data['news_subject']."";
	}
	opentable($locale['400']);
	echo openform('selectform', 'selectform', 'post', FUSION_SELF.$aidlink."&amp;action=edit", array('downtime' => 10, 'notice' => 0));
	echo form_select('', 'news_id', 'news_id', $editlist, '', array('placeholder' => $locale['choose'], 'class' => 'pull-left m-r-10'));
	echo form_button($locale['420'], 'edit', 'edit', $locale['420'], array('class' => 'btn-primary pull-left m-r-10'));
	echo form_button($locale['421'], 'delete', 'delete', $locale['421'], array('class' => 'btn-primary pull-left'));
	echo closeform();
	closetable();
	add_to_jquery("$('#delete').bind('click', function(){ DeleteNews(); });\n");
}
if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
	$result = dbquery("SELECT news_subject, news_cat, news_news, news_extended, news_start, news_end, news_image, news_image_t1, news_image_t2, news_visibility, news_draft, news_sticky, news_breaks, news_allow_comments, news_allow_ratings, news_language FROM ".DB_NEWS." WHERE news_id='".(isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'])."' LIMIT 1");
	if (dbrows($result)) {
		$data = dbarray($result);
		$news_subject = $data['news_subject'];
		$news_cat = $data['news_cat'];
		$body = phpentities(stripslashes($data['news_news']));
		$body2 = phpentities(stripslashes($data['news_extended']));
		$news_start = "";
		$news_end = "";
		if ($data['news_start'] > 0) $news_start = $data['news_start'];
		if ($data['news_end'] > 0) $news_end = $data['news_end'];
		$news_image = $data['news_image'];
		$news_image_t1 = $data['news_image_t1'];
		$news_image_t2 = $data['news_image_t2'];
		$news_visibility = $data['news_visibility'];
		$news_draft = $data['news_draft'] == "1" ? " checked='checked'" : "";
		$news_sticky = $data['news_sticky'] == "1" ? " checked='checked'" : "";
		$news_breaks = $data['news_breaks'] == "y" ? " checked='checked'" : "";
		$news_comments = $data['news_allow_comments'] == "1" ? " checked='checked'" : "";
		$news_ratings = $data['news_allow_ratings'] == "1" ? " checked='checked'" : "";
		$news_language = $data['news_language'];
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
if ((isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
	opentable($locale['402']);
} else {
	if (!isset($_POST['preview'])) {
		$news_subject = "";
		$news_cat = "0";
		$body = "";
		$body2 = "";
		$news_image = "";
		$news_image_t1 = "";
		$news_image_t2 = "";
		$news_visibility = 0;
		$news_draft = "";
		$news_sticky = "";
		$news_start = "";
		$news_end = "";
		$news_breaks = " checked='checked'";
		$news_comments = " checked='checked'";
		$news_ratings = " checked='checked'";
		$news_language = LANGUAGE;
	}
	opentable($locale['401']);
}
$result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
$news_cat_opts = array();
$news_cat_opts['0'] = $locale['424'];
if (dbrows($result)) {
	while ($data = dbarray($result)) {
		$news_cat_opts[$data['news_cat_id']] = $data['news_cat_name'];
	}
}
$visibility_opts = array();
$user_groups = getusergroups();
while (list($key, $user_group) = each($user_groups)) {
	$visibility_opts[$user_group['0']] = $user_group['1'];
}
echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink, array('enctype' => 1, 'downtime' => 0));
echo "<div class='text-right'>\n";
echo form_button($locale['436'], 'preview', 'preview-1', $locale['436'], array('class' => 'btn-primary m-r-10'));
echo form_button($locale['437'], 'save', 'save-1', $locale['437'], array('class' => 'btn-primary'));
echo "</div>\n";
echo "<hr/>\n";
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-9'>\n";
echo form_text($locale['422'], 'news_subject', 'news_subject', $news_subject, array('required' => 1, 'max_length' => 200, 'error_text' => $locale['450']));
echo "</div><div class='col-xs-12 col-sm-12 col-md-5 col-lg-3'>\n";
echo form_select($locale['423'], 'news_cat', 'news_cat', $news_cat_opts, $news_cat, array('placeholder' => $locale['choose'], 'width' => '100%'));
echo "</div>\n</div>\n";
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-9'>\n";
if ($news_image != "" && $news_image_t1 != "") {
	echo "<label><img src='".IMAGES_N_T.$news_image_t2."' alt='".$locale['439']."' /><br />\n";
	echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['421']."</label>\n";
	echo "<input type='hidden' name='news_image' value='".$news_image."' />\n";
	echo "<input type='hidden' name='news_image_t1' value='".$news_image_t1."' />\n";
	echo "<input type='hidden' name='news_image_t2' value='".$news_image_t2."' />\n";
} else {
	echo form_fileinput($locale['439'], 'news_image', 'news_image', IMAGES_N, '', array('thumbnail' => 1));
	echo "<div class='small m-b-10'>".sprintf($locale['440'], parsebytesize($settings['news_photo_max_b']))."</div>\n";
}
echo "</div><div class='col-xs-12 col-sm-12 col-md-5 col-lg-3'>\n";
if (multilang_table("NS")) {
	echo form_select($locale['global_ML100'], 'news_language', 'news_language', $language_opts, $news_language, array('placeholder' => $locale['choose'], 'width' => '100%'));
} else {
	echo form_hidden('', 'news_language', 'news_langugage', $news_language);
}
echo "</div>\n</div>\n";
echo "<hr/>\n";
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-9' style='padding-bottom:10px;'>\n";
echo form_datepicker($locale['427'], 'news_start', 'news_start', $news_start, array('class' => 'pull-left m-r-10'));
echo form_datepicker($locale['428'], 'news_end', 'news_end', $news_end, array('class' => 'm-r-10 pull-left', 'placeholder' => $locale['429']));
echo "</div><div class='col-xs-12 col-sm-12 col-md-5 col-lg-3'>\n";
echo form_select($locale['430'], 'news_visibility', 'news_visibility', $visibility_opts, $news_visibility, array('placeholder' => $locale['choose'], 'width' => '100%'));
echo "</div>\n</div>\n";
echo "<hr/>\n";
$fusion_mce = array();
if (!$settings['tinymce_enabled']) {
$fusion_mce = array('preview'=>1, 'html'=>1, 'autosize'=>1, 'form_name'=>'inputform');
}
echo form_textarea($locale['425'], 'body', 'body', $body, $fusion_mce);
echo "<hr/>\n";
echo form_textarea($locale['426'], 'body2', 'body2', $body2, $fusion_mce);
echo "<hr/>\n";
echo "<div class='well'>\n";
echo "<label><input type='checkbox' name='news_draft' value='yes'".$news_draft." /> ".$locale['431']."</label><br />\n";
echo "<label><input type='checkbox' name='news_sticky' value='yes'".$news_sticky." /> ".$locale['432']."</label><br />\n";
if ($settings['tinymce_enabled'] != 1) {
	echo "<label><input type='checkbox' name='line_breaks' value='yes'".$news_breaks." /> ".$locale['433']."</label><br />\n";
}
echo "<label><input type='checkbox' name='news_comments' value='yes' onclick='SetRatings();'".$news_comments." /> ".$locale['434']."</label>";
if ($settings['comments_enabled'] == "0") {
	echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
}
echo "<br />\n";
echo "<label><input type='checkbox' name='news_ratings' value='yes'".$news_ratings." /> ".$locale['435']."</label>";
if ($settings['ratings_enabled'] == "0") {
	echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
}
if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
	$sys = "";
	if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
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
if ((isset($_POST['edit']) && (isset($_POST['news_id']) && isnum($_POST['news_id']))) || (isset($_POST['preview']) && (isset($_POST['news_id']) && isnum($_POST['news_id']))) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
	echo form_hidden('', 'news_id', 'news_id', isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id']);
}
echo "</div>\n";
echo form_button($locale['436'], 'preview', 'preview-1', $locale['436'], array('class' => 'btn-primary m-r-10'));
echo form_button($locale['437'], 'save', 'save-1', $locale['437'], array('class' => 'btn-primary'));
echo closeform();
closetable();
echo "<script type='text/javascript'>\n"."function DeleteNews() {\n";
echo "return confirm('".$locale['451']."');\n}\n";
echo "function ValidateForm(frm) {\n"."if(frm.news_subject.value=='') {\n";
echo "alert('".$locale['450']."');\n"."return false;\n}\n}\n";
echo "function SetRatings() {\n"."if (inputform.news_comments.checked == false) {\n";
echo "inputform.news_ratings.checked = false;\n"."inputform.news_ratings.disabled = true;\n";
echo "} else {\n"."inputform.news_ratings.disabled = false;\n}\n}\n</script>\n";
require_once THEMES."templates/footer.php";
?>
