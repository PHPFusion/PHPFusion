<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_include.php
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

// Upload acceptable types for Forum
if (isset($_GET['getfile']) && isnum($_GET['getfile'])) {
	$result = dbquery("SELECT attach_id, attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE attach_id='".$_GET['getfile']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (file_exists(FORUM."attachments/".$data['attach_name'])) {
			$attach_count = dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET attach_count=attach_count+1 WHERE attach_id='".$data['attach_id']."'");
			require_once INCLUDES."class.httpdownload.php";
			ob_end_clean();
			$object = new httpdownload;
			$object->set_byfile(FORUM."attachments/".$data['attach_name']);
			$object->use_resume = TRUE;
			$object->download();
		} else {
			redirect("index.php");
		}
	}
	exit;
}

function attach_exists($file) {
	return \PHPFusion\Forums\Functions::attach_exists($file);
}

function forum_rank_cache() {
	return \PHPFusion\Forums\Functions::forum_rank_cache();
}

function show_forum_rank($posts, $level, $groups) {
	return PHPFusion\Forums\Functions::show_forum_rank($posts, $level, $groups);
}

function display_image($file) {
	PHPFusion\Forums\Functions::display_image($file);
}

function display_image_attach($file, $width = 50, $height = 50, $rel = "") {
	return PHPFusion\Forums\Functions::display_image_attach($file, $width, $height, $rel);
}

function define_forum_mods($info) {
	PHPFusion\Forums\Functions::define_forum_mods($info);
}

function verify_forum($forum_id) {
	return PHPFusion\Forums\Functions::verify_forum($forum_id);
}

function verify_post($post_id) {
	return PHPFusion\Forums\Functions::verify_post($post_id);
}

function verify_thread($thread_id) {
	return PHPFusion\Forums\Functions::verify_thread($thread_id);
}

function get_thread($thread_id) {
	return \PHPFusion\Forums\Functions::get_thread($thread_id);
}

/**
 * Cast Question Votes
 * @param     $info
 * @param int $points
 */
function set_forumVotes($info, $points = 0) {
	global $userdata;
	// @todo: extend on user's rank threshold before can vote. - Reputation threshold- Roadmap 9.1
	// @todo: allow multiple votes / drop $res - Roadmap 9.1
	if (checkgroup($info['forum_vote']) && dbcount("('thread_id')", DB_FORUM_THREADS, "thread_locked='0'")) {
		$data = array(
			'forum_id' => $_GET['forum_id'],
			'thread_id' => $_GET['thread_id'],
			'post_id'	=> $_GET['post_id'],
			'vote_points' => $points,
			'vote_user' => $userdata['user_id'],
			'vote_datestamp' => time(),
		);
		$hasVoted = dbcount("('vote_user')", DB_FORUM_VOTES, "vote_user='".intval($userdata['user_id'])."' AND thread_id='".intval($_GET['thread_id'])."'");
		if (!$hasVoted) {
			$isSelfPost = dbcount("('post_id')", DB_FORUM_POSTS, "post_id='".intval($_GET['post_id'])."' AND post_user='".intval($userdata['user_id'])."");
			if (!$isSelfPost) {
				$result = dbquery_insert(DB_FORUM_VOTES, $data, 'save', array('noredirect'=>1, 'no_unique'=>1));
				if ($result && $info['forum_answer_threshold'] >0) {
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
}

function parse_forumMods($forum_mods) {
	return PHPFusion\Forums\Functions::parse_forumMods($forum_mods);
}

function get_recentTopics($forum_id = 0) {
	return PHPFusion\Forums\Functions::get_recentTopics($forum_id);
}

function set_forumIcons(array $icons = array()) {
	PHPFusion\Forums\Functions::set_forumIcons($icons);
}

function get_forum($forum_id = 0, $forum_branch = 0) {
	return PHPFusion\Forums\Functions::get_forum($forum_id = 0, $forum_branch = 0);
}

function get_forumIcons($type = '') {
	return \PHPFusion\Forums\Functions::get_ForumIcons($type);
}

/**
 * Forum Breadcrumbs Generator
 * @param $forum_index
 */
function forum_breadcrumbs($forum_index) {
	global $locale;
	/* Make an infinity traverse */
	function breadcrumb_arrays($index, $id) {
		$crumb = &$crumb;
		if (isset($index[get_parent($index, $id)])) {
			$_name = dbarray(dbquery("SELECT forum_id, forum_name, forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id='".$id."'"));
			$crumb = array('link'=>FORUM."index.php?viewforum&amp;forum_id=".$_name['forum_id']."&amp;parent_id=".$_name['forum_cat'], 'title'=>$_name['forum_name']);
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
	if (count($crumb['title']) > 1)  { krsort($crumb['title']); krsort($crumb['link']); }
	if (count($crumb['title']) > 1) {
		foreach($crumb['title'] as $i => $value) {
			add_to_breadcrumbs(array('link'=>$crumb['link'][$i], 'title'=>$value));
			if ($i == count($crumb['title'])-1) {
				add_to_title($locale['global_201'].$value);
			}
		}
	} elseif (isset($crumb['title'])) {
		add_to_title($locale['global_201'].$crumb['title']);
		add_to_breadcrumbs(array('link'=>$crumb['link'], 'title'=>$crumb['title']));
	}
}
?>