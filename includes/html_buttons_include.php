<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
include LOCALE.LOCALESET."admin/html_buttons.php";
function display_html($formname, $textarea, $html = TRUE, $colors = FALSE, $images = FALSE, $folder = "") {
	global $locale;
	$res = "";
	if ($html) {
		$res .= "<div class='btn-group m-b-10'>\n";
		$res .= "<button type='button' value='b' class='btn btn-sm btn-default button' style='font-weight:bold;' onclick=\"addText('".$textarea."', '&lt;strong&gt;', '&lt;/strong&gt;', '".$formname."');\">b</button>\n";
		$res .= "<button type='button' value='i' class='btn btn-sm btn-default button' style='font-style:italic;' onclick=\"addText('".$textarea."', '&lt;i&gt;', '&lt;/i&gt;', '".$formname."');\">i</button>\n";
		$res .= "<button type='button' value='u' class='btn btn-sm btn-default button' style='text-decoration:underline;' onclick=\"addText('".$textarea."', '&lt;u&gt;', '&lt;/u&gt;', '".$formname."');\">u</button>\n";
		$res .= "<button type='button' value='link' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;a href=\'', '\' target=\'_blank\'>Link&lt;/a&gt;', '".$formname."');\">link</button>\n";
		$res .= "<button type='button' value='img' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;img src=\'".str_replace("../", "", $folder)."', '\' style=\'margin:5px\' alt=\'\' align=\'left\' /&gt;', '".$formname."');\">img</button>\n";
		$res .= "<button type='button' value='center' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;center&gt;', '&lt;/center&gt;', '".$formname."');\">center</button>\n";
		$res .= "<button type='button' value='small' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;span class=\'small\'&gt;', '&lt;/span&gt;', '".$formname."');\">small</button>\n";
		$res .= "<button type='button' value='small2' class='btn btn-sm  btn-default button' onclick=\"addText('".$textarea."', '&lt;span class=\'small2\'&gt;', '&lt;/span&gt;', '".$formname."');\">small2</button>\n";
		$res .= "<button type='button' value='alt' class='btn btn-sm btn-default button' onclick=\"addText('".$textarea."', '&lt;span class=\'alt\'&gt;', '&lt;/span&gt;', '".$formname."');\">alt</button>\n";
		$res .= "</div>\n";
	}
	if ($colors) {
		$color_array = array('maroon' => $locale['html402'], 'red' => $locale['html403'],
							 'orange' => $locale['html404'], 'brown' => $locale['html405'],
							 'yellow' => $locale['html406'], 'green' => $locale['html407'],
							 'lime' => $locale['html408'], 'olive' => $locale['html409'], 'cyan' => $locale['html410'],
							 'blue' => $locale['html411'], 'navy' => $locale['html412'], 'purple' => $locale['html413'],
							 'violet' => $locale['html414'], 'black' => $locale['html415'],
							 'gray' => $locale['html416'], 'silver' => $locale['html417'],
							 'white' => $locale['html418'],);
		$placeholder = $locale['html400'];
		$res .= form_select('', "setcolor-$formname", "setcolor-$formname", $color_array, '', array('placeholder' => $placeholder,
																									'class' => 'pull-left m-r-10',
																									'allowclear' => 1));
		add_to_jquery("
                function color(item) {
                if(!item.id) {return item.text;}
                var color = item.text;
                return '<table><tr><td><label style=\'display: inline-block; width: 18px; height:18px; margin:3px; margin-right:5px; padding: 0px 8px; background:'+item.text+'\'></label>'+item.text+'</td></tr></table>';
                }
                $('#setcolor-$formname').select2({
                formatSelection: color,
                escapeMarkup: function(m) { return m; },
                formatResult: color,
                placeholder:'$placeholder',
                allowClear:true,
                });
            $('#setcolor-$formname').on('change', function(e){
            addText('".$textarea."', '<span style=\'color:' + this.options[this.selectedIndex].value + '\'>', '</span>', '".$formname."');this.selectedIndex=0;
            $(this).select2({
                formatSelection: color,
                escapeMarkup: function(m) { return m; },
                formatResult: color,
                placeholder:'$placeholder',
                allowClear:true}).val('');
            });
        ");
	}
	if ($images && $folder) {
		$image_files = makefilelist($folder, ".|..|index.php", TRUE);
		$image_list = makefileopts($image_files);
		$res .= "<select name='insertimage' class='form-control textbox' style='margin-top:5px' onchange=\"insertText('".$textarea."', '&lt;img src=\'".str_replace("../", "", $folder)."' + this.options[this.selectedIndex].value + '\' alt=\'\' style=\'margin:5px\' align=\'left\' /&gt;', '".$formname."');this.selectedIndex=0;\">\n";
		$res .= "<option value=''>".$locale['html401']."</option>\n".$image_list."</select>\n";
	}
	return $res;
}

?>