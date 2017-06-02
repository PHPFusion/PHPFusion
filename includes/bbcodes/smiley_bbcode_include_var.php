<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: smiley_bbcode_include_var.php
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
    "description"    => $locale['bb_smiley_description'],
    "value"          => "smiley",
    "bbcode_start"   => "",
    "bbcode_end"     => "", "usage" => $locale['bb_smiley_usage'],
    "onclick"        => "return overlay(this, 'bbcode_smileys_list_".$textarea_name."', '".((isset($p_data['panel_side']) && $p_data['panel_side'] == 4) ? "bottomright" : "bottomleft")."');",
    "onmouseover"    => "",
    "onmouseout"     => "",
    "html_start"     => "<div id='bbcode_smileys_list_".$textarea_name."' class='bbcode-popup' style='display:none; border: 1px solid #ccc; position: absolute; overflow: auto; height: auto; padding: 6px 15px; background: #fff;' onclick=\"overlayclose('bbcode_smileys_list_".$textarea_name."');\">",
    "includejscript" => "",
    "calljscript"    => "",
    "phpfunction"    => "echo displaysmileys('$textarea_name', '$inputform_name');",
    "html_middle"    => "",
    "html_end"       => "</div>"
);

