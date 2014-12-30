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
function form_colorpicker($title = FALSE, $input_name, $input_id, $input_value = FALSE, array $options = array()) {
	global $defender;
	if (!defined("COLORPICKER")) {
		define("COLORPICKER", TRUE);
		add_to_head("<link href='".DYNAMICS."assets/colorpick/css/bootstrap-colorpicker.css' rel='stylesheet' media='screen' />");
		add_to_head("<script src='".DYNAMICS."assets/colorpick/js/bootstrap-colorpicker.js'></script>");
	}
	$title = (isset($title) && (!empty($title))) ? stripinput($title) : "";
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	$input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";
	$options += array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'width' => !empty($options['width']) ?  $options['width']  : '250px',
		'class' => !empty($options['class']) ?  $options['class']  : '',
		'inline' => !empty($options['inline']) ?  $options['inline']  : '',
		'max_length' => !empty($options['max_length']) ?  $options['max_length']  : '200',
		'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1'  : '0',
		'icon' => !empty($options['icon']) ?  $options['icon']  : '',
		'format' => !empty($options['format']) ?  $options['format']  : 'hex', //options = the color format - hex | rgb | rgba.
	);

	$html = "";
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 ".$options['class']." '>\n";
	$html .= $title ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "<br/>\n";
	$html .= "<div id='$input_id' style='width: ".$options['width']."' class='input-group colorpicker-component bscp colorpicker-element m-b-10' data-color='$input_value' data-color-format='".$options['format']."'>";
	$html .= "<input type='text' name='$input_name' class='form-control ".$options['class']."' id='".$input_id."' value='$input_value' data-color-format='".$options['format']."' placeholder='".$options['placeholder']."' ".($options['deactivate'] ? "readonly" : "").">";
	$html .= "<span id='$input_id-cp' class='input-group-addon'>";
	$html .= "<i style='background: rgba(255,255,255,1);'></i>";
	$html .= "</span></div>";
	$html .= $options['inline'] ? "</div>\n" : "";
	$html .= "</div>\n";
	//$html .= "<input type='hidden' name='def[$input_name]' value='[type=color],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' />";
	$defender->add_field_session(array(
		'input_name' 	=> 	$input_name,
		'type'			=>	'color',
	 	'title'			=>	$title2,
		'id' 			=>	$input_id,
		'required'		=>	$options['required'],
		'safemode' 		=> 	$options['safemode'],
		'error_text'	=> 	$options['error_text']
	 ));
	add_to_jquery("$('#$input_id').colorpicker({ format : '".$options['format']."'  });");
	return $html;
}

?>