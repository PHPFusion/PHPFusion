<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: post_actions.php
| Author: PHP-Fusion Development Team
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
if (!defined("IN_FUSION")) { die("Access Denied"); }
add_to_title($locale['global_201'].$locale['forum_0501']);

// poll add option not adding option.
$can_poll = $info['forum_poll'] && checkgroup($info['forum_poll']) ? 1  : 0;
$can_attach = $info['forum_attach'] && checkgroup($info['forum_attach']) ? 1 : 0;
$error = 0;
// define mode.
$data['edit'] = (isset($data['edit']) && $data['edit'] == 1) ? 1 : 0;
$data['new'] = (isset($data['new']) && $data['new'] == 1) ? 1 : 0;
$data['reply'] = (isset($data['reply']) && $data['reply'] == 1) ? 1 : 0;

// Debug 1 will stop all scripts from execution of save/update/delete. Use it when fixing isset errors.
// Debug 2 will stop just the redirection itself so you can compare to your tables.
$debug = false; // this will stop all scripts from execution save/update/delete.
$debug2 = false; // to stop just the redirection
/* -------------------
| The Button Roullette
+ ---------------------*/
$acceptable_postName = array(
	'savechanges',
	'postreply',
	'postnewthread',
	'add_poll_option',
	'delete_poll',
	'update_poll_title',
	'update_poll_option',
	'delete_poll_option',
	'previewpost'
);
$executable = 0;
foreach($acceptable_postName as $name) {
	if (array_key_exists($name, $_POST)) {
		$executable = $name;
	}
}

if (!$executable) throw new \Exception('Non-executable POST actions');

