<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_name.php
| Author: Frederick MC CHan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_name($title = FALSE, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	if (isset($title) && ($title !== "")) {
		$title = stripinput($title);
	} else {
		$title = "";
	}
	if (isset($input_name) && ($input_name !== "")) {
		$input_name = stripinput($input_name);
	} else {
		$input_name = "";
	}
	if (isset($input_id) && ($input_id !== "")) {
		$input_id = stripinput($input_id);
	} else {
		$input_id = "";
	}
	$html = '';
	if (!is_array($array)) {
		$array = array();
		$state_validation = "";
		$before = "";
		$after = "";
		$required = "";
		$placeholder = "";
		$deactivate = "";
		$width = "";
		$class = "input-sm";
		$well = '';
		$inline = '';
		$error_text = '';
	} else {
		$before = (array_key_exists('before', $array)) ? $array['before'] : "";
		$after = (array_key_exists('after', $array)) ? $array['after'] : "";
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$class = (array_key_exists('class', $array)) ? $array['class'] : "input-sm";
		$required = (array_key_exists('required', $array)) ? $array['required'] : "";
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? '1' : '0';
		$width = (array_key_exists('width', $array)) ? "style='width: ".$array['width']."'" : "";
		$well = (array_key_exists('well', $array)) ? "style='margin-top:-10px;'" : "";
		$type = (array_key_exists('password', $array) && ($array['password'] == "1")) ? "password" : "text";
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
		$inline = (array_key_exists("rowstart", $array)) ? 1 : 0;
	}
	// readback value
	if (isset($input_value) && (!empty($input_value))) {
		if (!is_array($input_value)) {
			$input_value = construct_array($input_value, '', '|');
		}
	} else {
		$input_value['0'] = "";
		$input_value['1'] = "";
		$input_value['2'] = "";
	}
	$html .= (!$inline) ? "<div id='$input_id-field' class='three fields'/>\n" : '';
	$html .= "<div id='$input_id-0-field' class='field'>\n";
	$html .= ($title) ? "<label for='$input_id-0'/><h3>First Name ".($required == 1 ? "<span class='required'>*</span>" : '')."</h3></label>\n" : '';
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<input type='text' id='".$input_id."-0' name='".$input_name."[]' class='$class' value='".$input_value['0']."' placeholder='First Name' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">";
	$html .= ($required) ? "<div class='ui corner label'/><i class='icon asterisk'/></i></div>\n" : '';
	$html .= "<div id='$input_id-0-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<div id='$input_id-1-field' class='field'>\n";
	$html .= ($title) ? "<label for='$input_id-1'/><h3>Middle Name</h3></label>\n" : '';
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<input type='text' id='".$input_id."-1' name='".$input_name."[]' class='$class' value='".$input_value['1']."' placeholder='Middle Name' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">";
	$html .= "<div id='$input_id-1-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<div id='$input_id-2-field'  class='field'>\n";
	$html .= ($title) ? "<label for='$input_id-2'/><h3>Last Name ".($required == 1 ? "<span class='required'>*</span>" : '')."</h3></label>\n" : '';
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<input type='text' id='".$input_id."-2' name='".$input_name."[]' class='form-control $class' value='".$input_value['2']."' placeholder='Last Name' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">";
	$html .= ($required) ? "<div class='ui corner label'/><i class='icon asterisk'/></i></div>\n" : '';
	$html .= "<div id='$input_id-2-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=textbox],[title=First Name],[id=".$input_id."-0],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' />";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=textbox],[title=Middle Name],[id=".$input_id."-1],[required=0],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' />";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=textbox],[title=Last Name],[id=".$input_id."-2],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' />";
	return $html;
}

?>