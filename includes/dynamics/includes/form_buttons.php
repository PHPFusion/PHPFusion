<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_buttons.php
| Author: Frederick MC CHan (Hien)
| Co-Author : Tyler Hurlbut
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_button($title, $input_name, $input_id, $input_value, $array = FALSE) {
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$html = "";
	if (!is_array($array)) {
		$class = "btn-default";
		$icon = "";
		$type = '';
		$icon_stack = 0;
		$block = 0;
		$btn_block = '';
		$deactivate = '';
	} else {
		$class = array_key_exists("class", $array) ? stripinput($array['class']) : "btn-default";
		$icon = array_key_exists("icon", $array) ? $array['icon'] : "";
		$deactivate = array_key_exists("deactivate", $array) && ($array['deactivate'] == 1) ? 1 : 0;
		$icon_stack = (array_key_exists("icon_stack", $array) && isnum($array['icon_stack']) && ($array['icon_stack'] == 1)) ? 1 : 0;
		$type = (array_key_exists("type", $array) && ($array['type'])) ? $array['type'] : '';
		$block = (array_key_exists("block", $array) && ($array['block'] == 1)) ? 1 : 0;
		$btn_block = ($block == 1) ? "btn-block" : "";
	}
	if ($type == 'link') {
		$html .= "<a id='".$input_id."' class='".($deactivate ? 'disabled' : '')." btn $class button' href='".$input_name."' data-value='".$input_value."' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</a>";
	} elseif ($type == 'button') {
		$html .= "<button id='".$input_id."' class='".($deactivate ? 'disabled' : '')." btn $class button' name='".$input_name."' value='".$input_value."' type='button' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</button>";
	} else {
		$html .= "<button id='".$input_id."' class='".($deactivate ? 'disabled' : '')." btn $class button' name='".$input_name."' value='".$input_value."' type='submit' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</button>";
	}
	//$html .= ($token == '1') ? generate_token($input_id) : '';
	return $html;
}

function form_btngroup($title, $input_name, $input_id, $options, $input_value, $array = FALSE) {
	$title = (isset($title) && (!empty($title))) ? stripinput($title) : "";
	$title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	$input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";
	if (!is_array($array)) {
		$class = 'small';
		$justified = "";
		$well = "";
		$wellclass = "";
		$helper_text = "";
		$slider = 0;
		$required = 0;
		$safemode = 0;
		$inline = '';
	} else {
		$class = (array_key_exists("class", $array)) ? $array['class'] : "small";
		$justified = (array_key_exists("justified", $array)) ? "btn-group-justified" : "";
		$well = (array_key_exists('well', $array)) ? "style='margin-top:-10px;'" : "";
		$helper_text = (array_key_exists("helper", $array)) ? $array['helper'] : "";
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$inline = (array_key_exists("rowstart", $array)) ? 1 : 0;
	}
	$html = "";
	$html .= (!$inline) ? "<div class='field'/>\n" : '';
	$html .= "<label><h3>$title</h3></label>\n";
	$html .= "<div class='ui buttons $justified' id='".$input_id."'>";
	$x = 1;
	$active = '';
	foreach ($options as $arr => $v) {
		if (($input_value == $arr)) {
			$active = "active";
		} else {
			$active = '';
		}
		$html .= "<span data-value='$arr' class='ui button $class ".((count($options) == $x ? 'last-child' : ''))." $active'/>";
		$html .= "$v";
		$html .= "</span>";
		$x++;
	}
	$html .= "<input readonly type='hidden' id='".$input_id."-text' value='$input_value'>\n";
	$html .= "</div>\n";
	add_to_jquery("
        $('#".$input_id." span').bind('click', function(e){
            $('#".$input_id." span').removeClass('active');
            $(this).toggleClass('active');
            value = $(this).data('value');
            $('#".$input_id."-text').val(value);
        });
        ");
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly>";
	$html .= (!$inline) ? "</div/>\n" : '';
	return $html;
}

?>