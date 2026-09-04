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
function form_geomap_combobox_attribute(array $overrides = []): string
{
    $options = $overrides + [
        'ajax'                 => [],
        'remote'               => [],
        'placeholder'          => '',
        'combobox_label'       => '',
        'floating_label'       => FALSE,
        'required'             => FALSE,
        'multiple'             => FALSE,
        'tags'                 => FALSE,
        'allowclear'           => FALSE,
        'allow_clear'          => FALSE,
        'display_search_count' => 5,
        'max_select'           => FALSE,
        'delimiter'            => ',',
        'flag'                 => FALSE,
        'flag_path'            => '',
        'avatar_path'          => '',
        'initial_items'        => [],
        'strings'              => [],
    ];

    return form_select_combobox_attribute($options);
}

function form_geo($input_name, $label = "", $input_value = "", array $options = []) {

    $locale = fusion_get_locale();

    $input_value = clean_input_value($input_value);

    $title = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $countries = [];
    include INCLUDES.'geomap/geo.countries.php';

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
        'safemode'        => false,
        'flag'            => '',
        'stacked'         => '',
        'default_country' => fusion_get_settings('site_location'),
    ];

    $options += $default_options;

    $default_country = strtoupper(trim((string)$options['default_country']));
    if (empty($input_value[2]) && isset($countries[$default_country])) {
        $input_value[2] = $default_country;
    }

    $input_id = $options['input_id'];
    $country_placeholder = $locale['sel_country'].' '.($options['required'] == 1 ? '*' : '');
    $state_placeholder = $locale['sel_state'].' '.($options['required'] == 1 ? '*' : '');
    $state_endpoint = fusion_get_settings('site_path').'api/?api=geomap-states';
    $country_combobox = form_geomap_combobox_attribute([
        'placeholder' => $country_placeholder,
        'combobox_label' => $locale['sel_country'],
        'required' => $options['required'],
        'allowclear' => !$options['required'],
        'allow_clear' => !$options['required'],
        'flag' => $options['flag'],
    ]);
    $state_combobox = form_geomap_combobox_attribute([
        'placeholder' => $state_placeholder,
        'combobox_label' => $locale['sel_state'],
        'required' => $options['required'],
        'allowclear' => TRUE,
        'allow_clear' => TRUE,
        'remote' => [
            'url' => $state_endpoint,
            'query_param' => 'q',
            'delay' => 150,
            'minimum_input_length' => 0,
            'load_on_open' => TRUE,
            'params' => ['id' => $input_value[2]],
        ],
    ]);

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
    ".dynamics_field_help($options['tip'])."
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

    // Keep the named controls in legacy serialization order while presenting
    // the locality fields as City, State, Country.
    $html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 order-3 m-b-10'>";

    $html .= "<select class='form-select' name='".$input_name."[]' id='".$input_id."_country' style='width:100%;' data-geomap-country data-geomap-state-target='".$input_id."_state' data-dynamics-combobox='".$country_combobox."'>";

    $html .= "<option value=''></option>";

    foreach ($countries as $arv => $country) { // outputs: key, value, class - in order
        $select = ($input_value[2] == $arv) ? "selected" : '';
        $flag = 'flag_'.strtolower(str_replace(' ', '_', $country['name'])).'.png';
        $html .= "<option value='".form_select_escape_attribute($arv)."' data-flag='".form_select_escape_attribute($flag)."' ".$select.">".form_select_escape_text($country['name'])."</option>";
    }

    $html .= "</select>";

    $html .= (($options['required'] == 1 && \Defender::inputHasError($input_name.$validation_key[2])) || \Defender::inputHasError($input_name.'-'.$validation_key[2])) ? "<div id='".$options['input_id']."-country-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_3']."</div>" : "";

    $html .= "</div>";

    $html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 order-2 m-b-10'>";

    $html .= "<input type='hidden' id='".$input_id."_state_fallback' value='' data-geomap-state-fallback data-input-name='".form_select_escape_attribute($input_name."[]")."'".(!empty($input_value[2]) ? '' : " name='".form_select_escape_attribute($input_name."[]")."'")." />";
    $html .= "<select class='form-select' name='".$input_name."[]' id='".$input_id."_state' style='width:100%;' data-geomap-state data-dynamics-combobox='".$state_combobox."'".(empty($input_value[2]) ? ' disabled' : '').">";
    $html .= "<option value=''></option>";
    if ($input_value[3] !== '') {
        $html .= "<option value='".form_select_escape_attribute($input_value[3])."' selected>".form_select_escape_text($input_value[3])."</option>";
    }
    $html .= "</select>";

    $html .= (($options['required'] == 1 && \Defender::inputHasError($input_name.$validation_key[3])) || \Defender::inputHasError($input_name.'-'.$validation_key[3])) ? "<div id='".$options['input_id']."-state-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_4']."</div>" : "";

    $html .= "</div>";

    $html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 order-1 m-b-10'>";

    $html .= "<input type='text' name='".$input_name."[]' id='".$input_id."_city' class='form-control textbox' value='".$input_value['4']."' placeholder='".$locale['city'].($options['required'] ? "*" : '')."'".($options['deactivate'] ? " readonly" : '')." />";

    $html .= (($options['required'] == 1 && \Defender::inputHasError($input_name.$validation_key[4])) || \Defender::inputHasError($input_name)) ? "<div id='".$options['input_id']."-city-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_5']."</div>" : "";

    $html .= "</div>";

    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 order-4 m-b-10'>";

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

    load_form_select_combobox_assets();
    fusion_load_script(DYNAMICS.'includes/form_geomap/component.js');

    return dynamics_render_component_template('form_geomap', $html);
}

