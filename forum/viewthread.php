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
require_once "../maincore.php";
require_once INCLUDES."forum_include.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum/main.php";

$posts_per_page = 20;

add_to_title($locale['global_200'].$locale['400']);

if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) { redirect("index.php"); }

$result = dbquery(
	"SELECT t.*, f.*, f2.forum_name AS forum_cat_name
	FROM ".DB_THREADS." t
	LEFT JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
	LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
	WHERE t.thread_id='".$_GET['thread_id']."' AND t.thread_hidden='0'"
);
if (dbrows($result)) {
	$fdata = dbarray($result);
	if (!checkgroup($fdata['forum_access']) || !$fdata['forum_cat'] || $fdata['thread_hidden'] == "1") { redirect("index.php"); }
} else {
	redirect("index.php");
}

if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }

if (isset($_GET['pid']) && isnum($_GET['pid'])) {
	$reply_count = dbcount("(post_id)", DB_POSTS, "thread_id='".$fdata['thread_id']."' AND post_id<='".$_GET['pid']."' AND post_hidden='0'");
	if ($reply_count > $posts_per_page) { $_GET['rowstart'] = ((ceil($reply_count / $posts_per_page)-1) * $posts_per_page); }
}

if ($fdata['forum_post'] != 0 && checkgroup($fdata['forum_post'])) {
	$can_post = true;
} else {
	$can_post = false;
}

if ($fdata['forum_reply'] != 0 && checkgroup($fdata['forum_reply'])) {
	$can_reply = true;
} else {
	$can_reply = false;
}

if ($settings['forum_edit_lock'] == 1) {
	$lock_edit = true;
} else {
	$lock_edit = false;
}

//locale dependent forum buttons
if (is_array($fusion_images)) {
	if ($settings['locale'] != "English") {
		$newpath = "";
		$oldpath = explode("/", $fusion_images['newthread']);
		$c_path = count($oldpath);
		for ($i = 0; $i < $c_path - 1; $i++) {
			$newpath .= $oldpath[$i]."/";
		}
		if (is_dir($newpath.$settings['locale'])) {
			redirect_img_dir($newpath, $newpath.$settings['locale']."/");
		}
	}
}

//javascript to footer
$highlight_js  = ""; $colorbox_js  = ""; $edit_reason_js  = "";

// moderators
$mod_groups = explode(".", $fdata['forum_moderators']);

if (iSUPERADMIN) { define("iMOD", true); }

if (!defined("iMOD") && iMEMBER && $fdata['forum_moderators']) {
	foreach ($mod_groups as $mod_group) {
		if (!defined("iMOD") && checkgroup($mod_group)) { define("iMOD", true); }
	}
}

if (!defined("iMOD")) { define("iMOD", false); }

if (iMOD && (((isset($_POST['delete_posts']) || isset($_POST['move_posts'])) && isset($_POST['delete_post'])) || isset($_GET['error']))) {
	require_once FORUM."viewthread_options.php";
}

// polls
$poll_on_first_page_only = ($_GET['rowstart'] == 0) ? true : false;
$poll_there = false; $poll_data = false; $poll_options = 0;
$can_vote   = false; $had_voted = false;
if ($fdata['thread_poll'] == "1") { // bug #1012
	$poll_there = true; 
	if (iMEMBER) {
		$presult = dbquery(
			"SELECT tfp.forum_poll_title, tfp.forum_poll_votes, tfv.forum_vote_user_id FROM ".DB_FORUM_POLLS." tfp
			LEFT JOIN ".DB_FORUM_POLL_VOTERS." tfv
			ON tfp.thread_id=tfv.thread_id AND forum_vote_user_id='".$userdata['user_id']."'
			WHERE tfp.thread_id='".$_GET['thread_id']."'"
		);
	} else {
		$presult = dbquery(
			"SELECT tfp.forum_poll_title, tfp.forum_poll_votes FROM ".DB_FORUM_POLLS." tfp
			WHERE tfp.thread_id='".$_GET['thread_id']."'"
		);
	}
	if (dbrows($presult)) {
		$poll_data = true;
		$pdata = dbarray($presult); 
		if (isset($pdata['forum_vote_user_id'])) { $had_voted = true; }
		$presult2 = dbquery("SELECT forum_poll_option_votes, forum_poll_option_text FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY forum_poll_option_id ASC");
		$poll_options = dbrows($presult2);
	}
	$can_vote = (!$had_voted && $fdata['forum_vote'] != 0 && checkgroup($fdata['forum_vote'])) ? true : false;
}

