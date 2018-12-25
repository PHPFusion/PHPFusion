<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_theme.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
if (!checkrights("S3") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_theme.php'.fusion_get_aidlink(), 'title' => $locale['theme_settings']]);

if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	}

	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message alert alert-info p-10'> ".$message." </div></div>\n";
	}
}

// Saving settings
if (isset($_POST['savesettings'])) {

    $settings_theme = array(
      "admin_theme" =>   stripinput($_POST['admin_theme'], "", "admin_theme"),
      "theme" => stripinput($_POST['theme'], "", "theme"),
      "bootstrap" => stripinput($_POST['bootstrap'], 0, "bootstrap"),
      "entypo" => stripinput($_POST['entypo'], 0, "entypo"),
      "fontawesome" => stripinput($_POST['fontawesome'], 0, "fontawesome"),
    );

	$error = "0";
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['admin_theme']."' WHERE settings_name='admin_theme'");
	if ($result) dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['theme']."' WHERE settings_name='theme'");
	if ($result) dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['bootstrap']."' WHERE settings_name='bootstrap'");
	if ($result) dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['entypo']."' WHERE settings_name='entypo'");
	if ($result) dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['fontawesome']."' WHERE settings_name='fontawesome'");
	if ($result) {
		redirect(FUSION_SELF.$aidlink."&amp;error=0");
	}

}
$theme_files = makefilelist(THEMES, ".|..|templates|admin_themes", TRUE, "folders");
$admin_theme_files = makefilelist(THEMES."admin_themes/", ".|..", TRUE, "folders");

opentable($locale['theme_settings']);
echo "<div class='well'>".$locale['theme_description']."</div>";
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
openside('');
echo "<table cellpadding='0' cellspacing='0' width='600' class='center'>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['465']."</td>\n";
echo "<td width='50%' class='tbl'><select name='bootstrap' class='textbox'>\n";
echo "<option value='1'".($settings['bootstrap'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['bootstrap'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr><tr>\n";
echo "<td width='50%' class='tbl'>".$locale['466']."</td>\n";
echo "<td width='50%' class='tbl'><select name='entypo' class='textbox'>\n";
echo "<option value='1'".($settings['entypo'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['entypo'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr><tr>\n";
echo "<td width='50%' class='tbl'>".$locale['467']."</td>\n";
echo "<td width='50%' class='tbl'><select name='fontawesome' class='textbox'>\n";
echo "<option value='1'".($settings['fontawesome'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['fontawesome'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr><tr>\n";
echo "<td width='35%' class='tbl'>".$locale['418'];
if ($userdata['user_theme'] == "Default") {
  if ($settings['theme'] != str_replace(THEMES, "", substr(THEME, 0, strlen(THEME)-1))) {
  	echo "<div id='close-message'><div class='admin-message'>".$locale['global_302']."</div></div>\n";
  }
}
echo "</td>\n";
echo "<td width='65%' class='tbl'><select name='theme' class='textbox'>\n";
echo makefileopts($theme_files, $settings['theme'])."\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='35%' class='tbl'>".$locale['418a'];
echo "<td width='65%' class='tbl'><select name='admin_theme' class='textbox'>\n";
echo makefileopts($admin_theme_files, $settings['admin_theme'])."\n";
echo "</select></td>\n";
echo "</tr>\n";
closeside();
echo "<tr><td align='center' colspan='2' class='tbl'><br />";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";

closetable();
require_once THEMES."templates/footer.php";