<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_colorpicker.php
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
/*
Courtesy of : Mjolnic @ http://mjolnic.github.io/bootstrap-colorpicker/
*/
function form_colorpicker($title = FALSE, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	if (!defined("COLORPICKER")) {
		define("COLORPICKER", TRUE);
		add_to_head("<link href='".DYNAMICS."assets/colorpick/css/bootstrap-colorpicker.css' rel='stylesheet' media='screen' />");
		add_to_head("<script src='".DYNAMICS."assets/colorpick/js/bootstrap-colorpicker.js'></script>");
	}
	global $_POST;
	$title = (isset($title) && (!empty($title))) ? stripinput($title) : "";
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	$input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";
	if (!is_array($array)) {
		$array = array();
		$state_validation = "";
		$placeholder = "";
		$width = "250px";
		$class = "";
		$deactivate = "";
		$format = "";
		$helper_text = "";
		$required = 0;
		$safemode = 0;
		$inline = 0;
	} else {
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$class = (array_key_exists('class', $array)) ? $array['class'] : "";
		$width = (array_key_exists('width', $array)) ? $array['width'] : "250px";
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
		$format = (array_key_exists("format", $array)) ? $array['format'] : "rgba"; // options = the color format - hex | rgb | rgba.
		$helper_text = (array_key_exists("helper", $array)) ? $array['helper'] : "";
	}
	$html = "";
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 $class'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "<br/>\n";
	$html .= "<div id='$input_id' style='width: ".$width."' class='input-group colorpicker-component bscp colorpicker-element m-b-10' data-color='$input_value' data-color-format='$format'>";
	$html .= "<input type='text' name='$input_name' class='form-control $class' id='".$input_id."' value='$input_value' data-color-format='$format' placeholder='".$placeholder."' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=color],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly>";
	$html .= "<span id='$input_id-cp' class='input-group-addon'>";
	$html .= "<i style='background: rgba(255,255,255,1);'></i>";
	$html .= "</span></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";

	add_to_jquery("
    $('#$input_id').colorpicker(
    {
    format : '$format'
    });
    ");
	return $html;
}

?>