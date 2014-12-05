<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_functions_include.php
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
include LOCALE.LOCALESET."admin/forums.php";

if (!defined("IN_FUSION")) { die("Access Denied"); }

// delete all attachment in forum
function prune_attachment($forum_id, $time=false) {
	global $locale;
	// delete attachments.
	$result = dbquery("SELECT post_id, post_datestamp FROM ".DB_POSTS." WHERE forum_id='".$forum_id."' ".($time ? "AND post_datestamp < '".$time."'" : '')."");
	$delattach = 0;
	if (dbrows($result)>0) {
		while ($data = dbarray($result)) {
			// delete all attachments
			$result2 = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'");
			if (dbrows($result2) != 0) {
				$delattach++;
				$attach = dbarray($result2);
				@unlink(FORUM."attachments/".$attach['attach_name']);
				$result3 = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'");
			}
		}
	}
	return $locale['610'].$delattach;
}

// delete all posts in forum
function prune_posts($forum_id, $time=false) {
	global $locale;
	// delete posts.
	$result = dbquery("DELETE FROM ".DB_POSTS." WHERE forum_id='".$forum_id."' ".($time ? "AND post_datestamp < '".$time."'" : '')."");
	return $locale['609'].mysql_affected_rows();
}

function prune_threads($forum_id, $time=false) {
	// delete follows on threads
	$result = dbquery("SELECT thread_id, thread_lastpost FROM ".DB_THREADS." WHERE forum_id='".$forum_id."' ".($time ? "AND thread_lastpost < '".$time."'" : '')." ");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			$result2 = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$data['thread_id']."'");
		}
	}
	// delete threads
	$result = dbquery("DELETE FROM ".DB_THREADS." WHERE forum_id='$forum_id' ".($time ? "AND thread_lastpost < '".$time."'" : '')." ");
}

function prune_forums($branch_data = FALSE,  $index = FALSE, $time = FALSE) {
	// delete forums - wipeout branch, image, order updated.
	$index = $index ? $index : 0;
	//print_p($branch_data[$index]);
	//print_p("Index is $index");
	$index_data = dbarray(dbquery("SELECT forum_id, forum_image, forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."'"));
	// check if there is a sub for this node.
	if (isset($branch_data[$index])) {
		foreach($branch_data[$index] as $forum_id) {
			//print_p("Forum id is $forum_id");
			$data = dbarray(dbquery("SELECT forum_id, forum_image, forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$forum_id."'"));
			if ($data['forum_image'] && file_exists(IMAGES."forum/".$data['forum_image'])) {
				unlink(IMAGES."forum/".$data['forum_image']);
				//print_p("unlinked ".$data['forum_image']."");
			}
			dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$forum_id."' AND forum_order>'".$data['forum_order']."'");
			//print_p("deleted ".$forum_id."");
			dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='$forum_id' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");
			if (isset($branch_data[$data['forum_id']])) {
				prune_forums($branch_data, $data['forum_id'], $time);
			}
		// end foreach
		}
		// finally remove itself.
		if ($index_data['forum_image'] && file_exists(IMAGES."forum/".$index_data['forum_image'])) {
			unlink(IMAGES."forum/".$data['forum_image']);
			//print_p("unlinked ".$index_data['forum_image']."");
		}
		dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."' AND forum_order>'".$index_data['forum_order']."'");
		//print_p("deleted ".$index."");
		dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");
	} else {

		if ($index_data['forum_image'] && file_exists(IMAGES."forum/".$index_data['forum_image'])) {
			unlink(IMAGES."forum/".$index_data['forum_image']);
			//print_p("unlinked ".$index_data['forum_image']."");
		}
		dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."' AND forum_order>'".$index_data['forum_order']."'");
		//print_p("deleted ".$index."");
		dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");
	}
}

function recalculate_post($forum_id) {
	global $locale;
	// update last post
	$result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_THREADS." WHERE forum_id='".$forum_id."' ORDER BY thread_lastpost DESC LIMIT 0,1"); // get last thread_lastpost.
	if (dbrows($result)) {
		$data = dbarray($result);
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$data['thread_lastpost']."', forum_lastuser='".$data['thread_lastuser']."' WHERE forum_id='".$forum_id."'");
	} else {
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastuser='0' WHERE forum_id='".$forum_id."'");
	}
	// update postcount on each threads -  this is the remaining.
	$result = dbquery("SELECT COUNT(post_id) AS postcount, thread_id FROM ".DB_POSTS." WHERE forum_id='".$forum_id."' GROUP BY thread_id");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_THREADS." SET thread_postcount='".$data['postcount']."' WHERE thread_id='".$data['thread_id']."'");
		}
	}
	// calculate and update total combined postcount on all threads to forum
	$result = dbquery("SELECT SUM(thread_postcount) AS postcount, forum_id FROM ".DB_THREADS."
			WHERE forum_id='".$forum_id."' GROUP BY forum_id");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_FORUMS." SET forum_postcount='".$data['postcount']."' WHERE forum_id='".$data['forum_id']."'");
		}
	}
	// calculate and update total threads to forum
	$result = dbquery("SELECT COUNT(thread_id) AS threadcount, forum_id FROM ".DB_THREADS." WHERE forum_id='".$forum_id."' GROUP BY forum_id");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_FORUMS." SET forum_threadcount='".$data['threadcount']."' WHERE forum_id='".$data['forum_id']."'");
		}
	}
	return $locale['611'].mysql_affected_rows();
}

function prune_users_posts($forum_id) {
	// after clean up.
	$result = dbquery("SELECT post_user FROM ".DB_POSTS." WHERE forum_id='".$forum_id."'");
	$user_data = array();
	if (dbrows($result)>0) {
		while ($data = dbarray($result)) {
			$user_data[$data['post_user']] = isset($user_data[$data['post_user']]) ? $user_data[$data['post_user']]+1 : 1;
		}
	}
	if (!empty($user_data)) {
		foreach($user_data as $user_id => $count) {
			$result = dbquery("SELECT user_post FROM ".DB_USERS." WHERE user_id='".$user_id."'");
			if (dbrows($result)>0) {
				$_userdata = dbarray($result);
				$calculated_post = $_userdata['user_post']-$count;
				$calculated_post = $calculated_post > 1 ? $calculated_post : 0;
				dbquery("UPDATE ".DB_USERS." SET user_post='".$calculated_post."' WHERE user_id='".$user_id."'");
			}
		}
	}
}

?>