<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: newthread.php
| Author: Nick Jones (Digitanium)
| Co-author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_FORUMS)) {
	$_GET['code'] = 404;
	require_once BASEDIR.'error.php';
	exit;
}

require_once THEMES."templates/header.php";
include INFUSIONS."forum/locale/".LOCALESET."forum.php";

add_to_title($locale['global_204']);

require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."forum/classes/Functions.php";
require_once INFUSIONS."forum/forum_include.php";
require_once INFUSIONS."forum/templates/forum_input.php";

if (iMEMBER && PHPFusion\Forums\Functions::verify_forum($_GET['forum_id'])) {
	// yield forum_id and forum_id before that
	$inf_settings = get_settings('forum');
	$forum_data = dbarray(dbquery("SELECT f.*, f2.forum_name AS forum_cat_name
						FROM ".DB_FORUMS." f
						LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
						WHERE f.forum_id='".intval($_GET['forum_id'])."'
						AND ".groupaccess('f.forum_access')."
					")
			);
	// main forum. // get forum is sanitized
	if ($forum_data['forum_type'] == 1) redirect(INFUSIONS."forum/index.php");
	define_forum_mods($forum_data);
	$forum_data['lock_edit'] = $inf_settings['forum_edit_lock'] == 1 ? TRUE : FALSE;
	// set permissions.
	$info['permissions'] = array(
		'can_post' => iMOD or iSUPERADMIN ? true : (checkgroup($forum_data['forum_post']) && checkgroup($forum_data['forum_lock'])) ? true : false,
		'can_attach' => iMOD or iSUPERADMIN ? true : checkgroup($forum_data['forum_attach']) && $forum_data['forum_allow_attach'] ? true : false,
	);

	if ($info['permissions']['can_post']) {

		add_breadcrumb(array('link'=>INFUSIONS.'forum/index.php', 'title'=>$locale['forum_0000']));
		add_breadcrumb(array('link'=>INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id'].'&amp;parent_id='.$forum_data['forum_cat'], 'title'=>$forum_data['forum_name']));
		add_breadcrumb(array('link'=>INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id'].'&amp;parent_id='.$forum_data['forum_cat'], 'title'=>$locale['forum_0057']));

		$thread_data = array(
			'forum_id' => $forum_data['forum_id'],
			'thread_id' => 0,
			'thread_subject' => isset($_POST['thread_subject']) ? form_sanitizer($_POST['thread_subject'], '', 'thread_subject') : '',
			'thread_author' => $userdata['user_id'],
			'thread_views' => 0,
			'thread_lastpost' => time(),
			'thread_lastpostid' => 0, // need to run update
			'thread_lastuser' => $userdata['user_id'],
			'thread_postcount' => 1,
			'thread_poll' => 0,
			'thread_sticky' => 0,
			'thread_locked' => 0,
			'thread_hidden' => 0,
		);

		$post_data = array(
			'forum_id' => $forum_data['forum_id'],
			'thread_id' => 0,
			'post_id' => 0,
			'post_message' => isset($_POST['post_message']) ? form_sanitizer($_POST['post_message'], '', 'post_message') : '',
			'post_showsig' => isset($_POST['post_showsig']) ? 1 : 0,
			'post_smileys' => !isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? false : true,
			'post_author' => $userdata['user_id'],
			'post_datestamp' => time(),
			'post_ip' =>  USER_IP,
			'post_ip_type' => USER_IP_TYPE,
			'post_edituser' => 0,
			'post_edittime' => 0,
			'post_editreason' => '',
			'post_hidden' => false,
			'notify_me' => false,
			'post_locked' => 0, //$inf_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0,
		);

		// Execute post new thread
		if (isset($_POST['post_newthread'])) {
			require_once INCLUDES."flood_include.php";
			// all data is sanitized here.
			if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice

				// create a new thread.
				dbquery_insert(DB_FORUM_THREADS, $thread_data, 'save', array('primary_key'=>'thread_id', 'keep_session'=>true));
				$post_data['thread_id'] = dblastid();

				dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', array('primary_key'=>'post_id', 'keep_session'=>true));
				$post_data['post_id'] = dblastid();

				if (!defined('FUSION_NULL')) {
					dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpostid='".$post_data['post_id']."' WHERE thread_id='".$post_data['thread_id']."'");
					dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");

					// save all file attachments and get error
					if (is_uploaded_file($_FILES['file_attachments']['tmp_name'][0])) {
						$upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
						if ($upload['error'] == 0) {
							foreach($upload['target_file'] as $arr => $file_name) {
								$attachment = array(
									'thread_id' => $post_data['thread_id'],
									'post_id' => $post_data['post_id'],
									'attach_name' => $file_name,
									'attach_mime' => $upload['type'][$arr],
									'attach_size' => $upload['source_size'][$arr],
									'attach_count' => '0', // downloaded times?
								);
								dbquery_insert(DB_FORUM_ATTACHMENTS, $attachment, 'save', array('keep_session'=>true));
							}
						}
					}
					// Update stats in forum and threads
					// find all parents and update them
					$list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $post_data['forum_id']);
					foreach($list_of_forums as $fid) {
						dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$fid."'");
					}
					// update current forum
					dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$post_data['forum_id']."'");
					// update current thread
					dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_data['post_id']."', thread_postcount=thread_postcount+1, thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$post_data['thread_id']."'");
					// set notify
					if ($inf_settings['thread_notify'] && isset($_POST['notify_me']) && $post_data['thread_id']) {
						if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$post_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")) {
							dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$post_data['thread_id']."', '".time()."', '".$post_data['post_author']."', '1')");
						}
					}
				}
				$error = defined("FUSION_NULL") ? '1' : '0';
				redirect("postify.php?post=new&error=$error&amp;forum_id=".intval($post_data['forum_id'])."&amp;parent_id=".intval($post_data['forum_cat'])."&amp;forum_branch=".intval($post_data['forum_branch'])."&amp;thread_id=".intval($post_data['thread_id'].""));
			}
		}

		// template data
		$form_action = ($settings['site_seo'] ? FUSION_ROOT : '').INFUSIONS."forum/newthread.php?forum_id=".$post_data['forum_id'];
		$info = array(
			'title' => $locale['forum_0057'],
			'description' => '',
			'openform' =>  openform('input_form', 'post', $form_action, array('enctype' => 1, 'max_tokens' => 1)),
			'closeform' => closeform(),
			'forum_id_field' => '',
			'thread_id_field' => '',
			'subject_field' => form_text('thread_subject', $locale['forum_0600'], $thread_data['thread_subject'], array('required' => 1, 'placeholder' => $locale['forum_2001'], 'error_text' => '', 'class' => 'm-t-20 m-b-20')),
			'message_field' => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'], array('required' => 1, 'error_text' => '', 'autosize' => 1, 'no_resize' => 1, 'preview' => 1, 'form_name' => 'input_form', 'bbcode' => 1)),
			// happens only in EDIT
			'attachment_field' => $info['permissions']['can_attach'] ? array('title'=>$locale['forum_0557'], 'field'=>
					"<div class='m-b-10'>".sprintf($locale['forum_0559'], parsebytesize($settings['attachmax']), str_replace(',', ' ', $inf_settings['attachtypes']), $inf_settings['attachmax_count'])."</div>\n
					".form_fileinput('', 'file_attachments[]', 'file_attachments', INFUSIONS.'forum/attachments', '', array('type'=>'object', 'preview_off'=>true, 'multiple'=>true, 'max_count'=>$inf_settings['attachmax_count'], 'valid_ext'=>$inf_settings['attachtypes']))
				) : array(),
			'poll' => array(),
			'smileys_field' => form_checkbox('post_smileys', $locale['forum_0622'], $post_data['post_smileys'], array('class' => 'm-b-0')),
			'signature_field' => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ?form_checkbox('post_showsig', $locale['forum_0623'], $post_data['post_showsig'], array('class' => 'm-b-0')) : '',
			'sticky_field' => (iMOD || iSUPERADMIN) ? form_checkbox('thread_sticky', $locale['forum_0620'], $thread_data['thread_sticky'], array('class' => 'm-b-0')) : '',
			'lock_field' => (iMOD || iSUPERADMIN) ?  form_checkbox('thread_locked', $locale['forum_0621'], $thread_data['thread_locked'], array('class' => 'm-b-0')) : '',
			'edit_reason_field' => '',
			'delete_field' => '',
			'hide_edit_field' => '',
			'post_locked_field' => '',
			'notify_field' => $inf_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0626'], $post_data['notify_me'], array('class' => 'm-b-0')) : '',
			'post_buttons' => form_button('post_newthread', $locale['forum_0057'], $locale['forum_0057'], array('class' => 'btn-primary btn-sm')).form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default btn-sm m-l-10')),
			'last_posts_reply' => '',
		);
	}
	postform($info);
} else {
	redirect(INFUSIONS.'forum/index.php');
}
require_once THEMES."templates/footer.php";