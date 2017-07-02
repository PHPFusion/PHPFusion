<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Geo Input Based
| Filename: form_geomap.php
| Author: Frederick MC Chan (Chan)
| Co-Author: Joakim Falk (Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_geo($input_name, $label = '', $input_value = FALSE, array $options = array()) {
    $locale = fusion_get_locale();
    $title = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $countries = array();
    require(INCLUDES.'geomap/geomap.inc.php');
    // NOTE (remember to parse readback value as of '|' seperator)
    if (isset($input_value) && (!empty($input_value))) {
        if (!is_array($input_value)) {
            $input_value = construct_array($input_value, "", "|");
        }
    } else {
        $input_value = [];
        $input_value['0'] = "";
        $input_value['1'] = "";
        $input_value['2'] = "";
        $input_value['3'] = "";
        $input_value['4'] = "";
        $input_value['5'] = "";
    }

    $options += array(
        'input_id'     => $input_name,
        'required'     => FALSE,
        'placeholder'  => '',
        'deactivate'   => FALSE,
        'width'        => '100%',
        'class'        => '',
        'inline'       => '',
        'tip'          => '',
        'error_text'   => !empty($options['error_text']) ? $options['error_text'] : $locale['street_error'],
        'error_text_2' => !empty($options['error_text_2']) ? $options['error_text_2'] : $locale['street_error'],
        'error_text_3' => !empty($options['error_text_3']) ? $options['error_text_3'] : $locale['country_error'],
        'error_text_4' => !empty($options['error_text_4']) ? $options['error_text_4'] : $locale['state_error'],
        'error_text_5' => !empty($options['error_text_5']) ? $options['error_text_5'] : $locale['city_error'],
        'error_text_6' => !empty($options['error_text_6']) ? $options['error_text_6'] : $locale['postcode_error'],
        'safemode'     => FALSE,
        'flag'         => '',
    );

    $input_id = $options['input_id'];

    $validation_key = array(
        0 => 'street-1',
        1 => 'street-2',
        2 => 'country',
        3 => 'region',
        4 => 'city',
        5 => 'postcode',
    );

    $error_key = array(
        0 => $options['error_text'],
        1 => $options['error_text_2'],
        2 => $options['error_text_3'],
        3 => $options['error_text_4'],
        4 => $options['error_text_5'],
        5 => $options['error_text_6'],
    );

    $error_class = "";
    for ($i = 0; $i <= 5; $i++) {
        if (\defender::inputHasError($input_name.'-'.$validation_key[$i])) {
            $error_class = "has-error ";
            addNotice("danger", "<strong>$title</strong> - ".$error_key[$i]);
        }
    }

    $html = "<div id='$input_id-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' >\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
	".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
	</label>\n" : '';
    $html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
    $html .= "<div class='row'>\n";
    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
    $html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-street' value='".$input_value['0']."' placeholder='".$locale['street1']." ".($options['required'] ? '*' : '')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name.'-'.$validation_key[0])) || \defender::inputHasError($input_name.'-'.$validation_key[0])) ? "<div id='".$options['input_id']."-street-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= "</div>\n";

    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
    // Street 2 is not needed even on required.
    $html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-street2' value='".$input_value['1']."' placeholder='".$locale['street2']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />";
    $html .= "</div>\n";

    $html .= "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5 m-b-10'>\n";
    $html .= "<select name='".$input_name."[]' id='$input_id-country' style='width:100%;'>\n";
    $html .= "<option value=''></option>";
    foreach ($countries as $arv => $countryname) { // outputs: key, value, class - in order
        $country_key = str_replace(" ", "-", $countryname);
        $select = ($input_value[2] == $country_key) ? "selected" : '';
        $html .= "<option value='$country_key' ".$select.">".translate_country_names($countryname)."</option>";
    }
    $html .= "</select>\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name.'-'.$validation_key[2])) || \defender::inputHasError($input_name.'-'.$validation_key[2])) ? "<div id='".$options['input_id']."-country-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_3']."</div>" : "";
    $html .= "</div>\n";
    $html .= "<div class='col-xs-12 col-sm-7 col-md-7 col-lg-7 m-b-10'>\n";
    $html .= "<div id='state-spinner' style='display:none;'>\n<img src='".fusion_get_settings('siteurl')."images/loader.svg'>\n</div>\n";
    $html .= "<input type='hidden' name='".$input_name."[]' id='$input_id-state' value='".$input_value['3']."' style='width:100%;' />\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name.'-'.$validation_key[3])) || \defender::inputHasError($input_name.'-'.$validation_key[3])) ? "<div id='".$options['input_id']."-state-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_4']."</div>" : "";
    $html .= "</div>\n";
    $html .= "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5 m-b-10'>\n";
    $html .= "<input type='text' name='".$input_name."[]' id='".$input_id."-city' class='form-control textbox' value='".$input_value['4']."' placeholder='".$locale['city']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name[4])) || \defender::inputHasError($input_name[4])) ? "<div id='".$options['input_id']."-city-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_5']."</div>" : "";
    $html .= "</div>\n";
    $html .= "<div class='col-xs-12 col-sm-7 col-md-4 col-lg-7 m-b-10'>\n";
    $html .= "<input type='text' name='".$input_name."[]'  id='".$input_id."-postcode' class='form-control textbox' value='".$input_value['5']."' placeholder='".$locale['postcode']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name.'-'.$validation_key[5])) || \defender::inputHasError($input_name.'-'.$validation_key[5])) ? "<div id='".$options['input_id']."-postcode-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_6']."</div>" : "";
    $html .= "</div>\n";
    $html .= "</div>\n"; // close inner row
    $html .= ($options['inline']) ? "</div>\n" : "";
    $html .= "</div>\n";
    \defender::getInstance()->add_field_session(array(
                                     'input_name'   => $input_name,
                                     'type'         => 'address',
                                     'title'        => $title,
                                     'id'           => $input_id,
                                     'required'     => $options['required'],
                                     'safemode'     => $options['safemode'],
                                     'error_text'   => $options['error_text'],
                                     'error_text_2' => $options['error_text_2'],
                                     'error_text_3' => $options['error_text_3'],
                                     'error_text_4' => $options['error_text_4'],
                                     'error_text_5' => $options['error_text_5'],
                                     'error_text_6' => $options['error_text_6']
                                 ));

    $flag_function = '';
    $flag_plugin = '';
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
	placeholder: '".$locale['sel_country']." ".($options['required'] == 1 ? '*' : '')."'
    });
    $('#".$input_id."-country').bind('change', function(){
    	var ce_id = $(this).val();
        $.ajax({
        url: '".fusion_get_settings('site_path')."includes/geomap/form_geomap.json.php',
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
        placeholder: '".$locale['sel_state']." ".($options['required'] == 1 ? '*' : '')."',
        allowClear: true,
        data : data
        });
        },
        error : function() {
            console.log('Error Fetching');
        }
        })
    }).trigger('change');
	");

    return $html;
}

