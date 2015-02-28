<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members.php
| Author: Nick Jones (Digitanium)
| Author: Paul Buek (Muscapaul)
| Author: Hans Kristian Flaatten (Starefossen)
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

if (!checkrights("M") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."suspend_include.php";
include LOCALE.LOCALESET."admin/members.php";
include LOCALE.LOCALESET."user_fields.php";

$rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0);
$sortby = (isset($_GET['sortby']) ? stripinput($_GET['sortby']) : "all");
$status = (isset($_GET['status']) && isnum($_GET['status'] && $_GET['status'] < 9) ? $_GET['status'] : 0);
$user_id = (isset($_GET['user_id']) && isnum($_GET['user_id']) ? $_GET['user_id'] : false);
$action = (isset($_GET['action']) && isnum($_GET['action']) ? $_GET['action'] : "");

define("USER_MANAGEMENT_SELF", FUSION_SELF.$aidlink."&sortby=$sortby&status=$status&rowstart=$rowstart");

$checkRights = dbcount("(user_id)", DB_USERS, "user_id='".$user_id."' AND user_level>101");
if ($checkRights > 0) {
	$isAdmin = true;
} else {
	$isAdmin = false;
}

if (isset($_POST['cancel'])) {
	redirect(USER_MANAGEMENT_SELF);
} elseif (isset($_GET['step']) && $_GET['step'] == "log" && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	display_suspend_log($user_id, "all", $rowstart);
// Deactivate Inactive Users
} elseif (isset($_GET['step']) && $_GET['step'] == "inactive"  && !$user_id && $settings['enable_deactivation'] == 1  && (!$isAdmin || iSUPERADMIN)) {
	$inactive = dbcount("(user_id)", DB_USERS, "user_status='0' AND user_level<'103' AND user_lastvisit<'$time_overdue' AND user_actiontime='0'");
	$action = ($settings['deactivation_action'] == 0 ? $locale['616'] : $locale['615']);
	$button = $locale['614'].($inactive == 1 ? " 1 ".$locale['612'] : " 50 ".$locale['613']);
	if (!$inactive) { redirect(USER_MANAGEMENT_SELF); }
	opentable($locale['580']);
	if ($inactive > 50) {
		$run_times = round($inactive/50);
		echo "<div id='close-message'><div class='admin-message'>".sprintf($locale['581'], $run_times)."</div></div>";
	}
	echo "<div class='tbl1'>";
	echo sprintf($locale['610'], $inactive, $settings['deactivation_period'], $settings['deactivation_response'], $action);
	if ($settings['deactivation_action'] == 1) {
		echo "<br />\n".$locale['611'];
		echo "</div>\n<div class='admin-message'><strong>".$locale['617']."</strong>\n".$locale['618']."\n";
		if (checkrights("S9")) { echo "<a href='".ADMIN."settings_users.php".$aidlink."'>".$locale['619']."</a>"; }
	}
	echo "</div>\n<div class='tbl1' style='text-align:center;'>\n";
	echo "<form method='post' action='".FUSION_SELF.$aidlink."&amp;step=inactive'>\n";
	echo "<input type='submit' name='cancel' value='".$locale['418']."' class='button' />\n";
	echo "<input type='submit' name='deactivate_users' value='".$button."' class='button' />\n";
	echo "</form>\n</div>\n";
	closetable();

	if (isset($_POST['deactivate_users'])) {
		require_once LOCALE.LOCALESET."admin/members_email.php";
		require_once INCLUDES."sendmail_include.php";

		$result = dbquery(
			"SELECT user_id, user_name, user_email, user_password FROM ".DB_USERS."
			WHERE user_level<'103' AND user_lastvisit<'".$time_overdue."' AND user_actiontime='0' AND user_status='0'
			LIMIT 0,50"
		);

		while ($data = dbarray($result)) {
			$code = md5($response_required.$data['user_password']);
			$message = str_replace("[CODE]", $code, $locale['email_deactivate_message']);
			$message = str_replace("[USER_NAME]", $data['user_name'], $message);
			$message = str_replace("[USER_ID]", $data['user_id'], $message);

			if (sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['email_deactivate_subject'], $message)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_status='7', user_actiontime='".$response_required."' WHERE user_id='".$data['user_id']."'");
				suspend_log($data['user_id'], 7, $locale['621']);
			}
		}
		redirect(FUSION_SELF.$aidlink);
	}
