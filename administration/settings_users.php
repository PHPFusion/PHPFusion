<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_users.php
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
pageAccess('S9');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
add_breadcrumb(array('link' => ADMIN."settings_user.php".$aidlink, 'title' => $locale['user_settings']));
if (isset($_POST['savesettings'])) {
	$error = 0;
	if (!defined('FUSION_NULL')) {
		if ($_POST['enable_deactivation'] == '0') {
			$result = dbquery("UPDATE ".DB_USERS." SET user_status='0' WHERE user_status='5'");
			if (!$result) {
				$error = 1;
			}
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['enable_deactivation']) ? $_POST['enable_deactivation'] : "0")."' WHERE settings_name='enable_deactivation'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['deactivation_period']) ? $_POST['deactivation_period'] : "365")."' WHERE settings_name='deactivation_period'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['deactivation_response']) ? $_POST['deactivation_response'] : "14")."' WHERE settings_name='deactivation_response'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['deactivation_action']) ? $_POST['deactivation_action'] : "0")."' WHERE settings_name='deactivation_action'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['hide_userprofiles']) ? $_POST['hide_userprofiles'] : "0")."' WHERE settings_name='hide_userprofiles'");
		if (!$result) {
			$error = 1;
		}
		$avatar_filesize = form_sanitizer($_POST['calc_b'], '15', 'calc_b')*form_sanitizer($_POST['calc_c'], '100000', 'calc_c');
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$avatar_filesize' WHERE settings_name='avatar_filesize'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['avatar_width']) ? $_POST['avatar_width'] : "100")."' WHERE settings_name='avatar_width'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['avatar_height']) ? $_POST['avatar_height'] : "100")."' WHERE settings_name='avatar_height'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['avatar_ratio']) ? $_POST['avatar_ratio'] : "0")."' WHERE settings_name='avatar_ratio'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['userNameChange']) ? $_POST['userNameChange'] : "0")."' WHERE settings_name='userNameChange'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['userthemes']) ? $_POST['userthemes'] : "0")."' WHERE settings_name='userthemes'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['multiple_logins']) ? $_POST['multiple_logins'] : "0")."' WHERE settings_name='multiple_logins'");
		if (!$result) {
			$error = 1;
		}
		if ($error) {
			addNotice('danger', $locale['901']);
		} else {
			addNotice('success', $locale['900']);
		}
		redirect(FUSION_SELF.$aidlink);
	}
}
$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
opentable($locale['user_settings']);
echo "<div class='well'>".$locale['user_description']."</div>";
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
openside('');
$choice_opts = array('0' => $locale['no'], '1' => $locale['yes']);
echo form_select('enable_deactivation', $locale['1002'], $settings2['enable_deactivation'], array("options" => $choice_opts));
echo form_text('deactivation_period', $locale['1003'], $settings2['deactivation_period'], array('max_length' => 3,
	'width' => '100px',
	'number' => 1));
echo "<span class='text-smaller mid-opacity display-block m-b-10'>(".$locale['1004'].")</span>";
echo form_text('deactivation_response', $locale['1005'], $settings2['deactivation_response'], array('max_length' => 3,
	'width' => '100px',
	'number' => 1));
echo "<span class='text-smaller mid-opacity display-block m-b-10'>(".$locale['1006'].")</span>";
$action_opts = array('0' => $locale['1012'], '1' => $locale['1013']);
echo form_select('deactivation_action', $locale['1011'], $settings2['deactivation_action'], array("options" => $action_opts));
closeside();
openside('');
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='photo_max_w'>".$locale['1008']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('avatar_width', '', $settings2['avatar_width'], array('class' => 'pull-left m-r-10',
		'max_length' => 4,
		'number' => 1,
		'width' => '150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('avatar_height', '', $settings2['avatar_height'], array('class' => 'pull-left',
		'max_length' => 4,
		'number' => 1,
		'width' => '150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
$calc_c = calculate_byte($settings2['avatar_filesize']);
$calc_b = $settings2['avatar_filesize']/$calc_c;
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='calc_b'>".$locale['605']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('calc_b', '', $calc_b, array('required' => 1,
		'number' => 1,
		'error_text' => $locale['error_rate'],
		'width' => '150px',
		'max_length' => 4,
		'class' => 'pull-left m-r-10'))."
	".form_select('calc_c', '', $calc_c, array('options' => $calc_opts,
		'placeholder' => $locale['choose'],
		'class' => 'pull-left',
		'width' => '180px'))."
	</div>
</div>
";
$ratio_opts = array('0' => $locale['955'], '1' => $locale['956']);
echo form_select('avatar_ratio', $locale['1001'], $settings2['avatar_ratio'], array('options' => $ratio_opts,
	'inline' => 1,
	'width' => '100%'));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('hide_userprofiles', $locale['673'], $settings2['hide_userprofiles'], array('options' => $choice_opts));
closeside();
openside('');
echo form_select('userNameChange', $locale['691'], $settings2['userNameChange'], array("options" => $choice_opts));
echo form_select('userthemes', $locale['668'], $settings2['userthemes'], array("options" => $choice_opts));
echo form_select('multiple_logins', $locale['1014'], $settings2['multiple_logins'], array("options" => $choice_opts));
echo "<span class='text-smaller mid-opacity display-block m-b-10'>".$locale['1014a']."</span>\n";
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
