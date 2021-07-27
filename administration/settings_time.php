<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_time.php
| Author: Core Development Team
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
require_once THEMES.'templates/admin_header.php';
pageaccess('S2');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'settings_time.php'.fusion_get_aidlink(), 'title' => $locale['time_settings']]);

if (check_post('savesettings')) {
    $inputData = [
        'shortdate'  => sanitizer('shortdate', '', 'shortdate'),
        'longdate'   => sanitizer('longdate', '', 'longdate'),
        'forumdate'  => sanitizer('forumdate', '', 'forumdate'),
        'newsdate'   => sanitizer('newsdate', '', 'newsdate'),
        'timeoffset' => sanitizer('timeoffset', '', 'timeoffset'),
        'week_start' => sanitizer('week_start', 0, 'week_start')
    ];

    if (fusion_safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        addnotice("success", $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

$json_file = @file_get_contents(INCLUDES.'geomap/timezones.json', FALSE);
$timezones_json = json_decode($json_file, TRUE);

$timezone_array = [];
foreach ($timezones_json as $zone => $zone_city) {
    $date = new DateTime(NULL, new DateTimeZone($zone));
    $offset = $date->getOffset() / 3600;
    $timezone_array[$zone] = '(GMT'.($offset < 0 ? $offset : '+'.$offset).') '.$zone_city;
}

$weekdayslist = explode("|", $locale['weekdays']);

$date_opts = [];
foreach ($locale['dateformats'] as $dateformat) {
    $date_opts[$dateformat] = showdate($dateformat, time());
}
unset($dateformat);
opentable($locale['time_settings']);
echo "<div class='well'>".$locale['time_description']."</div>\n";

echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n";

echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo '<span class="strong">'.$locale['458'].' ('.$locale['464'].')</span>';
echo '<span class="pull-right">'.showdate($settings['longdate'], time(), ['tz_override' => $settings['timeoffset']]).'</span>';
closeside();

openside('');
echo form_select('timeoffset', $locale['464'], $settings['timeoffset'], ['options' => $timezone_array]);
closeside();

openside('');
echo form_select('week_start', $locale['465'], $settings['week_start'], ['options' => $weekdayslist]);
closeside();
echo "</div>\n";

echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('shortdate_select', $locale['451'], $settings['shortdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
echo form_text('shortdate', '', $settings['shortdate']);
closeside();

openside('');
echo form_select('longdate_select', $locale['452'], $settings['longdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
echo form_text('longdate', '', $settings['longdate']);
closeside();

openside('');
echo form_select('forumdate_select', $locale['453'], $settings['forumdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
echo form_text('forumdate', '', $settings['forumdate']);
closeside();

openside('');
echo form_select('newsdate_select', $locale['457'], $settings['newsdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['455']
]);
echo form_text('newsdate', '', $settings['newsdate']);
closeside();

echo "</div>\n";

add_to_jquery('
$("[id*=\'_select\']").change(function () {
    $("#" + $(this).attr("id").replace("_select", "")).val($(this).val());
})
');

echo "</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
