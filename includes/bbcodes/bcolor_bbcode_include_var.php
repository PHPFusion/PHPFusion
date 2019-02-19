<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: bcolor_bbcode_include_var.php
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
    "description"    => $locale['bb_bcolor_description'],
    "value"          => "bcolor", "bbcode_start" => "[bcolor=#000000]", "bbcode_end" => "[/bcolor]",
    "usage"          => "[bcolor=#".$locale['bb_bcolor_hex']."]".$locale['bb_bcolor_usage']."[/bcolor]",
    'id'             => 'bbcode_bcolor_map_'.$textarea_name,
    "onclick"        => "return false;",
    "html_start"     => "<div id='bbcode_bcolor_map_".$textarea_name."' class='tbl1'>",
    "includejscript" => "bcolor_bbcode_include_js.js",
    "calljscript"    => "BColorMap('".$textarea_name."', '".$inputform_name."');",
    "html_end"       => "</div>",
    'dropdown'       => TRUE
];
