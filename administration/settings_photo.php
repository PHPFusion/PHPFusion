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

if (!checkrights("S5") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
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
	for ($ca = 0; $ca < count($cvalue); $ca++) {
		for ($cb = 0; $cb < count($cvalue); $cb++) {
			for ($cc = 0; $cc < count($cvalue); $cc++) {
				$hcolor = $cvalue[$ca].$cvalue[$cb].$cvalue[$cc];
				$select .= "<option value='".$hcolor."'".($value == $hcolor ? " selected='selected' " : " ")."style='background-color:#".$hcolor.";'>#".$hcolor."</option>\n";
			}
		}
	}
	$select .= "</select>\n";
	return $select;
}

if (isset($_POST['delete_watermarks'])) {
	define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
	$result = dbquery("SELECT album_id,photo_filename FROM ".DB_PHOTOS." ORDER BY album_id, photo_id");
	$rows = dbrows($result);
	if ($rows) {
		$parts = array();
		$watermark1 = "";
		$watermark2 = "";
		$photodir = "";
		while ($data = dbarray($result)) {
			$parts = explode(".", $data['photo_filename']);
			$watermark1 = $parts[0]."_w1.".$parts[1];
			$watermark2 = $parts[0]."_w2.".$parts[1];
			$photodir = PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : "");
			if (file_exists($photodir.$watermark1)) unlink($photodir.$watermark1);
			if (file_exists($photodir.$watermark2)) unlink($photodir.$watermark2);
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
	if (!defined('FUSION_NULL')) {
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thumb_w']) ? $_POST['thumb_w'] : "100")."' WHERE settings_name='thumb_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thumb_h']) ? $_POST['thumb_h'] : "100")."' WHERE settings_name='thumb_h'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_w']) ? $_POST['photo_w'] : "400")."' WHERE settings_name='photo_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_h']) ? $_POST['photo_h'] : "300")."' WHERE settings_name='photo_h'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_max_w']) ? $_POST['photo_max_w'] : "1800")."' WHERE settings_name='photo_max_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_max_h']) ? $_POST['photo_max_h'] : "1600")."' WHERE settings_name='photo_max_h'");
		if (!$result) {
			$error = 1;
		}
		$photo_max_b = form_sanitizer($_POST['calc_b'], '512', 'calc_b')*form_sanitizer($_POST['calc_c'], '100000', 'calc_c');
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$photo_max_b' WHERE settings_name='photo_max_b'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['thumb_compression'])."' WHERE settings_name='thumb_compression'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thumbs_per_row']) ? $_POST['thumbs_per_row'] : "4")."' WHERE settings_name='thumbs_per_row'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['thumbs_per_page']) ? $_POST['thumbs_per_page'] : "12")."' WHERE settings_name='thumbs_per_page'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_watermark']) ? $_POST['photo_watermark'] : "0")."' WHERE settings_name='photo_watermark'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_watermark_save']) ? $_POST['photo_watermark_save'] : "0")."' WHERE settings_name='photo_watermark_save'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['photo_watermark_image'])."' WHERE settings_name='photo_watermark_image'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['photo_watermark_text']) ? $_POST['photo_watermark_text'] : "0")."' WHERE settings_name='photo_watermark_text'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(preg_match("/^([0-9A-F]){6}$/i", $_POST['photo_watermark_text_color1']) ? $_POST['photo_watermark_text_color1'] : "FF6600")."' WHERE settings_name='photo_watermark_text_color1'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(preg_match("/^([0-9A-F]){6}$/i", $_POST['photo_watermark_text_color2']) ? $_POST['photo_watermark_text_color2'] : "FFFF00")."' WHERE settings_name='photo_watermark_text_color2'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(preg_match("/^([0-9A-F]){6}$/i", $_POST['photo_watermark_text_color3']) ? $_POST['photo_watermark_text_color3'] : "FFFFFF")."' WHERE settings_name='photo_watermark_text_color3'");
		if (!$result) {
			$error = 1;
		}
		redirect(FUSION_SELF.$aidlink."&error=".$error);
	}
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

opentable($locale['400']);

echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));

echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
echo "<div class='clearfix'>\n";
echo "<label for='thumb_w'>".$locale['601']."</label>\n<span class='small2'>".$locale['604']."</span>\n<br/>\n";
echo form_text('', 'thumb_w', 'thumb_w', $settings2['thumb_w'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1));
echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
echo form_text('', 'thumb_h', 'thumb_h', $settings2['thumb_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1));
echo "</div>\n";
echo "<div class='clearfix'>\n";
echo "<label for='photo_w'>".$locale['602']."</label>\n<span class='small2'>".$locale['604']."</span>\n<br/>\n";
echo form_text('', 'photo_w', 'photo_w', $settings2['photo_w'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1));
echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
echo form_text('', 'photo_h', 'photo_h', $settings2['photo_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1));
echo "</div>\n";
echo "<div class='clearfix'>\n";
echo "<label for='photo_max_w'>".$locale['603']."</label>\n<span class='small2'>".$locale['604']."</span>\n<br/>\n";
echo form_text('', 'photo_max_w', 'photo_max_w', $settings2['photo_max_w'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1));
echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
echo form_text('', 'photo_max_h', 'photo_max_h', $settings2['photo_max_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1));
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='calc_b'>".$locale['605']."</label><br/>\n";

function calculate_byte($download_max_b) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($download_max_b/$byte <= 999) {
			return $byte;
		}
	}
	return 1000000;
}

$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
$calc_c = calculate_byte($settings2['photo_max_b']);
$calc_b = $settings2['photo_max_b']/$calc_c;

echo form_text('', 'calc_b', 'calc_b', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '100px', 'max_length' => '4', 'class' => 'pull-left m-r-10'));
echo form_select('', 'calc_c', 'calc_c', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'));
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='thumb_compression'>".$locale['606']."</label>\n<br/>\n";
$opts = array('gd1' => $locale['607'], 'gd2' => $locale['608']);
echo form_select('', 'thumb_compression', 'thumb_compression', $opts, $settings2['thumb_compression']);
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='thumbs_per_row'>".$locale['609']."</label>\n<br/>\n";
echo form_text('', 'thumbs_per_row', 'thumbs_per_row', $settings2['thumbs_per_row'], array('max_length' => 2));
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='thumbs_per_page'>".$locale['610']."</label><br/>\n";
echo form_text('', 'thumbs_per_page', 'thumbs_per_page', $settings2['thumbs_per_page'], array('max_length' => 2));
echo "</div>\n";

echo "<div class='clearfix'>\n";
$yes_no_array = array('1' => $locale['518'], '0' => $locale['519']);
echo "<label for='photo_watermark'>".$locale['611']."</label>\n<br/>\n";
echo form_select('', 'photo_watermark', 'photo_watermark', $yes_no_array, $settings2['photo_watermark']);
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='photo_watermark_save'>".$locale['617']."</label>\n<span class='small2'>".$locale['618']."</span>\n<br/>\n";
echo form_select('', 'photo_watermark_save', 'photo_watermark_save', $yes_no_array, $settings2['photo_watermark']);
echo form_button($locale['619'], 'delete_watermarks', 'delete_watermarks', $locale['619'], array('deactivate' => !$settings2['photo_watermark'] ? 1 : 0, 'class' => 'm-t-10 m-b-10 btn-primary'));
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='photo_watermark_image'>".$locale['612']."</label><br/>\n";
echo form_text('', 'photo_watermark_image', 'photo_watermark_image', $settings2['photo_watermark_image'], array('deactivate' => !$settings2['photo_watermark'] ? 1 : 0));
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='photo_watermark_text'>".$locale['613']."</label><br/>\n";
echo form_select('', 'photo_watermark_text', 'photo_watermark_text', $yes_no_array, $settings2['photo_watermark_text'], array('deactivate' => !$settings2['photo_watermark'] ? 1 : 0));
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='photo_watermark_text_color1'>".$locale['614']."</label><br/>\n";
echo form_colorpicker('', 'photo_watermark_text_color1', 'photo_watermark_text_color1', $settings2['photo_watermark_text_color1'], array('deactivate' => !$settings2['photo_watermark'] ? 1 : 0));
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='photo_watermark_text_color2'>".$locale['615']."</label><br/>\n";
echo form_colorpicker('', 'photo_watermark_text_color2', 'photo_watermark_text_color2', $settings2['photo_watermark_text_color2'], array('deactivate' => !$settings2['photo_watermark'] ? 1 : 0));
echo "</div>\n";

echo "<div class='clearfix'>\n";
echo "<label for='photo_watermark_text_color3'>".$locale['616']."</label><br/>\n";
echo form_colorpicker('', 'photo_watermark_text_color3', 'photo_watermark_text_color3', $settings2['photo_watermark_text_color3'], array('deactivate' => !$settings2['photo_watermark'] ? 1 : 0));
echo "</div>\n";
echo "</div></div>\n";

echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
echo closeform();

closetable();
add_to_jquery("
        $('#photo_watermark').bind('change', function(){
        var vals = $(this).select2().val();
        if (vals == 1) {
            $('#photo_watermark_save').select2('enable');
            $('#delete_watermarks').removeAttr('disabled');
            $('#photo_watermark_image').removeAttr('disabled');
            $('#photo_watermark_text').select2('enable');
            $('#photo_watermark_text_color1').colorpicker('enable');
            $('#photo_watermark_text_color2').colorpicker('enable');
            $('#photo_watermark_text_color3').colorpicker('enable');
        } else {
            $('#photo_watermark_save').select2('disable');
            $('#delete_watermarks').attr('disabled', 'disabled');
            $('#photo_watermark_image').attr('disabled', 'disabled');
            $('#photo_watermark_text').select2('disable');
            $('#photo_watermark_text_color1').colorpicker('disable');
            $('#photo_watermark_text_color2').colorpicker('disable');
            $('#photo_watermark_text_color3').colorpicker('disable');
        }
        });
    ");
require_once THEMES."templates/footer.php";
?>
