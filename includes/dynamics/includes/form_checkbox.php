<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_checkbox.php
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

function form_checkbox($input_name, $label = '', $input_value = '0', array $options = array()) {
	global $defender, $locale;

	$locale['error_input_checkbox'] = 'Please tick this checkbox'; // to be moved

	$options += array(
		'input_id'			=> !empty($options['input_id']) ? $options['input_id'] : $input_name,
		'class'				=> !empty($options['class']) ? $options['class'] : '',
		
		'toggle'			=> !empty($options['toggle']) && $options['toggle'] == 1 ? 1 : 0,
		'toggle_text'		=> !empty($options['toggle_text']) && (!empty($options['toggle_text'][0]) && !empty($options['toggle_text'][1])) ? $options['toggle_text'] : array($locale['no'], $locale['yes']),
		'keyflip'			=> !empty($options['keyflip']) && $options['keyflip'] == 1 ? 1 : 0,
		
		'error_text'		=> !empty($options['error_text']) ? $options['error_text'] : $locale['error_input_checkbox'],
		'inline'			=> !empty($options['inline']) && $options['inline'] == 1 ? 1 : 0,
		'required'			=> !empty($options['required']) && $options['required'] == 1 ? 1 : 0,
		'disabled'			=> !empty($options['disabled']) && $options['disabled'] == 1 ? 1 : 0,
		'value'				=> !empty($options['value']) && $options['value'] ? $options['value'] : 1,
		'tip'				=> !empty($options['tip']) ? "title='".$options['tip']."'" : '',
		// If this checkbox would be marked as a child of another checkbox
		// it would get disabled when the main checkbox is unchecked. Not completed yet.
		'child_of'		=> !empty($options['child_of']) ? $options['child_of'] : '',
	);

	if ($options['toggle'] && !defined("BOOTSTRAP_SWITCH_ASSETS")) {
		define("BOOTSTRAP_SWITCH_ASSETS", TRUE);
		// http://www.bootstrap-switch.org
		add_to_head("<link href='".DYNAMICS."assets/switch/css/bootstrap-switch.min.css' rel='stylesheet' />");
		add_to_footer("<script src='".DYNAMICS."assets/switch/js/bootstrap-switch.min.js'></script>");

		// Target by class and type, not IDs. We don't want repetitive code
		add_to_jquery("$('.is-bootstrap-switch input[type=checkbox]').bootstrapSwitch();");
	}
	$title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$error_class = $defender->inputHasError($input_name) ? "has-error" : "";
	$switch_class = $options['toggle'] ? "is-bootstrap-switch" : "";

	$on_label = $options['toggle_text'][1];
	$off_label = $options['toggle_text'][0];
	if ($options['keyflip']) {
		$on_label = $options['toggle_text'][0];
		$off_label = $options['toggle_text'][1];
	}

	$html = "<div id='".$options['input_id']."-field' class='$switch_class $error_class form-group clearfix ".$options['class']."'>\n";
	$html .= "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='".$options['input_id']."'>\n";
	$html .= "$label ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n";
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "\n";
	$html .= "<input id='".$options['input_id']."' ".($options['toggle'] ? "data-on-text='".$on_label."' data-off-text='".$off_label."'" : "")." style='margin: 0;vertical-align: middle' ".($options['child_of'] ? "data-child-of='".$options['child_of']."'" : "")." name='$input_name' value='".$options['value']."' type='checkbox' ".($options['disabled'] ? 'disabled' : '')." ".($input_value == '1' ? 'checked' : '')." />\n";
	$html .= (($options['required'] == 1 && $defender->inputHasError($input_name)) || $defender->inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
	$html .= $options['inline'] ? "</div>\n" : "";
	$html .= "</div>\n";

	$defender->add_field_session(array(
		'input_name'	=> $input_name,
		'title'			=> $title,
		'id'			=> $options['input_id'],
		'type'			=> 'checkbox',
		'child_of'		=> $options['child_of'],
		'required'		=> $options['required'],
		//'subtype'		=> (isnum($input_value) ? 'number' : 'text'),
		//'safemode'	=> 0,
	));

	// Experimental stuff
	if (!empty($options['child_of']) && !defined($input_name.'_JS')) {
	define($input_name.'_JS', TRUE);
		add_to_jquery("
		$('#".$options['child_of']."').each(function() {
			if (this.checked) {
				$('input[data-child-of=".$options['child_of']."]').removeAttr('disabled');
			} else {
				$('input[data-child-of=".$options['child_of']."]').attr('disabled', 'disabled');
			}
		});

		$('#".$options['child_of']."').change(function(){
			if (this.checked) {
				$('input[data-child-of=".$options['child_of']."]').removeAttr('disabled');
			} else {
				$('input[data-child-of=".$options['child_of']."]').attr('disabled', 'disabled');
			}
		});
	
		");
	}

	return $html;
}
