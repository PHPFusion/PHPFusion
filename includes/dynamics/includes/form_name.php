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
function form_name($title = FALSE, $input_name, $input_id, $input_value = FALSE, array $options) {
	global $defender, $locale;

	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";

	$html = '';
	// NOTE (remember to parse readback value as of '|' seperator)
	if (isset($input_value) && (!empty($input_value))) {
		if (!is_array($input_value)) {
			$input_value = construct_array($input_value, "", "|");
		}
	} else {
		$input_value['0'] = "";
		$input_value['1'] = "";
		$input_value['2'] = "";
	}

	$options += array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'width' => !empty($options['width']) ?  $options['width']  : '100%',
		'class' => !empty($options['class']) ?  $options['class']  : '',
		'inline' => !empty($options['inline']) ?  $options['inline']  : '',
		'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1'  : '0',
	);

	$html .= "<div id='$input_id-field' class='form-group clearfix ".$options['class']."' >\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 ".($title ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 col-md-12 col-lg-12")." p-l-0'>\n" : "";
	$html .= "<div class='row p-l-15'>\n";
	$html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 m-b-10 p-l-0'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control textbox' id='".$input_id."-fName' value='".$input_value['0']."' placeholder='".$locale['first_name']." ".($options['required'] ? '*':'')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-fName-help'></div>";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control textbox' id='".$input_id."-lName' value='".$input_value['1']."' placeholder='".$locale['last_name']." ".($options['required'] ? '*':'')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-lName-help'></div>";
	$html .= "</div>\n";

	$html .= "</div>\n"; // close inner row
	$html .= ($options['inline']) ? "</div>\n" : "";
	$html .= "</div>\n";
	$defender->add_field_session(array(
									 'input_name' 	=> 	$input_name,
									 'type'			=>	'name',
									 'title'		=>	$title2,
									 'id' 			=>	$input_id,
									 'required'		=>	$options['required'],
									 'safemode'		=> 	$options['safemode'],
									 'error_text'	=> 	$options['error_text']
								 ));
	return $html;
}