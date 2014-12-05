<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: viewthread.php
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
require_once dirname(__FILE__)."../../maincore.php";
require_once INCLUDES."forum_include.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum.php";
// Load Template
include THEMES."templates/global/forum.index.php";

// Sanitize Globals
$_GET['forum_id'] = (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) ? $_GET['forum_id'] : 0;
$_GET['pid'] = (isset($_GET['pid']) && isnum($_GET['pid'])) ? $_GET['pid'] : 0;
$_GET['forum_cat'] = (isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])) ? $_GET['forum_cat'] : 0;
$_GET['forum_branch'] = (isset($_GET['forum_branch']) && isnum($_GET['forum_branch'])) ? $_GET['forum_branch'] : 0;
$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart'])) ? $_GET['rowstart'] : '0';
$edit_reason = FALSE;
add_to_title($locale['global_200'].$locale['forum_0000']);

$info = array();
$info['posts_per_page'] = $settings['posts_per_page'];
$info['threads_per_page'] = $settings['threads_per_page'];
$info['lastvisited'] = (isset($info['lastvisited']) && isnum($info['lastvisited'])) ? : $userdata['user_lastvisit'];

/* Errors */
if (isset($_GET['error'])) {
	if ($_GET['error'] == 'vote') {
		notify('Vote rejected', 'You cannot vote for a second time per thread');
	} elseif ($_GET['error'] == 'vote_self') {
		notify('Vote rejected', 'You cannot vote at your own post.');
	}
}

/* Jumps to last links -- there is another with pid in Line 264 */
if (isset($_GET['pid']) && isnum($_GET['pid'])) {
	$result = dbquery("SELECT thread_id FROM ".DB_POSTS." WHERE post_id='".$_GET['pid']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
	//	redirect("viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$_GET['pid']."#post_".$_GET['pid']);
	}
} elseif (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
	redirect("index.php");
}

