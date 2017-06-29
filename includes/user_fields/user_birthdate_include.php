<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_birthdate_include.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Display user field input
if ($profile_method == "input") {
    if (isset($field_value) && $field_value != "1900-01-01") {
        $user_birthDate = date('Y-m-d', strtotime($field_value));
    } else {
        $user_birthDate = date('Y-m-d', strtotime('today'));
    }
    $options += array(
        'inline'          => TRUE,
        'type'            => 'date',
        'width'           => '250px',
        'showTime'        => FALSE,
        'date_format_js'  => 'YYYY-M-DD',
        'date_format_php' => 'Y-m-d',
    );
    $user_fields = form_datepicker('user_birthdate', $locale['uf_birthdate'], $user_birthDate, $options);

// Display in profile
} elseif ($profile_method == "display") {
    if ($field_value != "1900-01-01") {
        //$months = explode("|", fusion_get_locale('months'));
        $user_birthDate = explode("-", $field_value);
        $lastday = mktime(0, 0, 0, $user_birthDate[1], $user_birthDate[2], $user_birthDate[0]);
        //$month_name = $months[number_format($user_birthDate[1])];
        $fmt = array('0' => '%Y %B %d', '1' => '%d %B %Y');
        $fmt_lg = array("hu", "eo", "eu", "ko", "it", "si", "zh-cn", "zh-tw");
        $user_fields = array(
            'title' => $locale['uf_birthdate'],
            'value' => showdate($fmt[(in_array(fusion_get_locale('datepicker', LOCALE.LOCALESET.'global.php'), $fmt_lg) ? 0 : 1)], $lastday)
        );
    } else {
        $user_fields = array('title' => $locale['uf_birthdate'], 'value' => $locale['na']);
    }
}
