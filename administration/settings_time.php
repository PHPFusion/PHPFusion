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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageAccess('S2');

$settings = fusion_get_settings();

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

add_breadcrumb(['link' => ADMIN.'settings_time.php'.fusion_get_aidlink(), 'title' => $locale['time_settings']]);


if (post('savesettings')) {

    $inputData = [
        'shortdate'        => sanitizer('shortdate', '', 'shortdate'),
        'longdate'         => sanitizer('longdate', '', 'longdate'),
        'forumdate'        => sanitizer('forumdate', '', 'forumdate'),
        'newsdate'         => sanitizer('newsdate', '', 'newsdate'),
        'subheaderdate'    => sanitizer('subheaderdate', '', 'subheaderdate'),
        'timeoffset'       => sanitizer('timeoffset', '', 'timeoffset'),
        'serveroffset'     => sanitizer('serveroffset', '', 'serveroffset'),
        'default_timezone' => sanitizer('default_timezone', '', 'default_timezone'),
        'week_start'       => sanitizer('week_start', 0, 'week_start')
    ];

    $custom_shortdate = sanitizer('custom_shortdate', '', 'custom_shortdate');
    if ($inputData['shortdate'] == 'custom') {
        $inputData['shortdate'] = $custom_shortdate;

    }

    $custom_longdate = sanitizer('custom_longdate', '', 'custom_longdate');
    if ($inputData['longdate'] == 'custom') {
        $inputData['longdate'] = $custom_longdate;
    }

    $custom_forumdate = sanitizer('custom_forumdate', '', 'custom_forumdate');
    if ($inputData['forumdate'] == 'custom') {
        $inputData['forumdate'] = $custom_forumdate;
    }

    $custom_newsdate = sanitizer('custom_newsdate', '', 'custom_forumdate');
    if ($inputData['newsdate'] == 'custom') {
        $inputData['newsdate'] = $custom_newsdate;
    }

    $subheaderdate = sanitizer('custom_subheader', '', 'custom_subheader');
    if ($inputData['subheaderdate'] == 'custom') {
        $inputData['subheaderdate'] = $subheaderdate;
    }
    if (\Defender::safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }
        addNotice("success", $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

$timezones_json = json_decode('{
  "Etc/GMT+12": "International Date Line West",
  "Pacific/Midway": "Midway Island, Samoa",
  "Pacific/Honolulu": "Hawaii",
  "America/Juneau": "Alaska",
  "America/Dawson": "Pacific Time (US and Canada); Tijuana",
  "America/Boise": "Mountain Time (US and Canada)",
  "America/Chihuahua": "Chihuahua, La Paz, Mazatlan",
  "America/Phoenix": "Arizona",
  "America/Chicago": "Central Time (US and Canada)",
  "America/Regina": "Saskatchewan",
  "America/Mexico_City": "Guadalajara, Mexico City, Monterrey",
  "America/Belize": "Central America",
  "America/Detroit": "Eastern Time (US and Canada)",
  "America/Indiana/Indianapolis": "Indiana (East)",
  "America/Bogota": "Bogota, Lima, Quito",
  "America/Glace_Bay": "Atlantic Time (Canada)",
  "America/Caracas": "Caracas, La Paz",
  "America/Santiago": "Santiago",
  "America/St_Johns": "Newfoundland and Labrador",
  "America/Sao_Paulo": "Brasilia",
  "America/Argentina/Buenos_Aires": "Buenos Aires, Georgetown",
  "America/Godthab": "Greenland",
  "Etc/GMT+2": "Mid-Atlantic",
  "Atlantic/Azores": "Azores",
  "Atlantic/Cape_Verde": "Cape Verde Islands",
  "GMT": "Dublin, Edinburgh, Lisbon, London",
  "Africa/Casablanca": "Casablanca, Monrovia",
  "Atlantic/Canary": "Canary Islands",
  "Europe/Belgrade": "Belgrade, Bratislava, Budapest, Ljubljana, Prague",
  "Europe/Sarajevo": "Sarajevo, Skopje, Warsaw, Zagreb",
  "Europe/Brussels": "Brussels, Copenhagen, Madrid, Paris",
  "Europe/Amsterdam": "Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
  "Africa/Algiers": "West Central Africa",
  "Europe/Bucharest": "Bucharest",
  "Africa/Cairo": "Cairo",
  "Europe/Helsinki": "Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius",
  "Europe/Athens": "Athens, Istanbul, Minsk",
  "Asia/Jerusalem": "Jerusalem",
  "Africa/Harare": "Harare, Pretoria",
  "Europe/Moscow": "Moscow, St. Petersburg, Volgograd",
  "Asia/Kuwait": "Kuwait, Riyadh",
  "Africa/Nairobi": "Nairobi",
  "Asia/Baghdad": "Baghdad",
  "Asia/Tehran": "Tehran",
  "Asia/Dubai": "Abu Dhabi, Muscat",
  "Asia/Baku": "Baku, Tbilisi, Yerevan",
  "Asia/Kabul": "Kabul",
  "Asia/Yekaterinburg": "Ekaterinburg",
  "Asia/Karachi": "Islamabad, Karachi, Tashkent",
  "Asia/Kolkata": "Chennai, Kolkata, Mumbai, New Delhi",
  "Asia/Kathmandu": "Kathmandu",
  "Asia/Dhaka": "Astana, Dhaka",
  "Asia/Colombo": "Sri Jayawardenepura",
  "Asia/Almaty": "Almaty, Novosibirsk",
  "Asia/Rangoon": "Yangon Rangoon",
  "Asia/Bangkok": "Bangkok, Hanoi, Jakarta",
  "Asia/Krasnoyarsk": "Krasnoyarsk",
  "Asia/Shanghai": "Beijing, Chongqing, Hong Kong SAR, Urumqi",
  "Asia/Kuala_Lumpur": "Kuala Lumpur, Singapore",
  "Asia/Taipei": "Taipei",
  "Australia/Perth": "Perth",
  "Asia/Irkutsk": "Irkutsk, Ulaanbaatar",
  "Asia/Seoul": "Seoul",
  "Asia/Tokyo": "Osaka, Sapporo, Tokyo",
  "Asia/Yakutsk": "Yakutsk",
  "Australia/Darwin": "Darwin",
  "Australia/Adelaide": "Adelaide",
  "Australia/Sydney": "Canberra, Melbourne, Sydney",
  "Australia/Brisbane": "Brisbane",
  "Australia/Hobart": "Hobart",
  "Asia/Vladivostok": "Vladivostok",
  "Pacific/Guam": "Guam, Port Moresby",
  "Asia/Magadan": "Magadan, Solomon Islands, New Caledonia",
  "Pacific/Fiji": "Fiji Islands, Kamchatka, Marshall Islands",
  "Pacific/Auckland": "Auckland, Wellington",
  "Pacific/Tongatapu": "Nuku\'alofa"
}', TRUE);
$timezone_array = [];
foreach ($timezones_json as $zone => $zone_city) {
    $date = new DateTime(NULL, new DateTimeZone($zone));
    $offset = $date->getOffset() / 3600;
    $timezone_array[$zone] = '(GMT'.($offset < 0 ? $offset : '+'.$offset).') '.$zone_city;
}
$weekdayslist = explode("|", $locale['weekdays']);
foreach ($locale['dateformats'] as $dateformat) {
    $date_opts[$dateformat] = showdate($dateformat, TIME);
}
unset($dateformat);

opentable($locale['time_settings'].'<small class="m-l-10"><i class="fas fa-info-circle" title="'.$locale['time_description'].'"></i></small>');

echo "<div class='".grid_row()."'>\n";
echo "<div class='".grid_column_size(100,70)."'>\n";

echo openform('timedatefrm', 'post');

echo "<h4>".$locale['467']."</h4>";
echo "<p class='m-b-50'>".$locale['468']."</p>";

echo form_select('serveroffset', $locale['463'], $settings['serveroffset'], ['options' => $timezone_array, 'inline' => TRUE, 'width' => '450px', 'inner_width' => '450px']);
echo form_select('timeoffset', $locale['456'], $settings['timeoffset'], ['options' => $timezone_array, 'inline' => TRUE, 'width' => '450px', 'inner_width' => '450px']);
echo form_select('default_timezone', $locale['464'], $settings['default_timezone'], ['options' => $timezone_array, 'inline' => TRUE, 'width' => '450px', 'inner_width' => '450px']);
echo form_select('week_start', $locale['465'], $settings['week_start'], ['options' => $weekdayslist, 'inline' => TRUE,]);

echo "<h4 class='p-t-20'>".$locale['469']."</h4>";
echo "<p class='m-b-50'>".$locale['470']."</p>";

echo form_checkbox('shortdate', $locale['451'], get_shortdate_input_value('shortdate', $settings['shortdate']), [
    'options' => get_shortdate_opts('custom_shortdate', $settings['shortdate']),
    'inline'  => TRUE,
    'type'    => 'radio',
]);
echo form_checkbox('longdate', $locale['452'], get_longdate_input_value('longdate', $settings['longdate']), [
    'options' => get_longdate_opts('custom_longdate', $settings['longdate']),
    'inline'  => TRUE,
    'type'    => 'radio',
]);
echo form_checkbox('forumdate', $locale['453'], get_shortdate_input_value('forumdate', $settings['forumdate']), [
    'options' => get_shortdate_opts('custom_forumdate', $settings['forumdate']),
    'inline'  => TRUE,
    'type'    => 'radio',
]);
echo form_checkbox('newsdate', $locale['457'], get_shortdate_input_value('newsdate', $settings['newsdate']), [
    'options' => get_shortdate_opts('custom_newsdate', $settings['newsdate']),
    'inline'  => TRUE,
    'type'    => 'radio',
]);
echo form_checkbox('subheaderdate', $locale['454'], get_shortdate_input_value('subheaderdate', $settings['subheaderdate']), [
    'options' => get_shortdate_opts('custom_subheaderdate', $settings['subheaderdate']),
    'inline'  => TRUE,
    'type'    => 'radio',
]);
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-primary']);
echo closeform();


echo "</div><div class='".grid_column_size(100,30)."'>\n";

echo "<table class='table'>\n";
echo "<tr>\n";
echo "<td><strong>".$locale['458']." (".$locale['459'].")</strong></td>";
echo "<td>".showdate($settings['longdate'], TIME, ['tz_override' => $settings['serveroffset']])."</td>";
echo "</tr>\n";
echo "<tr>\n";
echo "<td><strong>".$locale['458']." (".$locale['460'].")</strong></td>";
echo "<td>\n";
if (column_exists('users', 'user_timezone')) {
    echo showdate($settings['longdate'], TIME, ['tz_override' => fusion_get_userdata('user_timezone')]);
} else {
    echo $locale['na'];
}
echo "</td>";
echo "</tr>\n";
echo "<tr>\n";
echo "<td><strong>".$locale['458']." (".$locale['461'].")</strong></td>";
echo "<td>".showdate($settings['longdate'], TIME, ['tz_override' => $settings['timeoffset']])."</td>";
echo "</tr>\n";
echo "<tr>\n";
echo "<td><strong>".$locale['458']." (".$locale['466'].")</strong></td>";
echo "<td>".showdate($settings['longdate'], TIME, ['tz_override' => $settings['default_timezone']])."</td>";
echo "</tr>\n";
echo "</table>\n";

echo "</div>\n</div>\n";
closetable();

require_once THEMES.'templates/footer.php';

function get_shortdate_opts($input_name, $input_value) {

    $short_date_opts = [
        '%d %b, %Y' => '<span style="width:200px;float:none;display:inline-block">'.showdate('%d %b, %Y', TIME).'</span><code class="m-l-20">%d %b, %Y</code>',
        '%b %d, %Y' => '<span style="width:200px;float:none;display:inline-block">'.showdate('%b %d, %Y', TIME).'</span><code class="m-l-20">%b %d, %Y</code>', //'Jul 15, 2019',
        '%d %B, %Y' => '<span style="width:200px;float:none;display:inline-block">'.showdate('%d %B, %Y', TIME).'</span><code class="m-l-20">%d %B, %Y</code>',// '15 July, 2019',
        '%B %d, %Y' => '<span style="width:200px;float:none;display:inline-block">'.showdate('%B %d, %Y', TIME).'</span><code class="m-l-20">%B %d, %Y</code>', //'July 15, 2019',
        '%Y-%m-%d'  => '<span style="width:200px;float:none;display:inline-block">'.showdate('%Y-%m-%d', TIME).'</span><code class="m-l-20">%Y-%m-%d</code>', //'2019-07-15',
        '%d-%m-%Y'  => '<span style="width:200px;float:none;display:inline-block">'.showdate('%d-%m-%Y', TIME).'</span><code class="m-l-20">%d-%m-%Y</code>', //'15-07-2019',
        '%m/%d/%Y'  => '<span style="width:200px;float:none;display:inline-block">'.showdate('%m/%d/%Y', TIME).'</span><code class="m-l-20">%m/%d/%Y</code>', //'07/15/2019',
        'custom'    => '<span style="width:200px;float:none;display:inline-block">'.fusion_get_locale('471').'</span>'.form_text($input_name, '', $input_value, ['class' => 'm-l-10 pull-right']),
    ];
    add_to_jquery("
    $('#$input_name').on('focus', function(e) {
        console.log('oi');
        $(this).closest('.radio').find('input[type=\"radio\"]').prop('checked', true);
    });
    ");
    return $short_date_opts;
}

function get_longdate_opts($input_name, $input_value) {

    $long_date_opts = [
        '%a %d %b, %Y %I:%M %r' => '<span style="width:200px;float:none;display:inline-block">'.showdate('%a %d %b, %Y %r', TIME).'</span><code class="m-l-20">%a %d %b, %Y %I:%M %r</code>',
        '%A %b %d, %Y %I:%M %r' => '<span style="width:200px;float:none;display:inline-block">'.showdate('%A %b %d, %r', TIME).'</span><code class="m-l-20">%A %b %d, %Y %I:%M %r</code>', //'Jul 15, 2019',
        '%a %d %B, %Y %H:%M %p' => '<span style="width:200px;float:none;display:inline-block">'.showdate('%a %d %B, %Y %H:%M %p', TIME).'</span><code class="m-l-20">%a %d %B, %Y %H:%M %p</code>',// '15 July, 2019',
        '%a %B %d, %Y %H:%M'    => '<span style="width:200px;float:none;display:inline-block">'.showdate('%a %B %d, %Y %H:%M', TIME).'</span><code class="m-l-20">%a %B %d, %Y %H:%M</code>', //'July 15, 2019',
        '%a %Y-%m-%d %H:%M'     => '<span style="width:200px;float:none;display:inline-block">'.showdate('%a %Y-%m-%d %H:%M', TIME).'</span><code class="m-l-20">%a %Y-%m-%d %H:%M</code>', //'2019-07-15',
        '%a %d-%m-%Y %H:%M'     => '<span style="width:200px;float:none;display:inline-block">'.showdate('%a %d-%m-%Y %H:%M', TIME).'</span><code class="m-l-20">%a %d-%m-%Y %H:%M</code>', //'15-07-2019',
        '%a %m/%d/%Y %H:%M'     => '<span style="width:200px;float:none;display:inline-block">'.showdate('%a %m/%d/%Y %H:%M', TIME).'</span><code class="m-l-20">%a %m/%d/%Y %H:%M</code>', //'07/15/2019',
        'custom'                => '<span style="width:200px;float:none;display:inline-block">'.fusion_get_locale('471').'</span>'.form_text($input_name, '', $input_value, ['class' => 'm-l-10 pull-right']),
    ];

    add_to_jquery("
    $('#$input_name').on('focus', function(e) {
        console.log('oi');
        $(this).closest('.radio').find('input[type=\"radio\"]').prop('checked', true);
    });
    ");
    return $long_date_opts;
}

function get_shortdate_input_value($input_name, $value) {
    $array = get_shortdate_opts($input_name, $value);
    if (isset($array[$value])) {
        return $value;
    }
    return 'custom';
}

function get_longdate_input_value($input_name, $value) {
    $array = get_longdate_opts($input_name, $value);
    if (isset($array[$value])) {
        return $value;
    }
    return 'custom';
}