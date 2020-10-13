<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
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

use PHPFusion\Geomap;

function form_geo( $input_name, $label, $input_value = FALSE, array $options = [] ) {
    global $fusion_steam;

    $locale = fusion_get_locale();
    $title = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $countries = [];
    require(INCLUDES.'geomap/geomap.inc.php');

    // NOTE (remember to parse readback value as of '|' seperator)
    if (isset($input_value) && (!empty($input_value))) {
        if (!is_array($input_value)) {
            $input_value = explode('|', $input_value);
        }
    } else {
        $input_value = [];
        $input_value[] = "";
        $input_value[] = "";
        $input_value[] = "";
        $input_value[] = "";
        $input_value[] = "";
        $input_value[] = "";
    }

    $options += [
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
        'flag'         => FALSE,
        'stacked'      => '',
    ];

    $input_id = $options['input_id'];

    $validation_key = [
        0 => 'street-1',
        1 => 'street-2',
        2 => 'country',
        3 => 'region',
        4 => 'city',
        5 => 'postcode',
    ];

    $error_key = [
        0 => $options['error_text'],
        1 => $options['error_text_2'],
        2 => $options['error_text_3'],
        3 => $options['error_text_4'],
        4 => $options['error_text_5'],
        5 => $options['error_text_6'],
    ];

    $options['error_class'] = "";
    for ($i = 0; $i <= 5; $i++) {
        if ( Defender::inputHasError( $input_name.'-'.$validation_key[ $i ] ) ) {
            $options['error_class'] = "has-error ";
            add_notice("danger", "<strong>$title</strong> - ".$error_key[$i]);
        }
    }

    // input name is
    $grid_class = $fusion_steam->load('Layout')->getColumnClass([100, 100, 100]).' p-0';

    $grid_offset_class = $fusion_steam->load('Layout')->getColumnClass([0, 0, 0, 0], TRUE);

    if ($options['inline'] === TRUE && !empty($label)) {

        $grid_class = $fusion_steam->load('Layout')->getColumnClass([100, 100, 80]);

        $grid_offset_class = $fusion_steam->load('Layout')->getColumnClass([0, 0, 20, 20], TRUE);

    }

    // Street 1
    $options['placeholder'] = $locale['street1'];
    $options['input_id'] = $input_id.'-street1';

    $default_label = [
        0 => '',
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => ''
    ];
    $input_label = [];
    if (!empty($label)) {
        $input_label = $label; // array
        // 9.00 compatibility
        if (is_string($label)) {
            unset($input_label);
            $input_label[] = $label;
        }
    }
    $input_label += $default_label;

    $html = form_text($input_name.'[0]', $input_label[0], $input_value[0], $options);

    // Street 2
    // This is not required at all times.
    $options['placeholder'] = $locale['street2'];
    $options['input_id'] = $input_id.'-street2';
    $html .= "<div class='clearfix'><div class='$grid_class $grid_offset_class'>";
    $html .= form_text($input_name.'[1]', $input_label[1], $input_value[1], $options + ['required' => FALSE]);
    $html .= "</div></div>";

    // Country
    // Deprecate this method to MY etc.
    $country_options = Geomap::get_Country();
    $array = [];
    foreach ($countries as $arv => $countryname) { // outputs: key, value, class - in order
        $country_key = str_replace(" ", "-", $countryname);
        $country_name = translate_country_names($countryname);
        $array[$country_key] = $country_name;
    }

    $options['options'] = $country_options;
    $options['input_id'] = $input_id.'-country';
    $options['select2_disabled'] = TRUE;
    $html .= "<div class='clearfix'><div class='$grid_class $grid_offset_class'>";
    // Country
    $html .= "<div class='display-inline-block m-r-10'>";
    $html .= form_select($input_name.'[2]', $input_label[2], $input_value[2], $options);
    $html .= "</div>";
    // Region
    $options['input_id'] = $input_id.'-state';
    $options['options'] = [];
    $options['jsonmode'] = TRUE;
    $options['allowclear'] = FALSE;
    $html .= "<div class='display-inline-block'>";
    $html .= form_select($input_name.'[3]', $input_label[3], $input_value[3], $options);
    $html .= "</div>";
    $html .= "</div></div>"; // clearfix, gridclass end

    $html .= "<div class='clearfix'><div class='$grid_class $grid_offset_class'>";
    // City
    $options['placeholder'] = $locale['city'];
    $options['width'] = '250px';
    $options['inner_width'] = '250px';
    $options['input_id'] = $input_id.'-city';
    $html .= "<div class='display-inline-block m-r-10'>";
    $html .= form_text($input_name.'[4]', $input_label[4], $input_value[4], $options);
    $html .= "</div>";

    // Postal code
    $options['placeholder'] = $locale['postcode'];
    $options['input_id'] = $input_id.'-postcode';
    $html .= "<div class='display-inline-block m-r-10'>";
    $html .= form_text($input_name.'[5]', $input_label[5], $input_value[5], $options);
    $html .= "</div>";
    $html .= "</div>";
    $html .= "</div>";

    $config = [
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
    ];

    Defender::getInstance()->add_field_session( $config );

    static $flag_function = NULL;
    $flag_plugin = '';

    if ($options['flag']) {
        if (empty($flag_function)) {
            $flag_function = DYNAMICS.'assets/geo/flag.select2.js';
            add_to_head("<script src='$flag_function'></script>");
        }

        $flag_plugin = "
         formatResult: flag.show_flag,
         formatSelection: flag.show_flag,
         escapeMarkup: function(m) { return m; },
        ";
    }

    $state_default_selected = 0;

    $default_opts[] = [
        'id'   => '0',
        'text' => $locale['sel_state']
    ];
    $default_opts += state_search('AW'); // AW is the first one.
    $state_default = json_encode($default_opts);

    if (!empty($input_value[2])) {
        // find the states array
        // find the states
        $state_default_opts = state_search($input_value[2]);
        $state_default = json_encode($state_default_opts);
        // submitted but states are blank - find default values
        if (!empty($state_default_opts)) {
            $state_default_selected = $state_default_opts[0]['id'];
            if (count($state_default_opts) > 1) {
                $state_default_selected = $state_default_opts[1]['id'];
            }
        }
    }

    if (!empty($input_value[3])) {
        $state_default_selected = $input_value[3];
    }

    // make this into a function object.
    add_to_jquery( "
    $('#$input_id-country').select2({
        $flag_plugin
        placeholder: '".$locale['sel_country']." ".($options['required'] == 1 ? '*' : '')."'

    });

    $('#$input_id-state').select2({
        data: $state_default,
        allowClear: false,
        //placeholder: '".$locale['sel_state']." ".($options['required'] == 1 ? '*' : '')."'
    });

    $('#$input_id-state').select2('val', '$state_default_selected');

    // on change event.
    $('body').on('change', '#$input_id-country', function(){
        var ce_id = $(this).val();
        $.ajax({
            url: '".fusion_get_settings('site_path')."includes/dynamics/assets/geo/states.geo.php',
            type: 'GET',
            data: { id : ce_id },
            dataType: 'json',
            success: function(data) {
                $('#".$input_id."-state').select2({
                    placeholder: '".$locale['sel_state']." ".($options['required'] == 1 ? '*' : '')."',
                    allowClear: false,
                    data : data
                });
                $('#$input_id-state').select2('val', data[1]['id']);
            },
            error : function() {
                console.log('Error fetching region results');
            }
        })
    });
    ");

    return (string)$html;
}

function form_location($input_name, $label = '', $input_value = FALSE, array $options = []) {
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

    $default_options = [
        'options'        => [],
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
    ];

    $options += $default_options;

    $countries = [];
    if ($options['multiple'] == FALSE) {
        require(INCLUDES.'geomap/geomap.inc.php');
    }

    // always trim id
    $options['input_id'] = trim(str_replace("[", "-", $options['input_id']), "]");

    $length = "minimumInputLength: 1,";

    $error_class = "";
    if ( Defender::inputHasError( $input_name ) ) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = Defender::getErrorText( $input_name );
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            add_notice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']." ".($options['icon'] ? 'has-feedback' : '')."'  ".($options['width'] && !$label ? "style='width: ".$options['width']."'" : '').">\n";

    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='".$options['input_id']."'>$label ".($options['required'] == TRUE ? "<span class='required'>*</span>" : '')."
    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
    </label>\n" : '';

    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12")."'>\n" : "";

    if ($options['multiple'] == TRUE) {

        $html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='".$options['input_id']."' data-placeholder='".$options['placeholder']."' style='width: ".($options['width'] ? $options['width'] : $default_options['width'])."' ".($options['deactivate'] ? 'disabled' : '')." />";

        $path = $options['file'] ? $options['file'] : DYNAMICS."assets/location/location.json.php";
        if (!empty($input_value)) {
            // json mode.
            $encoded = $options['file'] ? $options['file'] : location_search($input_value);
        } else {
            $encoded = json_encode([]);
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
            }
            ";
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
        $html .= form_hidden($input_name, "", $input_value, ["input_id" => $options['input_id']]);
    }

    $html .= Defender::inputHasError( $input_name ) ? "<div class='input-error".( ( !$options['inline'] ) ? " display-block" : "" )."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : '';

    $html .= ($options['inline'] && $label) ? "</div>\n" : "";

    $html .= "</div>\n";

    Defender::add_field_session( [
        'input_name'     => $input_name,
        'type'           => 'textbox',
        'title'          => trim($title, '[]'),
        'id'             => $options['input_id'],
        'regex'          => $options['regex'],
        'callback_check' => $options['callback_check'],
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'error_text'     => $options['error_text']
    ]);

    return (string)$html;
}

function map_country($states, $country) {
    $states_list = [];
    $flag = "small_flag/flag_".str_replace('-', '_', strtolower($country)).".png";
    foreach ($states[$country] as $states_name) {
        $states_list[] = [
            'id' => "$states_name", 'text' => "$states_name, $country", 'flag' => "$flag", "region" => "$country"
        ];
    }

    return $states_list;
}

function map_region($states) {
    $states_list = [];
    foreach ($states as $country_name => $country_states) {
        $flag = "small_flag/flag_".str_replace('-', '_', strtolower($country_name)).".png";
        foreach ($country_states as $states_name) { // add [] to prevent duplicate since Sabah exist in Yemen and Malaysia.
            $states_list[$states_name][] = [
                'id'     => "$states_name", 'text' => "$states_name, $country_name", 'flag' => "$flag",
                "region" => "$country_name"
            ];
        }
    }

    return $states_list;
}

/* Returns Json Encoded Object used in form_select_user */
function location_search($q) {
    $states = [];
    include INCLUDES."geomap/geomap.inc.php";
    // since search is on user_name.
    $found = 0;
    foreach (array_keys($states) as $k) { // type the country then output full states
        if (preg_match('/^'.$q.'/', $k, $matches)) {
            header('Content-Type: application/json');
            $states_list = map_country($states, $k);
            return json_encode($states_list);
        }
    }
    if (!$found) { // a longer version
        $region_list = map_region($states);
        if (array_key_exists($q, $region_list)) {
            header('Content-Type: application/json');
            return json_encode($region_list[$q]);
        }
    }

    return FALSE;
}


// New Geomap class functions
function state_search($country_iso) {
    /*$states = [];
    include INCLUDES."geomap/geomap.inc.php";
    $states_array[] = ["id" => "Other", "text" => fusion_get_locale('other_states')];

    foreach ($states as $key => $value) {
         if ($country_iso == $key) {
             if (!empty($value)) {
                 foreach ($value as $name => $region) {
                     $states_array[] = ['id' => $region, 'text' => $region];
                 }
             }
             return $states_array;
         }
    }*/

    $states = Geomap::get_StatesOpts( $country_iso );
    $array[] = ['id' => 'other', 'text' => fusion_get_locale('other_states')];
    if (!empty($states)) {
        foreach ($states as $key => $val) {

            $key = normalize(html_entity_decode($key));

            $array[] = [
                'id'   => $key,
                'text' => $val
            ];
        }
        return $array;
    }

    return [];
}
