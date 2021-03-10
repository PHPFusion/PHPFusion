<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_contact.php
| Author: Frederick MC CHan (Chan)
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
 * @param        $input_name
 * @param        $label
 * @param string $input_value
 * @param array  $options
 *
 * @return string
 */
function form_contact($input_name, $label, $input_value = "", $options = []) {

    $locale = fusion_get_locale();

    $input_value = clean_input_value($input_value);

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $id = trim($input_name, "[]");

    $default_options = [
        'type'              => 'text',
        'required'          => FALSE,
        'label_icon'        => '',
        'feedback_icon'     => '',
        'safemode'          => FALSE,
        'regex'             => '',
        'regex_error_text'  => '',
        'callback_check'    => FALSE,
        'input_id'          => $id,
        'placeholder'       => '',
        'deactivate'        => FALSE,
        'width'             => '',
        'inner_width'       => '',
        'class'             => '',
        'inner_class'       => '',
        'inline'            => FALSE,
        'min_length'        => 1,
        'max_length'        => 200,
        'icon'              => '',
        'autocomplete_off'  => FALSE,
        'tip'               => '',
        'ext_tip'           => '',
        // prepend is prohibited
        'append_id'         => "p-".$id."-append",
        'append_button'     => '',
        'append_value'      => '',
        'append_form_value' => '',
        'append_size'       => '',
        'append_class'      => 'btn-default',
        'append_type'       => 'submit',
        'delimiter'         => '|',
        'stacked'           => '',
        'group_size'        => '', // http://getbootstrap.com/components/#input-groups-sizing - sm, md, lg
        'data'              => [],
        'append_html'       => '',
        'descript'          => TRUE,
        'error_text'        => !empty($options['error_text']) ? $options['error_text'] : $locale['prefix_error'],
        'error_text_2'      => !empty($options['error_text_2']) ? $options['error_text_2'] : $locale['contact_error'],
    ];

    $options += $default_options;

    $options['type'] = "tel";

    // NOTE (remember to parse readback value as of '|' seperator)
    if (isset($input_value) && (!empty($input_value))) {
        if (!is_array($input_value)) {
            $input_value = explode($options["delimiter"], $input_value);
        }
    } else {
        $input_value = [];
        $input_value['0'] = "";
        $input_value['1'] = "";
    }

    $options += [
        'append_button_name' => !empty($options['append_button_name']) ? $options['append_button_name'] : "p-submit-".$options['input_id'],
        'append_button_id'   => !empty($options['append_button_id']) ? $options['append_button_id'] : $options['input_id'].'-append-btn',
    ];

    if (!empty($options['data'])) {
        array_walk($options['data'], function ($a, $b) use (&$options_data) {
            $options_data[] = "data-$b='$a'";
        }, $options_data);
    }

    $error_class = "";
    if (\defender::inputHasError($input_name)) {
        $error_class = " has-error";
        if (!empty($options['error_text'])) {
            $new_error_text = \defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    // Fixes HTML DOM type number that does not respect max_length prop.
    $max_length = '';
    if ($options['max_length'] && isnum($options['max_length'])) {
        $max_length = ' maxlength="'.$options['max_length'].'"';
    }

    // Formats a prefix number

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').($error_class ? $error_class : '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'".($options['width'] && !$label ? " style='width: ".$options['width']."'" : '').">";

    $html .= ($label ? "<label class='control-label".($options['inline'] ? " col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$options['label_icon'].$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>" : "");

    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>" : "";

    $html .= "<div class='input-group".($options['group_size'] ? ' input-group-'.$options['group_size'] : '')."' ".($options['width'] ? "style='width: ".$options['width']."'" : '').">";

    $html .= "<span class='input-group-addon input-group-prepend p-0 br-0'>";

    $html .= "<span class='input-group-text'>";

    $html .= form_select($input_name."_prefix", "", $input_value[0], ["options" => calling_codes(), "class" => "m-0", "width" => "250px"]);

    $html .= "</span>";

    $html .= "</span>";

    $html .= "<input type='tel' data-type='tel' ".(!empty($options_data) ? implode(' ', $options_data) : '')." "."class='form-control textbox ".($options['inner_class'] ? " ".$options['inner_class']." " : '')."' ".($options['inner_width'] ? "style='width:".$options['inner_width'].";'" : '').$max_length." name='$input_name' id='".$options['input_id']."_contact' value='".$input_value['1']."'".($options['placeholder'] ? " placeholder='".$options['placeholder']."' " : '')."".($options['autocomplete_off'] ? " autocomplete='off'" : '')." ".($options['deactivate'] ? 'readonly' : '').">";

    if ($options['append_button'] && $options['append_type'] && $options['append_form_value'] && $options['append_class'] && $options['append_value']) {

        $html .= "<span class='input-group-btn'>\n";

        $html .= "<button id='".$options['append_button_id']."' name='".$options['append_button_name']."' type='".$options['append_type']."' value='".$options['append_form_value']."' class='btn ".$options['append_size']." ".$options['append_class']."'>".$options['append_value']."</button>\n";

        $html .= "</span>\n";

    } else if ($options['append_value']) {

        $html .= "<span class='input-group-addon input-group-append' id='".$options['append_id']."'><span class='input-group-text'>".$options['append_value']."</span></span>\n";

    }

    $html .= ($options['feedback_icon']) ? "<div class='form-control-feedback' style='top:0;'><i class='".$options['icon']."'></i></div>" : '';

    $html .= $options['stacked'];

    $html .= ($options['append_button'] || $options['append_value']) ? "</div>" : "";

    $html .= $options['append_html'];

    $html .= ($options['inline'] && $label) ? "</div>" : "";

    $html .= ($options['ext_tip'] ? "<br/><span class='tip'><i>".$options['ext_tip']."</i></span>" : "");

    $html .= \defender::inputHasError($input_name) ? "<div class='input-error".((!$options['inline'] || $options['append_button'] || $options['append_value']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : "";

    $html .= "</div>";

    $html .= "</div>";

    \defender::add_field_session([
        'input_name'     => clean_input_name($input_name),
        'title'          => $title,
        'id'             => $options['input_id'],
        'type'           => 'contact',
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'regex'          => $options['regex'],
        'callback_check' => $options['callback_check'],
        'delimiter'      => $options['delimiter'],
        'min_length'     => $options['min_length'],
        'max_length'     => $options['max_length']
    ]);

    // This should affect all number inputs by type, not by ID
    if (!defined('TEL_ONLY_JS')) {
        define('TEL_ONLY_JS', TRUE);
        // Add plugin codes
        add_to_jquery("
        // Restricts input for each element in the set of matched elements to the given inputFilter.
        (function($) {
          $.fn.inputFilter = function(inputFilter) {
            return this.on(\"input keydown keyup mousedown mouseup select contextmenu drop\", function() {
              if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
              } else if (this.hasOwnProperty(\"oldValue\")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
              } else {
                this.value = \"\";
              }
            });
          };
        }(jQuery));
       ");
    }
    add_to_jquery("
    $('#".$options['input_id']."_contact').inputFilter(function(value) { return /^-?\d*$/.test(value); });
    ");

    // Live Regex Error Check
    if ($options['regex'] && $options['regex_error_text']) {
        add_to_jquery("
        $('#".$options['input_id']."').blur(function(ev) {
            var Inner_Object = $(this).parent('div').find('.label-danger');
            var Outer_Object = $(this).parent('div').find('.input-error');
            if (!$(this).val().match(/".$options['regex']."/g) && $(this).val()) {
                var ErrorText = '".$options['regex_error_text']."';
                var ErrorDOM = '<div class=\'input-error spacer-xs\'><div class=\'label label-danger p-5\'>'+ ErrorText +'</div></div>';
                if (Inner_Object.length > 0) {
                    object.html(ErrorText);
                } else {
                    $(this).after(function() {
                        return ErrorDOM;
                    });
                }
            } else {
               Outer_Object.remove();
            }
        });
        ");
    }

    if ($options['autocomplete_off']) {
        // Delay by 20ms and reset values.
        add_to_jquery("
            $('#".$options['input_id']."').val(' ');
            setTimeout( function(){ $('#".$options['input_id']."').val(''); }, 20);
        ");
    }

    return $html;
}

/**
 * @param null $country_code
 *
 * @return array|mixed|null
 */
function calling_codes($country_code = NULL) {
    $countries = [];
    static $calling_codes = [];
    require_once INCLUDES."geomap/callingcodes.inc.php";
    if (!empty($countries) && empty($calling_codes)) {
        foreach ($countries as $country) {
            // there is an array for these areas replicated.
            $calling_codes[$country["code"]."*".$country["prefix"]] = $country["name"]." (".$country["prefix"].")";
        }
    }

    return $country_code === NULL ? $calling_codes : (isset($calling_codes[$country_code]) ? $calling_codes[$country_code] : NULL);
}
