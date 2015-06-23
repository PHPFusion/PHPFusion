<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: viewthread.php
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
	require_once __DIR__.'/../../error.php';
	exit;
}

include INFUSIONS."forum/locale/".LOCALESET."forum.php";
require_once THEMES."templates/header.php";
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."forum/classes/Viewthread.php";
require_once INFUSIONS."forum/classes/Functions.php";
require_once INFUSIONS."forum/classes/Moderator.php";
require_once INFUSIONS."forum/forum_include.php";

// Load Template
include INFUSIONS."forum/templates/forum_main.php";
include INFUSIONS."forum/templates/forum_thread.php";
include INFUSIONS."forum/templates/forum_input.php";

add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
$forum_settings = get_settings('forum');
$thread = new PHPFusion\Forums\Viewthread();

echo renderNotices(getNotices());
// how to add meta?
add_to_meta($locale['forum_0000']);
if (isset($_GET['action'])) {
	switch($_GET['action']) {
		case 'editpoll':
			$thread->render_poll_form(true);
			break;
		case 'deletepoll':
			$thread->delete_poll();
			break;
		case 'newpoll':
			$thread->render_poll_form();
			break;
		case 'edit':
			$thread->render_edit_form();
			break;
		case 'reply':
			$thread->render_reply_form();
			break;
		default:
			redirect(clean_request('', array('action'), false));
	}
} else {
	$info = $thread->get_thread_data();
	render_thread($info);
}

/* Errors */
/* changed
if (isset($_GET['error'])) {
	if ($_GET['error'] == 'vote') {
		notify($locale['forum_0800'], $locale['forum_0801']);
	} elseif ($_GET['error'] == 'vote_self') {
		notify($locale['forum_0800'], $locale['forum_0802']);
	}
}

/* Jumps to last links -- there is another with pid in Line 264 */
/*
if (isset($_GET['pid']) && isnum($_GET['pid'])) {
	$result = dbquery("SELECT thread_id FROM ".DB_FORUM_POSTS." WHERE post_id='".$_GET['pid']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
	//	redirect("viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$_GET['pid']."#post_".$_GET['pid']);
	}
} */

//locale dependent forum buttons
/*
if ($settings['locale'] != "English") {
	$newpath = "";
	$oldpath = explode("/", get_image('newthread'));
	$c_path = count($oldpath);
	for ($i = 0; $i < $c_path-1; $i++) {
		$newpath .= $oldpath[$i]."/";
	}
	if (is_dir($newpath.$settings['locale'])) {
		redirect_img_dir($newpath, $newpath.$settings['locale']."/");
	}
}*/
require_once THEMES."templates/footer.php";
