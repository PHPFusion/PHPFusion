<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: messages.php
| Author: Nick Jones (Digitanium)
| Co-Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
if (!iMEMBER) {	redirect("index.php"); }
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."messages.php";
include THEMES."templates/global/messages.php";
add_to_title($locale['global_200'].$locale['400']);
/**
 * Sanitize environment
 */
if (!isset($_GET['folder']) || !preg_check("/^(inbox|outbox|archive|options)$/", $_GET['folder'])) {
	$_GET['folder'] = "inbox";
}
function validate_user($user_id) {
	global $aidlink;
	if (isnum($user_id) && dbcount("(user_id)", DB_USERS, "user_id='".intval($user_id)."' AND user_status == '0'")) {
		return TRUE;
	}
	return FALSE;
}
if (isset($_POST['msg_send']) && isnum($_POST['msg_send']) && validate_user($_POST['msg_send'])) {
	$_GET['msg_send'] = $_POST['msg_send'];
}
// prohibits send message to non-existing group
$user_group = fusion_get_groups();
unset($user_group[0]);
if (isset($_POST['msg_to_group']) && isnum($_POST['msg_to_group']) && isset($user_group[$_POST['msg_to_group']])) {
	$_GET['msg_to_group'] = $_POST['msg_to_group'];
}

$message = new \PHPFusion\PrivateMessages();

// UI variables for themes.
$message->setInfo(
	array(
		"folders" => array(
			"inbox" => array("link" => FUSION_SELF."?folder=inbox", "title" => $locale['402']),
			"outbox" => array("link" => FUSION_SELF."?folder=outbox", "title" => $locale['403']),
			"archive" => array("link" => FUSION_SELF."?folder=archive", "title" => $locale['404']),
			"options" => array("link" => FUSION_SELF."?folder=options", "title" => $locale['425']),
		),
		"chat_rows" => 0,
		"channel" => "",
		"inbox_total" => dbrows(dbquery("SELECT message_id FROM ".DB_MESSAGES." WHERE message_user='".$userdata['user_id']."' AND message_folder='0'")),
		"outbox_total" => dbrows(dbquery("SELECT message_id FROM ".DB_MESSAGES." WHERE message_user='".$userdata['user_id']."' AND message_folder='1'")),
		"archive_total" => dbrows(dbquery("SELECT message_id FROM ".DB_MESSAGES." WHERE message_user='".$userdata['user_id']."' AND message_folder='2'")),
		"button" => array(
			"new" => array(
				'link' => FUSION_SELF."?folder=".$_GET['folder']."&amp;msg_send=0",
				'name' => $locale['401']
			),
			"options" => array('link' => FUSION_SELF."?folder=options", 'name' => $locale['425']),
		)
	)
);

switch($_GET['folder']) {
	case "options":
		$message->display_settings();
		break;
	case "inbox":
		$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart'])) ? : 0;
		$message->display_inbox();
		break;
	case "default":
		$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart'])) ? : 0;
		$message->display_inbox();
}


// Sanitization

$msg_ids = "";
$check_count = 0;
if (isset($_POST['check_mark'])) {
	if (is_array($_POST['check_mark']) && count($_POST['check_mark']) > 1) {
		foreach ($_POST['check_mark'] as $thisnum) {
			if (isnum($thisnum)) $msg_ids .= ($msg_ids ? "," : "").$thisnum;
			$check_count++;
		}
	} else {
		if (isnum($_POST['check_mark'][0])) $msg_ids = $_POST['check_mark'][0];
		$check_count = 1;
	}
}

