<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: html_buttons_include.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}


/**
 * Get the color name
 * @param $id - color locale ID
 * @return string
 */
function getcolorname($id) {

    $locale = fusion_get_locale("", LOCALE.LOCALESET."colors.php");

    $id = "{$locale['color_'.$id]}";

    return $id;
}

/**
 * @param        $formname
 * @param        $textarea
 * @param bool   $html
 * @param bool   $colors
 * @param bool   $images
 * @param string $folder
 * @return string
 */
function display_html($formname, $textarea, $html = TRUE, $colors = FALSE, $images = FALSE, $folder = "") {

    $locale = fusion_get_locale("", LOCALE.LOCALESET."colors.php");
    $locale += fusion_get_locale("", LOCALE.LOCALESET."admin/html_buttons.php");

    $res = "";
    if ($html) {
        $res .= "<div class='btn-group'>\n";
        $res .= "<button type='button' value='b' title='".$locale['html_000']."' class='btn btn-sm btn-default m-b-10 button' style='font-weight:bold;' onclick=\"addText('".$textarea."', '&lt;strong&gt;', '&lt;/strong&gt;', '".$formname."');\"><i class='glyphicon glyphicon-bold'></i></button>\n";
        $res .= "<button type='button' value='i' title='".$locale['html_001']."' class='btn btn-sm btn-default m-b-10 button' style='font-style:italic;' onclick=\"addText('".$textarea."', '&lt;i&gt;', '&lt;/i&gt;', '".$formname."');\">I</button>\n";
        $res .= "<button type='button' value='u' title='".$locale['html_002']."' class='btn btn-sm btn-default m-b-10 button' style='text-decoration:underline;' onclick=\"addText('".$textarea."', '&lt;u&gt;', '&lt;/u&gt;', '".$formname."');\">U</button>\n";
        $res .= "<button type='button' value='strike' title='".$locale['html_003']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;del&gt;', '&lt;/del&gt;', '".$formname."');\"><del>ABC</del></button>\n";
        $res .= "<button type='button' value='blockquote' title='".$locale['html_004']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;blockquote&gt;', '&lt;/blockquote&gt;', '".$formname."');\"><i class='fa fa-quote-left'></i></button>\n";
        $res .= "<button type='button' value='hr' title='".$locale['html_005']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;hr/&gt;', '', '".$formname."');\"><i class='glyphicon glyphicon-resize-horizontal'></i></button>\n";
        $res .= "</div>\n";

        $res .= "<div class='btn-group'>\n";
        $res .= "<button type='button' value='".$locale['html_016']."' title='".$locale['html_016']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;!--PAGEBREAK--&gt;', '', '".$formname."');\"><i class='glyphicon glyphicon-minus'></i></button>\n";
        $res .= fusion_get_settings("allow_php_exe") ? "<button type='button' class='btn btn-sm btn-default button m-b-10' value='&lt;?php?&gt;' onclick=\"addText('".$textarea."', '&lt;?php\\n', '\\n?&gt;', '".$formname."');\">&lt;?php?&gt;</button>\n" : "";
        $res .= "<button type='button' class='btn btn-sm btn-default button m-b-10' value='&lt;p&gt;' onclick=\"addText('".$textarea."', '&lt;p&gt;', '&lt;/p&gt;', '".$formname."');\">&lt;p&gt;</button>\n";
        $res .= "<button type='button' class='btn btn-default btn-sm button m-b-10' value='&lt;br /&gt;' onclick=\"insertText('".$textarea."', '&lt;br /&gt;', '".$formname."');\">&lt;br /&gt;</button>\n";
        $res .= "</div>\n";

        $res .= "<div class='btn-group'>\n";
        $res .= "<button type='button' value='left' title='".$locale['html_006']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:left;\'&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-left'></i></button>\n";
        $res .= "<button type='button' value='center' title='".$locale['html_007']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:center;\'&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-center'></i></button>\n";
        $res .= "<button type='button' value='right' title='".$locale['html_008']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:right;\'&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-right'></i></button>\n";
        $res .= "<button type='button' value='justify' title='".$locale['html_009']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:justify;\'&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-justify'></i></button>\n";
        $res .= "</div>\n";

        $res .= "<div class='btn-group'>\n";
        $res .= "<button type='button' value='link' title='".$locale['html_010']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;a href=\'', '\' target=\'_blank\'>Link&lt;/a&gt;', '".$formname."');\"><i class='glyphicon glyphicon-paperclip'></i></button>\n";
        //$res .= "<button type='button' value='img' title='".$locale['html_011']."' class='btn btn-sm btn-default m-b-10 dropdown-toggle button' data-toggle='dropdown' onclick=\"addText('".$textarea."', '&lt;img src=\'".str_replace("../", "", $folder)."', '\' style=\'margin:5px\' alt=\'\' align=\'left\' /&gt;', '".$formname."');\"><i class='fa fa-picture-o'></i></button>\n";
        $res .= "<button type='button' value='center' title='".$locale['html_012']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;center&gt;', '&lt;/center&gt;', '".$formname."');\">center</button>\n";
        $res .= "<button type='button' value='small' title='".$locale['html_013']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;span class=\'small\'&gt;', '&lt;/span&gt;', '".$formname."');\">small</button>\n";
        $res .= "<button type='button' value='small2' title='".$locale['html_014']."' class='btn btn-sm  btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;span class=\'small2\'&gt;', '&lt;/span&gt;', '".$formname."');\">small2</button>\n";
        $res .= "<button type='button' value='alt' title='".$locale['html_015']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;span class=\'alt\'&gt;', '&lt;/span&gt;', '".$formname."');\">alt</button>\n";
        $res .= "</div>\n";

        if ($colors) {
            $res .= "<div class='btn-group'>\n";
            $res .= "<button title='".$locale['html_017']."' class='btn btn-sm btn-default m-b-10 button dropdown-toggle' data-toggle='dropdown'><i class='fa fa-tint m-r-5'></i> <span class='caret'></span></button>\n";
            $res .= "<ul class='dropdown-menu' role='text-color' style='width:190px;'>\n";
            $res .= "<li>\n";
            $res .= "<div class='display-block p-l-10 p-r-5 p-t-5 p-b-0' style='width:100%'>\n";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_8']."' style='background-color:#000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#993300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_136']."' style='background-color:#993300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_137']."' style='background-color:#333300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#003300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_138']."' style='background-color:#003300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#003366\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_139']."' style='background-color:#003366; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#000080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_92']."' style='background-color:#000080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333399\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_140']."' style='background-color:#333399; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333333\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_141']."' style='background-color:#333333; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "</div>\n";
            $res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#800000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_77']."' style='background-color:#800000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF6600\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_142']."' style='background-color:#FF6600; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF6600\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_35']."' style='background-color:#2F4F4F; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#008000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_51']."' style='background-color:#008000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#008080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_126']."' style='background-color:#008080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#0000FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_10']."' style='background-color:#0000FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#666699\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_143']."' style='background-color:#666699; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#808080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_50']."' style='background-color:#808080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "</div>\n";
            $res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF0000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_110']."' style='background-color:#FF0000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF9900\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_144']."' style='background-color:#FF9900; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#99CC00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_145']."' style='background-color:#99CC00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#339966\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_146']."' style='background-color:#339966; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#33CCCC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_147']."' style='background-color:#33CCCC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#3366FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_148']."' style='background-color:#3366FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#800080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_109']."' style='background-color:#800080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#999999\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_149']."' style='background-color:#999999; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "</div>\n";
            $res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF00FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_45']."' style='background-color:#FF00FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFCC00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_150']."' style='background-color:#FFCC00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFF00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_134']."' style='background-color:#FFFF00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00FF00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_75']."' style='background-color:#00FF00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00FFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_3']."' style='background-color:#00FFFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00CCFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_151']."' style='background-color:#00CCFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#993366\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_152']."' style='background-color:#993366; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_132']."' style='background-color:#FFFFFF; width:17px; margin:2px; text-decoration:none !important; box-shadow: 0 0 2px #a0a0a0;'>&nbsp;</a>";
            $res .= "</div>\n";
            $res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF99CC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_153']."' style='background-color:#FF99CC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFCC99\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_154']."' style='background-color:#FFCC99; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFF99\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_155']."' style='background-color:#FFFF99; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CCFFCC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_156']."' style='background-color:#CCFFCC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CCFFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_157']."' style='background-color:#CCFFFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#99CCFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_158']."' style='background-color:#99CCFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CC99FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_159']."' style='background-color:#CC99FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:transparent\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_0']."' style='background-color:transparent; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
            $res .= "</div>\n";
            $res .= "</li>\n";
            $res .= "</ul>\n";
            $res .= "</div>\n";
        }


        $res .= "<div class='btn-group'>\n";
        $res .= "<button type='button' title='".$locale['html_018']."' class='btn btn-sm btn-default m-b-10 button strong' onclick=\"addText('".$textarea."', '&lt;p&gt;', '&lt;/p&gt;', '".$formname."');\">".$locale['html_018']."</button>\n";
        $res .= "<button title='".$locale['html_019']."' class='dropdown-toggle btn btn-sm btn-default m-b-10 button' data-toggle='dropdown'><i class='glyphicon glyphicon-header'></i> ".$locale['html_019']." <span class='caret'></span></button>\n";
        $res .= "<ul class='dropdown-menu' role='text-heading' style='width:190px;'>\n";
        $res .= "<li>\n<a value='H1' class='pointer' onclick=\"addText('".$textarea."', '&lt;h1&gt;', '&lt;/h1&gt;', '".$formname."');\"><span class='strong' style='font-size:24px; font-family: Georgia, \'Times New Roman\', Times, serif !important;'>Heading 1</span></a>\n</li>\n";
        $res .= "<li>\n<a value='H2' class='pointer' onclick=\"addText('".$textarea."', '&lt;h2&gt;', '&lt;/h2&gt;', '".$formname."');\"><span class='strong' style='font-size:19.5px; font-family: Georgia, \'Times New Roman\', Times, serif !important;'>Heading 2</span></a>\n</li>\n";
        $res .= "<li>\n<a value='H3' class='pointer' onclick=\"addText('".$textarea."', '&lt;h3&gt;', '&lt;/h3&gt;', '".$formname."');\"><span class='strong' style='font-size:15.5px; font-family: Georgia, \'Times New Roman\', Times, serif !important;'>Heading 3</span></a>\n</li>\n";
        $res .= "<li>\n<a value='H4' class='pointer' onclick=\"addText('".$textarea."', '&lt;h4&gt;', '&lt;/h4&gt;', '".$formname."');\"><span class='strong' style='font-size:13px; font-family: Georgia, \'Times New Roman\', Times, serif !important;'>Heading 4</span></a>\n</li>\n";
        $res .= "<li>\n<a value='H5' class='pointer' onclick=\"addText('".$textarea."', '&lt;h5&gt;', '&lt;/h5&gt;', '".$formname."');\"><span class='strong' style='font-size:11px; font-family: Georgia, \'Times New Roman\', Times, serif !important;'>Heading 5</span></a>\n</li>\n";
        $res .= "<li>\n<a value='H6' class='pointer' onclick=\"addText('".$textarea."', '&lt;h6&gt;', '&lt;/h6&gt;', '".$formname."');\"><span class='strong' style='font-size:9px; font-family: Georgia, \'Times New Roman\', Times, serif !important;'>Heading 6</span></a>\n</li>\n";
        $res .= "</ul>\n";
        $res .= "</div>\n";

        if ($images && $folder) {
            if (is_array($folder)) {
                $options = array();
                foreach ($folder as $dir) {
                    if (file_exists($dir)) {
                        $file_list = makefilelist($dir, '.|..|index.php', TRUE, 'files', 'js|psd|rar|zip|7s|_DS_STORE|doc|docx|docs|md|php');
                        if (!empty($file_list)) {
                            foreach ($file_list as $file) {
                                $options[str_replace('../', '', $dir).$file] = $file;
                            }
                        }
                    }
                }
            } else {
                if (file_exists($folder)) {
                    $file_list = makefilelist($folder, '.|..|index.php', TRUE, 'files', 'js|psd|rar|zip|7s|_DS_STORE|doc|docx|docs|md|php');
                    if (!empty($file_list)) {
                        foreach ($file_list as $file) {
                            $options[str_replace('../', '', $folder).$file] = $file;
                        }
                    }
                }
            }

            $res .= form_select($textarea.'-insertimage', '', '',
                                array(
                                    'options'     => $options,
                                    'placeholder' => $locale['html_011'],
                                    'allowclear'  => TRUE,
                                    'width'       => '200px',
                                    'class'       => 'm-0'

                                )
            );
            add_to_jquery("
            $('#$textarea-insertimage').bind('change', function(e){
                insertText('$textarea', '<img src=\"".fusion_get_settings('siteurl')."'+$(this).val()+'\" alt=\"\" class=\"img-responsive\" style=\"margin:5px;\"/>', '$formname');
                $(this).select2('val', '');
            });
            ");
        }


    }

    return $res;
}

