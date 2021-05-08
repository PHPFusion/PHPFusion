<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_time.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'settings_time.php'.fusion_get_aidlink(), 'title' => $locale['time_settings']]);

if (check_post('savesettings')) {
    $inputData = [
        'shortdate'        => sanitizer('shortdate', '', 'shortdate'),
        'longdate'         => sanitizer('longdate', '', 'longdate'),
        'forumdate'        => sanitizer('forumdate', '', 'forumdate'),
        'newsdate'         => sanitizer('newsdate', '', 'newsdate'),
        'timeoffset'       => sanitizer('timeoffset', '', 'timeoffset'),
        'serveroffset'     => sanitizer('serveroffset', '', 'serveroffset'),
        'default_timezone' => sanitizer('default_timezone', '', 'default_timezone'),
        'week_start'       => sanitizer('week_start', 0, 'week_start')
    ];

    if (\defender::safe()) {
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

$date_opts = [];
foreach ($locale['dateformats'] as $dateformat) {
    $date_opts[$dateformat] = showdate($dateformat, TIME);
}
unset($dateformat);
opentable($locale['time_settings']);
echo "<div class='well'>".$locale['time_description']."</div>\n";

echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n";

echo "<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo '<div>';
echo '<span class="strong">'.$locale['458'].' ('.$locale['459'].')</span>';
echo '<span class="pull-right">'.showdate($settings['longdate'], TIME, ['tz_override' => $settings['serveroffset']]).'</span>';
echo '</div>';
echo '<hr class="m-t-5 m-b-5">';
echo '<div>';
echo '<span class="strong">'.$locale['458'].' ('.$locale['460'].')</span>';
echo '<span class="pull-right">'.(column_exists('users', 'user_timezone') ? showdate($settings['longdate'], TIME, ['tz_override' => fusion_get_userdata('user_timezone')]) : $locale['na']).'</span>';
echo '</div>';
echo '<hr class="m-t-5 m-b-5">';
echo '<div>';
echo '<span class="strong">'.$locale['458'].' ('.$locale['461'].')</span>';
echo '<span class="pull-right">'.showdate($settings['longdate'], TIME, ['tz_override' => $settings['timeoffset']]).'</span>';
echo '</div>';
echo '<hr class="m-t-5 m-b-5">';
echo '<div>';
echo '<span class="strong">'.$locale['458'].' ('.$locale['466'].')</span>';
echo '<span class="pull-right">'.showdate($settings['longdate'], TIME, ['tz_override' => $settings['default_timezone']]).'</span>';
echo '</div>';
closeside();

openside('');
echo form_select('serveroffset', $locale['463'], $settings['serveroffset'], ['options' => $timezone_array]);
echo form_select('timeoffset', $locale['456'], $settings['timeoffset'], ['options' => $timezone_array]);
echo form_select('default_timezone', $locale['464'], $settings['default_timezone'], ['options' => $timezone_array]);
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
