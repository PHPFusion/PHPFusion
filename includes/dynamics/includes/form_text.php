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
function form_text($title = FALSE, $input_name, $input_id, $input_value = FALSE, array $options = array()) {
	$html = '';
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";

	$options += array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'width' => !empty($options['width']) ?  $options['width']  : '100%',
		'class' => !empty($options['class']) ?  $options['class']  : '',
		'inline' => !empty($options['inline']) ?  $options['inline']  : '',
		'length' => !empty($options['length']) ?  $options['length']  : '200',
		'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1'  : '0',
		'icon' => !empty($options['icon']) ?  $options['icon']  : '',
		'append_button' => !empty($options['append_button']) ?  $options['append_button']  : '',
		'append_value' => !empty($options['append_value']) ?  $options['append_value']  : '<i class="entypo search"></i>',
		'append_size' => !empty($options['append_size']) ?  $options['append_size']  : '',
		'append_class' => !empty($options['append_class']) ?  $options['append_class']  : 'btn-default',
		'append_type' => !empty($options['append_type']) ?  $options['append_type']  : 'submit',
		'prepend_button' => !empty($options['prepend_button']) ?  $options['prepend_button']  : '',
		'prepend_value' => !empty($options['prepend_value']) ?  $options['prepend_value'] : '<i class="entypo search"></i>',
		'prepend_size' => !empty($options['prepend_size']) ?  $options['prepend_size']  : '',
		'prepend_class' => !empty($options['prepend_class']) ?  $options['prepend_class']  : 'btn-default',
		'prepend_type' => !empty($options['prepend_type']) ?  $options['prepend_type'] : 'submit',
		'autocomplete_off' => !empty($options['autocomplete_off']) && $options['autocomplete_off'] == 1 ?  '1' : '0',
		'password' => !empty($options['password']) && $options['password'] ==1 ?  '1'  : '0',
		'email' => !empty($options['email']) && $options['email'] ==1 ?  '1' : '0',
		'number' => !empty($options['number']) && $options['number'] ==1 ? '1' : '0',
		'url' => !empty($options['url']) && $options['url'] ==1 ? '1' : '0',
		'type' => !empty($options['password']) && $options['password'] == 1 ? 'password' : 'text',
	);

	$type_config = 'textbox';
	if ($options['password'] == 1) {
		$type_config= "password";
	} elseif ($options['email'] == 1) {
		$type_config = "email";
	} elseif ($options['number'] == 1) {
		$type_config = "number";
	} elseif ($options['url'] == 1) {
		$type_config = "url";
	}

	$html .= "<div id='$input_id-field' class='form-group ".$options['class']." ".($options['icon'] ? 'has-feedback' : '')."'>\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	$html .= ($options['append_button'] || $options['prepend_button']) ? "<div class='input-group'>\n" : '';
	$html .= ($options['prepend_button']) ? "<span class='input-group-btn'>\n<button type='".$options['prepend_type']."' value='submit-".$input_name."' class='btn ".$options['prepend_size']." ".$options['prepend_class']."'>".$options['prepend_value']."</button></span>" : '';
	$html .= "<input type='".$options['type']."' class='form-control textbox' ".($options['width'] ? "style='width:".$options['width'].";'" : '')." ".($options['length'] ? "maxlength='".$options['length']."'" : '')." name='$input_name' id='".$input_id."' value='$input_value' placeholder='".$options['placeholder']."' ".($options['autocomplete_off'] ? "autocomplete='off'" : '')." ".($options['deactivate'] ? 'readonly' : '').">";
	$html .= ($options['append_button']) ? "<span class='input-group-btn'><button type='".$options['append_type']."' value='submit-".$input_name."' class='btn ".$options['append_size']." ".$options['append_class']."'>".$options['append_value']."</button></span>" : '';
	$html .= ($options['icon']) ? "<div class='form-control-feedback' style='top:0;'><i class='glyphicon ".$options['icon']."'></i></div>\n" : '';
	$html .= ($options['append_button'] || $options['prepend_button']) ? "</div>\n" : '';
	$html .= "<div id='$input_id-help'></div>";
	$html .= ($options['inline']) ? "</div>\n" : '';
	$html .= "</div>\n";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=$type_config],[title=$title2],[id=$input_id],[required=".$options['required']."],[safemode=".$options['safemode']."]".($options['error_text'] ? ",[error_text=".$options['error_text']."]" : '')."' />";
	if ($options['number']) {
		add_to_jquery("
		$('#".$input_id."').keypress(function(e) {
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