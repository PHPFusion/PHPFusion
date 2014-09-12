<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 8
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Text Input Based
| Filename: form_text.php
| Author: PHP-Fusion 8 Development Team
| Coded by : Frederick MC Chan (Hien)
| Version : 8.1.0 (please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_alert($title,$text,$array=false) {
// <a href="#" class="alert-link">...</a>

    if (isset($title) && ($title !=="")) { $title = stripinput($title); } else { $title = ""; }

    //if (isset($text) && ($text !=="")) { $text = stripinput($text); } else { $text = ""; }

    if (!is_array($array)) {

        $class = '';

        $dismiss = '';

    } else {

        $class = (array_key_exists('class', $array)) ? $array['class'] : "";

        $dismiss = (array_key_exists('dismiss', $array)) ? $array['dismiss'] : "";

    }

    if ($dismiss == "1") {

        $html = "<div class='alert alert-dismissable $class'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>$title</strong> $text</div>";

    } else {

        $html = "<div class='alert $class'><strong>$title</strong> $text</div>";
    }

    add_to_jquery("
    $('div.alert a').addClass('alert-link');
    ");

    return $html;
}

?>