<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 8
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Textarea Input Based & Editor
| Filename: form_textarea.php
| Author: Frederick MC Chan (Hien)
| Version : 8.5.3 (Please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_textarea($title = FALSE, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	global $userdata; // for editor
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if (!is_array($array)) {
		$required = 0;
		$safemode = 0;
		$deactivate = "";
		$width = "100%";
		$height = "80px";
		$editor = 0;
		$placeholder = "";
		$inline = '';
		$form_name = 'input_form';
		$bbcode = 0;
		$error_text = '';
		$class = '';
	} else {
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$bbcode = (array_key_exists('bbcode', $array) && $array['bbcode'] == 1) ? 1 : 0;
		$width = (array_key_exists('width', $array)) ? $array['width'] : "98%";
		$height = (array_key_exists('height', $array)) ? $array['height'] : "80";
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
		$form_name = (array_key_exists('form_name', $array)) ? $array['form_name'] : 'input_form';
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
		$class = (array_key_exists("class", $array) && $array['class']) ? $array['class'] : '';
	}
	$input_value = phpentities(stripslashes($input_value));
	$input_value = str_replace("<br />", "", $input_value);
	if ($bbcode) {
		require_once INCLUDES."bbcode_include.php";
	}
	$html = "";
	$html .= "<div id='$input_id-field' class='form-group m-b-0 ".$class."'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	$html .= ($bbcode) ? "".display_bbcodes('90%', $input_name, $form_name)."" : '';
	$html .= "<textarea name='$input_name' style='width:100%; min-height:100px;' class='form-control textbox' placeholder='$placeholder' id='$input_id' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">$input_value</textarea>\n";
	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=textarea],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' readonly />";
	return $html;
}

?>