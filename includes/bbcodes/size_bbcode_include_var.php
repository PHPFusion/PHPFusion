<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: size_bbcode_include_var.php
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
"description"		=>	$locale['bb_size_description'],
"value"			=>	"size",
"bbcode_start"		=>	"",
"bbcode_end"		=>	"",
"usage"			=>	"[size=(12|16|20|24|28|32)]".$locale['bb_size_usage']."[/size]",
"onclick"		=>	"return overlay(this, 'bbcode_text_size_".$textarea_name."', 'rightbottom');",
"onmouseover"		=>	"",
"onmouseout"		=>	"",
"includejscript"	=>	"",
"calljscript"		=>	"",
"html_start"		=>	"<div id='bbcode_text_size_".$textarea_name."' class='tbl1 bbcode-popup' style='display: none; border:1px solid black; position: absolute; width: 50px; height: auto; text-align: center' onclick=\"overlayclose('bbcode_text_size_".$textarea_name."');\">",
"html_middle"		=>	"<input type='button' value='12 px' class='button' onclick=\"addText('".$textarea_name."', '[size=12]', '[/size]', '".$inputform_name."');return false;\" /><br /><input type='button' value='16 px' class='button' onclick=\"addText('".$textarea_name."', '[size=16]', '[/size]', '".$inputform_name."');return false;\" /><br /><input type='button' value='20 px' class='button' onclick=\"addText('".$textarea_name."', '[size=20]', '[/size]', '".$inputform_name."');return false;\" /><br /><input type='button' value='24 px' class='button' onclick=\"addText('".$textarea_name."', '[size=24]', '[/size]', '".$inputform_name."');return false;\" /><br /><input type='button' value='28 px' class='button' onclick=\"addText('".$textarea_name."', '[size=28]', '[/size]', '".$inputform_name."');return false;\" /><br /><input type='button' value='32 px' class='button' onclick=\"addText('".$textarea_name."', '[size=32]', '[/size]', '".$inputform_name."');return false;\" />",
"html_end"		=>	"</div>",
);
?>