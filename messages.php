<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: messages.php
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
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."messages.php";

if (!iMEMBER) { redirect("index.php"); }

add_to_title($locale['global_200'].$locale['400']);

$msg_settings = dbarray(dbquery("SELECT * FROM ".DB_MESSAGES_OPTIONS." WHERE user_id='0'"));

if (iADMIN  || $userdata['user_id'] == 1) {
	$msg_settings['pm_inbox'] = 0;
	$msg_settings['pm_savebox'] = 0;
	$msg_settings['pm_sentbox'] = 0;
}

if (!isset($_GET['folder']) || !preg_check("/^(inbox|outbox|archive|options)$/", $_GET['folder'])) { $_GET['folder'] = "inbox"; }
if (isset($_POST['msg_send']) && isnum($_POST['msg_send'])) { $_GET['msg_send'] = $_POST['msg_send']; }
if (isset($_POST['msg_to_group']) && isnum($_POST['msg_to_group'])) { $_GET['msg_to_group'] = $_POST['msg_to_group']; }

$error = ""; $msg_ids = ""; $check_count = 0;

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

if (isset($_POST['save_options'])) {
	$pm_email_notify = isnum($_POST['pm_email_notify']) ? $_POST['pm_email_notify'] : "0";
	$pm_save_sent = isnum($_POST['pm_save_sent']) ? $_POST['pm_save_sent'] : "0";
	if ($_POST['update_type'] == "insert") {
		$result = dbquery("INSERT INTO ".DB_MESSAGES_OPTIONS." (user_id, pm_email_notify, pm_save_sent, pm_inbox, pm_savebox, pm_sentbox) VALUES ('".$userdata['user_id']."', '$pm_email_notify', '$pm_save_sent', '0', '0', '0')");
	} else {
		$result = dbquery("UPDATE ".DB_MESSAGES_OPTIONS." SET pm_email_notify='$pm_email_notify', pm_save_sent='$pm_save_sent' WHERE user_id='".$userdata['user_id']."'");
	}
	redirect(FUSION_SELF."?folder=options");
}


if (isset($_GET['msg_id']) && isnum($_GET['msg_id'])) {
	if (isset($_POST['save'])) {
		$archive_total = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='2'");
		if ($msg_settings['pm_savebox'] == "0" || ($archive_total + 1) <= $msg_settings['pm_savebox']) {
			$result = dbquery("UPDATE ".DB_MESSAGES." SET message_folder='2' WHERE message_id='".$_GET['msg_id']."' AND message_to='".$userdata['user_id']."'");
		} else {
			$error = "1";
		}
		redirect(FUSION_SELF."?folder=archive".($error ? "&error=$error" : ""));
	} elseif (isset($_POST['unsave'])) {
		$inbox_total = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='0'");
		if ($msg_settings['pm_inbox'] == "0" || ($inbox_total + 1) <= $msg_settings['pm_inbox']) {
			$result = dbquery("UPDATE ".DB_MESSAGES." SET message_folder='0' WHERE message_id='".$_GET['msg_id']."' AND message_to='".$userdata['user_id']."'");
		} else {
			$error = "1";
		}
		redirect(FUSION_SELF."?folder=archive".($error ? "&error=$error" : ""));
	} elseif (isset($_POST['delete'])) {
		$result = dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_id='".$_GET['msg_id']."' AND message_to='".$userdata['user_id']."'");
		redirect(FUSION_SELF."?folder=".$_GET['folder']);
	}
}

if ($msg_ids && $check_count > 0) {
	if (isset($_POST['save_msg'])) {
		$archive_total = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='2'");
		if ($msg_settings['pm_savebox'] == "0" || ($archive_total + $check_count) <= $msg_settings['pm_savebox']) {
			$result = dbquery("UPDATE ".DB_MESSAGES." SET message_folder='2' WHERE message_id IN(".$msg_ids.") AND message_to='".$userdata['user_id']."'");
		} else {
			$error = "1";
		}
	} elseif (isset($_POST['unsave_msg'])) {
		$inbox_total = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='0'");
		if ($msg_settings['pm_inbox'] == "0" || ($inbox_total + $check_count) <= $msg_settings['pm_inbox']) {
			$result = dbquery("UPDATE ".DB_MESSAGES." SET message_folder='0' WHERE message_id IN(".$msg_ids.") AND message_to='".$userdata['user_id']."'");
		} else {
			$error = "1";
		}
	} elseif (isset($_POST['read_msg'])) {
		$result = dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id IN(".$msg_ids.") AND message_to='".$userdata['user_id']."'");
	} elseif (isset($_POST['unread_msg'])) {
		$result = dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id IN(".$msg_ids.") AND message_to='".$userdata['user_id']."'");
	} elseif (isset($_POST['delete_msg'])) {
		$result = dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_id IN(".$msg_ids.") AND message_to='".$userdata['user_id']."'");
	}
	redirect(FUSION_SELF."?folder=".$_GET['folder'].($error ? "&error=$error" : ""));
}

