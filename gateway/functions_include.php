<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: functions_include.php
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

$locale = fusion_get_locale('', LOCALE.LOCALESET.'gateway.php');

function convertNumberToWord($num = FALSE) {
    global $locale;

    $num = str_replace([',', ' '], '', trim($num));
    if (!$num) {
        return FALSE;
    }
    $num = (int)$num;
    $words = [];
    $list1 = ['', $locale['gateway_001'], $locale['gateway_002'], $locale['gateway_003'], $locale['gateway_004'], $locale['gateway_005'], $locale['gateway_006'], $locale['gateway_007'], $locale['gateway_008'], $locale['gateway_009'], $locale['gateway_010'],
              $locale['gateway_011'], $locale['gateway_012'], $locale['gateway_013'], $locale['gateway_014'], $locale['gateway_015'], $locale['gateway_016'], $locale['gateway_017'], $locale['gateway_018'], $locale['gateway_019']];
    $list2 = ['', $locale['gateway_020'], $locale['gateway_021'], $locale['gateway_022'], $locale['gateway_023'], $locale['gateway_024'], $locale['gateway_025'], $locale['gateway_026'], $locale['gateway_027'], $locale['gateway_028'], $locale['gateway_029']];
    $list3 = ['', $locale['gateway_030'], $locale['gateway_031'], $locale['gateway_032'], $locale['gateway_033'], $locale['gateway_034'], $locale['gateway_035'], $locale['gateway_036'], $locale['gateway_037'],
              $locale['gateway_038'], $locale['gateway_039'], $locale['gateway_040'], $locale['gateway_041'], $locale['gateway_042'], $locale['gateway_043'], $locale['gateway_044'],
              $locale['gateway_045'], $locale['gateway_046'], $locale['gateway_047'], $locale['gateway_048'], $locale['gateway_049'], $locale['gateway_050']];
    $num_length = strlen($num);
    $levels = (int)(($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00'.$num, -$max_length);
    $num_levels = str_split($num, 3);

    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int)($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' '.$list1[$hundreds].$locale['gateway_051'].' ' : '');
        $tens = (int)($num_levels[$i] % 100);
        $singles = '';
        if ($tens < 20) {
            $tens = ($tens ? ' '.$list1[$tens].' ' : '');
        } else {
            $tens = (int)($tens / 10);
            $tens = ' '.$list2[$tens].' ';
            $singles = (int)($num_levels[$i] % 10);
            $singles = ' '.$list1[$singles].' ';
        }
        $words[] = $hundreds.$tens.$singles.(($levels && (int)($num_levels[$i])) ? ' '.$list3[$levels].' ' : '');
    }

    $words = str_replace(' ', '', $words);
    return implode($words);
}

if (!function_exists('str_rot47')) {
    function str_rot47($str) {
        return strtr($str,
            '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~',
            'PQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNO'
        );
    }
}

if (file_exists(CONTROL_LOCK_FILE)) {
    if (time() - filemtime(CONTROL_LOCK_FILE) > CONTROL_BAN_TIME) {
        // this user has complete his punishment
        unlink(CONTROL_LOCK_FILE);
    } else {
        redirect(BASEDIR."error.php?code=401");
        touch(CONTROL_LOCK_FILE);
        die;
    }
}

function antiflood_countaccess() {
    // counting requests and last access time
    $control = [];

    if (file_exists(CONTROL_DB)) {
        $fh = fopen(CONTROL_DB, "r");
        $control = array_merge($control, unserialize(fread($fh, filesize(CONTROL_DB))));
        fclose($fh);
    }

    if (isset($control[USER_IP])) {
        if (time() - $control[USER_IP]["t"] < CONTROL_REQ_TIMEOUT) {
            $control[USER_IP]["c"]++;
        } else {
            $control[USER_IP]["c"] = 1;
        }
    } else {
        $control[USER_IP]["c"] = 1;
    }
    $control[USER_IP]["t"] = time();

    if ($control[USER_IP]["c"] >= CONTROL_MAX_REQUESTS) {
        // this one did too many requests within a very short period of time
        $fh = fopen(CONTROL_LOCK_FILE, "w");
        fwrite($fh, USER_IP);
        fclose($fh);
    }

    // write updated control table
    $fh = fopen(CONTROL_DB, "w");
    fwrite($fh, serialize($control));
    fclose($fh);
}
