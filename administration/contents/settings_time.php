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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_button',
    'js'          => 'pf_js',
    'link'        => ($admin_link ?? ''),
    'settings'    => TRUE,
    'title'       => $locale['time_settings'],
    'description' => $locale['time_description'],
    'actions'     => ['post' => ['savesettings'=>'settingsFrm']]
];

$settings = fusion_get_settings();

function pf_post() {
    $locale = fusion_get_locale();

    if (isset($_POST['savesettings'])) {
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

        if (fusion_safe()) {
            foreach ($inputData as $settings_name => $settings_value) {
                dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                    ':settings_value' => $settings_value,
                    ':settings_name'  => $settings_name
                ]);
            }

            add_notice("success", $locale['900']);
            redirect(FUSION_REQUEST);
        }
    }
}

function pf_view() {
    $locale = fusion_get_locale();

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
        $offset = (new DateTime(NULL, new DateTimeZone($zone)))->getOffset() / 3600;
        $timezone_array[$zone] = '(GMT'.($offset < 0 ? $offset : '+'.$offset).') '.$zone_city;
    }

    $weekdayslist = explode("|", $locale['weekdays']);

    $date_opts = [];
    foreach ($locale['dateformats'] as $dateformat) {
        $date_opts[$dateformat] = showdate($dateformat, time());
    }

    $settings = fusion_get_settings();

    echo openform('settingsFrm', 'POST');
    openside('Site timezone<small>Set the time and date configurations', TRUE);

    closeside();
    openside($locale['458']);
    echo '<div class="row flexbox m-0"><span>'.$locale['459'].'</span><span class="m-l-a">'.showdate($settings['longdate'], time(), ['tz_override' => $settings['serveroffset']]).'</span></div>';
    echo '<div class="row flexbox m-0"><span>'.$locale['460'].'</span><span class="m-l-a">'.(column_exists('users', 'user_timezone') ? showdate($settings['longdate'], time(), ['tz_override' => fusion_get_userdata('user_timezone')]) : $locale['na']).'</span></div>';
    echo '<div class="row flexbox m-0"><span>'.$locale['461'].'</span><span class="m-l-a">'.showdate($settings['longdate'], time(), ['tz_override' => $settings['timeoffset']]).'</span></div>';
    echo '<div class="row flexbox m-0"><span>'.$locale['466'].'</span><span class="m-l-a">'.showdate($settings['longdate'], time(), ['tz_override' => $settings['default_timezone']]).'</span></div>';
    closeside();
    openside('Time Settings<small>'.$locale['time_description'].'</small>', TRUE);
    echo form_select('shortdate', $locale['451'], $settings['shortdate'], [
        'options'     => $date_opts,
        'placeholder' => $locale['455']
    ]);
    echo form_select('longdate', $locale['452'], $settings['longdate'], [
        'options'     => $date_opts,
        'placeholder' => $locale['455']
    ]);
    echo form_select('forumdate', $locale['453'], $settings['forumdate'], [
        'options'     => $date_opts,
        'placeholder' => $locale['455']
    ]);
    echo form_select('newsdate', $locale['457'], $settings['newsdate'], [
        'options'     => $date_opts,
        'placeholder' => $locale['455']
    ]);
    echo form_select('subheaderdate', $locale['454'], $settings['subheaderdate'], [
        'options'     => $date_opts,
        'placeholder' => $locale['455'],
        'width'       => '100%'
    ]);
    closeside();
    openside('Offset Settings<small>The configuration settings for offset on Time and Date system</small>', TRUE);
    echo form_select('serveroffset', $locale['463'], $settings['serveroffset'], ['options' => $timezone_array, 'inner_width' => '100%']);
    echo form_select('timeoffset', $locale['456'], $settings['timeoffset'], ['options' => $timezone_array, 'inner_width' => '100%']);
    echo form_select('default_timezone', $locale['464'], $settings['default_timezone'], ['options' => $timezone_array, 'inner_width' => '100%']);
    echo form_select('week_start', $locale['465'], $settings['week_start'], ['options' => $weekdayslist, 'inner_width' => '100%']);
    closeside();

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    echo pf_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();
}

function pf_button() {
    $locale = fusion_get_locale();
    return form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-primary']);
}
