<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_gallery.php
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
function form_photosize($title, $input_name, $input_id, $input_value_width, $input_value_height, $array = FALSE) {
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if (!is_array($array)) {
		$inline = '';
		$placeholder = 'px';
		$deactivate = '';
		$required = 0;
		$error_text = '';
	} else {
		$inline = (array_key_exists('rowstart', $array)) ? 1 : 0;
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : 'px';
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$class = (array_key_exists('class', $array)) ? "class='".$array['class']."'" : "";
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$deactivate = '';
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
	}
	$html = "<div id='$input_id-field' class='form-group m-b-0 has-feedback'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-3 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-9 col-sm-9 col-md-9 col-lg-9'>\n" : "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0 form-horizontal'>\n";
	$html .= "<div class='form-group m-b-0 has-feedback col-sm-6 col-md-6 col-lg-6 m-r-10'>";
	$html .= "<input type='text' class='form-control input-sm $class' name='".$input_name."_w' id='".$input_id."' value='$input_value_width' placeholder='width (".$placeholder.")' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">\n";
	$html .= "<div class='form-control-feedback'><i class='glyphicon glyphicon-resize-horizontal'></i></div>\n";
	$html .= "<div id='$input_id-help' style='display:inline-block !important;'></div>";
	$html .= "</div>\n";
	$html .= "<div class='form-group m-b-0 has-feedback col-sm-6 col-md-6 col-lg-6'>";
	$html .= "<input type='text' class='form-control input-sm $class' name='".$input_name."_h' id='".$input_id."' value='$input_value_height' placeholder='height (".$placeholder.")' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">\n";
	$html .= "<div class='form-control-feedback'><i class='glyphicon glyphicon-resize-vertical'></i></div>\n";
	$html .= "<div id='$input_id-help' style='display:inline-block !important;'></div>";
	$html .= "</div>\n";
	$html .= "</div></div>\n";
	$html .= "<input type='hidden' name='def['".$input_name."_w']' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=0]".($error_text ? ",[error_text=$error_text]" : '')."' />";
	$html .= "<input type='hidden' name='def['".$input_name."_h']' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=0]".($error_text ? ",[error_text=$error_text]" : '')."' />";
	return $html;
}

