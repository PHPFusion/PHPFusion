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

if (isset($_POST['cancel'])) {
	redirect(FUSION_SELF.$aidlink);
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['news_id']) && isnum($_GET['news_id'])) {
	$del_data['news_id'] = $_GET['news_id'];
	$result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS." WHERE news_id='".$del_data['news_id']."' LIMIT 1");
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
		//redirect(FUSION_SELF.$aidlink."&status=del");
		dbquery_insert(DB_NEWS, $del_data, 'delete');
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}


function news_listing() {
	global $aidlink, $locale;
	$result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_image, news_cat_language FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
	echo "<div class='m-t-20'>\n";
	echo opencollapse('news-list');
	// uncategorized listing
	echo "<div class='panel panel-default'>\n";
	echo "<div class='panel-heading clearfix'>\n";
	echo "<div class='overflow-hide'>\n";
	echo "<h4 class='panel-title display-inline-block'><a ".collapse_header_link('news-list', '0', '0', 'm-r-10').">".$locale['424']."</a></h4>\n";
	echo "<br/><span class='text-smaller text-uppercase strong'>".LANGUAGE."</span>";
	echo "</div>\n";
	echo "</div>\n"; // end panel heading
	echo "<div ".collapse_footer_link('news-list','0', '0').">\n";
	echo "<ul class='list-group'>\n";
	$result2 = dbquery("SELECT news_id, news_subject, news_image_t1, news_news, news_draft FROM ".DB_NEWS." ".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."'" : "")." AND news_cat='0' ORDER BY news_draft DESC, news_sticky DESC, news_datestamp DESC");
	if (dbrows($result2) > 0) {
		while ($data2 = dbarray($result2)) {
			echo "<li class='list-group-item'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			$img_thumb = ($data2['news_image_t1']) ? IMAGES_N_T.$data2['news_image_t1'] : IMAGES."imagenotfound70.jpg";
			echo thumbnail($img_thumb, '50px');
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<div><span class='strong text-dark'>".$data2['news_subject']."</span><br/>".trim_word($data2['news_news'], '50')."</div>\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;news_id=".$data2['news_id']."'>".$locale['420']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;news_id=".$data2['news_id']."' onclick=\"return confirm('".$locale['451']."');\">".$locale['421']."</a>\n";
			echo "</div>\n";
			echo "</li>\n";
		}
	} else {
		echo "<div class='panel-body text-center'>\n";
		echo $locale['456'];
		echo "</div>\n";
	}
	// news listing.
	echo "</ul>\n";
	echo "</div>\n"; // panel container
	echo "</div>\n"; // panel default

	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading clearfix'>\n";
			echo "<div class='btn-group pull-right m-t-5'>\n";
			echo "<a class='btn btn-sm btn-default' href='".ADMIN."news_cats.php".$aidlink."&amp;action=edit&amp;cat_id=".$data['news_cat_id']."'>".$locale['420']."</a>";
			echo "<a class='btn btn-sm btn-default' href='".ADMIN."news_cats.php".$aidlink."&amp;action=delete&amp;cat_id=".$data['news_cat_id']."' onclick=\"return confirm('".$locale['451b']."');\">".$locale['421']."</a>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide p-r-10'>\n";
			echo "<h4 class='panel-title display-inline-block'><a ".collapse_header_link('news-list', $data['news_cat_id'], '0', 'm-r-10').">".$data['news_cat_name']."</a></h4>\n";
			echo "<br/><span class='text-smaller text-uppercase'>".$data['news_cat_language']."</span>";
			echo "</div>\n"; /// end overflow-hide
			echo "</div>\n"; // end panel heading
			echo "<div ".collapse_footer_link('news-list', $data['news_cat_id'], '0').">\n";
			echo "<ul class='list-group'>\n";
			$result2 = dbquery("SELECT news_id, news_subject, news_image_t1, news_news, news_draft FROM ".DB_NEWS." ".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."' AND" : "WHERE")." news_cat='".$data['news_cat_id']."' ORDER BY news_draft DESC, news_sticky DESC, news_datestamp DESC");
			if (dbrows($result2) > 0) {
				while ($data2 = dbarray($result2)) {
					echo "<li class='list-group-item'>\n";
					echo "<div class='pull-left m-r-10'>\n";
					$img_thumb = ($data2['news_image_t1']) ? IMAGES_N_T.$data2['news_image_t1'] : IMAGES."imagenotfound70.jpg";
					echo thumbnail($img_thumb, '50px');
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<div><span class='strong text-dark'>".$data2['news_subject']."</span><br/>".trim_word($data2['news_news'], '50')."</div>\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;news_id=".$data2['news_id']."'>".$locale['420']."</a> -\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;news_id=".$data2['news_id']."' onclick=\"return confirm('".$locale['451']."');\">".$locale['421']."</a>\n";
					echo "</div>\n";
					echo "</li>\n";
				}
			} else {
				echo "<div class='panel-body text-center'>\n";
				echo $locale['456'];
				echo "</div>\n";
			}
			// news listing.
			echo "</ul>\n";
			echo "</div>\n"; // panel container
			echo "</div>\n"; // panel default
		}
	}
	echo closecollapse();
	echo "</div>\n";
}

