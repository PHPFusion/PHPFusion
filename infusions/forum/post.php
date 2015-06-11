<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: post.php
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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_FORUMS)) {
	$_GET['code'] = 404;
	require_once BASEDIR.'error.php';
	exit;
}
require_once THEMES."templates/header.php";
include INFUSIONS."forum/locale/".LOCALESET."forum.php";
add_to_title($locale['global_204']);
require_once INCLUDES."bbcode_include.php";
require_once INCLUDES."mimetypes_include.php";
require_once INFUSIONS."forum/forum_include.php";
require_once INFUSIONS."forum/templates/forum_input.php";

if (!iMEMBER || !isset($_GET['forum_id']) || !isnum($_GET['forum_id'])) { redirect("index.php"); }
if (isset($_GET['thread_id']) && !isnum($_GET['thread_id'])) {	redirect(FORUM.'index.php'); }
if (isset($_GET['post_id']) && !isnum($_GET['post_id'])) { 	redirect(FORUM.'index.php'); }

$data = array();
$info = array();

$result = dbquery("SELECT f.*, f2.forum_name AS forum_cat_name	FROM ".DB_FORUMS." f LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id WHERE f.forum_id='".$_GET['forum_id']."' LIMIT 1");
if (dbrows($result)) {
	$info = dbarray($result);
	$info['lock_edit'] = $settings['forum_edit_lock'] == 1 ? TRUE : FALSE;
	if (!checkgroup($info['forum_access']) || !$info['forum_type'] == 1) { // check cannot post on forum cat
		redirect("index.php");
	}
} else {
	redirect("index.php");
}

// Define Mods
define_forum_mods($info);
// Set breadcrumbs on post form.


if (isset($_GET['action']) && ($_GET['action'] == 'voteup' or $_GET['action'] == 'votedown') && ($info['forum_vote'] != 0 && checkgroup($info['forum_vote'])) && isset($_GET['thread_id']) && isnum($_GET['thread_id']) && isset($_GET['post_id']) && isnum($_GET['post_id'])) {
	if ($_GET['action'] == 'voteup') {
		$data['vote_points'] = 1;
	} elseif ($_GET['action'] == 'votedown') {
		$data['vote_points'] = -1;
	}
	$data['vote_user'] = $userdata['user_id'];
	// imported to do list.
	// @todo: extend on user's rank threshold before can vote. - Reputation threshold- Roadmap 9.1
	// @todo: allow multiple votes / drop $res - Roadmap 9.1
	// @action: cannot vote second time.
	$res = dbcount("('vote_user')", DB_FORUM_VOTES, "vote_user='".$userdata['user_id']."' AND thread_id='".$_GET['thread_id']."'");
	if (!$res) {
		$data['forum_id'] = $_GET['forum_id'];
		$data['thread_id'] = $_GET['thread_id'];
		$data['post_id'] = $_GET['post_id'];
		// @action : cannot vote at your own post.
		$self_post = dbcount("('post_id')", DB_FORUM_POSTS, "post_id='".$_GET['post_id']."' AND post_user='".$userdata['user_id']."");
		if (!$self_post) {
			$data['vote_datestamp'] = time();
			dbquery_insert(DB_FORUM_VOTES, $data, 'save', array('noredirect'=>1, 'no_unique'=>1));
			// lock thread if point threshold reached on that specific post id.
			if ($info['forum_answer_threshold'] >0) {
				$vote_result = dbquery("SELECT SUM('vote_points'), thread_id FROM ".DB_FORUM_VOTES." WHERE post_id='".$data['post_id']."'");
				$v_data = dbarray($vote_result);
				if ($info['forum_answer_threshold'] !=0 && $v_data['vote_points'] >= $info['forum_answer_threshold']) {
					$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET 'thread_locked'='1' WHERE thread_id='".$v_data['thread_id']."'");
				}
			}
			redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id']);
		} else {
			redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id'].'&error=vote_self');
		}
	} else {
		redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id'].'&error=vote');
	}
}

