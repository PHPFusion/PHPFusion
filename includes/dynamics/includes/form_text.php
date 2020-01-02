<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_text.php
| Author: Frederick MC Chan (Chan)
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
 *                            default it's also used as the ID for the input
 * @param string $label The label
 * @param string $input_value The value to be displayed
 *                            in the input, usually a value from DB prev. saved
 * @param array  $options Various options
 *
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
 * $options['append_button_id'] = your button name , default: ".$input_name."-append-btn
 *
 * To add a decorative labels (set prepend or append - respectively)
 * $options['append_value'] = "your-value" - You can also insert HTML <i class='fa fa-something'></i> for glyphs
 *
 */

function form_text($input_name, $label = "", $input_value = "", array $options = []) {

    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $id = trim($input_name, "[]");

    $default_options = [
        'type'               => 'text',
        'required'           => FALSE,
        'label_icon'         => '',
        'feedback_icon'      => '',
        'safemode'           => FALSE,
        'regex'              => '',
        'regex_error_text'   => '',
        'callback_check'     => FALSE,
        'input_id'           => $id,
        'placeholder'        => '',
        'deactivate'         => FALSE,
        'width'              => '',
        'inner_width'        => '',
        'class'              => '',
        'inner_class'        => '',
        'inline'             => FALSE,
        'min_length'         => 1,
        'max_length'         => 200,
        'number_min'         => 0,
        'number_max'         => 0,
        'number_step'        => 1,
        'icon'               => '',
        'autocomplete_off'   => FALSE,
        'tip'                => '',
        'ext_tip'            => '',
        'append_button'      => '',
        'append_value'       => '',
        'append_form_value'  => '',
        'append_size'        => '',
        'append_class'       => 'btn-default',
        'append_type'        => 'submit',
        'prepend_id'         => "p-".$id."-prepend",
        'append_id'          => "p-".$id."-append",
        'prepend_button'     => '',
        'prepend_value'      => '',
        'prepend_form_value' => '',
        'prepend_size'       => '',
        'prepend_class'      => 'btn-default',
        'prepend_type'       => 'submit',
        'error_text'         => '',
        'delimiter'          => ',',
        'stacked'            => '',
        'group_size'         => '', // http://getbootstrap.com/components/#input-groups-sizing - sm, md, lg
        'password_strength'  => FALSE,
        'data'               => [],
        'append_html'        => '',
        'censor_words'       => TRUE,
        'password_toggle'    => TRUE
    ];

    $options += $default_options;

    $valid_types = [
        'text', 'number', 'password', 'email', 'url', 'color', 'date', 'datetime', 'datetime-local', 'month', 'range', 'search', 'tel', 'time', 'week'
    ];

    $options['type'] = in_array($options['type'], $valid_types) ? $options['type'] : 'text';

    $options += [
        'append_button_name'  => !empty($options['append_button_name']) ? $options['append_button_name'] : "p-submit-".$options['input_id'],
        'prepend_button_name' => !empty($options['append_button_name']) ? $options['append_button_name'] : "p-submit-".$options['input_id'],
        'append_button_id'    => !empty($options['append_button_id']) ? $options['append_button_id'] : $options['input_id'].'-append-btn',
        'prepend_button_id'   => !empty($options['prepend_button_id']) ? $options['prepend_button_id'] : $options['input_id'].'-prepend-btn',
    ];

    if (!empty($options['data'])) {
        array_walk($options['data'], function ($a, $b) use (&$options_data) {
            $options_data[] = "data-$b='$a'";
        }, $options_data);
    }

    // Error messages based on settings
    if ($options['type'] == 'password') {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_password'] : $options['error_text'];
    } else if ($options['type'] == 'email') {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_email'] : $options['error_text'];
    } else if ($options['type'] == 'number') {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_number'] : $options['error_text'];
    } else if ($options['type'] == 'url') {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_url'] : $options['error_text'];
    } else if ($options['regex']) {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_regex'] : $options['error_text'];
    } else if ($options['safemode']) {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_safemode'] : $options['error_text'];
    } else {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_default'] : $options['error_text'];
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

    $min = '';
    $max = '';
    $step = '';
    switch ($options['type']) {
        case "number":
            $input_type = "number";
            $min = ((!empty($options['number_min']) || $options['number_min'] === "0") && isnum($options['number_min']) ? "min='".$options['number_min']."' " : '');
            $max = ((!empty($options['number_max']) || $options['number_max'] === "0") && isnum($options['number_max']) ? "max='".$options['number_max']."' " : '');
            // $step = "step='".str_replace(",", ".", $options['number_step'])."' ";
            $step = "step='any' ";
            break;
        case "text":
            $input_type = "text";
            break;
        case "password":
            $input_type = "password";

            $pwd_locale = fusion_get_locale("password_strength");
            $path = DYNAMICS."assets/password/lang/$pwd_locale.js";
            if (file_exists($path)) {
                $path = DYNAMICS."assets/password/lang/$pwd_locale.js";
            } else {
                $path = DYNAMICS."assets/password/lang/en.js";
            }
            PHPFusion\OutputHandler::addToFooter("<script type='text/javascript' src='$path'></script>");

            // Incompatible with password meter strength due to jquery appending layout.
            // @todo: Fix pwstrength.js
            if ($options['password_toggle'] == TRUE && $options['password_strength'] == FALSE) {
                static $password_toggle = '';
                if (!$password_toggle) {
                    $password_toggle = TRUE;
                    PHPFusion\OutputHandler::addToFooter("<script type='text/javascript' src='".DYNAMICS."assets/password/pwtoggle.min.js'></script>");
                }

                $options['append_button'] = TRUE;
                $options['append_type'] = "button";
                $options['append_form_value'] = 'show';
                $options['append_class'] = 'btn-default';
                $options['append_value'] = $locale['show'];
                $options['append_button_name'] = $options['input_id'].'_pwdToggle';
                $options['append_button_id'] = $options['input_id'].'_pwdToggle';
                add_to_jquery("
                    $('#".$options['input_id']."_pwdToggle').bind('click', function(e) {
                        togglePasswordInput('".$options['input_id']."_pwdToggle', '".$options['input_id']."');
                    });
                    ");
            }
            break;
        default:
            $input_type = "text";
    }

    // Fixes HTML DOM type number that does not respect max_length prop.
    $max_length = '';
    if ($options['max_length'] && isnum($options['max_length'])) {
        $max_length = ' maxlength="'.$options['max_length'].'"';
        if ($input_type == 'number') {
            $max_length .= ' oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"';
        }
    }

    if ($options['password_strength'] == TRUE) {
        PHPFusion\OutputHandler::addToFooter("<script type='text/javascript' src='".DYNAMICS."assets/password/pwstrength.js'></script>");
        PHPFusion\OutputHandler::addToHead('<script type="text/javascript">'.jsminify('
            jQuery(document).ready(function() {
                var options = {};
                options.ui = {
                    showVerdictsInsideProgressBar: true,
                    viewports: {
                        progress: ".pwstrength_viewport_progress"
                    }
                };
                $("#'.$options['input_id'].'").pwstrength(options);
            });
        ').'</script>');
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group".($options['inline'] ? ' overflow-hide' : '').($error_class ? $error_class : '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'".($options['width'] && !$label ? " style='width: ".$options['width']."'" : '').">\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$options['label_icon'].$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n" : '';
    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n" : "";

    $html .= ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? "<div class='input-group".($options['group_size'] ? ' input-group-'.$options['group_size'] : '')."' ".($options['width'] ? "style='width: ".$options['width']."'" : '').">\n" : "";

    if ($options['prepend_button'] && $options['prepend_type'] && $options['prepend_form_value'] && $options['prepend_class'] && $options['prepend_value']) {
        $html .= "<span class='input-group-btn'>\n";
        $html .= "<button id='".$options['prepend_button_id']."' name='".$options['prepend_button_name']."' type='".$options['prepend_type']."' value='".$options['prepend_form_value']."' class='btn ".$options['prepend_size']." ".$options['prepend_class']."'>".$options['prepend_value']."</button>\n";
        $html .= "</span>\n";
    } else if ($options['prepend_value']) {
        $html .= "<span class='input-group-addon' id='".$options['prepend_id']."'>".$options['prepend_value']."</span>\n";
    }

    $html .= "<input type='".$input_type."' data-type='".$input_type."' ".(!empty($options_data) ? implode(' ', $options_data) : '')." ".$min.$max.$step."class='form-control textbox ".($options['inner_class'] ? " ".$options['inner_class']." " : '')."' ".($options['inner_width'] ? "style='width:".$options['inner_width'].";'" : '').$max_length." name='".$input_name."' id='".$options['input_id']."' value='".$input_value."'".($options['placeholder'] ? " placeholder='".$options['placeholder']."' " : '')."".($options['autocomplete_off'] ? " autocomplete='off'" : '')." ".($options['deactivate'] ? 'readonly' : '')." ".($options['required'] ? 'required="required"' : '').">";

    $html .= $options['password_strength'] == TRUE ? '<div class="pwstrength_viewport_progress"></div>' : '';

    if ($options['append_button'] && $options['append_type'] && $options['append_form_value'] && $options['append_class'] && $options['append_value']) {

        $html .= "<span class='input-group-btn'>\n";
        $html .= "<button id='".$options['append_button_id']."' name='".$options['append_button_name']."' type='".$options['append_type']."' value='".$options['append_form_value']."' class='btn ".$options['append_size']." ".$options['append_class']."'>".$options['append_value']."</button>\n";
        $html .= "</span>\n";

    } else if ($options['append_value']) {

        $html .= "<span class='input-group-addon' id='".$options['append_id']."'>".$options['append_value']."</span>\n";

    }

    $html .= ($options['feedback_icon']) ? "<div class='form-control-feedback' style='top:0;'><i class='".$options['icon']."'></i></div>\n" : '';

    $html .= $options['stacked'];

    $html .= ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? "</div>\n" : "";

    $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";

    $html .= \defender::inputHasError($input_name) ? "<div class='input-error".((!$options['inline'] || $options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : "";

    $html .= $options['append_html'];

    $html .= ($options['inline'] && $label) ? "</div>\n" : "";

    $html .= "</div>\n";

    // Add input settings in the SESSION
    \defender::add_field_session([
        'input_name'     => $input_name,
        'title'          => trim($title, '[]'),
        'id'             => $options['input_id'],
        'type'           => $options['type'],
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'regex'          => $options['regex'],
        'callback_check' => $options['callback_check'],
        'delimiter'      => $options['delimiter'],
        'min_length'     => $options['min_length'],
        'max_length'     => $options['max_length'],
        'censor_words'   => $options['censor_words']
    ]);

    // This should affect all number inputs by type, not by ID
    if ($options['type'] == 'number' && !defined('NUMBERS_ONLY_JS')) {
        define('NUMBERS_ONLY_JS', TRUE);
        add_to_jquery("$('input[data-type=\"number\"]').keypress(function(e) {
		var key_codes = [96, 97, 98, 99, 100, 101, 102, 103, 44, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 8];
		if (!($.inArray(e.which, key_codes) >= 0)) { e.preventDefault(); }
		});\n");
    }

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