if (iMOD) { $can_vote = !$had_voted; }

if ($poll_there && $can_vote && !$had_voted && isset($_POST['cast_vote']) && (isset($_POST['poll_option']) && isnum($_POST['poll_option'])) && ($_POST['poll_option'] <= $poll_options)) { // bug #1012
	$result = dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_votes=forum_poll_option_votes+1 WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$_POST['poll_option']."'");
	$result = dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_votes=forum_poll_votes+1 WHERE thread_id='".$_GET['thread_id']."'");
	$result = dbquery("INSERT INTO ".DB_FORUM_POLL_VOTERS." (thread_id, forum_vote_user_id, forum_vote_user_ip, forum_vote_user_ip_type) VALUES ('".$_GET['thread_id']."', '".$userdata['user_id']."', '".USER_IP."', '".USER_IP_TYPE."')");
	redirect(FUSION_SELF."?thread_id=".$_GET['thread_id']);
}

// user fields
$user_field = array("user_sig" => false, "user_web" => false);
if (iMEMBER) {
	$thread_match = $fdata['thread_id']."\|".$fdata['thread_lastpost']."\|".$fdata['forum_id'];
	if (($fdata['thread_lastpost'] > $lastvisited) && !preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads'])) {
		$result = dbquery("UPDATE ".DB_USERS." SET user_threads='".$userdata['user_threads'].".".stripslashes($thread_match)."' WHERE user_id='".$userdata['user_id']."'");
	}
	if (isset($userdata['user_sig'])) { $user_field['user_sig'] = true; }
	if (isset($userdata['user_web'])) { $user_field['user_web'] = true; }
} else {
	$result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS." WHERE field_name='user_sig' OR field_name='user_web'");
	while ($data = dbarray($result)) {
		$user_field[$data['field_name']] = true;
	}
}

opentable($locale['500']);

// breadcrumbs
$caption = $fdata['forum_cat_name']." &raquo; <a href='viewforum.php?forum_id=".$fdata['forum_id']."'>".$fdata['forum_name']."</a>";
echo "<!--pre_forum_thread--><div class='tbl2 forum_breadcrumbs' style='margin:0px 0px 4px 0px'><a href='index.php' id='top'>".$settings['sitename']."</a> &raquo; ".$caption."</div>\n";

// thread
list($rows, $last_post) = dbarraynum(dbquery(
	"SELECT COUNT(post_id), MAX(post_id) FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' AND post_hidden='0' GROUP BY thread_id"));

