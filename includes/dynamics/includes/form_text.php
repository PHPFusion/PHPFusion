<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_text.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_text($title = FALSE, $input_name, $input_id, $input_value = FALSE, $options = FALSE) {
	$html = '';
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if (!is_array($options)) {
		$required = 0;
		$placeholder = '';
		$deactivate = '';
		$width = '100%';
		$class = '';
		$inline = '';
		$type = '';
		$length = 200;
		$number = 0;
		$error_text = '';
		$safemode = 0;
		$type_config = 'textbox';
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
		$icon = isset($options['icon']) && $options['icon'] ? $options['icon'] : '';
		$placeholder = isset($options['placeholder']) && $options['placeholder'] ? $options['placeholder'] : '';
		$deactivate = isset($options['deactivate']) && $options['deactivate'] == 1 ? 1 : 0;
		$class = isset($options['class']) && $options['class'] ? $options['class'] : '';
		$required = isset($options['required']) && $options['required'] == 1 ? 1 : 0;
		$safemode = isset($options['safemode']) && $options['safemode'] == 1  ? 1 : 0;
		$width = isset($options['width']) && $options['width'] ? $options['width'] : '';
		$type = isset($options['password']) && $options['password'] == 1 ? 'password' : 'text';
		// for defender.
		$type_config = 'textbox';
		if (isset($options['password']) && $options['password'] == 1) {
			$type_config = "password";
		} elseif (isset($options['email']) && $options['email'] == 1) {
			$type_config = "email";
		} elseif (isset($options['number']) && $options['number'] == 1) {
			$type_config = "number";
		} elseif (isset($options['url']) && $options['url'] == 1) {
			$type_config = "url";
		}
		$append_button = isset($options['append_button']) && $options['append_button'] == 1 ? 1 : 0;
		$append_value = isset($options['append_value']) && $options['append_value'] ? $options['append_value'] : "<i class='entypo search'></i>";
		$append_class = isset($options['append_class']) && $options['append_class'] ? $options['append_class'] : "btn-default";
		$append_size = isset($options['append_size']) && $options['append_size'] ? $options['append_size'] : '';
		$append_type = isset($options['append_type']) && $options['append_type'] ? $options['append_type'] : 'submit';
		$prepend_button = isset($options['prepend_button']) && $options['prepend_button'] ? 1 : 0;
		$prepend_value = isset($options['prepend_value']) && $options['prepend_value'] ? $options['prepend_value'] : "<i class='entypo search'></i>";
		$prepend_class = isset($options['prepend_class']) && $options['prepend_class'] ? $options['prepend_class'] : "btn-default";
		$prepend_size = isset($options['prepend_size']) && $options['prepend_size'] ? $options['prepend_size'] : "";
		$prepend_type = isset($options['prepend_type']) && $options['prepend_type'] ? $options['prepend_type'] : 'submit';
		$number = isset($options['number']) && $options['number'] == 1 ? 1 : 0;
		$length = isset($options['max_length']) && isnum($options['max_length']) ? $options['max_length'] : 50;
		$inline = isset($options['inline']) && $options['inline'] == 1 ? 1 : 0;
		$error_text = isset($options['error_text']) && $options['error_text'] ? $options['error_text'] : "";
		$autocomplete_off = isset($options['autocomplete_off']) && $options['autocomplete_off'] == 1 ? 1 : 0;
	}

	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 $class ".($icon ? 'has-feedback' : '')."'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	if ($append_button || $prepend_button) {
		$html .= "<div class='input-group'>\n";
	}
	$html .= ($prepend_button) ? "<span class='input-group-btn'>\n<button type='$prepend_type' value='submit-".$input_name."' class='btn $prepend_size $prepend_class'>$prepend_value</button></span>" : '';
	$html .= "<input type='$type' class='form-control textbox' ".($width ? "style='width:$width;'" : '')." ".($length ? "maxlength='".$length."'" : '')." name='$input_name' id='".$input_id."' value='$input_value' placeholder='".$placeholder."' ".($autocomplete_off ? "autocomplete='off'" : '')." ".($deactivate ? "readonly" : "").">";
	$html .= ($append_button) ? "<span class='input-group-btn'><button type='$append_type' value='submit-".$input_name."' class='btn $append_size $append_class'>$append_value</button></span>" : '';
	$html .= ($icon) ? "<div class='form-control-feedback' style='top:0;'><i class='glyphicon $icon'></i></div>\n" : '';
	if ($append_button || $prepend_button) {
		$html .= "</div>\n";
	}
	$html .= "<div id='$input_id-help'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";

	$html .= "<input type='hidden' name='def[$input_name]' value='[type=$type_config],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' readonly />";
	if ($number) {
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