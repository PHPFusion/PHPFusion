<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: postedit.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (isset($_POST['previewchanges']) || isset($_POST['delete_poll']) || isset($_POST['update_poll_title']) || isset($_POST['update_poll_option']) || isset($_POST['delete_poll_option']) || isset($_POST['add_poll_option'])) {
	$message = trim(stripinput(censorwords($_POST['message'])));
	$subject = isset($_POST['subject']) ? trim(stripinput(censorwords($_POST['subject']))) : $tdata['thread_subject'];
	$disable_smileys_check = isset($_POST['disable_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? " checked='checked'" : "";
	$sig_checked = isset($_POST['post_showsig']) ? " checked='checked'" : "";
	$del_check = isset($_POST['delete']) ? " checked='checked'" : "";
	$del_attach_check = isset($_POST['delete_attach']) ? " checked='checked'" : "";
	$poll_opts = array();
	$edit_reason = trim(stripinput(censorwords($_POST['edit_reason'])));
	$post_locked = (isset($_POST['post_locked']) && $_POST['post_locked'] == 1 ? 1 : 0);
	if ($fdata['forum_poll'] && checkgroup($fdata['forum_poll'])) {
		if ($tdata['thread_poll'] == 1 && ($pdata['post_author'] == $tdata['thread_author']) && ($userdata['user_id'] == $tdata['thread_author'] || iSUPERADMIN || iMOD)) {
			$poll_title = trim(stripinput(censorwords($_POST['poll_title'])));
			if (isset($_POST['update_poll_title'])) {
				$result = dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_title='$poll_title' WHERE thread_id='".$_GET['thread_id']."'");
			} elseif (isset($_POST['delete_poll'])) {
				$result = dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$_GET['thread_id']."'");
				$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."'");
				$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$_GET['thread_id']."'");
				$result = dbquery("UPDATE ".DB_THREADS." SET thread_poll='0' WHERE thread_id='".$_GET['thread_id']."'");
				$fdata['forum_poll'] = 0;
			}
			if (isset($_POST['poll_options']) && is_array($_POST['poll_options'])) {
				$i = 1;
				foreach ($_POST['poll_options'] as $poll_option) {
					if (isset($_POST['delete_poll_option'][$i])) {
						$data = dbarray(dbquery("SELECT forum_poll_option_votes FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$i."'"));
						$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$i."'");
						$result = dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_id=forum_poll_option_id-1 WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id>'".$i."'");
						$result = dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_votes=forum_poll_votes-".$data['forum_poll_option_votes']." WHERE thread_id='".$_GET['thread_id']."'");
					} elseif (isset($_POST['add_poll_option'][$i])) {
						if (trim($poll_option)) {
							$poll_opts[] = trim(stripinput($poll_option));
							$result = dbquery("INSERT INTO ".DB_FORUM_POLL_OPTIONS." (thread_id, forum_poll_option_id, forum_poll_option_text, forum_poll_option_votes) VALUES('".$_GET['thread_id']."', '".$i."', '".trim(stripinput($poll_option))."', '0')");
						}
					} elseif (isset($_POST['update_poll_option'][$i])) {
						if (trim($poll_option)) {
							$poll_opts[] = trim(stripinput($poll_option));
							$result = dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_text='".trim(stripinput($poll_option))."' WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$i."'");
						}
					} else {
						if (trim($poll_option)) { $poll_opts[] = trim(stripinput($poll_option)); }
					}
					$i++;
				}
			} else {
				$poll_opts = array();
			}
		}
	}

	if (isset($_POST['previewchanges'])) {
		if ($message == "") {
			$previewmessage = $locale['421'];
		} else {
			$previewmessage = $message;
			if (!$disable_smileys_check) { $previewmessage = parsesmileys($previewmessage); }
			$previewmessage = parseubb($previewmessage);
			$previewmessage = nl2br($previewmessage);
		}
		$udata = dbarray(dbquery("SELECT user_id, user_name, user_status, user_avatar, user_level, user_posts, user_joined FROM ".DB_USERS." WHERE user_id='".$pdata['post_author']."'"));
		add_to_title($locale['global_201'].$locale['405']);
		opentable($locale['405']);
		echo "<div class='tbl2 forum_breadcrumbs' style='margin-bottom:5px'><a href='index.php'>".$settings['sitename']."</a> &raquo; ".$caption."</div>\n";

		if ($fdata['forum_poll'] && checkgroup($fdata['forum_poll'])) {
			if ($tdata['thread_poll'] == 1 && ($pdata['post_author'] == $tdata['thread_author']) && ($userdata['user_id'] == $tdata['thread_author'] || iSUPERADMIN || iMOD)) {
				if ((isset($poll_title) && $poll_title != "") && (isset($poll_opts) && is_array($poll_opts))) {
					echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border' style='margin-bottom:5px'>\n<tr>\n";
					echo "<td align='center' class='tbl2'><strong>".$poll_title."</strong></td>\n</tr>\n<tr>\n<td class='tbl1'>\n";
					echo "<table align='center' cellpadding='0' cellspacing='0'>\n";
					foreach ($poll_opts as $poll_option) {
						echo "<tr>\n<td class='tbl1'><input type='radio' name='poll_option' value='$i' style='vertical-align:middle;' /> ".$poll_option."</td>\n</tr>\n";
						$i++;
					}
					echo "</table>\n</td>\n</tr>\n</table>\n";
				}
			}
		}
		echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border forum_thread_table'>\n<tr>\n";
		echo "<td colspan='2' class='tbl2'><strong>".$subject."</strong></td>\n</tr>\n";
		echo "<tr>\n<td class='tbl2 forum_thread_user_name' style='width:140px;'>".profile_link($udata['user_id'], $udata['user_name'], $udata['user_status'])."</td>\n";
		echo "<td class='tbl2 forum_thread_post_date'>".$locale['426'].showdate("forumdate", time())."</td>\n";
		echo "</tr>\n<tr>\n<td valign='top' width='140' class='tbl2 forum_thread_user_info'>\n";
		if ($udata['user_avatar'] != "" && file_exists(IMAGES."avatars/".$udata['user_avatar']) && is_file(IMAGES."avatars/".$udata['user_avatar'])) {
			echo "<img src='".IMAGES."avatars/".$udata['user_avatar']."' alt='' /><br /><br />\n";
		}
		echo "<span class='small'>".getuserlevel($udata['user_level'])."</span><br /><br />\n";
		echo "<span class='small'><strong>".$locale['423']."</strong> ".$udata['user_posts']."</span><br />\n";
		echo "<span class='small'><strong>".$locale['425']."</strong> ".showdate("shortdate", $udata['user_joined'])."</span><br />\n";
		echo "<br /></td>\n<td valign='top' class='tbl1 forum_thread_user_post'>".$previewmessage;
		echo "<hr />\n".$locale['427'].profile_link($userdata['user_id'], $userdata['user_name'], $userdata['user_status'])."".$locale['428'].showdate("forumdate", time())."</td>\n";
		echo "</tr>\n</table>\n";
		closetable();
	}
}
if (isset($_POST['savechanges'])) {
	if (isset($_POST['delete'])) {
		$result = dbquery("SELECT post_author FROM ".DB_POSTS." WHERE post_id='".$_GET['post_id']."' AND thread_id='".$_GET['thread_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$result = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-1 WHERE user_id='".$data['post_author']."'");
			$result = dbquery("DELETE FROM ".DB_POSTS." WHERE post_id='".$_GET['post_id']."' AND thread_id='".$_GET['thread_id']."'");
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_postcount=forum_postcount-1 WHERE forum_id = '".$_GET['forum_id']."'");
			$result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."'");
			if (dbrows($result)) {
				while ($attach = dbarray($result)) {
					unlink(FORUM."attachments/".$attach['attach_name']);
					$result2 = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."'");
				}
			}
			$posts = dbcount("(post_id)", DB_POSTS, "thread_id='".$_GET['thread_id']."'");
			if (!$posts) {
				$result = dbquery("DELETE FROM ".DB_THREADS." WHERE thread_id='".$_GET['thread_id']."' AND forum_id='".$_GET['forum_id']."'");
				$result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$_GET['thread_id']."'");
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_threadcount=forum_threadcount-1 WHERE forum_id = '".$_GET['forum_id']."'");
			}
			$result = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."' AND forum_lastuser='".$pdata['post_author']."' AND forum_lastpost='".$pdata['post_datestamp']."'");
			if (dbrows($result)) {
				$result = dbquery("	SELECT p.forum_id, p.post_author, p.post_datestamp
									FROM ".DB_POSTS." p
									LEFT JOIN ".DB_THREADS." t ON p.thread_id=t.thread_id
									WHERE p.forum_id='".$_GET['forum_id']."' AND thread_hidden='0' AND post_hidden='0'
									ORDER BY post_datestamp DESC LIMIT 1");
				if (dbrows($result)) {
					$pdata2 = dbarray($result);
					$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$pdata2['post_datestamp']."', forum_lastuser='".$pdata2['post_author']."' WHERE forum_id='".$_GET['forum_id']."'");
				} else {
					$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastuser='0' WHERE forum_id='".$_GET['forum_id']."'");
				}
			}
			if ($posts) {
				$result = dbcount("(thread_id)", DB_THREADS, "thread_id='".$_GET['thread_id']."' AND thread_lastpostid='".$_GET['post_id']."' AND thread_lastuser='".$pdata['post_author']."'");
				if (!empty($result)) {
					$result = dbquery("SELECT thread_id, post_id, post_author, post_datestamp FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' AND post_hidden='0' ORDER BY post_datestamp DESC LIMIT 1");
					$pdata2 = dbarray($result);
					$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$pdata2['post_datestamp']."', thread_lastpostid='".$pdata2['post_id']."', thread_postcount=thread_postcount-1, thread_lastuser='".$pdata2['post_author']."' WHERE thread_id='".$_GET['thread_id']."'");
				}
			}
			add_to_title($locale['global_201'].$locale['407']);
			opentable($locale['407']);
			echo "<div style='text-align:center'><br />\n".$locale['445']."<br /><br />\n";
			if ($posts > 0) { echo "<a href='viewthread.php?thread_id=".$_GET['thread_id']."'>".$locale['447']."</a> ::\n"; }
			echo "<a href='viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['448']."</a> ::\n";
			echo "<a href='index.php'>".$locale['449']."</a><br /><br />\n</div>\n";
			closetable();
		}
	} else {
		$error = 0;
		if ($pdata['first_post'] == $_GET['post_id']) {
			$subject = trim(stripinput(censorwords($_POST['subject'])));
		}
		$message = trim(stripinput(censorwords($_POST['message'])));
		$smileys = isset($_POST['disable_smileys'])|| preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? "0" : "1";
		$updateSig = (isset($_POST['post_showsig']) && $_POST['post_showsig'] == 1 ? 1 : 0);
		$post_locked = (isset($_POST['post_locked']) && $_POST['post_locked'] == 1 ? 1 : 0);
		if (iMEMBER) {
			if ($message != "") {
				if (isset($_POST['hide_edit'])) {
					$post_edit_time = 0;
					$reason = "";
				} else {
					$thread_lastpost = dbarray(dbquery("SELECT post_id FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
					if ($thread_lastpost['post_id'] == $_GET['post_id'] && time() - $pdata['post_datestamp'] < 5*60) {
						$post_edit_time = 0;
						$reason = "";
					} elseif ($settings['forum_editpost_to_lastpost']) {
						$post_edit_time = time();
						$reason = trim(stripinput(censorwords($_POST['edit_reason'])));
						$lastPost = dbcount("(thread_id)", DB_THREADS, "thread_lastpostid='".$_GET['post_id']."'");
						if ($lastPost > 0) {
							$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$post_edit_time."' WHERE thread_id='".$_GET['thread_id']."'");
						}
						$forum_lastpost = dbarray(dbquery("SELECT post_id FROM ".DB_POSTS." WHERE forum_id='".$_GET['forum_id']."' ORDER BY post_id DESC LIMIT 1"));
						if ($forum_lastpost['post_id'] == $_GET['post_id']) {
							$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$post_edit_time."' WHERE forum_id='".$_GET['forum_id']."'");
						}
					} else {
						$post_edit_time = time();
						$reason = trim(stripinput(censorwords($_POST['edit_reason'])));
					}
				}

				$result = dbquery(
					"UPDATE ".DB_POSTS." SET
						post_message='".$message."',
						post_showsig='".$updateSig."',
						post_smileys='".$smileys."',
						post_edituser='".$userdata['user_id']."',
						post_edittime='".$post_edit_time."',
						post_editreason='".$reason."',
						post_locked='".$post_locked."'
					WHERE post_id='".$_GET['post_id']."'"
				);

				if ($pdata['first_post'] == $_GET['post_id'] && $subject != "") {
					$result = dbquery("UPDATE ".DB_THREADS." SET thread_subject='".$subject."' WHERE thread_id='".$_GET['thread_id']."'");
				}

				foreach ($_POST as $key=>$value){
					if(!strstr($key, "delete_attach")) continue;
					$key = str_replace("delete_attach_", "", $key);
					$result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
					if (dbrows($result) != 0 && $value) {
						$adata = dbarray($result);
						unlink(FORUM."attachments/".$adata['attach_name']);
						$result = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
					}
				}
				if ($fdata['forum_attach'] && checkgroup($fdata['forum_attach'])) {
					foreach($_FILES as $attach){
						if ($attach['name'] != "" && !empty($attach['name']) && is_uploaded_file($attach['tmp_name'])) {
							$attachname = stripfilename(substr($attach['name'], 0, strrpos($attach['name'], ".")));
							$attachext = strtolower(strrchr($attach['name'],"."));
							if (preg_match("/^[-0-9A-Z_\[\]]+$/i", $attachname) && $attach['size'] <= $settings['attachmax']) {
								$attachtypes = explode(",", $settings['attachtypes']);
								if (in_array($attachext, $attachtypes)) {
									$attachname .= $attachext;
									$attachname = attach_exists(strtolower($attachname));
									move_uploaded_file($attach['tmp_name'], FORUM."attachments/".$attachname);
									chmod(FORUM."attachments/".$attachname,0644);
									if (in_array($attachext, $imagetypes) && (!@getimagesize(FORUM."attachments/".$attachname) || !@verify_image(FORUM."attachments/".$attachname))) {
										unlink(FORUM."attachments/".$attachname);
										$error = 1;
									}
									if (!$error) $result = dbquery("INSERT INTO ".DB_FORUM_ATTACHMENTS." (thread_id, post_id, attach_name, attach_ext, attach_size) VALUES ('".$_GET['thread_id']."', '".$_GET['post_id']."', '".$attachname."', '".$attachext."', '".$attach['size']."')");
								} else {
									@unlink($attach['tmp_name']);
									$error = 1;
								}
							} else {
								@unlink($attach['tmp_name']);
								$error = 2;
							}
						}
					}
				}
			} else {
				$error = 3;
			}
		} else {
			$error = 4;
		}
		redirect("postify.php?post=edit&error=$error&forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."&post_id=".$_GET['post_id']);
	}
} else {
	if (!isset($_POST['previewchanges']) && !isset($_POST['update_poll_title']) && !isset($_POST['update_poll_option']) && !isset($_POST['delete_poll_option']) && !isset($_POST['add_poll_option'])) {
		$subject = $pdata['thread_subject'];
		$message = $pdata['post_message'];
		$edit_reason = $pdata['post_editreason'];
		$disable_smileys_check = ($pdata['post_smileys'] == "0" ? " checked='checked'" : "");
		$sig_checked = ($pdata['post_showsig'] ? " checked='checked'" : "");
		$post_locked = ($pdata['post_locked'] ? " checked='checked'" : "");
		$del_check = "";
		if ($pdata['post_author'] == $tdata['thread_author'] && $tdata['thread_poll'] == 1) {
			$result = dbquery("SELECT * FROM ".DB_FORUM_POLLS." WHERE thread_id='".$_GET['thread_id']."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				$poll_title = $data['forum_poll_title'];
				$result = dbquery("SELECT forum_poll_option_text FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY forum_poll_option_id ASC");
				while ($data = dbarray($result)) {
					$poll_opts[] = $data['forum_poll_option_text'];
				}
			}
		}
	}
	opentable($locale['408']);
	if (!isset($_POST['previewchanges'])) echo "<div class='tbl2 forum_breadcrumbs' style='margin-bottom:5px'><a href='index.php'>".$settings['sitename']."</a> &raquo; ".$caption."</div>\n";

	echo "<form name='inputform' method='post' action='".FUSION_SELF."?action=edit&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id']."' enctype='multipart/form-data'>\n";
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
	if ($pdata['first_post'] == $_GET['post_id']) {
		echo "<td width='145' class='tbl2'>".$locale['460']."</td>\n";
		echo "<td class='tbl2'><input type='text' name='subject' value='".$subject."' class='textbox' maxlength='255' style='width:250px' /></td>\n";
		echo "</tr>\n<tr>\n";
	}
	echo "<td valign='top' width='145' class='tbl2'>".$locale['461']."</td>\n";
	echo "<td class='tbl1'><textarea name='message' cols='60' rows='15' class='textbox' style='width:98%'>".$message."</textarea></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='145' class='tbl2'>&nbsp;</td>\n";
	echo "<td class='tbl1'>".display_bbcodes("99%", "message")."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='145' class='tbl2'>".$locale['474']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='edit_reason' class='textbox' style='width:250px;' value='".$edit_reason."' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td valign='top' width='145' class='tbl2'>".$locale['463']."</td>\n";
	echo "<td class='tbl1'>\n";
	echo "<label><input type='checkbox' name='disable_smileys' value='1'".$disable_smileys_check." /> ".$locale['482']."</label><br />\n";
	if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
		echo "<label><input type='checkbox' name='post_showsig' value='1'".$sig_checked." /> ".$locale['483']."</label><br />\n";
	}
	if(iMOD || iADMIN) echo "<label><input type='checkbox' name='hide_edit' value='1' /> ".$locale['487']."</label><br />\n";
	if(iMOD || iADMIN) echo "<label><input type='checkbox' name='post_locked' value='1'".$post_locked." /> ".$locale['488']."</label><br />\n";
	echo "<label><input type='checkbox' name='delete' value='1'".$del_check." /> ".$locale['484']."</label>\n";
	echo "</td>\n</tr>\n";
	if ($fdata['forum_attach'] && checkgroup($fdata['forum_attach'])) {
		add_to_head("<script type='text/javascript' src='".INCLUDES."multi_attachment.js'></script>\n");
		echo "<tr>\n<td valign='top' width='145' class='tbl2'>".$locale['464']."</td>\n<td class='tbl1'>\n";

		$result = dbquery("SELECT attach_id, attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$_GET['post_id']."'");
		$counter = 0;
		if (dbrows($result)) {
			while($adata = dbarray($result)){
				if($counter > 0) echo "<br />\n";
				echo "<label><input type='checkbox' name='delete_attach_".$adata['attach_id']."' value='1' /> ".$locale['485']."</label>\n";
				echo "<a href='".FORUM."attachments/".$adata['attach_name']."'>".$adata['attach_name']."</a> [".parsebytesize(filesize(FORUM."attachments/".$adata['attach_name']))."]\n";
				$counter++;
			}
		echo "<br /><br />\n";
		}
		$max = ($settings['attachmax_count'] - $counter <= 0 ? "-2" : $settings['attachmax_count'] - $counter);

		echo "<input id='my_file_element' type='file' name='file_1' style='width:200px;' class='textbox' /><br />\n";
		echo "<span class='small2'>".sprintf($locale['466'], parsebytesize($settings['attachmax']), str_replace(',', ' ', $settings['attachtypes']), $settings['attachmax_count'])."</span>\n";
		echo "<div id='files_list'></div>\n";
		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->\n";
		echo "var multi_selector = new MultiSelector( document.getElementById( \"files_list\" ), ".$max." );\n";
		echo "<!-- Pass in the file element -->\n";
		echo "multi_selector.addElement( document.getElementById( \"my_file_element\" ) );\n";
		echo "/* ]]>*/\n";
		echo "</script>\n";
		echo "</td>\n</tr>\n";
	}

	if ($fdata['forum_poll'] && checkgroup($fdata['forum_poll'])) {
		if ($tdata['thread_poll'] && ($pdata['post_author'] == $tdata['thread_author']) && ($userdata['user_id'] == $tdata['thread_author'] || iSUPERADMIN || iMOD)) {
			echo "<tr>\n<td align='center' colspan='2' class='tbl2'>".$locale['468']."</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='145' class='tbl2'>".$locale['469']."</td>\n";
			echo "<td class='tbl1'><input type='text' name='poll_title' value='".$poll_title."' class='textbox' maxlength='255' style='width:150px' />\n";
			echo "<input type='submit' name='update_poll_title' value='".$locale['472']."' class='button' />\n";
			echo "<input type='submit' name='delete_poll' value='".$locale['473']."' class='button' />\n</td>\n</tr>\n";
			$i = 1;
			if (isset($poll_opts) && is_array($poll_opts)) {
				foreach ($poll_opts as $poll_option) {
					echo "<tr>\n<td width='145' class='tbl2'>".$locale['470']." ".$i."</td>\n";
					echo "<td class='tbl1'><input type='text' name='poll_options[$i]' value='".$poll_option."' class='textbox' maxlength='255' style='width:150px' />\n";
					echo "<input type='submit' name='update_poll_option[$i]' value='".$locale['472']."' class='button' />\n";
					echo "<input type='submit' name='delete_poll_option[$i]' value='".$locale['473']."' class='button' />\n</td>\n</tr>\n";
					$i++;
				}
				echo "<tr>\n<td width='145' class='tbl2'>".$locale['470']." ".$i."</td>\n";
				echo "<td class='tbl1'><input type='text' name='poll_options[$i]' class='textbox' maxlength='255' style='width:150px' />\n";
				echo "<input type='submit' name='add_poll_option[$i]' value='".$locale['471']."' class='button' /></td>\n</tr>\n";
			} else {
				echo "<tr>\n<td width='145' class='tbl2'>".$locale['470']."</td>\n";
				echo "<td class='tbl1'><input type='text' name='poll_options[$i]' class='textbox' maxlength='255' style='width:150px' />\n";
				echo "<input type='submit' name='add_poll_option[$i]' value='".$locale['471']."' class='button' /></td>\n</tr>\n";
			}
		}
	}

	echo "<tr>\n<td align='center' colspan='2' class='tbl1'>\n";
	echo "<input type='submit' name='previewchanges' value='".$locale['405']."' class='button' />\n";
	echo "<input type='submit' name='savechanges' value='".$locale['409']."' class='button' />\n";
	echo "</td>\n</tr>\n</table>\n</form>\n";
	closetable();
}
?>