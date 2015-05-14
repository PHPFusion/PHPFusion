<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_time.php
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
pageAccess('S2');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

add_breadcrumb(array('link'=>ADMIN."settings_time.php".$aidlink, 'title'=>$locale['time_settings']));

if (isset($_POST['savesettings'])) {
	$error = 0;
	if (!defined('FUSION_NULL')) {
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['shortdate'])."' WHERE settings_name='shortdate'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['longdate'])."' WHERE settings_name='longdate'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['forumdate'])."' WHERE settings_name='forumdate'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['newsdate'])."' WHERE settings_name='newsdate'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['subheaderdate'])."' WHERE settings_name='subheaderdate'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['timeoffset'])."' WHERE settings_name='timeoffset'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['serveroffset'])."' WHERE settings_name='serveroffset'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['default_timezone'])."' WHERE settings_name='default_timezone'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['week_start'])."' WHERE settings_name='week_start'");
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
$timezones = timezone_abbreviations_list();
$timezoneArray = array();
foreach ($timezones as $zones) {
	foreach ($zones as $zone) {
		if (preg_match('/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $zone['timezone_id'])) {
			if (!in_array($zone['timezone_id'], $timezoneArray)) {
				$timezoneArray[$zone['timezone_id']] = $zone['timezone_id'];
			}
		}
	}
}
unset($dummy);
unset($timezones);
$weekdayslist = explode("|", $locale['weekdays']);
$timestamp = time()+($settings2['timeoffset']*3600);
$date_opts = array();
foreach ($locale['dateformats'] as $dateformat) {
	$date_opts[$dateformat] = strftime($dateformat, $timestamp);
}
unset($dateformat);
opentable($locale['time_settings']);
echo "<div class='well'>".$locale['time_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<table class='table table-condensed table-hover table-responsive'>\n<tbody>\n<tr>\n";
echo "<td valign='top' width='40%' class='tbl'><strong>".$locale['458']." (".$locale['459']."):</strong></td>\n";
echo "<td width='60%' class='tbl'>".strftime($settings2['longdate'], (time())+($settings2['serveroffset']*3600))."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' class='tbl'><strong>".$locale['458']." (".$locale['460']."):</strong></td>\n";
echo "<td class='tbl'>".showdate("longdate", time())."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' class='tbl'><strong>".$locale['458']." (".$locale['461']."):</strong></td>\n";
echo "<td class='tbl'>".strftime($settings2['longdate'], time()+(($settings2['serveroffset']+$settings2['timeoffset'])*3600))."</td>\n";
echo "</tr>\n</tbody>";
echo "</table>\n";

echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('shortdate', $locale['451'], $date_opts, $settings2['shortdate'], array('placeholder' => $locale['455']));
echo form_select('longdate', $locale['452'], $date_opts, $settings2['longdate'], array('placeholder' => $locale['455']));
echo form_select('forumdate', $locale['453'], $date_opts, $settings2['forumdate'], array('placeholder' => $locale['455']));
echo form_select('newsdate', $locale['457'], $date_opts, $settings2['newsdate'], array('placeholder' => $locale['455']));
echo form_select('subheaderdate', $locale['454'], $date_opts, $settings2['subheaderdate'], array('placeholder' => $locale['455']));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('serveroffset',$locale['463'], $timezoneArray, $settings2['serveroffset']);
echo form_select('timeoffset', $locale['456'], $timezoneArray, $settings2['timeoffset']);
echo form_select('default_timezone', $locale['464'], $timezoneArray, $settings2['default_timezone']);
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('week_start', $locale['465'], $weekdayslist, $settings2['week_start']);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";
?>