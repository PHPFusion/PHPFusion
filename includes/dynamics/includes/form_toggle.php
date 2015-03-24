<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_toggle.php
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
/* http://zamanak.ir/themes/zamanak/bootstrap-switch-3.0/ */
function form_toggle($title, $input_name, $input_id, $opts, $input_value, array $options = array()) {
	global $defender;
	if (!defined("TOGGLE")) {
		define("TOGGLE", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/switch/js/bootstrap-switch.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/switch/css/bootstrap-switch.min.css' rel='stylesheet' />");
	}
	$html = '';
	$title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$options += array(
		'class' => !empty($options['class']) ? $options['class'] : '',
		'inline' => !empty($options['inline']) && $options['inline'] == 1 ? 1 : 0,
		'keyflip' => !empty($options['keyflip']) && $options['keyflip'] == 1 ? 1 : 0,
		'error_text' => !empty($options['error_text']) ? $options['error_text'] : '',
		'required' => !empty($options['required']) ? $options['required'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? 1 : 0,
		'value' => !empty($options['value']) && $options['value'] ? $options['value'] : 1
	);
	$checkbox_type = isnum($options['value']) ? 'number' : 'textbox';

	$html .= "<div id='$input_id-field' class='form-group clearfix ".$options['class']."'>\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "<br/>\n";
	$on_label = $opts['1'];
	$off_label = $opts['0'];
	if ($options['keyflip']) {
		$on_label = $opts['0'];
		$off_label = $opts['1'];
	}
	$html .= "<input id='$input_id' name='$input_name' value='".$options['value']."' type='checkbox' data-on-text='$on_label' data-off-text='$off_label' ".($options['deactivate'] ? 'readonly' : '')." ".($input_value == '1' ? 'checked' : '')." />\n";
	$defender->add_field_session(array(
		 'input_name' 	=> 	$input_name,
		 'type'			=>	$checkbox_type,
		 'title'		=>	$title2,
		 'id' 			=>	$input_id,
		 'required'		=>	$options['required'],
		 'safemode' 	=> 	0,
		 'error_text'	=> 	$options['error_text']
	 ));
	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";
	$html .= $options['inline'] ? "</div>\n" : "";
	$html .= "</div>\n";

	/* For fancy customization, redeclare on script end*/
	add_to_jquery("
	$('#".$input_id."').bootstrapSwitch();
	");
	return $html;
}

function form_checkbox($title, $input_name, $input_id, $input_value, array $options = array()) {
	global $defender;
	$title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$options += array(
		'class' => !empty($options['class']) ? $options['class'] : '',
		'error_text' => !empty($options['error_text']) ? $options['error_text'] : '',
		'required' => !empty($options['required']) ? $options['required'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? 1 : 0,
		'value' => !empty($options['value']) && $options['value'] ? $options['value'] : 1,
		'tip' => !empty($options['tip']) ? "title='".$options['tip']."'" : '',
	);
	$checkbox_type = isnum($options['value']) ? 'number' : 'textbox';
	$html = "<div id='$input_id-field' class='form-group clearfix ".$options['class']."'>\n";
	$html .= "<label class='control-label col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0' for='$input_id'>\n";
	$html .= "<input id='$input_id' name='$input_name' value='".$options['value']."' type='checkbox' ".($options['deactivate'] ? 'readonly' : '')." ".($input_value == '1' ? 'checked' : '')." />\n";
	$html .= "$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n";
	$defender->add_field_session(array(
		 'input_name' 	=> 	$input_name,
		 'type'			=>	$checkbox_type,
		 'title'		=>	$title2,
		 'id' 			=>	$input_id,
		 'required'		=>	$options['required'],
		 'safemode' 	=> 	0,
		 'error_text'	=> 	$options['error_text']
	 ));
	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";
	$html .= "</div>\n";
	return $html;
}

// still testing
function form_checklistbox($title = FALSE, $input_name, $input_id, array $option_array = array(), array $input_values = array(), array $options = array()) {
	global $locale, $defender;

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
		'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1'  : '0',
		'tip' => !empty($options['tip']) ? "title='".$options['tip']."'" : '',
		'two_cols' => !empty($options['two_cols']) && $options['two_cols'] == 1 ? '1' : '0',
		'value' => !empty($options['value']) && $options['value'] ? $options['value'] : 1,
	);

	$html = "<div id='$input_id-field' class='form-group clearfix m-b-10 ".$options['class']."' >\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title".($options['required'] ? "<span class='required'> *</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';

	$html .= "<div class='row'>\n";

	$checkbox_type = isnum($options['value']) ? 'number' : 'textbox';
//	$html = "<div id='$input_id-field' class='form-group clearfix ".$options['class']."'>\n";

	$colwidth = $options['two_cols'] ? 6 : 12;

	foreach($option_array as $key => $value) {

		$html .= "<label class='control-label col-xs-$colwidth col-sm-$colwidth col-md-$colwidth col-lg-$colwidth p-l-0' for='$input_id-$key'>\n";
		$html .= "<input id='$input_id-$key' name='".$input_name."[]' value='".$options['value']."' type='checkbox' ".($options['deactivate'] ? 'readonly' : '')." ".(in_array($key, $input_values) ? 'checked' : '')." />\n";
		$html .= $value." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n";
	}

//	$html .= "</div>\n";

	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";

	$html .= "</div>\n";
	$html .= ($options['inline']) ? "</div>\n" : "";	
	$html .= "</div>\n";

	$defender->add_field_session(array(
		 'input_name' 	=> 	$input_name,
		 'type'			=>	'checklistbox',
		 'title'		=>	$title2,
		 'id' 			=>	$input_id,
		 'required'		=>	$options['required'],
		 'safemode'		=> 	$options['safemode'],
		 'error_text'	=> 	$options['error_text']
	 ));

	return $html;
}
