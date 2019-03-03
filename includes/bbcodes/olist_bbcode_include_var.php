<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: olist_bbcode_include_var.php
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
    'description'    => $locale['bb_olist_description'],
    'value'          => "olist", 'bbcode_start' => "[olist=TYPE]", 'bbcode_end' => '[/olist]',
    'usage'          => "[olist=(1|a|A|i|I)]".$locale['bb_olist_usage']."[/olist]",
    'onclick'        => "return false",
    'id'             => 'bbcode_olist_'.$textarea_name,
    'html_middle'    => "<input type='button' value='1' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=1]', '[/olist]', '".$inputform_name."');return false;\"/>
                         <input type='button' value='a' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=a]', '[/olist]', '".$inputform_name."');return false;\"/>
                         <input type='button' value='A' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=A]', '[/olist]', '".$inputform_name."');return false;\"/>
                         <input type='button' value='i' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=i]', '[/olist]', '".$inputform_name."');return false;\"/>
                         <input type='button' value='I' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[olist=I]', '[/olist]', '".$inputform_name."');return false;\"/>",
    'dropdown'       => TRUE,
    'dropdown_style' => 'min-width: 50px;'
];
