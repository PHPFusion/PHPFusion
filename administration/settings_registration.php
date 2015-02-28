<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_registration.php
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

if (!checkrights("S4") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header_mce.php";
include LOCALE.LOCALESET."admin/settings.php";

if ($settings['tinymce_enabled']) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	require_once INCLUDES."html_buttons_include.php";
}

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

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

if (isset($_POST['savesettings'])) {
	$error = 0;

	if (addslash($_POST['license_agreement']) != $settings2['license_agreement']) {
		$license_lastupdate = time();
	} else {
		$license_lastupdate = $settings2['license_lastupdate'];
	}

	$license_agreement = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['license_agreement']));

	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['enable_registration']) ? $_POST['enable_registration'] : "1")."' WHERE settings_name='enable_registration'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['email_verification']) ? $_POST['email_verification'] : "1")."' WHERE settings_name='email_verification'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['admin_activation']) ? $_POST['admin_activation'] : "0")."' WHERE settings_name='admin_activation'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['display_validation']) ? $_POST['display_validation'] : "1")."' WHERE settings_name='display_validation'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['enable_terms']) ? $_POST['enable_terms'] : "0")."' WHERE settings_name='enable_terms'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$license_agreement' WHERE settings_name='license_agreement'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$license_lastupdate' WHERE settings_name='license_lastupdate'");
	if (!$result) { $error = 1; }

	redirect(FUSION_SELF.$aidlink."&error=".$error);
}

opentable($locale['400']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='500' class='center'>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['551']."</td>\n";
echo "<td width='50%' class='tbl'><select name='enable_registration' class='textbox'>\n";
echo "<option value='1'".($settings2['enable_registration'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['enable_registration'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['552']."</td>\n";
echo "<td width='50%' class='tbl'><select name='email_verification' class='textbox'>\n";
echo "<option value='1'".($settings2['email_verification'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['email_verification'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['557']."</td>\n";
echo "<td width='50%' class='tbl'><select name='admin_activation' class='textbox'>\n";
echo "<option value='1'".($settings2['admin_activation'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['admin_activation'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['553']."</td>\n";
echo "<td width='50%' class='tbl'><select name='display_validation' class='textbox'>\n";
echo "<option value='1'".($settings2['display_validation'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['display_validation'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['558']."</td>\n";
echo "<td width='50%' class='tbl'><select name='enable_terms' class='textbox'>\n";
echo "<option value='1'".($settings2['enable_terms'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['enable_terms'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl' colspan='2'>".$locale['559']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl' colspan='2'><textarea name='license_agreement' cols='50' rows='10' class='textbox' style='width:320px'>".phpentities(stripslashes($settings2['license_agreement']))."</textarea></td>\n";
echo "</tr>\n";
if (!$settings['tinymce_enabled']) {
	echo "<tr>\n<td class='tbl' colspan='2'>\n";
	echo display_html("settingsform", "license_agreement", true, true, true);
	echo "</td>\n</tr>\n";
}
echo "<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

require_once THEMES."templates/footer.php";
?>
