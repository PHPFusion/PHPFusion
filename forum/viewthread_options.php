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
	$del_posts = "";
	$i = 0;
	$post_count = 0;
	foreach ($_POST['delete_post'] as $del_post_id) {
		if (isnum($del_post_id)) {
			$del_posts .= ($del_posts ? "," : "").$del_post_id;
			$i++;
		}
	}
	if ($del_posts) {
		$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." WHERE post_id IN (".$del_posts.") GROUP BY post_author");
		if (dbrows($result)) {
			while ($pdata = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-".$pdata['num_posts']." WHERE user_id='".$pdata['post_author']."'");
				$post_count = $post_count+$pdata['num_posts'];
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
		$thread_count = FALSE;
	} else {
		$result = dbquery("SELECT post_datestamp, post_author, post_id FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_datestamp DESC LIMIT 1");
		$pdata = dbarray($result);
		$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$pdata['post_datestamp']."', thread_lastpostid='".$pdata['post_id']."', thread_postcount=thread_postcount-1, thread_lastuser='".$pdata['post_author']."' WHERE thread_id='".$_GET['thread_id']."'");
		$thread_count = TRUE;
	}
	$result = dbquery("SELECT post_datestamp, post_author FROM ".DB_POSTS." WHERE forum_id='".$info['forum_id']."' ORDER BY post_datestamp DESC LIMIT 1");
	if (dbrows($result)) {
		$pdata = dbarray($result);
		$forum_lastpost = "forum_lastpost='".$pdata['post_datestamp']."', forum_lastuser='".$pdata['post_author']."'";
	} else {
		$forum_lastpost = "forum_lastpost='0', forum_lastuser='0'";
	}
	$result = dbquery("UPDATE ".DB_FORUMS." SET ".$forum_lastpost.(!$thread_count ? ", forum_threadcount=forum_threadcount-1," : ",")." forum_postcount=forum_postcount-".$post_count." WHERE forum_id = '".$info['forum_id']."'");
	if (!$thread_count) {
		redirect(FORUM."index.php?viewforum.php?forum_id=".$info['forum_id']);
	}
} elseif (isset($_POST['move_posts']) && isset($_POST['delete_post']) && is_array($_POST['delete_post']) && count($_POST['delete_post'])) {
	$move_posts = "";
	$array_post = array();
	$f_post = FALSE;
	$dell_f_post = FALSE;
	$f_post_blo = FALSE;
	$first_post = dbarray(dbquery("SELECT post_id FROM ".DB_POSTS." WHERE thread_id='".$info['thread_id']."' ORDER BY post_datestamp ASC LIMIT 1"));
	// negotiate a first post
	foreach ($_POST['delete_post'] as $move_post_id) {
		if (isnum($move_post_id)) {
			$move_posts .= ($move_posts ? "," : "").$move_post_id;
			$array_post[] = $move_post_id;
			if ($move_post_id == $first_post['post_id']) {
				$f_post = TRUE;
			}
		}
	}
	// triggered move post
	if ($move_posts) {
		// validate whether post exists
		$move_result = dbquery("SELECT forum_id, thread_id, COUNT(post_id) as num_posts
			FROM ".DB_POSTS." 
			WHERE post_id IN (".$move_posts.") AND forum_id='".$info['forum_id']."'
			AND thread_id='".$info['thread_id']."'
			GROUP BY thread_id");
		if (dbrows($move_result)) {
			$pdata = dbarray($move_result);
			$num_posts = $pdata['num_posts'];
			echo openmodal('forum0300', $locale['forum_0300'], array('class'=>'modal-md top-30p', 'static'=>1));
			if ($f_post) { // there is a first post.
				echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>";
				if ($num_posts != dbcount("(post_id)", DB_POSTS, "thread_id='".$pdata['thread_id']."'")) {
					$dell_f_post = TRUE;
					echo $locale['forum_0305']."<br />\n"; // trying to remove first post with other post in the thread
				} else {
					echo $locale['forum_0306']."<br />\n"; // confirm ok to remove first post.
				}
				if ($dell_f_post && count($array_post) == 1) {
					echo "<br /><strong>".$locale['forum_0307']."</strong><br /><br />\n"; // no post to move.
					echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a>";
					$f_post_blo = TRUE;
				}
				echo "</div></div>\n";
			}
			if (!isset($_POST['new_forum_id']) && !$f_post_blo) {
				$fl_result = dbquery("
					SELECT f.forum_id, f.forum_name, f2.forum_name AS forum_cat_name, 
						(SELECT COUNT(thread_id) 
						FROM ".DB_THREADS." th 
						WHERE f.forum_id=th.forum_id AND th.thread_id!='".$pdata['thread_id']."'
						GROUP BY th.forum_id) AS threadcount
					FROM ".DB_FORUMS." f
					INNER JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
							WHERE ".groupaccess('f.forum_access')." AND f.forum_type !='1'
					HAVING threadcount != 0
					ORDER BY f2.forum_order ASC, f.forum_order ASC
				");
				if (dbrows($fl_result) > 0) {

					// To exclude all type 1 forum.
					$find_category = dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_type='1'");
					$category_excluded = array();
					if (dbrows($find_category)>0) {
						while ($cf_data = dbarray($find_category)) {
							$category_excluded[] = $cf_data['forum_id'];
							}
						}

					echo openform('modopts', 'modopts', 'post', FORUM."viewthread.php?thread_id=".$info['thread_id']."&amp;rowstart=".$_GET['rowstart']);
					echo form_select_tree($locale['forum_0301'], 'new_forum_id', 'new_forum_id', '', array('disable_opts'=>$category_excluded, 'no_root'=>1, 'inline'=>1), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
					foreach ($array_post as $value) {
						echo form_hidden('', "delete_post[]", "delete_post[$value]", $value);
					}
					echo form_hidden('', 'move_posts', 'move_posts', 1);
					echo "<div class='clearfix'>\n<div class='col-xs-12 col-md-offset-3 col-lg-offset-3'>\n";
					echo form_button($locale['forum_0302'], 'go', 'go', $locale['forum_0302'], array('class'=>'btn-primary btn-sm'));
					echo "</div>\n</div>\n";
					echo closeform();
				} else {
					echo "<div class='well'>\n";
					echo "<strong>".$locale['forum_0310']."</strong><br /><br />\n";
					echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a><br /><br />\n";
					echo "</div>\n";
				}
			}
			// Select Threads in Selected Forum.
			elseif (isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && !isset($_POST['new_thread_id']) && !$f_post_blo) {
				// build the list.
				$tl_result = dbquery("
						SELECT thread_id, thread_subject
						FROM ".DB_THREADS." 
						WHERE forum_id='".$_POST['new_forum_id']."' AND thread_id!='".$info['thread_id']."' AND thread_hidden='0'
						ORDER BY thread_subject ASC
					");

				if (dbrows($tl_result) > 0) {
					$forum_list = array();
					while ($tl_data = dbarray($tl_result)) {
						$forum_list[$tl_data['thread_id']] = $tl_data['thread_subject'];
					}
					echo openform('modopts', 'modopts', 'post', FORUM."viewthread.php?thread_id=".$info['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;sv");
					echo form_hidden('', 'new_forum_id', 'new_forum_id', $_POST['new_forum_id']);
					echo form_select($locale['forum_0303'], 'new_thread_id', 'new_thread_id', $forum_list, '', array('inline'=>1));
					foreach ($array_post as $value) {
						echo form_hidden('', "delete_post[]", "delete_post[$value]", $value);
					}
					echo form_hidden('', 'move_posts', 'move_posts', 1);
					echo "<div class='clearfix'>\n<div class='col-xs-12 col-md-offset-3 col-lg-offset-3'>\n";
					echo form_button($locale['forum_0304'], 'go', 'go', $locale['forum_0302'], array('class'=>'btn-primary btn-sm'));
					echo "</div>\n</div>\n";
				} else {
					echo "<div id='close-message'><div class='admin-message'>".$locale['forum_0308']."<br /><br />\n";
					echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."'>".$locale['forum_0309']."</a>\n";
					echo "</div></div><br />\n";
				}
			}

			elseif (isset($_GET['sv']) && isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && isset($_POST['new_thread_id']) && isnum($_POST['new_thread_id'])) {
				// Execute move and redirect after
				$move_posts_add = "";
				if (!dbcount("(thread_id)", DB_THREADS, "thread_id='".$_POST['new_thread_id']."' AND forum_id='".$_POST['new_forum_id']."'")) {
					redirect(FORUM."viewthread.php?thread_id=".$info['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=1");
				}
				foreach ($array_post as $move_post_id) {
					if (isnum($move_post_id)) {
						if ($f_post && $dell_f_post) {
							if ($move_post_id != $first_post['post_id']) {
								$move_posts_add .= ($move_posts_add ? "," : "").$move_post_id;
							}
							$num_posts = ($num_posts-1);
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
						redirect(FORUM."viewthread.php?thread_id=".$info['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=2"); // or here.
					}
				} else {
					redirect(FORUM."viewthread.php?thread_id=".$info['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=3");
				}
			}

			else {
				echo closemodal();
			}
		} else {
			redirect(FORUM."viewthread.php?thread_id=".$info['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=2"); // always go here.
		}
	} else {
		redirect(FORUM."viewthread.php?thread_id=".$info['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=3");
	}
	echo closemodal();
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
		echo "<a href='".FORUM."viewthread.php?thread_id=".$info['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a><br />";
		echo "</div></div>\n";
		closetable();
		require_once THEMES."templates/footer.php";
		die();
	}
}
?>