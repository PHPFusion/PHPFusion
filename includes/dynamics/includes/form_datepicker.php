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
function form_datepicker($title, $input_name, $input_id, $input_value, array $options = array()) {
	global $defender;
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

	$options += array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'width' => !empty($options['width']) ?  $options['width']  : '250px',
		'class' => !empty($options['class']) ?  $options['class']  : '',
		'inline' => !empty($options['inline']) ?  $options['inline']  : '',
		'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1'  : '0',
		'icon' => !empty($options['icon']) ?  $options['icon']  : '',
		'date_format' => !empty($options['date_format']) ?  $options['date_format']  : 'dd-mm-yyyy',
		'fieldicon_off' => !empty($options['fieldicon']) && $options['fieldicon'] == 1 ?  1  : 0,
	);

	$html = "<div id='$input_id-field' class='form-group ".$options['class']." ".($options['icon'] ? 'has-feedback' : '')."'>\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	$html .= "<div class='input-group date' ".($options['width'] ? "style='width:".$options['width'].";'" : '').">\n";
	$html .= "<input type='text' name='".$input_name."' id='".$input_id."' value='".$input_value."' class='form-control textbox' placeholder='".$options['placeholder']."' />\n";
	$html .= $options['icon'] ? "<div class='form-control-feedback'><i class='glyphicon ".$options['icon']."'></i></div>\n" : '';
	$html .= "<span class='input-group-addon ".($options['fieldicon_off'] ? 'display_none' : '')."'><i class='entypo calendar'></i></span>\n";
	$html .= "</div>\n";
	$html .= "<div id='$input_id-help'></div>";
	$html .= $options['inline'] ? "</div>\n" : "";
	$html .= "</div>\n";
	// Generate Defender Strings
	//$html .= "<input type='hidden' name='def[$input_name]' value='[type=date],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' />";
	$defender->add_field_session(array(
			 'input_name' 	=> 	$input_name,
			 'type'			=>	'date',
			 'title'		=>	$title2,
			 'id' 			=>	$input_id,
			 'required'		=>	$options['required'],
			 'safemode' 	=> 	$options['safemode'],
			 'error_text'	=> 	$options['error_text']
		 ));
	if ($options['deactivate'] !== 1) {
		add_to_jquery("
        $('#$input_id-field .input-group.date').datepicker({
        format: '".$options['date_format']."',
        todayBtn: 'linked',
        autoclose: true,
        todayHighlight: true
        });
        ");
	}
	return $html;
}

?>