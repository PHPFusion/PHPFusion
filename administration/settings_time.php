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

$settings_main = [
    'shortdate'        => fusion_get_settings('shortdate'),
    'longdate'         => fusion_get_settings('longdate'),
    'forumdate'        => fusion_get_settings('forumdate'),
    'newsdate'         => fusion_get_settings('newsdate'),
    'subheaderdate'    => fusion_get_settings('subheaderdate'),
    'timeoffset'       => fusion_get_settings('timeoffset'),
    'serveroffset'     => fusion_get_settings('serveroffset'),
    'default_timezone' => fusion_get_settings('default_timezone'),
    'week_start'       => fusion_get_settings('week_start')
];

if (isset($_POST['savesettings'])) {
    $settings_main = [
        'shortdate'        => form_sanitizer($_POST['shortdate'], '', 'shortdate'),
        'longdate'         => form_sanitizer($_POST['longdate'], '', 'longdate'),
        'forumdate'        => form_sanitizer($_POST['forumdate'], '', 'forumdate'),
        'newsdate'         => form_sanitizer($_POST['newsdate'], '', 'newsdate'),
        'subheaderdate'    => form_sanitizer($_POST['subheaderdate'], '', 'subheaderdate'),
        'timeoffset'       => form_sanitizer($_POST['timeoffset'], '', 'timeoffset'),
        'serveroffset'     => form_sanitizer($_POST['serveroffset'], '', 'serveroffset'),
        'default_timezone' => form_sanitizer($_POST['default_timezone'], '', 'default_timezone'),
        'week_start'       => form_sanitizer($_POST['week_start'], 0, 'week_start')
    ];

    if (\defender::safe()) {
        foreach ($settings_main as $settings_key => $settings_value) {
            $data = [
                'settings_name'  => $settings_key,
                'settings_value' => $settings_value
            ];
            dbquery_insert(DB_SETTINGS, $data, 'update', array('primary_key' => 'settings_name'));
        }
        addNotice("success", $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

$timezones = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA | DateTimeZone::AFRICA | DateTimeZone::ARCTIC | DateTimeZone::ASIA | DateTimeZone::ATLANTIC | DateTimeZone::EUROPE | DateTimeZone::INDIAN | DateTimeZone::PACIFIC); //gives both african and american time zones

foreach ($timezones as $zone) {
    $zone = explode('/', $zone); // 0 => Continent, 1 => City
    if (!empty($zone[1])) {
        $timezoneArray[$zone[0].'/'.$zone[1]] = str_replace('_', ' ', $zone[1]); // Creates array(DateTimeZone => 'Friendly name')
    }
}

$weekdayslist = explode("|", $locale['weekdays']);

$date_opts = array();
foreach ($locale['dateformats'] as $dateformat) {
    $date_opts[$dateformat] = showdate($dateformat, TIME);
}
unset($dateformat);
opentable($locale['time_settings']);
echo "<div class='well'>".$locale['time_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-4'>\n";
echo "<div class='panel-body text-left'><strong>".$locale['458']." (".$locale['459'].")</strong></div>\n";
echo "<div class='panel-body text-left'><strong>".$locale['458']." (".$locale['460'].")</strong></div>\n";
echo "<div class='panel-body text-left'><strong>".$locale['458']." (".$locale['461'].")</strong></div>\n";
echo "<div class='panel-body text-left'><strong>".$locale['458']." (".$locale['466'].")</strong></div>\n";
echo "</div>\n";

echo "<div class='col-xs-12 col-sm-12 col-md-8'>\n";
echo "<div class='panel-body text-left'>".showdate($settings_main['longdate'], TIME, ['tz_override' => $settings_main['serveroffset']])."</div>\n";
echo "<div class='panel-body text-left'>";
if (column_exists('users', 'user_timezone')) {
    echo showdate($settings_main['longdate'], TIME, ['tz_override' => fusion_get_userdata('user_timezone')]);
} else {
    echo $locale['na'];
}
echo "</div>\n";
echo "<div class='panel-body text-left'>".showdate($settings_main['longdate'], TIME, ['tz_override' => $settings_main['timeoffset']])."</div>\n";
echo "<div class='panel-body text-left'>".showdate($settings_main['longdate'], TIME, ['tz_override' => $settings_main['default_timezone']])."</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";

openside('');
echo form_select('shortdate', $locale['451'], $settings_main['shortdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
echo form_select('longdate', $locale['452'], $settings_main['longdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
echo form_select('forumdate', $locale['453'], $settings_main['forumdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
echo form_select('newsdate', $locale['457'], $settings_main['newsdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
echo form_select('subheaderdate', $locale['454'], $settings_main['subheaderdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('serveroffset', $locale['463'], $settings_main['serveroffset'], ['options' => $timezoneArray]);
echo form_select('timeoffset', $locale['456'], $settings_main['timeoffset'], ['options' => $timezoneArray]);
echo form_select('default_timezone', $locale['464'], $settings_main['default_timezone'], ['options' => $timezoneArray]);
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('week_start', $locale['465'], $settings_main['week_start'], ['options' => $weekdayslist]);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES."templates/footer.php";
