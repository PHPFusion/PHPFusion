<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_banners.php
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

if (!checkrights("SB") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
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

if (isset($_POST['save_banners'])) {
	$error = 0;
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash($_POST['sitebanner1'])."' WHERE settings_name='sitebanner1'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash($_POST['sitebanner2'])."' WHERE settings_name='sitebanner2'");
		if (!$result) { $error = 1; }

		set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		redirect(FUSION_SELF.$aidlink."&error=".$error, true);
	} else {
		redirect(FUSION_SELF.$aidlink."&error=2");
	}
}

if (isset($_POST['preview_banners'])) {
	$sitebanner1 = "";
	$sitebanner2 = "";

	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		$sitebanner1 = stripslash($_POST['sitebanner1']);
		$sitebanner2 = stripslash($_POST['sitebanner2']);

		set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
	} else {
		redirect(FUSION_SELF.$aidlink."&error=2");
	}
} else {
	$sitebanner1 = stripslashes($settings['sitebanner1']);
	$sitebanner2 = stripslashes($settings['sitebanner2']);
}

opentable($locale['850']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='450' class='center'>\n<tr>\n";
echo "<td class='tbl'>".$locale['851']."<br />\n";
echo "<textarea name='sitebanner1' cols='50' rows='5' class='textbox' style='width:450px'>".phpentities($sitebanner1)."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl'>\n";
echo "<input type='button' value='<?php?>' class='button' style='width:60px;' onclick=\"addText('sitebanner1', '<?php\\n', '\\n?>', 'settingsform');\" />\n";
echo display_html("settingsform", "sitebanner1", true)."</td>\n";
echo "</tr>\n<tr>\n";
if (isset($_POST['preview_banners']) && $sitebanner1) {
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		eval("?><td class='tbl'>".$sitebanner1."</td><?php ");
		echo "</tr>\n<tr>\n";
	}
}
echo "<td class='tbl'>".$locale['852']."<br />\n";
echo "<textarea name='sitebanner2' cols='50' rows='5' class='textbox' style='width:450px'>".phpentities($sitebanner2)."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl'>\n";
echo "<input type='button' value='<?php?>' class='button' style='width:60px;' onclick=\"addText('sitebanner2', '<?php\\n', '\\n?>', 'settingsform');\" />\n";
echo display_html("settingsform", "sitebanner2", true)."</td>\n";
echo "</tr>\n<tr>\n";
if (isset($_POST['preview_banners']) && $sitebanner2) {
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		eval("?><td class='tbl'>".$sitebanner2."</td><?php ");
		echo "</tr>\n<tr>\n";
	}
}
if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
	echo "<td class='tbl'>".$locale['853']." <input type='password' name='admin_password' value='".(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")."' class='textbox' style='width:150px;' autocomplete='off' /></td>\n";
	echo "</tr>\n<tr>\n";
}
echo "<td align='center' class='tbl'><br />";
echo "<input type='submit' name='save_banners' value='".$locale['854']."' class='button' />\n";
echo "<input type='submit' name='preview_banners' value='".$locale['855']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

require_once THEMES."templates/footer.php";
?>