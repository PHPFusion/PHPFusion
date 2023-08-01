<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_hidden.php
| Author: Frederick MC Chan (Chan)
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
function form_hidden( $input_name, $label = '', $input_value = '', array $options = [] ) {

    $title = $label ? stripinput( $label ) : ucfirst( strtolower( str_replace( "_", " ", $input_name ) ) );

    $input_value = clean_input_value( $input_value );

    $options += [
        'input_id'    => clean_input_id( $input_name ),
        'input_name'  => clean_input_name( $input_name ),
        'show_title'  => FALSE,
        'width'       => '100%',
        'class'       => '',
        'inline'      => FALSE,
        'required'    => FALSE,
        'placeholder' => '',
        'deactivate'  => FALSE,
        'delimiter'   => ',',
        'error_text'  => '',
    ];

    $options['template_type'] = 'hidden';

    if (!$options['show_title']) {
        $label = '';
    }

    set_field_config( [
        'input_name' => $options['input_name'],
        'title'      => $title,
        'type'       => 'textbox',
        'id'         => $options['input_id'],
        'required'   => $options['required'],
        'safemode'   => FALSE,
        "delimiter"  => $options['delimiter'],
        'error_text' => $options['error_text']
    ] );

    ksort( $options );

    return fusion_get_template( 'form_inputs', [
        "input_name"    => $input_name,
        "input_label"   => $label,
        "input_value"   => $options['priority_value'] ?? $input_value,
        "input_options" => $options,
    ] );

}
