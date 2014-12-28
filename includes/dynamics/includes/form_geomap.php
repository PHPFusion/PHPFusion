<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 8
| Copyright (C) 2002 - 2013 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Address Input Based
| Filename: form_geomap.php
| Author: Frederick MC Chan (Hien)
| Sub-Author: Joakim Falke
| Communities of PHP-Fusion at PHP-Fusion.co.uk
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_address($title = FALSE, $input_name, $input_id, $input_value = FALSE, array $options = array()) {
	global $locale, $defender;
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
	}
	$countries = array();
	require INCLUDES."geomap/geomap.inc.php";
	// NOTE (remember to parse readback value as of '|' seperator)
	if (isset($input_value) && (!empty($input_value))) {
		if (!is_array($input_value)) {
			$input_value = construct_array($input_value, "", "|");
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
		'flag' => !empty($options['flag']) ?  $options['flag']  : '',
	);

	$html = "";
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 ".$options['class']."' >\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
	$html .= "<div class='row'>\n";
	$html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control input-sm' id='".$input_id."-street' value='".$input_value['0']."' placeholder='".$locale['street1']." ".($options['required'] ? '*':'')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-street-help'></div>";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control input-sm' id='".$input_id."-street2' value='".$input_value['1']."' placeholder='".$locale['street2']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5 m-b-10'>\n";
	$html .= "<select name='".$input_name."[]' id='$input_id-country' style='width:100%;'/>\n";
	$html .= "<option value=''></option>";
	foreach ($countries as $arv => $countryname) { // outputs: key, value, class - in order
		$country_key = str_replace(" ", "-", $countryname);
		$select = ($input_value[2] == $country_key) ? "selected" : '';
		$html .= "<option value='$country_key' ".$select.">$countryname</option>";
	}
	$html .= "</select>\n";
	$html .= "<div id='$input_id-country-help'></div>";
	$html .= "</div>\n";
	$html .= "<div class='col-xs-12 col-sm-7 col-md-7 col-lg-7 m-b-10'>\n";
	$html .= "<div id='state-spinner' style='display:none;'>\n<img src='".IMAGES."loader.gif'>\n</div>\n";
	$html .= "<input type='hidden' name='".$input_name."[]' id='$input_id-state' value='".$input_value['3']."' style='width:100%;' />\n";
	$html .= "<div id='$input_id-state-help'></div>";
	$html .= "</div>\n";
	$html .= "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' id='".$input_id."-city' class='form-control input-sm' value='".$input_value['4']."' placeholder='".$locale['city']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-city-help'></div>";
	$html .= "</div>\n";
	$html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]'  id='".$input_id."-postcode' class='form-control input-sm' value='".$input_value['5']."' placeholder='".$locale['postcode']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-postcode-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n"; // close inner row
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";
	// Defender Strings
	//$html .= "<input type='hidden' name='def[$input_name]' value='[type=address],[title=$input_name],[id=".$input_id."],[required=$required],[safemode=$safemode]' />\n";
	$defender->add_field_session(array(
		 'input_name' 	=> 	$input_name,
		 'type'			=>	'address',
		 'title'		=>	$title2,
		 'id' 			=>	$input_id,
		 'required'		=>	$options['required'],
		 'safemode'		=> 	$options['safemode'],
		 'error_text'	=> 	$options['error_text']
	 ));

	$flag_function = ''; $flag_plugin = '';
	if ($options['flag']) {
		$flag_function = "
		function show_flag(item) {
		if(!item.id) {return item.text;}
		var icon = '".IMAGES."small_flag/flag_'+ item.id.replace(/-/gi,'_').toLowerCase() +'.png';
		return '<img style=\"float:left; margin-right:5px; margin-top:3px;\" src=\"' + icon + '\"/></i>' + item.text;
		}";
		$flag_plugin = "
         formatResult: show_flag,
		 formatSelection: show_flag,
		 escapeMarkup: function(m) { return m; },
		";
	}

	add_to_jquery("
	".$flag_function."
    $('#$input_id-country').select2({
	$flag_plugin
	placeholder: 'Country ".($options['required'] == 1 ? '*':'')."'
    });
    $('#".$input_id."-country').bind('change', function(){
    	var ce_id = $(this).val();
        $.ajax({
        url: '".INCLUDES."geomap/form_geomap.json.php',
        type: 'GET',
        data: { id : ce_id },
        dataType: 'json',
        beforeSend: function(e) {
        //$('#state-spinner').show();
        $('#".$input_id."-state').hide();
        },
        success: function(data) {
        //$('#state-spinner').hide();
        $('#".$input_id."-state').select2({
        placeholder: 'Select State ".($options['required'] == 1 ? '*':'')."',
        allowClear: true,
        data : data
        });
        },
        error : function() {
		$.pnotify({title: 'Error! Something went wrong.',
		text: 'We cannot read the database, please recheck source codes.',
		icon: 'pngicon-l-badge-multiply',
		width: 'auto'
		});
        }
        })
	}).trigger('change');
	");
	return $html;
}

?>