if (($rows > $posts_per_page) || ($can_post || $can_reply)) {
	echo "<table cellspacing='0' cellpadding='0' width='100%'>\n<tr>\n";
	if ($rows > $posts_per_page) { echo "<td style='padding:4px 0px 4px 0px'>".makepagenav($_GET['rowstart'],$posts_per_page,$rows,3,FUSION_SELF."?thread_id=".$_GET['thread_id']."&amp;")."</td>\n"; }
	if (iMEMBER && $can_post) {
		echo "<td align='right' style='padding:0px 0px 4px 0px'>\n<!--pre_forum_buttons-->\n";
		if ($can_post) {
			echo "<a href='post.php?action=newthread&amp;forum_id=".$fdata['forum_id']."'>";
			echo "<img src='".get_image("newthread")."' alt='".$locale['566']."' style='border:0px' /></a>\n";
		}
		if (!$fdata['thread_locked'] && $can_reply) {
			echo "<a href='post.php?action=reply&amp;forum_id=".$fdata['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>";
			echo "<img src='".get_image("reply")."' alt='".$locale['565']."' style='border:0px' /></a>\n";
		}
		echo "</td>\n";
	}
	echo "</tr>\n</table>\n";
}

if ($rows != 0) {
	dbquery("UPDATE ".DB_THREADS." SET thread_postcount='$rows', thread_lastpostid='$last_post', thread_views=thread_views+1 WHERE thread_id='".$_GET['thread_id']."'");

	/* poll */
	if ($poll_on_first_page_only && $poll_there && $poll_data) {
		$i = 1;
		if ($can_vote) { echo "<form name='voteform' method='post' action='".FUSION_SELF."?forum_id=".$fdata['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>\n"; }
		echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border' style='margin-bottom:5px'>\n<tr>\n";
		echo "<td align='center' class='tbl2'><strong>".$pdata['forum_poll_title']."</strong></td>\n</tr>\n<tr>\n<td class='tbl1'>\n";
		echo "<table align='center' cellpadding='0' cellspacing='0'>\n";
		while ($pvdata = dbarray($presult2)) {
			if (!$can_vote) {
				$option_votes = ($pdata['forum_poll_votes'] ? number_format(100 / $pdata['forum_poll_votes'] * $pvdata['forum_poll_option_votes']) : 0);
				echo "<tr>\n<td class='tbl1'>".$pvdata['forum_poll_option_text']."</td>\n";
				echo "<td class='tbl1'><img src='".get_image("pollbar")."' alt='".$pvdata['forum_poll_option_text']."' height='12' width='".(200 / 100 * $option_votes)."' class='poll' /></td>\n";
				echo "<td class='tbl1'>".$option_votes."%</td><td class='tbl1'>[".$pvdata['forum_poll_option_votes']." ".($pvdata['forum_poll_option_votes'] == 1 ? $locale['global_133'] : $locale['global_134'])."]</td>\n</tr>\n";
			} else {
				echo "<tr>\n<td class='tbl1'><label><input type='radio' name='poll_option' value='".$i."' style='vertical-align:middle' /> ".$pvdata['forum_poll_option_text']."</label></td>\n</tr>\n";
				$i++;
			}
		}
		if (!$can_vote) {
			echo "<tr>\n<td align='center' colspan='4' class='tbl1'>".$locale['480']." : ".$pdata['forum_poll_votes']."</td>\n</tr>\n";
		} else {
			echo "<tr>\n<td class='tbl1'><input type='submit' name='cast_vote' value='".$locale['481']."' class='button' /></td>\n</tr>\n";
		}
		echo "</table>\n</td>\n</tr>\n</table>\n";
		if ($can_vote) { echo "</form>\n"; }
	}
	/* end poll */

	$result = dbquery(
		"SELECT p.forum_id, p.thread_id, p.post_id, p.post_message, p.post_showsig, p.post_smileys, p.post_author,
		p.post_datestamp, p.post_ip, p.post_ip_type, p.post_edituser, p.post_edittime, p.post_editreason,
		u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_posts, u.user_groups, u.user_joined,
		".($user_field['user_sig'] ? " u.user_sig," : "").($user_field['user_web'] ? " u.user_web," : "")."
		u2.user_name AS edit_name, u2.user_status AS edit_status
		FROM ".DB_POSTS." p
		LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
		LEFT JOIN ".DB_USERS." u2 ON p.post_edituser = u2.user_id AND post_edituser > '0'
		WHERE p.thread_id='".$_GET['thread_id']."' AND post_hidden='0'
		ORDER BY post_datestamp LIMIT ".$_GET['rowstart'].",$posts_per_page"
	);
	if (iMOD) { echo "<form name='mod_form' method='post' action='".FUSION_SELF."?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>\n"; }
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border forum_thread_table'>\n";
	$numrows = dbrows($result); $current_row = 1; $colorbox_rel = array(); $edit_reason = false;
	while ($data = dbarray($result)) {
		$message = $data['post_message'];
		if ($data['post_smileys']) { $message = parsesmileys($message); }
		if ($current_row == 1) {
			echo "<tr>\n<td colspan='2' class='tbl2 forum-caption'>\n<div style='float:right' class='small'>";
			if (iMEMBER && $settings['thread_notify']) {
				if (dbcount("(thread_id)", DB_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'")) {
					$result2 = dbquery("UPDATE ".DB_THREAD_NOTIFY." SET notify_datestamp='".time()."', notify_status='1' WHERE thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'");
					echo "<a href='postify.php?post=off&amp;forum_id=".$fdata['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>".$locale['515']."</a>";
				} else {
					echo "<a href='postify.php?post=on&amp;forum_id=".$fdata['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>".$locale['516']."</a>";
				}
			}
			echo "&nbsp;<a href='".BASEDIR."print.php?type=F&amp;thread=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."'><img src='".get_image("printer")."' alt='".$locale['519']."' title='".$locale['519']."' style='border:0;vertical-align:middle' /></a></div>\n";
			add_to_title($locale['global_201'].$fdata['thread_subject']);
			echo "<div style='position:absolute' class='forum_thread_title'><!--forum_thread_title--><strong>".$fdata['thread_subject']."</strong></div>\n</td>\n</tr>\n";
		}
		echo "<!--forum_thread_prepost_".$current_row."-->\n";
		if ($current_row > 1) { echo "<tr>\n<td colspan='2' class='tbl1 forum_thread_post_space' style='height:10px'></td>\n</tr>\n"; }
		echo "<tr>\n<td class='tbl2 forum_thread_user_name' style='width:140px'><!--forum_thread_user_name-->".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
		echo "<td class='tbl2 forum_thread_post_date'>\n";
		echo "<div style='float:right' class='small'>";
		echo "<a href='#top'><img src='".get_image("up")."' alt='".$locale['541']."' title='".$locale['542']."' style='border:0;vertical-align:middle' /></a>\n";
		echo "&nbsp;<a href='#post_".$data['post_id']."' name='post_".$data['post_id']."' id='post_".$data['post_id']."'>#".($current_row+$_GET['rowstart'])."</a>";
		echo "&nbsp;<a href='".BASEDIR."print.php?type=F&amp;thread=".$_GET['thread_id']."&amp;post=".$data['post_id']."&amp;nr=".($current_row+$_GET['rowstart'])."'><img src='".get_image("printer")."' alt='".$locale['519a']."' title='".$locale['519a']."' style='border:0;vertical-align:middle' /></a></div>\n";
		echo "<div class='small'>".$locale['505'].showdate("forumdate", $data['post_datestamp'])."</div>\n";
		echo "</td>\n";
		echo "</tr>\n<tr>\n<td valign='top' class='tbl2 forum_thread_user_info' style='width:140px'>\n";
		if ($data['user_avatar'] && file_exists(IMAGES."avatars/".$data['user_avatar']) && $data['user_status']!=6 && $data['user_status']!=5) {
			echo "<img src='".IMAGES."avatars/".$data['user_avatar']."' alt='".$locale['567']."' /><br /><br />\n";
		} else {
			echo "<img src='".IMAGES."avatars/noavatar100.png' alt='".$locale['567']."' /><br /><br />\n";
		}
		echo "<span class='small'>";
		if ($data['user_level'] >= 102) {
			echo $settings['forum_ranks'] ? show_forum_rank($data['user_posts'], $data['user_level'], $data['user_groups']) : getuserlevel($data['user_level']);
		} else {
			$is_mod = false;
			foreach ($mod_groups as $mod_group) {
				if (!$is_mod && preg_match("(^\.{$mod_group}$|\.{$mod_group}\.|\.{$mod_group}$)", $data['user_groups'])) {
					$is_mod = true;
				}
			}
			if ($settings['forum_ranks']) {
				echo $is_mod ? show_forum_rank($data['user_posts'], 104, $data['user_groups']) : show_forum_rank($data['user_posts'], $data['user_level'], $data['user_groups']);
			} else {
				echo $is_mod ? $locale['userf1'] : getuserlevel($data['user_level']);
			}
		}
		echo "</span><br /><br />\n";
		echo "<!--forum_thread_user_info--><span class='small'><strong>".$locale['502']."</strong> ".$data['user_posts']."</span><br />\n";
		echo "<span class='small'><strong>".$locale['504']."</strong> ".showdate("shortdate", $data['user_joined'])."</span><br />\n";
		echo "<br /></td>\n<td valign='top' class='tbl1 forum_thread_user_post'>\n";
		if (iMOD) { echo "<div style='float:right'><input type='checkbox' name='delete_post[]' value='".$data['post_id']."' /></div>\n"; }
		$message = parseubb($message);
		$message = (isset($_GET['highlight'])) ? "<div class='search_result'>".$message."</div>\n" : $message;
		echo nl2br($message);
		echo "<!--sub_forum_post_message-->";
		$a_result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'");
		$a_files = ""; $a_images = ""; $i_files = 0; $i_images = 0;
		if(dbrows($a_result)){
			if (checkgroup($fdata['forum_attach_download'])) {
				while($a_data = dbarray($a_result)){
					if (!file_exists(FORUM."attachments/".$a_data['attach_name'])) { break; }
					if (in_array($a_data['attach_ext'], $imagetypes) && @getimagesize(FORUM."attachments/".$a_data['attach_name'])) {
						$a_images .= display_image_attach($a_data['attach_name'], "100", "100", $data['post_id'])."\n";
						$i_images++;
					} else {
						if($i_files > 0) $a_files .= "<br />\n";
						$a_files .= "<a href='".FUSION_SELF."?thread_id=".$_GET['thread_id']."&amp;getfile=".$a_data['attach_id']."'>".$a_data['attach_name']."</a>&nbsp;";
						$a_files .= "[<span class='small'>".parsebytesize(filesize(FORUM."attachments/".$a_data['attach_name']))." / ".$a_data['attach_count'].$locale['507a']."</span>]\n";
						$i_files++;
					}
				}
			} else {
				$a_files = $locale['507b'];
			}
			if ($a_files) {
				echo "<div class='emulated-fieldset'>\n";
				echo "<span class='emulated-legend'>".profile_link($data['user_id'], $data['user_name'], $data['user_status']).$locale['506'].($i_files > 1 ? $locale['506d'] : $locale['506c'])."</span>\n";
				echo "<div class='attachments-list'>".$a_files."</div>\n";
				echo "</div>\n";
			}
			if($a_images){
				$colorbox_rel[] = $data['post_id'];
				echo "<div class='emulated-fieldset'>\n";
				echo "<span class='emulated-legend'>".profile_link($data['user_id'], $data['user_name'], $data['user_status']).$locale['506'].($i_images > 1 ? $locale['506b'] : $locale['506a'])."</span>\n";
				echo "<div class='attachments-list'>".$a_images."</div>\n";
				echo "</div>\n";
			}
		}
		if ($data['post_edittime'] != "0") {
			echo "\n<hr />\n<span class='small'>".$locale['508'].profile_link($data['post_edituser'], $data['edit_name'], $data['edit_status']).$locale['509'].showdate("forumdate", $data['post_edittime'])."</span>\n";
			if ($data['post_editreason'] != "" && iMEMBER) {
				$edit_reason = true;
				echo "<br /><div class='edit_reason'><a id='reason_pid_".$data['post_id']."' rel='".$data['post_id']."' class='reason_button small' href='#reason_div_pid_".$data['post_id']."'>";
				echo "<strong>".$locale['508a']."</strong>";
				echo "</a>\n";
				echo "<div id='reason_div_pid_".$data['post_id']."' class='reason_div small'>".$data['post_editreason']."</div></div>\n";
			}
		}
		if ($data['post_showsig'] && isset($data['user_sig']) && $data['user_sig'] && $data['user_status']!=6 && $data['user_status']!=5) {
			echo "\n<hr /><div class='forum_sig'>".nl2br(parseubb(parsesmileys($data['user_sig']), "b|i|u||center|small|url|mail|img|color")) . "</div>\n";
		}
		echo "<!--sub_forum_post--></td>\n</tr>\n";
		echo "<tr>\n<td class='tbl2 forum_thread_ip' style='width:140px;white-space:nowrap'>";
		if (($settings['forum_ips'] && iMEMBER) || iMOD) { echo "<strong>".$locale['571']."</strong>: ".$data['post_ip']; } else { echo "&nbsp;"; }
		echo "</td>\n<td class='tbl2 forum_thread_userbar'>\n<div style='float:left;white-space:nowrap' class='small'><!--forum_thread_userbar-->\n";
		if (isset($data['user_web']) && $data['user_web'] && (iADMIN || $data['user_status']!=6 && $data['user_status']!=5)) {
			echo "<a href='".$data['user_web']."' target='_blank'><img src='".get_image("web")."' alt='".$data['user_web']."' style='border:0;vertical-align:middle' /></a>"; 
		}
		if (iMEMBER && $data['user_id']!=$userdata['user_id'] && (iADMIN || $data['user_status']!=6 && $data['user_status']!=5)) {
			echo "<a href='".BASEDIR."messages.php?msg_send=".$data['user_id']."'><img src='".get_image("pm")."' alt='".$locale['572']."' style='border:0;vertical-align:middle' /></a>\n";
		}
		echo "</div>\n<div style='float:right' class='small'>\n";
		if (iMEMBER && ($can_post || $can_reply)) {
			if (!$fdata['thread_locked']) {
				echo "<a href='post.php?action=reply&amp;forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id']."&amp;quote=".$data['post_id']."'><img src='".get_image("quote")."' alt='".$locale['569']."' style='border:0px;vertical-align:middle' /></a>\n";
				if (iMOD || (($lock_edit && $last_post['post_id'] == $data['post_id'] || !$lock_edit)) && ($userdata['user_id'] == $data['post_author']) && ($settings['forum_edit_timelimit'] <= 0 || time() - $settings['forum_edit_timelimit']*60 < $data['post_datestamp'])) {
					echo "<a href='post.php?action=edit&amp;forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id']."'><img src='".get_image("forum_edit")."' alt='".$locale['568']."' style='border:0px;vertical-align:middle' /></a>\n";
				}
			} elseif (iMOD) {
				echo "<a href='post.php?action=edit&amp;forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id']."'><img src='".get_image("forum_edit")."' alt='".$locale['568']."' style='border:0px;vertical-align:middle' /></a>\n";
			}
		}
		echo "</div>\n</td>\n</tr>\n";
		$current_row++;
	}
	/** javascript **/
	// highlight jQuery plugin
	if (isset($_GET['highlight'])) {
		$words = explode(" ", urldecode($_GET['highlight']));
		$higlight = ""; $i = 1; $c_words = count($words);
		foreach ($words as $hlight) {
			$hlight = htmlentities($hlight, ENT_QUOTES);
			$higlight .= "'".$hlight."'";
			$higlight .= ($i < $c_words ? "," : "");
			$i++;
		}
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
		$highlight_js .=   "jQuery('.search_result').highlight([".$higlight."],{wordsOnly:true});";
		$highlight_js .=   "jQuery('.highlight').css({backgroundColor:'#FFFF88'});"; //better via theme or settings
	}
	// colourbox jQuery plugin
	if (count($colorbox_rel) > 0) {
		add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
		$colorbox_js .=    "jQuery('a[rel^=\"attach\"]').colorbox({";
		$colorbox_js .=       "current:'".$locale['506e']." {current} ".$locale['506f']." {total}',width:'80%',height:'80%'";
		$colorbox_js .=    "});";
	} unset($colorbox_rel);
	// edit reason
	if ($edit_reason) {
		$edit_reason_js .=    "jQuery('div[id^=\"reason_div_pid\"]').hide();";
		$edit_reason_js .=    "jQuery('div').find('a[id^=\"reason_pid\"]').css({cursor:'pointer'})";
		$edit_reason_js .=    ".removeAttr('href')";
		$edit_reason_js .=    ".attr('title','".str_replace("'","&#39;",$locale['508b'])."')";
		$edit_reason_js .=    ".bind('click',function(){";
		$edit_reason_js .=       "jQuery('#reason_div_pid_'+this.rel).stop().slideToggle('fast');";
		$edit_reason_js .=    "});";
	}
}

echo "</table><!--sub_forum_thread_table-->\n";

if (iMOD) {
	echo "<table cellspacing='0' cellpadding='0' width='100%'>\n<tr>\n<td style='padding-top:5px'>";
	echo "<a href='#' onclick=\"javascript:setChecked('mod_form','delete_post[]',1);return false;\">".$locale['460']."</a> ::\n";
	echo "<a href='#' onclick=\"javascript:setChecked('mod_form','delete_post[]',0);return false;\">".$locale['461']."</a></td>\n";
	echo "<td align='right' style='padding-top:5px'><input type='submit' name='move_posts' value='".$locale['517a']."' class='button' onclick=\"return confirm('".$locale['518a']."');\" />\n<input type='submit' name='delete_posts' value='".$locale['517']."' class='button' onclick=\"return confirm('".$locale['518']."');\" /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
}

if ($rows > $posts_per_page) {
	echo "<div align='center' style='padding-top:5px'>\n";
	echo makepagenav($_GET['rowstart'],$posts_per_page,$rows,3,FUSION_SELF."?thread_id=".$_GET['thread_id'].(isset($_GET['highlight']) ? "&amp;highlight=".urlencode($_GET['highlight']):"")."&amp;")."\n";
	echo "</div>\n";
}

$forum_list = ""; $current_cat = "";
$result = dbquery(
	"SELECT f.forum_id, f.forum_name, f2.forum_name AS forum_cat_name
	FROM ".DB_FORUMS." f
	INNER JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
	WHERE ".groupaccess('f.forum_access')." AND f.forum_cat!='0'
	ORDER BY f2.forum_order ASC, f.forum_order ASC"
);
while ($data = dbarray($result)) {
	if ($data['forum_cat_name'] != $current_cat) {
		if ($current_cat != "") { $forum_list .= "</optgroup>\n"; }
		$current_cat = $data['forum_cat_name'];
		$forum_list .= "<optgroup label='".$data['forum_cat_name']."'>\n";
	}
	$sel = ($data['forum_id'] == $fdata['forum_id'] ? " selected='selected'" : "");
	$forum_list .= "<option value='".$data['forum_id']."'$sel>".$data['forum_name']."</option>\n";
}
$forum_list .= "</optgroup>\n";
if (iMOD) {
	echo "<form name='modopts' method='post' action='options.php?forum_id=".$fdata['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>\n";
}
echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
echo "<td style='padding-top:5px'>".$locale['540']."<br />\n";
echo "<select name='jump_id' class='textbox' onchange=\"jumpforum(this.options[this.selectedIndex].value);\">\n";
echo $forum_list."</select></td>\n";

if (iMOD) {
	echo "<td align='right' style='padding-top:5px'>\n";
	echo $locale['520']."<br />\n<select name='step' class='textbox'>\n";
	echo "<option value='none'>&nbsp;</option>\n";
	echo "<option value='renew'>".$locale['527']."</option>\n";
	echo "<option value='delete'>".$locale['521']."</option>\n";
	echo "<option value='".($fdata['thread_locked'] ? "unlock" : "lock")."'>".($fdata['thread_locked'] ? $locale['523'] : $locale['522'])."</option>\n";
	echo "<option value='".($fdata['thread_sticky'] ? "nonsticky" : "sticky")."'>".($fdata['thread_sticky'] ? $locale['525'] : $locale['524'])."</option>\n";
	echo "<option value='move'>".$locale['526']."</option>\n";
	echo "</select>\n<input type='submit' name='go' value='".$locale['528']."' class='button' />\n";
	echo "</td>\n";
}
echo "</tr>\n</table>\n"; if (iMOD) { echo "</form>\n"; }

if ($can_post || $can_reply) {
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td align='right' style='padding-top:10px'>\n<!--post_forum_buttons-->\n";
	if ($can_post) {
		echo "<a href='post.php?action=newthread&amp;forum_id=".$fdata['forum_id']."'>";
		echo "<img src='".get_image("newthread")."' alt='".$locale['566']."' style='border:0px' /></a>\n";
	}
	if (!$fdata['thread_locked'] && $can_reply) {
		echo "<a href='post.php?action=reply&amp;forum_id=".$fdata['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>";
		echo "<img src='".get_image("reply")."' alt='".$locale['565']."' style='border:0px' /></a>\n";
	}
	echo "</td>\n</tr>\n</table>\n";
}
closetable();

if ($can_reply && !$fdata['thread_locked']) {
	require_once INCLUDES."bbcode_include.php";
	opentable($locale['512']);
	echo "<form name='inputform' method='post' action='".FORUM."post.php?action=reply&amp;forum_id=".$fdata['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>\n";
	echo "<table cellpadding='0' cellspacing='1' class='tbl-border center'>\n<tr>\n";
	echo "<td align='center' class='tbl1'><textarea name='message' cols='70' rows='7' class='textbox' style='width:98%'></textarea><br />\n";
	echo display_bbcodes("360px", "message")."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' class='tbl2'><label><input type='checkbox' name='disable_smileys' value='1' /> ".$locale['513']."</label>";
	if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
		echo "<br />\n<label><input type='checkbox' name='show_sig' value='1' checked='checked' /> ".$locale['513a']."</label>";
	}
	if ($settings['thread_notify']) {
		if (dbcount("(thread_id)", DB_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'")) {
			$notify_checked = " checked='checked'";
		} else {
			$notify_checked = "";
		}
		echo "<br />\n<label><input type='checkbox' name='notify_me' value='1'".$notify_checked." /> ".$locale['513b']."</label>";
	}
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' class='tbl1'>\n";
	echo "<input type='submit' name='previewreply' value='".$locale['514a']."' class='button' />&nbsp;\n";
	echo "<input type='submit' name='postreply' value='".$locale['514']."' class='button' />\n";
	echo "</td>\n";
	echo "</tr>\n</table>\n</form><!--sub_forum_thread-->\n";
	closetable();
}

// viewthread javascript, moved to footer
$viewthread_js  = "<script type='text/javascript'>";
$viewthread_js .= "/*<![CDATA[*/";
$viewthread_js .= "jQuery(document).ready(function(){";
if (!empty($highlight_js) || !empty($colorbox_js) || !empty($edit_reason_js)){
	$viewthread_js .= $highlight_js.$colorbox_js.$edit_reason_js;
}
$viewthread_js .= "jQuery('a[href=#top]').click(function(){";
$viewthread_js .=	"jQuery('html, body').animate({scrollTop:0}, 'slow');";
$viewthread_js .=	"return false;";
$viewthread_js .= "});";
$viewthread_js .= "});";
// below functions could be made more unobtrusive thanks to jQuery, giving a more accessible cms
$viewthread_js .= "function jumpforum(forum_id){";
$viewthread_js .=    "document.location.href='".FORUM."viewforum.php?forum_id='+forum_id;";
$viewthread_js .= "}";
if (iMOD) { // only moderators need this javascript
	$viewthread_js .= "function setChecked(frmName,chkName,val){";
	$viewthread_js .=	"dml=document.forms[frmName];";
	$viewthread_js .=		"len=dml.elements.length;";
	$viewthread_js .=		"for(i=0;i<len;i++){";
	$viewthread_js .=			"if(dml.elements[i].name==chkName){";
	$viewthread_js .=				"dml.elements[i].checked=val;";
	$viewthread_js .=			"}";
	$viewthread_js .=		"}";
	$viewthread_js .= "}"; }
$viewthread_js .= "/*]]>*/";
$viewthread_js .= "</script>";
add_to_footer($viewthread_js); //unset($viewthread_js);

require_once THEMES."templates/footer.php";
?>