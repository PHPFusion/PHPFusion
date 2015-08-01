<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_datepicker.php
| Author: Frederick MC Chan (Hien)
| Credits:  eternicode @ http://bootstrap-datepicker.readthedocs.org/en/latest/
| Docs: http://bootstrap-datepicker.readthedocs.org/en/release/options.html
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/**
 * Input to save date using datepicker
 * @param        $input_name
 * @param string $label
 * @param string $input_value
 * @param array  $options
 *                <ul>
 *                <li><strong>class</strong> (string): Empty string by default.
 *                The value of attribute class of the input.</li>
 *                <li><strong>date_format</strong> (string): dd-mm-yyyy by default.
 *                Date format for datepicker plugin.</li>
 *                <li><strong>deactivate</strong> (boolean): FALSE by default.
 *                You can pass TRUE and turn off the javascript datepicker plugin</li>
 *                <li><strong>error_text</strong> (string): empty string by default.
 *                An error message</li>
 *                <li><strong>fieldicon_off</strong> (boolean): FALSE by default.
 *                If TRUE, the calendar icon will be not displayed in the input.</li>
 *                <li><strong>inline</strong> (boolean): FALSE by default.
 *                TRUE if the input should be an inline element.</li>
 *                <li><strong>input_id</strong> (string): $input name by default.
 *                The value of attribute id of input.</li>
 *                <li><strong>required</strong> (boolean): FALSE by default</li>
 *                <li><strong>type</strong> (string): timestamp by default.
 *                Valid types:
 *                <ul>
 *                <li>date: The date will be saved as mysql date.</li>
 *                <li>timestamp: A timestamp will be saved as an integer</li>
 *                </ul>
 *                </li>
 *                <li><strong>week_start</strong> (int): 0 by default.
 *                An integer between 0 and 6. It is the same as
 *                the attribute weekStart of datepicker.</li>
 *                <li><strong>width</strong> (string): 250px by default.
 *                A valid value for CSS width</li>
 *                </ul>
 * @return string
 */
function form_datepicker($input_name, $label = '', $input_value = '', array $options = array()) {
	global $defender, $locale;
	if (!defined('DATEPICKER')) {
		define('DATEPICKER', TRUE);
		add_to_head("<link href='".DYNAMICS."assets/datepicker/css/datepicker3.css' rel='stylesheet' />");
		add_to_head("<script src='".DYNAMICS."assets/datepicker/js/bootstrap-datepicker.js'></script>");
		add_to_head("<script src='".DYNAMICS."assets/datepicker/js/locales/bootstrap-datepicker.".$locale['datepicker'].".js'></script>");
	}
	$title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$label = stripinput($label);
	$input_name = stripinput($input_name);
	if ($input_value && !strstr($input_value, "-")) { // must be -
		$input_value = date("d-m-Y", $input_value);
	}
	$default_options = array('input_id' => $input_name,
		'required' => FALSE,
		'placeholder' => '',
		'deactivate' => FALSE,
		'width' => '250px',
		'class' => '',
		'inline' => FALSE,
		'error_text' => $locale['error_input_default'],
		'date_format' => 'dd-mm-yyyy',
		'fieldicon_off' => FALSE,
		'type' => 'timestamp',
		'week_start' => fusion_get_settings('week_start'));
	$options += $default_options;
	if (!$options['width']) {
		$options['width'] = $default_options['width'];
	}
	if (!in_array($options['type'], array('date', 'timestamp'))) {
		$options['type'] = $default_options['type'];
	}
	$options['week_start'] = (int)$options['week_start'];
	$error_class = $defender->inputHasError($input_name) ? "has-error " : "";
	$input_id = $options['input_id'] ? : $default_options['input_id'];
	$html = "<div id='$input_id-field' class='form-group ".$error_class.$options['class']."'>\n";
	$html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$label ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	$html .= "<div class='input-group date' ".($options['width'] ? "style='width:".$options['width'].";'" : '').">\n";
	$html .= "<input type='text' name='".$input_name."' id='".$input_id."' value='".$input_value."' class='form-control textbox' placeholder='".$options['placeholder']."' />\n";
	$html .= "<span class='input-group-addon ".($options['fieldicon_off'] ? 'display-none' : '')."'><i class='entypo calendar'></i></span>\n";
	$html .= "</div>\n";
	$html .= ($options['required'] == 1 && $defender->inputHasError($input_name)) || $defender->inputHasError($input_name) ? "<div id='".$input_id."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
	$html .= $options['inline'] ? "</div>\n" : "";
	$html .= "</div>\n";
	// Generate Defender Strings
	//$html .= "<input type='hidden' name='def[$input_name]' value='[type=date],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' />";
	$defender->add_field_session(array('input_name' => $input_name,
									 'type' => $options['type'],
									 'title' => $title,
									 'id' => $input_id,
									 'required' => $options['required'],
									 'safemode' => TRUE,
									 'error_text' => $options['error_text']));
	if (!$options['deactivate']) {
		add_to_jquery("
        $('#$input_id-field .input-group.date').datepicker({
        format: '".$options['date_format']."',
        todayBtn: 'linked',
        autoclose: true,
		weekStart: ".$options['week_start'].",
		language: '".$locale['datepicker']."',
        todayHighlight: true
        });
        ");
	}
	return $html;
}
