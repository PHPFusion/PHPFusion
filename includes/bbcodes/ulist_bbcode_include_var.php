<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ulist_bbcode_include_var.php
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
//Unordered list BBCode VARS
$__BBCODE__[] = [
    "description" => $locale['bb_ulist_description'],
    "value"       => "ulist", "bbcode_start" => "[ulist=TYPE]", "bbcode_end" => "[/ulist]",
    "usage"       => "[ulist=(disc|circle|square)]".$locale['bb_ulist_usage']."[/ulist]",
    "onclick"     => "return overlay(this, 'bbcode_ulist_".$textarea_name."', 'rightbottom');",
    "onmouseover" => "", "onmouseout" => "", "includejscript" => "", "calljscript" => "", "phpfunction" => "",
    "html_start"  => "<ul id='bbcode_ulist_".$textarea_name."' class='bbcode-popup dropdown-menu' style='display: none; width:70px' onclick=\"overlayclose('bbcode_ulist_".$textarea_name."');\">",
    "html_middle" => "<li><input type='button' value='".$locale['bb_ulist_1']."' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[ulist=disc]', '[/ulist]', '".$inputform_name."');return false;\" /></li>
                      <li><input type='button' value='".$locale['bb_ulist_2']."' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[ulist=circle]', '[/ulist]', '".$inputform_name."');return false;\" /></li>
                      <li><input type='button' value='".$locale['bb_ulist_3']."' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[ulist=square]', '[/ulist]', '".$inputform_name."');return false;\" /></li>",
    "html_end"    => "</ul>",
    'dropdown'    => TRUE
];