if (isset($_POST['send_message'])) {
	$result = dbquery("SELECT * FROM ".DB_MESSAGES_OPTIONS." WHERE user_id='".$userdata['user_id']."'");
	if (dbrows($result)) {
		$my_settings = dbarray($result);
	} else {
		$my_settings['pm_save_sent'] = $msg_settings['pm_save_sent'];
		$my_settings['pm_email_notify'] = $msg_settings['pm_email_notify'];
	}
	$subject = stripinput(trim($_POST['subject']));
	$message = stripinput(trim($_POST['message']));
	if ($subject == "" || $message == "") { redirect(FUSION_SELF."?folder=inbox"); }
	$smileys = isset($_POST['chk_disablesmileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? "n" : "y";
	require_once INCLUDES."sendmail_include.php";
	if (iADMIN && isset($_POST['chk_sendtoall']) && isnum($_POST['msg_to_group'])) {
		$msg_to_group = $_POST['msg_to_group'];
		if ($msg_to_group == "101" || $msg_to_group == "102" || $msg_to_group == "103") {
			$result = dbquery(
				"SELECT u.user_id, u.user_name, u.user_email, mo.pm_email_notify FROM ".DB_USERS." u
				LEFT JOIN ".DB_MESSAGES_OPTIONS." mo USING(user_id)
				WHERE user_level>='".$msg_to_group."' AND user_status='0'"
			);
			if (dbrows($result)) {
				while ($data = dbarray($result)) {
					if ($data['user_id'] != $userdata['user_id']) {
						$result2 = dbquery("INSERT INTO ".DB_MESSAGES." (message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('".$data['user_id']."','".$userdata['user_id']."','".$subject."','".$message."','".$smileys."','0','".time()."','0')");
						$message_content = str_replace("[SUBJECT]", $subject, $locale['626']);
						$message_content = str_replace("[USER]", $userdata['user_name'], $message_content);
						$send_email = isset($data['pm_email_notify']) ? $data['pm_email_notify'] : $msg_settings['pm_email_notify'];
						if ($send_email == "1") { sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['625'], $data['user_name'].$message_content); }
					}
				}
			} else {
				redirect(FUSION_SELF."?folder=inbox");
			}
		} else {
			$result = dbquery(
				"SELECT u.user_id, u.user_name, u.user_email, mo.pm_email_notify FROM ".DB_USERS." u
				LEFT JOIN ".DB_MESSAGES_OPTIONS." mo USING(user_id)
				WHERE user_groups REGEXP('^\\\.{$msg_to_group}$|\\\.{$msg_to_group}\\\.|\\\.{$msg_to_group}$') AND user_status='0'"
			);
			if (dbrows($result)) {
				while ($data = dbarray($result)) {
					if ($data['user_id'] != $userdata['user_id']) {
						$result2 = dbquery("INSERT INTO ".DB_MESSAGES." (message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('".$data['user_id']."','".$userdata['user_id']."','".$subject."','".$message."','".$smileys."','0','".time()."','0')");
						$message_content = str_replace("[SUBJECT]", $subject, $locale['626']);
						$message_content = str_replace("[USER]", $userdata['user_name'], $message_content);
						$send_email = isset($data['pm_email_notify']) ? $data['pm_email_notify'] : $msg_settings['pm_email_notify'];
						if ($send_email == "1") { sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['625'], $data['user_name'].$message_content); }
					}
				}
			} else {
				redirect(FUSION_SELF."?folder=inbox");
			}
		}
	} elseif (isnum($_GET['msg_send'])) {
		require_once INCLUDES."flood_include.php";
		if (!flood_control("message_datestamp", DB_MESSAGES, "message_from='".$userdata['user_id']."'")) {
			$result = dbquery(
				"SELECT u.user_id, u.user_name, u.user_email, u.user_level, mo.pm_email_notify, s.pm_inbox, COUNT(message_id) as message_count
				FROM ".DB_USERS." u
				LEFT JOIN ".DB_MESSAGES_OPTIONS." mo USING(user_id)
				LEFT JOIN ".DB_MESSAGES_OPTIONS." s ON s.user_id='0'
				LEFT JOIN ".DB_MESSAGES." ON message_to=u.user_id AND message_folder='0'
				WHERE u.user_id='".$_GET['msg_send']."' GROUP BY u.user_id"
			);
			if (dbrows($result)) {
				$data = dbarray($result);
				if ($data['user_id'] != $userdata['user_id']) {
					if ($data['user_id'] == 1 || $data['user_level'] > 101 || $data['pm_inbox'] == "0" || ($data['message_count'] + 1) <= $data['pm_inbox']) {
						$result = dbquery("INSERT INTO ".DB_MESSAGES." (message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('".$data['user_id']."','".$userdata['user_id']."','".$subject."','".$message."','".$smileys."','0','".time()."','0')");
						$send_email = isset($data['pm_email_notify']) ? $data['pm_email_notify'] : $msg_settings['pm_email_notify'];
						if ($send_email == "1") {
							$message_content = str_replace("[SUBJECT]", $subject, $locale['626']);
							$message_content = str_replace("[USER]", $userdata['user_name'], $message_content);
							sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['625'], $data['user_name'].$message_content);
						}
					} else {
						$error = "2";
					}
				}
			} else {
				redirect(FUSION_SELF."?folder=inbox&error=noresult");
			}
		} else {
			redirect(FUSION_SELF."?folder=inbox&error=flood");
		}
	}
	if (!$error) {
		$cdata = dbarray(dbquery("SELECT COUNT(message_id) AS outbox_count, MIN(message_id) AS last_message FROM ".DB_MESSAGES." WHERE message_to='".$userdata['user_id']."' AND message_folder='1' GROUP BY message_to"));
		if ($my_settings['pm_save_sent']) {
			if ($msg_settings['pm_sentbox'] != "0" && ($cdata['outbox_count'] + 1) > $msg_settings['pm_sentbox']) {
				$result = dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_id='".$cdata['last_message']."' AND message_to='".$userdata['user_id']."'");
			}
			if (isset($_POST['chk_sendtoall']) && isnum($_POST['msg_to_group'])) {
				$outbox_user = $userdata['user_id'];
			} elseif (isset($_GET['msg_send']) && isnum($_GET['msg_send'])) {
				$outbox_user = $_GET['msg_send'];
			} else {
				$outbox_user = "";
			}
			if ($outbox_user && $outbox_user != $userdata['user_id']) { $result = dbquery("INSERT INTO ".DB_MESSAGES." (message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES ('".$userdata['user_id']."','".$outbox_user."','".$subject."','".$message."','".$smileys."','1','".time()."','1')"); }
		}
	}
	redirect(FUSION_SELF."?folder=inbox".($error ? "&error=$error" : ""));
}

