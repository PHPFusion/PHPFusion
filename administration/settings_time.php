<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_time.php
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
require_once "../maincore.php";
pageAccess('S2');
require_once THEMES."templates/admin_header.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_time.php'.fusion_get_aidlink(), 'title' => $locale['time_settings']]);

$settings_main = array(
    'shortdate' => fusion_get_settings('shortdate'),
    'longdate' => fusion_get_settings('longdate'),
    'forumdate' => fusion_get_settings('forumdate'),
    'newsdate' => fusion_get_settings('newsdate'),
    'subheaderdate' => fusion_get_settings('subheaderdate'),
    'timeoffset' => fusion_get_settings('timeoffset'),
    'serveroffset' => fusion_get_settings('serveroffset'),
    'default_timezone' => fusion_get_settings('default_timezone'),
    'week_start' => fusion_get_settings('week_start')
);

if (isset($_POST['savesettings'])) {
	$settings_main = array(
	    'shortdate' => form_sanitizer($_POST['shortdate'], '', 'shortdate'),
	    'longdate' => form_sanitizer($_POST['longdate'], '', 'longdate'),
	    'forumdate' => form_sanitizer($_POST['forumdate'], '', 'forumdate'),
	    'newsdate' => form_sanitizer($_POST['newsdate'], '', 'newsdate'),
	    'subheaderdate' => form_sanitizer($_POST['subheaderdate'], '', 'subheaderdate'),
	    'timeoffset' => form_sanitizer($_POST['timeoffset'], '', 'timeoffset'),
	    'serveroffset' => form_sanitizer($_POST['serveroffset'], '', 'serveroffset'),
	    'default_timezone' => form_sanitizer($_POST['default_timezone'], '', 'default_timezone'),
	    'week_start' => form_sanitizer($_POST['week_start'], 0, 'week_start')
	);

    if (\defender::safe()) {
        foreach ($settings_main as $settings_key => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_value."' WHERE settings_name='".$settings_key."'");
        }
        addNotice("success", $locale['900']);
        redirect(FUSION_SELF.fusion_get_aidlink());
    }
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
$timestamp = time() + ($settings_main['timeoffset'] * 3600);
$date_opts = array();
foreach ($locale['dateformats'] as $dateformat) {
    $date_opts[$dateformat] = showdate($dateformat, $timestamp);
}
unset($dateformat);
opentable($locale['time_settings']);
echo "<div class='well'>".$locale['time_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_SELF.fusion_get_aidlink());
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-4'>\n";
echo "<div class='panel-body text-left'><strong>".$locale['458']." (".$locale['459']."):</strong></div>\n";
echo "<div class='panel-body text-left'><strong>".$locale['458']." (".$locale['460']."):</strong></div>\n";
echo "<div class='panel-body text-left'><strong>".$locale['458']." (".$locale['461']."):</strong></div>\n";
echo "</div>\n";

echo "<div class='col-xs-12 col-sm-12 col-md-8'>\n";
echo "<div class='panel-body text-left'>".showdate($settings_main['longdate'], (time()) + ($settings_main['serveroffset'] * 3600))."</div>\n";
echo "<div class='panel-body text-left'>".showdate($settings_main['longdate'], time())."</div>\n";
echo "<div class='panel-body text-left'>".showdate($settings_main['longdate'], time() + (($settings_main['serveroffset'] + $settings_main['timeoffset']) * 3600))."</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";

openside('');
echo form_select('shortdate', $locale['451'], $settings_main['shortdate'], array(
    'options' => $date_opts,
    'placeholder' => $locale['455']
));
echo form_select('longdate', $locale['452'], $settings_main['longdate'], array(
    'options' => $date_opts,
    'placeholder' => $locale['455']
));
echo form_select('forumdate', $locale['453'], $settings_main['forumdate'], array(
    'options' => $date_opts,
    'placeholder' => $locale['455']
));
echo form_select('newsdate', $locale['457'], $settings_main['newsdate'], array(
    'options' => $date_opts,
    'placeholder' => $locale['455']
));
echo form_select('subheaderdate', $locale['454'], $settings_main['subheaderdate'], array(
    'options' => $date_opts,
    'placeholder' => $locale['455']
));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('serveroffset', $locale['463'], $settings_main['serveroffset'], array("options" => $timezoneArray));
echo form_select('timeoffset', $locale['456'], $settings_main['timeoffset'], array("options" => $timezoneArray));
echo form_select('default_timezone', $locale['464'], $settings_main['default_timezone'], array("options" => $timezoneArray));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('week_start', $locale['465'], $settings_main['week_start'], array("options" => $weekdayslist));
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";
