<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_timezone_include.php
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
defined('IN_FUSION') || exit;

// Display user field input
if ($profile_method == "input") {
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

    $options = [
            'inline'  => TRUE,
            'options' => $timezone_array,
        ] + $options;
    $user_fields = form_select('user_timezone', $locale['uf_timezone'], $field_value, $options);
    // Display in profile
} else if ($profile_method == "display") {
    if (!empty($field_value)) {
        $date = new DateTime(NULL, new DateTimeZone($field_value));
        $offset = $date->getOffset() / 3600;
        $field_value = 'GMT'.($offset < 0 ? $offset : '+'.$offset);

        $user_fields = [
            'title' => $locale['uf_timezone'],
            'value' => $field_value
        ];
    }
}