// Add new User
} elseif (isset($_GET['step']) && $_GET['step'] == "add"  && (!$isAdmin || iSUPERADMIN)) {
	require_once CLASSES."UserFields.class.php";
	require_once CLASSES."UserFieldsInput.class.php";

	$errors = array();
	if (isset($_POST['add_user'])) {
		$userInput = new UserFieldsInput();
		$userInput->adminActivation 		= 0;
		$userInput->emailVerification 		= 0;
		$userInput->isAdminPanel			= true;
		$userInput->skipCurrentPass 		= true;
		$userInput->validation 				= 0;
		$userInput->saveInsert();
		$userInput->displayMessages();
		$errors 							= $userInput->getErrorsArray();
		unset($userInput);
	}

	if (!isset($_POST['add_user']) || (isset($_POST['add_user']) && count($errors) > 0)) {
		opentable($locale['480']);
		member_nav(member_url("add", "")."| ".$locale['480']);
		$userFields 						= new UserFields();
		$userFields->postName 				= "add_user";
		$userFields->postValue 				= $locale['480'];
		$userFields->formaction				= FUSION_SELF.$aidlink."&amp;step=add";
		$userFields->isAdminPanel			= true;
		$userFields->showAdminPass 			= false;
		$userFields->showAvatarInput 		= false;
		$userFields->skipCurrentPass 		= true;
		$userFields->errorsArray 			= $errors;
		$userFields->displayInput();
		closetable();
	}

// View User Profile
} elseif (isset($_GET['step']) && $_GET['step'] == "view" && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	require_once CLASSES."UserFields.class.php";

	$result = dbquery(
		"SELECT u.*, s.suspend_reason
		FROM ".DB_USERS." u
		LEFT JOIN ".DB_SUSPENDS." s ON u.user_id=s.suspended_user
		WHERE user_id='".$user_id."'
		ORDER BY suspend_date DESC
		LIMIT 1"
	);
	if (dbrows($result)) { $user_data = dbarray($result); } else { redirect(FUSION_SELF.$aidlink); }

	member_nav(member_url("view", $user_id)."|".$user_data['user_name']);
	opentable($locale['u104']." ".$user_data['user_name']);
	$userFields 							= new UserFields();
	$userFields->userData 					= $user_data;
	$userFields->displayOutput();
	closetable();

// Edit User Profile
} elseif (isset($_GET['step']) && $_GET['step'] == "edit" && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	require_once CLASSES."UserFields.class.php";
	require_once CLASSES."UserFieldsInput.class.php";

	$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$user_id."'"));
	if (!$user_data || $user_data['user_level'] == 103) { redirect(FUSION_SELF.$aidlink); }

	$errors = array();
	if (isset($_POST['savechanges'])) {
		$userInput 							= new UserFieldsInput();
		$userInput->userData 				= $user_data;
		$userInput->adminActivation 		= 0;
		$userInput->emailVerification 		= 0;
		$userInput->isAdminPanel 			= true;
		$userInput->skipCurrentPass 		= true;
		$userInput->saveUpdate();
		$userInput->displayMessages();
		$errors 							= $userInput->getErrorsArray();

		$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$user_id."'"));
		unset($userInput);
	}

	opentable($locale['430']);
	member_nav(member_url("edit", $user_id)."| ".$locale['430']);
	$userFields 							= new UserFields();
	$userFields->postName 					= "savechanges";
	$userFields->postValue 					= $locale['430'];
	$userFields->formaction					= FUSION_SELF.$aidlink."&amp;step=edit&amp;user_id=".$user_id;
	$userFields->isAdminPanel				= true;
	$userFields->showAdminPass 				= false;
	$userFields->skipCurrentPass 			= true;
	$userFields->userData 					= $user_data;
	$userFields->errorsArray 				= $errors;
	$userFields->displayInput();
	closetable();

