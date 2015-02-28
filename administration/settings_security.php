<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_security.php
| Author: Paul Beuk (muscapaul)
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

if (!checkrights("S9") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

$available_captchas = array();
if ($temp = opendir(INCLUDES."captchas/")) {
	while (false !== ($file = readdir($temp))) {
		if ($file != "." && $file != ".." && is_dir(INCLUDES."captchas/".$file)) {
			$available_captchas[] = $file;
		}
	}
}
sort($available_captchas);

function captcha_options($captchas, $select) {
	$options = "";
	foreach ($captchas AS $captcha) {
		$selected = ($captcha == $select ? "selected='selected'" : "");
		$options .= "<option ".$selected.">".$captcha."</option>\n";
	}
	return $options;
}

if (isset($_POST['savesettings'])) {
	$error = 0;

	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['flood_interval']) ? $_POST['flood_interval'] : "15")."' WHERE settings_name='flood_interval'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['flood_autoban']) ? $_POST['flood_autoban'] : "1")."' WHERE settings_name='flood_autoban'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['maintenance_level']) ? $_POST['maintenance_level'] : "102")."' WHERE settings_name='maintenance_level'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['maintenance']) ? $_POST['maintenance'] : "0")."' WHERE settings_name='maintenance'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash(descript($_POST['maintenance_message']))."' WHERE settings_name='maintenance_message'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['bad_words_enabled']) ? $_POST['bad_words_enabled'] : "0")."' WHERE settings_name='bad_words_enabled'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash($_POST['bad_words'])."' WHERE settings_name='bad_words'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['bad_word_replace'])."' WHERE settings_name='bad_word_replace'");
	if (!$result) { $error = 1; }
	if ($_POST['captcha'] == "recaptcha" && ($_POST['recaptcha_public'] == "" || $_POST['recaptcha_private'] == "")) {
		$error = 2;
	} else {
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['captcha'])."' WHERE settings_name='captcha'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['recaptcha_public'])."' WHERE settings_name='recaptcha_public'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['recaptcha_private'])."' WHERE settings_name='recaptcha_private'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['recaptcha_theme'])."' WHERE settings_name='recaptcha_theme'");
		if (!$result) { $error = 1; }
	}
	redirect(FUSION_SELF.$aidlink."&error=".$error);
}

if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	} elseif ($_GET['error'] == 2) {
		$message = $locale['696'];
	}
	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
	}
}

opentable($locale['683']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='500' class='center'>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'>".$locale['692']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>";
echo $locale['693']."<br />";
echo "<div class='recaptcha_keys' style='margin-top:7px;'>";
echo $locale['694']."<br />";
echo "<span style='margin-top:7px; display:block;'>".$locale['695']."</span>";
echo "<span style='margin-top:7px; display:block;'>".$locale['697']."</span>";
echo "</div>";
echo "</td>\n";
echo "<td width='50%' class='tbl'>";
echo "<select name='captcha' id='captcha' size='1' class='textbox'>".captcha_options($available_captchas, $settings['captcha'])."</select>";
echo "<div class='recaptcha_keys' style='margin-top:5px;'>";
echo "<input type='text' name='recaptcha_public' value='".$settings['recaptcha_public']."' class='textbox' style='width:200px;' /><br />";
echo "<input type='text' name='recaptcha_private' value='".$settings['recaptcha_private']."' class='textbox' style='width:200px; margin-top:5px;' />";
echo "<select name='recaptcha_theme' size='1' class='textbox' style='margin-top:5px;'>\n";
echo "<option value='red'".($settings['recaptcha_theme'] == "red" ? " selected='selected'" : "").">".$locale['697r']."</option>\n";
echo "<option value='blackglass'".($settings['recaptcha_theme'] == "blackglass" ? " selected='selected'" : "").">".$locale['697b']."</option>\n";
echo "<option value='clean'".($settings['recaptcha_theme'] == "clean" ? " selected='selected'" : "").">".$locale['697c']."</option>\n";
echo "<option value='white'".($settings['recaptcha_theme'] == "white" ? " selected='selected'" : "").">".$locale['697w']."</option>\n";
echo "</select>\n</div>";
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'>".$locale['682']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['660']."</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='flood_interval' value='".$settings['flood_interval']."' maxlength='2' class='textbox' style='width:50px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['680']."</td>\n";
echo "<td width='50%' class='tbl'><select name='flood_autoban' class='textbox'>\n";
echo "<option value='1'".($settings['flood_autoban'] == "1" ? " selected='selected'" : "").">".$locale['502']."</option>\n";
echo "<option value='0'".($settings['flood_autoban'] == "0" ? " selected='selected'" : "").">".$locale['503']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'>".$locale['687']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['659']."</td>\n";
echo "<td width='50%' class='tbl'><select name='bad_words_enabled' class='textbox'>\n";
echo "<option value='1'".($settings['bad_words_enabled'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings['bad_words_enabled'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['651']."<br /><span class='small2'>".$locale['652']."<br />".$locale['653']."</span></td>\n";
echo "<td width='50%' class='tbl'><textarea name='bad_words' cols='50' rows='5' class='textbox' style='width:200px;'>".$settings['bad_words']."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['654']."</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='bad_word_replace' value='".$settings['bad_word_replace']."' maxlength='128' class='textbox' style='width:200px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'>".$locale['681']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['675']."</td>\n";
echo "<td width='50%' class='tbl'><select name='maintenance_level' class='textbox'>\n";
echo "<option value='102'".($settings['maintenance_level'] == "102" ? " selected='selected'" : "").">".$locale['676']."</option>\n";
echo "<option value='103'".($settings['maintenance_level'] == "103" ? " selected='selected'" : "").">".$locale['677']."</option>\n";
echo "<option value='1'".($settings['maintenance_level'] == "1" ? " selected='selected'" : "").">".$locale['678']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['657']."</td>\n";
echo "<td width='50%' class='tbl'><select name='maintenance' class='textbox'>\n";
echo "<option value='1'".($settings['maintenance'] == "1" ? " selected='selected'" : "").">".$locale['502']."</option>\n";
echo "<option value='0'".($settings['maintenance'] == "0" ? " selected='selected'" : "").">".$locale['503']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['658']."</td>\n";
echo "<td width='50%' class='tbl'><textarea name='maintenance_message' cols='50' rows='5' class='textbox' style='width:200px;'>".stripslashes($settings['maintenance_message'])."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

echo "<script language='JavaScript' type='text/javascript'>\n";
echo "/* <![CDATA[ */\n";
echo "jQuery(document).ready(function() {";
if ($settings['captcha'] != "recaptcha") {
	echo "jQuery('.recaptcha_keys').hide();";
}
echo "jQuery('#captcha').change(function(){
if(this.value == 'recaptcha')
{jQuery('.recaptcha_keys').slideDown('slow');}
else
{jQuery('.recaptcha_keys').slideUp('slow');}
});";
echo "});";
echo "/* ]]>*/\n";
echo "</script>\n";

require_once THEMES."templates/footer.php";
?>