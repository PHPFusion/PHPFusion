<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: newthread.php
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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_FORUMS)) {
	$_GET['code'] = 404;
	require_once BASEDIR.'error.php';
	exit;
}
require_once THEMES."templates/header.php";
include INFUSIONS."forum/locale/".LOCALESET."forum.php";
add_to_title($locale['forum_0000']);
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."forum/classes/Forum.php";
require_once INFUSIONS."forum/classes/Functions.php";
require_once INFUSIONS."forum/forum_include.php";
require_once INFUSIONS."forum/templates/forum_input.php";
if (iMEMBER) {
	$forum_settings = get_settings('forum');
	add_to_meta("description", $locale['forum_0000']);
	add_breadcrumb(array("link"=> FORUM."index.php", "title"=> $locale['forum_0000']));
	add_to_title($locale['global_201'].$locale['forum_0057']);

	if (PHPFusion\Forums\Functions::verify_forum($_GET['forum_id'])) {

		$forum = new PHPFusion\Forums\Forum();
		$forum_data = dbarray(dbquery("SELECT f.*, f2.forum_name AS forum_cat_name
				FROM ".DB_FORUMS." f
				LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
				WHERE f.forum_id='".intval($_GET['forum_id'])."'
				AND ".groupaccess('f.forum_access')."
				"));
		if ($forum_data['forum_type'] == 1) redirect(INFUSIONS."forum/index.php");
		define_forum_mods($forum_data);
		// Use the new permission settings
		$forum->setForumPermission($forum_data);
		$permission = $forum->getForumPermission();
		$forum_data['lock_edit'] = $forum_settings['forum_edit_lock'] == 1 ? TRUE : FALSE;
		if ($permission['can_post'] && $permission['can_access']) {
			add_breadcrumb(array(
							   'link' => INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id'].'&amp;parent_id='.$forum_data['forum_cat'],
							   'title' => $forum_data['forum_name']
						   ));
			add_breadcrumb(array(
							   'link' => INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id'].'&amp;parent_id='.$forum_data['forum_cat'],
							   'title' => $locale['forum_0057']
						   ));
			/**
			 * Generate a poll form
			 */
			$poll_form = "";
			if ($permission['can_create_poll']) {
				// initial data to push downwards
				$pollData = array(
					'thread_id' => 0,
					'forum_poll_title' => !empty($_POST['forum_poll_title']) ? form_sanitizer($_POST['forum_poll_title'], '', 'forum_poll_title') : '',
					'forum_poll_start' => time(), // time poll started
					'forum_poll_length' => 2, // how many poll options we have
					'forum_poll_votes' => 0, // how many vote this poll has
				);
				// counter of lengths
				$option_data[1] = "";
				$option_data[2] = "";
				// Do a validation if checked add_poll
				if (isset($_POST['add_poll'])) {
					$pollData = array(
						'thread_id' => 0,
						'forum_poll_title' => isset($_POST['forum_poll_title']) ? form_sanitizer($_POST['forum_poll_title'], '', 'forum_poll_title') : '',
						'forum_poll_start' => time(), // time poll started
						'forum_poll_length' => count($option_data), // how many poll options we have
						'forum_poll_votes' => 0, // how many vote this poll has
					);
					// calculate poll lengths
					if (!empty($_POST['poll_options']) && is_array($_POST['poll_options'])) {
						foreach ($_POST['poll_options'] as $i => $value) {
							$option_data[$i] = form_sanitizer($value, '', "poll_options[$i]");
						}
					}
				}
				if (isset($_POST['add_poll_option']) && isset($_POST['poll_options'])) {
					// reindex the whole array with blank values.
					foreach ($_POST['poll_options'] as $i => $value) {
						$option_data[$i] = form_sanitizer($value, '', "poll_options[$i]");
					}
					if ($defender->safe()) {
						$option_data = array_values(array_filter($option_data));
						array_unshift($option_data, NULL);
						unset($option_data[0]);
						$pollData['forum_poll_length'] = count($option_data);
					}
					array_push($option_data, '');
				}
				$poll_field = '';
				$poll_field['poll_field'] = form_text('forum_poll_title', $locale['forum_0604'], $pollData['forum_poll_title'], array(
																			'max_length' => 255,
																			'placeholder' => $locale['forum_0604a'],
																			'inline' => TRUE,
																			'required' => TRUE
																		));
				for ($i = 1; $i <= count($option_data); $i++) {
					$poll_field['poll_field'] .= form_text("poll_options[$i]", sprintf($locale['forum_0606'], $i), $option_data[$i], array(
						'max_length' => 255,
						'placeholder' => $locale['forum_0605'],
						'inline' => TRUE,
						'required' => $i <= 2 ? TRUE : FALSE
					));
				}
				$poll_field['poll_field'] .= "<div class='col-xs-12 col-sm-offset-3'>\n";
				$poll_field['poll_field'] .= form_button('add_poll_option', $locale['forum_0608'], $locale['forum_0608'], array('class' => 'btn-primary btn-sm'));
				$poll_field['poll_field'] .= "</div>\n";
				$info = array(
					'title' => $locale['forum_0366'],
					'description' => $locale['forum_0630'],
					'field' => $poll_field
				);
				ob_start();
				echo form_checkbox("add_poll", $locale['forum_0366'], isset($_POST['add_poll']) ? TRUE : FALSE);
				echo "<div id='poll_form' class='poll-form' style='display:none;'>\n";
				echo "<div class='well clearfix'>\n";
				echo "<!--pre_form-->\n";
				echo $info['field']['poll_field'];
				echo "</div>\n";
				echo "</div>\n";
				$poll_form = ob_get_contents();
				ob_end_clean();
			}

			$thread_data = array(
				'forum_id' => $forum_data['forum_id'],
				'thread_id' => 0,
				'thread_subject' => isset($_POST['thread_subject']) ? form_sanitizer($_POST['thread_subject'], '', 'thread_subject') : '',
				'thread_author' => $userdata['user_id'],
				'thread_views' => 0,
				'thread_lastpost' => time(),
				'thread_lastpostid' => 0, // need to run update
				'thread_lastuser' => $userdata['user_id'],
				'thread_postcount' => 1, // already insert 1 postcount.
				'thread_poll' => 0,
				'thread_sticky' => isset($_POST['thread_sticky']) ? 1 : 0,
				'thread_locked' => isset($_POST['thread_sticky']) ? 1 : 0,
				'thread_hidden' => 0,
			);
			$post_data = array(
				'forum_id' => $forum_data['forum_id'],
				'forum_cat' => $forum_data['forum_cat'],
				'thread_id' => 0,
				'post_id' => 0,
				'post_message' => isset($_POST['post_message']) ? form_sanitizer($_POST['post_message'], '', 'post_message') : '',
				'post_showsig' => isset($_POST['post_showsig']) ? 1 : 0,
				'post_smileys' => !isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 0 : 1,
				'post_author' => $userdata['user_id'],
				'post_datestamp' => time(),
				'post_ip' => USER_IP,
				'post_ip_type' => USER_IP_TYPE,
				'post_edituser' => 0,
				'post_edittime' => 0,
				'post_editreason' => '',
				'post_hidden' => 0,
				'notify_me' => isset($_POST['notify_me']) ? 1 : 0,
				'post_locked' => 0, //$forum_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0,
			);
			// Execute post new thread
			if (isset($_POST['post_newthread']) && $defender->safe()) {
				require_once INCLUDES."flood_include.php";
				// all data is sanitized here.
				if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
					if ($defender->safe()) {
						// create a new thread.
						dbquery_insert(DB_FORUM_THREADS, $thread_data, 'save', array(
							'primary_key' => 'thread_id',
							'keep_session' => TRUE
						));
						$post_data['thread_id'] = dblastid();
						$pollData['thread_id'] = dblastid();
						dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', array(
							'primary_key' => 'post_id',
							'keep_session' => TRUE
						));
						$post_data['post_id'] = dblastid();

						// Attach files if permitted
						if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0]) && $forum->getForumPermission("can_upload_attach")) {
							$upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
							if ($upload['error'] == 0) {
								foreach ($upload['target_file'] as $arr => $file_name) {
									$adata = array(
										'thread_id' => $post_data['thread_id'],
										'post_id' => $post_data['post_id'],
										'attach_name' => $file_name,
										'attach_mime' => $upload['type'][$arr],
										'attach_size' => $upload['source_size'][$arr],
										'attach_count' => '0', // downloaded times
									);
									dbquery_insert(DB_FORUM_ATTACHMENTS, $adata, "save", array('keep_session' => TRUE));
								}
							}
						}

						dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");
						// Update stats in forum and threads
						// find all parents and update them
						$list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $post_data['forum_id']);
						foreach ($list_of_forums as $fid) {
							dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$fid."'");
						}
						// update current forum
						dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$post_data['forum_id']."'");
						// update current thread
						dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_data['post_id']."', thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$post_data['thread_id']."'");
						// set notify
						if ($forum_settings['thread_notify'] && isset($_POST['notify_me']) && $post_data['thread_id']) {
							if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$post_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")) {
								dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$post_data['thread_id']."', '".time()."', '".$post_data['post_author']."', '1')");
							}
						}
						// Add poll if exist
						if (!empty($option_data) && isset($_POST['add_poll'])) {
							dbquery_insert(DB_FORUM_POLLS, $pollData, 'save');
							$poll_option_data['thread_id'] = $pollData['thread_id'];
							$i = 1;
							foreach ($option_data as $option_text) {
								if ($option_text) {
									$poll_option_data['forum_poll_option_id'] = $i;
									$poll_option_data['forum_poll_option_text'] = $option_text;
									$poll_option_data['forum_poll_option_votes'] = 0;
									dbquery_insert(DB_FORUM_POLL_OPTIONS, $poll_option_data, 'save');
									$i++;
								}
							}
							dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_poll='1' WHERE thread_id='".$pollData['thread_id']."'");
						}
					}
					if ($defender->safe()) {
						redirect("postify.php?post=new&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;parent_id=".intval($post_data['forum_cat'])."&amp;thread_id=".intval($post_data['thread_id'].""));
					}
				}
			}
			$form_action = (fusion_get_settings("site_seo") ? FUSION_ROOT : '').INFUSIONS."forum/newthread.php?forum_id=".$post_data['forum_id'];
			$info = array(
				'title' => $locale['forum_0057'],
				'description' => '',
				'openform' => openform('input_form', 'post', $form_action, array('enctype' => $permission['can_upload_attach'])),
				// use new permission to toggle enctype
				'closeform' => closeform(),
				'forum_id_field' => '',
				'thread_id_field' => '',
				"forum_field" => "",
				'subject_field' => form_text('thread_subject', $locale['forum_0600'], $thread_data['thread_subject'], array(
					'required' => 1,
					'placeholder' => $locale['forum_2001'],
					'error_text' => '',
					'class' => 'm-t-20 m-b-20'
				)),
				'message_field' => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'], array(
					'required' => 1,
					'error_text' => '',
					'autosize' => 1,
					'no_resize' => 1,
					'preview' => 1,
					'form_name' => 'input_form',
					'bbcode' => 1
				)),
				'attachment_field' => $forum->getForumPermission("can_upload_attach") ? form_fileinput('file_attachments[]', $locale['forum_0557'], "", array(
																															   'input_id' => 'file_attachments',
																															   'upload_path' => INFUSIONS.'forum/attachments/',
																															   'type' => 'object',
																															   'preview_off' => TRUE,
																															   "multiple" => TRUE,
																															   "inline" => FALSE,
																															   'max_count' => $forum_settings['forum_attachmax_count'],
																															   'valid_ext' => $forum_settings['forum_attachtypes'],
																															   "class" => "m-b-0",
																														   ))."
								 <div class='m-b-20'>\n<small>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n" : "",
				'poll_form' => $poll_form,
				'smileys_field' => form_checkbox('post_smileys', $locale['forum_0622'], $post_data['post_smileys'], array('class' => 'm-b-0')),
				'signature_field' => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0623'], $post_data['post_showsig'], array('class' => 'm-b-0')) : '',
				'sticky_field' => (iMOD || iSUPERADMIN) ? form_checkbox('thread_sticky', $locale['forum_0620'], $thread_data['thread_sticky'], array('class' => 'm-b-0')) : '',
				'lock_field' => (iMOD || iSUPERADMIN) ? form_checkbox('thread_locked', $locale['forum_0621'], $thread_data['thread_locked'], array('class' => 'm-b-0')) : '',
				'edit_reason_field' => '',
				'delete_field' => '',
				'hide_edit_field' => '',
				'post_locked_field' => '',
				'notify_field' => $forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0626'], $post_data['notify_me'], array('class' => 'm-b-0')) : '',
				'post_buttons' => form_button('post_newthread', $locale['forum_0057'], $locale['forum_0057'], array('class' => 'btn-primary btn-sm')).form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default btn-sm m-l-10')),
				'last_posts_reply' => '',
			);
			// add a jquery to toggle the poll form
			add_to_jquery("
			if ($('#add_poll').is(':checked')) {
				$('#poll_form').show();
			} else {
				$('#poll_form').hide();
			}
			$('#add_poll').bind('click', function() {
				if ($(this).is(':checked')) {
					$('#poll_form').slideDown();
				} else {
					$('#poll_form').slideUp();
				}
			});
			");
			postform($info);
		} else {
			redirect(INFUSIONS.'forum/index.php');
		}
	} else {

		/*
		 * Quick New Forum Posting.
		 * Does not require to run permissions.
		 * Does not contain forum poll.
		 * Does not contain attachment
		 */
		if (!dbcount("(forum_id)", DB_FORUMS, "forum_type !='1'")) redirect(INFUSIONS."forum/index.php");
		add_breadcrumb(array("link"=> FORUM."newthread.php?forum_id=0", "title"=> $locale['forum_0057']));

		$thread_data = array(
			'forum_id' => isset($_POST['forum_id']) ? form_sanitizer($_POST['forum_id'], 0, "forum_id") : 0,
			'thread_id' => 0,
			'thread_subject' => isset($_POST['thread_subject']) ? form_sanitizer($_POST['thread_subject'], '', 'thread_subject') : '',
			'thread_author' => $userdata['user_id'],
			'thread_views' => 0,
			'thread_lastpost' => time(),
			'thread_lastpostid' => 0, // need to run update
			'thread_lastuser' => $userdata['user_id'],
			'thread_postcount' => 1, // already insert 1 postcount.
			'thread_poll' => 0,
			'thread_sticky' => isset($_POST['thread_sticky']) ? TRUE : FALSE,
			'thread_locked' => isset($_POST['thread_sticky']) ? TRUE : FALSE,
			'thread_hidden' => 0,
		);
		$post_data = array(
			'forum_id' => isset($_POST['forum_id']) ? form_sanitizer($_POST['forum_id'], 0, "forum_id") : 0,
			"forum_cat" => 0, // for redirect
			'thread_id' => 0, // required lastid
			'post_id' => 0, // auto insertion
			'post_message' => isset($_POST['post_message']) ? form_sanitizer($_POST['post_message'], '', 'post_message') : '',
			'post_showsig' => isset($_POST['post_showsig']) ? TRUE : FALSE,
			'post_smileys' => !isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? FALSE : TRUE,
			'post_author' => $userdata['user_id'],
			'post_datestamp' => time(),
			'post_ip' => USER_IP,
			'post_ip_type' => USER_IP_TYPE,
			'post_edituser' => 0,
			'post_edittime' => 0,
			'post_editreason' => '',
			'post_hidden' => FALSE,
			'notify_me' => isset($_POST['notify_me']) ? TRUE : FALSE,
			'post_locked' => 0,
		);

		// go for a new thread posting.
		// check data
		// and validate
		// do not run attach, and do not run poll.
		if (isset($_POST['post_newthread']) && $defender->safe()) {
			require_once INCLUDES."flood_include.php";
			// all data is sanitized here.
			if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice

				// get the forum data.
				// run permissions for posting
				//
				if (PHPFusion\Forums\Functions::verify_forum($thread_data['forum_id'])) {
					$forum = new PHPFusion\Forums\Forum();
					$forum_data = dbarray(dbquery("SELECT f.*, f2.forum_name AS forum_cat_name
					FROM ".DB_FORUMS." f
					LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
					WHERE f.forum_id='".intval($thread_data['forum_id'])."'
					AND ".groupaccess('f.forum_access')."
					"));
					if ($forum_data['forum_type'] == 1) redirect(INFUSIONS."forum/index.php");
					define_forum_mods($forum_data);
					// Use the new permission settings
					$forum->setForumPermission($forum_data);
					$permission = $forum->getForumPermission();
					$forum_data['lock_edit'] = $forum_settings['forum_edit_lock'] == 1 ? TRUE : FALSE;
					if ($permission['can_post'] && $permission['can_access']) {

						$post_data['forum_cat'] = $forum_data['forum_cat'];
						// create a new thread.
						dbquery_insert(DB_FORUM_THREADS, $thread_data, 'save', array(
							'primary_key' => 'thread_id',
							'keep_session' => TRUE
						));
						$post_data['thread_id'] = dblastid();

						dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', array(
							'primary_key' => 'post_id',
							'keep_session' => TRUE
						));
						$post_data['post_id'] = dblastid();
						dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");
						// Update stats in forum and threads
						// find all parents and update them
						$list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $post_data['forum_id']);
						foreach ($list_of_forums as $fid) {
							dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$fid."'");
						}
						// update current forum
						dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$post_data['forum_id']."'");
						// update current thread
						dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_data['post_id']."', thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$post_data['thread_id']."'");
						// set notify
						if ($forum_settings['thread_notify'] && isset($_POST['notify_me']) && $post_data['thread_id']) {
							if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$post_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")) {
								dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$post_data['thread_id']."', '".time()."', '".$post_data['post_author']."', '1')");
							}
						}
						if ($defender->safe()) {
							redirect("postify.php?post=new&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;parent_id=".intval($post_data['forum_cat'])."&amp;thread_id=".intval($post_data['thread_id'].""));
						}
					} else {
						addNotice("danger", $locale['forum_0186']);
					}
				} else {
					addNotice("danger", $locale['forum_0187']);
					redirect(INFUSIONS."forum/index.php");
				}
			}
		}

		$form_action = (fusion_get_settings("site_seo") ? FUSION_ROOT : '').INFUSIONS."forum/newthread.php";
		$info = array(
			'title' => $locale['forum_0057'],
			'description' => '',
			'openform' => openform('input_form', 'post', $form_action, array('enctype' => FALSE)),
			'closeform' => closeform(),
			'forum_id_field' => '',
			'thread_id_field' => '',
			'forum_field' => form_select_tree("forum_id", $locale['forum_0395'], $thread_data['forum_id'],
											  array(
												  "required"=>true,
												  "width"=>"100%",
												  "no_root" => TRUE,
											  ),
				DB_FORUMS, "forum_name", "forum_id", "forum_cat"),
			'subject_field' => form_text('thread_subject', $locale['forum_0600'], $thread_data['thread_subject'], array(
				'required' => 1,
				'placeholder' => $locale['forum_2001'],
				'error_text' => '',
				'class' => 'm-t-20 m-b-20'
			)),
			'message_field' => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'], array(
				'required' => 1,
				'error_text' => '',
				'autosize' => 1,
				'no_resize' => 1,
				'preview' => 1,
				'form_name' => 'input_form',
				'bbcode' => 1
			)),
			'attachment_field' => "",
			'poll_form' => "",
			'smileys_field' => form_checkbox('post_smileys', $locale['forum_0622'], $post_data['post_smileys'], array('class' => 'm-b-0')),
			'signature_field' => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0623'], $post_data['post_showsig'], array('class' => 'm-b-0')) : '',
			'sticky_field' => (iSUPERADMIN) ? form_checkbox('thread_sticky', $locale['forum_0620'], $thread_data['thread_sticky'], array('class' => 'm-b-0')) : '',
			'lock_field' => (iSUPERADMIN) ? form_checkbox('thread_locked', $locale['forum_0621'], $thread_data['thread_locked'], array('class' => 'm-b-0')) : '',
			'edit_reason_field' => '',
			'delete_field' => '',
			'hide_edit_field' => '',
			'post_locked_field' => '',
			'notify_field' => $forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0626'], $post_data['notify_me'], array('class' => 'm-b-0')) : '',
			'post_buttons' => form_button('post_newthread', $locale['forum_0057'], $locale['forum_0057'], array('class' => 'btn-primary btn-sm')).form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default btn-sm m-l-10')),
			'last_posts_reply' => '',
		);
		postform($info);
	}
} else {
	redirect(INFUSIONS.'forum/index.php');
}
require_once THEMES."templates/footer.php";