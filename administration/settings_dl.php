<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_dl.php
| Author: Hans Kristian Flaatten (Starefossen)
| Author: Frederick MC Chan (Hien)
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
pageAccess('S11');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
require_once INCLUDES."mimetypes_include.php";
add_to_breadcrumbs(array('link'=>ADMIN.'settings_dl.php'.$aidlink, 'title'=>$locale['download_settings']));
if (isset($_POST['savesettings'])) {
	$admin_password = (isset($_POST['admin_password'])) ? form_sanitizer($_POST['admin_password'], '', 'admin_password') : '';
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "") && !defined('FUSION_NULL')) {
		$download_max_b = form_sanitizer($_POST['calc_b'], 1, 'calc_b')*form_sanitizer($_POST['calc_c'], 1000000, 'calc_c');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['calc_b']) && isnum($_POST['calc_c']) ? $download_max_b : "150000")."' WHERE settings_name='download_max_b'") : '';
		$download_types = form_sanitizer($_POST['download_types'], '', 'download_types');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$download_types' WHERE settings_name='download_types'") : '';
		$download_screen_max_w = form_sanitizer($_POST['download_screen_max_w'], 0, 'download_screen_max_w');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$download_screen_max_w' WHERE settings_name='download_screen_max_w'") : '';
		$download_screen_max_h = form_sanitizer($_POST['download_screen_max_h'], 0, 'download_screen_max_h');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$download_screen_max_h' WHERE settings_name='download_screen_max_h'") : '';
		$download_screen_max_b = form_sanitizer($_POST['calc_bb'], 200, 'calc_bb')*form_sanitizer($_POST['calc_cc'], 1000, 'calc_cc');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$download_screen_max_b' WHERE settings_name='download_screen_max_b'") : '';
		$download_thumb_max_h = form_sanitizer($_POST['download_thumb_max_h'], 100, 'download_thumb_max_h');
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['download_thumb_max_h']) ? $_POST['download_thumb_max_h'] : "100")."' WHERE settings_name='download_thumb_max_h'");
		$download_thumb_max_w = form_sanitizer($_POST['download_thumb_max_w'], 100, 'download_thumb_max_w');
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['download_thumb_max_w']) ? $_POST['download_thumb_max_w'] : "100")."' WHERE settings_name='download_thumb_max_w'");
		$download_screenshot = form_sanitizer($_POST['download_screenshot'], 0, 'download_screenshot');
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$download_screenshot' WHERE settings_name='download_screenshot'");
		if (!defined('FUSION_NULL')) {
			set_admin_pass($admin_password);
			redirect(FUSION_SELF.$aidlink."&amp;error=0");
		}
	}
}
$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
/**
 * Options for dropdown field
 */
$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
$calc_c = calculate_byte($settings2['download_max_b']);
$calc_b = $settings2['download_max_b']/$calc_c;
$calc_cc = calculate_byte($settings2['download_screen_max_b']);
$calc_bb = $settings2['download_screen_max_b']/$calc_cc;

$choice_opts = array('1' => $locale['yes'], '0' => $locale['no']);
$mime = mimeTypes();
$mime_opts = array();
foreach ($mime as $m => $Mime) {
	$ext = ".$m";
	$mime_opts[$ext] = $ext;
}

opentable($locale['download_settings']);
echo "<div class='well'>".$locale['download_description']."</div>";
if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
		if (isset($message)) {
			echo admin_message($message);
		}
	}
}

echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>";
openside('');
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='photo_w'>".$locale['934']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('download_screen_max_w', '', $settings2['download_screen_max_w'], array('class' => 'pull-left m-r-10', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('download_screen_max_h', '', $settings2['download_screen_max_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";

echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='photo_w'>".$locale['937']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('download_thumb_max_w', '', $settings2['download_thumb_max_w'], array('class' => 'pull-left m-r-10', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('download_thumb_max_h', '', $settings2['download_thumb_max_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='calc_b'>".$locale['930']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('calc_b', '', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '150px', 'max_length' => 4, 'class' => 'pull-left m-r-10'))."
	".form_select('calc_c', '', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'))."
	</div>
</div>
";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='calc_bb'>".$locale['936']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('calc_bb', '', $calc_bb, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '150px', 'max_length' => 4, 'class' => 'pull-left m-r-10'))."
	".form_select('calc_cc', '', $calc_opts, $calc_cc, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'))."
	</div>
</div>
";
closeside();
openside();
echo form_select('download_types[]', $locale['932'], $mime_opts, $settings2['download_types'], array('input_id'=>'dltype', 'error_text' => $locale['error_type'], 'placeholder' => $locale['choose'], 'multiple' => 1, 'width' => '100%', 'delimiter' => '|'));
closeside();
echo "</div><div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('download_screenshot', $locale['938'], $choice_opts, $settings2['download_screenshot']);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";


function calculate_byte($download_max_b) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($download_max_b/$byte <= 999) {
			return $byte;
		}
	}
	return 1000000;
}
