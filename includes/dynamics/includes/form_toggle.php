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
function form_toggle($title, $input_name, $input_id, $opts, $input_value, $array = FALSE) {
	if (!defined("TOGGLE")) {
		define("TOGGLE", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/switch/js/bootstrap-switch.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/switch/css/bootstrap-switch.min.css' rel='stylesheet' />");
	}
	$html = '';
	$title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
	if (!is_array($array)) {
		$class = '';
		$error_text = '';
		$required = 0;
		$inline = 0;
		$keyflip = 0;
		$deactivate = 0;
		$value = '1';
	} else {
		$class = (array_key_exists("class", $array)) ? $array['class'] : "";
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
		$keyflip = (array_key_exists('keyflip', $array)) ? $array['keyflip'] : 0;
		$value = (array_key_exists('value', $array)) ? $array['value'] : '1';
		$deactivate = (array_key_exists("deactivate", $array) && ($array['deactivate'] == "1")) ? 1 : 0;
	}

	$html .= "<div id='$input_id-field' class='form-group clearfix $class'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "<br/>\n";
	$on_label = $opts['1'];
	$off_label = $opts['0'];
	if ($keyflip) {
		$on_label = $opts['0'];
		$off_label = $opts['1'];
	}
	$html .= "<input id='$input_id' name='$input_name' value='$value' type='checkbox' data-on-text='$on_label' data-off-text='$off_label' ".($deactivate ? 'readonly' : '')." ".($input_value == '1' ? 'checked' : '')." />\n"; ///>\n";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=checkbox],[title=$title2],[id=$input_id],[required=$required]".($error_text ? ",[error_text=$error_text]" : '')."' />";
	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";

	/* For fancy customization, redeclare on script end*/
	add_to_jquery("
	$('#".$input_id."').bootstrapSwitch();
	");
	return $html;
}

function form_checkbox($title, $input_name, $input_id, $input_value, array $options = array()) {
	$title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$options += array(
		'class' => !empty($options['class']) ? $options['class'] : '',
		'error_text' => !empty($options['error_text']) ? $options['error_text'] : '',
		'required' => !empty($options['required']) ? $options['required'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? 1 : 0,
		'value' => !empty($options['value']) && $options['value'] ? $options['value'] : 1
	);

	$html = "<div id='$input_id-field' class='form-group clearfix ".$options['class']."'>\n";
	$html .= "<label class='control-label col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0' for='$input_id'>\n";
	$html .= "<input id='$input_id' name='$input_name' value='".$options['value']."' type='checkbox' ".($options['deactivate'] ? 'readonly' : '')." ".($input_value == '1' ? 'checked' : '')." />\n";
	$html .= "$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>\n";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=checkbox],[title=$title2],[id=$input_id],[required=".$options['required']."]".($options['error_text'] ? ",[error_text=".$options['error_text']."" : '')."'/>";
	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";
	$html .= "</div>\n";
	return $html;

}

?>