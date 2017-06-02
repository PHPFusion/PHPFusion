<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: bcolor_bbcode_include_var.php
| Author: Wooya
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
$__BBCODE__[] = array(
    "description" => $locale['bb_bcolor_description'], "value" => "bcolor",
    "bbcode_start" => "[bcolor=#000000]", "bbcode_end" => "[/bcolor]",
    "usage" => "[bcolor=#".$locale['bb_bcolor_hex']."]".$locale['bb_bcolor_usage']."[/bcolor]",
    "onclick" => "return overlay(this, 'bbcode_bcolor_map_".$textarea_name."', 'rightbottom');",
    "onmouseover" => "", "onmouseout" => "",
    "html_start" => "<div id='bbcode_bcolor_map_".$textarea_name."' class='tbl1 bbcode-popup' style='display:none;border:1px solid black;position:absolute;width:182px;height:149px' onclick=\"overlayclose('bbcode_bcolor_map_".$textarea_name."');\">",
    "includejscript" => "bcolor_bbcode_include_js.js",
    "calljscript" => "BColorMap('".$textarea_name."', '".$inputform_name."');", "phpfunction" => "",
    "html_middle" => "", "html_end" => "</div>",
);