/* Outbox Channeling */
if ($_GET['folder'] == "outbox") {
	add_to_title($locale['global_201'].$folders[$_GET['folder']]);
	if ($info['outbox_total'] > 0) {
		$result = dbquery("SELECT m.message_id, m.message_subject, m.message_folder, m.message_datestamp, m.message_from, m.message_read,
			u.user_id, u.user_name, u.user_status, u.user_avatar,
			up.user_id as contact_id, up.user_name as contact_name, up.user_status as contact_status, up.user_avatar as contact_avatar,
			max(m.message_id) as last_message
			FROM ".DB_MESSAGES." m
			LEFT JOIN ".DB_USERS." u ON (m.message_to=u.user_id)
			LEFT JOIN ".DB_USERS." up ON (m.message_from=up.user_id)
			WHERE m.message_user='".$userdata['user_id']."' AND m.message_folder ='1'
			GROUP BY m.message_to
			ORDER BY m.message_datestamp DESC");
		$info['chat_rows'] = dbrows($result);
		if (dbrows($result) > 0) {
			add_to_title($locale['global_201'].$folders[$_GET['folder']]);
			while ($data = dbarray($result)) { // threads
				$data['contact_user'] = array(
					'user_id' => $data['contact_id'],
					'user_name' => $data['contact_name'],
					'user_status' => $data['contact_status'],
					'user_avatar' => $data['contact_avatar']
				);
				$data['message'] = array(
					'link' => BASEDIR."messages.php?folder=outbox&amp;msg_user=".$data['contact_id'],
					'name' => $data['message_subject']
				);
				$info['chat_list'][$data['contact_id']] = $data; // group by contact_id.
			}
			// Outbox channeling - to reduce scope of sql search.
			if (isset($_GET['msg_user']) && isnum($_GET['msg_user'])) {
				$result = dbquery("SELECT m.*, u.user_id, u.user_name, u.user_status, u.user_avatar,
						up.user_id as contact_id, up.user_name as contact_name, up.user_status as contact_status
						FROM ".DB_MESSAGES." m
						LEFT JOIN ".DB_USERS." u ON m.message_to=u.user_id
						LEFT JOIN ".DB_USERS." up ON m.message_from=up.user_id
						WHERE m.message_user='".$userdata['user_id']."' AND m.message_from='".$_GET['msg_user']."' and message_folder='1'
						GROUP BY message_subject ORDER BY message_datestamp DESC
						");
				$info['max_rows'] = dbrows($result);
				if ($info['max_rows'] > 0) {
					while ($topics = dbarray($result)) {
						$info['item'][] = $topics;
						$info['channel'] = profile_link($topics['contact_id'], $topics['contact_name'], $topics['contact_status']); // bah let it loop
					}
				}
			} else {
				$info['channel'] = $locale['467'];
			}
			// end channeling.
		}
	}
} /* Archive Channeling */
elseif ($_GET['folder'] == "archive") {
	if ($info['archive_total'] > 0) {
		$result = dbquery("SELECT m.message_id, m.message_subject, m.message_folder, m.message_datestamp, m.message_from, m.message_read,
			up.user_id as contact_id, up.user_name as contact_name, up.user_status as contact_status, up.user_avatar as contact_avatar,
			max(m.message_id) as last_message
			FROM ".DB_MESSAGES." m
			LEFT JOIN ".DB_USERS." up ON IF(m.message_from='".$userdata['user_id']."', m.message_to=up.user_id, m.message_from=up.user_id)
			WHERE m.message_user='".$userdata['user_id']."' AND m.message_folder ='2'
			GROUP BY m.message_subject
			ORDER BY m.message_datestamp DESC");
		$info['chat_rows'] = dbrows($result);
		if (dbrows($result) > 0) {
			add_to_title($locale['global_201'].$folders[$_GET['folder']]);
			while ($data = dbarray($result)) { // threads
				$data['contact_user'] = array(
					'user_id' => $data['contact_id'],
					'user_name' => $data['contact_name'],
					'user_status' => $data['contact_status'],
					'user_avatar' => $data['contact_avatar']
				);
				$data['message'] = array(
					'link' => BASEDIR."messages.php?folder=archive&amp;msg_user=".$data['contact_id'],
					'name' => $data['message_subject']
				);
				$info['chat_list'][$data['contact_id']] = $data; // group by contact_id.
			}
			// channeling - to reduce scope of sql search.
			if (isset($_GET['msg_user']) && isnum($_GET['msg_user'])) {
				$result = dbquery("SELECT m.*, u.user_id, u.user_name, u.user_status, u.user_avatar
						FROM ".DB_MESSAGES." m
						LEFT JOIN ".DB_USERS." u ON IF(m.message_from='".$userdata['user_id']."', m.message_to=u.user_id, m.message_from=u.user_id)
						WHERE m.message_user='".$userdata['user_id']."' AND (m.message_to='".$_GET['msg_user']."' or m.message_from='".$_GET['msg_user']."') AND message_folder='2'
						GROUP BY message_subject ORDER BY message_datestamp DESC
						");
				$info['max_rows'] = dbrows($result);
				if ($info['max_rows'] > 0) {
					while ($topics = dbarray($result)) {
						$info['item'][] = $topics;
						$info['channel'] = profile_link($topics['user_id'], $topics['user_name'], $topics['user_status']); // bah let it loop
					}
				}
			} else {
				$info['channel'] = $locale['467'];
			}
			// end channeling.
		}
	}
}
if ((isset($_GET['msg_read']) && isnum($_GET['msg_read'])) && ($_GET['folder'] == "inbox" || $_GET['folder'] == "archive" || $_GET['folder'] == "outbox")) {
	// real full pm - debug success - nothing here.
	$p_result = dbquery("SELECT message_id, message_subject FROM ".DB_MESSAGES." WHERE message_id='".$_GET['msg_read']."' LIMIT 1");
	if (dbrows($p_result) > 0) {
		$p_data = dbarray($p_result);
		$message_subject = $p_data['message_subject'];
		// Messages Query in msg_read.
		if ($_GET['folder'] == 'inbox') {
			$result = dbquery("SELECT  m.message_id, m.message_to, m.message_from, m.message_subject, m.message_message, m.message_smileys,
				m.message_datestamp, m.message_folder, u.user_id, u.user_name, u.user_status, u.user_avatar
				FROM ".DB_MESSAGES." m
				LEFT JOIN ".DB_USERS." u ON IF(message_folder=1, m.message_to=u.user_id, m.message_from=u.user_id)
				WHERE message_from !='".$userdata['user_id']."' AND message_subject='$message_subject' AND message_folder='0' OR
				message_to='".$userdata['user_id']."' AND message_subject='".$p_data['message_subject']."' AND message_folder='1' AND message_user='".$userdata['user_id']."'
				ORDER BY message_datestamp DESC");
		} elseif ($_GET['folder'] == 'outbox') {
			$result = dbquery("SELECT  m.message_id, m.message_subject, m.message_message, m.message_smileys,
				m.message_datestamp, m.message_folder, u.user_id, u.user_name, u.user_status, u.user_avatar
				FROM ".DB_MESSAGES." m
				LEFT JOIN ".DB_USERS." u ON m.message_to=u.user_id
				WHERE message_to='".$userdata['user_id']."' AND message_subject='".$p_data['message_subject']."' AND message_folder='1' AND message_user='".$userdata['user_id']."'
				ORDER BY message_datestamp DESC");
		} elseif ($_GET['folder'] == 'archive') {
			$result = dbquery("SELECT  m.message_id, m.message_to, m.message_from, m.message_subject, m.message_message, m.message_smileys,
				m.message_datestamp, m.message_folder, u.user_id, u.user_name, u.user_status, u.user_avatar
				FROM ".DB_MESSAGES." m
				LEFT JOIN ".DB_USERS." u ON IF(message_folder=1, m.message_to=u.user_id, m.message_from=u.user_id)
				WHERE message_from='".$userdata['user_id']."' AND message_subject='$message_subject' AND message_folder='2' OR
					  message_to='".$userdata['user_id']."' AND message_subject='".$p_data['message_subject']."' AND message_folder='2' AND message_user='".$userdata['user_id']."'
				ORDER BY message_datestamp DESC");
		}
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				if ($data['message_smileys'] == "y") $data['message_message'] = parsesmileys($data['message_message']);
				$info['message'][] = $data;
			}
			$result = dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".$p_data['message_id']."'");
			add_to_title($locale['global_201'].$locale['431']);
		} else {
			//echo 'no message found inner';
			redirect(BASEDIR."messages.php");
		}
	} else {
		redirect(BASEDIR."messages.php");
	}
}
require_once THEMES."templates/footer.php";