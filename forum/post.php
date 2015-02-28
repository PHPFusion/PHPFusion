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
require_once "../maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum/post.php";

add_to_title($locale['global_204']);

require_once INCLUDES."forum_include.php";
require_once INCLUDES."bbcode_include.php";

if (!iMEMBER || !isset($_GET['forum_id']) || !isnum($_GET['forum_id'])) { redirect("index.php"); }

if ($settings['forum_edit_lock'] == 1) {
	$lock_edit = true;
} else {
	$lock_edit = false;
}

$result = dbquery(
	"SELECT f.*, f2.forum_name AS forum_cat_name
	FROM ".DB_FORUMS." f
	LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
	WHERE f.forum_id='".$_GET['forum_id']."' LIMIT 1"
);

if (dbrows($result)) {
	$fdata = dbarray($result);
	if (!checkgroup($fdata['forum_access']) || !$fdata['forum_cat']) { redirect("index.php"); }
} else {
	redirect("index.php");
}

if (iSUPERADMIN) { define("iMOD", true); }

if (!defined("iMOD") && iMEMBER && $fdata['forum_moderators']) {
	$mod_groups = explode(".", $fdata['forum_moderators']);
	foreach ($mod_groups as $mod_group) {
		if (!defined("iMOD") && checkgroup($mod_group)) { define("iMOD", true); }
	}
}

if (!defined("iMOD")) { define("iMOD", false); }

$caption = $fdata['forum_cat_name']." &raquo; <a href='".FORUM."viewforum.php?forum_id=".$fdata['forum_id']."'>".$fdata['forum_name']."</a>";

if ((isset($_GET['action']) && $_GET['action'] == "newthread") && ($fdata['forum_post'] != 0 && checkgroup($fdata['forum_post']))) {
	include "postnewthread.php";
} elseif ((isset($_GET['action']) && $_GET['action'] == "reply") && ($fdata['forum_reply'] != 0 && checkgroup($fdata['forum_reply']))) {
	if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
		redirect("index.php");
	}

	$result = dbquery("SELECT * FROM ".DB_THREADS." WHERE thread_id='".$_GET['thread_id']."' AND forum_id='".$fdata['forum_id']."' AND thread_hidden='0'");

	if (dbrows($result)) {
		$tdata = dbarray($result);
	} else {
		redirect("index.php");
	}

	$caption .= " &raquo; ".$tdata['thread_subject'];

	if (!$tdata['thread_locked']) {
		include "postreply.php";
	} else {
		redirect("index.php");
	}
} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['thread_id']) && isnum($_GET['thread_id']) && isset($_GET['post_id']) && isnum($_GET['post_id'])) {
	$result = dbquery("SELECT * FROM ".DB_THREADS." WHERE thread_id='".$_GET['thread_id']."' AND forum_id='".$fdata['forum_id']."' AND thread_hidden='0'");

	if (dbrows($result)) {
		$tdata = dbarray($result);
	} else {
		redirect("index.php");
	}

	$result = dbquery("SELECT tp.*, tt.thread_subject, MIN(tp2.post_id) AS first_post FROM ".DB_POSTS." tp
	INNER JOIN ".DB_THREADS." tt on tp.thread_id=tt.thread_id
	INNER JOIN ".DB_POSTS." tp2 on tp.thread_id=tp2.thread_id
	WHERE tp.post_id='".$_GET['post_id']."' AND tp.thread_id='".$tdata['thread_id']."' AND tp.forum_id='".$fdata['forum_id']."' GROUP BY tp2.post_id");

	if (dbrows($result)) {
		$pdata = dbarray($result);
		$last_post = dbarray(dbquery("SELECT post_id FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' AND forum_id='".$_GET['forum_id']."' AND post_hidden='0' ORDER BY post_datestamp DESC LIMIT 1"));
	} else {
		redirect("index.php");
	}

	if ($userdata['user_id'] != $pdata['post_author'] && !iMOD && !iSUPERADMIN) { redirect("index.php"); }

	if ($pdata['post_locked'] && !iMOD) { redirect("postify.php?post=edit&error=5&forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."&post_id=".$_GET['post_id']); }
	if (!iMOD && ($settings['forum_edit_timelimit'] > 0 && time() - $settings['forum_edit_timelimit']*60 > $pdata['post_datestamp'])) { redirect("postify.php?post=edit&error=6&forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."&post_id=".$_GET['post_id']); }
	if (!$tdata['thread_locked'] && (($lock_edit && $last_post['post_id'] == $pdata['post_id'] && $userdata['user_id'] == $pdata['post_author']) || (!$lock_edit && $userdata['user_id'] == $pdata['post_author'])) ) {
		include "postedit.php";
	} elseif (iMOD) {
		include "postedit.php";
	} else {
		redirect("index.php");
	}
} else {
	redirect("index.php");
}

require_once THEMES."templates/footer.php";
?>