<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: html_buttons_include.php
| Author: Nick Jones (Digitanium)
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
include LOCALE.LOCALESET."admin/html_buttons.php";
function display_html($formname, $textarea, $html = TRUE, $colors = FALSE, $images = FALSE, $folder = "") {
	global $locale;
	$res = "";

	if ($html) {
		$res .= "<div class='btn-group'>\n";
		$res .= "<button type='button' value='b' title='".$locale['html_000']."' class='btn btn-sm btn-default m-b-10 button' style='font-weight:bold;' onclick=\"addText('".$textarea."', '&lt;strong&gt;', '&lt;/strong&gt;', '".$formname."');\"><i class='glyphicon glyphicon-bold'></i></button>\n";
		$res .= "<button type='button' value='i' title='".$locale['html_001']."' class='btn btn-sm btn-default m-b-10 button' style='font-style:italic;' onclick=\"addText('".$textarea."', '&lt;i&gt;', '&lt;/i&gt;', '".$formname."');\">I</button>\n";
		$res .= "<button type='button' value='u' title='".$locale['html_002']."' class='btn btn-sm btn-default m-b-10 button' style='text-decoration:underline;' onclick=\"addText('".$textarea."', '&lt;u&gt;', '&lt;/u&gt;', '".$formname."');\">U</button>\n";
		$res .= "<button type='button' value='strike' title='".$locale['html_003']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;del&gt;', '&lt;/del&gt;', '".$formname."');\"><del>ABC</del></button>\n";
		$res .= "<button type='button' value='blockquote' title='".$locale['html_004']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;blockquote&gt;', '&lt;/blockquote&gt;', '".$formname."');\"><i class='entypo iquote'></i></button>\n";
		$res .= "<button type='button' value='hr' title='".$locale['html_005']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;hr/&gt;', '', '".$formname."');\"><i class='glyphicon glyphicon-resize-horizontal'></i></button>\n";
		$res .= "</div>\n";
		$res .= "<div class='btn-group'>\n";
		$res .= "<button type='button' value='left' title='".$locale['html_006']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:left;\'/&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-left'></i></button>\n";
		$res .= "<button type='button' value='center' title='".$locale['html_007']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:center;\'/&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-center'></i></button>\n";
		$res .= "<button type='button' value='right' title='".$locale['html_008']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:right;\'/&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-right'></i></button>\n";
		$res .= "<button type='button' value='justify' title='".$locale['html_009']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:justify;\'/&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-justify'></i></button>\n";
		$res .= "</div>\n";
		$res .= "<div class='btn-group'>\n";
		$res .= "<button type='button' value='link' title='".$locale['html_010']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;a href=\'', '\' target=\'_blank\'>Link&lt;/a&gt;', '".$formname."');\"><i class='glyphicon glyphicon-paperclip'></i></button>\n";
		$res .= "<button type='button' value='img' title='".$locale['html_011']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;img src=\'".str_replace("../", "", $folder)."', '\' style=\'margin:5px\' alt=\'\' align=\'left\' /&gt;', '".$formname."');\"><i class='entypo picture'></i></button>\n";
		$res .= "<button type='button' value='center' title='".$locale['html_012']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;center&gt;', '&lt;/center&gt;', '".$formname."');\">center</button>\n";
		$res .= "<button type='button' value='small' title='".$locale['html_013']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;span class=\'small\'&gt;', '&lt;/span&gt;', '".$formname."');\">small</button>\n";
		$res .= "<button type='button' value='small2' title='".$locale['html_014']."' class='btn btn-sm  btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;span class=\'small2\'&gt;', '&lt;/span&gt;', '".$formname."');\">small2</button>\n";
		$res .= "<button type='button' value='alt' title='".$locale['html_015']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;span class=\'alt\'&gt;', '&lt;/span&gt;', '".$formname."');\">alt</button>\n";
		$res .= "<button type='button' value='".$locale['html_016']."' title='".$locale['html_016']."' class='btn btn-sm btn-default m-b-10 button' onclick=\"addText('".$textarea."', '&lt;!--PAGEBREAK--&gt;', '', '".$formname."');\"><i class='glyphicon glyphicon-minus'></i></button>\n";
		if ($colors) {
			$res .= "<div class='btn-group'>\n";
			$res .= "<button title='".$locale['html_017']."' class='dropdown-toggle btn btn-sm btn-default m-b-10 button strong text-bigger' style='padding:2px 10px 3px;' data-toggle='dropdown'><i style='text-decoration: underline !important; font-weight:bold;'>A</i><span class='caret'></span></button>\n";
			$res .= "<ul class='dropdown-menu' role='text-color' style='width:190px;'>\n";
			$res .= "<li>\n";
			$res .= "<div class='display-block p-l-10 p-r-5 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_000']."' style='background-color:#000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#993300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_001']."' style='background-color:#993300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_002']."' style='background-color:#333300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#003300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_003']."' style='background-color:#003300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#003366\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_004']."' style='background-color:#003366; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#000080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_005']."' style='background-color:#000080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333399\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_006']."' style='background-color:#333399; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333333\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_007']."' style='background-color:#333333; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#800000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_008']."' style='background-color:#800000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF6600\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_009']."' style='background-color:#FF6600; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF6600\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_010']."' style='background-color:#FF6600; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#008000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_011']."' style='background-color:#008000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#008080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_012']."' style='background-color:#008080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#0000FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_013']."' style='background-color:#0000FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#666699\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_014']."' style='background-color:#666699; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#808080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_015']."' style='background-color:#808080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF0000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_016']."' style='background-color:#FF0000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF9900\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_017']."' style='background-color:#FF9900; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#99CC00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_018']."' style='background-color:#99CC00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#339966\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_019']."' style='background-color:#339966; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#33CCCC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_020']."' style='background-color:#33CCCC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#3366FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_021']."' style='background-color:#3366FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#800080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_022']."' style='background-color:#800080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#999999\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_023']."' style='background-color:#999999; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF00FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_024']."' style='background-color:#FF00FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFCC00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_025']."' style='background-color:#FFCC00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFF00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_026']."' style='background-color:#FFFF00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00FF00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_027']."' style='background-color:#00FF00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00FFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_028']."' style='background-color:#00FFFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00CCFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_029']."' style='background-color:#00CCFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#993366\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_030']."' style='background-color:#993366; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_031']."' style='background-color:#FFFFFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF99CC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_032']."' style='background-color:#FF99CC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFCC99\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_033']."' style='background-color:#FFCC99; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFF99\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_034']."' style='background-color:#FFFF99; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CCFFCC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_035']."' style='background-color:#CCFFCC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CCFFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_036']."' style='background-color:#CCFFFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#99CCFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_037']."' style='background-color:#99CCFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CC99FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_038']."' style='background-color:#CC99FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:transparent\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='".$locale['color_039']."' style='background-color:transparent; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "</li>\n";
			$res .= "</ul>\n";
			$res .= "</div>\n";
		}
		$res .= "</div>\n";

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

	}

	if ($images && $folder) {
		$image_files = makefilelist($folder, ".|..|index.php", TRUE);
		$image_list = makefileopts($image_files);
		//$res .= "<select name='insertimage' class='form-control textbox' style='margin-top:5px' onchange=\"insertText('".$textarea."', '&lt;img src=\'".str_replace("../", "", $folder)."' + this.options[this.selectedIndex].value + '\' alt=\'\' style=\'margin:5px\' align=\'left\' /&gt;', '".$formname."');this.selectedIndex=0;\">\n";
		//$res .= "<option value=''>".$locale['html401']."</option>\n".$image_list."</select>\n";
	}
	return $res;
}

?>