// Delete User
} elseif (isset($_GET['step']) && $_GET['step'] == "delete" && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	if (isset($_POST['delete_user'])) {
		$result = dbquery("SELECT user_id, user_avatar FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level<'103'");
		if (dbrows($result)) {
			// Delete avatar
			$data = dbarray($result);
			if ($data['user_avatar'] != "" && file_exists(IMAGES."avatars/".$data['user_avatar'])) {
				@unlink(IMAGES."avatars/".$data['user_avatar']);
			}
			// Delete photos
			if (!@ini_get("safe_mode")) { define("SAFEMODE", false); } else { define("SAFEMODE", true); }
			$result = dbquery("SELECT album_id, photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE photo_user='".$user_id."'");
			if (dbrows($result)) {
				while ($data = dbarray($result)) {
					$result = dbquery("DELETE FROM ".DB_PHOTOS." WHERE photo_user='".$user_id."'");
					$photoDir = PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : "");
					@unlink($photoDir.$data['photo_filename']);
					@unlink($photoDir.$data['photo_thumb1']);
					@unlink($photoDir.$data['photo_thumb2']);
				}
			}


			// Delete content
			$result = dbquery("DELETE FROM ".DB_USERS." WHERE user_id='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_name='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_name='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_to='".$user_id."' OR message_from='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_NEWS." WHERE news_name='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_POLL_VOTES." WHERE vote_user='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_user='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_SUSPENDS." WHERE suspended_user='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_THREADS." WHERE thread_author='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_POSTS." WHERE post_author='".$user_id."'");
			$result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE notify_user='".$user_id."'");
			// New in 7.02.07
			$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id='".$user_id."'"); // Delete votes on forum threads
			$result = dbquery("DELETE FROM ".DB_MESSAGES_OPTIONS." WHERE user_id='".$user_id."'"); // Delete messages options


$threads = dbquery("SELECT * FROM ".DB_THREADS." WHERE thread_lastuser='".$user_id."'");
if (dbrows($threads)) {
	while ($thread = dbarray($threads)) {
		// Update thread last post author
		$last_thread_post = dbarray(dbquery("SELECT post_id, post_author FROM ".DB_POSTS." WHERE thread_id='".$thread['thread_id']."' ORDER BY post_id DESC LIMIT 0,1"));
		dbquery("UPDATE ".DB_THREADS." SET thread_lastuser='".$last_thread_post['post_author']."' WHERE thread_id='".$thread['thread_id']."'");
		// Update thread last post id
		dbquery("UPDATE ".DB_THREADS." SET thread_lastpostid='".$last_thread_post['post_id']."' WHERE thread_id='".$thread['thread_id']."'");
		// Update thread posts count
		$posts_count = dbcount("(post_id)", DB_POSTS, "thread_id='".$thread['thread_id']."'");
		dbquery("UPDATE ".DB_THREADS." SET thread_postcount='".$posts_count."' WHERE thread_id='".$thread['thread_id']."'");
		// Update forum last post
		$last_forum_post = dbarray(dbquery("SELECT post_id, post_author FROM ".DB_POSTS." WHERE forum_id='".$thread['forum_id']."' ORDER BY post_id DESC LIMIT 0,1"));
		dbquery("UPDATE ".DB_FORUMS." SET forum_lastuser='".$last_forum_post['post_author']."' WHERE thread_id='".$thread['thread_id']."'");
		// Update forum threads count and posts count
		list($threadcount, $postcount) = dbarraynum(dbquery("SELECT COUNT(thread_id), SUM(thread_postcount) FROM ".DB_THREADS." WHERE forum_id='".$thread['forum_id']."' AND thread_lastuser='".$user_id."' AND thread_hidden='0'"));
		if (isnum($threadcount) && isnum($postcount)) {
			dbquery("UPDATE ".DB_FORUMS." SET forum_postcount='$postcount', forum_threadcount='$threadcount' WHERE forum_id='".$thread['forum_id']."' AND thread_lastuser='".$user_id."'");
		}
	}
}

$threads = dbquery("SELECT * FROM ".DB_THREADS." WHERE thread_author='".$user_id."'");
if (dbrows($threads)) {
	while ($thread = dbarray($threads)) {
		// Delete the posts made by other users in threads started by deleted user
		if ($thread['thread_postcount'] > 0) {
		   dbquery("DELETE FROM ".DB_POSTS." WHERE thread_id='".$thread['thread_id']."'");
		}
		// Delete polls in threads and their associated poll options and votes cast by other users in threads started by deleted user 
		if ($thread['thread_poll'] == 1) {
			dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$thread['thread_id']."'");
			dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$thread['thread_id']."'");
			dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$thread['thread_id']."'");
		}
	}
}

