<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: size_bbcode_include_var.php
| Author: Core Development Team
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
    "description"    => $locale['bb_size_description'],
    "value"          => "size",
    "usage"          => "[size=(12|16|20|24|28|32)]".$locale['bb_size_usage']."[/size]",
    "onclick"        => "return false;",
    "id"             => 'bbcode_text_size_'.$textarea_name,
    'dropdown_items' => [
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[size=28]', '[/size]', '".$inputform_name."');return false;\"><span style='font-size:28px;'>Heading 1</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[size=24]', '[/size]', '".$inputform_name."');return false;\"><span style='font-size:24px;'>Heading 2</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[size=20]', '[/size]', '".$inputform_name."');return false;\"><span style='font-size:20px;'>Heading 3</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[size=16]', '[/size]', '".$inputform_name."');return false;\"><span style='font-size:16px;'>Heading 4</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[size=12]', '[/size]', '".$inputform_name."');return false;\"><span style='font-size:12px;'>Heading 5</span></a>",
    ],
    // "html_middle"    => "<input type='button' value='Heading 1' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[size=12]', '[/size]', '".$inputform_name."');return false;\"/>
    //                      <input type='button' value='16 px' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[size=16]', '[/size]', '".$inputform_name."');return false;\"/>
    //                      <input type='button' value='20 px' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[size=20]', '[/size]', '".$inputform_name."');return false;\"/>
    //                      <input type='button' value='24 px' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[size=24]', '[/size]', '".$inputform_name."');return false;\"/>
    //                      <input type='button' value='28 px' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[size=28]', '[/size]', '".$inputform_name."');return false;\"/>
    //                      <input type='button' value='32 px' class='button btn btn-link btn-block btn-xs' onclick=\"addText('".$textarea_name."', '[size=32]', '[/size]', '".$inputform_name."');return false;\"/>",
    'dropdown'       => TRUE,
    'dropdown_style' => 'min-width: 50px;',
    "svg"            => "<i class='fa fa-heading fa-lg'></i>",
];

