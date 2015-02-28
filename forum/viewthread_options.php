<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: viewthread_mod_options.php
| Author: Slawomir Nonas (slawekneo)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (!defined("iMOD") || !iMOD) { redirect("index.php"); }

if (isset($_POST['delete_posts']) && isset($_POST['delete_post']) && is_array($_POST['delete_post']) && count($_POST['delete_post'])) {
	$del_posts = ""; $i = 0; $post_count = 0;
	foreach ($_POST['delete_post'] as $del_post_id) {
		if (isnum($del_post_id)) { $del_posts .= ($del_posts ? "," : "").$del_post_id; $i++; }
	}
	if ($del_posts) {
		$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." WHERE post_id IN (".$del_posts.") GROUP BY post_author");
		if (dbrows($result)) {
			while ($pdata = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-".$pdata['num_posts']." WHERE user_id='".$pdata['post_author']."'");
				$post_count = $post_count + $pdata['num_posts'];
			}
		}
		$result = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id IN (".$del_posts.")");
		if (dbrows($result)) {
			while ($adata = dbarray($result)) {
				unlink(FORUM."attachments/".$adata['attach_name']);
			}
		}
		$result = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$_GET['thread_id']."' AND post_id IN(".$del_posts.")");
		$result = dbquery("DELETE FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' AND post_id IN(".$del_posts.")");
	}
	if (!dbcount("(post_id)", DB_POSTS, "thread_id='".$_GET['thread_id']."'")) {
		$result = dbquery("DELETE FROM ".DB_THREADS." WHERE thread_id='".$_GET['thread_id']."'");
		$result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$_GET['thread_id']."'");
		$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$_GET['thread_id']."'");
		$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."'");
		$result = dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$_GET['thread_id']."'");
		$thread_count = false;
	} else {
		$result = dbquery("SELECT post_datestamp, post_author, post_id FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_datestamp DESC LIMIT 1");
		$pdata = dbarray($result);
		$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$pdata['post_datestamp']."', thread_lastpostid='".$pdata['post_id']."', thread_postcount=thread_postcount-1, thread_lastuser='".$pdata['post_author']."' WHERE thread_id='".$_GET['thread_id']."'");
		$thread_count = true;
	}
	$result = dbquery("SELECT post_datestamp, post_author FROM ".DB_POSTS." WHERE forum_id='".$fdata['forum_id']."' ORDER BY post_datestamp DESC LIMIT 1");
	if (dbrows($result)) {
		$pdata = dbarray($result);
		$forum_lastpost = "forum_lastpost='".$pdata['post_datestamp']."', forum_lastuser='".$pdata['post_author']."'";
	} else {
		$forum_lastpost = "forum_lastpost='0', forum_lastuser='0'";
	}
	$result = dbquery("UPDATE ".DB_FORUMS." SET ".$forum_lastpost.(!$thread_count ? ", forum_threadcount=forum_threadcount-1," : ",")." forum_postcount=forum_postcount-".$post_count." WHERE forum_id = '".$fdata['forum_id']."'");
	if (!$thread_count) { redirect("viewforum.php?forum_id=".$fdata['forum_id']); }
} elseif (isset($_POST['move_posts']) && isset($_POST['delete_post']) && is_array($_POST['delete_post']) && count($_POST['delete_post'])) {
	$move_posts = ""; $array_post = array(); $f_post = false; $dell_f_post = false; $f_post_blo = false;
	$first_post = dbarray(dbquery("SELECT post_id FROM ".DB_POSTS." WHERE thread_id='".$fdata['thread_id']."' ORDER BY post_datestamp ASC LIMIT 1"));
	foreach ($_POST['delete_post'] as $move_post_id) {
		if (isnum($move_post_id)) {
			$move_posts .= ($move_posts ? "," : "").$move_post_id;
			$array_post[] = $move_post_id;
			if ($move_post_id == $first_post['post_id']) { $f_post = true; }
		}
	}
	if ($move_posts) {
		$move_result = dbquery(
			"SELECT forum_id, thread_id, COUNT(post_id) as num_posts 
			FROM ".DB_POSTS." 
			WHERE post_id IN (".$move_posts.") AND forum_id='".$fdata['forum_id']."'
			AND thread_id='".$fdata['thread_id']."' 
			GROUP BY thread_id"
		);
		if (dbrows($move_result)) {
			$pdata = dbarray($move_result);
			$num_posts = $pdata['num_posts'];
			opentable($locale['600']);
			if ($f_post) {
				echo "<div id='close-message'><div class='admin-message'>";
				if ($num_posts != dbcount("(post_id)", DB_POSTS, "thread_id='".$pdata['thread_id']."'")) {
					$dell_f_post = true;
					echo $locale['605']."<br />\n";
				} else {
					echo $locale['606']."<br />\n";
				}
				if ($dell_f_post && count($array_post) == 1) {
					echo "<br /><strong>".$locale['607']."</strong><br /><br />\n";
					echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['609']."</a>";
					$f_post_blo = true;
				}	
				echo "</div></div>\n";
			}
			if (!isset($_POST['new_forum_id']) && !$f_post_blo) {
				$forum_list = ""; $current_cat = "";
				$fl_result = dbquery("
					SELECT f.forum_id, f.forum_name, f2.forum_name AS forum_cat_name, 
						(SELECT COUNT(thread_id) 
						FROM ".DB_THREADS." th 
						WHERE f.forum_id=th.forum_id AND th.thread_id!='".$pdata['thread_id']."'
						GROUP BY th.forum_id) AS threadcount
					FROM ".DB_FORUMS." f
					INNER JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
					WHERE ".groupaccess('f.forum_access')." AND f.forum_cat!='0' 
					HAVING threadcount != 0
					ORDER BY f2.forum_order ASC, f.forum_order ASC
				");
				if (dbrows($fl_result) > 0) {
					while ($fl_data = dbarray($fl_result)) {
						if ($fl_data['forum_cat_name'] != $current_cat) {
							if ($current_cat != "") { $forum_list .= "</optgroup>\n"; }
							$current_cat = $fl_data['forum_cat_name'];
							$forum_list .= "<optgroup label='".$fl_data['forum_cat_name']."'>\n";
						}
						$sel = ($fl_data['forum_id'] == $fdata['forum_id'] ? " selected='selected'" : "");
						$forum_list .= "<option value='".$fl_data['forum_id']."'$sel>".$fl_data['forum_name']."</option>\n";
					}
					$forum_list .= "</optgroup>\n";
					echo "<form name='modopts' method='post' action='".FUSION_SELF."?thread_id=".$fdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>\n"; 
					echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'>\n<tr>\n";
					echo "<td style='padding-top:5px' align='center'>".$locale['601']."\n";
					echo "<select name='new_forum_id' class='textbox'>\n$forum_list</select>\n";
					foreach($array_post as $value) { echo "<input type='hidden' name='delete_post[]' value='".$value."' />\n"; }
					echo "<input type='hidden' name='move_posts' value='1' />\n";
					echo "<input type='submit' name='go' value='".$locale['602']."' class='button' /></td>\n</tr>\n</table>\n</form>\n";
				} else {
					echo "<div id='close-message'><div class='admin-message'><br />\n";
					echo "<strong>".$locale['610']."</strong><br /><br />\n";
					echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['609']."</a><br /><br />\n";
					echo "</div></div>";
				}
			} elseif (isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && !isset($_POST['new_thread_id']) &&  !$f_post_blo) {
					$forum_list = "";
					$tl_result = dbquery("
						SELECT thread_id, thread_subject
						FROM ".DB_THREADS." 
						WHERE forum_id='".$_POST['new_forum_id']."' AND thread_id!='".$fdata['thread_id']."' AND thread_hidden='0' 
						ORDER BY thread_subject ASC
					");
					if (dbrows($tl_result) > 0) {
						while ($tl_data = dbarray($tl_result)) {
							$sel = ($tl_data['thread_id'] == $fdata['thread_id'] ? " selected='selected'" : "");
							$forum_list .= "<option value='".$tl_data['thread_id']."'$sel>".$tl_data['thread_subject']."</option>\n";
						}
						echo "<form name='modopts' method='post' action='".FUSION_SELF."?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;sv'>\n"; 
						echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'>\n<tr>\n";
						echo "<td style='padding-top:5px' align='center'>".$locale['603']."\n";
						echo "<input type='hidden' name='new_forum_id' value='".$_POST['new_forum_id']."' />\n";
						echo "<select name='new_thread_id' class='textbox' >\n$forum_list</select>\n";
						foreach($array_post as $value) { echo "<input type='hidden' name='delete_post[]' value='".$value."' />\n"; }
						echo "<input type='hidden' name='move_posts' value='1' />\n";
						echo "<input type='submit' name='go' value='".$locale['604']."' class='button' /></td>\n";
						echo "</tr></table></form>";
					} else { 
						echo "<div id='close-message'><div class='admin-message'>".$locale['608']."<br /><br />\n";
						echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."'>".$locale['609']."</a>\n";
						echo "</div></div><br />\n";
					}
			} elseif (isset($_GET['sv']) && isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && isset($_POST['new_thread_id']) && isnum($_POST['new_thread_id'])) {
				$move_posts_add = "";
				if (!dbcount("(thread_id)", DB_THREADS, "thread_id='".$_POST['new_thread_id']."' AND forum_id='".$_POST['new_forum_id']."'")) {
					redirect(FORUM."viewthread.php?thread_id=".$fdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=1");
				}
				foreach ($array_post as $move_post_id) {
					if (isnum($move_post_id)) {
						if ($f_post && $dell_f_post) { 
							if ($move_post_id != $first_post['post_id']) { $move_posts_add .= ($move_posts_add ? "," : "").$move_post_id; }
							$num_posts = ($num_posts - 1); 
						} else { 
							$move_posts_add = $move_post_id.($move_posts_add ? "," : "").$move_posts_add;
						}
					}
				}
				if ($move_posts_add) {
					$posts_ex = dbcount("(post_id)", DB_POSTS, "thread_id='".$pdata['thread_id']."' AND post_id IN (".$move_posts_add.")");
					if ($posts_ex) {	
						$result = dbquery("UPDATE ".DB_POSTS." SET forum_id='".$_POST['new_forum_id']."', thread_id='".$_POST['new_thread_id']."' WHERE post_id IN (".$move_posts_add.")");
						$result = dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET thread_id='".$_POST['new_thread_id']."' WHERE post_id IN(".$move_posts_add.")");
						$new_thread = dbarray(dbquery("
							SELECT forum_id, thread_id, post_id, post_author, post_datestamp 
							FROM ".DB_POSTS." 
							WHERE thread_id='".$_POST['new_thread_id']."' 
							ORDER BY post_datestamp DESC 
							LIMIT 1
						"));
						$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$new_thread['post_datestamp']."', thread_lastpostid='".$new_thread['post_id']."', thread_postcount=thread_postcount+".$num_posts.", thread_lastuser='".$new_thread['post_author']."' WHERE thread_id='".$_POST['new_thread_id']."'");
						$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$new_thread['post_datestamp']."', forum_postcount=forum_postcount+".$num_posts.", forum_lastuser='".$new_thread['post_author']."' WHERE forum_id='".$_POST['new_forum_id']."'");
						$old_thread = dbarray(dbquery("
							SELECT forum_id, thread_id, post_id, post_author, post_datestamp 
							FROM ".DB_POSTS." 
							WHERE thread_id='".$pdata['thread_id']."' 
							ORDER BY post_datestamp DESC 
							LIMIT 1
						"));
						if (!dbcount("(post_id)", DB_POSTS, "thread_id='".$pdata['thread_id']."'")) {
							$new_last_post = dbarray(dbquery("SELECT post_author, post_datestamp FROM ".DB_POSTS." WHERE forum_id='".$pdata['forum_id']."' ORDER BY post_datestamp DESC LIMIT 1 "));
							$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$new_last_post['post_datestamp']."', forum_postcount=forum_postcount-".$num_posts.", forum_threadcount=forum_threadcount-1, forum_lastuser='".$new_last_post['post_author']."' WHERE forum_id='".$pdata['forum_id']."'");
							$result = dbquery("DELETE FROM ".DB_THREADS." WHERE thread_id='".$pdata['thread_id']."'");
							$result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$pdata['thread_id']."'");
							$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$pdata['thread_id']."'");
							$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$pdata['thread_id']."'");
							$result = dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$pdata['thread_id']."'");
						} else {
							$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$old_thread['post_datestamp']."', thread_lastpostid='".$old_thread['post_id']."', thread_postcount=thread_postcount-".$num_posts.", thread_lastuser='".$old_thread['post_author']."' WHERE thread_id='".$pdata['thread_id']."'");
							$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$old_thread['post_datestamp']."', forum_postcount=forum_postcount-".$num_posts.", forum_lastuser='".$old_thread['post_author']."' WHERE forum_id='".$pdata['forum_id']."'");
						}
						$pid = count($array_post)-1;
						redirect(FORUM."viewthread.php?thread_id=".$_POST['new_thread_id']."&amp;pid=".$array_post[$pid]."#post_".$array_post[$pid]);
					} else { 
						redirect(FORUM."viewthread.php?thread_id=".$fdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=2");
					}
				} else { 
					redirect(FORUM."viewthread.php?thread_id=".$fdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=3");
				}
			} else { 
				redirect(FORUM."index.php");	
				closetable();
			}
		} else { 
			redirect(FORUM."viewthread.php?thread_id=".$fdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=2");
		}
	} else { 
		redirect(FORUM."viewthread.php?thread_id=".$fdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=3");
	}
	closetable();
	require_once THEMES."templates/footer.php";
	die();
} elseif (isset($_GET['error']) && isnum($_GET['error'])) {
	if ($_GET['error'] == 1) {
		$message = $locale['error-MP001'];
	} elseif ($_GET['error'] == 2) {
		$message = $locale['error-MP002'];
	} elseif ($_GET['error'] == 3) {
		$message = $locale['error-MP003'];
	} else {
		$message = "";
	}
	if ($message != "") {
		opentable($locale['error-MP000']);
		echo "<div id='close-message'><div class='admin-message'>".$message."<br /><br />\n";
		echo "<a href='".FORUM."viewthread.php?thread_id=".$fdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['609']."</a><br />";
		echo "</div></div>\n";
		closetable();
		require_once THEMES."templates/footer.php";
		die();
	}
}
?>