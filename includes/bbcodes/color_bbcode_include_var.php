<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: color_bbcode_include_var.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

$__BBCODE__[] = 
array(
"description"		=>	$locale['bb_color_description'],
"value"			=>	"color",
"bbcode_start"		=>	"[color=#000000]",
"bbcode_end"		=>	"[/color]",
"usage"			=>	"[color=#".$locale['bb_color_hex']."]".$locale['bb_color_usage']."[/color]",
"onclick"		=>	"return overlay(this, 'bbcode_color_map_".$textarea_name."', 'rightbottom');",
"onmouseover"		=>	"",
"onmouseout"		=>	"",
"html_start"		=>	"<div id='bbcode_color_map_".$textarea_name."' class='tbl1 bbcode-popup' style='display:none;border:1px solid black;position:absolute;width:220px;height:160px' onclick=\"overlayclose('bbcode_color_map_".$textarea_name."');\">",
"includejscript"	=>	"color_bbcode_include_js.js",
"calljscript"		=>	"ColorMap('".$textarea_name."', '".$inputform_name."');",
"phpfunction"		=>	"",
"html_middle"		=>	"",
"html_end"		=>	"</div>",
);
?>