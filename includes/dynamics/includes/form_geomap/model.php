<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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

/**
 * @param string $input_name
 * @param string $label
 * @param string $input_value
 * @param array  $options
 *
 * @return string
 */
function form_geo($input_name, $label = "", $input_value = "", array $options = []) {

    $locale = fusion_get_locale();

    $input_value = clean_input_value($input_value);

    $title = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $countries = [];
    $states = [];

    include INCLUDES.'geomap/geo.countries.php';
    include INCLUDES.'geomap/geo.states.php';

    $states += ["id" => "Other", "text" => fusion_get_locale('other_states')];
    $states_array = json_encode($states);

    $id = trim($input_name, "[]");

    // NOTE (remember to parse readback value as of '|' seperator)
    if (isset($input_value) && (!empty($input_value))) {
        if (!is_array($input_value)) {
            $input_value = explode('|', $input_value);
        }
    } else {
        $input_value = [];
        $input_value[0] = "";
        $input_value[1] = "";
        $input_value[2] = "";
        $input_value[3] = "";
        $input_value[4] = "";
        $input_value[5] = "";
    }

    $default_options = [
        'input_id'     => $id,
        'required'     => false,
        'placeholder'  => '',
        'deactivate'   => false,
        'width'        => '100%',
        'class'        => '',
        'inline'       => false,
        'tip'          => '',
        'error_text'   => !empty($options['error_text']) ? $options['error_text'] : $locale['street_error'],
        'error_text_2' => !empty($options['error_text_2']) ? $options['error_text_2'] : $locale['street_error'],
        'error_text_3' => !empty($options['error_text_3']) ? $options['error_text_3'] : $locale['country_error'],
        'error_text_4' => !empty($options['error_text_4']) ? $options['error_text_4'] : $locale['state_error'],
        'error_text_5' => !empty($options['error_text_5']) ? $options['error_text_5'] : $locale['city_error'],
        'error_text_6' => !empty($options['error_text_6']) ? $options['error_text_6'] : $locale['postcode_error'],
        'safemode'     => false,
        'flag'         => '',
        'stacked'      => '',
    ];

    $options += $default_options;

    $input_id = $options['input_id'];

    $validation_key = [
        0 => 'street1',
        1 => 'street2',
        2 => 'country',
        3 => 'region',
        4 => 'city',
        5 => 'postcode',
    ];

    $error_key = [
        0 => $options['error_text'], // street1
        1 => $options['error_text_2'], // street2
        2 => $options['error_text_3'], // country
        3 => $options['error_text_4'], // state
        4 => $options['error_text_5'], // city
        5 => $options['error_text_6'], // postcode
    ];

    $error_class = "";
    for ($i = 0; $i <= 5; $i++) {
        if (\Defender::inputHasError($input_name.'-'.$validation_key[$i])) {
            $error_class = "has-error ";
            addnotice("danger", $error_key[$i]);
        }
    }

    $html = "<div id='$input_id-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').$error_class.$options['class']."' >";

    $html .= ($label) ? "<label class='control-label".($options['inline'] ? " col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
    </label>" : '';

    $html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>" : '';

    $html .= "<div class='row'>";

    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>";

    $html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."_street' value='".$input_value['0']."' placeholder='".$locale['street1'].($options['required'] ? "*" : '')."'".($options['deactivate'] ? " readonly" : '')." />";

    $html .= (($options['required'] == 1 && \Defender::inputHasError($input_name.$validation_key[0])) || \Defender::inputHasError($input_name.'-'.$validation_key[0])) ? "<div id='".$options['input_id']."-street-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";

    $html .= "</div>";

    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>";
    // Street 2 is not needed even on required.
    $html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."_street2' value='".$input_value['1']."' placeholder='".$locale['street2']."'".($options['deactivate'] ? " readonly" : '')." />";

    $html .= "</div>";

    $html .= "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5 m-b-10'>";

    $html .= "<select name='".$input_name."[]' id='".$input_id."_country' style='width:100%;'>";

    $html .= "<option value=''></option>";

    foreach ($countries as $arv => $country) { // outputs: key, value, class - in order
        $select = ($input_value[2] == $arv) ? "selected" : '';
        $html .= "<option value='$arv' ".$select.">".$country['name']."</option>";
    }

    $html .= "</select>";

    $html .= (($options['required'] == 1 && \Defender::inputHasError($input_name.$validation_key[2])) || \Defender::inputHasError($input_name.'-'.$validation_key[2])) ? "<div id='".$options['input_id']."-country-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_3']."</div>" : "";

    $html .= "</div>";

    $html .= "<div class='col-xs-12 col-sm-7 col-md-7 col-lg-7 m-b-10'>";

    $html .= "<div id='state-spinner' style='display:none;'><img src='".fusion_get_settings('siteurl')."images/loader.gif'></div>";

    $html .= "<input type='hidden' name='".$input_name."[]' id='".$input_id."_state' value='".$input_value['3']."' style='width:100%;' />";

    $html .= (($options['required'] == 1 && \Defender::inputHasError($input_name.$validation_key[3])) || \Defender::inputHasError($input_name.'-'.$validation_key[3])) ? "<div id='".$options['input_id']."-state-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_4']."</div>" : "";

    $html .= "</div>";

    $html .= "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5 m-b-10'>";

    $html .= "<input type='text' name='".$input_name."[]' id='".$input_id."_city' class='form-control textbox' value='".$input_value['4']."' placeholder='".$locale['city'].($options['required'] ? "*" : '')."'".($options['deactivate'] ? " readonly" : '')." />";

    $html .= (($options['required'] == 1 && \Defender::inputHasError($input_name.$validation_key[4])) || \Defender::inputHasError($input_name)) ? "<div id='".$options['input_id']."-city-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_5']."</div>" : "";

    $html .= "</div>";

    $html .= "<div class='col-xs-12 col-sm-7 col-md-4 col-lg-7 m-b-10'>";

    $html .= "<input type='text' name='".$input_name."[]'  id='".$input_id."_postcode' class='form-control textbox' value='".$input_value['5']."' placeholder='".$locale['postcode'].($options['required'] ? "*" : '')."'".($options['deactivate'] ? " readonly" : '')." />";

    $html .= (($options['required'] == 1 && \Defender::inputHasError($input_name.$validation_key[5])) || \Defender::inputHasError($input_name.'-'.$validation_key[5])) ? "<div id='".$options['input_id']."-postcode-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_6']."</div>" : "";

    $html .= "</div>";

    $html .= "</div>"; // close inner row

    $html .= $options['stacked'];

    $html .= $options['inline'] && $label ? "</div>" : "";

    $html .= "</div>";

    \Defender::getInstance()->add_field_session([
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
    ]);

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
    $('#{$input_id}_country').select2({
        $flag_plugin
        placeholder: '".$locale['sel_country']." ".($options['required'] == 1 ? '*' : '')."'
    });

    $('#{$input_id}_state').select2({
        placeholder: '".$locale['sel_state']." ".($options['required'] == 1 ? '*' : '')."',
        allowClear: true,
        data: $states_array
    });

    $('#{$input_id}_country').bind('change', function(){
        var ce_id = $(this).val();
        $.ajax({
        url: '".fusion_get_settings('site_path')."api/?api=geomap-states',
        type: 'GET',
        data: { id : ce_id },
        dataType: 'json',
        beforeSend: function(e) {
            //$('#state-spinner').show();
            $('#{$input_id}_state').hide();
        },
        success: function(data) {
            //$('#state-spinner').hide();
            $('#{$input_id}_state').select2({
                placeholder: '".$locale['sel_state']." ".($options['required'] == 1 ? '*' : '')."',
                allowClear: true,
                data : data
            });
        },
        error : function() {
            console.log('Geomap states file is missing!');
        }
        })
    }).trigger('change');
    ");

    load_select2_script();

    return dynamics_render_component_template('form_geomap', $html);
}

function form_location($input_name, $label = '', $input_value = false, array $options = []) {

    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

    $default_options = [
        'options'        => [],
        'required'       => false,
        'regex'          => '',
        'input_id'       => $input_name,
        'placeholder'    => $locale['choose-location'],
        'deactivate'     => false,
        'safemode'       => false,
        'allowclear'     => false,
        'flag'           => false,
        'multiple'       => false,
        'width'          => '250px',
        'keyflip'        => false,
        'tags'           => false,
        'jsonmode'       => false,
        'chainable'      => false,
        'max_select'     => 1,
        'error_text'     => $locale['error_input_default'],
        'class'          => '',
        'inline'         => false,
        'tip'            => '',
        'ext_tip'        => '',
        'delimiter'      => ',',
        'callback_check' => '',
        "stacked"        => "",
        'icon'           => '',
        'file'           => '',
    ];

    $options += $default_options;

    $countries = [];

    // always trim id
    $options['input_id'] = trim($options['input_id'], "[]");

    $length = "minimumInputLength: 1,";

    $error_class = "";
    if (\Defender::inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = \Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addnotice("danger", $options['error_text']);
        }
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'row ' : '').$error_class.$options['class']." ".($options['icon'] ? 'has-feedback' : '')."'  ".($options['width'] && !$label ? "style='width: ".$options['width']."'" : '').">";

    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='".$options['input_id']."'>$label ".($options['required'] == true ? "<span class='required'>*</span>" : '')."
    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
    </label>" : '';

    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12")."'>" : "";

    if ($options['multiple'] == true) {

        $html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='{$input_name}[]' id='".$options['input_id']."' data-placeholder='".$options['placeholder']."' style='width: ".(!empty($options['width']) ? $options['width'] : $default_options['width'])."' ".($options['deactivate'] ? 'disabled' : '')." />";

        $path = !empty($options['file']) ? $options['file'] : INCLUDES.'dynamics/assets/location/location.json.php';

        $encoded = json_encode([]);

        if (!empty($input_value)) {
            // json mode.
            $encoded = !empty($options['file']) ? $options['file'] : location_search($input_value);
        }

        if (!defined('PLOCATION_MULTI')) {
            define('PLOCATION_MULTI', true);
            add_to_jquery("
            function plocation_multi(item) {
                if(!item.id) {return item.text;}
                var flag = item.flag;
                var region = item.region;
                return '<table><tr><td style=\"\"><img style=\"height:16px;\" src=\"".IMAGES."small_flag/' + flag + '\"/></td><td style=\"padding-left:10px\"><div>' + item.text + '</div></div></td></tr></table>';
            }
            ");
        }

        $flag_plugin = '';
        if ($options['flag']) {

            $flag_plugin = "
            formatResult: plocation_multi,
            formatSelection: plocation_multi,
            escapeMarkup: function(m) { return m; },
            ";
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
            ".$flag_plugin."            
            ".$options['allowclear']."
        })".(!empty($encoded) ? ".select2('data', $encoded );" : '')."
        ");

    } else {

        require INCLUDES.'geomap/geo.countries.php';

        $html .= "<select name='".$input_name."' id='".$options['input_id']."' style='width:".(!empty($options['width']) ? $options['width'] : $default_options['width'])."' />";
        $html .= "<option value=''></option>";
        foreach ($countries as $country_key => $country) { // outputs: key, value, class - in order
            $select = ($input_value == $country_key) ? "selected" : '';
            $html .= "<option value='$country_key' ".$select.">".translate_country_names($country['name'])."</option>";
        }
        $html .= "</select>";


        $flag_plugin = '';
        if ($options['flag']) {

            if (!defined('PLOCATION')) {
                define('PLOCATION', true);
                add_to_jquery("
                function plocation(item) {
                    if(!item.id) {return item.text;}
                    var icon = '".IMAGES."small_flag/flag_'+ item.id.replace(/-/gi,'_').toLowerCase() +'.png';
                    return '<img style=\"float:left; margin-right:5px; margin-top:3px;\" src=\"' + icon + '\"/></i>' + item.text;
                }");
            }

            $flag_plugin = "
            formatResult: plocation,
            formatSelection: plocation,
            escapeMarkup: function(m) { return m; },
            ";

        }

        add_to_jquery("        
        $('#".$options['input_id']."').select2({
            $flag_plugin
            placeholder: '".$locale['sel_country']." ".($options['required'] == 1 ? '*' : '')."'
        });
        ");

    }

    $html .= $options['stacked'];
    $html .= $options['ext_tip'] ? "<br/><span class='tip'><i>".$options['ext_tip']."</i></span>" : "";
    if ($options['deactivate']) {
        $html .= form_hidden($input_name, "", $input_value, ["input_id" => $options['input_id']]);
    }

    $html .= \Defender::inputHasError($input_name) ? "<div class='input-error".((!$options['inline']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : '';

    $html .= ($options['inline'] && $label) ? "</div>" : "";

    $html .= "</div>";

    \Defender::add_field_session([
        'input_name'     => clean_input_name($input_name),
        'type'           => 'textbox',
        'title'          => trim($title, '[]'),
        'id'             => $options['input_id'],
        'regex'          => $options['regex'],
        'callback_check' => $options['callback_check'],
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'error_text'     => $options['error_text']
    ]);

    load_select2_script();

    return dynamics_render_component_template('form_geomap', $html);
}

/* Returns json encoded response for form_location  */
function location_search($q)
{
    $country = [];

    include INCLUDES."geomap/geo.countries.php";

    if (empty($q)) {
        return json_encode($country);
    }

    $country_codes = explode(',', $q);

    foreach ($countries as $cca => $country_array) {

        $country_name = $country_array['name'];
        $country_code = $cca;

        if (in_array($country_code, $country_codes)) {

            $country[] = ['id' => $country_code, 'text' => $country_name, 'flag'=>'flag_'.str_replace(" ", "_",$country_name).'.png'];
        }
    }

    return json_encode($country);

}
