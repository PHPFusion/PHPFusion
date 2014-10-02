<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_text.php
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
function form_text($title = FALSE, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	$html = '';
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if (!is_array($array)) {
		$required = 0;
		$placeholder = "";
		$deactivate = "";
		$width = "100%";
		$class = "";
		$inline = '';
		$type = "";
		$length = 200;
		$number = 0;
		$error_text = '';
		$safemode = 0;
		$type_config = "textbox";
		$icon = '';
		$append_button = '';
		$append_value = '';
		$append_size = '';
		$append_class = '';
		$prepend_button = '';
		$prepend_value = '';
		$prepend_size = '';
		$prepend_class = '';
		$autocomplete_off = 0;
	} else {
		$icon = (array_key_exists('icon', $array)) ? $array['icon'] : "";
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$class = (array_key_exists('class', $array)) ? $array['class'] : "";
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? '1' : '0';
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? '1' : '0';
		$width = (array_key_exists('width', $array)) ? $array['width'] : "";
		$type = (array_key_exists("password", $array) && $array['password'] == 1) ? "password" : "text";
		// for defender.
		if (array_key_exists("password", $array) && ($array['password'] == 1)) {
			$type_config = "password";
		} elseif (array_key_exists("email", $array) && ($array['email'] == 1)) {
			$type_config = "email";
		} elseif (array_key_exists("number", $array) && ($array['number'] == 1)) {
			$type_config = "number";
		} elseif (array_key_exists("url", $array) && ($array['url'] == 1)) {
			$type_config = "url";
		} else {
			$type_config = "textbox";
		}
		$append_button = (array_key_exists('append_button', $array) && $array['append_button']) ? 1 : 0;
		$append_value = (array_key_exists('append_value', $array) && $array['append_value']) ? $array['append_value'] : "<i class='entypo search'></i>";
		$append_class = (array_key_exists('append_class', $array) && $array['append_class']) ? $array['append_class'] : "btn-default";
		$append_size = (array_key_exists('append_size', $array) && $array['append_size']) ? $array['append_size'] : '';
		$append_type = (array_key_exists('append_type', $array) && $array['append_type']) ? $array['append_type'] : 'submit';
		$prepend_button = (array_key_exists('prepend_button', $array) && $array['prepend_button']) ? 1 : 0;
		$prepend_value = (array_key_exists('prepend_value', $array) && $array['prepend_value']) ? $array['prepend_value'] : "<i class='entypo search'></i>";
		$prepend_class = (array_key_exists('prepend_class', $array) && $array['prepend_class']) ? $array['prepend_class'] : "btn-default";
		$prepend_size = (array_key_exists('prepend_size', $array) && $array['prepend_size']) ? $array['prepend_size'] : "";
		$prepend_type = (array_key_exists('prepend_type', $array) && $array['prepend_type']) ? $array['prepend_type'] : 'submit';
		$number = (array_key_exists("number", $array) && $array['number'] == 1) ? 1 : 0;
		$length = (array_key_exists("max_length", $array)) ? $array['max_length'] : 50;
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
		$autocomplete_off = (array_key_exists("autocomplete_off", $array) && $array['autocomplete_off'] == 1) ? 1 : 0;
	}
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 $class ".($icon ? 'has-feedback' : '')."'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	if ($append_button || $prepend_button) {
		$html .= "<div class='input-group'>\n";
	}
	$html .= ($prepend_button) ? "<span class='input-group-btn'>\n<button type='$prepend_type' value='submit-".$input_name."' class='btn $prepend_size $prepend_class'>$prepend_value</button></span>" : '';
	$html .= "<input type='$type' class='form-control textbox' ".($width ? "style='width:$width;'" : '')." ".($length ? "maxlength='".$length."'" : '')." name='$input_name' id='".$input_id."' value='$input_value' placeholder='".$placeholder."' ".($autocomplete_off ? "autocomplete='off'" : '')." ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">";
	$html .= ($append_button) ? "<span class='input-group-btn'><button type='$append_type' value='submit-".$input_name."' class='btn $append_size $append_class'>$append_value</button></span>" : '';
	$html .= ($icon) ? "<div class='form-control-feedback'><i class='glyphicon $icon'></i></div>\n" : '';
	if ($append_button || $prepend_button) {
		$html .= "</div>\n";
	}
	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";
	// Generate Defender Strings
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=$type_config],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' readonly />";
	if ($number == "1") {
		add_to_jquery("
                $('#".$input_id."').keypress(function(e)
                   {
                     var key_codes = [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 8];
                     if (!($.inArray(e.which, key_codes) >= 0)) {
                       e.preventDefault();
                     }
                   });
        ");
	}
	return $html;
}

?>