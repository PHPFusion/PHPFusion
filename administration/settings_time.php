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

if (!checkrights("S2") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

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
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['shortdate'])."' WHERE settings_name='shortdate'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['longdate'])."' WHERE settings_name='longdate'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['forumdate'])."' WHERE settings_name='forumdate'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['newsdate'])."' WHERE settings_name='newsdate'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['subheaderdate'])."' WHERE settings_name='subheaderdate'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['timeoffset'])."' WHERE settings_name='timeoffset'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['serveroffset'])."' WHERE settings_name='serveroffset'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['default_timezone'])."' WHERE settings_name='default_timezone'");
	if (!$result) { $error = 1; }

	redirect(FUSION_SELF.$aidlink."&error=".$error);
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

$offset_array = array(
	"-12.0" => "(GMT -12:00) Eniwetok, Kwajalein",
	"-11.0" => "(GMT -11:00) Midway Island, Samoa",
	"-10.0" => "(GMT -10:00) Hawaii",
	"-9.0" => "(GMT -9:00) Alaska",
	"-8.0" => "(GMT -8:00) Pacific Time (US &amp; Canada)",
	"-7.0" => "(GMT -7:00) Mountain Time (US &amp; Canada)",
	"-6.0" => "(GMT -6:00) Central Time (US &amp; Canada), Mexico City",
	"-5.0" => "(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima",
	"-4.0" => "(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz",
	"-3.5" => "(GMT -3:30) Newfoundland",
	"-3.0" => "(GMT -3:00) Brazil, Buenos Aires, Georgetown",
	"-2.0" => "(GMT -2:00) Mid-Atlantic",
	"-1.0" => "(GMT -1:00 hour) Azores, Cape Verde Islands",
	"0.0" => "(GMT) Western Europe Time, London, Lisbon, Casablanca",
	"1.0" => "(GMT +1:00) Brussels, Copenhagen, Madrid, Paris",
	"2.0" => "(GMT +2:00) Kaliningrad, South Africa",
	"3.0" => "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg",
	"3.5" => "(GMT +3:30) Tehran",
	"4.0" => "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi",
	"4.5" => "(GMT +4:30) Kabul",
	"5.0" => "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
	"5.5" => "(GMT +5:30) Bombay, Calcutta, Madras, New Delhi",
	"5.75" => "(GMT +5:45) Kathmandu",
	"6.0" => "(GMT +6:00) Almaty, Dhaka, Colombo",
	"7.0" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
	"8.0" => "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong",
	"9.0" => "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
	"9.5" => "(GMT +9:30) Adelaide, Darwin",
	"10.0" => "(GMT +10:00) Eastern Australia, Guam, Vladivostok",
	"11.0" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
	"12.0" => "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka"
);

$offsetsite = ""; $offsetserver = "";
foreach ($offset_array as $key => $offset) {
	$sel1 = ($settings2['timeoffset'] == $key ? " selected='selected'" : "");
	$sel2 = ($settings2['serveroffset'] == $key ? " selected='selected'" : "");
	$offsetsite .= "<option value='".$key."'".$sel1.">".$offset."</option>\n";
	$offsetserver .= "<option".$sel2.">".$key."</option>\n";
}

$timezones = timezone_abbreviations_list();
$timezoneArray = array();
foreach($timezones as $zones) {
    foreach($zones as $zone) {
		if (preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $zone['timezone_id'])) {
			if (!in_array($zone['timezone_id'], $timezoneArray)) {
				$timezoneArray[] = $zone['timezone_id'];
			}
		}
	}
}

unset($dummy); unset($timezones);
sort($timezoneArray);

$timezoneOptions = "";
foreach ($timezoneArray AS $timezone) {
	$timezoneOptions .= "<option ".($settings2['default_timezone'] == $timezone ? "selected='selected'" : "").">".$timezone."</option>\n";
}

$timestamp = time()+($settings2['timeoffset']*3600);
$date_opts = "<option value=''>".$locale['455']."</option>\n";
foreach($locale['dateformats'] as $dateformat) {
	$date_opts .= "<option value='".$dateformat."'>".strftime($dateformat, $timestamp)."</option>\n";
}
unset($dateformat);