elseif ((isset($_GET['action']) && $_GET['action'] == 'newthread') && ($info['forum_post'] != 0 && checkgroup($info['forum_post']))) {
	// Make new thread - actio nmust be newthread
	$data['new'] = 1;
	add_breadcrumb(array('link'=>FORUM.'index.php?viewforum&amp;forum_id='.$info['forum_id'].'&amp;parent_id='.$info['forum_cat'], 'title'=>'New Thread'));
}
elseif (isset($_GET['action']) && $_GET['action'] == 'reply' && ($info['forum_reply'] != 0 && checkgroup($info['forum_reply'])) && isset($_GET['thread_id']) && isnum($_GET['thread_id'])) {
// fully ported
}
elseif (isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['thread_id']) && isnum($_GET['thread_id']) && isset($_GET['post_id']) && isnum($_GET['post_id'])) {
	// fetch data.
	$verify_thread = dbcount("('thread_id')", DB_FORUM_THREADS, "thread_id='".$_GET['thread_id']."' AND forum_id='".$info['forum_id']."' AND thread_hidden='0'");
	if ($verify_thread) {
		$result = dbquery("SELECT tp.*, tt.thread_subject, tt.thread_poll, tt.thread_author, tt.thread_locked, MIN(tp2.post_id) AS first_post
				FROM ".DB_FORUM_POSTS." tp
				INNER JOIN ".DB_FORUM_THREADS." tt on tp.thread_id=tt.thread_id
				INNER JOIN ".DB_FORUM_POSTS." tp2 on tp.thread_id=tp2.thread_id
				WHERE tp.post_id='".$_GET['post_id']."' AND tp.thread_id='".$_GET['thread_id']."' AND tp.forum_id='".$info['forum_id']."' GROUP BY tp2.post_id
				");
		if (dbrows($result)) {
			$data = dbarray($result);
			// verify mode
			$data['edit'] = 1;
			// if forum edit locked, only can edit the last post unless you are the moderator.
			$last_post = dbarray(dbquery("SELECT post_id FROM ".DB_FORUM_POSTS." WHERE thread_id='".$_GET['thread_id']."' AND forum_id='".$_GET['forum_id']."' AND post_hidden='0' ORDER BY post_datestamp DESC LIMIT 1"));
			if (iMOD || !$data['thread_locked'] && (($info['forum_edit_lock'] && $last_post['post_id'] == $data['post_id'] && $userdata['user_id'] == $data['post_author']) || (!$info['forum_edit_lock'] && $userdata['user_id'] == $data['post_author']))) {
				// Attachments Info
				if ($info['forum_attach'] && checkgroup($info['forum_attach'])) {
					$result = dbquery("SELECT attach_id, attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."'");
					$counter = 0;
					if (dbrows($result)) {
						while ($adata = dbarray($result)) {
							$info['attachment'][$adata['attach_id']] = $adata['attach_name'];
							$counter++;
						}
						$info['attachmax_count'] = ($settings['attachmax_count']-$counter <= 0 ? "-2" : $settings['attachmax_count']-$counter);
					}
				}
				// Poll Data
				if ($info['forum_poll'] && checkgroup($info['forum_poll'])) {
					if ($data['thread_poll'] && ($data['post_author'] == $data['thread_author']) && ($userdata['user_id'] == $data['thread_author'] || iSUPERADMIN || iMOD)) {
						$result = dbquery("SELECT * FROM ".DB_FORUM_POLLS." WHERE thread_id='".$_GET['thread_id']."'");
						if (dbrows($result)>0) {
							$data += dbarray($result);
							$result = dbquery("SELECT forum_poll_option_text FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY forum_poll_option_id ASC");
							while ($_pdata = dbarray($result)) {
								$data['poll_opts'][] = $_pdata['forum_poll_option_text'];
							}
						}
					}
				}
			} else {
				redirect(FORUM."index.php"); // edit rules failed.
			}
		} else {
			redirect(FORUM."index.php"); // cannot find post_id.
		}
	} else {
		redirect(FORUM."index.php"); // cannot find thread
	}
}
else {
	// Note: if edit/reply/quote redirected to forum/index.php, then $_GET['action'] is missing caused by URL Rewrite Class /includes/classes/rewrite.class.php
	redirect(FORUM.'index.php');
}

if (iMEMBER){
	include "post_actions.php";
	postform($data, $info);
} else {
	// cookie suddenly expire.
	//$defender->stop();
	//$defender->AddNotice('Sorry we cannot process your post. Please relogin to access the forum posting again.');
	//redirect("postify.php?post=edit&error=$error&forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."&post_id=".$_GET['post_id']);
}

require_once THEMES."templates/footer.php";