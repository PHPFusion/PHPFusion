<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_birthdate_include.php
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
defined('IN_FUSION') || exit;

// Display user field input
if ($profile_method == "input") {
    $options += [
        'inline'          => TRUE,
        'type'            => 'date',
        'width'           => '250px',
        'showTime'        => FALSE,
        'date_format_js'  => 'YYYY-M-DD',
        'date_format_php' => 'Y-m-d',
    ];
    $user_fields = form_datepicker('user_birthdate', $locale['uf_birthdate'], $field_value, $options);

    // Display in profile
} else if ($profile_method == "display") {
    if (!empty($field_value) || $field_value != '1900-01-01' || $field_value != '1970-1-01') {
        $user_birthDate = explode("-", $field_value);
        $lastday = mktime(0, 0, 0, $user_birthDate[1], $user_birthDate[2], $user_birthDate[0]);
        $user_fields = [
            'title' => $locale['uf_birthdate'],
            'value' => showdate($locale['uf_birthdate_date'], $lastday)
        ];
    }
}