function news_form() {
	global $userdata, $locale, $settings, $aidlink, $language_opts, $defender;
	$data = array();
	if (isset($_POST['save'])) {
		$error = "";
		$data['news_id'] = isset($_POST['news_id']) ? form_sanitizer($_POST['news_id'], '', 'news_id') : '';
		$data['news_subject'] = form_sanitizer($_POST['news_subject'], '', 'news_subject');
		$data['news_cat'] = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
		if (!empty($_FILES['news_image']['name']) && is_uploaded_file($_FILES['news_image']['tmp_name'])) {
			//require_once INCLUDES."photo_functions_include.php";
			require_once INCLUDES."infusions_include.php";
			$image = "news_image";
			// Left blank to use the image name as it is
			$name = $_FILES['news_image']['name'];
			// Upload folder
			$folder = IMAGES_N;
			$thumb_folder = IMAGES_N_T;
			// Maximum image width in pixels
			$width = $settings['news_photo_max_w'];
			// Maximum image height in pixels
			$height = $settings['news_photo_max_w'];
			// Maximum file size in bytes
			$size = $settings['news_photo_max_b'];
			$upload = upload_image($image, $name, $folder, $width, $height, $size, FALSE, TRUE, TRUE, 1, $thumb_folder, '_t1', $settings['news_thumb_w'], $settings['news_thumb_h'],
		   	0, IMAGES_N_T, "_t2", $settings['news_photo_w'], $settings['news_photo_h']
			);
			if ($upload['error'] != 0) {
				$defender->stop();
				switch ($upload['error']) { // 415a, 416a, 415b, 419a
					case 1:
						$defender->addNotice(sprintf($locale['414'], parsebytesize($settings['news_photo_max_b'])));
						// Invalid file size
						break;
					case 2:
						// Unsupported image type
						$defender->addNotice(sprintf($locale['415'], ".gif .jpg .png"));
						break;
					case 3:
						// Invalid image resolution
						$defender->addNotice(sprintf($locale['416'], $settings['news_photo_max_w']." x ".$settings['news_photo_max_h']));
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
				$data['news_image'] = (isset($_POST['news_image']) ? $_POST['news_image'] : "");
				$data['news_image_t1'] = (isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "");
				$data['news_image_t2'] = (isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "");
			} else {
				// !upload success
				$data['news_image'] = $upload['image_name'];
				$data['news_image_t1'] = $upload['thumb1_name'];
				$data['news_image_t2'] = $upload['thumb2_name'];
			}
			/* Pending for code reviews on the forum.
			$image = $_FILES['news_image'];
			$image_name = stripfilename(str_replace(" ", "_", strtolower(substr($image['name'], 0, strrpos($image['name'], ".")))));
			$image_ext = strtolower(strrchr($image['name'], "."));
			if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $image_name)) {
				$defender->stop();
				$defender->addNotice($locale['413']);
				$error = 1;
			} */
		} else {
			$data['news_image'] = (isset($_POST['news_image']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['news_image']) ? $_POST['news_image'] : "") : "");
			$data['news_image_t1'] = (isset($_POST['news_image_t1']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['news_image_t1']) ? $_POST['news_image_t1'] : "") : "");
			$data['news_image_t2'] = (isset($_POST['news_image_t2']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['news_image_t2']) ? $_POST['news_image_t2'] : "") : "");
		}
		//$data['news_news'] = form_sanitizer($_POST['news_news'], '', 'news_news'); // Destroys HTML coding,
		//$data['news_extended'] = form_sanitizer($_POST['news_extended'], '', 'news_extended'); // table-safe values, // Destroys HTML coding.
		$data['news_news'] = addslash($_POST['news_news']);
		$data['news_extended'] = addslash($_POST['news_extended']);
		$data['news_datestamp'] = time();
		$data['news_start_date'] = 0;
		$data['news_end_date'] = 0;
		$data['news_start_date'] = form_sanitizer($_POST['news_start'], '', 'news_start');
		$data['news_end_date'] = form_sanitizer($_POST['news_end'], '', 'news_end');
		$data['news_visibility'] = form_sanitizer($_POST['news_visibility'], '0', 'news_visibility');
		$data['news_draft'] = isset($_POST['news_draft']) ? "1" : "0";
		$data['news_sticky'] = isset($_POST['news_sticky']) ? "1" : "0";
		$data['news_allow_comments'] = isset($_POST['news_allow_comments']) ? "1" : "0";
		$data['news_allow_ratings'] = isset($_POST['news_allow_ratings']) ? "1" : "0";
		$data['news_language'] = form_sanitizer($_POST['news_language'], '', 'news_language');
		if ($settings['tinymce_enabled'] != 1) {
			$data['news_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
		} else {
			$data['news_breaks'] = "n";
		}
		if (isset($_POST['news_id']) && isnum($_POST['news_id']) && !defined('FUSION_NULL')) {
			$result = dbquery("SELECT news_image, news_image_t1, news_image_t2, news_sticky FROM ".DB_NEWS." WHERE news_id='".$_POST['news_id']."' LIMIT 1");
			if (dbrows($result)) {
				$data2 = dbarray($result);
				if ($data['news_sticky'] == "1") {
					// reset other sticky.
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
					$data['news_image'] = "";
					$data['news_image_t1'] = "";
					$data['news_image_t2'] = "";
				}
				dbquery_insert(DB_NEWS, $data, 'update');
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		} else {
			if ($data['news_sticky'] == "1") {
				$result = dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
			}
			dbquery_insert(DB_NEWS, $data, 'save');
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
	$result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
	$news_cat_opts = array();
	$news_cat_opts['0'] = $locale['424'];
	if (dbrows($result)) {
		while ($odata = dbarray($result)) {
			$news_cat_opts[$odata['news_cat_id']] = $odata['news_cat_name'];
		}
	}
	$visibility_opts = array();
	$user_groups = getusergroups();
	while (list($key, $user_group) = each($user_groups)) {
		$visibility_opts[$user_group['0']] = $user_group['1'];
	}
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
		$result = dbquery("SELECT news_subject, news_cat, news_news, news_extended, news_start, news_end, news_image, news_image_t1, news_image_t2, news_visibility, news_draft, news_sticky, news_breaks, news_allow_comments, news_allow_ratings, news_language FROM ".DB_NEWS." WHERE news_id='".(isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'])."' LIMIT 1");
		if (dbrows($result)) {
			$data2 = dbarray($result);
			$data += array(
				'news_subject' => (!empty($_POST['news_subject'])) ? $_POST['news_subject'] : $data2['news_subject'],
				'news_cat' => (!empty($_POST['news_cat'])) ? $_POST['news_cat'] : $data2['news_cat'],
				'news_news' => (!empty($_POST['body'])) ? $_POST['body'] : $data2['news_news'], // phpentities(stripslashes($data['news_news'])),
				'news_extended' => (!empty($_POST['body'])) ? $_POST['body'] : $data2['news_extended'], // phpentities(stripslashes($data['news_extended']));
				'news_start' => (!empty($_POST['news_start'])) ? $_POST['news_start'] : $data2['news_start'],
				'news_end' => (!empty($_POST['news_end'])) ? $_POST['news_end'] : $data2['news_end'],
				'news_datestamp' => (!empty($_POST['news_datestamp'])) ? $_POST['news_datestamp'] : $data2['news_datestamp'],
				'news_image' => (!empty($_POST['news_image'])) ? $_POST['news_image'] : $data2['news_image'],
				'news_image_t1' => (!empty($_POST['news_image_t1'])) ? $_POST['news_image_t1'] : $data2['news_image_t1'],
				'news_image_t2' => (!empty($_POST['news_image_t2'])) ? $_POST['news_image_t2'] : $data2['news_image_t2'],
				'news_visibility' => (!empty($_POST['news_visibility'])) ? $_POST['news_visibility'] : $data2['news_visibility'],
				'news_draft' => (!empty($_POST['news_draft'])) ? "1" : $data2['news_draft'] ? "1" : '',
				'news_sticky' => (!empty($_POST['news_sticky'])) ? "1" : $data2['news_sticky'] ? "1" : '',
				'news_breaks' => (!empty($_POST['news_breaks'])) ? "1" : $data2['news_breaks'] ? "1" : '',
				'news_allow_comments' => (!empty($_POST['news_allow_comments'])) ? "1" : $data2['news_allow_comments'] ? "1" : '',
				'news_allow_ratings' => (!empty($_POST['news_allow_ratings'])) ? "1" : $data2['news_allow_ratings'] ? "1" : '',
				'news_language' => (!empty($_POST['news_language'])) ? $_POST['news_language'] : $data2['news_language']
			);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$data['news_draft'] = '0';
		$data['news_sticky'] = '0';
		$data['news_news'] = '';
		$data['news_extended'] = '';
		$data['news_breaks'] = " 1";
		$data['news_allow_comments'] = " 1";
		$data['news_allow_ratings'] = " 1";
		$data['news_language'] = LANGUAGE;
		$data['news_visibility'] = '';
		$data['news_subject'] = '';
		$data['news_start'] = '';
		$data['news_end'] = '';
		$data['news_datestamp'] = time();
		$data['news_cat'] = '0';
		$data['news_image'] = '';
	}
	if (isset($_POST['preview'])) {
		$data['news_subject'] = form_sanitizer($_POST['news_subject'], '', 'news_subject');
		$data['news_cat'] = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
		$data['news_language'] = form_sanitizer($_POST['news_language'], '', 'news_language');
		$data['news_news'] = phpentities(stripslash($_POST['news_news']));
		$data['news_news'] = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslash($_POST['news_news']));
		$data['news_extended'] = '';
		if ($_POST['news_extended']) {
			$data['news_extended'] = phpentities(stripslash($_POST['news_extended']));
			$data['news_extended'] = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslash($_POST['news_extended']));
		}
		$data['news_breaks'] = "";
		if (isset($_POST['line_breaks'])) {
			$data['news_breaks'] = " 1";
			$data['news_news'] = nl2br($data['news_news']);
			if ($data['news_extended']) {
				$data['news_extended'] = nl2br($data['news_extended']);
			}
		}
		$data['news_start'] = (isset($_POST['news_start']) && $_POST['news_start']) ? $_POST['news_start'] : '';
		$data['news_end'] = (isset($_POST['news_end']) && $_POST['news_end']) ? $_POST['news_end'] : '';
		$data['news_image'] = isset($_POST['news_image']) ? $_POST['news_image'] : '';
		$data['news_image_t1'] = (isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "");
		$data['news_image_t2'] = (isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "");
		$data['news_visibility'] = isnum($_POST['news_visibility']) ? $_POST['news_visibility'] : "0";
		$data['news_draft'] = isset($_POST['news_draft']) ? " 1" : "";
		$data['news_sticky'] = isset($_POST['news_sticky']) ? " 1" : "";
		$data['news_allow_comments'] = isset($_POST['news_allow_comments']) ? " 1" : "";
		$data['news_allow_ratings'] = isset($_POST['news_allow_ratings']) ? " 1" : "";
		if (!defined('FUSION_NULL')) {
			echo openmodal('news_preview', 'News Preview');
			echo $data['news_news'];
			echo "<hr/>\n";
			if (isset($data['news_extended'])) {
				echo $data['news_extended'];
			}
			echo closemodal();
		}
	}
	$formaction = FUSION_SELF.$aidlink;
	if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['news_id']) && isnum($_GET['news_id'])) {
		$formaction = FUSION_SELF.$aidlink."&action=edit&news_id=".$_GET['news_id'];
	}
	echo "<div class='m-t-20'>\n";
	// remove downtime after beta.
	echo openform('inputform', 'inputform', 'post', $formaction, array('enctype' => 1, 'downtime' => 0));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
	echo form_text($locale['422'], 'news_subject', 'news_subject', $data['news_subject'], array('required' => 1, 'max_length' => 200, 'error_text' => $locale['450']));
	echo "<div class='pull-left m-r-10 display-inline-block'>\n";
	echo form_datepicker($locale['427'], 'news_start', 'news_start', $data['news_start'], array('placeholder' => $locale['429']));
	echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
	echo form_datepicker($locale['428'], 'news_end', 'news_end', $data['news_end'], array('placeholder' => $locale['429']));
	echo "</div>\n";
	echo "</div><div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
	openside('');
	echo form_select($locale['423'], 'news_cat', 'news_cat', $news_cat_opts, $data['news_cat'], array('placeholder' => $locale['choose'], 'width' => '100%'));
	echo form_button($locale['cancel'], 'cancel', 'cancel', $locale['cancel'], array('class' => 'btn-default btn-sm m-r-10'));
	echo form_button($locale['437'], 'save', 'save-1', $locale['437'], array('class' => 'btn-primary btn-sm'));
	closeside();
	echo "</div>\n</div>\n";
	// second row
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
	if ($data['news_image'] != "" && $data['news_image_t1'] != "") {
		echo "<label><img src='".IMAGES_N_T.$data['news_image_t1']."' alt='".$locale['439']."' /><br />\n";
		echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['421']."</label>\n";
		echo "<input type='hidden' name='news_image' value='".$data['news_image']."' />\n";
		echo "<input type='hidden' name='news_image_t1' value='".$data['news_image_t1']."' />\n";
		echo "<input type='hidden' name='news_image_t2' value='".$data['news_image_t2']."' />\n";
	} else {
		echo form_fileinput($locale['439'], 'news_image', 'news_image', IMAGES_N, '', array('thumbnail' => IMAGES_N_T, 'type' => 'image'));
		echo "<div class='small m-b-10'>".sprintf($locale['440'], parsebytesize($settings['news_photo_max_b']))."</div>\n";
	}
	$fusion_mce = array();
	if (!$settings['tinymce_enabled']) {
		$fusion_mce = array('preview' => 1, 'html' => 1, 'autosize' => 1, 'form_name' => 'inputform');
	}
	echo form_textarea($locale['425'], 'news_news', 'news_news', $data['news_news'], $fusion_mce);
	echo form_textarea($locale['426'], 'news_extended', 'news_extended', $data['news_extended'], $fusion_mce);
	echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
	openside('');
	if (multilang_table("NS")) {
		echo form_select($locale['global_ML100'], 'news_language', 'news_language', $language_opts, $data['news_language'], array('placeholder' => $locale['choose'], 'width' => '100%'));
	} else {
		echo form_hidden('', 'news_language', 'news_langugage', $data['news_language']);
	}
	echo form_select($locale['430'], 'news_visibility', 'news_visibility', $visibility_opts, $data['news_visibility'], array('placeholder' => $locale['choose'], 'width' => '100%'));
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
	echo "<label><input type='checkbox' name='news_draft' value='yes'".($data['news_draft'] ? "checked='checked'" : "")." /> ".$locale['431']."</label><br />\n";
	echo "<label><input type='checkbox' name='news_sticky' value='yes'".($data['news_sticky'] ? "checked='checked'" : "")."  /> ".$locale['432']."</label><br />\n";
	if ($settings['tinymce_enabled'] != 1) {
		echo "<label><input type='checkbox' name='line_breaks' value='yes'".($data['news_breaks'] ? "checked='checked'" : "")." /> ".$locale['433']."</label><br />\n";
	}
	echo "<label><input type='checkbox' name='news_allow_comments' value='yes' onclick='SetRatings();'".($data['news_allow_comments'] ? "checked='checked'" : "")." /> ".$locale['434']."</label><br/>";
	echo "<label><input type='checkbox' name='news_allow_ratings' value='yes'".($data['news_allow_ratings'] ? "checked='checked'" : "")." /> ".$locale['435']."</label>";
	closeside();
	if (isset($_GET['action']) && isset($_GET['news_id']) && isnum($_GET['news_id']) || (isset($_POST['preview']) && (isset($_POST['news_id']) && isnum($_POST['news_id']))) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
		$news_id = isset($_GET['news_id']) && isnum($_GET['news_id']) ? $_GET['news_id'] : '';
		echo form_hidden('', 'news_id', 'news_id', $news_id);
	}
	echo "</div>\n</div>\n";
	echo form_button($locale['436'], 'preview', 'preview-1', $locale['436'], array('class' => 'btn-primary m-r-10'));
	echo form_button($locale['437'], 'save', 'save-1', $locale['437'], array('class' => 'btn-primary'));
	echo closeform();
	echo "</div>\n";
}

$master_title['title'][] = $locale['400'];
$master_title['id'][] = 'news';
$master_title['icon'] = '';

$master_title['title'][] = $locale['401'];
$master_title['id'][] = 'nform';
$master_title['icon'] = '';

$tab_active = tab_active($master_title, 1);

opentable('News');
echo opentab($master_title, $tab_active, 'news');
echo opentabbody($master_title['title'][0], 'news', $tab_active);
news_listing();
echo closetabbody();
echo opentabbody($master_title['title'][1], 'nform', $tab_active);
news_form();
echo closetabbody();
echo closetab();
closetable();


if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['news_id'])) {
	add_to_jquery("
		// change the name of the second tab and activate it.
		$('#tab-nformAdd-News').text('".$locale['402']."');
		$('#news a:last').tab('show');
		");
}


require_once THEMES."templates/footer.php";
?>