opentable($locale['400']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='500' class='center'>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['458']." (".$locale['459']."):</td>\n";
echo "<td width='50%' class='tbl'>".strftime($settings2['longdate'], (time())+($settings2['serveroffset']*3600))."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['458']." (".$locale['460']."):</td>\n";
echo "<td width='50%' class='tbl'>".showdate("longdate", time())."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['458']." (".$locale['461']."):</td>\n";
echo "<td width='50%' class='tbl'>".strftime($settings2['longdate'], time()+(($settings2['serveroffset']+$settings2['timeoffset'])*3600))."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'>".$locale['400']." - ".$locale['450']."</td>";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['451']."</td>\n";
echo "<td width='50%' class='tbl'><select name='shortdatetext' class='textbox' style='width:201px;'>\n".$date_opts."</select><br />\n";
echo "<input type='button' name='setshortdate' value='>>' onclick=\"shortdate.value=shortdatetext.options[shortdatetext.selectedIndex].value;shortdatetext.selectedIndex=0;\" class='button' />\n";
echo "<input type='text' name='shortdate' value='".$settings2['shortdate']."' maxlength='50' class='textbox' style='width:180px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['452']."</td>\n";
echo "<td width='50%' class='tbl'><select name='longdatetext' class='textbox' style='width:201px;'>\n".$date_opts."</select><br />\n";
echo "<input type='button' name='setlongdate' value='>>' onclick=\"longdate.value=longdatetext.options[longdatetext.selectedIndex].value;longdatetext.selectedIndex=0;\" class='button' />\n";
echo "<input type='text' name='longdate' value='".$settings2['longdate']."' maxlength='50' class='textbox' style='width:180px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['453']."</td>\n";
echo "<td width='50%' class='tbl'><select name='forumdatetext' class='textbox' style='width:201px;'>\n".$date_opts."</select><br />\n";
echo "<input type='button' name='setforumdate' value='>>' onclick=\"forumdate.value=forumdatetext.options[forumdatetext.selectedIndex].value;forumdatetext.selectedIndex=0;\" class='button' />\n";
echo "<input type='text' name='forumdate' value='".$settings2['forumdate']."' maxlength='50' class='textbox' style='width:180px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['457']."</td>\n";
echo "<td width='50%' class='tbl'><select name='newsdatetext' class='textbox' style='width:201px;'>\n".$date_opts."</select><br />\n";
echo "<input type='button' name='setnewsate' value='>>' onclick=\"newsdate.value=newsdatetext.options[newsdatetext.selectedIndex].value;newsdatetext.selectedIndex=0;\" class='button' />\n";
echo "<input type='text' name='newsdate' value='".$settings2['newsdate']."' maxlength='50' class='textbox' style='width:180px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'>".$locale['454']."</td>\n";
echo "<td width='50%' class='tbl'><select name='subheaderdatetext' class='textbox' style='width:201px;'>\n".$date_opts."</select><br />\n";
echo "<input type='button' name='setsubheaderdate' value='>>' onclick=\"subheaderdate.value=subheaderdatetext.options[subheaderdatetext.selectedIndex].value;subheaderdatetext.selectedIndex=0;\" class='button' />\n";
echo "<input type='text' name='subheaderdate' value='".$settings2['subheaderdate']."' maxlength='50' class='textbox' style='width:180px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['462']."<br /><span class='small'>(".$locale['463'].")</span></td>\n";
echo "<td width='50%' class='tbl'><select name='serveroffset' class='textbox' style='width:201px;'>\n".$offsetserver."</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['456']."</td>\n";
echo "<td width='50%' class='tbl'><select name='timeoffset' class='textbox' style='width:201px;'>\n".$offsetsite."</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>".$locale['464']."</td>\n";
echo "<td width='50%' class='tbl'><select name='default_timezone' class='textbox' style='width:201px;'>\n".$timezoneOptions."</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

require_once THEMES."templates/footer.php";
?>
