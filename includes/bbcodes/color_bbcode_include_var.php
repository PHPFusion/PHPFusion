<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: color_bbcode_include_var.php
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

$__BBCODE__[] = [
    "description"    => $locale['bb_color_description'],
    "value"          => "color", "bbcode_start" => "[color=#000000]", "bbcode_end" => "[/color]",
    "usage"          => "[color=#".$locale['bb_color_hex']."]".$locale['bb_color_usage']."[/color]",
    "onclick"        => "return false",
    'id'             => 'bbcode_color_map_'.$textarea_name,
    "html_start"     => "<div id='bbcode_color_map_".$textarea_name."' class='tbl1'>",
    "includejscript" => "color_bbcode_include_js.js",
    "calljscript"    => "ColorMap('".$textarea_name."', '".$inputform_name."');",
    "html_end"       => "</div>",
    'dropdown'       => TRUE
];