/* Get Thread and Forum data */
$result = dbquery("SELECT t.*, f.*, f2.forum_name AS forum_cat_name
	FROM ".DB_THREADS." t
	LEFT JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
	LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
	".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." t.thread_id='".$_GET['thread_id']."' AND t.thread_hidden='0'");
if (dbrows($result) > 0) {
	$info += dbarray($result);
	ksort($info);
	if (!checkgroup($info['forum_access']) or $info['thread_hidden'] == "1") {
		redirect("index.php");
	}

	// Moderators
	define_forum_mods($info);
	if (iMOD && (((isset($_POST['delete_posts']) || isset($_POST['move_posts'])) && isset($_POST['delete_post'])) || isset($_GET['error']))) {
		require_once FORUM."viewthread_options.php";
	}

	// Forum Polls.
	$info['permissions']['can_view_poll'] = checkgroup($info['forum_poll']) ? 1 : 0;
	if (checkgroup($info['forum_poll']) && $info['thread_poll']) {
		// for those who have access to see the poll.
		$info['permissions']['can_vote_poll'] = 0;
		$info['permissions']['can_vote_poll'] = checkgroup($info['forum_vote']) ? 1 : 0;
		if ($info['permissions']['can_vote_poll']) {
			$poll_result = dbquery("SELECT tfp.forum_poll_title, tfp.forum_poll_votes, tfv.forum_vote_user_id FROM ".DB_FORUM_POLLS." tfp
						LEFT JOIN ".DB_FORUM_POLL_VOTERS." tfv
						ON tfp.thread_id=tfv.thread_id AND forum_vote_user_id='".$userdata['user_id']."'
						WHERE tfp.thread_id='".$_GET['thread_id']."'");
		} else {
			$poll_result = dbquery("SELECT tfp.forum_poll_title, tfp.forum_poll_votes FROM ".DB_FORUM_POLLS." tfp WHERE tfp.thread_id='".$_GET['thread_id']."'");
		}
		if (dbrows($poll_result) > 0) {
			// poll details.
			$info['poll'] = dbarray($poll_result);
			// get the options
			$p_options = dbquery("SELECT forum_poll_option_votes, forum_poll_option_text FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."'
					ORDER BY forum_poll_option_id ASC
					");
			$poll_option_rows = dbrows($p_options);
			$info['poll']['max_option_id'] = $poll_option_rows;
			if ($poll_option_rows > 0) {
				while ($pdata = dbarray($p_options)) {
					$info['poll']['poll_opts'][] = $pdata;
				}
			}
			// override can vote or not.
			$info['permissions']['can_vote_poll'] = isset($info['poll']['forum_vote_user_id']) ? 0 : 1;
		}
		if ((isset($_POST['poll_option']) && isnum($_POST['poll_option']) && $_POST['poll_option'] <= $info['poll']['max_option_id']) && $info['permissions']['can_vote_poll'] && !defined('FUSION_NULL')) {
			$result = dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_votes=forum_poll_option_votes+1 WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$_POST['poll_option']."'");
			$result = dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_votes=forum_poll_votes+1 WHERE thread_id='".$_GET['thread_id']."'");
			$result = dbquery("INSERT INTO ".DB_FORUM_POLL_VOTERS." (thread_id, forum_vote_user_id, forum_vote_user_ip, forum_vote_user_ip_type) VALUES ('".$_GET['thread_id']."', '".$userdata['user_id']."', '".USER_IP."', '".USER_IP_TYPE."')");
			redirect(FUSION_SELF."?thread_id=".$_GET['thread_id']);
		}
	}

	// Forum Attachments.
	if (checkgroup($info['forum_attach_download'])) {
		$a_result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_id ASC");
		if (dbrows($a_result) > 0) {
			while ($a_data = dbarray($a_result)) {
				if (file_exists(FORUM."attachments/".$a_data['attach_name'])) {
					$info['attachments'][$a_data['post_id']][] = $a_data;
				}
			}
		}
	}

	// Thread Global Information - XSS prevention rowstart
	list($rows, $last_post, $first_post) = dbarraynum(dbquery("SELECT COUNT(post_id), MAX(post_id), MIN(post_id) FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' AND post_hidden='0' GROUP BY thread_id"));
	$info['post_item_rows'] = $rows;
	$info['post_firstpost'] = $first_post;
	$info['post_lastpost'] = $last_post;
	$info['posts_per_page'] = $settings['posts_per_page'];

	// Update View Thread
	dbquery("UPDATE ".DB_THREADS." SET thread_postcount='$rows', thread_lastpostid='$last_post', thread_views=thread_views+1 WHERE thread_id='".$_GET['thread_id']."'");

	// User Fields
	$user_field = array("user_sig" => FALSE, "user_web" => FALSE);
	if (iMEMBER) {
		$thread_match = $info['thread_id']."\|".$info['thread_lastpost']."\|".$info['forum_id'];
		if (($info['thread_lastpost'] > $info['lastvisited']) && !preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads'])) {
			$result = dbquery("UPDATE ".DB_USERS." SET user_threads='".$userdata['user_threads'].".".stripslashes($thread_match)."' WHERE user_id='".$userdata['user_id']."'");
			// can know who is visiting here.
		}
		$user_field['user_sig'] = isset($userdata['user_sig']) ? TRUE : FALSE;
		$user_field['user_web'] = isset($userdata['user_web']) ? TRUE : FALSE;
	} else {
		$result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS." WHERE field_name='user_sig' OR field_name='user_web'");
		while ($data = dbarray($result)) {
			$user_field[$data['field_name']] = TRUE;
		}
	}

	$info['tracked_threads'] = dbcount("(thread_id)", DB_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'") ? TRUE : FALSE;

	// Forum Permissions
	$info['permissions']['can_post'] = $info['forum_post'] && checkgroup($info['forum_post']) ? 1 : 0;
	$info['permissions']['can_reply'] = $info['forum_reply'] && checkgroup($info['forum_reply']) ? 1 : 0;
	$info['permissions']['edit_lock'] = $settings['forum_edit_lock'] ? 1 : 0;
	$info['permissions']['can_vote'] = ($info['forum_type'] == 4 && $info['forum_allow_post_ratings']) ? 1 : 0;

	// Buttons
	$info['forum_cat_link'] = FORUM."index.php?viewforum&amp;forum_id=".$info['forum_id']."&amp;forum_cat=".$info['forum_cat']."&amp;forum_branch=".$info['forum_branch'];
	if (iMEMBER && $settings['thread_notify']) {
		if (dbcount("(thread_id)", DB_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'")) {
			$result2 = dbquery("UPDATE ".DB_THREAD_NOTIFY." SET notify_datestamp='".time()."', notify_status='1' WHERE thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'");
			$info['notify'] = array('link'=>FORUM."postify.php?post=off&amp;forum_id=".$info['forum_id']."&amp;thread_id=".$_GET['thread_id'], 'name'=>$locale['forum_0174']);
		} else {
			$info['notify'] = array('link'=>FORUM."postify.php?post=on&amp;forum_id=".$info['forum_id']."&amp;thread_id=".$_GET['thread_id'], 'name'=>$locale['forum_0175']);
		}
	}
	$info['print'] = array('link'=>BASEDIR."print.php?type=F&amp;thread=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart'], 'name'=>$locale['forum_0178']);
	if (iMEMBER) {
		if (checkgroup($info['permissions']['can_post']) or checkgroup($info['permissions']['can_reply'])) {
			if (checkgroup($info['permissions']['can_post'])) {
				$info['newthread'] = array('link'=>FORUM."post.php?action=newthread&amp;forum_id=".$info['forum_id'], 'name'=>$locale['forum_0264']);
			}
			if (checkgroup($info['permissions']['can_reply']) && !$info['thread_locked']) {
				$info['reply'] = array('link'=>FORUM."post.php?action=reply&amp;forum_id=".$info['forum_id']."&amp;thread_id=".$_GET['thread_id'], 'name'=>$locale['forum_0360']);
			}
		}
	}

	// Filters
	$info['allowed-post-filters'] = array('oldest', 'latest');
	$info['post-filters'][0] = array('value' => FORUM.'viewthread.php?thread_id='.$_GET['thread_id'].'&amp;section=oldest',
		'locale' => 'Oldest');
	$info['post-filters'][1] = array('value' => FORUM.'viewthread.php?thread_id='.$_GET['thread_id'].'&amp;section=latest',
		'locale' => 'Latest');

	if ($info['permissions']['can_vote']) {
		$info['allowed-post-filters'][2] = 'high';
		$info['post-filters'][2] = array('value' => FORUM.'viewthread.php?thread_id='.$_GET['thread_id'].'&amp;section=high',
			'locale' => 'Highest Ratings');
	}

	// Filters to SQL
	$sortCol = 'post_datestamp ASC';
	if (isset($_GET['section'])) {
		if ($_GET['section'] == 'oldest') {
			$sortCol = 'post_datestamp ASC';
		} elseif ($_GET['section'] == 'latest') {
			$sortCol = 'post_datestamp DESC';
		} elseif ($_GET['section'] == 'high') {
			$sortCol = 'vote_points DESC';
		}
	}

	// Main Postings SQL - left join items might have more than one. so we're going to thread very tip for filter
	$result = dbquery("SELECT p.forum_id, p.thread_id, p.post_id, p.post_message, p.post_showsig, p.post_smileys, p.post_author,
		p.post_datestamp, p.post_ip, p.post_ip_type, p.post_edituser, p.post_edittime, p.post_editreason,
		t.thread_id, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_posts, u.user_groups, u.user_joined, u.user_lastvisit,
		".($user_field['user_sig'] ? " u.user_sig," : "").($user_field['user_web'] ? " u.user_web," : "")."
		u2.user_name AS edit_name, u2.user_status AS edit_status,
		a.attach_name, SUM(v.vote_points) as vote_points
		FROM ".DB_POSTS." p
		INNER JOIN ".DB_THREADS." t ON t.thread_id = p.thread_id
		LEFT JOIN ".DB_FORUM_VOTES." v ON v.post_id = p.post_id
		LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
		LEFT JOIN ".DB_USERS." u2 ON p.post_edituser = u2.user_id AND post_edituser > '0'
		LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id = t.thread_id
		WHERE p.thread_id='".$_GET['thread_id']."' AND post_hidden='0' ".($info['forum_type'] == '4' ? "OR p.post_id='".$first_post."'" : '')."
		GROUP by p.post_id ORDER BY $sortCol LIMIT ".$_GET['rowstart'].", ".$info['posts_per_page']);
	$info['post_item_rows'] = dbrows($result);
	$i = 1;

	while ($data = dbarray($result)) {
		$data['first_post'] = $data['post_id'] == $first_post ? 1 : 0;
		$data['last_post'] = $data['post_id'] == $last_post ? 1 : 0;
		if ($data['post_smileys']) {
			$data['post_message'] = parsesmileys($data['post_message']);
		}
		$data['post_message'] = nl2br(parseubb($data['post_message']));
		$data['post_message'] = (isset($_GET['highlight'])) ? "<div class='search_result'>".$data['post_message']."</div>\n" : $data['post_message'];
		/* User Information */
		// rank img
		if ($data['user_level'] >= 102) {
			$data['rank_img'] =  $settings['forum_ranks'] ? show_forum_rank($data['user_posts'], $data['user_level'], $data['user_groups']) : getuserlevel($data['user_level']);
		} else {
			$is_mod = FALSE;
			foreach ($info['mod_groups'] as $mod_group) {
				if (!$is_mod && preg_match("(^\.{$mod_group}$|\.{$mod_group}\.|\.{$mod_group}$)", $data['user_groups'])) {
					$is_mod = TRUE;
				}
			}
			if ($settings['forum_ranks']) {
				$data['rank_img'] =  $is_mod ? show_forum_rank($data['user_posts'], 104, $data['user_groups']) : show_forum_rank($data['user_posts'], $data['user_level'], $data['user_groups']);
			} else {
				$data['rank_img'] =  $is_mod ? $locale['userf1'] : getuserlevel($data['user_level']);
			}
		}
		// Website
		if (isset($data['user_web']) && $data['user_web'] && (iADMIN || $data['user_status'] != 6 && $data['user_status'] != 5)) {
			$data['user_web'] = array('link'=>$data['user_web'], 'name'=>$locale['forum_0364']);
		}
		// Message
		if (iMEMBER && $data['user_id'] != $userdata['user_id'] && (iADMIN || $data['user_status'] != 6 && $data['user_status'] != 5)) {
			$data['user_message'] = array('link'=>BASEDIR.'messages.php?msg_send='.$data['user_id'], 'name'=>$locale['send_message']);
		}
		// IP
		if (($settings['forum_ips'] && iMEMBER) || iMOD) {
			$data['user_ip'] = $locale['forum_0268'].' '.$data['post_ip'];
		}
		// User Sig
		if ($data['post_showsig'] && isset($data['user_sig']) && $data['user_sig'] && $data['user_status'] != 6 && $data['user_status'] != 5) {
			$data['user_sig'] = nl2br(parseubb(parsesmileys($data['user_sig']), "b|i|u||center|small|url|mail|img|color"));
		}
		// Quote & Edit
		if (iMEMBER && ($info['permissions']['can_post'] || $info['permissions']['can_reply'])) {
			if (!$info['thread_locked']) {
				$data['post_quote'] = array('link'=>FORUM."post.php?action=reply&amp;forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id']."&amp;quote=".$data['post_id'], 'name'=>$locale['forum_0266']);
				if (iMOD || (($info['permissions']['edit_lock'] && $info['post_lastpost'] == $data['post_id'] || !$info['permissions']['edit_lock'])) && ($userdata['user_id'] == $data['post_author']) && ($settings['forum_edit_timelimit'] <= 0 || time()-$settings['forum_edit_timelimit']*60 < $data['post_datestamp'])) {
					$data['post_edit'] =  array('link'=>FORUM."post.php?action=edit&amp;forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id'], 'name'=>$locale['forum_0265']);
				}
			} elseif (iMOD) {
				$data['post_edit'] = array('link'=>FORUM."post.php?action=edit&amp;forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id'], 'name'=>$locale['forum_0265']);
			}
		}
		// Voting - need up or down link - accessible to author also the vote

		$data['vote_time'] = 0;
		if ($info['permissions']['can_vote']) { // can vote.
			$data['vote_time'] = 1; // pass forum settings
			$data['vote_up'] = '';
			$data['vote_down'] = '';
			if (checkgroup($info['forum_vote'])) { // everyone can vote as long pass checkgroup.
				// check for own vote link.
				if ($data['user_id'] !== $userdata['user_id']) {
					$data['vote_up'] = array('link'=>FORUM."post.php?action=voteup&amp;forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id'], 'name'=>$locale['forum_0265']);
					$data['vote_down'] = array('link'=>FORUM."post.php?action=votedown&amp;forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id'], 'name'=>$locale['forum_0265']);
				}
				$data['vote_points'] = !empty($data['vote_points']) ? $data['vote_points'] : 0;
				//print_p($data['vote_points']);
			} else {
				$data['vote_points'] = !empty($data['vote_points']) ? $data['vote_points'] : 0;
			}
		}
		// Marker
		$data['marker'] = array('link'=>"#post_".$data['post_id'], 'name'=>"#".($i+$_GET['rowstart']), 'id'=>"post_".$data['post_id']);

		// Print
		$data['print'] =  array('link'=>BASEDIR."print.php?type=F&amp;thread=".$_GET['thread_id']."&amp;post=".$data['post_id']."&amp;nr=".($i+$_GET['rowstart']), 'name'=>$locale['forum_0179']);
		// Edit Reason - NOT WORKING?
		$data['edit_reason'] = '';
		if ($data['post_edittime']) {
			$edit_time = "<span class='text-smaller'>".$locale['forum_0164'].profile_link($data['post_edituser'], $data['edit_name'], $data['edit_status']).$locale['forum_0167'].showdate("forumdate", $data['post_edittime'])."</span>\n";
			if ($data['post_editreason'] && iMEMBER) {
				$edit_reason = TRUE;
				$edit_time .= "<br /><div class='edit_reason'><a id='reason_pid_".$data['post_id']."' rel='".$data['post_id']."' class='reason_button small' href='#reason_div_pid_".$data['post_id']."'>";
				$edit_time .= "<strong>".$locale['forum_0165']."</strong>";
				$edit_time .= "</a>\n";
				$edit_time .= "<div id='reason_div_pid_".$data['post_id']."' class='reason_div small'>".$data['post_editreason']."</div></div>\n";
			}
			$data['edit_reason'] = $edit_time;
		}
		// Attachments
		// attachments - $image and $files
		$data['attach-files-count'] = 0;
		$data['attach-image-count'] = 0;
		if (isset($info['attachments'][$data['post_id']])) {
			require_once INCLUDES."mimetypes_include.php";
			$i_files = 1;	$i_image = 1;
			$data['attach-image'] = '';
			$data['attach-files'] = '';
			foreach($info['attachments'][$data['post_id']] as $attach) {
				if (in_array($attach['attach_mime'], img_mimeTypes())) {
					$data['attach-image'] .= display_image_attach($attach['attach_name'], "100", "100", $post_data['post_id'])."\n";
					$i_image++;
				} else {
					$data['attach-files'] .= "<div class='display-inline-block'><i class='entypo attach'></i><a href='".FUSION_SELF."?thread_id=".$_GET['thread_id']."&amp;getfile=".$attach['attach_id']."'>".$attach['attach_name']."</a>&nbsp;";
					$data['attach-files'] .= "[<span class='small'>".parsebytesize(filesize(FORUM."attachments/".$attach['attach_name']))." / ".$attach['attach_count'].$locale['forum_0162']."</span>]</div>\n";
					$i_files++;
				}
			}
			$data['attach-files-count'] = $i_files;
			$data['attach-image-count'] = $i_image;
			// $attach;
			if (!empty($data['attach-image'])) {
				if (!defined('COLORBOX')) {
					define('COLORBOX', true);
					add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
					add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
					add_to_jquery("$('a[rel^=\"attach\"]').colorbox({ current: '".$locale['forum_0159']." {current} ".$locale['forum_0160']." {total}',width:'80%',height:'80%'});");
				}
			}
		}

		$info['post_items'][$data['post_id']] = $data;
		$i++;
	}
} else {
	redirect("index.php");
}

// Set breadcrumbs, Meta & Title
$forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
function forum_breadcrumbs() {
	global $aidlink, $forum_index;
	/* Make an infinity traverse */
	function breadcrumb_arrays($index, $id) {
		global $aidlink;
		$crumb = & $crumb;
		//$crumb += $crumb;
		if (isset($index[get_parent($index, $id)])) {
			$_name = dbarray(dbquery("SELECT forum_id, forum_name, forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id='".$id."'"));
			$crumb = array('link' => FORUM."index.php?viewforum&amp;forum_id=".$_name['forum_id']."&amp;parent_id=".$_name['forum_cat'],
				'title' => $_name['forum_name']);
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
	$crumb = breadcrumb_arrays($forum_index, $_GET['forum_id']);
	// then we sort in reverse.
	if (count($crumb['title']) > 1) {
		krsort($crumb['title']);
		krsort($crumb['link']);
	}
	// then we loop it out using Dan's breadcrumb.
	add_to_breadcrumbs(array('link' => FORUM.'index.php', 'title' => 'Forum Board Index'));
	if (count($crumb['title']) > 1) {
		foreach ($crumb['title'] as $i => $value) {
			add_to_breadcrumbs(array('link' => $crumb['link'][$i], 'title' => $value));
		}
	} elseif (isset($crumb['title'])) {
		add_to_breadcrumbs(array('link' => $crumb['link'], 'title' => $crumb['title']));
	}
	// hola!
}
$_GET['forum_id'] = $info['forum_id'];
forum_breadcrumbs();
add_to_title($locale['global_201'].$info['thread_subject']);
add_to_breadcrumbs(array('link' => FORUM.'viewthread.php?thread_id='.$_GET['thread_id'],
					   'title' => $info['thread_subject']));

/* Get Post data ? */
if (isset($_GET['pid']) && isnum($_GET['pid'])) {
	$reply_count = dbcount("(post_id)", DB_POSTS, "thread_id='".$info['thread_id']."' AND post_id<='".$_GET['pid']."' AND post_hidden='0'");
	$info['reply_rows'] = $reply_count;
	if ($reply_count > $info['posts_per_page']) {
		$_GET['rowstart'] = ((ceil($reply_count/$info['posts_per_page'])-1)*$posts_per_page);
	}
}

//locale dependent forum buttons
if (is_array($fusion_images)) {
	if ($settings['locale'] != "English") {
		$newpath = "";
		$oldpath = explode("/", $fusion_images['newthread']);
		$c_path = count($oldpath);
		for ($i = 0; $i < $c_path-1; $i++) {
			$newpath .= $oldpath[$i]."/";
		}
		if (is_dir($newpath.$settings['locale'])) {
			redirect_img_dir($newpath, $newpath.$settings['locale']."/");
		}
	}
}


//javascript to footer
$highlight_js = "";
$colorbox_js = "";
$edit_reason_js = "";


/** javascript **/
// highlight jQuery plugin
if (isset($_GET['highlight'])) {
	$words = explode(" ", urldecode($_GET['highlight']));
	$higlight = "";
	$i = 1;
	$c_words = count($words);
	foreach ($words as $hlight) {
		$hlight = htmlentities($hlight, ENT_QUOTES);
		$higlight .= "'".$hlight."'";
		$higlight .= ($i < $c_words ? "," : "");
		$i++;
	}
	add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
	$highlight_js .= "jQuery('.search_result').highlight([".$higlight."],{wordsOnly:true});";
	$highlight_js .= "jQuery('.highlight').css({backgroundColor:'#FFFF88'});"; //better via theme or settings
}

if ($edit_reason) {
	$edit_reason_js .= "jQuery('div[id^=\"reason_div_pid\"]').hide();";
	$edit_reason_js .= "jQuery('div').find('a[id^=\"reason_pid\"]').css({cursor:'pointer'})";
	$edit_reason_js .= ".removeAttr('href')";
	$edit_reason_js .= ".attr('title','".str_replace("'", "&#39;", $locale['forum_0166'])."')";
	$edit_reason_js .= ".bind('click',function(){";
	$edit_reason_js .= "jQuery('#reason_div_pid_'+this.rel).stop().slideToggle('fast');";
	$edit_reason_js .= "});";
}

//if ($rows > $posts_per_page) {
//echo "<div class='clearfix'>\n<div id='pagenav-bottom' class='pull-right display-inline-block m-r-10'>\n";
//echo makepagenav($_GET['rowstart'], $posts_per_page, $rows, 3, FORUM."viewthread.php?thread_id=".$_GET['thread_id'].(isset($_GET['highlight']) ? "&amp;highlight=".urlencode($_GET['highlight']) : "")."&amp;")."\n";
//echo "</div>\n</div>\n";
//}

// viewthread javascript, moved to footer
$viewthread_js = "<script type='text/javascript'>";
$viewthread_js .= "/*<![CDATA[*/";
$viewthread_js .= "jQuery(document).ready(function(){";
if (!empty($highlight_js) || !empty($colorbox_js) || !empty($edit_reason_js)) {
	$viewthread_js .= $highlight_js.$colorbox_js.$edit_reason_js;
}
$viewthread_js .= "jQuery('a[href=#top]').click(function(){";
$viewthread_js .= "jQuery('html, body').animate({scrollTop:0}, 'slow');";
$viewthread_js .= "return false;";
$viewthread_js .= "});";
$viewthread_js .= "});";
// below functions could be made more unobtrusive thanks to jQuery, giving a more accessible cms
$viewthread_js .= "function jumpforum(forum_id){";
$viewthread_js .= "document.location.href='".FORUM."viewforum.php?forum_id='+forum_id;";
$viewthread_js .= "}";
if (iMOD) { // only moderators need this javascript
	$viewthread_js .= "function setChecked(frmName,chkName,val){";
	$viewthread_js .= "dml=document.forms[frmName];";
	$viewthread_js .= "len=dml.elements.length;";
	$viewthread_js .= "for(i=0;i<len;i++){";
	$viewthread_js .= "if(dml.elements[i].name==chkName){";
	$viewthread_js .= "dml.elements[i].checked=val;";
	$viewthread_js .= "}";
	$viewthread_js .= "}";
	$viewthread_js .= "}";
}
$viewthread_js .= "/*]]>*/";
$viewthread_js .= "</script>";
add_to_footer($viewthread_js); //unset($viewthread_js);

render_post($info);

require_once THEMES."templates/footer.php";
?>
