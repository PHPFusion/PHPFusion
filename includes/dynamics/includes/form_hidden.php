<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_hidden.php
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
function form_hidden($title, $input_name, $input_id, $input_value, array $options = array()) {
	global $defender;
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

	$options += array(
		'show_title' => !empty($options['title']) && $options['title'] == 1 ? '1' : '0',
		'width' => !empty($options['width']) ?  $options['width']  : '100%',
		'class' => !empty($options['class']) ?  $options['class']  : '',
		'inline' => !empty($options['inline']) ?  $options['inline']  : '',
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
	);


	$html = '';
	if ($options['show_title']) {
		$html .= "<div id='$input_id-field' class='form-group m-b-0 ".$options['class']." '>\n";
		$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
		$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
	}
	$html .= "<input type='hidden' name='$input_name' id='$input_id' value='$input_value' ".($options['width'] ? "style='width:".$options['width']."'" : '')." ".($options['show_title'] ? "" : "readonly")." />\n";

	if ($options['show_title']) {
		$html .= "<div id='$input_id-help'></div>";
		$html .= ($options['inline']) ? "</div>\n" : "";
		$html .= "</div>\n";
	}
	//$html .= "<input type='hidden' name='def[$input_name]' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=0]' />";
	$defender->add_field_session(array(
		 	'input_name' 	=> 	$input_name,
		 	'type'			=>	'textbox',
	 		'title'			=>	$title2,
		 	'id' 			=>	$input_id,
		 	'required'		=>	$options['required'],
		 	'safemode' 		=> 	'0',
		 	'error_text'	=> 	$options['error_text']
	 ));

	return $html;
}

