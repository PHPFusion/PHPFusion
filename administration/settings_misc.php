<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_misc.php
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

if (!checkrights("S6") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	}
	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
	}
}

if (isset($_POST['savesettings'])) {
	$error = 0;
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['tinymce_enabled']) ? $_POST['tinymce_enabled'] : "0")."' WHERE settings_name='tinymce_enabled'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['smtp_host'])."' WHERE settings_name='smtp_host'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['smtp_port'])."' WHERE settings_name='smtp_port'");
	if (!$result) { $error = 1; }
	$smtp_auth = isset($_POST['smtp_auth']) && !empty($_POST['smtp_username']) && !empty($_POST['smtp_password']) ? 1 : 0;
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$smtp_auth."' WHERE settings_name='smtp_auth'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['smtp_username'])."' WHERE settings_name='smtp_username'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['smtp_password'])."' WHERE settings_name='smtp_password'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['guestposts']) ? $_POST['guestposts'] : "0")."' WHERE settings_name='guestposts'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['comments_enabled']) ? $_POST['comments_enabled'] : "0")."' WHERE settings_name='comments_enabled'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['ratings_enabled']) ? $_POST['ratings_enabled'] : "0")."' WHERE settings_name='ratings_enabled'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['visitorcounter_enabled']) ? $_POST['visitorcounter_enabled'] : "0")."' WHERE settings_name='visitorcounter_enabled'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['rendertime_enabled']) ? $_POST['rendertime_enabled'] : "0")."' WHERE settings_name='rendertime_enabled'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['comments_sorting'])."' WHERE settings_name='comments_sorting'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['comments_avatar']) ? $_POST['comments_avatar'] : "0")."' WHERE settings_name='comments_avatar'");
	if (!$result) { $error = 1; }

	redirect(FUSION_SELF.$aidlink."&error=".$error);
}

opentable($locale['400']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='500' class='center'>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['662']."<br /><span class='small2'>".$locale['663']."</span></td>\n";
echo "<td width='50%' class='tbl'><select name='tinymce_enabled' class='textbox'>\n";
echo "<option value='1'".($settings['tinymce_enabled'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['tinymce_enabled'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['664']."<br /><span class='small2'>".$locale['665']."</span></td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='smtp_host' value='".$settings['smtp_host']."' maxlength='200' class='textbox' style='width:200px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['674']."</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='smtp_port' value='".$settings['smtp_port']."' maxlength='10' class='textbox' style='width:200px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td colspan='2' class='tbl2'><label><input type='checkbox' value='yes' id='smtp-auth' name='smtp_auth'".($settings['smtp_auth']?' checked="checked"':'')." /> ".$locale['698']."</label></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['666']."</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='smtp_username' value='".$settings['smtp_username']."' maxlength='100' class='textbox' style='width:200px;' autocomplete='off' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['667']."</td>\n";
echo "<td width='50%' class='tbl'><input type='password' name='smtp_password' value='".$settings['smtp_password']."' maxlength='100' class='textbox' style='width:200px;' autocomplete='off' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['655']."</td>\n";
echo "<td width='50%' class='tbl'><select name='guestposts' class='textbox'>\n";
echo "<option value='1'".($settings['guestposts'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['guestposts'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['671']."</td>\n";
echo "<td width='50%' class='tbl'><select name='comments_enabled' class='textbox'>\n";
echo "<option value='1'".($settings['comments_enabled'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['comments_enabled'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['684']."</td>\n";
echo "<td width='50%' class='tbl'><select name='comments_sorting' class='textbox'>\n";
echo "<option value='ASC'".($settings['comments_sorting'] == "ASC" ? " selected='selected'" : "").">".$locale['685']."</option>\n";
echo "<option value='DESC'".($settings['comments_sorting'] == "DESC" ? " selected='selected'" : "").">".$locale['686']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['656']."</td>\n";
echo "<td width='50%' class='tbl'><select name='comments_avatar' class='textbox'>\n";
echo "<option value='1'".($settings['comments_avatar'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['comments_avatar'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['672']."</td>\n";
echo "<td width='50%' class='tbl'><select name='ratings_enabled' class='textbox'>\n";
echo "<option value='1'".($settings['ratings_enabled'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['ratings_enabled'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['679']."</td>\n";
echo "<td width='50%' class='tbl'><select name='visitorcounter_enabled' class='textbox'>\n";
echo "<option value='1'".($settings['visitorcounter_enabled'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['visitorcounter_enabled'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['688']."</td>\n";
echo "<td width='50%' class='tbl'><select name='rendertime_enabled' class='textbox'>\n";
echo "<option value='0'".($settings['rendertime_enabled'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "<option value='1'".($settings['rendertime_enabled'] == "1" ? " selected='selected'" : "").">".$locale['689']."</option>\n";
echo "<option value='2'".($settings['rendertime_enabled'] == "2" ? " selected='selected'" : "").">".$locale['690']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

require_once THEMES."templates/footer.php";
?>