function form_location($input_name, $label = '', $input_value = false, array $options = []) {

    $locale = fusion_get_locale();

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
    $input_name = !empty($input_name) ? stripinput($input_name) : '';
    $country_options = $options['options'];

    if ($country_options === []) {
        $countries = [];
        require INCLUDES.'geomap/geo.countries.php';
        foreach ($countries as $country_code => $country) {
            $country_name = translate_country_names($country['name']);
            $country_options[$country_code] = [
                'text' => $country_name,
                'flag' => 'flag_'.strtolower(str_replace(' ', '_', $country['name'])).'.png',
            ];
        }
    }

    $select_options = [
        'options'        => $country_options,
        'required'       => $options['required'],
        'regex'          => $options['regex'],
        'input_id'       => trim($options['input_id'], '[]'),
        'placeholder'    => $options['placeholder'],
        'deactivate'     => $options['deactivate'],
        'safemode'       => $options['safemode'],
        'allowclear'     => $options['allowclear'],
        'allow_clear'    => $options['allowclear'],
        'flag'           => $options['flag'],
        'multiple'       => $options['multiple'],
        'width'          => $options['width'],
        'inner_width'    => '100%',
        'keyflip'        => $options['keyflip'],
        'tags'           => $options['tags'],
        'chainable'      => $options['chainable'],
        'max_select'     => $options['max_select'],
        'error_text'     => $options['error_text'],
        'class'          => $options['class'],
        'inline'         => $options['inline'],
        'tip'            => $options['tip'],
        'ext_tip'        => $options['ext_tip'],
        'delimiter'      => $options['delimiter'],
        'callback_check' => $options['callback_check'],
        'stacked'        => $options['stacked'],
    ];

    if ($options['multiple']) {
        $select_options['options'] = [];
        $select_options['jsonmode'] = TRUE;
        $select_options['remote'] = [
            'url' => $options['file'] ?: INCLUDES.'dynamics/assets/location/location.json.php',
            'query_param' => 'q',
            'minimum_input_length' => 1,
        ];
        $select_options['initial_items'] = json_decode(location_search((string)$input_value), TRUE) ?: [];
    }

    return form_select($input_name, $label, $input_value, $select_options);
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
