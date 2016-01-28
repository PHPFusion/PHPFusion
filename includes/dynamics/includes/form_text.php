<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_text.php
| Author: Frederick MC Chan (Hien)
| Co-Author: Dan C. (JoiNNN)
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
 * Generates a text input
 * TODO: Document each option
 *
 * Generates the HTML for a textbox or password input
 *
 * @param string $input_name Name of the input, by
 * default it's also used as the ID for the input
 * @param string $label The label
 * @param string $input_value The value to be displayed
 * in the input, usually a value from DB prev. saved
 * @param array  $options Various options
 * @return string
 *
 * To add an inline button (set prepend or append - respectively)
 * register these options accordingly into the function
 * $options['append_button'] = true
 * $options['append_type'] = button or submit
 * $options['append_form_value'] = the value of the button
 * $options['append_class'] = your pick of .btn classes (bootstrap .btn-success, .btn-info, etc)
 * $options['append_value'] = the label
 * $options['append_button_name'] = your button name , default: p-submit-".$options['input_id']."
 *
 * To add a decorative labels (set prepend or append - respectively)
 * $options['append_value'] = "your-value" - You can also insert HTML <i class='fa fa-something'></i> for glyphs
 *
 */

function form_text($input_name, $label = "", $input_value = "", array $options = array()) {
    global $defender, $locale;

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $valid_types = array('text', 'number', 'password', 'email', 'url');

    $options += array(
        'type' => !empty($options['type']) && in_array($options['type'], $valid_types) ? $options['type'] : 'text',
        'required' => !empty($options['required']) && $options['required'] == 1 ? 1 : 0,
        'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? 1 : 0,
        'regex' => !empty($options['regex']) ? $options['regex'] : FALSE,
        'callback_check' => !empty($options['callback_check']) ? $options['callback_check'] : FALSE,
        'input_id' => !empty($options['input_id']) ? $options['input_id'] : $input_name,
        'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
        'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? 1 : 0,
        'width' => !empty($options['width']) ? $options['width'] : "",
        'class' => !empty($options['class']) ? $options['class'] : '',
        'inline' => !empty($options['inline']) ? $options['inline'] : '',
        'max_length' => !empty($options['max_length']) ? $options['max_length'] : '200',
        'icon' => !empty($options['icon']) ? $options['icon'] : '',
        'autocomplete_off' => !empty($options['autocomplete_off']) && $options['autocomplete_off'] == 1 ? 1 : 0,
        'tip' => !empty($options['tip']) ? $options['tip'] : '',
        'append_button' => !empty($options['append_button']) ? $options['append_button'] : '',
        'append_value' => !empty($options['append_value']) ? $options['append_value'] : "",
        'append_form_value' => !empty($options['append_form_value']) ? $options['append_form_value'] : '',
        'append_size' => !empty($options['append_size']) ? $options['append_size'] : '',
        'append_class' => !empty($options['append_class']) ? $options['append_class'] : 'btn-default',
        'append_type' => !empty($options['append_type']) ? $options['append_type'] : 'submit',
        'prepend_button' => !empty($options['prepend_button']) ? $options['prepend_button'] : '',
        'prepend_value' => !empty($options['prepend_value']) ? $options['prepend_value'] : "",
        'prepend_form_value' => !empty($options['prepend_form_value']) ? $options['prepend_form_value'] : '',
        'prepend_size' => !empty($options['prepend_size']) ? $options['prepend_size'] : '',
        'prepend_class' => !empty($options['prepend_class']) ? $options['prepend_class'] : 'btn-default',
        'prepend_type' => !empty($options['prepend_type']) ? $options['prepend_type'] : 'submit',
        'error_text' => $locale['error_input_default'],
        'delimiter' => ',',
        'stacked' => !empty($options['stacked']) ? $options['stacked'] : "",
    );
    // always trim id
    $options['input_id'] = trim($options['input_id'], "[]");

    $options += array(
        'append_button_name' => !empty($options['append_button_name']) ? $options['append_button_name'] : "p-submit-".$options['input_id'],
        'prepend_button_name' => !empty($options['append_button_name']) ? $options['append_button_name'] : "p-submit-".$options['input_id'],
    );


    // Error messages based on settings
    if ($options['type'] == 'password') {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_password'] : $options['error_text'];
    } elseif ($options['type'] == 'email') {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_email'] : $options['error_text'];
    } elseif ($options['type'] == 'number') {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_number'] : $options['error_text'];
    } elseif ($options['type'] == 'url') {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_url'] : $options['error_text'];
    } elseif ($options['regex']) {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_regex'] : $options['error_text'];
    } elseif ($options['safemode']) {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_safemode'] : $options['error_text'];
    } else {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_default'] : $options['error_text'];
    }

    $error_class = "";
    if ($defender->inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = $defender->getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".$error_class.$options['class']." ".($options['icon'] ? 'has-feedback' : '')."'  ".($options['width'] && !$label ? "style='width: ".$options['width']."'" : '').">\n";

    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='".$options['input_id']."'>$label ".($options['required'] ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n" : "";

    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";

    $html .= ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? "<div class='input-group'>\n" : "";

    if ($options['prepend_button'] && $options['prepend_type'] && $options['prepend_form_value'] && $options['prepend_class'] && $options['prepend_value']) {

        $html .= "<span class='input-group-btn'>\n";
        $html .= "<button id='".$options['input_id']."-prepend-btn' name='".$options['prepend_button_name']."' type='".$options['prepend_type']."' value='".$options['prepend_form_value']."' class='btn ".$options['prepend_size']." ".$options['prepend_class']."'>".$options['prepend_value']."</button>\n";
        $html .= "</span>\n";

    } elseif ($options['prepend_value']) {

        $html .= "<span class='input-group-addon' id='p-".$options['input_id']."-prepend'>".$options['prepend_value']."</span>\n";

    }

    $html .= "<input type='".($options['type'] == "password" ? "password" : "text")."' data-type='".$options['type']."' class='form-control textbox ".($options['stacked'] ? "stacked" : "")."' ".($options['width'] ? "style='width:".$options['width'].";'" : '')." ".($options['max_length'] ? "maxlength='".$options['max_length']."'" : '')." name='".$input_name."' id='".$options['input_id']."' value='".$input_value."' placeholder='".$options['placeholder']."' ".($options['autocomplete_off'] ? "autocomplete='off'" : '')." ".($options['deactivate'] ? 'readonly' : '').">";

    if ($options['append_button'] && $options['append_type'] && $options['append_form_value'] && $options['append_class'] && $options['append_value']) {

        $html .= "<span class='input-group-btn'>\n";
        $html .= "<button id='".$options['input_id']."-append-btn' name='".$options['append_button_name']."' type='".$options['append_type']."' value='".$options['append_form_value']."' class='btn ".$options['append_size']." ".$options['append_class']."'>".$options['append_value']."</button>\n";
        $html .= "</span>\n";

    } elseif ($options['append_value']) {

        $html .= "<span class='input-group-addon' id='p-".$options['input_id']."-append'>".$options['append_value']."</span>\n";

    }

    $html .= ($options['icon']) ? "<div class='form-control-feedback' style='top:0;'><i class='glyphicon ".$options['icon']."'></i></div>\n" : "";

    $html .= $options['stacked'];

    $html .= ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? "</div>\n" : "";

    $html .= $defender->inputHasError($input_name) ? "<div class='input-error".((!$options['inline'] || $options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : "";

    $html .= ($options['inline'] && $label) ? "</div>\n" : "";

    $html .= "</div>\n";

    // Add input settings in the SESSION
    $defender->add_field_session(array(
                                     'input_name' => $input_name,
                                     'title' => trim($title, '[]'),
                                     'id' => $options['input_id'],
                                     'type' => $options['type'],
                                     'required' => $options['required'],
                                     'safemode' => $options['safemode'],
                                     'regex' => $options['regex'],
                                     'callback_check' => $options['callback_check'],
                                     'delimiter' => $options['delimiter'],
                                 ));

    // This should affect all number inputs by type, not by ID
    if ($options['type'] == 'number' && !defined('NUMBERS_ONLY_JS')) {
        define('NUMBERS_ONLY_JS', TRUE);
        add_to_jquery("$('input[data-type=\"number\"]').keypress(function(e) {
		var key_codes = [96, 97, 98, 99, 100, 101, 102, 103, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 8];
		if (!($.inArray(e.which, key_codes) >= 0)) { e.preventDefault(); }
		});\n");
    }

    return $html;
}