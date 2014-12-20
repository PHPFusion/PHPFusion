<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
require_once "../maincore.php";
if (!checkrights("BLOG") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/blog.php";

if (isset($_POST['cancel'])) { redirect(FUSION_SELF.$aidlink); }

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['blog_id']) && isnum($_GET['blog_id'])) {
	$del_data['blog_id'] = $_GET['blog_id'];
	$result = dbquery("SELECT blog_image, blog_image_t1, blog_image_t2 FROM ".DB_BLOG." WHERE blog_id='".$del_data['blog_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!empty($data['blog_image']) && file_exists(IMAGES_B.$data['blog_image'])) {
			unlink(IMAGES_B.$data['blog_image']);
		}
		if (!empty($data['blog_image_t1']) && file_exists(IMAGES_B_T.$data['blog_image_t1'])) {
			unlink(IMAGES_B_T.$data['blog_image_t1']);
		}
		if (!empty($data['blog_image_t2']) && file_exists(IMAGES_B_T.$data['blog_image_t2'])) {
			unlink(IMAGES_B_T.$data['blog_image_t2']);
		}
		$result = dbquery("DELETE FROM ".DB_BLOG." WHERE blog_id='".$_POST['blog_id']."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$_POST['blog_id']."' and comment_type='B'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$_POST['blog_id']."' and rating_type='B'");
		dbquery_insert(DB_BLOG, $del_data, 'delete');
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}


function blog_listing() {
	global $aidlink, $locale;
	$result = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_image, blog_cat_language FROM ".DB_BLOG_CATS." ".(multilang_table("NS") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_name");
	echo "<div class='m-t-20'>\n";
	echo opencollapse('blog-list');
	// uncategorized listing
	echo "<div class='panel panel-default'>\n";
	echo "<div class='panel-heading clearfix'>\n";
	echo "<div class='overflow-hide'>\n";
	echo "<h4 class='panel-title display-inline-block'><a ".collapse_header_link('blog-list', '0', '0', 'm-r-10').">".$locale['424']."</a></h4>\n";
	echo "<br/><span class='text-smaller text-uppercase strong'>".LANGUAGE."</span>";
	echo "</div>\n";
	echo "</div>\n"; // end panel heading
	echo "<div ".collapse_footer_link('blog-list','0', '0').">\n";
	echo "<ul class='list-group'>\n";
	$result2 = dbquery("SELECT blog_id, blog_subject, blog_image_t1, blog_blog, blog_draft FROM ".DB_BLOG." ".(multilang_table("NS") ? "WHERE blog_language='".LANGUAGE."'" : "")." AND blog_cat='0' ORDER BY blog_draft DESC, blog_sticky DESC, blog_datestamp DESC");
	if (dbrows($result2) > 0) {
		while ($data2 = dbarray($result2)) {
			echo "<li class='list-group-item'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			$img_thumb = ($data2['blog_image_t1']) ? IMAGES_B_T.$data2['blog_image_t1'] : IMAGES."imagenotfound70.jpg";
			echo thumbnail($img_thumb, '50px');
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<div><span class='strong text-dark'>".$data2['blog_subject']."</span><br/>".trim_word($data2['blog_blog'], '50')."</div>\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;blog_id=".$data2['blog_id']."'>".$locale['420']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;blog_id=".$data2['blog_id']."' onclick=\"return confirm('".$locale['451']."');\">".$locale['421']."</a>\n";
			echo "</div>\n";
			echo "</li>\n";
		}
	} else {
		echo "<div class='panel-body text-center'>\n";
		echo $locale['456'];
		echo "</div>\n";
	}
	// blog listing.
	echo "</ul>\n";
	echo "</div>\n"; // panel container
	echo "</div>\n"; // panel default

	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading clearfix'>\n";
			echo "<div class='btn-group pull-right m-t-5'>\n";
			echo "<a class='btn btn-sm btn-default' href='".ADMIN."blog_cats.php".$aidlink."&amp;action=edit&amp;cat_id=".$data['blog_cat_id']."'>".$locale['420']."</a>";
			echo "<a class='btn btn-sm btn-default' href='".ADMIN."blog_cats.php".$aidlink."&amp;action=delete&amp;cat_id=".$data['blog_cat_id']."' onclick=\"return confirm('".$locale['451b']."');\">".$locale['421']."</a>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide p-r-10'>\n";
			echo "<h4 class='panel-title display-inline-block'><a ".collapse_header_link('blog-list', $data['blog_cat_id'], '0', 'm-r-10').">".$data['blog_cat_name']."</a></h4>\n";
			echo "<br/><span class='text-smaller text-uppercase'>".$data['blog_cat_language']."</span>";
			echo "</div>\n"; /// end overflow-hide
			echo "</div>\n"; // end panel heading
			echo "<div ".collapse_footer_link('blog-list', $data['blog_cat_id'], '0').">\n";
			echo "<ul class='list-group'>\n";
			$result2 = dbquery("SELECT blog_id, blog_subject, blog_image_t1, blog_blog, blog_draft FROM ".DB_BLOG." ".(multilang_table("NS") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." blog_cat='".$data['blog_cat_id']."' ORDER BY blog_draft DESC, blog_sticky DESC, blog_datestamp DESC");
			if (dbrows($result2) > 0) {
				while ($data2 = dbarray($result2)) {
					echo "<li class='list-group-item'>\n";
					echo "<div class='pull-left m-r-10'>\n";
					$img_thumb = ($data2['blog_image_t1']) ? IMAGES_B_T.$data2['blog_image_t1'] : IMAGES."imagenotfound70.jpg";
					echo thumbnail($img_thumb, '50px');
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<div><span class='strong text-dark'>".$data2['blog_subject']."</span><br/>".trim_word($data2['blog_blog'], '50')."</div>\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;blog_id=".$data2['blog_id']."'>".$locale['420']."</a> -\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;blog_id=".$data2['blog_id']."' onclick=\"return confirm('".$locale['451']."');\">".$locale['421']."</a>\n";
					echo "</div>\n";
					echo "</li>\n";
				}
			} else {
				echo "<div class='panel-body text-center'>\n";
				echo $locale['456'];
				echo "</div>\n";
			}
			// blog listing.
			echo "</ul>\n";
			echo "</div>\n"; // panel container
			echo "</div>\n"; // panel default
		}
	}
	echo closecollapse();
	echo "</div>\n";
}

