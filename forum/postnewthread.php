<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: postnewthread.php
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

if (isset($_POST['previewpost']) || isset($_POST['add_poll_option'])) {
	$subject = trim(stripinput(censorwords($_POST['subject'])));
	$message = trim(stripinput(censorwords($_POST['message'])));
	$sticky_thread_check = isset($_POST['sticky_thread']) ? " checked='checked'" : "";
	$lock_thread_check = isset($_POST['lock_thread']) ? " checked='checked'" : "";
	$sig_checked = isset($_POST['show_sig']) ? " checked='checked'" : "";
	$disable_smileys_check = isset($_POST['disable_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? " checked='checked'" : "";
	if ($settings['thread_notify']) { $notify_checked = isset($_POST['notify_me']) ? " checked='checked'" : ""; }

	if ($fdata['forum_poll'] && checkgroup($fdata['forum_poll'])) {
		$poll_title = trim(stripinput(censorwords($_POST['poll_title'])));
		if (isset($_POST['poll_options']) && is_array($_POST['poll_options'])) {
			$poll_opts = array();
			foreach ($_POST['poll_options'] as $poll_option) {
				if ($poll_option) { $poll_opts[] = stripinput($poll_option); }
			}
		} else {
			$poll_opts = array();
		}
		if (isset($_POST['add_poll_option'])) {
			if (count($poll_opts)) { array_push($poll_opts, ""); }
		}
	}

	if (isset($_POST['previewpost'])) {
		if ($subject == "") { $subject = $locale['420']; }
		if ($message == "") {
			$previewmessage = $locale['421'];
		} else {
			$previewmessage = $message;
			if ($sig_checked) { $previewmessage = $previewmessage."\n\n".$userdata['user_sig']; }
			if (!$disable_smileys_check) { $previewmessage = parsesmileys($previewmessage); }
			$previewmessage = parseubb($previewmessage);
			$previewmessage = nl2br($previewmessage);
		}
		//$is_mod = iMOD && iUSER < "102" ? true : false;
		opentable($locale['400']);
		echo "<div class='tbl2 forum_breadcrumbs' style='margin-bottom:5px'><a href='index.php'>".$settings['sitename']."</a> &raquo; ".$caption."</div>\n";

		if ($fdata['forum_poll'] && checkgroup($fdata['forum_poll'])) {
			if ((isset($poll_title) && $poll_title) && (isset($poll_opts) && is_array($poll_opts))) {
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
		echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border forum_thread_table'>\n<tr>\n";
		echo "<td colspan='2' class='tbl2'><strong>".$subject."</strong></td>\n</tr>\n";
		echo "<tr>\n<td class='tbl2 forum_thread_user_name' style='width:140px;'>".profile_link($userdata['user_id'], $userdata['user_name'], $userdata['user_status'])."</td>\n";
		echo "<td class='tbl2 forum_thread_post_date'>".$locale['426'].showdate("forumdate", time())."</td>\n";
		echo "</tr>\n<tr>\n<td valign='top' width='140' class='tbl2 forum_thread_user_info'>\n";
		if ($userdata['user_avatar'] && file_exists(IMAGES."avatars/".$userdata['user_avatar'])) {
			echo "<img src='".IMAGES."avatars/".$userdata['user_avatar']."' alt='' /><br /><br />\n";
		}
		echo "<span class='small'>".getuserlevel($userdata['user_level'])."</span><br /><br />\n";
		echo "<span class='small'><strong>".$locale['423']."</strong> ".$userdata['user_posts']."</span><br />\n";
		echo "<span class='small'><strong>".$locale['425']."</strong> ".showdate("shortdate", $userdata['user_joined'])."</span><br />\n";
		echo "<br /></td>\n<td valign='top' class='tbl1 forum_thread_user_post'>".$previewmessage."</td>\n";
		echo "</tr>\n</table>\n";
		closetable();
	}
}
if (isset($_POST['postnewthread'])) {
	$subject = trim(stripinput(censorwords($_POST['subject'])));
	$message = trim(stripinput(censorwords($_POST['message'])));
	$flood = false; $error = 0;
	$sticky_thread = isset($_POST['sticky_thread']) && (iMOD || iSUPERADMIN) ? 1 : 0;
	$lock_thread = isset($_POST['lock_thread']) && (iMOD || iSUPERADMIN) ? 1 : 0;
	$sig = isset($_POST['show_sig']) ? 1 : 0;
	$smileys = isset($_POST['disable_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? 0 : 1;
	$thread_poll = 0;
	$poll_opts = array();

	if ($fdata['forum_poll'] && checkgroup($fdata['forum_poll'])) {
		if (isset($_POST['poll_options']) && is_array($_POST['poll_options'])) {
			foreach ($_POST['poll_options'] as $poll_option) {
				if (trim($poll_option)) { $poll_opts[] = trim(stripinput(censorwords($poll_option))); }
				unset($poll_option);
			}
		}
		$thread_poll = (trim($_POST['poll_title']) && (isset($poll_opts) && is_array($poll_opts)) ? 1 : 0);
	}

	if (iMEMBER) {
		if ($subject != "" && $message != "") {
			require_once INCLUDES."flood_include.php";
			if (!flood_control("post_datestamp", DB_POSTS, "post_author='".$userdata['user_id']."'")) {
				$result = dbquery("INSERT INTO ".DB_THREADS." (forum_id, thread_subject, thread_author, thread_views, thread_lastpost, thread_lastpostid, thread_lastuser, thread_postcount, thread_poll, thread_sticky, thread_locked) VALUES('".$_GET['forum_id']."', '$subject', '".$userdata['user_id']."', '0', '".time()."', '0', '".$userdata['user_id']."', '1', '".$thread_poll."', '".$sticky_thread."', '".$lock_thread."')");
				$thread_id = mysql_insert_id();
				$result = dbquery("INSERT INTO ".DB_POSTS." (forum_id, thread_id, post_message, post_showsig, post_smileys, post_author, post_datestamp, post_ip, post_ip_type, post_edituser, post_edittime, post_editreason) VALUES ('".$_GET['forum_id']."', '".$thread_id."', '".$message."', '".$sig."', '".$smileys."', '".$userdata['user_id']."', '".time()."', '".USER_IP."', '".USER_IP_TYPE."', '0', '0', '')");
				$post_id = mysql_insert_id();
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastuser='".$userdata['user_id']."' WHERE forum_id='".$_GET['forum_id']."'");
				$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpostid='".$post_id."' WHERE thread_id='".$thread_id."'");
				$result = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$userdata['user_id']."'");
				if ($settings['thread_notify'] && isset($_POST['notify_me'])) { $result = dbquery("INSERT INTO ".DB_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$thread_id."', '".time()."', '".$userdata['user_id']."', '1')"); }

				if (($fdata['forum_poll'] && checkgroup($fdata['forum_poll'])) && $thread_poll) {
					$poll_title = trim(stripinput(censorwords($_POST['poll_title'])));
					if ($poll_title && (isset($poll_opts) && is_array($poll_opts))) {
						$result = dbquery("INSERT INTO ".DB_FORUM_POLLS." (thread_id, forum_poll_title, forum_poll_start, forum_poll_length, forum_poll_votes) VALUES('".$thread_id."', '".$poll_title."', '".time()."', '0', '0')");
						$forum_poll_id = mysql_insert_id();
						$i = 1;
						foreach ($poll_opts as $poll_option) {
							$result = dbquery("INSERT INTO ".DB_FORUM_POLL_OPTIONS." (thread_id, forum_poll_option_id, forum_poll_option_text, forum_poll_option_votes) VALUES('".$thread_id."', '".$i."', '".$poll_option."', '0')");
							$i++;
						}
					}
				}

				if ($fdata['forum_attach'] && checkgroup($fdata['forum_attach'])) {
						// $attach = $_FILES['attach'];
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
									if (!$error) { $result = dbquery("INSERT INTO ".DB_FORUM_ATTACHMENTS." (thread_id, post_id, attach_name, attach_ext, attach_size) VALUES ('".$thread_id."', '".$post_id."', '".$attachname."', '".$attachext."', '".$attach['size']."')"); }
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
					redirect("viewforum.php?forum_id=".$_GET['forum_id']);
			}
		} else {
			$error = 3;
		}
	} else {
		$error = 4;
	}
	if ($error > 2) {
		redirect("postify.php?post=new&error=$error&forum_id=".$_GET['forum_id']);
	} else {
		redirect("postify.php?post=new&error=$error&forum_id=".$_GET['forum_id']."&thread_id=".$thread_id."");
	}
} else {
	if (!isset($_POST['previewpost']) && !isset($_POST['add_poll_option'])) {
		$subject = "";
		$message = "";
		$sticky_thread_check = "";
		$lock_thread_check = "";
		$disable_smileys_check = "";
		$sig_checked = " checked='checked'";
		if ($settings['thread_notify']) { $notify_checked = ""; }
		$poll_title = "";
		$poll_opts = array();
	}
	add_to_title($locale['global_201'].$locale['401']);
	echo "<!--pre_postnewthread-->";
	opentable($locale['401']);
	if (!isset($_POST['previewpost'])) { echo "<div class='tbl2 forum_breadcrumbs' style='margin-bottom:5px'><a href='index.php'>".$settings['sitename']."</a> &raquo; ".$caption."</div>\n"; }

	echo "<form id='inputform' method='post' action='".FUSION_SELF."?action=newthread&amp;forum_id=".$_GET['forum_id']."' enctype='multipart/form-data'>\n";
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
	echo "<td width='145' class='tbl2'>".$locale['460']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='subject' value='".$subject."' class='textbox' maxlength='255' style='width: 250px' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td valign='top' width='145' class='tbl2'>".$locale['461']."</td>\n";
	echo "<td class='tbl1'><textarea name='message' cols='60' rows='15' class='textbox' style='width:98%'>".$message."</textarea></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='145' class='tbl2'>&nbsp;</td>\n";
	echo "<td class='tbl1'>".display_bbcodes("99%", "message")."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td valign='top' width='145' class='tbl2'>".$locale['463']."</td>\n";
	echo "<td class='tbl1'>\n";
	if (iMOD || iSUPERADMIN) {
		echo "<label><input type='checkbox' name='sticky_thread' value='1'".$sticky_thread_check." /> ".$locale['480']."</label><br />\n";
		echo "<label><input type='checkbox' name='lock_thread' value='1'".$lock_thread_check." /> ".$locale['481']."</label><br />\n";
	}
	echo "<label><input type='checkbox' name='disable_smileys' value='1'".$disable_smileys_check." /> ".$locale['482']."</label>";
	if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
		echo "<br />\n<label><input type='checkbox' name='show_sig' value='1'".$sig_checked." /> ".$locale['483']."</label>";
	}
	if ($settings['thread_notify']) { echo "<br />\n<label><input type='checkbox' name='notify_me' value='1'".$notify_checked." /> ".$locale['486']."</label>"; }
	echo "</td>\n</tr>\n";
	if ($fdata['forum_attach'] && checkgroup($fdata['forum_attach'])) {
		add_to_head("<script type='text/javascript' src='".INCLUDES."multi_attachment.js'></script>\n");
		echo "<tr>\n<td width='145' class='tbl2'>".$locale['464']."</td>\n";
		echo "<td class='tbl1'><input id='my_file_element' type='file' name='file_1' class='textbox' style='width:200px;' /><br />\n";
		echo "<span class='small2'>".sprintf($locale['466'], parsebytesize($settings['attachmax']), str_replace(',', ' ', $settings['attachtypes']), $settings['attachmax_count'])."</span><br />\n";
		echo "<div id='files_list'></div>\n";
		echo "<script>\n";
		echo "/* <![CDATA[ */\n";
		echo "<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->\n";
		echo "var multi_selector = new MultiSelector( document.getElementById( \"files_list\" ), ".$settings['attachmax_count']." );\n";
		echo "<!-- Pass in the file element -->\n";
		echo "multi_selector.addElement( document.getElementById( \"my_file_element\" ) );\n";
		echo "/* ]]>*/\n";
		echo "</script>\n";
		echo "</td>\n";
		echo "</tr>\n";
	}
	if ($fdata['forum_poll'] && checkgroup($fdata['forum_poll'])) {
		echo "<tr>\n<td align='center' colspan='2' class='tbl2'>".$locale['467']."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='145' class='tbl2'>".$locale['469']."</td>\n";
		echo "<td class='tbl1'><input type='text' name='poll_title' value='".$poll_title."' class='textbox' maxlength='255' style='width:250px' /></td>\n";
		echo "</tr>\n";
		$i = 1;
		if (isset($poll_opts) && is_array($poll_opts) && count($poll_opts)) {
			foreach ($poll_opts as $poll_option) {
				echo "<tr>\n<td width='145' class='tbl2'>".$locale['470']." ".$i."</td>\n";
				echo "<td class='tbl1'><input type='text' name='poll_options[$i]' value='".$poll_option."' class='textbox' maxlength='255' style='width:250px'>";
				if ($i == count($poll_opts)) {
					echo " <input type='submit' name='add_poll_option' value='".$locale['471']."' class='button' />";
				}
				echo "</td>\n</tr>\n";
				$i++;
			}
		} else {
			echo "<tr>\n<td width='145' class='tbl2'>".$locale['470']." 1</td>\n";
			echo "<td class='tbl1'><input type='text' name='poll_options[1]' value='' class='textbox' maxlength='255' style='width:250px' /></td>\n</tr>\n";
			echo "<tr>\n<td width='145' class='tbl2'>".$locale['470']." 2</td>\n";
			echo "<td class='tbl1'><input type='text' name='poll_options[2]' value='' class='textbox' maxlength='255' style='width:250px' /> ";
			echo "<input type='submit' name='add_poll_option' value='".$locale['471']."' class='button' /></td>\n</tr>\n";
		}
	}
	echo "<tr>\n<td align='center' colspan='2' class='tbl1'>\n";
	echo "<input type='submit' name='previewpost' value='".$locale['400']."' class='button' />\n";
	echo "<input type='submit' name='postnewthread' value='".$locale['401']."' class='button' />\n";
	echo "</td>\n</tr>\n</table>\n</form>\n";
	closetable();
	echo "<!--sub_postnewthread-->";
}
?>