function form_location($input_name, $label = '', $input_value = FALSE, array $options = array()) {
    $locale = fusion_get_locale();
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    if (!defined('PLOCATION')) {
        define('PLOCATION', TRUE);
        add_to_jquery("
		function plocation(item) {
			if(!item.id) {return item.text;}
			var flag = item.flag;
			var region = item.region;
			return '<table><tr><td style=\"\"><img style=\"height:16px;\" src=\"".IMAGES."/' + flag + '\"/></td><td style=\"padding-left:10px\"><div>' + item.text + '</div></div></td></tr></table>';
		}
		");
    }

    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

    $default_options = array(
        'options'        => array(),
        'required'       => FALSE,
        'regex'          => '',
        'input_id'       => $input_name,
        'placeholder'    => $locale['choose-location'],
        'deactivate'     => FALSE,
        'safemode'       => FALSE,
        'allowclear'     => FALSE,
        'flag'           => FALSE,
        'multiple'       => FALSE,
        'width'          => '250px',
        'keyflip'        => FALSE,
        'tags'           => FALSE,
        'jsonmode'       => FALSE,
        'chainable'      => FALSE,
        'max_select'     => 1,
        'error_text'     => $locale['error_input_default'],
        'class'          => '',
        'inline'         => FALSE,
        'tip'            => '',
        'ext_tip'        => '',
        'delimiter'      => ',',
        'callback_check' => '',
        "stacked"        => "",
        'icon'           => '',
        'file'           => '',
    );

    $options += $default_options;

    $countries = array();
    if ($options['multiple'] == FALSE) {
        require(INCLUDES.'geomap/geomap.inc.php');
    }

    // always trim id
    $options['input_id'] = trim($options['input_id'], "[]");

    $length = "minimumInputLength: 1,";

    $error_class = "";
    if (\defender::inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = \defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']." ".($options['icon'] ? 'has-feedback' : '')."'  ".($options['width'] && !$label ? "style='width: ".$options['width']."'" : '').">\n";

    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='".$options['input_id']."'>$label ".($options['required'] == TRUE ? "<span class='required'>*</span>" : '')."
    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
	</label>\n" : '';

    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 p-l-0")."'>\n" : "";

    if ($options['multiple'] == TRUE) {

        $html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='".$options['input_id']."' data-placeholder='".$options['placeholder']."' style='width: ".($options['width'] ? $options['width'] : $default_options['width'])."' ".($options['deactivate'] ? 'disabled' : '')." />";

        $path = $options['file'] ? $options['file'] : INCLUDES."search/location.json.php";
        if (!empty($input_value)) {
            // json mode.
            $encoded = $options['file'] ? $options['file'] : location_search($input_value);
        } else {
            $encoded = json_encode(array());
        }
        add_to_jquery("
        $('#".$options['input_id']."').select2({
        $length
        multiple: ".($options['multiple'] ? "true" : "false").",
        maximumSelectionSize: ".$options['max_select'].",
        ajax: {
        url: '$path',
        dataType: 'json',
        data: function (term, page) {
                return {q: term};
              },
              results: function (data, page) {
                return {results: data};
              }
        },
        formatSelection: plocation,
        escapeMarkup: function(m) { return m; },
        formatResult: plocation,
        ".$options['allowclear']."
        })".(!empty($encoded) ? ".select2('data', $encoded );" : '')."
    ");

    } else {

        $html .= "<select name='".$input_name."' id='".$options['input_id']."' style='width:".($options['width'] ? $options['width'] : $default_options['width'])."' />\n";
        $html .= "<option value=''></option>";
        foreach ($countries as $arv => $countryname) { // outputs: key, value, class - in order
            $country_key = str_replace(" ", "-", $countryname);
            $select = ($input_value == $country_key) ? "selected" : '';
            $html .= "<option value='$country_key' ".$select.">".translate_country_names($countryname)."</option>";
        }
        $html .= "</select>\n";

        $flag_function = '';
        $flag_plugin = '';
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
        $('#".$options['input_id']."').select2({
            $flag_plugin
            placeholder: '".$locale['sel_country']." ".($options['required'] == 1 ? '*' : '')."'
        });
        ");

    }

    $html .= $options['stacked'];
    $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";
    if ($options['deactivate']) {
        $html .= form_hidden($input_name, "", $input_value, array("input_id" => $options['input_id']));
    }

    $html .= \defender::inputHasError($input_name) ? "<div class='input-error".((!$options['inline']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : '';

    $html .= ($options['inline'] && $label) ? "</div>\n" : "";

    $html .= "</div>\n";

    \defender::add_field_session(array(
                                     'input_name'     => $input_name,
                                     'type'           => 'textbox',
                                     'title'          => trim($title, '[]'),
                                     'id'             => $options['input_id'],
                                     'regex'          => $options['regex'],
                                     'callback_check' => $options['callback_check'],
                                     'required'       => $options['required'],
                                     'safemode'       => $options['safemode'],
                                     'error_text'     => $options['error_text']
                                 ));

    return $html;
}

function map_country($states, $country) {
    $states_list = array();
    $flag = "small_flag/flag_".str_replace('-', '_', strtolower($country)).".png";
    foreach ($states[$country] as $states_name) {
        $states_list[] = array(
            'id' => "$states_name", 'text' => "$states_name, $country", 'flag' => "$flag", "region" => "$country"
        );
    }

    return $states_list;
}

function map_region($states) {
    $states_list = array();
    foreach ($states as $country_name => $country_states) {
        $flag = "small_flag/flag_".str_replace('-', '_', strtolower($country_name)).".png";
        foreach ($country_states as $states_name) { // add [] to prevent duplicate since Sabah exist in Yemen and Malaysia.
            $states_list[$states_name][] = array(
                'id' => "$states_name", 'text' => "$states_name, $country_name", 'flag' => "$flag",
                "region" => "$country_name"
            );
        }
    }

    return $states_list;
}

/* Returns Json Encoded Object used in form_select_user */
function location_search($q) {
    $states = array();
    include INCLUDES."geomap/geomap.inc.php";
    // since search is on user_name.
    $found = 0;
    foreach (array_keys($states) as $k) { // type the country then output full states
        if (preg_match('/^'.$q.'/', $k, $matches)) {
            $found = 1;
            $states_list = map_country($states, $k);

            return json_encode($states_list);
        }
    }
    if (!$found) { // a longer version
        $region_list = map_region($states);
        if (array_key_exists($q, $region_list)) {
            //print_p($region_list[$q]);
            return json_encode($region_list[$q]);
        }
    }

    return FALSE;
}
