<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: olist_bbcode_include_var.php
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
$__BBCODE__[] = [
    'description'    => $locale['bb_olist_description'],
    'value'          => "olist", 'bbcode_start' => "[olist=TYPE]", 'bbcode_end' => '[/olist]',
    'usage'          => "[olist=(1|a|A|i|I)]".$locale['bb_olist_usage']."[/olist]",
    'onclick'        => "return overlay(this, 'bbcode_olist_".$textarea_name."', 'rightbottom');", 'onmouseover' => "", 'onmouseout' => "",
    'html_start'     => "<ul id='bbcode_olist_".$textarea_name."' class='bbcode-popup dropdown-menu' style='display: none; width: 30px;' onclick=\"overlayclose('bbcode_olist_".$textarea_name."');\">",
    'includejscript' => "", 'calljscript' => "", 'phpfunction' => "",
    'html_middle'    => "<li><input type='button' value='1' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=1]', '[/olist]', '".$inputform_name."');return false;\" /></li>
                         <li><input type='button' value='a' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=a]', '[/olist]', '".$inputform_name."');return false;\" /></li>
                         <li><input type='button' value='A' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=A]', '[/olist]', '".$inputform_name."');return false;\" /></li>
                         <li><input type='button' value='i' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=i]', '[/olist]', '".$inputform_name."');return false;\" /></li>
                         <li><input type='button' value='I' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=I]', '[/olist]', '".$inputform_name."');return false;\" /></li>",
    'html_end'       => "</ul>",
    'dropdown'       => TRUE
];
