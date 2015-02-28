<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_photos.php
| Author: Nick Jones (Digitanium)
| Co-Author: Robert Gaudyn (Wooya)
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

if (!checkrights("S5") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

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

function color_mapper($field, $value) {
	global $settings2;
	$cvalue[] = "00";
	$cvalue[] = "33";
	$cvalue[] = "66";
	$cvalue[] = "99";
	$cvalue[] = "CC";
	$cvalue[] = "FF";
	$select = "";
	$select = "<select name='".$field."' class='textbox' onchange=\"document.getElementById('preview_".$field."').style.background = '#' + this.options[this.selectedIndex].value;\" ".(!$settings2['photo_watermark'] ? "disabled='disabled'" : "").">\n";
	for ($ca=0; $ca<count($cvalue); $ca++) {
		for ($cb=0; $cb<count($cvalue); $cb++) {
			for ($cc=0; $cc<count($cvalue); $cc++) {
				$hcolor = $cvalue[$ca].$cvalue[$cb].$cvalue[$cc];
				$select .= "<option value='".$hcolor."'".($value==$hcolor?" selected='selected' ":" ")."style='background-color:#".$hcolor.";'>#".$hcolor."</option>\n";
			}
		}
	}
	$select .= "</select>\n";
	return $select;
}

if (isset($_POST['delete_watermarks'])) {
	define("SAFEMODE", @ini_get("safe_mode") ? true : false);
	$result = dbquery("SELECT album_id,photo_filename FROM ".DB_PHOTOS." ORDER BY album_id, photo_id");
	$rows = dbrows($result);
	if ($rows) {
		$parts = array(); $watermark1 = ""; $watermark2 = ""; $photodir = "";
		while ($data = dbarray($result)) {
			$parts = explode(".", $data['photo_filename']);
			$watermark1 = $parts[0]."_w1.".$parts[1];
			$watermark2 = $parts[0]."_w2.".$parts[1];
			$photodir = PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : "");
			if (file_exists($photodir.$watermark1)) unlink ($photodir.$watermark1);
			if (file_exists($photodir.$watermark2)) unlink ($photodir.$watermark2);
			unset($parts);
		}
		redirect(FUSION_SELF.$aidlink);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} else if (isset($_POST['savesettings'])) {
	$_POST['photo_watermark_save'] = isset($_POST['photo_watermark_save']) ? $_POST['photo_watermark_save'] : 0;
	$_POST['photo_watermark_image'] = isset($_POST['photo_watermark_image']) ? $_POST['photo_watermark_image'] : $settings['photo_watermark_image'];
	$_POST['photo_watermark_text'] = isset($_POST['photo_watermark_text']) ? $_POST['photo_watermark_text'] : 0;
	$_POST['photo_watermark_text_color1'] = isset($_POST['photo_watermark_text_color1']) ? $_POST['photo_watermark_text_color1'] : $settings['photo_watermark_text_color1'];
	$_POST['photo_watermark_text_color2'] = isset($_POST['photo_watermark_text_color2']) ? $_POST['photo_watermark_text_color2'] : $settings['photo_watermark_text_color2'];
	$_POST['photo_watermark_text_color3'] = isset($_POST['photo_watermark_text_color3']) ? $_POST['photo_watermark_text_color3'] : $settings['photo_watermark_text_color3'];
	$error = 0;
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thumb_w']) ? $_POST['thumb_w'] : "100")."' WHERE settings_name='thumb_w'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thumb_h']) ? $_POST['thumb_h'] : "100")."' WHERE settings_name='thumb_h'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_w']) ? $_POST['photo_w'] : "400")."' WHERE settings_name='photo_w'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_h']) ? $_POST['photo_h'] : "300")."' WHERE settings_name='photo_h'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_max_w']) ? $_POST['photo_max_w'] : "1800")."' WHERE settings_name='photo_max_w'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_max_h']) ? $_POST['photo_max_h'] : "1600")."' WHERE settings_name='photo_max_h'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_max_b']) ? $_POST['photo_max_b'] : "512000")."' WHERE settings_name='photo_max_b'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['thumb_compression'])."' WHERE settings_name='thumb_compression'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thumbs_per_row']) ? $_POST['thumbs_per_row'] : "4")."' WHERE settings_name='thumbs_per_row'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thumbs_per_page']) ? $_POST['thumbs_per_page'] : "12")."' WHERE settings_name='thumbs_per_page'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_watermark']) ? $_POST['photo_watermark'] : "0")."' WHERE settings_name='photo_watermark'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_watermark_save']) ? $_POST['photo_watermark_save'] : "0")."' WHERE settings_name='photo_watermark_save'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['photo_watermark_image'])."' WHERE settings_name='photo_watermark_image'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_watermark_text']) ? $_POST['photo_watermark_text'] : "0")."' WHERE settings_name='photo_watermark_text'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(preg_match("/^([0-9A-F]){6}$/i",$_POST['photo_watermark_text_color1']) ? $_POST['photo_watermark_text_color1'] : "FF6600")."' WHERE settings_name='photo_watermark_text_color1'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(preg_match("/^([0-9A-F]){6}$/i",$_POST['photo_watermark_text_color2']) ? $_POST['photo_watermark_text_color2'] : "FFFF00")."' WHERE settings_name='photo_watermark_text_color2'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(preg_match("/^([0-9A-F]){6}$/i",$_POST['photo_watermark_text_color3']) ? $_POST['photo_watermark_text_color3'] : "FFFFFF")."' WHERE settings_name='photo_watermark_text_color3'");
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
echo "<td width='50%' class='tbl'>".$locale['601']."<br /><span class='small2'>".$locale['604']."</span></td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='thumb_w' value='".$settings2['thumb_w']."' maxlength='3' class='textbox' style='width:40px;' /> x\n";
echo "<input type='text' name='thumb_h' value='".$settings2['thumb_h']."' maxlength='3' class='textbox' style='width:40px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['602']."<br /><span class='small2'>".$locale['604']."</span></td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='photo_w' value='".$settings2['photo_w']."' maxlength='3' class='textbox' style='width:40px;' /> x\n";
echo "<input type='text' name='photo_h' value='".$settings2['photo_h']."' maxlength='3' class='textbox' style='width:40px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['603']."<br /><span class='small2'>".$locale['604']."</span></td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='photo_max_w' value='".$settings2['photo_max_w']."' maxlength='4' class='textbox' style='width:40px;' /> x\n";
echo "<input type='text' name='photo_max_h' value='".$settings2['photo_max_h']."' maxlength='4' class='textbox' style='width:40px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['605']."</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='photo_max_b' value='".$settings2['photo_max_b']."' maxlength='10' class='textbox' style='width:100px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['606']."</td>\n";
echo "<td width='50%' class='tbl'><select name='thumb_compression' class='textbox'>\n";
echo "<option value='gd1'".($settings2['thumb_compression'] == "gd1" ? " selected='selected'" : "").">".$locale['607']."</option>\n";
echo "<option value='gd2'".($settings2['thumb_compression'] == "gd2" ? " selected='selected'" : "").">".$locale['608']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['609']."</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='thumbs_per_row' value='".$settings2['thumbs_per_row']."' maxlength='2' class='textbox' style='width:40px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['610']."</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='thumbs_per_page' value='".$settings2['thumbs_per_page']."' maxlength='2' class='textbox' style='width:40px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['611']."</td>\n";
echo "<td width='50%' class='tbl'><select name='photo_watermark' class='textbox' onchange=\"Watermark(this);\">\n";
echo "<option value='1'".($settings2['photo_watermark'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['photo_watermark'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['617']."<br  /><span class='small2'>".$locale['618']."</span></td>\n";
echo "<td width='50%' class='tbl'><select name='photo_watermark_save' class='textbox' ".(!$settings2['photo_watermark'] ? "disabled='disabled'" : "").">\n";
echo "<option value='1'".($settings2['photo_watermark_save'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['photo_watermark_save'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select>&nbsp;<input class='button' type='submit' name='delete_watermarks' value='".$locale['619']."' onclick=\"javascript:return confirm('".$locale['620']."');\" ".(!$settings2['photo_watermark'] ? "disabled='disabled'" : "")." /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['612']."</td>\n";
echo "<td width='50%' class='tbl'><input type='text' name='photo_watermark_image' value='".$settings2['photo_watermark_image']."' class='textbox' style='width:200px;' ".(!$settings2['photo_watermark'] ? "disabled='disabled'" : "")." /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['613']."</td>\n";
echo "<td width='50%' class='tbl'><select name='photo_watermark_text' class='textbox' ".(!$settings2['photo_watermark'] ? "disabled='disabled'" : "").">\n";
echo "<option value='1'".($settings2['photo_watermark_text'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
echo "<option value='0'".($settings2['photo_watermark_text'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><div style='float:left'>".$locale['614']."</div><div id='preview_photo_watermark_text_color1' style='float:right;width:12px;height:12px;border:1px solid black;background-color:#".$settings2['photo_watermark_text_color1'].";'>&nbsp;</div></td>\n";
echo "<td width='50%' class='tbl'>".color_mapper("photo_watermark_text_color1", $settings2['photo_watermark_text_color1'])."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><div style='float:left'>".$locale['615']."</div><div id='preview_photo_watermark_text_color2' style='float:right;width:12px;height:12px;border:1px solid black;background-color:#".$settings2['photo_watermark_text_color2'].";'>&nbsp;</div></td>\n";
echo "<td width='50%' class='tbl'>".color_mapper("photo_watermark_text_color2", $settings2['photo_watermark_text_color2'])."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><div style='float:left'>".$locale['616']."</div><div id='preview_photo_watermark_text_color3' style='float:right;width:12px;height:12px;border:1px solid black;background-color:#".$settings2['photo_watermark_text_color3'].";'>&nbsp;</div></td>\n";
echo "<td width='50%' class='tbl'>".color_mapper("photo_watermark_text_color3", $settings2['photo_watermark_text_color3'])."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
echo "<script type='text/javascript'>\n";
echo "/* <![CDATA[ */\n";
echo "function Watermark(phtomrk) {\n";
echo "if (phtomrk.value == 0) {\n";
echo "document.forms['settingsform'].photo_watermark_save.value = 0;\n";
echo "document.forms['settingsform'].photo_watermark_save.disabled = true;\n";
echo "document.forms['settingsform'].delete_watermarks.disabled = true;\n";
echo "document.forms['settingsform'].photo_watermark_image.disabled = true;\n";
echo "document.forms['settingsform'].photo_watermark_text.value = 0;\n";
echo "document.forms['settingsform'].photo_watermark_text.disabled = true;\n";
echo "document.forms['settingsform'].photo_watermark_text_color1.disabled = true;\n";
echo "document.forms['settingsform'].photo_watermark_text_color2.disabled = true;\n";
echo "document.forms['settingsform'].photo_watermark_text_color3.disabled = true;\n";
echo "} else {\n";
echo "document.forms['settingsform'].photo_watermark_save.disabled = false;\n";
echo "document.forms['settingsform'].delete_watermarks.disabled = false;\n";
echo "document.forms['settingsform'].photo_watermark_image.disabled = false;\n";
echo "document.forms['settingsform'].photo_watermark_text.disabled = false;\n";
echo "document.forms['settingsform'].photo_watermark_text_color1.disabled = false;\n";
echo "document.forms['settingsform'].photo_watermark_text_color2.disabled = false;\n";
echo "document.forms['settingsform'].photo_watermark_text_color3.disabled = false;\n";
echo "}\n";
echo "}\n";
echo "/* ]]>*/\n";
echo "</script>\n";
closetable();

require_once THEMES."templates/footer.php";
?>