function blog_form() {
	global $userdata, $locale, $settings, $aidlink, $language_opts, $defender;

	/* Something like this is needed at some point before release.
	
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
		$editblogaction = FUSION_SELF.$aidlink."&action=edit";
		echo "<div class='pull-left'>\n";
		echo openform('editblog', 'editblog', 'post', $editblogaction, array('downtime' => 0));
		echo "<select name='blog_id' class='textbox' style='width:250px'>\n".$editlist."</select>\n";
		echo "<input type='submit' name='edit' value='".$locale['420']."' class='button' />\n";
		echo "<input type='submit' name='delete' value='".$locale['421']."' onclick='return Deleteblog();' class='button' />\n";
		echo closeform();
		echo "</div>\n";
		closetable();
	}
*/
	$data = array();
	if (isset($_POST['save'])) {
		$error = "";
		$data['blog_id'] = isset($_POST['blog_id']) ? form_sanitizer($_POST['blog_id'], '', 'blog_id') : '';
		$data['blog_subject'] = form_sanitizer($_POST['blog_subject'], '', 'blog_subject');
		$data['blog_cat'] = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
		$data['blog_name'] = $userdata['user_id'];
		if (!empty($_FILES['blog_image']['name']) && is_uploaded_file($_FILES['blog_image']['tmp_name'])) {
			//require_once INCLUDES."photo_functions_include.php";
			require_once INCLUDES."infusions_include.php";
			$image = "blog_image";
			// Left blank to use the image name as it is
			$name = $_FILES['blog_image']['name'];
			// Upload folder
			$folder = IMAGES_B;
			$thumb_folder = IMAGES_B_T;
			// Maximum image width in pixels
			$width = $settings['blog_photo_max_w'];
			// Maximum image height in pixels
			$height = $settings['blog_photo_max_w'];
			// Maximum file size in bytes
			$size = $settings['blog_photo_max_b'];
			$upload = upload_image($image, $name, $folder, $width, $height, $size, FALSE, TRUE, TRUE, 1, $thumb_folder, '_t1', $settings['blog_thumb_w'], $settings['blog_thumb_h'],
		   	0, IMAGES_B_T, "_t2", $settings['blog_photo_w'], $settings['blog_photo_h']
			);
			if ($upload['error'] != 0) {
				$defender->stop();
				switch ($upload['error']) { // 415a, 416a, 415b, 419a
					case 1:
						$defender->addNotice(sprintf($locale['414'], parsebytesize($settings['blog_photo_max_b'])));
						// Invalid file size
						break;
					case 2:
						// Unsupported image type
						$defender->addNotice(sprintf($locale['415'], ".gif .jpg .png"));
						break;
					case 3:
						// Invalid image resolution
						$defender->addNotice(sprintf($locale['416'], $settings['blog_photo_max_w']." x ".$settings['blog_photo_max_h']));
						break;
					case 4:
						// Invalid query string
						$defender->addNotice($locale['417']);
						break;
					case 5:
						// Image not uploaded
						$defender->addNotice($locale['417']);
						break;
				}
				$data['blog_image'] = (isset($_POST['blog_image']) ? $_POST['blog_image'] : "");
				$data['blog_image_t1'] = (isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "");
				$data['blog_image_t2'] = (isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "");
				$data['blog_ialign'] = (isset($_POST['blog_ialign']) ? $_POST['blog_ialign'] : "pull-left");
				} else {
				// !upload success
				$data['blog_image'] = $upload['image_name'];
				$data['blog_image_t1'] = $upload['thumb1_name'];
				$data['blog_image_t2'] = $upload['thumb2_name'];
				$data['blog_ialign'] = (isset($_POST['blog_ialign']) ? $_POST['blog_ialign'] : "pull-left");
			}
			/* Pending for code reviews on the forum.
			$image = $_FILES['blog_image'];
			$image_name = stripfilename(str_replace(" ", "_", strtolower(substr($image['name'], 0, strrpos($image['name'], ".")))));
			$image_ext = strtolower(strrchr($image['name'], "."));
			if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $image_name)) {
				$defender->stop();
				$defender->addNotice($locale['413']);
				$error = 1;
			} */
		} else {
			$data['blog_image'] = (isset($_POST['blog_image']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_image']) ? $_POST['blog_image'] : "") : "");
			$data['blog_image_t1'] = (isset($_POST['blog_image_t1']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "") : "");
			$data['blog_image_t2'] = (isset($_POST['blog_image_t2']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "") : "");
			$data['blog_ialign'] = (isset($_POST['blog_ialign']) ? $_POST['blog_ialign'] : "pull-left");
		}
		//$data['blog_blog'] = form_sanitizer($_POST['blog_blog'], '', 'blog_blog'); // Destroys HTML coding,
		//$data['blog_extended'] = form_sanitizer($_POST['blog_extended'], '', 'blog_extended'); // table-safe values, // Destroys HTML coding.
		$data['blog_blog'] = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['blog_blog'])); // Needed for HTML to work
		$data['blog_extended'] = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['blog_extended'])); // Needed for HTML to work
		$data['blog_keywords'] = form_sanitizer($_POST['blog_keywords'], '', 'blog_keywords');
		$data['blog_datestamp'] = form_sanitizer($_POST['blog_datestamp'], time(), 'blog_datestamp');
		$data['blog_start'] = form_sanitizer($_POST['blog_start'], 0, 'blog_start');
		$data['blog_end'] = form_sanitizer($_POST['blog_end'], 0, 'blog_end');
		$data['blog_visibility'] = form_sanitizer($_POST['blog_visibility'], '0', 'blog_visibility');
		$data['blog_draft'] = isset($_POST['blog_draft']) ? "1" : "0";
		$data['blog_sticky'] = isset($_POST['blog_sticky']) ? "1" : "0";
		$data['blog_allow_comments'] = isset($_POST['blog_allow_comments']) ? "1" : "0";
		$data['blog_allow_ratings'] = isset($_POST['blog_allow_ratings']) ? "1" : "0";
		$data['blog_language'] = form_sanitizer($_POST['blog_language'], '', 'blog_language');
		if ($settings['tinymce_enabled'] != 1) {
			$data['blog_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
		} else {
			$data['blog_breaks'] = "n";
		}
		if (isset($_POST['blog_id']) && isnum($_POST['blog_id']) && !defined('FUSION_NULL')) {
			$result = dbquery("SELECT blog_image, blog_image_t1, blog_image_t2, blog_sticky, blog_datestamp FROM ".DB_BLOG." WHERE blog_id='".$_POST['blog_id']."'");
			if (dbrows($result)) {
				$data2 = dbarray($result);
				if ($data['blog_sticky'] == "1") {
					// reset other sticky.
					$result = dbquery("UPDATE ".DB_BLOG." SET blog_sticky='0' WHERE blog_sticky='1'");
				}
				if (isset($_POST['del_image'])) {
					if (!empty($data['blog_image']) && file_exists(IMAGES_B.$data['blog_image'])) {
						unlink(IMAGES_B.$data['blog_image']);
					}
					if (!empty($data['blog_image_t1']) && file_exists(IMAGES_B_T.$data['blog_image_t1'])) {
						unlink(IMAGES_B_T.$data['blog_image_t1']);
					}
					if (!empty($data['blog_image_t2']) && file_exists(IMAGES_B_T.$data['blog_image_t2'])) {
						unlink(IMAGES_B_T.$data['blog_image_t2']);
					}
					$data['blog_image'] = "";
					$data['blog_image_t1'] = "";
					$data['blog_image_t2'] = "";
				}
				dbquery_insert(DB_BLOG, $data, 'update');
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		} else {
			if ($data['blog_sticky'] == "1") {
				$result = dbquery("UPDATE ".DB_BLOG." SET blog_sticky='0' WHERE blog_sticky='1'");
			}
			dbquery_insert(DB_BLOG, $data, 'save');
		}
	}
	if ($settings['tinymce_enabled']) {
		echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
	} else {
		require_once INCLUDES."html_buttons_include.php";
	}
	if (isset($_GET['status'])) {
		if ($_GET['status'] == "success") {
			$message = $locale['410'];
		} elseif ($_GET['status'] == "updated") {
			$message = $locale['411'];
		} elseif ($_GET['status'] == "del") {
			$message = $locale['412'];
		}
		if ($message) {
			echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
		}
	}
	$result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("NS") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_name");
	$blog_cat_opts = array();
	$blog_cat_opts['0'] = $locale['424'];
	if (dbrows($result)) {
		while ($odata = dbarray($result)) {
			$blog_cat_opts[$odata['blog_cat_id']] = $odata['blog_cat_name'];
		}
	}
	$visibility_opts = array();
	$user_groups = getusergroups();
	while (list($key, $user_group) = each($user_groups)) {
		$visibility_opts[$user_group['0']] = $user_group['1'];
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['blog_id']) && isnum($_POST['blog_id'])) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
		$result = dbquery("SELECT * FROM ".DB_BLOG." WHERE blog_id='".(isset($_POST['blog_id']) ? $_POST['blog_id'] : $_GET['blog_id'])."'");
		if (dbrows($result)) {
			$data2 = dbarray($result);
			$data += array(
				'blog_subject' => (!empty($_POST['blog_subject'])) ? $_POST['blog_subject'] : $data2['blog_subject'],
				'blog_cat' => (!empty($_POST['blog_cat'])) ? $_POST['blog_cat'] : $data2['blog_cat'],
				'blog_blog' => (!empty($_POST['body'])) ? $_POST['body'] : $data2['blog_blog'], // phpentities(stripslashes($data['blog_blog'])),
				'blog_extended' => (!empty($_POST['body'])) ? $_POST['body'] : $data2['blog_extended'], // phpentities(stripslashes($data['blog_extended']));
				'blog_keywords' => (!empty($_POST['blog_keywords'])) ? $_POST['blog_keywords'] : $data2['blog_keywords'],
				'blog_datestamp' => $data2['blog_datestamp'],
				'blog_start' => (!empty($_POST['blog_start'])) ? $_POST['blog_start'] : $data2['blog_start'],
				'blog_end' => (!empty($_POST['blog_end'])) ? $_POST['blog_end'] : $data2['blog_end'],
				'blog_image' => (!empty($_POST['blog_image'])) ? $_POST['blog_image'] : $data2['blog_image'],
				'blog_image_t1' => (!empty($_POST['blog_image_t1'])) ? $_POST['blog_image_t1'] : $data2['blog_image_t1'],
				'blog_image_t2' => (!empty($_POST['blog_image_t2'])) ? $_POST['blog_image_t2'] : $data2['blog_image_t2'],
				'blog_ialign' => (!empty($_POST['blog_ialign'])) ? $_POST['blog_ialign'] : $data2['blog_ialign'],
				'blog_visibility' => (!empty($_POST['blog_visibility'])) ? $_POST['blog_visibility'] : $data2['blog_visibility'],
				'blog_draft' => (!empty($_POST['blog_draft'])) ? "1" : $data2['blog_draft'] ? "1" : '',
				'blog_sticky' => (!empty($_POST['blog_sticky'])) ? "1" : $data2['blog_sticky'] ? "1" : '',
				'blog_breaks' => (!empty($_POST['blog_breaks'])) ? "1" : $data2['blog_breaks'] ? "1" : '',
				'blog_allow_comments' => (!empty($_POST['blog_allow_comments'])) ? "1" : $data2['blog_allow_comments'] ? "1" : '',
				'blog_allow_ratings' => (!empty($_POST['blog_allow_ratings'])) ? "1" : $data2['blog_allow_ratings'] ? "1" : '',
				'blog_language' => (!empty($_POST['blog_language'])) ? $_POST['blog_language'] : $data2['blog_language']
			);
		} else {
//			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$data['blog_draft'] = '0';
		$data['blog_sticky'] = '0';
		$data['blog_blog'] = '';
		$data['blog_datestamp'] = time();
		$data['blog_extended'] = '';
		$data['blog_keywords'] = '';
		$data['blog_breaks'] = " 1";
		$data['blog_allow_comments'] = " 1";
		$data['blog_allow_ratings'] = " 1";
		$data['blog_language'] = LANGUAGE;
		$data['blog_visibility'] = '0';
		$data['blog_subject'] = '';
		$data['blog_start'] = '';
		$data['blog_end'] = '';
		$data['blog_cat'] = '0';
		$data['blog_image'] = '';
		$data['blog_ialign'] = 'pull-left';
	}

	if (isset($_POST['preview'])) {
		$data['blog_subject'] = form_sanitizer($_POST['blog_subject'], '', 'blog_subject');
		$data['blog_cat'] = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
		$data['blog_language'] = form_sanitizer($_POST['blog_language'], '', 'blog_language');
		$data['blog_blog'] = phpentities(stripslash($_POST['blog_blog']));
		$data['blog_blog'] = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslash($_POST['blog_blog']));
		$data['blog_extended'] = '';
		if ($_POST['blog_extended']) {
			$data['blog_extended'] = phpentities(stripslash($_POST['blog_extended']));
			$data['blog_extended'] = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslash($_POST['blog_extended']));
		}
		$data['blog_keywords'] = form_sanitizer($_POST['blog_keywords'], '', 'blog_keywords');
		$data['blog_breaks'] = "";
		if (isset($_POST['line_breaks'])) {
			$data['blog_breaks'] = " 1";
			$data['blog_blog'] = nl2br($data['blog_blog']);
			if ($data['blog_extended']) {
				$data['blog_extended'] = nl2br($data['blog_extended']);
			}
		}
		$data['blog_start'] = (isset($_POST['blog_start']) && $_POST['blog_start']) ? $_POST['blog_start'] : '';
		$data['blog_end'] = (isset($_POST['blog_end']) && $_POST['blog_end']) ? $_POST['blog_end'] : '';
		$data['blog_image'] = isset($_POST['blog_image']) ? $_POST['blog_image'] : '';
		$data['blog_image_t1'] = (isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "");
		$data['blog_image_t2'] = (isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "");
		$data['blog_ialign'] = (isset($_POST['blog_ialign']) ? $_POST['blog_ialign'] : "pull-left");
		$data['blog_visibility'] = isnum($_POST['blog_visibility']) ? $_POST['blog_visibility'] : "0";
		$data['blog_draft'] = isset($_POST['blog_draft']) ? " 1" : "";
		$data['blog_sticky'] = isset($_POST['blog_sticky']) ? " 1" : "";
		$data['blog_allow_comments'] = isset($_POST['blog_allow_comments']) ? " 1" : "";
		$data['blog_allow_ratings'] = isset($_POST['blog_allow_ratings']) ? " 1" : "";
		$data['blog_datestamp'] = isset($_POST['blog_datestamp']) ? $_POST['blog_datestamp'] : '';
		if (!defined('FUSION_NULL')) {
			echo openmodal('blog_preview', 'blog Preview');
			echo $data['blog_blog'];
			echo "<hr/>\n";
			if (isset($data['blog_extended'])) {
				echo $data['blog_extended'];
			}
			echo closemodal();
		}
	}
	$formaction = FUSION_SELF.$aidlink;
	if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['blog_id']) && isnum($_GET['blog_id'])) {
		$formaction = FUSION_SELF.$aidlink."&action=edit&blog_id=".$_GET['blog_id'];
	}

	echo "<div class='m-t-20'>\n";
	// remove downtime after beta.
	
	echo openform('inputform', 'inputform', 'post', $formaction, array('enctype' => 1, 'downtime' => 0));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
	echo form_text($locale['422'], 'blog_subject', 'blog_subject', $data['blog_subject'], array('required' => 1, 'max_length' => 200, 'error_text' => $locale['450']));
	echo "<div class='pull-left m-r-10 display-inline-block'>\n";
	echo form_datepicker($locale['427'], 'blog_start', 'blog_start', $data['blog_start'], array('placeholder' => $locale['429']));
	echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
	echo form_datepicker($locale['428'], 'blog_end', 'blog_end', $data['blog_end'], array('placeholder' => $locale['429']));
	echo "</div>\n";
	echo "</div>\n";
	
	echo "<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
	openside('');
	echo form_select_tree($locale['423'], "blog_cat", "blog_cat", $data['blog_cat'], array("parent_value" => $locale['424']), DB_BLOG_CATS, "blog_cat_name", "blog_cat_id", "blog_cat_parent");
	echo form_button($locale['cancel'], 'cancel', 'cancel', $locale['cancel'], array('class' => 'btn-default btn-sm m-r-10'));
	echo form_button($locale['437'], 'save', 'save-1', $locale['437'], array('class' => 'btn-primary btn-sm'));
	closeside();
	echo "</div>\n</div>\n";
	
	// second row
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
	if ($data['blog_image'] != "" && $data['blog_image_t1'] != "") {
		echo "<label><img src='".IMAGES_B_T.$data['blog_image_t1']."' alt='".$locale['439']."' /><br />\n";
		echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['421']."</label>\n";
		echo "<input type='hidden' name='blog_image' value='".$data['blog_image']."' />\n";
		echo "<input type='hidden' name='blog_image_t1' value='".$data['blog_image_t1']."' />\n";
		echo "<input type='hidden' name='blog_image_t2' value='".$data['blog_image_t2']."' />\n";
		$options = array('pull-left'=>$locale['left'], 'blog-img-center'=>$locale['center'], 'pull-right'=>$locale['right']);
		echo form_select($locale['442'], 'blog_ialign', 'blog_ialign', $options, $data['blog_ialign']);
		} else {
		echo form_fileinput($locale['439'], 'blog_image', 'blog_image', IMAGES_B, '', array('thumbnail' => IMAGES_B_T, 'type' => 'image'));
		echo "<div class='small m-b-10'>".sprintf($locale['440'], parsebytesize($settings['blog_photo_max_b']))."</div>\n";
		$options = array('pull-left'=>$locale['left'], 'blog-img-center'=>$locale['center'], 'pull-right'=>$locale['right']);
		echo form_select($locale['442'], 'blog_ialign', 'blog_ialign', $options, $data['blog_ialign']);
}	
	$fusion_mce = array();
	if (!$settings['tinymce_enabled']) {
		$fusion_mce = array('preview' => 1, 'html' => 1, 'autosize' => 1, 'form_name' => 'inputform');
	}
	echo form_textarea($locale['425'], 'blog_blog', 'blog_blog', $data['blog_blog'], $fusion_mce);
	echo form_textarea($locale['426'], 'blog_extended', 'blog_extended', $data['blog_extended'], $fusion_mce);
	echo form_select($locale['443'], 'blog_keywords', 'blog_keywords', array(), $data['blog_keywords'], array('required' => 1, 'max_length' => 320, 'width'=>'100%', 'error_text' => $locale['457'], 'tags'=>1, 'multiple' => 1));
	echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
	openside('');
	if (multilang_table("NS")) {
		echo form_select($locale['global_ML100'], 'blog_language', 'blog_language', $language_opts, $data['blog_language'], array('placeholder' => $locale['choose'], 'width' => '100%'));
	} else {
		echo form_hidden('', 'blog_language', 'blog_langugage', $data['blog_language']);
	}
	echo form_hidden('', 'blog_datestamp', 'blog_datestamp', $data['blog_datestamp']);
	echo form_select($locale['430'], 'blog_visibility', 'blog_visibility', $visibility_opts, $data['blog_visibility'], array('placeholder' => $locale['choose'], 'width' => '100%'));
	closeside();
	openside('');
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
			$sys = $locale['455'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['453'];
		} else {
			$sys = $locale['454'];
		}
		echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['452'], $sys)."</span><br/>\n";
	}
	echo "<label><input type='checkbox' name='blog_draft' value='yes'".($data['blog_draft'] ? "checked='checked'" : "")." /> ".$locale['431']."</label><br />\n";
	echo "<label><input type='checkbox' name='blog_sticky' value='yes'".($data['blog_sticky'] ? "checked='checked'" : "")."  /> ".$locale['432']."</label><br />\n";
	if ($settings['tinymce_enabled'] != 1) {
		echo "<label><input type='checkbox' name='line_breaks' value='yes'".($data['blog_breaks'] ? "checked='checked'" : "")." /> ".$locale['433']."</label><br />\n";
	}
	echo "<label><input type='checkbox' name='blog_allow_comments' value='yes' onclick='SetRatings();'".($data['blog_allow_comments'] ? "checked='checked'" : "")." /> ".$locale['434']."</label><br/>";
	echo "<label><input type='checkbox' name='blog_allow_ratings' value='yes'".($data['blog_allow_ratings'] ? "checked='checked'" : "")." /> ".$locale['435']."</label>";
	closeside();
	if (isset($_GET['action']) && isset($_GET['blog_id']) && isnum($_GET['blog_id']) || (isset($_POST['preview']) && (isset($_POST['blog_id']) && isnum($_POST['blog_id']))) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
		$blog_id = isset($_GET['blog_id']) && isnum($_GET['blog_id']) ? $_GET['blog_id'] : '';
		echo form_hidden('', 'blog_id', 'blog_id', $blog_id);
	}
	echo "</div>\n</div>\n";
	echo form_button($locale['436'], 'preview', 'preview-1', $locale['436'], array('class' => 'btn-primary m-r-10'));
	echo form_button($locale['437'], 'save', 'save-1', $locale['437'], array('class' => 'btn-primary'));
	echo closeform();
	echo "</div>\n";
}

$master_title['title'][] = $locale['400'];
$master_title['id'][] = 'blog';
$master_title['icon'] = '';

$master_title['title'][] = $locale['401'];
$master_title['id'][] = 'nform';
$master_title['icon'] = '';

$tab_active = tab_active($master_title, 1);

opentable($locale['405']);
echo opentab($master_title, $tab_active, 'blog');
echo opentabbody($master_title['title'][0], 'blog', $tab_active);
blog_listing();
echo closetabbody();
echo opentabbody($master_title['title'][1], 'nform', $tab_active);
blog_form();
echo closetabbody();
echo closetab();
closetable();

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['blog_id'])) {
	add_to_jquery("
		// change the name of the second tab and activate it.
		$('#tab-nformAdd-blog').text('".$locale['402']."');
		$('#blog a:last').tab('show');
		");
}

require_once THEMES."templates/footer.php";
?>