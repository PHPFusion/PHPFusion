<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_bbcode_include_var.php
| Author: Robert Gaudyn {Wooya}
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

if (!function_exists("generate_search_opts")) {
    function generate_search_opts($textarea_name, $inputform_name) {
        $locale = fusion_get_locale();
        $generated = "<input type='button' value='".$locale['407']."' class='button' style='width:100px' onclick=\"addText('".$textarea_name."', '[search=all]', '[/search]', '".$inputform_name."');return false;\" /><br />";
        if ($handle = opendir(BASEDIR."includes/search")) {
            while (FALSE !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.php" && preg_match("/_include.php/i", $file)) {
                    $search_name = explode("_", $file);
                    include LOCALE.LOCALESET."search/".$search_name[1].".php";
                    foreach ($locale as $key => $value) {
                        if (preg_match("/400/i", $key)) {
                            $name = $key;
                        }
                    }
                    $generated .= "<input type='button' value='".$locale[$name]."' class='button' style='width:100px' onclick=\"addText('".$textarea_name."', '[search=".$search_name[1]."]', '[/search]', '".$inputform_name."');return false;\" /><br />";
                }
            }
            closedir($handle);
        }

        return $generated;
    }
}

$__BBCODE__[] = array(
    'description' => $locale['bb_search_description'], 'value' => "search",
    'bbcode_start' => "[search=".$locale['bb_search_where']."]", 'bbcode_end' => "[/search]",
    'usage' => "[search=".$locale['bb_search_where']."]".$locale['bb_search_usage']."[/search]",
    'onclick' => "return overlay(this, 'bbcode_search_".$textarea_name."', 'rightbottom');",
    'onmouseover' => "", 'onmouseout' => "",
    'html_start' => "<div id='bbcode_search_".$textarea_name."' class='tbl1 bbcode-popup' style='display: none; border:1px solid black; position: absolute; width: auto; height: auto; text-align: center' onclick=\"overlayclose('bbcode_search_".$textarea_name."');\">",
    'includejscript' => "", 'calljscript' => "",
    'phpfunction' => "echo generate_search_opts('".$textarea_name."', '".$inputform_name."');",
    'html_middle' => "", 'html_end' => "</div>"
);

