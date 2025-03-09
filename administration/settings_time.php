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

add_breadcrumb(['link' => ADMIN.'settings_time.php'.fusion_get_aidlink(), 'title' => $locale['admins_time_settings']]);

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

        addnotice("success", $locale['admins_900']);
        redirect(FUSION_REQUEST);
    }
}

$json_file = @file_get_contents(INCLUDES.'geomap/timezones.json', FALSE);
$timezones_json = json_decode($json_file, TRUE);

$timezone_array = [];
foreach ($timezones_json as $zone => $zone_city) {
    $date = new DateTime('now', new DateTimeZone($zone));
    $offset = $date->getOffset() / 3600;
    $timezone_array[$zone] = '(GMT'.($offset < 0 ? $offset : '+'.$offset).') '.$zone_city;
}

$weekdayslist = explode("|", $locale['weekdays']);

$date_opts = [];
foreach ($locale['dateformats'] as $dateformat) {
    $date_opts[$dateformat] = showdate($dateformat, time());
}
unset($dateformat);
opentable($locale['admins_time_settings']);
echo "<div class='well'>".$locale['admins_time_description']."</div>\n";

echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";

openside('');
echo '<span class="strong">'.$locale['admins_458'].' ('.$locale['admins_464'].')</span>';
echo '<span class="pull-right">'.showdate($settings['longdate'], time(), ['tz_override' => $settings['timeoffset']]).'</span>';
closeside();

openside('');
echo form_select('timeoffset', $locale['admins_464'], $settings['timeoffset'], [
    'options' => $timezone_array, 'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE
]);
closeside();

openside('');
echo form_select('week_start', $locale['admins_465'], $settings['week_start'], [
    'options' => $weekdayslist, 'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE
]);
closeside();
echo "</div>\n";

echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_select('shortdate_select', $locale['admins_451'], $settings['shortdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['admins_455'],
    'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE
]);
echo form_text('shortdate', '', $settings['shortdate'], ['deactivate' => TRUE, 'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE]);
closeside();

openside('');
echo form_select('longdate_select', $locale['admins_452'], $settings['longdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['admins_455'],
    'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE
]);
echo form_text('longdate', '', $settings['longdate'], ['deactivate' => TRUE, 'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE]);
closeside();

openside('');
echo form_select('forumdate_select', $locale['admins_453'], $settings['forumdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['admins_455'],
    'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE
]);
echo form_text('forumdate', '', $settings['forumdate'], ['deactivate' => TRUE, 'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE]);
closeside();

openside('');
echo form_select('newsdate_select', $locale['admins_457'], $settings['newsdate'], [
    'options'     => $date_opts,
    'placeholder' => $locale['admins_455'],
    'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE
]);
echo form_text('newsdate', '', $settings['newsdate'], ['deactivate' => TRUE, 'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE]);
closeside();

echo "</div>\n";

add_to_jquery('
$("[id*=\'_select\']").change(function () {
    $("#" + $(this).attr("id").replace("_select", "")).val($(this).val());
})
');

echo "</div>\n";
echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