if (isset($_GET['error'])) {
	if ($_GET['error'] == "1") {
		$message = $locale['629'];
	} elseif ($_GET['error'] == "2") {
		$message = $locale['628'];
	} elseif ($_GET['error'] == "noresult") {
		$message = $locale['482'];
	} elseif ($_GET['error'] == "flood") {
		$message = sprintf($locale['487'], $settings['flood_interval']);
	} else {
		$message = "";
	}
	add_to_title($locale['global_201'].$locale['627']);
	opentable($locale['627']);
	echo "<div style='text-align:center'>".$message."</div>\n";
	closetable();
}

if (!isset($_GET['msg_send']) && !isset($_GET['msg_read']) && $_GET['folder'] != "options") {
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
	$bdata = dbarray(dbquery(
		"SELECT COUNT(IF(message_folder=0, 1, null)) inbox_total,
		COUNT(IF(message_folder=1, 1, null)) outbox_total, COUNT(IF(message_folder=2, 1, null)) archive_total
		FROM ".DB_MESSAGES." WHERE message_to='".$userdata['user_id']."' GROUP BY message_to"
	));
	$bdata['inbox_total'] = isset($bdata['inbox_total']) ? $bdata['inbox_total'] : "0";
	$bdata['outbox_total'] = isset($bdata['outbox_total']) ? $bdata['outbox_total'] : "0";
	$bdata['archive_total'] = isset($bdata['archive_total']) ? $bdata['archive_total'] : "0";
	if ($_GET['folder'] == "inbox") {
		$total_rows = $bdata['inbox_total'];
		$result = dbquery(
			"SELECT m.message_id, m.message_subject, m.message_read, m.message_datestamp,
			u.user_id, u.user_name, u.user_status
			FROM ".DB_MESSAGES." m
			LEFT JOIN ".DB_USERS." u ON m.message_from=u.user_id
			WHERE message_to='".$userdata['user_id']."' AND message_folder='0'
			ORDER BY message_datestamp DESC LIMIT ".$_GET['rowstart'].",20"
		);
	} elseif ($_GET['folder'] == "outbox") {
		$total_rows = $bdata['outbox_total'];
		$result = dbquery(
			"SELECT m.message_id, m.message_subject, m.message_read, m.message_datestamp,
			u.user_id, u.user_name, u.user_status
			FROM ".DB_MESSAGES." m
			LEFT JOIN ".DB_USERS." u ON m.message_from=u.user_id
			WHERE message_to='".$userdata['user_id']."' AND message_folder='1'
			ORDER BY message_datestamp DESC LIMIT ".$_GET['rowstart'].",20"
		);
	} elseif ($_GET['folder'] == "archive") {
		$total_rows = $bdata['archive_total'];
		$result = dbquery(
			"SELECT m.message_id, m.message_subject, m.message_read, m.message_datestamp,
			u.user_id, u.user_name, u.user_status
			FROM ".DB_MESSAGES." m
			LEFT JOIN ".DB_USERS." u ON m.message_from=u.user_id
			WHERE message_to='".$userdata['user_id']."' AND message_folder='2'
			ORDER BY message_datestamp DESC LIMIT ".$_GET['rowstart'].",20"
		);
	}

	$folders = array("inbox" => $locale['402'], "outbox" => $locale['403'], "archive" => $locale['404'], "options" => $locale['425']);
	add_to_title($locale['global_201'].$folders[$_GET['folder']]);
	opentable($locale['400']);
	if ($total_rows) echo "<form name='pm_form' method='post' action='".FUSION_SELF."?folder=".$_GET['folder']."'>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
	echo "<tr>\n<td align='left' width='100%' class='tbl'><a href='".FUSION_SELF."?msg_send=0'>".$locale['401']."</a></td>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap;font-weight:".($_GET['folder']=="inbox"?"bold":"normal")."'><a href='".FUSION_SELF."?folder=inbox'>".$locale['402']." [".$bdata['inbox_total']."/".($msg_settings['pm_inbox'] != 0 ? $msg_settings['pm_inbox'] : "&infin;")."]</a></td>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap;font-weight:".($_GET['folder']=="outbox"?"bold":"normal")."'><a href='".FUSION_SELF."?folder=outbox'>".$locale['403']." [".$bdata['outbox_total']."/".($msg_settings['pm_inbox'] != 0 ? $msg_settings['pm_inbox'] : "&infin;")."]</a></td>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap;font-weight:".($_GET['folder']=="archive"?"bold":"normal")."'><a href='".FUSION_SELF."?folder=archive'>".$locale['404']." [".$bdata['archive_total']."/".($msg_settings['pm_inbox'] != 0 ? $msg_settings['pm_inbox'] : "&infin;")."]</a></td>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap;font-weight:".($_GET['folder']=="options"?"bold":"normal")."'><a href='".FUSION_SELF."?folder=options'>".$locale['425']."</a></td>\n";
	echo "</tr>\n</table>\n";
	if ($total_rows) {
		echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n";
		echo "<tr>\n<td class='tbl2'>".$locale['405']."</td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'>".($_GET['folder'] != "outbox" ? $locale['406'] : $locale['421'])."</td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'>".$locale['407']."</td>\n</tr>\n";
		while ($data = dbarray($result)) {
			$message_subject = $data['message_subject'];
			if (!$data['message_read']) { $message_subject = "<strong>".$message_subject."</strong>"; }
			echo "<tr>\n<td class='tbl1'><input type='checkbox' name='check_mark[]' value='".$data['message_id']."' />\n";
			echo "<a href='".FUSION_SELF."?folder=".$_GET['folder']."&amp;msg_read=".$data['message_id']."'>".$message_subject."</a></td>\n";
			echo "<td width='1%' class='tbl1' style='white-space:nowrap'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
			echo "<td width='1%' class='tbl1' style='white-space:nowrap'>".showdate("shortdate", $data['message_datestamp'])."</td>\n</tr>\n";
		}
		echo "</table>\n";

		echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
		echo "<tr>\n<td class='tbl'><a href='#' onclick=\"javascript:setChecked('pm_form','check_mark[]',1);return false;\">".$locale['410']."</a> |\n";
		echo "<a href='#' onclick=\"javascript:setChecked('pm_form','check_mark[]',0);return false;\">".$locale['411']."</a></td>\n";
		echo "<td align='right' class='tbl'>".$locale['409']."\n";
		if ($_GET['folder'] == "inbox") { echo "<input type='submit' name='save_msg' value='".$locale['412']."' class='button' />\n"; }
		if ($_GET['folder'] == "archive") { echo "<input type='submit' name='unsave_msg' value='".$locale['413']."' class='button' />\n"; }
		echo "<input type='submit' name='read_msg' value='".$locale['414']."' class='button' />\n";
		echo "<input type='submit' name='unread_msg' value='".$locale['415']."' class='button' />\n";
		echo "<input type='submit' name='delete_msg' value='".$locale['416']."' class='button' />\n";
		echo "</td>\n</tr>\n</table>\n</form>\n";
	} else {
		echo "<div style='text-align:center'><br />".$locale['461']."<br /><br /></div>";
	}
	echo "<script type='text/javascript'>\n";
	echo "/* <![CDATA[ */\n";
	echo "function setChecked(frmName,chkName,val) {"."\n";
	echo "dml=document.forms[frmName];"."\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {"."\n";
	echo "if(dml.elements[i].name == chkName) {"."\n"."dml.elements[i].checked = val;"."\n";
	echo "}\n}\n}\n";
	echo "/* ]]> */\n";
	echo "</script>\n";
	closetable();
	if ($total_rows > 20) echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $total_rows, 3, FUSION_SELF."?folder=".$_GET['folder']."&amp;")."\n</div>\n";
} elseif ($_GET['folder'] == "options") {
	$result = dbquery("SELECT * FROM ".DB_MESSAGES_OPTIONS." WHERE user_id='".$userdata['user_id']."'");
	if (dbrows($result)) {
		$my_settings = dbarray($result);
		$update_type = "update";
	} else {
		$options = dbarray(dbquery("SELECT pm_save_sent, pm_email_notify FROM ".DB_MESSAGES_OPTIONS." WHERE user_id='0' LIMIT 1"));
		$my_settings['pm_save_sent'] = $options['pm_save_sent'];
		$my_settings['pm_email_notify'] = $options['pm_email_notify'];
		$update_type = "insert";
	}
	$bdata = dbarray(dbquery(
		"SELECT COUNT(IF(message_folder=0, 1, null)) inbox_total,
		COUNT(IF(message_folder=1, 1, null)) outbox_total, COUNT(IF(message_folder=2, 1, null)) archive_total
		FROM ".DB_MESSAGES." WHERE message_to='".$userdata['user_id']."' GROUP BY message_to"
	));
	$bdata['inbox_total'] = isset($bdata['inbox_total']) ? $bdata['inbox_total'] : "0";
	$bdata['outbox_total'] = isset($bdata['outbox_total']) ? $bdata['outbox_total'] : "0";
	$bdata['archive_total'] = isset($bdata['archive_total']) ? $bdata['archive_total'] : "0";
	$folders = array("inbox" => $locale['402'], "outbox" => $locale['403'], "archive" => $locale['404'], "options" => $locale['425']);
	add_to_title($locale['global_201'].$folders[$_GET['folder']]);
	opentable($locale['400']);
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
	echo "<tr>\n<td align='left' width='100%' class='tbl'><a href='".FUSION_SELF."?msg_send=0'>".$locale['401']."</a></td>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap;font-weight:".($_GET['folder']== "inbox" ? "bold" : "normal")."'><a href='".FUSION_SELF."?folder=inbox'>".$locale['402']." [".$bdata['inbox_total']."/".($msg_settings['pm_inbox'] != 0 ? $msg_settings['pm_inbox'] : "&infin;")."]</a></td>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap;font-weight:".($_GET['folder']== "outbox" ? "bold" : "normal")."'><a href='".FUSION_SELF."?folder=outbox'>".$locale['403']." [".$bdata['outbox_total']."/".($msg_settings['pm_sentbox'] != 0 ? $msg_settings['pm_inbox'] : "&infin;")."]</a></td>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap;font-weight:".($_GET['folder']== "archive" ? "bold" : "normal")."'><a href='".FUSION_SELF."?folder=archive'>".$locale['404']." [".$bdata['archive_total']."/".($msg_settings['pm_savebox'] != 0 ? $msg_settings['pm_inbox'] : "&infin;")."]</a></td>\n";
	echo "<td width='1%' class='tbl' style='white-space:nowrap;font-weight:".($_GET['folder'] == "options" ? "bold" : "normal")."'><a href='".FUSION_SELF."?folder=options'>".$locale['425']."</a></td>\n";
	echo "</tr>\n</table>\n";
	echo "<div style='margin:4px;'></div>\n";
	echo "<form name='options_form' method='post' action='".FUSION_SELF."?folder=options'>\n";
	echo "<table cellpadding='0' cellspacing='1' width='500' class='center'>\n";
	echo "<tr><td class='tbl1' width='60%'>".$locale['621']."</td>\n";
	echo "<td class='tbl1' width='40%'><select name='pm_email_notify' class='textbox'>\n";
	echo "<option value='1'".($my_settings['pm_email_notify'] ? " selected='selected'" : "").">".$locale['631']."</option>\n";
	echo "<option value='0'".(!$my_settings['pm_email_notify'] ? " selected='selected'" : "").">".$locale['632']."</option>\n";
	echo "</select></td></tr>\n";
	echo "<tr><td class='tbl1' width='60%'>".$locale['622']."</td>\n";
	echo "<td class='tbl1' width='40%'><select name='pm_save_sent' class='textbox'>\n";
	echo "<option value='1'".($my_settings['pm_save_sent'] ? " selected='selected'" : "").">".$locale['631']."</option>\n";
	echo "<option value='0'".(!$my_settings['pm_save_sent'] ? " selected='selected'" : "").">".$locale['632']."</option>\n";
	echo "</select></td></tr>\n";
	echo "<tr><td align='center' colspan='2' class='tbl1'><br />\n";
	echo "<input type='hidden' name='update_type' value='$update_type' />\n";
	echo "<input type='submit' name='save_options' value='".$locale['623']."' class='button' /></td>\n</tr>\n";
	echo "</table></form>\n";
	closetable();
} elseif ((isset($_GET['msg_read']) && isnum($_GET['msg_read'])) && ($_GET['folder'] == "inbox" || $_GET['folder'] == "archive" || $_GET['folder'] == "outbox")) {
	$result = dbquery(
		"SELECT m.message_id, m.message_subject, m.message_message, m.message_smileys,
		m.message_datestamp, m.message_folder, u.user_id, u.user_name, u.user_status
		FROM ".DB_MESSAGES." m
		LEFT JOIN ".DB_USERS." u ON m.message_from=u.user_id
		WHERE message_to='".$userdata['user_id']."' AND message_id='".$_GET['msg_read']."'"
	);
	if (dbrows($result)) {
		$data = dbarray($result);
		$result = dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".$data['message_id']."'");
		$message_message = $data['message_message'];
		if ($data['message_smileys'] == "y") $message_message = parsesmileys($message_message);
		add_to_title($locale['global_201'].$locale['431']);
		opentable($locale['431']);
		echo "<form name='pm_form' method='post' action='".FUSION_SELF."?folder=".$_GET['folder']."&amp;msg_send=".$data['user_id']."&amp;msg_id=".$data['message_id']."'>\n";
		echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
		echo "<td align='right' width='1%' class='tbl2' style='white-space:nowrap'>".($_GET['folder'] != "outbox" ? $locale['406'] : $locale['421'])."</td>\n";
		echo "<td class='tbl1'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n</tr>\n";
		echo "<tr>\n<td align='right' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['407']."</td>\n";
		echo "<td class='tbl1'>".showdate("longdate", $data['message_datestamp'])."</td>\n</tr>\n";
		echo "<tr>\n<td align='right' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['405']."</td>\n";
		echo "<td class='tbl1'>".$data['message_subject']."</td>\n</tr>\n";
		echo "<tr>\n<td colspan='2' class='tbl1'>".nl2br(parseubb($message_message))."</td>\n</tr>\n";
		echo "</table>\n";
		echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
		echo "<tr>\n<td colspan='2' class='tbl'><a href='".FUSION_SELF."?folder=".$_GET['folder']."'>".$locale['432']."</a></td>\n";
		echo "<td align='right' class='tbl'>\n";
		if ($_GET['folder'] == "inbox" && $data['message_folder'] == 0) { echo "<input type='submit' name='reply' value='".$locale['439']."' class='button' />\n"; }
		if ($_GET['folder'] == "inbox" && $data['message_folder'] == 0) { echo "<input type='submit' name='save' value='".$locale['412']."' class='button' />\n"; }
		if ($_GET['folder'] == "archive" && $data['message_folder'] == 2) { echo "<input type='submit' name='unsave' value='".$locale['413']."' class='button' />\n"; }
		echo "<input type='submit' name='delete' value='".$locale['416']."' class='button' />\n";
		echo "</td>\n</tr>\n</table>\n</form>\n";
		closetable();
	} else {
		redirect(FUSION_SELF);
	}
} elseif (isset($_GET['msg_send']) && isnum($_GET['msg_send'])) {
	require_once INCLUDES."bbcode_include.php";
	if (isset($_POST['send_preview'])) {
		$subject = stripinput($_POST['subject']);
		$message = stripinput($_POST['message']);
		$message_preview = $message;
		if (isset($_POST['chk_sendtoall']) && isnum($_POST['msg_to_group'])) {
			$msg_to_group = $_POST['msg_to_group'];
			$sendtoall_chk = " checked='checked'";
			$msg_to_group_state = "";
			$msg_send_state = " disabled";
		} else {
			$msg_to_group = "";
			$sendtoall_chk = "";
			$msg_to_group_state = " disabled";
			$msg_send_state = "";
		}
		$disablesmileys_chk = isset($_POST['chk_disablesmileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message_preview) ? " checked='checked'" : "";
		if (!$disablesmileys_chk) $message_preview = parsesmileys($message_preview);
		opentable($locale['438']);
		echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
		echo "<td class='tbl1'>".nl2br(parseubb($message_preview))."</td>\n</tr>\n";
		echo "</table>\n";
		closetable();
	} else {
		$subject = ""; $message = ""; $msg_send_state = ""; $msg_to_group = "";
		$msg_to_group_state = " disabled"; $sendtoall_chk = ""; $disablesmileys_chk = "";
	}

	if (isset($_GET['msg_id']) && isnum($_GET['msg_id'])) {
		$result = dbquery(
			"SELECT m.message_subject, m.message_message, m.message_smileys, u.user_id, u.user_name FROM ".DB_MESSAGES." m
			LEFT JOIN ".DB_USERS." u ON m.message_from=u.user_id
			WHERE message_to='".$userdata['user_id']."' AND message_id='".$_GET['msg_id']."'"
		);
		$data = dbarray($result);
		$_GET['msg_send'] = $data['user_id'];
		if ($subject == "") $subject = (!strstr($data['message_subject'], "RE: ") ? "RE: " : "").$data['message_subject'];
		$reply_message = $data['message_message'];
		if (!$data['message_smileys']) $reply_message = parsesmileys($reply_message);
	} else {
		$reply_message = "";
	}

	$user_list = ""; $user_types = "";
	if (!isset($_POST['chk_sendtoall']) || $_GET['msg_send'] != "0") {
		$sel = "";
		$result = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_status='0' ORDER BY user_level DESC, user_name ASC");
		while ($data = dbarray($result)) {
			if ($data['user_id'] != $userdata['user_id']) {
				$sel = ($_GET['msg_send'] == $data['user_id'] ? " selected='selected'" : "");
				$user_list .= "<option value='".$data['user_id']."'$sel>".$data['user_name']."</option>\n";
			}
		}
	}

	if (iADMIN && !isset($_GET['msg_id'])) {
		$user_groups = getusergroups();
		while(list($key, $user_group) = each($user_groups)){
			if ($user_group['0'] != "0") {
				$sel = ($msg_to_group == $user_group['0'] ? " selected='selected'" : "");
				$user_types .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
			}
		}
	}

	add_to_title($locale['global_201'].$locale['420']);
	opentable($locale['420']);
	echo "<form name='inputform' method='post' action='".FUSION_SELF."?msg_send=0' onsubmit=\"return ValidateForm(this)\">\n";
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n";
	echo "<tr>\n<td align='right' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['421'].":</td>\n<td class='tbl1'>\n";
	if ($_GET['msg_send'] == "0") {
		echo "<select name='msg_send' class='textbox'>\n".$user_list."</select>\n";
	} else {
		$udata = dbarray(dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_id='".$_GET['msg_send']."'"));
		echo "<input type='hidden' name='msg_send' value='".$udata['user_id']."' />\n";
		echo profile_link($udata['user_id'], $udata['user_name'], $udata['user_status'])."\n";
	}
	echo "</td>\n<td class='tbl1' align='right'>\n";
	if (iADMIN && !isset($_GET['msg_id'])) {
		echo "<label><input name='chk_sendtoall' type='checkbox' ".$sendtoall_chk." />\n";
		echo "".$locale['434'].":</label> <select name='msg_to_group' class='textbox'>\n".$user_types."</select>\n";
	}
	echo "</td>\n</tr>\n";
	echo "<tr>\n<td align='right' class='tbl2' style='white-space:nowrap'>".$locale['405'].":</td>\n";
	echo "<td class='tbl1' colspan='2'><input type='text' name='subject' value='".$subject."' maxlength='32' class='textbox' style='width:250px;' /></td>\n</tr>\n";
	if ($reply_message) {
		echo "<tr>\n<td align='right' class='tbl2' valign='top' style='white-space:nowrap'>".$locale['422'].":</td>\n";
		echo "<td class='tbl1' colspan='2'>".nl2br(parseubb($reply_message))."</td>\n</tr>\n";
	}
	echo "<tr>\n<td align='right' class='tbl2' valign='top' style='white-space:nowrap'>".($reply_message ? $locale['433'] : $locale['422']).":</td>\n";
	echo "<td class='tbl1' colspan='2'><textarea name='message' cols='75' rows='15' class='textbox' style='width:98%'>".$message."</textarea></td>\n</tr>\n";
	echo "<tr>\n<td align='right' class='tbl2' valign='top'></td>\n<td class='tbl1' colspan='2'>\n";
	echo display_bbcodes("98%", "message")."</td>\n</tr>\n";
	echo "<tr>\n<td align='right' class='tbl2' valign='top' style='white-space:nowrap'>".$locale['425'].":</td>\n";
	echo "<td class='tbl1' colspan='2'>\n<label><input type='checkbox' name='chk_disablesmileys' value='y'".$disablesmileys_chk." />".$locale['427']."</label></td>\n</tr>\n";
	echo "</table>\n";
	echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
	echo "<tr>\n<td class='tbl'><a href='".FUSION_SELF."?folder=inbox'>".$locale['435']."</a></td>\n";
	echo "<td align='right' class='tbl'>\n<input type='submit' name='send_preview' value='".$locale['429']."' class='button' />\n";
	echo "<input type='submit' name='send_message' value='".$locale['430']."' class='button' />\n</td>\n</tr>\n";
	echo "</table>\n</form>\n";
	closetable();
	echo "<script type='text/javascript'>\n";
	echo "/* <![CDATA[ */\n";
	echo "function ValidateForm(frm){\n";
	echo "if (frm.subject.value == \"\" || frm.message.value == \"\"){\n";
	echo "alert(\"".$locale['486']."\");return false;}\n";
	echo "}\n";
	echo "/* ]]>*/\n";
	echo "</script>\n";

} else {
	redirect(FUSION_SELF);
}

require_once THEMES."templates/footer.php";
?>