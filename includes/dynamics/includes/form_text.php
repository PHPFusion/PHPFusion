<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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
 * Generates a text input.
 *
 * @param string $input_name  Name of the input, by default it's also used as the ID for the input.
 * @param string $label       Input label.
 * @param bool   $input_value The value to be displayed.
 * @param array  $options
 *
 * @return string
 */
function form_text($input_name, $label = "", $input_value = "", array $options = []) {

    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $input_id = trim(str_replace("[", "-", $input_name), "]");

    $input_value = clean_input_value($input_value);

    $default_options = [
        'type'               => 'text', // Possible value: text, number, price, password, email, url, color, date, datetime, datetime-local, month, range, search, tel, time, week, ip.
        'required'           => FALSE, // Whether this field is required during form submission.
        'label_icon'         => '',
        'feedback_icon'      => '',
        'safemode'           => FALSE, // Extra security settings such as strict type GD2 checks, and other validation during upload.
        'regex'              => '',
        'regex_error_text'   => '',
        'callback_check'     => FALSE,
        'input_id'           => $input_id,
        'placeholder'        => '', // A placeholder for the field.
        'deactivate'         => FALSE, // Disable the input and set it as readonly.
        'width'              => '', // Accepts px or % values.
        'inner_width'        => '', // Accepts px or % values.
        'class'              => '', // The input container wrapper class.
        'inner_class'        => '', // The input class.
        'inline'             => FALSE,
        'min_length'         => 1,
        'max_length'         => 200,
        'number_min'         => 0,
        'number_max'         => 0,
        'number_step'        => 1,
        'icon'               => '',
        'autocomplete_off'   => FALSE,
        'tip'                => '', // Displays a tip by the label.
        'ext_tip'            => '', // Displays a tip at the bottom of the input.
        'append_button'      => '',
        'append_value'       => '',
        'append_form_value'  => '',
        'append_size'        => '',
        'append_class'       => 'btn-default',
        'append_type'        => 'submit',
        'prepend_id'         => "p-".$input_id."-prepend",
        'append_id'          => "p-".$input_id."-append",
        'prepend_button'     => '',
        'prepend_value'      => '',
        'prepend_form_value' => '',
        'prepend_size'       => '',
        'prepend_class'      => 'btn-default',
        'prepend_type'       => 'submit',
        'error_text'         => '',
        'delimiter'          => ',',
        'stacked'            => '',
        'group_size'         => '', // Possible value: sm, md, lg
        'password_strength'  => FALSE,
        'data'               => [],
        'append_html'        => '',
        'censor_words'       => TRUE,
        'password_toggle'    => TRUE,
        'descript'           => TRUE,
        'mask'               => '', // http://igorescobar.github.io/jQuery-Mask-Plugin/docs.html#basic-usage
        'mask_options'       => []
    ];

    $options += $default_options;

    $valid_types = ['text', 'number', 'price', 'password', 'email', 'url', 'color', 'date', 'datetime', 'datetime-local', 'month', 'range', 'search', 'tel', 'time', 'week', 'ip'];

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
    $options['error_text'] = empty($options['error_text']) ? $locale['error_input_default'] : $options['error_text'];

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
    if (\Defender::inputHasError($input_name)) {
        $error_class = " has-error";
        if (!empty($options['error_text'])) {
            $new_error_text = \Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }

            addnotice("danger", $options['error_text']);
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
        case 'price':
            $input_type = "text";
            $options['mask'] = '0,000,000,000,000.00';
            $options['mask_options']['reverse'] = 'true';
            break;
        case 'ip':
            $input_type = 'text';
            $options['mask'] = '0ZZ.0ZZ.0ZZ.0ZZ';
            $options['mask_options']['translation'] = '{\'Z\': {pattern: /[0-9]/, optional: true}}';
            break;
        case "password":
            $input_type = "password";
            if ($options['password_toggle'] == TRUE) {
                if (!defined('PWTOGGLE')) {
                    define('PWTOGGLE', TRUE);
                    add_to_footer("<script>function togglePasswordInput(button_id, field_id) {var button=$('#'+button_id);var input=$('#'+field_id);if(input.attr('type')=='password'){input.attr('type','text');button.text('".$locale['hide']."');}else{input.attr('type','password');button.text('".$locale['show']."');}}</script>");
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

    if (!empty($options['mask'])) {
        fusion_load_script(INCLUDES.'jquery/jquery-mask.min.js');
        $mask_opts = [];
        $opts = '';

        if (!empty($options['mask_options'])) {
            foreach ($options['mask_options'] as $name => $value) {
                $mask_opts[] = $name.':'.$value;
            }
            $opts = ', {'.implode(',', $mask_opts).(!empty($mask_opts) ? ',' : '').'}';
        }

        add_to_jquery("$('#".$options['input_id']."').mask('".$options['mask']."' ".$opts.");");
    }

    // Fixes HTML DOM type number that does not respect max_length prop.
    $max_length = '';
    if ($options['max_length'] && isnum($options['max_length'])) {
        $max_length = ' maxlength="'.$options['max_length'].'"';
        if ($input_type == 'number') {
            $max_length .= ' oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"';
        }
    }

    if ($options['password_strength'] === TRUE) {

        // locale file
        if (file_exists(LOCALE.LOCALESET."includes/dynamics/assets/password/lang/".$locale['password_strength'].".js")) {
            $path = LOCALE.LOCALESET."includes/dynamics/assets/password/lang/".$locale['password_strength'].".js";
        } else {
            $path = LOCALE.LOCALESET."includes/dynamics/assets/password/lang/en.js";
        }

        fusion_load_script($path);
        fusion_load_script(DYNAMICS.'assets/password/i18next.js');
        fusion_load_script(DYNAMICS.'assets/password/pwstrength-bootstrap.min.js');

        add_to_jquery("
            i18next.init({
                lng: '".$locale['password_strength']."',resources: {".$locale['password_strength'].": {translation: pwstrength_locale}}
            }, function () {
                var options = {};
                options.ui = {
                    ".(!defined('BOOTSTRAP4') ? 'bootstrap3: true,' : '')."
                    container: '#".$options['input_id']."-field',
                    showVerdictsInsideProgressBar: true,
                    viewports: {
                        progress: '.pwstrength_viewport_progress'
                    }
                };

                $('#".$options['input_id']."').pwstrength(options);
            });
        ");
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').(!empty($error_class) ? $error_class : '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'".($options['width'] && !$label ? " style='width: ".$options['width']."'" : '').">";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$options['label_icon'].$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>" : '';
    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>" : "";

    $html .= ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? "<div class='input-group".($options['group_size'] ? ' input-group-'.$options['group_size'] : '')."' ".($options['width'] ? "style='width: ".$options['width']."'" : '').">" : "";

    if ($options['prepend_button'] && $options['prepend_type'] && $options['prepend_form_value'] && $options['prepend_class'] && $options['prepend_value']) {
        $html .= "<span class='input-group-btn input-group-prepend'>";
        $html .= "<button id='".$options['prepend_button_id']."' name='".$options['prepend_button_name']."' type='".$options['prepend_type']."' value='".$options['prepend_form_value']."' class='btn ".$options['prepend_size']." ".$options['prepend_class']."'>".$options['prepend_value']."</button>";
        $html .= "</span>\n";
    } else if ($options['prepend_value']) {
        $html .= "<span class='input-group-addon input-group-prepend' id='".$options['prepend_id']."'><span class='input-group-text'>".$options['prepend_value']."</span></span>";
    }

    $html .= "<input type='".$input_type."' data-type='".$input_type."' ".(!empty($options_data) ? implode(' ', $options_data) : '')." ".$min.$max.$step."class='form-control textbox ".($options['inner_class'] ? " ".$options['inner_class']." " : '')."' ".($options['inner_width'] ? "style='width:".$options['inner_width'].";'" : '').$max_length." name='".$input_name."' id='".$options['input_id']."' value='".$input_value."'".($options['placeholder'] ? " placeholder='".$options['placeholder']."' " : '')."".($options['autocomplete_off'] ? " autocomplete='off'" : '')." ".($options['deactivate'] ? 'readonly' : '').">";

    if ($options['append_button'] && $options['append_type'] && $options['append_form_value'] && $options['append_class'] && $options['append_value']) {
        $html .= "<span class='input-group-btn input-group-append'>";
        $html .= "<button id='".$options['append_button_id']."' name='".$options['append_button_name']."' type='".$options['append_type']."' value='".$options['append_form_value']."' class='btn ".$options['append_size']." ".$options['append_class']."'>".$options['append_value']."</button>";
        $html .= "</span>\n";

    } else if ($options['append_value']) {
        $html .= "<span class='input-group-addon input-group-append' id='".$options['append_id']."'><span class='input-group-text'>".$options['append_value']."</span></span>";
    }

    $html .= ($options['feedback_icon'] ? "<div class='form-control-feedback' style='top:0;'><i class='".$options['icon']."'></i></div>" : '');

    $html .= $options['stacked'];

    $html .= ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? "</div>" : "";

    $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";

    $html .= (\Defender::inputHasError($input_name) ? "<div class='input-error".((!$options['inline'] || $options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : "");

    $html .= $options['append_html'];

    $html .= ($options['password_strength'] == TRUE ? '<div class="m-t-5 pwstrength_viewport_progress"></div>' : "");

    $html .= (($options['inline'] && $label) ? "</div>" : "");

    $html .= "</div>";

    // Add input settings in the SESSION
    \Defender::add_field_session([
        'input_name'     => clean_input_name($input_name),
        'title'          => clean_input_name($title),
        'id'             => $options['input_id'],
        'type'           => $input_type,
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'regex'          => $options['regex'],
        'callback_check' => $options['callback_check'],
        'delimiter'      => $options['delimiter'],
        'min_length'     => $options['min_length'],
        'max_length'     => $options['max_length'],
        'censor_words'   => $options['censor_words'],
        'descript'       => $options['descript']
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
