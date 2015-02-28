<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_forum.php
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
require_once "../maincore.php";

if (!checkrights("S3") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	} elseif ($_GET['error'] == 2) {
		$message = $locale['global_182'];
	}
	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
	}
}

if (isset($_GET['action']) && $_GET['action'] == "count_posts") {
	$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." GROUP BY post_author");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			$result2 = dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
		}
	}
}

if (isset($_POST['savesettings'])) {
	$error = 0;
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['numofthreads']) ? $_POST['numofthreads'] : "5")."' WHERE settings_name='numofthreads'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['forum_ips']) ? $_POST['forum_ips'] : "103")."' WHERE settings_name='forum_ips'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['attachmax']) ? $_POST['attachmax'] : "150000")."' WHERE settings_name='attachmax'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['attachmax_count']) ? $_POST['attachmax_count'] : "5")."' WHERE settings_name='attachmax_count'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['attachtypes'])."' WHERE settings_name='attachtypes'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thread_notify']) ? $_POST['thread_notify'] : "0")."' WHERE settings_name='thread_notify'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['forum_ranks']) ? $_POST['forum_ranks'] : "0")."' WHERE settings_name='forum_ranks'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['forum_edit_lock']) ? $_POST['forum_edit_lock'] : "0")."' WHERE settings_name='forum_edit_lock'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['forum_edit_timelimit']) ? $_POST['forum_edit_timelimit'] : "0")."' WHERE settings_name='forum_edit_timelimit'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['popular_threads_timeframe']) ? $_POST['popular_threads_timeframe'] : 604800)."' WHERE settings_name='popular_threads_timeframe'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['forum_last_posts_reply']) ? $_POST['forum_last_posts_reply'] : "0")."' WHERE settings_name='forum_last_posts_reply'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['forum_editpost_to_lastpost']) ? $_POST['forum_editpost_to_lastpost'] : "0")."' WHERE settings_name='forum_editpost_to_lastpost'");
		if (!$result) { $error = 1; }

		set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		redirect(FUSION_SELF.$aidlink."&error=".$error, true);
	} else {
		redirect(FUSION_SELF.$aidlink."&error=2");
	}
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

opentable($locale['400']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='500' class='center'>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['505']."<br /><span class='small2'>".$locale['506']."</span></td>\n";
echo "<td width='50%' class='tbl'><select name='numofthreads' class='textbox'>\n";
echo "<option".($settings2['numofthreads'] == 5 ? " selected='selected'" : "").">5</option>\n";
echo "<option".($settings2['numofthreads'] == 10 ? " selected='selected'" : "").">10</option>\n";
echo "<option".($settings2['numofthreads'] == 15 ? " selected='selected'" : "").">15</option>\n";
echo "<option".($settings2['numofthreads'] == 20 ? " selected='selected'" : "").">20</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['507']."</td>\n";
echo "<td width='50%' class='tbl'><select name='forum_ips' class='textbox'>\n";
echo "<option value='1'".($settings2['forum_ips'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['forum_ips'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['508']."<br /><span class='small2'>".$locale['509']."</span></td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='attachmax' value='".$settings2['attachmax']."' maxlength='150' class='textbox' style='width:100px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['534']."<br /><span class='small2'>".$locale['535']."</span></td>\n";
echo "<td width='50%' class='tbl'><select name='attachmax_count' class='textbox'>";
for ($i = 1; $i <= 10; $i++) {
	echo "<option value='".$i."'".($settings2['attachmax_count'] == $i ? " selected='selected'" : "").">".$i."</option>";
}
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['510']."<br /><span class='small2'>".$locale['511']."</span></td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='attachtypes' value='".$settings2['attachtypes']."' maxlength='150' class='textbox' style='width:200px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['512']."</td>\n";
echo "<td width='50%' class='tbl'><select name='thread_notify' class='textbox'>\n";
echo "<option value='1'".($settings2['thread_notify'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['thread_notify'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['520']."</td>\n";
echo "<td width='50%' class='tbl'><select name='forum_ranks' class='textbox'>\n";
echo "<option value='1'".($settings2['forum_ranks'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['forum_ranks'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['521']."<br /><span class='small2'>".$locale['522']."</span></td>\n";
echo "<td width='50%' class='tbl'><select name='forum_edit_lock' class='textbox'>\n";
echo "<option value='1'".($settings2['forum_edit_lock'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['forum_edit_lock'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['536']."<br /><span class='small2'>".$locale['537']."</span></td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='forum_edit_timelimit' value='".$settings2['forum_edit_timelimit']."' maxlength='50' class='textbox' style='width:40px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['538']."</td>\n";
echo "<td width='50%' class='tbl'><select name='forum_editpost_to_lastpost' class='textbox'>\n";
echo "<option value='1'".($settings2['forum_editpost_to_lastpost'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['forum_editpost_to_lastpost'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['525']."<br /><span class='small2'>".$locale['526']."</span></td>\n";
echo "<td width='50%' class='tbl'><select name='popular_threads_timeframe' class='textbox'>\n";
echo "<option value='604800'".($settings2['popular_threads_timeframe'] == "604800" ? " selected='selected'" : "").">".$locale['527']."</option>\n";
echo "<option value='2419200'".($settings2['popular_threads_timeframe'] == "2419200" ? " selected='selected'" : "").">".$locale['528']."</option>\n";
echo "<option value='31557600'".($settings2['popular_threads_timeframe'] == "31557600" ? " selected='selected'" : "").">".$locale['529']."</option>\n";
echo "<option value='0'".($settings2['popular_threads_timeframe'] == "0" ? " selected='selected'" : "").">".$locale['530']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['531']."</td>";
echo "<td width='50%' class='tbl'><select name='forum_last_posts_reply' class='textbox'>\n";
echo "<option value='0'".($settings2['forum_last_posts_reply'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>";
echo "<option value='1'".($settings2['forum_last_posts_reply'] == "1" ? " selected='selected'" : "").">".$locale['533']."</option>";
for ($i = 2; $i<=20; $i++) {
	echo "<option value='".$i."'".($settings2['forum_last_posts_reply'] == $i ? " selected='selected'" : "").">".sprintf($locale['532'], $i)."</option>";
}
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
	echo "<td class='tbl'>".$locale['853']."</td>\n";
	echo "<td class='tbl'><input type='password' name='admin_password' value='".(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")."' class='textbox' style='width:150px;' autocomplete='off' /></td>\n";
	echo "</tr>\n<tr>\n";
}
echo "<td align='center' colspan='2' class='tbl'><br /><a href='".FUSION_SELF.$aidlink."&amp;action=count_posts'>".$locale['523']."</a>".(isset($_GET['action']) && $_GET['action'] == "count_posts" ? " ".$locale['524'] : "")."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

require_once THEMES."templates/footer.php";
?>
