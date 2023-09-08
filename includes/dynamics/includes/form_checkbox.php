<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_checkbox.php
| Author: Core Development Team
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
 * @param array $options
 *
 * @return string
 */
function form_checkbox( $input_name, $label = '', $input_value = '0', array $options = [] ) {

    $locale = fusion_get_locale( '', LOCALE . LOCALESET . 'global.php' );

    $title = ($label ? stripinput( $label ) : ucfirst( strtolower( str_replace( "_", " ", $input_name ) ) ));

    $input_value = clean_input_value( $input_value );

    $options += [
        'input_name'     => clean_input_name( $input_name ),
        'input_id'       => clean_input_id( $input_name ),
        'inline'         => FALSE,
        'inline_options' => FALSE,
        'required'       => FALSE,
        'deactivate'     => FALSE,
        'class'          => '',
        'type'           => 'checkbox',
        'toggle'         => FALSE,
        'options'        => [],
        'options_value'  => [],
        'delimiter'      => ',',
        'safemode'       => FALSE,
        'keyflip'        => FALSE,
        'error_text'     => $locale['error_input_checkbox'],
        'value'          => 1,
        'tip'            => '',
        'ext_tip'        => '',
        'inner_width'    => '',
        'reverse_label'  => FALSE,
        'deactivate_key' => NULL,
        'onclick'        => '',
    ];

    $options['template_type'] = 'checkbox';

    if ($options['type'] == 'toggle') {
        $options['toggle'] = TRUE;
    }

    if ($options['toggle']) {
        if (!defined( 'CHECKBOX_SWITCH_CSS' )) {
            define( 'CHECKBOX_SWITCH_CSS', TRUE );
            add_to_head( "<link rel='stylesheet' href='" . DYNAMICS . "assets/switch/switch.min.css'>" );
        }
    }

    list( $options['error_class'], $options['error_text'] ) = form_errors( $options );

    $option_value = [];

    if (!empty( $options['options'] ) && is_array( $options['options'] )) {

        $options['toggle'] = FALSE; // force toggle to be false if options existed

        if (!empty( $input_value ) && !is_array( $input_value )) {
            $option_value = array_flip( explode( $options['delimiter'], $input_value ) ); // require key to value
        }
        // The options_value to set check to the input on its value permanently
        $input_value = [];
        foreach (array_keys( $options['options'] ) as $key) {
            $input_value[$key] = 0;
            if (isset( $option_value[$key] )) {
                $input_value[$key] = (!empty( $options['options_value'][$key] ) ? $options['options_value'][$key] : 1);
            }
            // Fixes when input value is 0, and there are a key 0 in the options, this will select it.
            if (empty( $option_value ) && empty( $key )) {
                $input_value[$key] = 1;
            }
        }
        // Provided that input value keys are 0, and type is not checkbox, we default to select the first one.
        if ($options['type'] != 'checkbox' && empty( $options['options_value'] ) && empty( array_sum( $input_value ) )) {
            reset( $input_value );
            $key = key( $input_value );
            $input_value[$key] = 1;
        }

    }

    if ($options['type'] == 'toggle') {
        $options['type'] = 'checkbox';
    }

    set_field_config( [
        'input_name' => clean_input_name( $input_name ),
        'title'      => trim( $title, '[]' ),
        'id'         => $options['input_id'],
        'type'       => $options['type'],
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text'],
        'delimiter'  => $options['delimiter'],
    ] );

    ksort( $options );

    return fusion_get_template( 'form_inputs', [
        'input_name'    => $input_name,
        'input_label'   => $label,
        'input_value'   => $options['priority_value'] ?? $input_value,
        'input_options' => $options,
    ] );

}
