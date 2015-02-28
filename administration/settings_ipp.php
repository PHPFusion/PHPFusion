<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_ipp.php
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

if (!checkRights("S10") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

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
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['newsperpage']) && $_POST['newsperpage'] > 0 ? $_POST['newsperpage'] : "11")."' WHERE settings_name='newsperpage'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['articles_per_page']) && $_POST['articles_per_page'] > 0 ? $_POST['articles_per_page'] : "15")."' WHERE settings_name='articles_per_page'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['downloads_per_page']) && $_POST['downloads_per_page'] > 0 ? $_POST['downloads_per_page'] : "15")."' WHERE settings_name='downloads_per_page'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['links_per_page']) && $_POST['links_per_page'] > 0 ? $_POST['links_per_page'] : "15")."' WHERE settings_name='links_per_page'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['comments_per_page']) && $_POST['comments_per_page'] ? $_POST['comments_per_page'] : "10")."' WHERE settings_name='comments_per_page'");
	if (!$result) { $error = 1; }
	redirect(FUSION_SELF.$aidlink."&error=".$error);
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

opentable($locale['400']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='500' class='center'>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['669'].":</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='newsperpage' value='".$settings2['newsperpage']."' maxlength='2' class='textbox' style='width:50px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['910'].":</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='articles_per_page' value='".$settings2['articles_per_page']."' maxlength='2' class='textbox' style='width:50px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['911'].":</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='downloads_per_page' value='".$settings2['downloads_per_page']."' maxlength='2' class='textbox' style='width:50px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['912'].":</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='links_per_page' value='".$settings2['links_per_page']."' maxlength='2' class='textbox' style='width:50px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['913'].":</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='comments_per_page' value='".$settings2['comments_per_page']."' maxlength='2' class='textbox' style='width:50px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' />\n</td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

require_once THEMES."templates/footer.php";
?>
