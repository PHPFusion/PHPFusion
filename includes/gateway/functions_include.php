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

function get_gateway_info() {

    $locale = fusion_get_locale();

    function gateway_answer() {

        if ( post( 'gateway_answer' ) ) {
            // Terminate and ban all excessive access attempts
            antiflood_countaccess();

            $honeypot = session_get( 'honeypot' );

            if ( check_post( $honeypot ) && !post( $honeypot ) ) {
                $antibot = stripinput( strtolower( post( 'gateway_answer' ) ) );
                $antibot_session = session_get( 'antibot' );
                if ( $antibot_session == $antibot ) {
                    session_add( 'validated', 'TRUE' );
                    redirect( BASEDIR.'register.php' );
                }
            }

            return TRUE;
        }

        return FALSE;
    }

    $answer_status = gateway_answer();

    $info = [
        'showform'         => TRUE,
        'incorrect_answer' => $answer_status
    ];

    session_add( 'validated', 'FALSE' );

    if ( !post( 'gateway_submit' ) && !post( 'register' ) ) {

        // Get some numbers up. Always keep an odd number to void 10-10 etc.
        $a = rand( 11, 20 );
        $b = rand( 1, 10 );

        $method = fusion_get_settings('gateway_method'); // 0 words, 1 numbers, 2 both

        if ($method == 0) {
            $antibot = (int)( $a + $b );
            $multiplier = "+";
            $reply_method = $locale['gateway_062'];
            $a = convertNumberToWord( $a );
            $antibot = strtolower( convertNumberToWord( $antibot ) );
        } else if ($method == 1) {
            $antibot = (int)( $a - $b );
            $multiplier = "-";
            $reply_method = $locale['gateway_063'];
            $b = convertNumberToWord( $b );
        } else {
            if ( $a > 15 ) {
                $antibot = (int)( $a + $b );
                $multiplier = "+";
                $reply_method = $locale['gateway_062'];
                $a = convertNumberToWord( $a );
                $antibot = strtolower( convertNumberToWord( $antibot ) );
            } else {
                $antibot = (int)( $a - $b );
                $multiplier = "-";
                $reply_method = $locale['gateway_063'];
                $b = convertNumberToWord( $b );
            }
        }

        session_add( 'antibot', $antibot );

        $a = str_rot47( $a );
        $b = str_rot47( $b );

        echo "<noscript>".$locale['gateway_052']."</noscript>";
        // Just add fields to random
        $honeypot_array = [ $locale['gateway_053'], $locale['gateway_054'], $locale['gateway_055'], $locale['gateway_056'], $locale['gateway_057'], $locale['gateway_058'], $locale['gateway_059'] ];
        shuffle( $honeypot_array );
        //$_SESSION["honeypot"] = $honeypot_array[3];
        session_add( 'honeypot', $honeypot_array[3] );
        // Try this and we see, Rot47 Encryption etc..
        add_to_footer( '<script>
        function decode(x) {
            let s = "";
            for (let i = 0; i < x.length; i++) {
                let j = x.charCodeAt(i);
                if ((j >= 33) && (j <= 126)) {
                    s += String.fromCharCode(33 + ((j + 14) % 94));
                } else {
                    s += String.fromCharCode(j);
                }
            }
            return s;
        }
        $("#gateway_question").append("'.$locale['gateway_060'].' " + decode("'.$a.'") + " '.$multiplier.' " + decode("'.$b.'") + " '.$locale['gateway_061'].' '.$reply_method.'");
    </script>' );
        $info = [
            'showform'         => TRUE,
            'incorrect_answer' => $answer_status,
            'gateway_question' => '<span id="gateway_question"></span>',
            'openform'         => openform( 'Fusion_Gateway', 'post', 'register.php', [ 'class' => 'm-t-20' ] ),
            'closeform'        => closeform(),
            'hiddeninput'      => form_hidden( $honeypot_array[3], "", "" ),
            'textinput'        => form_text( 'gateway_answer', "", "", [ 'error_text' => $locale['gateway_064'], 'required' => TRUE ] ),
            'button'           => form_button( 'gateway_submit', $locale['gateway_065'], $locale['gateway_065'], [ 'class' => 'btn-primary btn-block m-t-10' ] ),
        ];
    }

    return $info;
}

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

function antiflood_countaccess() {
    // counting requests and last access time
    $control = [];
    // read control file
    if (file_exists(CONTROL_DB)) {
        $fh = fopen(CONTROL_DB, "r");
        $control = array_merge($control, unserialize(fread($fh, filesize(CONTROL_DB))));
        fclose($fh);
    }

    $control[ USER_IP ]["c"] = 1;

    if ( isset( $control[ USER_IP ]['t'] ) ) {
        if ( time() - $control[ USER_IP ]["t"] < CONTROL_REQ_TIMEOUT ) {
            $control[ USER_IP ]["c"]++;
        }
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