$count_posts = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." GROUP BY post_author");
if (dbrows($count_posts)) {

	while ($data = dbarray($count_posts)) {
		// Update the posts count for all users
		dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
	}
}

			redirect(USER_MANAGEMENT_SELF."&status=dok");
		} else {
			redirect(USER_MANAGEMENT_SELF."&status=der");
		}
	} elseif (isset($_POST['cancel'])) {
		redirect(USER_MANAGEMENT_SELF);
	} else {
		$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$user_id."'"));
		opentable($locale['410']." ".$locale['612'].": ".$user_data['user_name']);
			echo sprintf($locale['425'], $user_data['user_name']);
			echo "<form name='mod_form' method='post' action='".FUSION_SELF."?aid=".iAUTH."&amp;step=delete&amp;user_id=".$user_id."'>\n";
			echo "<input type='submit' name='delete_user' value='".$locale['426']."' class='button' />\n";
			echo "<input type='submit' name='cancel' value='".$locale['427']."' class='button' />\n";
			echo "</form>";
		closetable();
	}
	
// Ban User
} elseif (isset($_GET['action']) && $_GET['action'] == 1 && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	require_once LOCALE.LOCALESET."admin/members_email.php";
	require_once INCLUDES."sendmail_include.php";

	$result = dbquery("SELECT user_name, user_email, user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level<'103'");
	if (dbrows($result)) {
		$udata = dbarray($result);
		if (isset($_POST['ban_user'])) {
			if ($udata['user_status'] == 1) {
				$result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
				unsuspend_log($user_id, 1, stripinput($_POST['ban_reason']));
				redirect(USER_MANAGEMENT_SELF."&status=bre");
			} else {
				$result = dbquery("UPDATE ".DB_USERS." SET user_status='1', user_actiontime='0' WHERE user_id='".$user_id."'");
				suspend_log($user_id, 1, stripinput($_POST['ban_reason']));
				$message = str_replace("[USER_NAME]", $udata['user_name'], $locale['email_ban_message']);
				$message = str_replace("[REASON]", stripinput($_POST['ban_reason']), $message);
				sendemail($udata['user_name'], $udata['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['email_ban_subject'], $message);
				redirect(USER_MANAGEMENT_SELF."&status=bad");
			}
		} else {
			if ($udata['user_status'] == 1) {
				$ban_title = $locale['408']." ".$udata['user_name'];
			} else {
				$ban_title = $locale['409']." ".$udata['user_name'];
			}
			opentable($ban_title);
			echo "<form method='post' action='".stripinput(USER_MANAGEMENT_SELF)."&amp;action=1&amp;user_id=".$user_id."'>\n";
			echo "<table cellpadding='0' cellspacing='0' width='460' class='center'>\n<tr>\n";
			echo "<td colspan='2' class='tbl'>".$locale['585a'].$udata['user_name'].".</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td valign='top' width='80' class='tbl'>".$locale['515'].":</td>\n";
			echo "<td class='tbl'><textarea name='ban_reason' cols='60' rows='2' class='textbox' style='width:380px;'></textarea></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td colspan='2' align='center'><input type='submit' name='cancel' value='".$locale['418']."' class='button' />\n";
			echo "<input type='submit' name='ban_user' value='".$ban_title."' class='button' /></td>\n";
			echo "</tr>\n</table>\n</form>\n";
			closetable();
			display_suspend_log($user_id, 1, $rowstart, 10);
		}
	} else {
		redirect(USER_MANAGEMENT_SELF."&status=ber");
	}
// Activate User
} elseif (isset($_GET['action']) && $_GET['action'] == 2 && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	require_once LOCALE.LOCALESET."admin/members_email.php";
	require_once INCLUDES."sendmail_include.php";

	$result = dbquery("SELECT user_name, user_email FROM ".DB_USERS." WHERE user_id='".$user_id."' LIMIT 1");
	if (dbrows($result)) {
		$udata = dbarray($result);
		$result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
		suspend_log($user_id, 2);
		$subject = $locale['email_activate_subject'].$settings['sitename'];
		$message = str_replace("[USER_NAME]", $udata['user_name'], $locale['email_activate_message']);
		sendemail($udata['user_name'], $udata['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
		redirect(USER_MANAGEMENT_SELF."&status=aok");
	} else {
		redirect(USER_MANAGEMENT_SELF."&status=aer");
	}
// Suspend User
} elseif (isset($_GET['action']) && $_GET['action'] == 3 && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	include LOCALE.LOCALESET."admin/members_email.php";
	require_once INCLUDES."sendmail_include.php";

	$result = dbquery("SELECT user_name, user_email, user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level<'103'");
	if (dbrows($result)) {
		$udata = dbarray($result);
		if (isset($_POST['suspend_user'])) {
			if ($udata['user_status'] == 3) {
				$result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
				unsuspend_log($user_id, 3, stripinput($_POST['suspend_reason']));
				redirect(USER_MANAGEMENT_SELF."&status=sre");
			} else {
				$actiontime = (isset($_POST['suspend_duration']) && isnum($_POST['suspend_duration']) ? $_POST['suspend_duration'] * 86400 : 864000) + time();
				$result = dbquery("UPDATE ".DB_USERS." SET user_status='3', user_actiontime='$actiontime' WHERE user_id='".$user_id."'");
				suspend_log($user_id, 3, stripinput($_POST['suspend_reason']));
				$message = str_replace("[USER_NAME]", $udata['user_name'], $locale['email_suspend_message']);
				$message = str_replace("[DATE]", showdate('longdate', $actiontime), $message);
				$message = str_replace("[REASON]", stripinput($_POST['suspend_reason']), $message);
				sendemail($udata['user_name'], $udata['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['email_suspend_subject'], $message);
				redirect(USER_MANAGEMENT_SELF."&status=sad");
			}
		} else {
			if ($udata['user_status'] == 3) {
				$suspend_title = $locale['591']." ".$udata['user_name'];
				$action = $locale['593'];
			} else {
				$suspend_title = $locale['590']." ".$udata['user_name'];
				$action = $locale['592'];
			}
			opentable($suspend_title);
			echo "<form method='post' action='".stripinput(USER_MANAGEMENT_SELF)."&amp;action=3&amp;user_id=".$user_id."'>\n";
			echo "<table cellpadding='0' cellspacing='0' width='460' class='center'>\n<tr>\n";
			echo "<td colspan='2' class='tbl'>".$locale['594'].$action.$locale['595'].$udata['user_name'].".</td>\n";
			if ($udata['user_status'] != 3) {
				echo "</tr>\n<tr>\n";
				echo "<td valign='top' width='80' class='tbl'>".$locale['596']."</td>\n";
				echo "<td class='tbl'><input type='text' name='suspend_duration' class='textbox' style='width:60px;' /> <span class='small'>(".$locale['551'].")</span></td>\n";
			}
			echo "</tr>\n<tr>\n";
			echo "<td valign='top' width='80' class='tbl'>".$locale['552']."</td>\n";
			echo "<td class='tbl'><textarea name='suspend_reason' cols='60' rows='2' class='textbox' style='width:380px;'></textarea></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td colspan='2' align='center'><input type='submit' name='cancel' value='".$locale['418']."' class='button' />\n";
			echo "<input type='submit' name='suspend_user' value='".$suspend_title."' class='button' /></td>\n";
			echo "</tr>\n</table>\n</form>\n";
			closetable();
			display_suspend_log($user_id, 3, 10, 10);
		}
	} else {
		redirect(USER_MANAGEMENT_SELF."&status=ser");
	}
// Security Ban User
} elseif (isset($_GET['action']) && $_GET['action'] == 4 && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	require_once LOCALE.LOCALESET."admin/members_email.php";
	require_once INCLUDES."sendmail_include.php";

	$result = dbquery("SELECT user_name, user_email, user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level<'103'");
	if (dbrows($result)) {
		$udata = dbarray($result);
		if (isset($_POST['sban_user'])) {
			if ($udata['user_status'] == 4) {
				$result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
				unsuspend_log($user_id, 4, stripinput($_POST['sban_reason']));
				redirect(USER_MANAGEMENT_SELF."&status=sbre");
			} else {
				$result = dbquery("UPDATE ".DB_USERS." SET user_status='4', user_actiontime='0' WHERE user_id='".$user_id."'");
				suspend_log($user_id, 4, stripinput($_POST['sban_reason']));
				$message = str_replace("[USER_NAME]", $udata['user_name'], $locale['email_secban_message']);
				sendemail($udata['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['email_secban_subject'], $message);
				redirect(USER_MANAGEMENT_SELF."&status=sbad");
			}
		} else {
			if ($udata['user_status'] == 4) {
				$ban_title = $locale['602'].$udata['user_name'];
				$action = $locale['603'];
			} else {
				$ban_title = $locale['600']." ".$udata['user_name'];
				$action = $locale['601'];
			}
			opentable($ban_title);
			echo "<form method='post' action='".stripinput(USER_MANAGEMENT_SELF)."&amp;action=4&amp;user_id=".$user_id."'>\n";
			echo "<table cellpadding='0' cellspacing='0' width='460' class='center'>\n<tr>\n";
			echo "<td colspan='2' class='tbl'>".$locale['594'].$action.$locale['595'].$udata['user_name'].".</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td valign='top' width='80' class='tbl'>".$locale['604']."</td>\n";
			echo "<td class='tbl'><textarea name='sban_reason' cols='60' rows='2' class='textbox' style='width:380px;'></textarea></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td colspan='2' align='center'><input type='submit' name='cancel' value='".$locale['418']."' class='button' />\n";
			echo "<input type='submit' name='sban_user' value='".$ban_title."' class='button' /></td>\n";
			echo "</tr>\n</table>\n</form>\n";
			closetable();
			display_suspend_log($user_id, 4, 10, 10);
		}
	} else {
		redirect(USER_MANAGEMENT_SELF."&status=sber");
	}
// Cancel User
} elseif (isset($_GET['action']) && $_GET['action'] == 5 && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	$result = dbquery("SELECT user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level<'103'");
	if (dbrows($result)) {
		$udata = dbarray($result);
		if ($udata['user_status'] == 5) {
			$result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
			unsuspend_log($user_id, 5);
		} else {
			$result = dbquery("UPDATE ".DB_USERS." SET user_status='5', user_actiontime='".$response_required."' WHERE user_id='".$user_id."'");
			suspend_log($user_id, 5);
		}
		redirect(USER_MANAGEMENT_SELF);
	} else {
		redirect(USER_MANAGEMENT_SELF);
	}
// Annonymise User
} elseif (isset($_GET['action']) && $_GET['action'] == 6 && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	$result = dbquery("SELECT user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level<'103'");
	if (dbrows($result)) {
		$udata = dbarray($result);
		if ($udata['user_status'] == 6) {
			$result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
			unsuspend_log($user_id, 6);
		} else {
			$result = dbquery("UPDATE ".DB_USERS." SET user_status='6', user_actiontime='0' WHERE user_id='".$user_id."'");
			suspend_log($user_id, 6);
		}
		redirect(USER_MANAGEMENT_SELF);
	} else {
		redirect(USER_MANAGEMENT_SELF);
	}
// Deactivate User
} elseif (isset($_GET['action']) && $_GET['action'] == 7 && $user_id  && (!$isAdmin || iSUPERADMIN)) {
	$result = dbquery("SELECT user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level<'103'");
	if (dbrows($result)) {
		$udata = dbarray($result);
		if ($udata['user_status'] == 7) {
			$result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
			unsuspend_log($user_id, 7);
		} else {
			require_once LOCALE.LOCALESET."admin/members_email.php";
			require_once INCLUDES."sendmail_include.php";

			$code = md5($response_required.$data['user_password']);
			$message = str_replace("[CODE]", $code, $locale['email_deactivate_message']);
			$message = str_replace("[USER_NAME]", $data['user_name'], $message);
			$message = str_replace("[USER_ID]", $data['user_id'], $message);

			if (sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['email_deactivate_subject'], $message)) {
				$result = dbquery("UPDATE ".DB_USERS." SET user_status='7', user_actiontime='".$response_required."' WHERE user_id='".$user_id."'");
				suspend_log($user_id, 7);
			}
		}
		redirect(USER_MANAGEMENT_SELF);
	} else {
		redirect(USER_MANAGEMENT_SELF);
	}
} else {
	opentable($locale['400']);
	if (isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i", $_GET['search_text'])) {
		$user_name = " user_name LIKE '".stripinput($_GET['search_text'])."%' AND";
		$list_link = "search_text=".stripinput($_GET['search_text']);
	} elseif (isset($_GET['sortby']) && preg_check("/^[0-9A-Z]$/", $_GET['sortby'])) {
		$user_name = ($_GET['sortby'] == "all" ? "" : " user_name LIKE '".stripinput($_GET['sortby'])."%' AND");
		$list_link = "sortby=".stripinput($_GET['sortby']);
	} else {
		$user_name = "";
		$list_link = "sortby=all";
		$_GET['sortby'] = "all";
	}

	$usr_mysql_status = $status;

	if ($status == 0 && $settings['enable_deactivation'] == 1) {
		$usr_mysql_status = "0' AND user_lastvisit>'".$time_overdue."' AND user_actiontime='0";
	} elseif ($status == 8 && $settings['enable_deactivation'] == 1) {
		$usr_mysql_status = "0' AND user_lastvisit<'".$time_overdue."' AND user_actiontime='0";
	}

	$rows = dbcount("(user_id)", DB_USERS, "$user_name user_status='$usr_mysql_status' AND user_level<'103'");
	$result = dbquery(
		"SELECT user_id, user_name, user_level FROM ".DB_USERS."
		WHERE $user_name user_status='$usr_mysql_status' AND user_level<'103'
		ORDER BY user_level DESC, user_name
		LIMIT $rowstart,20"
	);
	echo "<table cellpadding='0' cellspacing='1' width='80%' class='tbl-border center'>\n<tr>\n<td class='tbl1'>\n";
	echo "<form name='viewstatus' method='get' action='".FUSION_SELF."'>\n";
	echo "<input type='hidden' name='aid' value='".iAUTH."' />\n";
	echo "<input type='hidden' name='sortby' value='$sortby' />\n";
	echo $locale['405']." <select name='status' class='textbox' onchange='submit()'>\n";
	for ($i = 0; $i < 9; $i++) {
		if ($i < 8 || $settings['enable_deactivation'] == 1) {
			echo "<option value='$i'".($status == $i ? " selected='selected'" : "").">".getsuspension($i)."</option>\n";
		}
	}
	echo "</select>\n";
	echo "<input type='hidden' name='rowstart' value='$rowstart' />\n</form>\n";
	echo "</td>\n<td class='tbl1' align='right'>\n";
	echo "<a href='".FUSION_SELF.$aidlink."&amp;step=add'>".$locale['402']."</a>\n";
	if ($settings['enable_deactivation'] == 1) {
		if (dbcount("(user_id)", DB_USERS, "user_status='0' AND user_level<'103' AND user_lastvisit<'$time_overdue' AND user_actiontime='0'")) {
			echo " | <a href='".FUSION_SELF.$aidlink."&amp;step=inactive'>".$locale['580']."</a>\n";
		}
	}
	echo "</td>\n</tr>\n</table>\n";
	echo "<div style='text-align:center;margin-bottom:10px;'></div>\n";


	if ($rows) {
		$i = 0;
		echo "<table cellpadding='0' cellspacing='1' width='80%' class='tbl-border center'>\n<tr>\n";
		echo "<td class='tbl2'><strong>".$locale['401']."</strong></td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['403']."</strong></td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['404']."</strong></td>\n";
		echo "</tr>\n";
		while ($data = dbarray($result)) {
			$cell_color = ($i % 2 == 0 ? "tbl1" : "tbl2"); $title = "";
			echo "<tr>\n<td class='$cell_color'><a href='".FUSION_SELF.$aidlink."&amp;step=view&amp;user_id=".$data['user_id']."'>".$data['user_name']."</a></td>\n";
			echo "<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'>".getuserlevel($data['user_level'])."</td>\n";
			echo "<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'>";
			if (iSUPERADMIN || $data['user_level'] < 102) {
				echo "<a href='".FUSION_SELF.$aidlink."&amp;step=edit&amp;user_id=".$data['user_id']."'>".$locale['406']."</a>\n";
				if ($status == 0) { echo "- <a href='".stripinput(USER_MANAGEMENT_SELF."&action=3&user_id=".$data['user_id'])."'>".$locale['553']."</a>\n";
				} elseif ($status == 2) { $title = $locale['407'];
				} elseif ($status != 8) { $title = $locale['419']; }
				if ($title) { echo "- <a href='".stripinput(USER_MANAGEMENT_SELF."&action=$status&user_id=".$data['user_id'])."'>$title</a>\n"; }
				echo "- <a href='".stripinput(USER_MANAGEMENT_SELF."&step=delete&user_id=".$data['user_id'])."' onclick='return DeleteMember();'>".$locale['410']."</a>\n";
				if ($status == 0) {
					echo "<form name='editstatus_".$data['user_id']."' method='get' action='".FUSION_SELF."'>\n";
					echo "<input type='hidden' name='aid' value='".iAUTH."' />\n";
					echo "<input type='hidden' name='sortby' value='$sortby' />\n";
					echo "<input type='hidden' name='status' value='$status' />\n";
					echo "<input type='hidden' name='rowstart' value='$rowstart' />\n";
					echo "<select name='action' class='textbox' onchange='submit()'>\n";
					echo "<option value='' selected='selected'>-- ".$locale['417']." --</option>\n";
					for ($ii = 1; $ii < 8; $ii++) {
						if ($ii != 2 && $ii != 4) { echo "<option value='$ii'>".getsuspension($ii, true)."</option>\n"; }
					}
					echo "</select>\n";
					echo "<input type='hidden' name='user_id' value='".$data['user_id']."' />\n";
					echo "</form>\n";
				}
			}
			echo "</td>\n</tr>\n"; $i++;
		}
		echo "</table>\n";
	} else {
		if (isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i", $_GET['search_text'])) {
			echo "<div style='text-align:center'><br />".sprintf($locale['411'], ($status == 0 ? "" : getsuspension($status))).$locale['413']."'".stripinput($_GET['search_text'])."'<br /><br />\n</div>\n";
		} else {
			echo "<div style='text-align:center'><br />".sprintf($locale['411'], ($status == 0 ? "" : getsuspension($status))).($_GET['sortby'] == "all" ? "" : $locale['412'].$_GET['sortby']).".<br /><br />\n</div>\n";
		}
	}
	$alphanum = array(
		"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R",
		"S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9"
	);
	echo "<div style='margin-top:10px;'></div>\n";
	echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n<tr>\n";
	echo "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;status=".$status."'>".$locale['414']."</a></td>";
	for ($i = 0; $i < 36; $i++) {
		echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF.$aidlink."&amp;sortby=".$alphanum[$i]."&amp;status=$status'>".$alphanum[$i]."</a></div></td>";
		echo ($i == 17 ? "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;status=".$status."'>".$locale['414']."</a></td>\n</tr>\n<tr>\n" : "\n");
	}
	echo "</tr>\n</table>\n";
	echo "<hr />\n<form name='searchform' method='get' action='".FUSION_SELF."'>\n";
	echo "<div style='text-align:center'>\n";
	echo "<input type='hidden' name='aid' value='".iAUTH."' />\n";
	echo "<input type='hidden' name='status' value='$status' />\n";
	echo $locale['415']." <input type='text' name='search_text' class='textbox' style='width:150px'/>\n";
	echo "<input type='submit' name='search' value='".$locale['416']."' class='button' />\n";
	echo "</div>\n</form>\n";
	closetable();
	if ($rows > 20) { echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($rowstart,20,$rows,3,FUSION_SELF.$aidlink."&amp;sortby=".$sortby."&amp;status=".$status."&amp;")."\n</div>\n"; }
	echo "<script type='text/javascript'>"."\n"."function DeleteMember(username) {\n";
	echo "return confirm('".$locale['423']."');\n}\n</script>\n";
}

require_once THEMES."templates/footer.php";
?>