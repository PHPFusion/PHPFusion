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
		$res .= "<button type='button' value='b' class='btn btn-sm btn-default button' style='font-weight:bold;' onclick=\"addText('".$textarea."', '&lt;strong&gt;', '&lt;/strong&gt;', '".$formname."');\"><i class='glyphicon glyphicon-bold'></i></button>\n";
		$res .= "<button type='button' value='i' class='btn btn-sm btn-default button' style='font-style:italic;' onclick=\"addText('".$textarea."', '&lt;i&gt;', '&lt;/i&gt;', '".$formname."');\">I</button>\n";
		$res .= "<button type='button' value='u' class='btn btn-sm btn-default button' style='text-decoration:underline;' onclick=\"addText('".$textarea."', '&lt;u&gt;', '&lt;/u&gt;', '".$formname."');\">U</button>\n";
		$res .= "<button type='button' value='u' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;del&gt;', '&lt;/del&gt;', '".$formname."');\"><del>ABC</del></button>\n";
		$res .= "<button type='button' value='u' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;blockquote&gt;', '&lt;/blockquote&gt;', '".$formname."');\"><i class='entypo iquote'></i></button>\n";
		$res .= "<button type='button' value='u' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;hr/&gt;', '', '".$formname."');\"><i class='entypo minus'></i></button>\n";
		$res .= "<button type='button' value='u' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:left;\'/&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-left'></i></button>\n";
		$res .= "<button type='button' value='u' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:center;\'/&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-center'></i></button>\n";
		$res .= "<button type='button' value='u' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;p style=\'text-align:right;\'/&gt;', '&lt;/p&gt;', '".$formname."');\"><i class='glyphicon glyphicon-align-right'></i></button>\n";
		$res .= "<button type='button' value='link' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;a href=\'', '\' target=\'_blank\'>Link&lt;/a&gt;', '".$formname."');\"><i class='glyphicon glyphicon-paperclip'></i></button>\n";
		$res .= "<button type='button' value='img' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;img src=\'".str_replace("../", "", $folder)."', '\' style=\'margin:5px\' alt=\'\' align=\'left\' /&gt;', '".$formname."');\"><i class='entypo picture'></i></button>\n";
		$res .= "<button type='button' value='center' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;center&gt;', '&lt;/center&gt;', '".$formname."');\">center</button>\n";
		$res .= "<button type='button' value='small' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;span class=\'small\'&gt;', '&lt;/span&gt;', '".$formname."');\">small</button>\n";
		$res .= "<button type='button' value='small2' class='btn btn-sm  btn-default button' onclick=\"addText('".$textarea."', '&lt;span class=\'small2\'&gt;', '&lt;/span&gt;', '".$formname."');\">small2</button>\n";
		$res .= "<button type='button' value='alt' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;span class=\'alt\'&gt;', '&lt;/span&gt;', '".$formname."');\">alt</button>\n";
		if ($colors) {
			$res .= "<div class='btn-group'>\n";
			$res .= "<button class='dropdown-toggle btn btn-sm btn-default button' data-toggle='dropdown'><i class='glyphicon glyphicon-adjust'></i></button>\n";
			$res .= "<ul class='dropdown-menu' role='text-color' style='width:190px;'>\n";
			$res .= "<li>\n";
			$res .= "<div class='display-block p-l-10 p-r-5 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Black' style='background-color:#000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#993300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Burnt Orange' style='background-color:#993300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Dark olive' style='background-color:#333300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#003300\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Dark green' style='background-color:#003300; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#003366\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Dark azure' style='background-color:#003366; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#000080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Navy Blue' style='background-color:#000080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333399\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Indigo' style='background-color:#333399; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#333333\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Dark grey' style='background-color:#333333; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#800000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Maroon' style='background-color:#800000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF6600\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Orange' style='background-color:#FF6600; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF6600\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Olive' style='background-color:#FF6600; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#008000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Green' style='background-color:#008000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#008080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Teal' style='background-color:#008080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#0000FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Blue' style='background-color:#0000FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#666699\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Grayish Blue' style='background-color:#666699; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#808080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Gray' style='background-color:#808080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF0000\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Red' style='background-color:#FF0000; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF9900\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Amber' style='background-color:#FF9900; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#99CC00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Light green' style='background-color:#99CC00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#339966\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Sea green' style='background-color:#339966; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#33CCCC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Turqoise' style='background-color:#33CCCC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#3366FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Royal Blue' style='background-color:#3366FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#800080\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Purple' style='background-color:#800080; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#999999\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Medium Gray' style='background-color:#999999; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF00FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Magenta' style='background-color:#FF00FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFCC00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Gold' style='background-color:#FFCC00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFF00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Yellow' style='background-color:#FFFF00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00FF00\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Lime' style='background-color:#00FF00; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00FFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Aqua' style='background-color:#00FFFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#00CCFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Sky Blue' style='background-color:#00CCFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#993366\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Red voilet' style='background-color:#993366; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='White' style='background-color:#FFFFFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "<div class='display-block p-l-10 p-r-10 p-t-5 p-b-0' style='width:100%'>\n";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FF99CC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Pink' style='background-color:#FF99CC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFCC99\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Peach' style='background-color:#FFCC99; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#FFFF99\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Light yellow' style='background-color:#FFFF99; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CCFFCC\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Pale green' style='background-color:#CCFFCC; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CCFFFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Pale cyan' style='background-color:#CCFFFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#99CCFF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Pale blue' style='background-color:#99CCFF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:#CC99FF\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='Plum' style='background-color:#CC99FF; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "<a class='display-inline-block' onclick=\"addText('".$textarea."', '&lt;span style=\'color:transparent\'&gt;', '&lt;/span&gt;', '".$formname."');\" title='No Color' style='background-color:transparent; width:17px; margin:2px; text-decoration:none !important;'>&nbsp;</a>";
			$res .= "</div>\n";
			$res .= "</li>\n";
			$res .= "</ul>\n";
			$res .= "</div>\n";
		}
		$res .= "</div>\n";
	}

	/*if ($colors) {
		$color_array = array(
			'maroon' => $locale['html402'],
			'red' => $locale['html403'],
			'orange' => $locale['html404'],
			'brown' => $locale['html405'],
			'yellow' => $locale['html406'],
			'green' => $locale['html407'],
			'lime' => $locale['html408'],
			'olive' => $locale['html409'],
			'cyan' => $locale['html410'],
			'blue' => $locale['html411'],
			'navy' => $locale['html412'],
			'purple' => $locale['html413'],
			'violet' => $locale['html414'],
			'black' => $locale['html415'],
			'gray' => $locale['html416'],
			'silver' => $locale['html417'],
			'white' => $locale['html418']
		);
		$placeholder = $locale['html400'];
		$seed = rand(0, 1000000);
		$res .= form_select('', "setcolor-$formname", "setcolor-$formname-$seed", $color_array, '', array('placeholder' => $placeholder, 'class' => 'pull-left m-r-10', 'allowclear' => 1));
		add_to_jquery("
                function color(item) {
                if(!item.id) {return item.text;}
                var color = item.text;
                return '<table><tr><td><label style=\'display: inline-block; width: 10px; height:13px; margin: 0 10px 0 5px; padding: 0px 8px; background:'+item.text+'\'></label>'+item.text+'</td></tr></table>';
                }
                $('#setcolor-$formname-$seed').select2({
                formatSelection: color,
                escapeMarkup: function(m) { return m; },
                formatResult: color,
                placeholder:'$placeholder',
                allowClear:true,
                });
            $('#setcolor-$formname').on('change', function(e){
            addText('".$textarea."', '<span style=\'color:' + this.options[this.selectedIndex].value + '\'>', '</span>', '".$formname."');
			this.selectedIndex=0;
            $(this).select2({
                formatSelection: color,
                escapeMarkup: function(m) { return m; },
                formatResult: color,
                placeholder:'$placeholder',
                allowClear:true}).val('');
            });
        ");
	} */
	if ($images && $folder) {
		$image_files = makefilelist($folder, ".|..|index.php", TRUE);
		$image_list = makefileopts($image_files);
		//$res .= "<select name='insertimage' class='form-control textbox' style='margin-top:5px' onchange=\"insertText('".$textarea."', '&lt;img src=\'".str_replace("../", "", $folder)."' + this.options[this.selectedIndex].value + '\' alt=\'\' style=\'margin:5px\' align=\'left\' /&gt;', '".$formname."');this.selectedIndex=0;\">\n";
		//$res .= "<option value=''>".$locale['html401']."</option>\n".$image_list."</select>\n";
	}
	return $res;
}

?>