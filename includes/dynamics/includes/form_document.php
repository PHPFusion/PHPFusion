<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Address Input Based
| Filename: form_document.php
| Author: Frederick MC Chan (Hien)
| Co-Author: Joakim Falk (Domi)
| Co-Author: Chubatyj Vitalij (Rizado)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_document($title = FALSE, $input_name, $input_id, $input_value = FALSE, array $options = array()) {
	global $locale, $defender, $settings;
	if (!defined('DATEPICKER')) {
		define('DATEPICKER', TRUE);
		add_to_head("<link href='".DYNAMICS."assets/datepicker/css/datepicker3.css' rel='stylesheet' />");
		add_to_head("<script src='".DYNAMICS."assets/datepicker/js/bootstrap-datepicker.js'></script>");
	}
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	// NOTE (remember to parse readback value as of '|' seperator)
	if (isset($input_value) && (!empty($input_value))) {
		if (!is_array($input_value)) {
			$input_value = construct_array($input_value, "", "|");
			if ($input_value[4] != "0000-00-00") {
				$input_value[4] = date('d-m-Y', strtotime($input_value[4]));
			} else {
				$input_value[4] = "";
			}
			if ($input_value[5] != "0000-00-00") {
				$input_value[5] = date('d-m-Y', strtotime($input_value[5]));
			} else {
				$input_value[5] = "";
			}
		}
	} else {
		$input_value['0'] = "";
		$input_value['1'] = "";
		$input_value['2'] = "";
		$input_value['3'] = "";
		$input_value['4'] = "";
		$input_value['5'] = "";
	}

	$options += array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'width' => !empty($options['width']) ?  $options['width']  : '100%',
		'class' => !empty($options['class']) ?  $options['class']  : '',
		'inline' => !empty($options['inline']) ?  $options['inline']  : '',
		'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1'  : '0',
		'date_format' => !empty($options['date_format']) ?  $options['date_format']  : 'dd-mm-yyyy',
		'week_start' => !empty($options['week_start']) && isnum($options['week_start']) ? $options['week_start'] : isset($settings['week_start']) && isnum($settings['week_start']) ? $settings['week_start'] : 0
	);

	$html = "";
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 ".$options['class']."' >\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title".($options['required'] ? "<span class='required'> *</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';

	$html .= "<div class='row'>\n";
	$html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-doc_type' value='".$input_value['0']."' placeholder='".$locale['doc_type'].($options['required'] ? ' *':'')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-doc_type-help'></div>";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-4 col-sm-4 col-md-4 col-lg-3 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-doc_series' value='".$input_value['1']."' placeholder='".$locale['doc_series'].($options['required'] ? ' *':'')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />";
	$html .= "<div id='$input_id-doc_series-help'></div>";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-8 col-sm-8 col-md-8 col-lg-6 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-doc_number' value='".$input_value['2']."' placeholder='".$locale['doc_number'].($options['required'] ? ' *':'')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />";
	$html .= "<div id='$input_id-doc_number-help'></div>";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-doc_authority' value='".$input_value['3']."' placeholder='".$locale['doc_authority'].($options['required'] ? ' *':'')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-doc_authority-help'></div>";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-5'>\n";
	$html .= "<div class='input-group date' ".($options['width'] ? "style='width:".$options['width'].";'" : '').">\n";
	$html .= "<input type='text' name='".$input_name."[]' id='".$input_id."-doc_date_issue' value='".$input_value[4]."' class='form-control textbox' placeholder='".$locale['doc_date_issue'].($options['required'] ? ' *':'')."' />\n";
	$html .= "<span class='input-group-addon '><i class='entypo calendar'></i></span>\n";
	$html .= "<div id='$input_id-doc_date_issue-help'></div>";
	$html .= "</div>\n</div>\n";

	$html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-5'>\n";
	$html .= "<div class='input-group date' ".($options['width'] ? "style='width:".$options['width'].";'" : '').">\n";
	$html .= "<input type='text' name='".$input_name."[]' id='".$input_id."-doc_date_expire' value='".$input_value[5]."' class='form-control textbox' placeholder='".$locale['doc_date_expire']."' />\n";
	$html .= "<span class='input-group-addon '><i class='entypo calendar'></i></span>\n";
	$html .= "<div id='$input_id-doc_date_expire-help'></div>";
	$html .= "</div>\n</div>\n";

	$html .= "</div>\n"; // close inner row

	$html .= ($options['inline']) ? "</div>\n" : "";
	$html .= "</div>\n";
	$defender->add_field_session(array(
		 'input_name' 	=> 	$input_name,
		 'type'			=>	'document',
		 'title'		=>	$title2,
		 'id' 			=>	$input_id,
		 'required'		=>	$options['required'],
		 'safemode'		=> 	$options['safemode'],
		 'error_text'	=> 	$options['error_text']
	 ));

	if ($options['deactivate'] !== 1) {
		add_to_jquery("
        $('#$input_id-field .input-group.date').datepicker({
        format: '".$options['date_format']."',
        todayBtn: 'linked',
        autoclose: true,
		weekStart: ".$options['week_start'].",
        todayHighlight: true
        });
        ");
	}
	return $html;
}

?>