/* -------------------
| Form Processing
+ ---------------------*/
if ($executable && iMEMBER) {
	// thread data
	$data['thread_subject'] = isset($_POST['thread_subject']) ? form_sanitizer($_POST['thread_subject'], '', 'thread_subject') : '';
	$data['forum_id'] = isset($_POST['forum_id']) && isnum($_POST['forum_id']) ? form_sanitizer($_POST['forum_id'], '', 'forum_id') : $_GET['forum_id'];
	// only moderators or superadmin can lock and sticky
	$data['thread_sticky'] = isset($_POST['thread_sticky']) && (iMOD || iSUPERADMIN) ? 1 : 0;
	$data['thread_locked'] = isset($_POST['thread_locked']) && (iMOD || iSUPERADMIN) ? 1 : 0;
	$data['thread_postcount'] = 1;
	$data['thread_poll'] = isset($_POST['thread_poll']) ? form_sanitizer($_POST['thread_poll'], '', 'thread_poll') : '';
	$data['thread_lastuser'] = $userdata['user_id'];
	$data['thread_lastpostid'] = 0;
	$data['thread_lastpost'] = time();
	$data['thread_views'] = 0;
	$data['thread_author'] = $userdata['user_id'];

	// post data
	$data['post_message'] = form_sanitizer($_POST['post_message'], '', 'post_message');
	$datap['post_smileys'] = isset($_POST['post_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $data['post_message']) ? 0 : 1;
	$data['post_showsig'] = isset($_POST['post_showsig']) ? 1 : 0;
	$data['post_locked'] = isset($_POST['post_locked']) && $data['edit'] ? 1 : 0;
	$data['post_author'] = $data['thread_lastuser'];
	$data['post_datestamp'] = $data['thread_lastpost'];
	$data['post_ip'] = USER_IP;
	$data['post_ip_type'] = USER_IP_TYPE;
	$data['post_edituser'] = 0;
	$data['post_edittime'] = 0;
	$data['post_editreason'] = '';
	$data['notify_me'] = $settings['thread_notify'] && isset($_POST['notify_me']) ? 1 : 0;

	/* -----------------
	|  Hide Edit Reason
	|  Note : STRICTLY Executable only during Edit.
	+ ------------------*/
	if ($data['edit']) {
		if (isset($_POST['hide_edit'])) {
			$post_edittime = 0;
			$post_editreason = '';
			$post_edituser = 0;
		} else {
			$post_edituser = $userdata['user_id'];
			$post_edittime = time();
			$post_editreason = form_sanitizer($_POST['post_editreason'], '', 'post_editreason');

			$thread_lastpost = dbarray(dbquery("SELECT post_id FROM ".DB_FORUM_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
			if ($thread_lastpost['post_id'] == $_GET['post_id'] && time()-$data['post_datestamp'] < 5*60) {
				$post_edittime = 0;
				$post_editreason = '';
				$post_edituser = 0;
			}
			if ($settings['forum_editpost_to_lastpost']) {
				$lastPost = dbcount("(thread_id)", DB_FORUM_THREADS, "thread_lastpostid='".$_GET['post_id']."'");
				// update thread_laspost time
				if ($lastPost > 0) {
					$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".$data['post_edittime']."' WHERE thread_id='".$_GET['thread_id']."'");
				}
				// update forum_laspost time.
				$forum_lastpost = dbarray(dbquery("SELECT post_id FROM ".DB_FORUM_POSTS." WHERE forum_id='".$_GET['forum_id']."' ORDER BY post_id DESC LIMIT 1"));
				if ($forum_lastpost['post_id'] == $_GET['post_id']) {
					$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$data['post_edittime']."' WHERE forum_id='".$_GET['forum_id']."'");
				}
			}
			$result = dbquery("UPDATE ".DB_FORUM_POSTS." SET post_edituser = '".$post_edituser."', post_edittime = '".$post_edittime."', post_editreason = '".$post_editreason."' WHERE post_id='".intval($_GET['post_id'])."'");
		}
	}

	/* -----------------
	|  Poll Validation
	|  Note : only happens during Newthread and Edit First Post
	+ ------------------*/
	$data['poll_opts'] = array();
	$can_edit_poll = ($data['thread_poll'] && ($data['post_author'] == $data['thread_author']) && ($userdata['user_id'] == $data['thread_author']) or iSUPERADMIN or iMOD) ? 1 : 0;
	// only execute add poll in edit or newthread mode.
	if ($data['edit'] or $data['new']) {
		if ($can_poll) {
			$data['forum_poll_title'] = isset($_POST['forum_poll_title']) ? form_sanitizer($_POST['forum_poll_title'], '', 'forum_poll_title') : '';
			// to update poll title
			if (isset($_POST['update_poll_title']) && $data['forum_poll_title'] && $can_edit_poll) {
				// to update poll title
				$result = dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_title='".$data['forum_poll_title']."' WHERE thread_id='".$_GET['thread_id']."'");
			}
			// to delete the entire poll
			elseif (isset($_POST['delete_poll']) && $can_edit_poll) {
				// to delete poll
				$result = dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$_GET['thread_id']."'");
				$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."'");
				$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$_GET['thread_id']."'");
				$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_poll='0' WHERE thread_id='".$_GET['thread_id']."'");
				$data['forum_poll'] = 0;
				$data['thread_poll'] = 0;
			}
			// to configure poll options
			if (isset($_POST['poll_options']) && is_array($_POST['poll_options']) && !empty($_POST['poll_options'])) {
				$i = 1;
				foreach ($_POST['poll_options'] as $poll_option) {
					if ($data['edit']) {
						if (isset($_POST['delete_poll_option'][$i])) {
							// delete poll option $i
							$data = dbarray(dbquery("SELECT forum_poll_option_votes FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$i."'"));
							$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$i."'");
							$result = dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_id=forum_poll_option_id-1 WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id > '".$i."'");
							$result = dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_votes=forum_poll_votes-".$data['forum_poll_option_votes']." WHERE thread_id='".$_GET['thread_id']."'");
						}
						elseif (isset($_POST['add_poll_option'][$i])) {
							// add poll option $i
							if (trim($poll_option)) {
								$data['poll_opts'][] = form_sanitizer($poll_option, '');
								$result = dbquery("INSERT INTO ".DB_FORUM_POLL_OPTIONS." (thread_id, forum_poll_option_id, forum_poll_option_text, forum_poll_option_votes) VALUES('".$_GET['thread_id']."', '".$i."', '".trim(stripinput($poll_option))."', '0')");
							}
						}
						elseif (isset($_POST['update_poll_option'][$i])) {
							// update poll option $i
							if (trim($poll_option)) {
								$data['poll_opts'][] = form_sanitizer($poll_option, '');
								$result = dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_text='".trim(stripinput($poll_option))."' WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$i."'");
							}
						} else {
							if (trim($poll_option)) {
								$data['poll_opts'][] = form_sanitizer($poll_option, '');
							}
						}
					} else {
						if (trim($poll_option)) {
							$data['poll_opts'][] = form_sanitizer($poll_option, '');
						}
					}
					$i++;
				}
				$data['thread_poll'] = trim($_POST['forum_poll_title']) && !empty($data['poll_opts']) ? 1 : 0;
			}
		}
	}

	$flood = FALSE;
	// On New Thread Post Execution
	if (isset($_POST['postnewthread']) && checkgroup($info['forum_post'])) {
		require_once INCLUDES."flood_include.php";
		if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'") && !defined('FUSION_NULL')) {
			$data['thread_id'] = dbquery_insert(DB_FORUM_THREADS, $data, 'save', array('primary_key'=>'thread_id')); // forum id is missing.
			$data['post_id'] = dbquery_insert(DB_FORUM_POSTS, $data, 'save', array('primary_key'=>'post_id'));
			$forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
			$list_of_forums = get_all_parent($forum_index, $_GET['forum_id']);
			// update every parent node as well
			foreach($list_of_forums as $forum_id) {
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastpostid='".$data['post_id']."', forum_lastuser='".$userdata['user_id']."' WHERE forum_id='".$forum_id."'");
			}
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastpostid='".$data['post_id']."', forum_lastuser='".$userdata['user_id']."' WHERE forum_id='".$_GET['forum_id']."'");
			$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpostid='".$data['post_id']."' WHERE thread_id='".$data['thread_id']."'");
			$result = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$userdata['user_id']."'");
			if ($data['notify_me']) {
				$result = dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$data['thread_id']."', '".time()."', '".$userdata['user_id']."', '1')");
			}
			if ($can_poll && $data['thread_poll']) {
				if ($data['forum_poll_title'] && !empty($data['poll_opts'])) {
					//thread id ok. forum_poll_title ok.
					$data['forum_poll_start'] = time();
					$data['forum_poll_length'] = 0;
					$data['forum_poll_votes'] = 0;
					dbquery_insert(DB_FORUM_POLLS, $data, 'save', array('noredirect'=>1));
					$data['forum_poll_id'] = dblastid();
					$i = 1;
					foreach ($data['poll_opts'] as $option_text) {
						$data['forum_poll_option_id'] = $i;
						$data['forum_poll_option_text'] = $option_text;
						$data['forum_poll_option_votes'] = 0;
						dbquery_insert(DB_FORUM_POLL_OPTIONS, $data, 'save', array('noredirect'=>1));
						$i++;
					}
				}
			}
		}
	}

	// On Edit Post Execution
	if (isset($_POST['savechanges']) && $data['edit'] && checkgroup($info['forum_post'])) {
		// Delete Action - maybe can change to a stand alone button
		if (isset($_POST['delete']) && !defined('FUSION_NULL')) { // added token protection.
			$result = dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE post_id='".$_GET['post_id']."' AND thread_id='".$_GET['thread_id']."'");
			if (dbrows($result)) {
				$pdata = dbarray($result);
				// update postcount
				$result = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-1 WHERE user_id='".$pdata['post_author']."'");
				// delete the post
				$result = dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_id='".$_GET['post_id']."' AND thread_id='".$_GET['thread_id']."'");
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_postcount=forum_postcount-1 WHERE forum_id = '".$_GET['forum_id']."'");
				// remove all attachments
				$result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."'");
				if (dbrows($result)) {
					while ($attach = dbarray($result)) {
						unlink(FORUM."attachments/".$attach['attach_name']);
						$result2 = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."'");
					}
				}
				// if thread is blank, remove threads, remove all thread notify
				$posts = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".$_GET['thread_id']."'");
				if (!$posts) {
					$result = dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id='".$_GET['thread_id']."' AND forum_id='".$_GET['forum_id']."'");
					$result = dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".$_GET['thread_id']."'");
					$result = dbquery("UPDATE ".DB_FORUMS." SET forum_threadcount=forum_threadcount-1 WHERE forum_id = '".$_GET['forum_id']."'");
				}
				// set last post.
				$result = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."' AND forum_lastuser='".$pdata['post_author']."' AND forum_lastpost='".$pdata['post_datestamp']."'");
				if (dbrows($result)>0) {
					$result = dbquery("	SELECT p.forum_id, p.post_id, p.post_author, p.post_datestamp
									FROM ".DB_FORUM_POSTS." p
									LEFT JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
									WHERE p.forum_id='".$_GET['forum_id']."' AND thread_hidden='0' AND post_hidden='0'
									ORDER BY post_datestamp DESC LIMIT 1");
					if (dbrows($result)>0) {
						$pdata2 = dbarray($result);
						$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpostid='".$pdata2['post_id']."', forum_lastpost='".$pdata2['post_datestamp']."', forum_lastuser='".$pdata2['post_author']."' WHERE forum_id='".$_GET['forum_id']."'");
					} else {
						$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastuser='0' WHERE forum_id='".$_GET['forum_id']."'");
					}
				}
				if ($posts) {
					$result = dbcount("(thread_id)", DB_FORUM_THREADS, "thread_id='".$_GET['thread_id']."' AND thread_lastpostid='".$_GET['post_id']."' AND thread_lastuser='".$pdata['post_author']."'");
					if (!empty($result)) {
						$result = dbquery("SELECT thread_id, post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id='".$_GET['thread_id']."' AND post_hidden='0' ORDER BY post_datestamp DESC LIMIT 1");
						$pdata2 = dbarray($result);
						$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".$pdata2['post_datestamp']."', thread_lastpostid='".$pdata2['post_id']."', thread_postcount=thread_postcount-1, thread_lastuser='".$pdata2['post_author']."' WHERE thread_id='".$_GET['thread_id']."'");
					}
				}
				add_to_title($locale['global_201'].$locale['forum_0506']);
				//notify
				opentable($locale['forum_0506']);
				echo "<div style='text-align:center'><br />\n".$locale['forum_0546']."<br /><br />\n";
				if ($posts > 0) {
					echo "<a href='viewthread.php?thread_id=".$_GET['thread_id']."'>".$locale['forum_0548']."</a> ::\n";
				}
				echo "<a href='viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0549']."</a> ::\n";
				echo "<a href='index.php'>".$locale['forum_0550']."</a><br /><br />\n</div>\n";
				closetable();
			}
		} else {
			if ($post_edittime) {
				unset($data['post_datestamp']);
				unset($data['post_editreason']);
				unset($data['post_edituser']);
				unset($data['post_edittime']);
			}
			dbquery_insert(DB_FORUM_POSTS, $data, 'update', array('primary_key'=>'post_id'));
			if ($data['first_post'] == $_GET['post_id'] && $data['thread_subject'] != "") {
				$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_subject='".$data['thread_subject']."' WHERE thread_id='".$_GET['thread_id']."'");
			}
		}
	}

	// On Reply Post Execution
	if (isset($_POST['postreply']) && checkgroup($info['forum_reply'])) {
		if ($data['post_message']) {
			require_once INCLUDES."flood_include.php";
			if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) {
				if ($info['forum_merge'] && $data['thread_lastuser'] == $userdata['user_id']) {
					$mergeData = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_id DESC"));
					$data['post_message'] = $mergeData['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$data['post_message'];
					$data['post_edittime'] = time();
					$data['post_id'] = $mergeData['post_id'];
					$data['post_edituser'] = $userdata['user_id'];
					dbquery_insert(DB_FORUM_POSTS, $data, 'update', array('primary_key'=>'post_id'));
					$threadCount = "";
					$postCount = "";
				} else {
					dbquery_insert(DB_FORUM_POSTS, $data, 'save', array('primary_key'=>'post_id'));
					$data['post_id'] = dblastid();
					$result = (!defined("FUSION_NULL")) ? dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$userdata['user_id']."'") : '';
					$threadCount = "thread_postcount=thread_postcount+1,";
					$postCount = "forum_postcount=forum_postcount+1,";
				}
				// now we need an index to update every forum with the lastpost
				$forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
				$list_of_forums = get_all_parent($forum_index, $_GET['forum_id']);
				foreach($list_of_forums as $forum_id) {
					$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', ".$postCount." forum_lastpostid='".$data['post_id']."', forum_lastuser='".$userdata['user_id']."' WHERE forum_id='".$forum_id."'");
				}
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', ".$postCount." forum_lastpostid='".$data['post_id']."', forum_lastuser='".$userdata['user_id']."' WHERE forum_id='".$_GET['forum_id']."'");
				$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$data['post_id']."', ".$threadCount." thread_lastuser='".$userdata['user_id']."' WHERE thread_id='".$_GET['thread_id']."'");
				if ($settings['thread_notify'] && isset($_POST['notify_me']) && !defined('FUSION_NULL')) {
					if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'")) {
						$result = dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$_GET['thread_id']."', '".time()."', '".$userdata['user_id']."', '1')");
					}
				}
			} else {
				// flood control error.
				$defender->stop();
				$defender->addNotice('Flood control nice message.');
			}
		} else {
			print_p('there are no post messages');
		}
	}

	// this need to drop..
	elseif (isset($_POST['add_poll_option'])) {
		//$is_mod = iMOD && iUSER < "102" ? true : false;
		if (isset($_POST['add_poll_option'])) {
			if (count($data['poll_opts'])) {
				array_push($data['poll_opts'], '');
			}
		}
	}

	// On Preview Execution
	elseif (isset($_POST['previewpost'])) {

		// Prepare Preview Data
		if (!$data['post_message']) {
			$data['preview_message'] = $locale['forum_0520'];
		} else {
			$data['preview_message'] = $data['post_message'];
			if ($data['post_showsig']) {
				$data['preview_message'] .= "\n\n".$userdata['user_sig'];
			}
			if (isset($data['post_smileys'])) {
				$data['preview_message'] = parsesmileys($data['preview_message']);
			}
			$data['preview_message'] = nl2br(parseubb($data['preview_message']));
		}
		post_preview($data);
	}

	//print_p($data);
	/* -----------------
	|  Attachments Validation
	+ ------------------*/
	$_attach = array();
	foreach ($_POST as $key => $value) {
		if (!strstr($key, "delete_attach")) continue;
		$key = str_replace("delete_attach_", "", $key);
		$result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
		if (dbrows($result) != 0 && $value) {
			$adata = dbarray($result);
			unlink(FORUM."attachments/".$adata['attach_name']);
			$result = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
		}
	}
	// @todo: port to defender.
	// Require POST_ID, fulfilled at this position.
	if ($can_attach) {
		$i = count($_FILES)-1;
		$file_type = 0;
		foreach($_FILES as $attach) {
			if (!empty($attach['name'])) {
				if (isset($attach['type']) && in_array($attach['type'], img_mimeTypes())) {
					// IMAGE UPLOAD Conditions
					$file_type = 1;
					$upload = upload_image("file_".$i."", '', FORUM."attachments/", $settings['photo_max_w'], $settings['photo_max_h'], $settings['photo_max_b'], FALSE, TRUE, FALSE, 0, FORUM."attachments/thumbs", '', $settings['news_thumb_w'], $settings['news_thumb_h']);
				} else {
					$file_type = 2;
					// FILE UPLOAD Conditions
					$upload = upload_file("file_".$i."", '', FORUM."attachments/", $settings['attachtypes'], $settings['attachmax']);
				}
				if ($upload['error']) {
					$defender->stop();
					if ($file_type == '1') {
						if ($upload['error'] == 1) {
							$defender->addNotice("Photo Max size exceeded. You can only upload up to ".($settings['photo_max_b']/1000)." mb");
						} elseif ($upload['error'] == 2) {
							$defender->addNotice('2');
						} elseif ($upload['error'] == 3) {
							$defender->addNotice('3');
						} elseif ($upload['error'] == 4) {
							$defender->addNotice('4');
						} elseif ($upload['error'] == 5) {
							$defender->addNotice('Image not uploaded');
						}
					} elseif ($file_type =='2') {
						if ($upload['error'] == 1) {
							$defender->addNotice('');
						} elseif ($upload['error'] == 2) {
							$defender->addNotice('');
						} elseif ($upload['error'] == 3) {
							$defender->addNotice('');
						} elseif ($upload['error'] == 4) {
							$defender->addNotice('');
						}
					}
				} else {
					// upload success!
					$_attach['thread_id'] = $data['thread_id'];
					$_attach['post_id'] = $data['post_id'];
					if ($file_type == 1) {
						$_attach['attach_name'] = $upload['image_name'];
						$_attach['attach_mime'] = $attach['type'];
					} elseif ($file_type == 2) {
						$_attach['attach_name'] = $upload['target_file'];
						$_attach['attach_mime'] = $attach['type'];
					}
					dbquery_insert(DB_FORUM_ATTACHMENTS, $_attach, 'save', array('noredirect'=>1));
				}
			}
			$i--;
		}
	}

	/* -------------------
	| Handle Debugs
	+ ---------------------*/
	if ($debug2 or $debug) {
		if (isset($_POST['flush_post'])) {
			$result = dbquery("DELETE FROM ".DB_FORUM_POSTS."");
			$a1 = mysql_affected_rows()-1;
			echo "<div class='alert alert-info'>".$a1." Posts deleted</div>\n";
		}

		if (isset($_POST['flush_thread'])) {
			$result = dbquery("DELETE FROM ".DB_FORUM_THREADS."");
			$a2 = mysql_affected_rows()-1;
			echo "<div class='alert alert-info'>".$a2." Threads deleted</div>\n";
		}

		if (isset($_POST['flush_attach'])) {
			$result = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS."");
			$a2 = mysql_affected_rows()-1;
			echo "<div class='alert alert-info'>".$a2." Attachments deleted</div>\n";
		}

		if (isset($_POST['flush_poll'])) {
			$result = dbquery("DELETE FROM ".DB_FORUM_POLLS."");
			$a2 = mysql_affected_rows()-1;
			echo "<div class='alert alert-info'>".$a2." Polls deleted</div>\n";
			$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS."");
			$a2 = mysql_affected_rows()-1;
			echo "<div class='alert alert-info'>".$a2." Polls Votes deleted</div>\n";
			$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS."");
			$a2 = mysql_affected_rows()-1;
			echo "<div class='alert alert-info'>".$a2." Polls Options deleted</div>\n";
		}

		echo "<div class='well'>\n";
		echo "<p class='strong'><i class='entypo attention'></i> Wipe records for Developer Use </p>";
		echo openform('flushform', 'post', FUSION_REQUEST, array('max_tokens' => 1));
		echo form_button('flush_post', 'Flush Post', 'flush_post', array('class'=>'btn-sm btn-default m-r-10'));
		echo form_button('flush_thread', 'Flush Threads', 'flush_thread', array('class'=>'btn-sm btn-default m-r-10'));
		echo form_button('flush_attach', 'Flush Attachments', 'flush_attach', array('class'=>'btn-sm btn-default m-r-10'));
		echo form_button('flush_poll', 'Flush Poll', 'flush_poll', array('class'=>'btn-sm btn-default m-r-10'));
		echo form_hidden('', $executable, $executable, '1'); // mimic executable.
		echo closeform();
		echo "</div>\n";

		echo "<div class='alert alert-info'>\n";
		echo "<strong>Current Mode is --- ";
		if ($data['edit']) {
			echo "EDIT MODE";
		} elseif ($data['reply']) {
			echo "REPLY MODE";
		} elseif ($data['new']) {
			echo "POST NEW THREAD MODE.";
		}
		echo "</strong>\n";
		echo "</div>\n";
	}
	if ($debug) {
		if (defined('FUSION_NULL')) {
			echo "<div class='alert alert-danger'>\n";
			echo "<strong>FUSION_NULL</strong> is being declared. No post have been executed.";
			echo "</div>\n";
		}

		echo "<div class='alert alert-success'>\n";
		echo '<p><strong>The $_POST raw data dump. You posted the following in this click.</strong></p><hr>';
		print_p($_POST);
		echo "</div>\n";
		echo "<div class='alert alert-success'>\n";
		echo '<p><strong>The $data raw data dump for dbquery_insert() function</strong></p><hr>';
		print_p($data);
		echo "</div>\n";
		echo "<div class='alert alert-success'>\n";
		echo '<p><strong>The $_attach raw data dump to DB_FORUM_ATTACH</strong></p><hr>';
		print_p($_attach);
		echo "</div>\n";
	}

	/* -------------------
	| Handle Redirection.
	+ ---------------------*/
	if (!defined('FUSION_NULL') && !$debug && !$debug2) {
		if ($data['reply']) {
			redirect("postify.php?post=reply&error=$error&amp;forum_id=".intval($_GET['forum_id'])."&amp;thread_id=".intval($_GET['thread_id'])."&amp;post_id=".intval($data['post_id']));
		} elseif ($data['edit']) {
			redirect("postify.php?post=edit&error=$error&amp;forum_id=".intval($_GET['forum_id'])."&amp;thread_id=".intval($_GET['thread_id'])."&amp;post_id=".intval($_GET['post_id']));
		} elseif ($data['new']) {
			// get parent id and branch id.
			$forum_data = dbarray(dbquery("SELECT forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id='".intval($data['forum_id'])."'"));
			redirect("postify.php?post=new&error=$error&amp;forum_id=".intval($data['forum_id'])."&amp;parent_id=".intval($forum_data['forum_cat'])."&amp;thread_id=".intval($data['thread_id'].""));
		}
	}
} else {
	if ($data['new'] or $data['reply'] or $data['edit']) {
	} else {
		if (!$executable) throw new \Exception('$data new, reply or edit was not found');
	}
}

?>
