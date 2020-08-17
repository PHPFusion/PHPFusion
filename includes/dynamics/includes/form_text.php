<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
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

use PHPFusion\Steam;

/**
 * Generates a text input
 * Generates the HTML for a textbox or password input
 *
 * @param string $input_name  Name of the input, by default it's also used as the ID for the input
 * @param string $label       The label text
 * @param string $input_value The value to be display in the input, usually a value from DB prev. saved
 * @param array  $options     Various options
 *
 * @return mixed
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

    $id = trim(str_replace("[", '', $input_name), "]");
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $valid_types = [
        'text', 'number', 'password', 'email', 'url', 'color', 'date', 'datetime', 'datetime-local', 'month', 'range', 'search', 'tel', 'time', 'week'
    ];

    $default_options = [
        "title"              => "",
        'type'               => 'text',
        'required'           => FALSE,
        'label_icon'         => '',
        'feedback_icon'      => '',
        'safemode'           => FALSE,
        'regex'              => '',
        'regex_error_text'   => '',
        'callback_check'     => FALSE,
        'input_id'           => $input_name,
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
        'append_id'          => "p-".$id."-append",
        'append_button'      => '',
        'append_value'       => '',
        'append_form_value'  => '',
        'append_size'        => '',
        'append_class'       => 'btn-default',
        'append_type'        => 'submit',
        'prepend_id'         => "p-".$id."-prepend",
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
        'min'                => '',
        'max'                => '',
        'step'               => '',
        'error_class'        => '',
        'options_data'       => [],
        'password_toggle'    => TRUE,
        'descript'           => TRUE
    ];
    $options += $default_options;

    // Type check
    $options['type'] = in_array($options['type'], $valid_types) ? $options['type'] : 'text';

    $options['input_id'] = trim(str_replace("[", '', $options['input_id']), "]");

    if (!$options["title"]) {
        $options["title"] = $title;
    }

    $options += [
        'append_button_name'  => !empty($options['append_button_name']) ? $options['append_button_name'] : "p-submit-".$options['input_id'],
        'prepend_button_name' => !empty($options['append_button_name']) ? $options['append_button_name'] : "p-submit-".$options['input_id'],
        'append_button_id'    => !empty($options['append_button_id']) ? $options['append_button_id'] : $options['input_id'].'-append-btn',
        'prepend_button_id'   => !empty($options['prepend_button_id']) ? $options['prepend_button_id'] : $options['input_id'].'-prepend-btn',
    ];

    $options['error_text'] = $options['error_text'] ?: $locale['error_input_default'];

    if (!empty($options['data'])) {
        array_walk($options['data'], function ($a, $b) use (&$options_data) {
            $options['options_data'] = "data-$b='$a'";
        }, $options_data);
    }

    // This is bootstrap only???
    if (Defender::inputHasError($input_name)) {
        $options['error_class'] = " has-error";

        // print_p($input_name);
        // print_p($new_error_text);
        if (!empty($options['error_text'])) {
            $new_error_text = Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            //add_notice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    switch ($options['type']) {
        case 'url':
            $options['error_text'] = empty($options['error_text']) ? $locale['error_input_url'] : $options['error_text'];
            break;
        case 'email':
            $options['error_text'] = empty($options['error_text']) ? $locale['error_input_email'] : $options['error_text'];
            break;
        case "number":
            $options['error_text'] = empty($options['error_text']) ? $locale['error_input_number'] : $options['error_text'];
            $options['min'] = ((!empty($options['number_min']) || $options['number_min'] === "0") && isnum($options['number_min']) ? "min='".$options['number_min']."' " : '');
            $options['max'] = ((!empty($options['number_max']) || $options['number_max'] === "0") && isnum($options['number_max']) ? "max='".$options['number_max']."' " : '');
            // $step = "step='".str_replace(",", ".", $options['number_step'])."' ";
            $options['step'] = "step='any' ";

            if (!defined('number_field_js')) {
                define('number_field_js', TRUE);
                add_to_jquery("$('input[data-type=\"number\"]').keypress(function(e) {
                var key_codes = [96, 97, 98, 99, 100, 101, 102, 103, 44, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 8];
                if (!($.inArray(e.which, key_codes) >= 0)) { e.preventDefault(); }
                });\n");
            }

            break;
        case "password":
            $options['error_text'] = empty($options['error_text']) ? $locale['error_input_password'] : $options['error_text'];

            $pwd_locale = fusion_get_locale("password_strength");
            $password_dir = DYNAMICS."assets".DIRECTORY_SEPARATOR."password".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR;
            $path = $password_dir.'en.js';
            $pwd_locale_path = $password_dir.$pwd_locale.'.js';
            if (is_file($pwd_locale_path)) {
                $path = $pwd_locale_path;
            }
            PHPFusion\OutputHandler::addToFooter("<script type='text/javascript' src='$path'></script>");

            // Incompatible with password meter strength due to jquery appending layout.
            // @todo: Fix pwstrength.js

            if ($options['password_toggle'] == TRUE && !$options['password_strength']) {
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
            } else {
                $options['type'] = 'text';
            }
            break;
    }

    if ($options['regex']) {

        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_regex'] : $options['error_text'];

        // Live Regex Error Check
        if ($options['regex_error_text'] && $options['regex']) {
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

    } else if ($options['safemode']) {
        $options['error_text'] = empty($options['error_text']) ? $locale['error_input_safemode'] : $options['error_text'];
    }

    // Fixes HTML DOM type number that does not respect max_length prop.
    $options['max_len'] = $options['max_length'];
    if ($options['max_length'] && isnum($options['max_length'])) {
        $options['max_length'] = ' maxlength="'.$options['max_length'].'"';
        if ($options['type'] == 'number') {
            $options['max_length'] .= ' oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"';
        }
    }

    if ($options['password_strength'] == TRUE) { // does this only work for bootstrap?
        // if there are 2 password field, will append 2 times.
        static $pwStrengthFile = '';
        if (empty($pwStrengthFile)) {
            $pwStrengthFile = DYNAMICS.'assets/password/pwstrength.js';
            add_to_footer('<script src="'.$pwStrengthFile.'"></script>');
        }

        add_to_head('<script type="text/javascript">'.jsminify('
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

    // Add input settings in the SESSION
    $config = [
        'input_name'     => $input_name,
        'title'          => $options["title"],
        'id'             => $options['input_id'],
        'type'           => $options['type'],
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'regex'          => $options['regex'],
        'callback_check' => $options['callback_check'],
        'delimiter'      => $options['delimiter'],
        'min_length'     => $options['min_length'],
        'max_length'     => $options['max_len'],
        'censor_words'   => $options['censor_words'],
        'descript'       => $options['descript']
    ];

    Defender::add_field_session($config);

    if ($options['autocomplete_off']) {
        // Delay by 20ms and reset values.
        add_to_jquery("
        $('#".$options['input_id']."').val(' ');
        setTimeout( function(){ $('#".$options['input_id']."').val(''); }, 20);
        ");
    }

    return Steam::getInstance()->load('Form')->input($input_name, $label, $input_value, $options);
}
