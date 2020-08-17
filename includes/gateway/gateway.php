<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: gateway.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

/**
 * Experimental Anti Bot Gateway that combine multiple methods to prevent auto bots.
*/
require_once "constants_include.php";
require_once "functions_include.php";

// Terminate and ban all excessive access atempts.
antiflood_countaccess();

// Flag for pass, just increment on amount of checks we add.
$multiplier = "0";
$reply_method = '';

if (empty($_SESSION["validated"])) {
    $_SESSION['validated'] = 'False';
}

// Don´t run twice
if (!isset($_POST['gateway_submit']) && !isset($_POST['Register']) && isset($_SESSION["validated"]) && $_SESSION['validated'] !== 'True') {
    $_SESSION['validated'] = 'False';

    // Get some numbers up. Always keep an odd number to void 10-10 etc.
    $a = rand(11, 20);
    $b = rand(1, 10);

    $method = fusion_get_settings('gateway_method'); // 0 words, 1 numbers, 2 both
    if ($method == 0) {
        $antibot = intval($a + $b);
        $multiplier = "+";
        $reply_method = $locale['gateway_062'];
        $a = convertNumberToWord($a);
        $antibot = convertNumberToWord($antibot);
        $_SESSION["antibot"] = strtolower($antibot);
    } else if ($method == 1) {
        $antibot = intval($a - $b);
        $multiplier = "-";
        $reply_method = $locale['gateway_063'];
        $_SESSION["antibot"] = intval($antibot);
        $b = convertNumberToWord($b);
    } else {
        if ($a > 15) {
            $antibot = intval($a + $b);
            $multiplier = "+";
            $reply_method = $locale['gateway_062'];
            $a = convertNumberToWord($a);
            $antibot = convertNumberToWord($antibot);
            $_SESSION["antibot"] = strtolower($antibot);
        } else {
            $antibot = intval($a - $b);
            $multiplier = "-";
            $reply_method = $locale['gateway_063'];
            $_SESSION["antibot"] = intval($antibot);
            $b = convertNumberToWord($b);
        }
    }

    $a = str_rot47($a);
    $b = str_rot47($b);

    echo "<noscript>".$locale['gateway_052']."</noscript>";

    // Just add fields to random
    $honeypot_array = [$locale['gateway_053'], $locale['gateway_054'], $locale['gateway_055'], $locale['gateway_056'], $locale['gateway_057'], $locale['gateway_058'], $locale['gateway_059']];
    shuffle($honeypot_array);
    $_SESSION["honeypot"] = $honeypot_array[3];

    // Try this and we see, Rot47 Encryption etc..
    echo '<script type="text/javascript">
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
    </script>';

    opentable($locale['gateway_069']);
    echo "<form name='Fusion_Gateway' method='post' action='register.php' />";
    echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>";
    echo '<td width="1%" class="tbl1" style="text-align:center;white-space:nowrap"><strong>';
    // Try this first, JS Rot47 Encryption etc..
    echo '<script type="text/javascript">
        document.write("<h3>'.$locale['gateway_060'].' " + decode("'.$a.'") + " '.$multiplier.' " + decode("'.$b.'") + " '.$locale['gateway_061'].' '.$reply_method.'</h3>");
    </script>';
    echo '</td></tr>';
    echo '<input type="hidden" name="'.$honeypot_array[3].'" />';
    echo "<tr><td width='1%' class='tbl2' style='text-align:center;white-space:nowrap'><strong><input type='text' style='text-align:center; width:300px;' id='gateway_answer' name='gateway_answer' placeholder='".$locale['gateway_064']."...' class='textbox' /></td></tr>";
    echo '<tr><td width="1%" class="tbl1" style="text-align:center;white-space:nowrap"><input type="submit" name="gateway_submit" value="'.$locale['gateway_065'].'" class="'.($settings["bootstrap"] || defined("BOOTSTRAP") ? "button btn-primary m-t-10" : "button").'" />';
    echo "</table>";
    echo '</form>';
    closetable();
}

if (isset($_POST['gateway_answer'])) {
    $honeypot = '';

    if (isset($_SESSION["honeypot"])) {
        $honeypot = $_SESSION["honeypot"];
    }

    $_SESSION["validated"] = "False";

    if (isset($_POST["$honeypot"]) && $_POST["$honeypot"] == "") {
        $antibot = stripinput(strtolower($_POST["gateway_answer"]));

        if (isset($_SESSION["antibot"])) {
            if ($_SESSION["antibot"] == $antibot) {
                $_SESSION["validated"] = "True";
            } else {
                echo "<div class='well text-center'><h3>".$locale['gateway_066']."</h3></div>";
                echo "<input type='button' value='".$locale['gateway_067']."' class='".($settings['bootstrap'] || defined('BOOTSTRAP') ? 'text-center btn btn-info spacer-xs' : 'button')."' onclick=\"location='".BASEDIR."register.php'\">";
            }
        }
    }
}
