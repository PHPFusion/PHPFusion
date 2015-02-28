<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forums.php
| Author: PHP-Fusion Inc.
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
/* todo : forum order
 * todo: forum answering via ranks.. assign groups points.
 * */

require_once "../maincore.php";
require_once INCLUDES."bbcode_include.php";
require_once THEMES."templates/render_functions.php";
if (!checkrights("F") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	//redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/forums.php";
require_once INCLUDES."forum_functions_include.php";

/* Global Sanitize */
$_GET['forum_id'] =  (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) ? $_GET['forum_id'] : 0;
$_GET['forum_cat'] =  (isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])) ? $_GET['forum_cat'] : 0;
$_GET['forum_branch'] =  (isset($_GET['forum_branch']) && isnum($_GET['forum_branch'])) ? $_GET['forum_branch'] : 0;
$_GET['parent_id'] =  (isset($_GET['parent_id']) && isnum($_GET['parent_id'])) ? $_GET['parent_id'] : 0;
$ext = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? "&amp;parent_id=".$_GET['parent_id'] : '';
/* Hierarchy index */
$forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
/* Push breadcrumb */
forum_breadcrumbs($forum_index);

/* Move Up */
if ((isset($_GET['action']) && $_GET['action'] == "mu") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_GET['order']) && isnum($_GET['order']))) {
	$data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['cat']."' AND forum_order='".$_GET['order']."'"));
	$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$data['forum_id']."'");
	$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."'");
	redirect(FUSION_SELF.$aidlink.$ext."&status=mup");
}
/* Move Down */
elseif ((isset($_GET['action']) && $_GET['action'] == "md") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_GET['order']) && isnum($_GET['order']))) {
	$data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['cat']."' AND forum_order='".$_GET['order']."'"));
	$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$data['forum_id']."'");
	$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."'");
	redirect(FUSION_SELF.$aidlink.$ext."&status=md");
}
/* Jump to Forum Permissions redirect if posted from form */
elseif (isset($_POST['jp_forum'])) {
	$data['forum_id'] = form_sanitizer($_POST['forum_id'], '', 'forum_id');
	redirect(FUSION_SELF.$aidlink."&amp;action=p_edit&amp;forum_id=".$data['forum_id']."&amp;parent_id=".$_GET['parent_id']);
}
/* Delete Check */
elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])) {
	// check if there are subforums, threads or posts.
	$forum_count = dbcount("('forum_id')", DB_FORUMS, "forum_cat='".$_GET['forum_id']."'");
	$thread_count = dbcount("('forum_id')", DB_FORUM_THREADS, "forum_id='".$_GET['forum_id']."'");
	$post_count = dbcount("('post_id')", DB_FORUM_THREADS, "forum_id='".$_GET['forum_id']."'");
	if (($forum_count+$thread_count+$post_count) >= 1) {
		move_form();
	} else {
		prune_attachment($_GET['forum_id']);
		prune_posts($_GET['forum_id']);
		prune_threads($_GET['forum_id']);
		recalculate_post($_GET['forum_id']);
		prune_forums('', $_GET['forum_id']); // without index, this prune will delete only one.
		redirect(FUSION_SELF.$aidlink."&status=crf");
	}
}
/* Delete Finalised - Remove */
elseif (isset($_POST['forum_remove'])) {
	remove_forum();
	redirect(FUSION_SELF.$aidlink."&status=crf");
}
/* Prune Actions - disabled now - do only after front end. Nothing to prune during coding admin, nothing to test */
elseif (isset($_GET['action']) && $_GET['action'] == "prune") {
	require_once "forums_prune.php";
}
/* Remove Image */
elseif (isset($_POST['remove_image']) && isset($_POST['forum_id'])) {
	$data['forum_id'] = form_sanitizer($_POST['forum_id'], '', 'forum_id');
	if ($data['forum_id']) {
		$result = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id='".$data['forum_id']."' LIMIT 1");
		if (dbrows($result)>0) {
			$data = dbarray($result); // will repopulate data here.
			if (!empty($data['forum_image']) && file_exists(IMAGES."forum/".$data['forum_image'])) {
				@unlink(IMAGES."forum/".$data['forum_image']);
				$data['forum_image'] = '';
			}
			dbquery_insert(DB_FORUMS, $data, 'update');
			redirect(FUSION_SELF.$aidlink."&status=rim");
		}
	}
}
/* Save Forum Permissions */
elseif (isset($_POST['save_permission'])) {
	$data['forum_id'] = form_sanitizer($_POST['forum_id'], '', 'forum_id');
	if ($data['forum_id']) {
		$result = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id='".$data['forum_id']."' LIMIT 1");
		if (dbrows($result)>0) {
			$data = dbarray($result); // will repopulate data here.
		}
	}
	$data['forum_post'] = form_sanitizer($_POST['forum_post'], 101, 'forum_post');
	$data['forum_reply'] = form_sanitizer($_POST['forum_reply'], 101, 'forum_reply');
	$data['forum_post_ratings'] = form_sanitizer($_POST['forum_post_ratings'], 101, 'forum_post_ratings');
	$data['forum_poll'] = form_sanitizer($_POST['forum_poll'], 101, 'forum_poll');
	$data['forum_vote'] = form_sanitizer($_POST['forum_vote'], 101, 'forum_vote');
	$data['forum_answer_threshold'] = form_sanitizer($_POST['forum_answer_threshold'], 0, 'forum_answer_threshold');
	$data['forum_attach'] = form_sanitizer($_POST['forum_attach'], 101, 'forum_attach');
	$data['forum_attach_download'] = form_sanitizer($_POST['forum_attach_download'], 101, 'forum_attach_download');
	$data['forum_mods'] = form_sanitizer($_POST['forum_mods'], '', 'forum_mods');
	dbquery_insert(DB_FORUMS, $data, 'update', array('nodirect'=>1));
	redirect(FUSION_SELF.$aidlink.$ext."&amp;status=psv");
}
/* New Save Forum */
elseif (isset($_POST['save_forum'])) {
	//print_p($_POST);
	$data['forum_id'] = form_sanitizer($_POST['forum_id'], '', 'forum_id');
	if ($data['forum_id']) {
		$result = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id='".$data['forum_id']."' LIMIT 1");
		if (dbrows($result)>0) {
			$data = dbarray($result); // will repopulate data here.
		}
	}
	// Then $data override if exist.
	$data['forum_name'] = form_sanitizer($_POST['forum_name'], '', 'forum_name');
	$data['forum_description'] = form_sanitizer($_POST['forum_description'], '', 'forum_description');
	$data['forum_cat'] = form_sanitizer($_POST['forum_cat'], '', 'forum_cat');
	$data['forum_type'] = form_sanitizer($_POST['forum_type'], '', 'forum_type');
	$data['forum_language'] = form_sanitizer($_POST['forum_language'], '', 'forum_language');
	$data['forum_alias'] = form_sanitizer($_POST['forum_alias'], '', 'forum_alias');
	$data['forum_alias'] = $data['forum_alias'] ? str_replace(' ', '-', $data['forum_alias']) : '';
	$data['forum_meta'] = form_sanitizer($_POST['forum_meta'], '', 'forum_meta');
	$data['forum_rules'] = form_sanitizer($_POST['forum_rules'], '', 'forum_rules');
	$data['forum_image_enable'] = isset($_POST['forum_image_enable']) ? 1 : 0;
	$data['forum_merge'] = isset($_POST['forum_merge']) ? 1 : 0;
	$data['forum_allow_attach'] = isset($_POST['forum_allow_attach']) ? 1 : 0;
	$data['forum_quick_edit'] = isset($_POST['forum_quick_edit']) ? 1 : 0;
	$data['forum_poll'] = isset($_POST['forum_poll']) ? 1 : 0;
	$data['forum_allow_ratings'] = isset($_POST['forum_allow_ratings']) ? 1 : 0;
	$data['forum_users'] = isset($_POST['forum_users']) ? 1 : 0;
	$data['forum_lock'] = isset($_POST['forum_lock']) ? 1 : 0;
	$data['forum_permissions'] = isset($_POST['forum_permissions']) ? form_sanitizer($_POST['forum_permissions'], 0, 'forum_permissions') : 0;
	$data['forum_order'] = isset($_POST['forum_order']) ? form_sanitizer($_POST['forum_order']) : '';
	// check integrity so far.
	if (!defined('FUSION_NULL')) {
		// get the root of the hierarchy and store it.
		$data['forum_branch'] = get_hkey(DB_FORUMS, 'forum_id', 'forum_cat', $data['forum_cat']);
		// check alias is unique or not
		if ($data['forum_alias']) {
			if ($data['forum_id']) {
				$alias_check = dbcount("('alias_id')", DB_PERMALINK_ALIAS, "alias_url='".$data['forum_alias']."' AND alias_item_id !='".$data['forum_id']."'");
			} else {
				$alias_check = dbcount("('alias_id')", DB_PERMALINK_ALIAS, "alias_url='".$data['forum_alias']."'");
			}
			if ($alias_check) {
				$defender->stop();
				$defender->addNotice($locale['forum_error_6']);
			}
		}
		// check forum name unique
		if ($data['forum_name']) {
			if ($data['forum_id']) {
				$name_check = dbcount("('forum_name')", DB_FORUMS, "forum_name='".$data['forum_name']."' AND forum_id !='".$data['forum_id']."'");
			} else {
				$name_check = dbcount("('forum_name')", DB_FORUMS, "forum_name='".$data['forum_name']."'");
			}
			if ($name_check) {
				$defender->stop();
				$defender->addNotice($locale['forum_error_7']);
			}
		}
		// check files and upload image or copy from url
		if (!empty($_FILES['forum_image']['name']) && is_uploaded_file($_FILES['forum_image']['tmp_name'])) {
			require_once INCLUDES."infusions_include.php";
			// Name of $_FILE key which holds the uploaded image
			$image = "forum_image";
			// Left blank to use the image name as it is
			$name = $_FILES['forum_image']['name'];
			// Upload folder
			$folder = IMAGES."forum/";
			// Maximum image width in pixels
			$max_width = 1000;
			$width = 200; //$settings['forum_image_max_w'];
			// Maximum image height in pixels
			$max_height = 1000;
			$height = 200; //$settings['forum_image_max_w'];
			// Maximum file size in bytes
			$max_size = 500*1000; //$settings['forum_image_max_b'];
			// have auto unlink.
			$upload = upload_image($image, $name, $folder, $max_width, $max_height, $max_size, TRUE, TRUE, FALSE, 0, $folder, '_t1', $width, $height);
			if ($upload['error'] != 0) {
				$defender->stop();
				switch ($upload['error']) {
					case 1:
						$defender->addNotice(sprintf($locale['forum_error_2'], parsebytesize($settings['download_screen_max_b'])));
						// Invalid file size
						break;
					case 2:
						// Unsupported image type
						$defender->addNotice(sprintf($locale['forum_error_4'], ".gif .jpg .png"));
						break;
					case 3:
						// Invalid image resolution
						$defender->addNotice(sprintf($locale['forum_error_3'], "$width  x $height"));
						break;
					case 4:
						// Invalid query string
						$defender->addNotice($locale['forum_error_5']);
						break;
					case 5:
						// Image not uploaded
						$defender->addNotice($locale['419a']);
						break;
				}
			} else {
				// Successful upload!
				$data['forum_image'] = $upload['thumb1_name'];
			}
		} elseif (isset($_POST['forum_image']) && $_POST['forum_image'] != "") {
			// if not uploaded, here on both save and update.
			$type_opts = array('0'=>BASEDIR, '1'=>'http://', '2'=>'https://');
			$data['forum_image'] = $type_opts[form_sanitizer($_POST['forum_image_header'], '0', 'forum_image_header')].form_sanitizer($_POST['forum_image'], '', 'forum_image');

			if ($data['forum_id']) {
				$image_check = dbarray(dbquery("SELECT forum_image FROM ".DB_FORUMS." WHERE forum_id='".$data['forum_id']."'"));
				$image_found =  ($image_check['forum_image'] && file_exists(IMAGES."forum/".$image_check['forum_image'])) ? 1 : 0;
				if (!$image_found) {
					$upload = copy_image(IMAGES."forum/", $data['forum_image']);
				} else {
					$defender->stop();
					$defender->addNotice($locale['forum_error_8']);
				}
			} else {
				$upload = copy_image(IMAGES."forum/", $data['forum_image']);
			}
			if (isset($upload['error'])) {
				$defender->stop();
				$defender->addNotice($locale['forum_error_9']);
			} else {
				$data['forum_image'] = $upload['name'];
			}
		}

		// forum_permissions
		if ($data['forum_permissions'] !=0) {
			$p_fields = dbarray(dbquery("SELECT
			forum_access, forum_post, forum_reply, forum_post_ratings, forum_poll, forum_vote, forum_answer_threshold, forum_attach, forum_attach_download, forum_mods
			FROM ".DB_FORUMS." WHERE forum_id='".$data['forum_permissions']."'
			"));
			$data += $p_fields;
		} else {
			$data['forum_access'] = 0;
			$data['forum_post'] = 101; // create new topics
			$data['forum_reply'] = 101; // reply
			$data['forum_post_ratings'] = 101; // cast vote on answers
			$data['forum_poll'] = 101; // vote
			$data['forum_vote'] = 101; // cast vote on poll.
		}
		if ($data['forum_id']) {
			// check old order.
			$old_order = dbarray(dbquery("SELECT forum_order FROM ".DB_FORUMS." WHERE forum_id='".$data['forum_id']."'"));
			if ($old_order > $data['forum_order']) { // current order is shifting up. 6 to 3., 1,2,(3),3->4,4->5,5->6. where orders which is less than 6 but is more or equals current.
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$data['forum_cat']."' AND forum_order<'".$old_order['forum_order']."' AND forum_order>='".$data['forum_order']."'");
			} elseif ($old_order < $data['forum_order']) { // current order is shifting down. 3 to 6. 1,2,(3),3<-4,5,5<-(6),7. where orders which is more than old order, and less than current equals.
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$data['forum_cat']."' AND forum_order>'".$old_order['forum_order']."' AND forum_order<='".$data['forum_order']."'");
			} // else no change.
			dbquery_insert(DB_FORUMS, $data, 'update', array('noredirect'=>1));
			redirect(FUSION_SELF.$aidlink.$ext."&amp;status=csup");
		} else {
			if (!$data['forum_order']) $data['forum_order'] = dbresult(dbquery("SELECT MAX(forum_order) FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$data['forum_cat']."'"), 0)+1;
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$data['forum_cat']."' AND forum_order>='".$data['forum_order']."'");
			dbquery_insert(DB_FORUMS, $data, 'save', array('noredirect'=>1));
			$this_forum_id = dblastid();
			
			if (!defined('FUSION_NULL')) {
				$ext = isset($_GET['parent_id']) ? "&amp;parent_id=".$_GET['parent_id'] : '';
				// added jump to permissions if the category is 0.
				if (!$data['forum_cat']) {
					redirect(FUSION_SELF.$aidlink."&amp;action=p_edit&amp;forum_id=".$this_forum_id."&amp;parent_id=0");
				} else {
					if ($data['forum_type'] == 1) {
						redirect(FUSION_SELF.$aidlink.$ext."&amp;status=cns");
					} elseif ($data['forum_type'] == 2) {
						redirect(FUSION_SELF.$aidlink.$ext."&amp;status=cfs");
					} elseif ($data['forum_type'] == 3) {
						redirect(FUSION_SELF.$aidlink.$ext."&amp;status=cls");
					} elseif ($data['forum_type'] == 4) {
						redirect(FUSION_SELF.$aidlink.$ext."&amp;status=cas");
					}
				}
			}
		}
	}
} else {
	$data = array();
}
/* Permissions Save */
if ((isset($_GET['action']) && ($_GET['action'] == "edit" || $_GET['action'] == 'p_edit')) && (isset($_GET['forum_id']) && isnum($_GET['forum_id']))) {
	$result = dbquery("SELECT * FROM ".DB_FORUMS."  ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."' LIMIT 1");
	if (dbrows($result)>0) {
		$data = dbarray($result);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}

/* Start Render */
forum_status_message();
$res = 0;
if (isset($_POST['init_forum'])) {
	$data['forum_name'] = form_sanitizer($_POST['forum_name'], '', 'forum_name');
	if (!defined('FUSION_NULL')) {
		$duplicate_rows = dbcount("('forum_id')", DB_FORUMS, "forum_name='".$data['forum_name']."'");
		if (!$duplicate_rows) {
			$res = 1;
			$data['forum_cat'] = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? $_GET['parent_id'] : 0;
		} else {
			$defender->stop();
			$defender->addNotice($locale['forum_error_7']);
		}
	}
}
if ($res == 1 or (isset($_POST['save_forum']) && defined('FUSION_NULL')) or isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['forum_id']) && isnum($_GET['forum_id'])) {
	forum_form($data);
} elseif (isset($_GET['action']) && $_GET['action'] == 'p_edit' && isset($_GET['forum_id']) && isnum($_GET['forum_id'])) {
	forum_permissions_form($data);
} else {
	/* Show prevalidation error */
	if (defined('FUSION_NULL')) {
		echo $defender->showNotice();
	}
	forum_jumper();
	view_forums();
	quick_create_forum();
}
/* End Render */

/* JS Menu Jumper */
function forum_jumper() {
	global $aidlink, $locale;
	echo "<div class='clearfix'>\n";
	echo form_select_tree('', 'forum_jump', 'forum_jump', $_GET['parent_id'], array('inline'=>1,  'class'=>'pull-right', 'parent_value'=>$locale['forum_root']), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
	echo "<label for='forum_jump' class='text-dark strong pull-right m-r-10 m-t-3'>".$locale['forum_044']."</label>\n";
	add_to_jquery("
	$('#forum_jump').change(function() {
		location = '".FUSION_SELF.$aidlink."&parent_id='+$(this).val();
	});
	");
	echo "</div>\n";
}
/* Full forum creation form */
function forum_form(array $data = array()) {
	global $aidlink, $settings, $locale;
	$language_opts = fusion_get_enabled_languages();

	add_to_breadcrumbs(array('link'=>'', 'title'=>$locale['forum_001']));

	$data += array(
		'forum_id' => !empty($data['forum_id']) && isnum($data['forum_id']) ? $data['forum_id'] : 0,
		'forum_name' => !empty($data['forum_name']) ? $data['forum_name'] : '',
		'forum_description' => !empty($data['forum_description']) ? $data['forum_description'] : '',
		'forum_cat' => !empty($data['forum_cat']) ? $data['forum_cat'] : '',
		'forum_branch' => !empty($data['forum_branch']) ? $data['forum_branch'] : '',
		'forum_type' => !empty($data['forum_type']) ? $data['forum_type'] : '2',
		'forum_language' => !empty($data['forum_language']) ? $data['forum_language'] : LANGUAGE,
		'forum_alias' => !empty($data['forum_alias']) ? $data['forum_alias'] : '',
		'forum_meta' => !empty($data['forum_meta']) ? $data['forum_meta'] : '',
		'forum_image' => !empty($data['forum_image']) ? $data['forum_image'] : '',
		'forum_image_url' => !empty($data['forum_image_url']) ? $data['forum_image_url'] : '',
		'forum_rules' => !empty($data['forum_rules']) ? $data['forum_rules'] : '',
		'forum_merge' => !empty($data['forum_merge']) ? $data['forum_merge'] : 0,
		'forum_attach' => !empty($data['forum_attach']) ? $data['forum_attach'] : 0,
		'forum_quick_edit' => !empty($data['forum_quick_edit']) ? $data['forum_quick_edit'] : 1,
		'forum_poll' => !empty($data['forum_poll']) ? $data['forum_poll'] : 0,
		'forum_allow_ratings' => !empty($data['forum_allow_ratings']) ? $data['forum_allow_ratings'] : 1,
		'forum_users' => !empty($data['forum_users']) ? $data['forum_users'] : 1,
		'forum_permissions' => !empty($data['forum_permissions']) ? $data['forum_permissions'] : '',
		'forum_lock' => !empty($data['forum_lock']) ? $data['forum_lock'] : 0,
		'forum_order' => !empty($data['forum_order']) ? $data['forum_order'] : 1,
	);

	if (!isset($_GET['action']) && $_GET['parent_id']) {
		$data['forum_cat'] = $_GET['parent_id'];
	}
	$type_opts = array('1'=>$locale['forum_opts_001'], '2'=>$locale['forum_opts_002'], '3'=>$locale['forum_opts_003'], '4'=>$locale['forum_opts_004']);
	opentable($locale['forum_001']);
	$ext = isset($_GET['parent_id']) ? '&amp;parent_id='.$_GET['parent_id'] : '';
	echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink.$ext, array('enctype'=>1, 'downtime'=>1));
	echo "<div class='row'>\n<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
	echo form_text($locale['forum_006'], 'forum_name', 'forum_name', $data['forum_name'], array('required'=>1, 'error_text'=>$locale['forum_error_1']));
	echo form_textarea($locale['forum_007'], 'forum_description', 'forum_description', $data['forum_description'], array('autosize'=>1, 'bbcode'=>1));
	echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
	openside('');
	$self_id = $data['forum_id'] ? $data['forum_id'] : '';
	echo form_select_tree($locale['forum_008'], 'forum_cat', 'forum_cat', $data['forum_cat'], array('add_parent_opts'=>1, 'disable_opts'=>$self_id, 'hide_disabled'=>1), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $self_id);
	echo form_select($locale['forum_009'], 'forum_type', 'forum_type', $type_opts, $data['forum_type']);
	echo form_select($locale['forum_010'], 'forum_language', 'forum_lang', $language_opts, $data['forum_language']);
	echo form_text($locale['forum_043'], 'forum_order', 'forum_order', $data['forum_order'], array('number'=>1));
	echo form_button($data['forum_id'] ? $locale['forum_000a'] : $locale['forum_000'], 'save_forum', 'save_forum', $locale['forum_000'], array('class'=>'btn btn-sm btn-primary'));
	closeside();
	echo "</div>\n</div>\n";
	echo "<div class='row'>\n<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
	echo form_text($locale['forum_011'], 'forum_alias', 'forum_alias', $data['forum_alias']); // need ajax check
	echo form_select($locale['forum_012'], 'forum_meta', 'forum_meta', array(), $data['forum_meta'], array('tags'=>1, 'multiple'=>1, 'width'=>'100%'));
	// possible bug? - if image is tied to a url. we can remove it after assigning to other's people page? what if i split to image_url ?
	if ($data['forum_image'] && file_exists(IMAGES."forum/".$data['forum_image'])) {
		openside();
		echo "<div class='pull-left m-r-10'>\n";
		$image_size = getimagesize(IMAGES."forum/".$data['forum_image']);
		echo thumbnail(IMAGES."forum/".$data['forum_image'], '80px', '80px');
		echo "</div>\n<div class='overflow-hide'>\n";
		echo "<span class='strong'>".$locale['forum_013']."</span><br/>\n";
		echo "<span class='text-smaller'>".sprintf($locale['forum_027'], $image_size[0], $image_size[1])."</span><br/>";
		echo form_button($locale['forum_028'], 'remove_image', 'remove_image', $locale['forum_028'], array('class'=>'btn-default btn-xs m-t-10', 'icon'=>'entypo trash'));
		// this form has forum_id - onclick of button - will also post forum_id @ L475
		echo "</div>\n";
		closeside();
	} else {
		$tab_title['title'][] = $locale['forum_013'];
		$tab_title['id'][] = 'fir';
		$tab_title['icon'][] = '';
		$tab_title['title'][] = $locale['forum_014'];
		$tab_title['id'][] = 'ful';
		$tab_title['icon'][] = '';
		$tab_active = tab_active($tab_title, 0);
		echo opentab($tab_title, $tab_active, 'forum-image-tab');
		echo opentabbody($tab_title['title'][0], 'fir', $tab_active);
		echo "<span class='display-inline-block m-t-10 m-b-10'>".sprintf($locale['forum_015'], parsebytesize($settings['download_max_b']))."</span>\n";
		echo form_fileinput('', 'forum_image', 'forum_image', IMAGES."forum", '', array('thumbnail'=>IMAGES."forum/thumbnail", 'type'=>'image'));
		echo closetabbody();
		echo opentabbody($tab_title['title'][1], 'ful', $tab_active);
		echo "<span class='display-inline-block m-t-10 m-b-10'>".$locale['forum_016']."</strong></span>\n";
		$header_opts = array(
			'0' => $settings['siteurl'],
			'1' => 'http://',
			'2' => 'https://'
		);
		echo form_select($locale['forum_056'], 'forum_image_header', 'forum_image_header', $header_opts, '', array('inline'=>1));
		echo form_text($locale['forum_014'], 'forum_image', 'forum_image_url', '', array('placeholder'=>'images/forum/', 'inline'=>1));
		echo closetabbody();
		echo closetab();
	}

	echo form_textarea($locale['forum_017'], 'forum_rules', 'forum_rules', $data['forum_rules'], array('autosize'=>1, 'bbcode'=>1));
	echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
	openside('');
	echo form_select_tree($locale['forum_025'], 'forum_permissions', 'forum_permissions', $data['forum_permissions'], array('no_root'=>1), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
	if ($data['forum_id']) {
		echo form_button($locale['forum_029'], 'jp_forum', 'jp_forum', $locale['forum_029'], array('class'=>'btn-sm btn-primary m-r-10'));
	}
	closeside();
	openside('');
	echo form_checkbox($locale['forum_026'], 'forum_lock', 'forum_lock', $data['forum_lock']);
	echo form_checkbox($locale['forum_024'], 'forum_users', 'forum_users', $data['forum_users']);
	echo form_checkbox($locale['forum_021'], 'forum_quick_edit', 'forum_quick_edit', $data['forum_quick_edit']);
	echo form_checkbox($locale['forum_019'], 'forum_merge', 'forum_merge', $data['forum_merge']);
	echo form_checkbox($locale['forum_020'], 'forum_allow_attach', 'forum_allow_attach', $data['forum_attach']);
	echo form_checkbox($locale['forum_022'], 'forum_poll', 'forum_poll', $data['forum_poll']);
	echo form_checkbox($locale['forum_023'], 'forum_allow_ratings', 'forum_allow_ratings', $data['forum_allow_ratings']);
	echo form_hidden('', 'forum_id', 'forum_id', $data['forum_id']);
	echo form_hidden('', 'forum_branch', 'forum_branch', $data['forum_branch']);
	closeside();
	echo "</div>\n</div>\n";
	echo form_button($data['forum_id'] ? $locale['forum_000a'] : $locale['forum_000'], 'save_forum', 'save_forum_1', $locale['forum_000'], array('class'=>'btn-sm btn-primary'));
	echo closeform();
	closetable();
}
/* Forum permissions form */
function forum_permissions_form(array $data = array()) {
	global $aidlink, $settings, $locale, $ext;
	$data += array(
		'forum_id' => !empty($data['forum_id']) && isnum($data['forum_id']) ? $data['forum_id'] : 0,
		'forum_type' => !empty($data['forum_type']) ? $data['forum_type'] : '', // redirect if not exist? no..
	);
	opentable($locale['forum_030']);
	$_access = getusergroups();
	$access_opts['0'] = $locale['531'];
	while (list($key, $option) = each($_access)) {
		$access_opts[$option['0']] = $option['1'];
	}
	echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink.$ext, array('enctype'=>1, 'downtime'=>1));
	echo "<span class='strong display-inline-block m-b-20'>".$locale['forum_006']." : ".$data['forum_name']."</span>\n";

	openside();
	echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_000']."</span><br/>\n";
	echo form_select($locale['forum_031'], 'forum_access', 'forum_access', $access_opts, '', array('inline'=>1));
	unset($access_opts[0]); // remove public away.
	echo form_select($locale['forum_032'], 'forum_post', 'forum_post', $access_opts, '', array('inline'=>1));
	echo form_select($locale['forum_033'], 'forum_reply', 'forum_reply', $access_opts, '', array('inline'=>1));
	echo form_select($locale['forum_039'], 'forum_post_ratings', 'forum_post_ratings', $access_opts, '', array('inline'=>1));
	closeside();

	openside();
	echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_001']."</span><br/>\n";
	echo form_select($locale['forum_036'], 'forum_poll', 'forum_poll', $access_opts, '', array('inline'=>1));
	echo form_select($locale['forum_037'], 'forum_vote', 'forum_vote', $access_opts, '', array('inline'=>1));
	closeside();

	openside();
	echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_004']."</span><br/>\n";
	$selection = array(
		$locale['forum_041'],
		"10 ".$locale['forum_points'],
		"20 ".$locale['forum_points'],
		"30 ".$locale['forum_points'],
		"40 ".$locale['forum_points'],
		"50 ".$locale['forum_points'],
		"60 ".$locale['forum_points'],
		"70 ".$locale['forum_points'],
		"80 ".$locale['forum_points'],
		"90 ".$locale['forum_points'],
		"100 ".$locale['forum_points']
	);
	echo form_select($locale['forum_040'], 'forum_answer_threshold', 'forum_answer_threshold', $selection, '', array('inline'=>1));
	closeside();

	openside();
	echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_002']."</span><br/>\n";
	echo form_select($locale['forum_034'], 'forum_attach', 'forum_attach', $access_opts, '', array('inline'=>1));
	echo form_select($locale['forum_035'], 'forum_attach_download', 'forum_attach_download', $access_opts, '', array('inline'=>1));
	closeside();

	openside();
	echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_003']."</span><br/>\n";
	$mod_groups = getusergroups();
	$mods1_user_id = array();
	$mods1_user_name = array();
	while (list($key, $mod_group) = each($mod_groups)) {
		if ($mod_group['0'] != "0" && $mod_group['0'] != "101" && $mod_group['0'] != "103") {
			if (!preg_match("(^{$mod_group['0']}$|^{$mod_group['0']}\.|\.{$mod_group['0']}\.|\.{$mod_group['0']}$)", $data['forum_mods'])) {
				$mods1_user_id[] = $mod_group['0'];
				$mods1_user_name[] = $mod_group['1'];
			} else {
				$mods2_user_id[] = $mod_group['0'];
				$mods2_user_name[] = $mod_group['1'];
			}
		}
	}
	echo "<div class='row'>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
	echo "<select multiple='multiple' size='10' name='modlist1' id='modlist1' class='form-control textbox m-r-10' onchange=\"addUser('modlist2','modlist1');\">\n";
	for ($i = 0; $i < count($mods1_user_id); $i++) {
		echo "<option value='".$mods1_user_id[$i]."'>".$mods1_user_name[$i]."</option>\n";
	}
	echo "</select>\n";
	echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
	echo "<select multiple='multiple' size='10' name='modlist2' id='modlist2' class='form-control textbox' onchange=\"addUser('modlist1','modlist2');\">\n";
	if (isset($mods2_user_id) && is_array($mods2_user_id)) {
		for ($i = 0; $i < count($mods2_user_id); $i++) {
			echo "<option value='".$mods2_user_id[$i]."'>".$mods2_user_name[$i]."</option>\n";
		}
	}
	echo "</select>\n";
	echo form_hidden('', 'forum_mods', 'forum_mods', '');
	echo form_hidden('', 'forum_id', 'forum_id', $data['forum_id']);
	echo "</div>\n</div>\n";
	closeside();

	echo form_button($locale['forum_042'], 'save_permission', 'save_permission', $locale['forum_042'], array('class' =>'btn-primary btn-sm'));

	add_to_jquery(" $('#save').bind('click', function() { saveMods(); }); ");
	echo "<script type='text/javascript'>\n"."function addUser(toGroup,fromGroup) {\n";
	echo "var listLength = document.getElementById(toGroup).length;\n";
	echo "var selItem = document.getElementById(fromGroup).selectedIndex;\n";
	echo "var selText = document.getElementById(fromGroup).options[selItem].text;\n";
	echo "var selValue = document.getElementById(fromGroup).options[selItem].value;\n";
	echo "var i; var newItem = true;\n";
	echo "for (i = 0; i < listLength; i++) {\n";
	echo "if (document.getElementById(toGroup).options[i].text == selText) {\n";
	echo "newItem = false; break;\n}\n}\n"."if (newItem) {\n";
	echo "document.getElementById(toGroup).options[listLength] = new Option(selText, selValue);\n";
	echo "document.getElementById(fromGroup).options[selItem] = null;\n}\n}\n";
	echo "function saveMods() {\n"."var strValues = \"\";\n";
	echo "var boxLength = document.getElementById('modlist2').length;\n";
	echo "var count = 0;\n"."	if (boxLength != 0) {\n"."for (i = 0; i < boxLength; i++) {\n";
	echo "if (count == 0) {\n"."strValues = document.getElementById('modlist2').options[i].value;\n";
	echo "} else {\n"."strValues = strValues + \".\" + document.getElementById('modlist2').options[i].value;\n";
	echo "}\n"."count++;\n}\n}\n";
	echo "if (strValues.length == 0) {\n"."document.forms['inputform'].submit();\n";
	echo "} else {\n"."document.forms['inputform'].forum_mods.value = strValues;\n";
	echo "document.forms['inputform'].submit();\n}\n}\n</script>\n";
	closetable();
}
/* Short forum creation form */
function quick_create_forum() {
	global $aidlink, $locale, $ext;
	opentable($locale['forum_000']);
	echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink.$ext, array('downtime'=>1, 'notice'=>0));
	echo form_text($locale['forum_006'], 'forum_name', 'forum_name', '', array('required'=>1, 'inline'=>1));
	echo form_button($locale['forum_001'], 'init_forum', 'init_forum', 'init_forum', array('class'=>'btn btn-sm btn-primary'));
	echo closeform();
	closetable();
}
/* Form Listing */
function view_forums() {
	global $locale, $aidlink, $settings, $forum_index, $ext;
	opentable($locale['forum_000b']);
	$threads_per_page = $settings['threads_per_page'];
	$max_rows = dbcount("('forum_id')", DB_FORUMS, (multilang_table("FO") ? "forum_language='".LANGUAGE."' AND" : '')." forum_cat='".$_GET['parent_id']."'"); // need max rows
	$_GET['rowstart'] = (isset($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows) ? $_GET['rowstart'] : '0';
	$result = dbquery("SELECT forum_id, forum_cat, forum_branch, forum_name, forum_description, forum_image, forum_alias, forum_type, forum_threadcount, forum_postcount, forum_order FROM
	".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$_GET['parent_id']."'
	 ORDER BY forum_order ASC LIMIT ".$_GET['rowstart'].", $threads_per_page
	 ");
	$rows = dbrows($result);
	if ($rows > 0) {
		$type_icon = array('1'=>'entypo folder', '2'=>'entypo chat', '3'=>'entypo link', '4'=>'entypo graduation-cap');
		$i = 1;
		while ($data = dbarray($result)) {
			$up = $data['forum_order']-1;
			$down = $data['forum_order']+1;
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-body'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			echo "<i class='".$type_icon[$data['forum_type']]." icon-sm'></i>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			$html2 = '';
			if ($data['forum_image'] && file_exists(IMAGES."forum/".$data['forum_image'])) {
				echo "<div class='pull-left m-r-10'>\n".thumbnail(IMAGES."forum/".$data['forum_image'], '50px')."</div>\n";
				echo "<div class='overflow-hide'>\n";
				$html2 = "</div>\n";
			}
			echo "<span class='strong'><a href='".FUSION_SELF.$aidlink."&amp;parent_id=".$data['forum_id']."&amp;branch=".$data['forum_branch']."'>".$data['forum_name']."</a></span><br/>".$data['forum_description'].$html2;
			echo "</div>\n<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo "<div class='pull-right'>\n";
			echo ($i == 1) ? '' : "<a title='".$locale['forum_045']."' href='".FUSION_SELF.$aidlink."&amp;action=mu&amp;order=$up&amp;forum_id=".$data['forum_id']."'><i class='entypo up-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
			echo ($i == $rows) ? '' : "<a title='".$locale['forum_046']."' href='".FUSION_SELF.$aidlink."&amp;action=md&amp;order=$down&amp;forum_id=".$data['forum_id']."'><i class='entypo down-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
			echo "<a title='".$locale['forum_047']."' href='".FUSION_SELF.$aidlink."&amp;action=p_edit&forum_id=".$data['forum_id']."&amp;parent_id=".$_GET['parent_id']."'><i class='entypo key m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>"; // edit
			echo "<a title='".$locale['forum_048']."' href='".FUSION_SELF.$aidlink."&amp;action=edit&forum_id=".$data['forum_id']."&amp;parent_id=".$_GET['parent_id']."'><i class='entypo cog m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>"; // edit
			echo "<a title='".$locale['forum_049']."' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;forum_id=".$data['forum_id']."&amp;forum_cat=".$data['forum_cat']."&amp;forum_branch=".$data['forum_branch'].$ext."' onclick=\"return confirm('".$locale['delete_notice']."');\"><i class='entypo icancel m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>"; // delete
			echo "</div>\n";
			echo "<span class='text-dark text-smaller strong'>Topics: ".number_format($data['forum_threadcount'])." / Posts: ".number_format($data['forum_postcount'])." </span>\n<br/>";
			$subforums = get_child($forum_index, $data['forum_id']);
			$subforums = !empty($subforums) ? count($subforums) : 0;
			echo "<span class='text-dark text-smaller strong'>".$locale['forum_050'].": ".number_format($subforums)."</span>\n<br/>";
			echo "<span class='text-smaller text-dark strong'>".$locale['forum_051'].":</span> <span class='text-smaller'>".$data['forum_alias']." </span>\n";
			echo "</div></div>\n"; // end row
			echo "</div>\n";
			echo "</div>\n</div>\n";
			$i++;
		}
		if ($max_rows > $threads_per_page) {
			$ext = (isset($_GET['parent_id'])) ? "&amp;parent_id=".$_GET['parent_id']."&amp;" : '';
			echo makepagenav($_GET['rowstart'], $threads_per_page, $max_rows, 3, FUSION_SELF.$aidlink.$ext);
		}
	} else {
		echo "<div class='well text-center'>".$locale['560']."</div>\n";
	}
	closetable();
}
/* Render a move form */
function move_form() {
	global $aidlink, $locale, $ext;
	echo openmodal('move', 'Forum Removal Options', array('static'=>1, 'class'=>'modal-md'));
	echo openform('moveform', 'moveform', 'post', FUSION_SELF.$aidlink.$ext, array('downtime' => 1));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5'>\n";
	echo "<span class='text-dark strong'>".$locale['forum_052']."</span><br/>\n";
	echo "</div><div class='col-xs-12 col-sm-7 col-md-7 col-lg-7'>\n";
	echo form_select_tree('', 'move_threads', 'move_threads', $_GET['forum_id'], array('width'=>'100%', 'inline'=>1, 'disable_opts'=>$_GET['forum_id'], 'hide_disabled'=>1, 'no_root'=>1), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $_GET['forum_id']);
	echo form_checkbox($locale['forum_053'], 'delete_threads', 'delete_threads', '');
	echo "</div>\n</div>\n";
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5'>\n";
	echo "<span class='text-dark strong'>".$locale['forum_054']."</span><br/>\n"; // if you move, then need new hcat_key
	echo "</div><div class='col-xs-12 col-sm-7 col-md-7 col-lg-7'>\n";
	echo form_select_tree('', 'move_forums', 'move_forums', $_GET['forum_id'], array('width'=>'100%', 'inline'=>1, 'disable_opts'=>$_GET['forum_id'], 'hide_disabled'=>1, 'no_root'=>1), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $_GET['forum_id']);
	echo form_checkbox($locale['forum_055'], 'delete_forums', 'delete_forums', '');
	echo "</div>\n</div>\n";
	echo "<div class='clearfix'>\n";
	echo form_hidden('', 'forum_remove', 'forum_remove', 1); // key to launch next sequence
	echo form_hidden('', 'forum_id', 'forum_id', $_GET['forum_id']);
	echo form_hidden('', 'forum_branch', 'forum_branch', $_GET['forum_branch']);
	echo form_button($locale['forum_049'], 'submit_move', 'submit_move', 'submit_move', array('class'=>'btn-sm btn-danger m-r-10'));
	echo "<button type='button' class='btn btn-sm btn-default' data-dismiss='modal'><i class='entypo cross'></i> ".$locale['close']."</button>\n";
	echo "</div>\n";
	echo closeform();
	echo closemodal();
}
/* Execute removals */
function remove_forum() {
	global $defender, $forum_index, $locale;
	// ok, so now we want to move_threads and posts
	$forum_id = isset($_POST['forum_id']) ? form_sanitizer($_POST['forum_id'], 0, 'forum_id') : 0;
	$forum_branch = isset($_POST['forum_branch']) ? form_sanitizer($_POST['forum_branch'], 0, 'forum_branch') : 0;
	$threads_to_forum = isset($_POST['move_threads']) ? form_sanitizer($_POST['move_threads'], 0, 'move_threads') : '';
	$delete_threads =  isset($_POST['delete_threads']) ? 1 : 0;
	$subforums_to_forum = isset($_POST['move_forums']) ? form_sanitizer($_POST['move_forums'], 0, 'move_forums') : '';
	$delete_forums = isset($_POST['delete_forums']) ? 1 : 0;
	// indexed forum branch.
	//$branch = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat', "WHERE forum_branch='".$forum_branch."'");

	// move whole forum to another location
	if (!$delete_threads && $threads_to_forum) {
		// simple move
		dbquery("UPDATE ".DB_FORUM_THREADS." SET forum_id='".$threads_to_forum."' WHERE forum_id='".$forum_id."'");
		dbquery("UPDATE ".DB_FORUM_POSTS." SET forum_id='".$threads_to_forum."' WHERE forum_id='".$forum_id."'");
		prune_forums($forum_index, $forum_id);
	}
	// wipe everything
	elseif ($delete_threads) {
		// remove all threads and all posts in this forum.
		prune_attachment($forum_id); // wipe
		prune_posts($forum_id); // wipe
		prune_threads($forum_id); // wipe
		recalculate_post($forum_id); // wipe
		prune_forums($forum_index, $forum_id); // wipe recursively
	}
	else {
		$defender->stop();
		$defender->addNotice('You cannot move threads and post back to itself.');
	}

	// Subforums
	if (!$delete_forums && $subforums_to_forum) {
		dbquery("UPDATE ".DB_FORUMS." SET forum_cat='".$subforums_to_forum."', forum_branch='".get_hkey(DB_FORUMS, 'forum_id', 'forum_cat', $subforums_to_forum)."' ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$forum_id."'");
		prune_forums($forum_index, $forum_id);
	} elseif ($delete_forums) {
		prune_forums($forum_index, $forum_id);
	} else {
		$defender->stop();
		$defender->addNotice('You cannot move a forum to itself.');
	}
}
/* Execute breadcrumb autopush */
function forum_breadcrumbs() {
	global $aidlink, $forum_index;
	/* Make an infinity traverse */
	function breadcrumb_arrays($index, $id) {
		global $aidlink;
		$crumb = &$crumb;
		//$crumb += $crumb;
		if (isset($index[get_parent($index, $id)])) {
			$_name = dbarray(dbquery("SELECT forum_id, forum_name FROM ".DB_FORUMS." WHERE forum_id='".$id."'"));
			$crumb = array('link'=>FUSION_SELF.$aidlink."&amp;parent_id=".$_name['forum_id'], 'title'=>$_name['forum_name']);
			if (isset($index[get_parent($index, $id)])) {
				if (get_parent($index, $id) == 0) {
					return $crumb;
				}
				$crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
				$crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
			}
		}
		return $crumb;
	}
	// then we make a infinity recursive function to loop/break it out.
	$crumb = breadcrumb_arrays($forum_index, $_GET['parent_id']);
	// then we sort in reverse.
	if (count($crumb['title']) > 1)  { krsort($crumb['title']); krsort($crumb['link']); }
	// then we loop it out using Dan's breadcrumb.
	add_to_breadcrumbs(array('link'=>FUSION_SELF.$aidlink, 'title'=>'Forum Board Index'));
	if (count($crumb['title']) > 1) {
		foreach($crumb['title'] as $i => $value) {
			add_to_breadcrumbs(array('link'=>$crumb['link'][$i], 'title'=>$value));
		}
	} elseif (isset($crumb['title'])) {
		add_to_breadcrumbs(array('link'=>$crumb['link'], 'title'=>$crumb['title']));
	}
	// hola!
}
/* Render Status Message */
function forum_status_message() {
	global $locale;
	if (isset($_GET['status'])) {
		if ($_GET['status'] == 'cns') {
			$message = $locale['forum_notice_1'];
		} elseif ($_GET['status'] == 'cfs') {
			$message = $locale['forum_notice_2'];
		} elseif ($_GET['status'] == 'cls') {
			$message = $locale['forum_notice_3'];
		} elseif ($_GET['status'] == 'cas') {
			$message = $locale['forum_notice_4'];
		} elseif ($_GET['status'] == 'crf') {
			$message = $locale['forum_notice_5'];
		} elseif ($_GET['status'] == 'mup') {
			$message = $locale['forum_notice_6'];
		} elseif ($_GET['status'] == 'md') {
			$message = $locale['forum_notice_7'];
		} elseif ($_GET['status'] == 'rim') {
			$message = $locale['forum_notice_8'];
		} elseif ($_GET['status'] == 'csup') {
			$message = $locale['forum_notice_9'];
		} elseif ($_GET['status'] == 'psv') {
			$message = $locale['forum_notice_10'];
		}
		if (isset($message)) {
			echo "<div id='close-message'><div class='alert alert-info m-t-10 admin-message'>".$message."</div></div>\n";
		}
	}
}

require_once THEMES."templates/footer.php";
?>