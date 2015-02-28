<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: postreply.php
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

if (isset($_POST['previewreply'])) {
	$message = trim(stripinput(censorwords($_POST['message'])));
	$sig_checked = isset($_POST['show_sig']) ? " checked='checked'" : "";
	$disable_smileys_check = isset($_POST['disable_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? " checked='checked'" : "";
	if ($settings['thread_notify']) $notify_checked = isset($_POST['notify_me']) ? " checked='checked'" : "";
	if ($message == "") {
		$previewmessage = $locale['421'];
	} else {
		$previewmessage = $message;
		if ($sig_checked) { $previewmessage = $previewmessage."\n\n".$userdata['user_sig']; }
		if (!$disable_smileys_check) {  $previewmessage = parsesmileys($previewmessage); }
		$previewmessage = parseubb($previewmessage);
		$previewmessage = nl2br($previewmessage);
	}
	$is_mod = iMOD && iUSER < "102" ? true : false;
	opentable($locale['402']);
	echo "<div class='tbl2 forum_breadcrumbs' style='margin-bottom:5px'><span class='small'><a href='index.php'>".$settings['sitename']."</a> &raquo; ".$caption."</span></div>\n";

	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
	echo "<td colspan='2' class='tbl2'><strong>".$tdata['thread_subject']."</strong></td>\n</tr>\n";
	echo "<tr>\n<td class='tbl2' style='width:140px;'>".profile_link($userdata['user_id'], $userdata['user_name'], $userdata['user_status'])."</td>\n";
	echo "<td class='tbl2'>".$locale['426'].showdate("forumdate", time())."</td>\n";
	echo "</tr>\n<tr>\n<td valign='top' width='140' class='tbl2'>\n";
	if ($userdata['user_avatar'] && file_exists(IMAGES."avatars/".$userdata['user_avatar'])) {
		echo "<img src='".IMAGES."avatars/".$userdata['user_avatar']."' alt='' /><br /><br />\n";
	}
	echo "<span class='small'>".getuserlevel($userdata['user_level'])."</span><br /><br />\n";
	echo "<span class='small'><strong>".$locale['423']."</strong> ".$userdata['user_posts']."</span><br />\n";
	echo "<span class='small'><strong>".$locale['425']."</strong> ".showdate("shortdate", $userdata['user_joined'])."</span><br />\n";
	echo "<br /></td>\n<td valign='top' class='tbl1'>".$previewmessage."</td>\n";
	echo "</tr>\n</table>\n";
	closetable();
}
if (isset($_POST['postreply'])) {
	$message = trim(stripinput(censorwords($_POST['message'])));
	$flood = false; $error = 0;
	$sig = isset($_POST['show_sig']) ? "1" : "0";
	$smileys = isset($_POST['disable_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? "0" : "1";
	if (iMEMBER) {
		if ($message != "") {
			require_once INCLUDES."flood_include.php";
			if (!flood_control("post_datestamp", DB_POSTS, "post_author='".$userdata['user_id']."'")) {
				if ($fdata['forum_merge'] && $tdata['thread_lastuser'] == $userdata['user_id']) {
					$mergeData = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_id DESC"));
					$mergedMessage = $mergeData['post_message']."\n\n".$locale['520']." ".showdate("longdate", time()).":\n".$message;

					$result = dbquery(
						"UPDATE ".DB_POSTS." SET
							post_message='".$mergedMessage."',
							post_showsig='".$sig."',
							post_smileys='".$smileys."',
							post_edituser='".$userdata['user_id']."',
							post_edittime='".time()."'
						WHERE post_id='".$mergeData['post_id']."'"
					);
					$post_id = $mergeData['post_id'];
					$threadCount = "";
					$postCount = "";
				} else {
					$result = dbquery("INSERT INTO ".DB_POSTS." (forum_id, thread_id, post_message, post_showsig, post_smileys, post_author, post_datestamp, post_ip, post_ip_type, post_edituser, post_edittime, post_editreason) VALUES ('".$_GET['forum_id']."', '".$_GET['thread_id']."', '$message', '$sig', '$smileys', '".$userdata['user_id']."', '".time()."', '".USER_IP."', '".USER_IP_TYPE."', '0', '0', '')");
					$post_id = mysql_insert_id();
					$result = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$userdata['user_id']."'");
					$threadCount = "thread_postcount=thread_postcount+1,";
					$postCount = "forum_postcount=forum_postcount+1,";
				}
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', ".$postCount." forum_lastuser='".$userdata['user_id']."' WHERE forum_id='".$_GET['forum_id']."'");
				$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_id."', ".$threadCount." thread_lastuser='".$userdata['user_id']."' WHERE thread_id='".$_GET['thread_id']."'");
				if ($settings['thread_notify'] && isset($_POST['notify_me'])) {
					if (!dbcount("(thread_id)", DB_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'")) {
						$result = dbquery("INSERT INTO ".DB_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$_GET['thread_id']."', '".time()."', '".$userdata['user_id']."', '1')");
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
									if (!$error) { $result = dbquery("INSERT INTO ".DB_FORUM_ATTACHMENTS." (thread_id, post_id, attach_name, attach_ext, attach_size) VALUES ('".$_GET['thread_id']."', '".$post_id."', '$attachname', '$attachext', '".$attach['size']."')"); }
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
		redirect("postify.php?post=reply&error=$error&forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']);
	} else {
		redirect("postify.php?post=reply&error=$error&forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."&post_id=$post_id");
	}
} else {
	if (!isset($_POST['previewreply'])) {
		$message = "";
		$disable_smileys_check = "";
		$sig_checked = " checked='checked'";
		if ($settings['thread_notify']) {
			if (dbcount("(thread_id)", DB_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'")) {
				$notify_checked = " checked='checked'";
			} else {
				$notify_checked = "";
			}
		}
	}
	if (isset($_GET['quote']) && isnum($_GET['quote'])) {
		$result = dbquery(
			"SELECT post_message, user_name FROM ".DB_POSTS."
			INNER JOIN ".DB_USERS." ON ".DB_POSTS.".post_author=".DB_USERS.".user_id
			WHERE thread_id='".$_GET['thread_id']."' and post_id='".$_GET['quote']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$message = "[quote][url=".$settings['siteurl']."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$_GET['quote']."#post_".$_GET['quote']."][b]".$data['user_name'].$locale['429']."[/b][/url]\n\n".strip_bbcodes($data['post_message'])."[/quote]";
		}
	}
	add_to_title($locale['global_201'].$locale['403']);
	echo "<!--pre_postreply-->";
	opentable($locale['403']);
	if (!isset($_POST['previewreply'])) echo "<div class='tbl2 forum_breadcrumbs' style='margin-bottom:5px'><a href='index.php'>".$settings['sitename']."</a> &raquo; ".$caption."</div>\n";

	echo "<form name='inputform' method='post' action='".FUSION_SELF."?action=reply&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."' enctype='multipart/form-data'>\n";
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
	echo "<td valign='top' width='145' class='tbl2'>".$locale['461']."</td>\n";
	echo "<td class='tbl1'><textarea name='message' cols='60' rows='15' class='textbox' style='width:98%'>$message</textarea></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='145' class='tbl2'>&nbsp;</td>\n";
	echo "<td class='tbl1'>".display_bbcodes("99%", "message")."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td valign='top' width='145' class='tbl2'>".$locale['463']."</td>\n";
	echo "<td class='tbl1'>\n";
	echo "<label><input type='checkbox' name='disable_smileys' value='1'".$disable_smileys_check." /> ".$locale['482']."</label>";
	if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
		echo "<br />\n<label><input type='checkbox' name='show_sig' value='1'".$sig_checked." /> ".$locale['483']."</label>";
	}
	if ($settings['thread_notify']) {
		echo "<br />\n<label><input type='checkbox' name='notify_me' value='1'".$notify_checked." /> ".$locale['486']."</label>";
	}
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
	echo "<tr>\n<td align='center' colspan='2' class='tbl1'>\n";
	echo "<input type='submit' name='previewreply' value='".$locale['402']."' class='button' />\n";
	echo "<input type='submit' name='postreply' value='".$locale['404']."' class='button' />\n";
	echo "</td>\n</tr>\n</table>\n</form>\n";
	closetable();
	echo "<!--sub_postreply-->";
	if ($settings['forum_last_posts_reply'] != "0") {
		$result = dbquery(
			"SELECT p.thread_id, p.post_message, p.post_smileys, p.post_author,	p.post_datestamp, p.post_hidden,
			u.user_id, u.user_name, u.user_status, u.user_avatar
			FROM ".DB_POSTS." p
			LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
			WHERE p.thread_id='".$_GET['thread_id']."' AND p.post_hidden='0'
			ORDER BY p.post_datestamp DESC LIMIT 0,".$settings['forum_last_posts_reply']
		);
		if (dbrows($result)) {
			$title = "";
			if ($settings['forum_last_posts_reply'] == "1") {
				$title = $locale['431'];
			} else {
				$title = sprintf($locale['432'], $settings['forum_last_posts_reply']);
			}
			opentable($title);
				echo "<div style='max-height:350px;overflow:auto;'>\n";
				echo "<table cellpadding='1' cellspacing='1' width='100%' class='tbl-border forum_thread_table'>\n";
				$i = $settings['forum_last_posts_reply'];
				while ($data = dbarray($result)) {
					$message = $data['post_message'];
					if ($data['post_smileys']) { $message = parsesmileys($message); }
					$message = parseubb($message);
					echo "<tr>\n<td class='tbl2 forum_thread_user_name' style='width:10%'><!--forum_thread_user_name-->".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
					echo "<td class='tbl2 forum_thread_post_date'>\n";
					echo "<div style='float:right' class='small'>\n";
					echo $i.($i == $settings['forum_last_posts_reply'] ? " (".$locale['431'].")" : "");
					echo "</div>\n";
					echo "<div class='small'>".$locale['426'].showdate("forumdate", $data['post_datestamp'])."</div>\n";
					echo "</td>\n";
					echo "</tr>\n<tr>\n<td valign='top' class='tbl2 forum_thread_user_info' style='width:10%'>\n";
					if ($data['user_avatar'] && file_exists(IMAGES."avatars/".$data['user_avatar'])) {
						echo "<img src='".IMAGES."avatars/".$data['user_avatar']."' alt='".$locale['430']."' style='height:50px;' />\n";
					} else {
						echo "<img src='".IMAGES."avatars/noavatar50.png' alt='".$locale['430']."' />\n";
					}
					echo "</td>\n<td valign='top' class='tbl1 forum_thread_user_post'>\n";
					echo nl2br($message);
					echo "</td>\n</tr>\n";
					$i--;
				}
				echo "</table>\n</div>\n";
			closetable();
		}
	}
}
?>
