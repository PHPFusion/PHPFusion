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
        'shortdate'           => sanitizer('shortdate', '', 'shortdate'),
        'longdate'            => sanitizer('longdate', '', 'longdate'),
        'forumdate'           => sanitizer('forumdate', '', 'forumdate'),
        'newsdate'            => sanitizer('newsdate', '', 'newsdate'),
        'timeoffset'          => sanitizer('timeoffset', '', 'timeoffset'),
        'week_start'          => sanitizer('week_start', 0, 'week_start'),
        'number_delimiter'    => sanitizer('number_delimiter', '.', 'number_delimiter'),
        'thousands_separator' => sanitizer('thousands_separator', ',', 'thousands_separator'),
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
echo "<div class='mb-5'><h5>".$locale['admins_time_description']."</h5></div>";
echo openform('settingsFrm', 'POST');

openside('Timezone Settings');
echo '<div class="display-flex flex-row align-center">';
echo '<span class="strong">'.$locale['admins_458'].' ('.$locale['admins_464'].')</span>';
echo '<span class="m-l-a">'.showdate($settings['longdate'], time(), ['tz_override' => $settings['timeoffset']]).'</span>';
echo '</div>';
tablebreak();
echo form_select('timeoffset', $locale['admins_464'], $settings['timeoffset'], [
    'options' => $timezone_array, 'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE
]);
echo form_select('week_start', $locale['admins_465'], $settings['week_start'], [
    'options' => $weekdayslist, 'inner_width' => '100%', 'width' => '100%', 'inline' => FALSE
]);
closeside();

openside('Date Settings');
echo '<div class="display-flex flex-row align-center">';
echo '<span class="strong">'.$locale['admins_451'].'</span>';
echo '<span class="m-l-a" id="shortdate_p">'.$settings['shortdate'].'</span>';
echo '</div>';
echo '<div class="display-flex flex-row align-center">';
echo '<span class="strong">'.$locale['admins_452'].'</span>';
echo '<span class="m-l-a" id="longdate_p">'.$settings['longdate'].'</span>';
echo '</div>';
echo '<div class="display-flex flex-row align-center">';
echo '<span class="strong">'.$locale['admins_453'].'</span>';
echo '<span class="m-l-a" id="forumdate_p">'.$settings['forumdate'].'</span>';
echo '</div>';
echo '<div class="display-flex flex-row align-center">';
echo '<span class="strong">'.$locale['admins_457'].'</span>';
echo '<span class="m-l-a" id="newsdate_p">'.$settings['newsdate'].'</span>';
echo '</div>';
tablebreak();
echo '<div class="row"><div class="col-xs-12 col-sm-6">';

echo form_select('shortdate', $locale['admins_451'], $settings['shortdate'], [
    'class'       => 'w-100',
    'data'        => ['pf-select' => '#shortdate_p'],
    'options'     => $date_opts,
    'placeholder' => $locale['admins_455'],
    'inner_width' => '100%',
    'width'       => '100%',
    'inline'      => FALSE,
]);
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_select('longdate', $locale['admins_452'], $settings['longdate'], [
    'class'       => 'w-100',
    'data'        => ['pf-select' => '#longdate_p'],
    'options'     => $date_opts,
    'placeholder' => $locale['admins_455'],
    'inner_width' => '100%',
    'width'       => '100%',
    'inline'      => FALSE
]);
echo '</div></div>';
echo '<div class="row"><div class="col-xs-12 col-sm-6">';
echo form_select('forumdate', $locale['admins_453'], $settings['forumdate'], [
    'class'       => 'w-100',
    'data'        => ['pf-select' => '#forumdate_p'],
    'options'     => $date_opts,
    'placeholder' => $locale['admins_455'],
    'inner_width' => '100%',
    'width'       => '100%',
    'inline'      => FALSE
]);
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_select('newsdate', $locale['admins_457'], $settings['newsdate'], [
    'class'       => 'w-100',
    'data'        => ['pf-select' => '#newsdate_p'],
    'options'     => $date_opts,
    'placeholder' => $locale['admins_455'],
    'inner_width' => '100%',
    'width'       => '100%',
    'inline'      => FALSE
]);
echo '</div></div>';
closeside();

openside('Numbering');

echo '<div class="row"><div class="col-xs-12 col-sm-6">';
echo form_select('number_delimiter', $locale['admins_611'], $settings['number_delimiter'], [
    'options'     => [
        '.' => '.',
        ',' => ','
    ],
    'width'       => '100%',
    'inner_width' => '100%',
    'inline'      => FALSE,
]);
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_select('thousands_separator', $locale['admins_612'], $settings['thousands_separator'], [
    'options'     => [
        '.' => '.',
        ',' => ','
    ],
    'width'       => '100%',
    'inner_width' => '100%',
    'inline'      => FALSE,
]);
echo '</div></div>';
closeside();


echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
echo closeform();
closetable();


add_to_jquery("
$('select[data-pf-select]').on('change', function(e) {
    var sDom = $(this).data('pf-select');
    if (sDom.length) {
        var objDom = $(sDom);
        if (objDom.length) {
            objDom.text( $(this).val() ).addClass('text-info');
        }
    }
});
");

require_once THEMES.'templates/footer.php';
