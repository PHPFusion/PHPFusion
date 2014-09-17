<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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

if (!checkrights("S6") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	}
	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}

if (isset($_POST['savesettings']) && !defined("FUSION_NULL")) {
	$error = 0;
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['tinymce_enabled']) ? $_POST['tinymce_enabled'] : "0")."' WHERE settings_name='tinymce_enabled'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['smtp_host'])."' WHERE settings_name='smtp_host'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['smtp_port'])."' WHERE settings_name='smtp_port'");
	if (!$result) {
		$error = 1;
	}
	$smtp_auth = isset($_POST['smtp_auth']) && !empty($_POST['smtp_username']) && !empty($_POST['smtp_password']) ? 1 : 0;
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$smtp_auth."' WHERE settings_name='smtp_auth'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['smtp_username'])."' WHERE settings_name='smtp_username'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['smtp_password'])."' WHERE settings_name='smtp_password'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['login_method']) ? $_POST['login_method'] : "0")."' WHERE settings_name='login_method'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['mime_check']) ? $_POST['mime_check'] : "0")."' WHERE settings_name='mime_check'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['guestposts']) ? $_POST['guestposts'] : "0")."' WHERE settings_name='guestposts'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['comments_enabled']) ? $_POST['comments_enabled'] : "0")."' WHERE settings_name='comments_enabled'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['ratings_enabled']) ? $_POST['ratings_enabled'] : "0")."' WHERE settings_name='ratings_enabled'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['visitorcounter_enabled']) ? $_POST['visitorcounter_enabled'] : "0")."' WHERE settings_name='visitorcounter_enabled'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['rendertime_enabled']) ? $_POST['rendertime_enabled'] : "0")."' WHERE settings_name='rendertime_enabled'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['comments_sorting'])."' WHERE settings_name='comments_sorting'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['comments_avatar']) ? $_POST['comments_avatar'] : "0")."' WHERE settings_name='comments_avatar'");
	if (!$result) {
		$error = 1;
	}
	redirect(FUSION_SELF.$aidlink."&error=".$error);
}

opentable($locale['400']);
echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='tinymce_enabled'>".$locale['662']."</label><br /><span class='small2'>".$locale['663']."</span></td>\n";
echo "<td width='50%' class='tbl'>\n";
$yes_no_array = array('1' => $locale['518'], '0' => $locale['519']);
echo form_select('', 'tinymce_enabled', 'tinymce_enabled', $yes_no_array, $settings['tinymce_enabled'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='smtp_host'>".$locale['664']."</label><br /><span class='small2'>".$locale['665']."</span></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_text('', 'smtp_host', 'smtp_host', $settings['smtp_host'], array('max_length' => 200));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='smtp_port'>".$locale['674']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_text('', 'smtp_port', 'smtp_port', $settings['smtp_port'], array('max_length' => 10));
echo "</td>\n</tr>\n<tr>\n";
echo "<td colspan='2' class='tbl2'><label><input type='checkbox' value='yes' id='smtp-auth' name='smtp_auth'".($settings['smtp_auth'] ? ' checked="checked"' : '')." /> ".$locale['698']."</label></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='smtp_username'>".$locale['666']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_text('', 'smtp_username', 'smtp_username', $settings['smtp_username'], array('max_length' => 100));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='smtp_password'>".$locale['667']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_text('', 'smtp_password', 'smtp_password', $settings['smtp_password'], array('max_length' => 100));
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='login_method'>".$locale['699']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
$opts = array('0' => $locale['global_101'], '1' => $locale['699e'], '2' => $locale['699b']);
echo form_select('', 'login_method', 'login_method', $opts, $settings['login_method'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='mime_check'>".$locale['699f']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_select('', 'mime_check', 'mime_check', $yes_no_array, $settings['mime_check'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='guestposts'>".$locale['655']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_select('', 'guestposts', 'guestposts', $yes_no_array, $settings['guestposts'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='comments_enabled'>".$locale['671']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_select('', 'comments_enabled', 'comments_enabled', $yes_no_array, $settings['comments_enabled'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='comments_sorting'>".$locale['684']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
$sort_opts = array('ASC' => $locale['685'], 'DESC' => $locale['686']);
echo form_select('', 'comments_sorting', 'comments_sorting', $sort_opts, $settings['comments_sorting'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='comments_avatar'>".$locale['656']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_select('', 'comments_avatar', 'comments_avatar', $yes_no_array, $settings['comments_avatar'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='ratings_enabled'>".$locale['672']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_select('', 'ratings_enabled', 'ratings_enabled', $yes_no_array, $settings['ratings_enabled'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='visitorcounter_enabled'>".$locale['679']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_select('', 'visitorcounter_enabled', 'visitorcounter_enabled', $yes_no_array, $settings['visitorcounter_enabled'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='rendertime_enabled'>".$locale['688']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
$opts = array('0' => $locale['519'], '1' => $locale['689'], '2' => $locale['690']);
echo form_select('', 'rendertime_enabled', 'rendertime_enabled', $opts, $settings['rendertime_enabled'], array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
echo "</td>\n</tr>\n</tbody>\n</table>\n";
echo closeform();
closetable();

require_once THEMES."templates/footer.php";
?>
