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
 * @param string $input_name Name of the input, by default it's also used as the ID for the input.
 * @param string $label Input label.
 * @param bool $input_value The value to be displayed.
 * @param array $options
 *
 * Jquery mask plugin (https://igorescobar.github.io/jQuery-Mask-Plugin/docs.html#basic-usage)
 * Password strength meter (https://github.com/ablanco/jquery.pwstrength.bootstrap) - MIT License
 *
 * @return string
 */
function form_text( $input_name, $label = "", $input_value = "", array $options = [] ) {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    $title = $label ? stripinput( $label ) : ucfirst( strtolower( str_replace( "_", " ", $input_name ) ) );

    $input_id = trim( str_replace( "[", "-", $input_name ), "]" );

    $input_value = clean_input_value( $input_value );

    $options += [
        'input_name'         => clean_input_name( $input_name ),
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
        'prepend_id'         => "p-" . $input_id . "-prepend",
        'append_id'          => "p-" . $input_id . "-append",
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
        'prepend_html'       => '',
        'append_html'        => '',
        'censor_words'       => TRUE,
        'password_toggle'    => TRUE,
        'descript'           => TRUE,
        'mask'               => '', // http://igorescobar.github.io/jQuery-Mask-Plugin/docs.html#basic-usage
        'mask_options'       => [],
        'add_error_notice'   => FALSE,
        'error_text_notice'  => '',
        'floating_label'     => FALSE,
    ];

    $valid_types = ['text', 'number', 'price', 'password', 'email', 'url', 'color', 'date', 'datetime', 'datetime-local', 'month', 'range', 'search', 'tel', 'time', 'week', 'ip'];

    $options['template_type'] = 'text'; // template identifier
    $options['type'] = in_array( $options['type'], $valid_types ) ? $options['type'] : 'text';

    $options += [
        'append_button_name'  => !empty( $options['append_button_name'] ) ? $options['append_button_name'] : "p-submit-" . $options['input_id'],
        'prepend_button_name' => !empty( $options['append_button_name'] ) ? $options['append_button_name'] : "p-submit-" . $options['input_id'],
        'append_button_id'    => !empty( $options['append_button_id'] ) ? $options['append_button_id'] : $options['input_id'] . '-append-btn',
        'prepend_button_id'   => !empty( $options['prepend_button_id'] ) ? $options['prepend_button_id'] : $options['input_id'] . '-prepend-btn',
    ];

    if (!empty( $options['data'] )) {
        array_walk( $options['data'], function ( $a, $b ) use ( &$options_data ) {
            $options_data[] = "data-$b='$a'";
        }, $options_data );
    }

    list( $options['error_class'], $options['error_text'] ) = form_errors( $options );

    $min = '';
    $max = '';
    $step = '';

    //Most common form control, text-based input fields. Includes support for all HTML5 types: text, password, datetime, datetime-local, date, month, time, week, number, email, url, search, tel, and color.
    switch ($options['type']) {
        case "number":
            $input_type = "number";
            $min = ((!empty( $options['number_min'] ) || $options['number_min'] === "0") && isnum( $options['number_min'] ) ? "min='" . $options['number_min'] . "' " : '');
            $max = ((!empty( $options['number_max'] ) || $options['number_max'] === "0") && isnum( $options['number_max'] ) ? "max='" . $options['number_max'] . "' " : '');
            // $step = "step='".str_replace(",", ".", $options['number_step'])."' ";
            $step = 'step="any" ';
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
        case 'email':
            $input_type = 'email';
            break;
        case 'password':
            $input_type = 'password';
            if ($options['password_toggle'] == TRUE) {

                if (!defined( 'PWTOGGLE' )) {
                    define( 'PWTOGGLE', TRUE );
                    add_to_footer( "<script>function togglePasswordInput(button_id, field_id) {var button=$('#'+button_id);var input=$('#'+field_id);if(input.attr('type')=='password'){input.attr('type','text');button.text('" . $locale['hide'] . "');}else{input.attr('type','password');button.text('" . $locale['show'] . "');}}</script>" );
                }

                $options['append_button'] = TRUE;
                $options['append_type'] = "button";
                $options['append_form_value'] = 'show';
                $options['append_class'] = 'btn-default password-toggle';
                $options['append_value'] = $locale['show'];
                $options['append_button_name'] = $options['input_id'] . '_pwdToggle';
                $options['append_button_id'] = $options['input_id'] . '_pwdToggle';

                add_to_jquery( "
                    $('#" . $options['input_id'] . "_pwdToggle').on('click', function(e) {
                        togglePasswordInput('" . $options['input_id'] . "_pwdToggle', '" . $options['input_id'] . "');
                    });
                " );
            }

            break;
        default:
            $input_type = "text";
    }

    if (!empty( $options['mask'] )) {
        fusion_load_script( INCLUDES . 'jquery/jquery-mask.min.js' );
        $mask_opts = [];
        $opts = '';
        if (!empty( $options['mask_options'] )) {
            foreach ($options['mask_options'] as $name => $value) {
                $mask_opts[] = $name . ':' . $value;
            }
            $opts = ', {' . implode( ',', $mask_opts ) . (!empty( $mask_opts ) ? ',' : '') . '}';
        }

        add_to_jquery( "$('#" . $options['input_id'] . "').mask('" . $options['mask'] . "' " . $opts . ");" );
    }

    // Fixes HTML DOM type number that does not respect max_length prop.
    $max_length = '';
    if ($options['max_length'] && isnum( $options['max_length'] )) {
        $max_length = ' maxlength="' . $options['max_length'] . '"';
        if ($input_type == 'number') {
            $max_length .= ' oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"';
        }
    }

    if ($options['password_strength'] === TRUE) {

        // locale file
        if (!defined( 'PASSWORD_METER' )) {
            define( "PASSWORD_METER", TRUE );

            if (is_file( LOCALE . LOCALESET . "includes/dynamics/assets/password/lang/" . $locale['password_strength'] . ".js" )) {
                $path = LOCALE . LOCALESET . "includes/dynamics/assets/password/lang/" . $locale['password_strength'] . ".js";
            } else {
                $path = LOCALE . LOCALESET . "includes/dynamics/assets/password/lang/en.js";
            }
            add_to_footer( '<script src="' . $path . '" defer></script>' );
            add_to_footer( '<script src="' . DYNAMICS . 'assets/password/i18next.js" defer></script>' );
            add_to_footer( '<script src="' . DYNAMICS . 'assets/password/pwstrength-bootstrap.min.js" defer></script>' );
        }

        if (defined('BOOTSTRAP') && isnum(BOOTSTRAP) && BOOTSTRAP < 5) {
            $ui_rules = 'bootstrap'.BOOTSTRAP.' : true,';
        }


        add_to_jquery( "
            i18next.init({            
                lng: '" . $locale['password_strength'] . "',resources: {" . $locale['password_strength'] . ": {translation: pwstrength_locale}}
                
            }, function () {
            
                var options = {};
                
                options.common = {
                    minChar: '".$settings['password_length']."',                    
                };
            
                options.ui = {
                   ".($ui_rules ?? '')."                                        
                    container: '#" . $options['input_id'] . "-field',
                    showVerdictsInsideProgressBar: true,
                    viewports: {
                      progress: '.pwstrength_viewport_progress'
                    }
                };
                options.rules = {
                    activated: {                    
                        wordTwoCharacterClasses: true,
                        wordRepetitions: true
                    }
                };

                $('#" . $options['input_id'] . "').pwstrength(options);
            });
        " );
    }

    $options['input_type'] = $input_type;
    $options['options_data'] = !empty( $options_data ) ? implode( '', $options_data ) : '';
    $options['min'] = $min;
    $options['max'] = $max;
    $options['step'] = $step;
    $options['input_error'] = \Defender::inputHasError( $input_name );

    // Add input settings in the SESSION
    set_field_config( [
        'input_name'     => clean_input_name( $input_name ),
        'title'          => clean_input_name( $title ),
        'id'             => $options['input_id'],
        'type'           => $options['input_type'],
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'regex'          => $options['regex'],
        'callback_check' => $options['callback_check'],
        'delimiter'      => $options['delimiter'],
        'min_length'     => $options['min_length'],
        'max_length'     => $options['max_length'],
        'censor_words'   => $options['censor_words'],
        'descript'       => $options['descript'],
        'error_text'     => $options['error_text']
    ] );

    // This should affect all number inputs by type, not by ID
    if ($options['type'] == 'number' && !defined( 'NUMBERS_ONLY_JS' )) {
        define( 'NUMBERS_ONLY_JS', TRUE );
        add_to_jquery( "$('input[data-type=\"number\"]').keypress(function(e) {
        var key_codes = [96, 97, 98, 99, 100, 101, 102, 103, 44, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 8];
        if (!($.inArray(e.which, key_codes) >= 0)) { e.preventDefault(); }
        });\n" );
    }

    // Live Regex Error Check
    if ($options['regex'] && $options['regex_error_text']) {
        add_to_jquery( "
        $('#" . $options['input_id'] . "').blur(function(ev) {
            var Inner_Object = $(this).parent('div').find('.label-danger');
            var Outer_Object = $(this).parent('div').find('.input-error');
            if (!$(this).val().match(/" . $options['regex'] . "/g) && $(this).val()) {
                var ErrorText = '" . $options['regex_error_text'] . "';
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
        " );
    }

    if ($options['autocomplete_off']) {
        // Delay by 20ms and reset values.
        add_to_jquery( "
            $('#" . $options['input_id'] . "').val(' ');
            setTimeout( function(){ $('#" . $options['input_id'] . "').val(''); }, 20);
        " );
    }

    ksort( $options );

    return fusion_get_template( 'form_inputs', [
        "input_name"    => $input_name,
        "input_label"   => $label,
        "input_value"   => $options['priority_value'] ?? $input_value,
        "input_options" => $options,
    ] );

}
