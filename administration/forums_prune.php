<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_prune.php
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

if ((!isset($_POST['prune_forum'])) && (isset($_GET['action']) && $_GET['action'] == "prune") && (isset($_GET['forum_id']) && isnum($_GET['forum_id']))) {
	$result = dbquery("SELECT forum_name FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."' AND forum_cat!='0'");
	if (dbrows($result)) {
		$data = dbarray($result);
		opentable($locale['600'].": ".$data['forum_name']);
		echo "<form name='prune_form' method='post' action='".FUSION_SELF.$aidlink."&amp;action=prune&amp;forum_id=".$_GET['forum_id']."'>\n";
		echo "<div style='text-align:center'>\n";
		echo $locale['601']."<br />\n".$locale['602']."<br /><br />\n";
		echo $locale['603']."<select name='prune_time' class='textbox'>\n";
		echo "<option value='7'>1 ".$locale['604']."</option>\n";
		echo "<option value='14'>2 ".$locale['605']."</option>\n";
		echo "<option value='30'>1 ".$locale['606']."</option>\n";
		echo "<option value='60'>2 ".$locale['607']."</option>\n";
		echo "<option value='90'>3 ".$locale['607']."</option>\n";
		echo "<option value='120'>4 ".$locale['607']."</option>\n";
		echo "<option value='150'>5 ".$locale['607']."</option>\n";
		echo "<option value='180' selected='selected'>6 ".$locale['607']."</option>\n";
		echo "</select><br /><br />\n";
		echo "<input type='submit' name='prune_forum' value='".$locale['600']."' class='button' / onclick=\"return confirm('".$locale['612']."');\">\n";
		echo "</div>\n</form>\n";
		closetable();
	}
} elseif ((isset($_POST['prune_forum'])) && (isset($_GET['action']) && $_GET['action'] == "prune") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_POST['prune_time']) && isnum($_POST['prune_time']))) {
	$result = dbquery("SELECT forum_name FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."' AND forum_cat!='0'");
	if (dbrows($result)) {
		$data = dbarray($result);
		opentable($locale['600'].": ".$data['forum_name']);
		echo "<div style='text-align:center'>\n<strong>".$locale['608']."</strong></br /></br />\n";
		$prune_time = (time() - (86400 * $_POST['prune_time']));
		$result = dbquery("SELECT post_id, post_datestamp FROM ".DB_POSTS." WHERE forum_id='".$_GET['forum_id']."' AND post_datestamp < '".$prune_time."'");
		$delattach = 0;
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$result2 = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'");
				if (dbrows($result2) != 0) {
					$delattach++;
					$attach = dbarray($result2);
					@unlink(FORUM."attachments/".$attach['attach_name']);
					$result3 = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'");
				}
			}
		}
		$result = dbquery("DELETE FROM ".DB_POSTS." WHERE forum_id='".$_GET['forum_id']."' AND post_datestamp < '".$prune_time."'");
		echo $locale['609'].mysql_affected_rows()."<br />";
		echo $locale['610'].$delattach."<br />";
		$result = dbquery("SELECT thread_id,thread_lastpost FROM ".DB_THREADS." WHERE  forum_id='".$_GET['forum_id']."' AND thread_lastpost < '".$prune_time."'");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$result2 = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$data['thread_id']."'");
			}
		}
		$result = dbquery("DELETE FROM ".DB_THREADS." WHERE forum_id='".$_GET['forum_id']."' AND  thread_lastpost < '".$prune_time."'");
		$result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_THREADS." WHERE forum_id='".$_GET['forum_id']."' ORDER BY thread_lastpost DESC LIMIT 0,1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$data['thread_lastpost']."', forum_lastuser='".$data['thread_lastuser']."' WHERE forum_id='".$_GET['forum_id']."'");
		} else {
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastuser='0' WHERE forum_id='".$_GET['forum_id']."'");
		}
		echo $locale['611'].mysql_affected_rows()."\n</div>";

		$result = dbquery(
			"SELECT COUNT(post_id) AS postcount, thread_id FROM ".DB_POSTS."
			WHERE forum_id='".$_GET['forum_id']."' GROUP BY thread_id"
		);

		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				dbquery("UPDATE ".DB_THREADS." SET thread_postcount='".$data['postcount']."' WHERE thread_id='".$data['thread_id']."'");
			}
		}

		$result = dbquery(
			"SELECT SUM(thread_postcount) AS postcount, forum_id FROM ".DB_THREADS."
			WHERE forum_id='".$_GET['forum_id']."' GROUP BY forum_id"
		);

		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				dbquery("UPDATE ".DB_FORUMS." SET forum_postcount='".$data['postcount']."' WHERE forum_id='".$data['forum_id']."'");
			}
		}

		$result = dbquery(
			"SELECT COUNT(thread_id) AS threadcount, forum_id FROM ".DB_THREADS."
			WHERE forum_id='".$_GET['forum_id']."' GROUP BY forum_id"
		);

		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				dbquery("UPDATE ".DB_FORUMS." SET forum_threadcount='".$data['threadcount']."' WHERE forum_id='".$data['forum_id']."'");
			}
		}
		closetable();
	}
}
?>