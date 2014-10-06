<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_datepicker.php
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
//credits: eternicode @ http://bootstrap-datepicker.readthedocs.org/en/latest/
//http://bootstrap-datepicker.readthedocs.org/en/release/options.html
function form_datepicker($title, $input_name, $input_id, $input_value, $array = FALSE) {
	if (!defined('DATEPICKER')) {
		define('DATEPICKER', TRUE);
		add_to_head("<link href='".DYNAMICS."assets/datepicker/css/datepicker3.css' rel='stylesheet' />");
		add_to_head("<script src='".DYNAMICS."assets/datepicker/js/bootstrap-datepicker.js'></script>");
	}
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if ($input_value && strstr($input_value, "-")) { // then this is date.
		$input_value = $input_value;
	} else {
		$input_value = ($input_value) ? date("d-m-Y", $input_value) : '';
	}
	if (!is_array($array)) {
		$placeholder = "";
		$date_format = "dd-mm-yyyy";
		$width = "250px";
		$required = 0;
		$safemode = 0;
		$deactivate = 0;
		$icon = '';
		$inline = 0;
		$error_text = '';
		$class = '';
	} else {
		$icon = (array_key_exists('icon', $array)) ? $array['icon'] : "";
		$placeholder = (array_key_exists("placeholder", $array)) ? $array['placeholder'] : "";
		$width = (array_key_exists("width", $array)) ? $array['width'] : "250px";
		$date_format = (array_key_exists("date_format", $array)) ? $array['date_format'] : "dd-mm-yyyy";
		$class = (array_key_exists('class', $array)) ? $array['class'] : "";
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$deactivate = (array_key_exists('deactivate', $array) && ($array['deactivate'] == 1)) ? 1 : 0;
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
	}
	$html = "<div id='$input_id-field' class='form-group m-b-10 $class ".($icon ? 'has-feedback' : '')."'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	$html .= "<div class='input-group date' ".($width ? "style='width:$width;'" : '').">\n";
	$html .= "<input type='text' name='".$input_name."' id='".$input_id."' value='".$input_value."' class='form-control textbox' placeholder='$placeholder' />\n";
	$html .= ($icon) ? "<div class='form-control-feedback'><i class='glyphicon $icon'></i></div>\n" : '';
	$html .= "<span class='input-group-addon'><i class='entypo calendar'></i></span>\n";
	$html .= "</div>\n";
	$html .= "<div id='$input_id-help'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";
	// Generate Defender Strings
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=date],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' readonly />";
	if ($deactivate !== 1) {
		add_to_jquery("
        $('#$input_id-field .input-group.date').datepicker({
        format: '".$date_format."',
        todayBtn: 'linked',
        autoclose: true,
        todayHighlight: true
        });
        ");
	}
	return $html;
}

?>