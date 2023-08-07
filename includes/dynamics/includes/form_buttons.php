<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_buttons.php
| Author: Frederick MC Chan (Chan)
| Co-Author: Tyler Hurlbut
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
function form_button( $input_name, $label, $input_value, array $options = [] ) {
    $html = "";

    $input_value = clean_input_value( $input_value );

    $options += [
        'input_id'    => clean_input_id( $input_name ),
        'input_value' => clean_input_name( $input_name ),
        'class'       => 'btn-default',
        'icon_class'  => 'm-r-10',
        'icon'        => '',
        'deactivate'  => FALSE,
        'type'        => 'submit',
        'block'       => FALSE,
        'alt'         => $label,
        'data'        => [],
    ];

    $options['template_type'] = 'button';

    if ($options['block']) {
        $options['class'] = $options['class'] . " btn-block";
    }

    array_walk( $options['data'], function ( $a, $b ) use ( &$options_data ) {
        $options_data[] = "data-$b='$a'";
    }, $options_data );

    $options['options_data'] = $options_data;

    ksort( $options );

    return fusion_get_template( 'form_inputs', [
        "input_name"    => $input_name,
        "input_label"   => $label,
        "input_value"   => $input_value,
        "input_options" => $options,
    ] );

}

/**
 * Button Groups
 *
 * @param string $input_name
 * @param string $label
 * @param string $input_value
 * @param array $options
 *
 * @return string
 */
function form_btngroup( $input_name, $label, $input_value = '', array $options = [] ) {
    $locale = fusion_get_locale();

    $title = $label ? stripinput( $label ) : ucfirst( strtolower( str_replace( "_", " ", $input_name ) ) );

    $input_value = clean_input_value( $input_value );

    $options += [
        'options'        => [$locale['disable'], $locale['enable']],
        'input_name'     => clean_input_name( $input_name ),
        'input_id'       => clean_input_id( $input_name ),
        'btn_class'      => 'btn-default', // change from 'class' to 'btn_class'
        'type'           => 'button',
        'icon'           => '',
        'multiple'       => FALSE,
        'delimiter'      => ',',
        'deactivate'     => FALSE,
        'error_text'     => '',
        'inline'         => FALSE,
        'safemode'       => FALSE,
        'required'       => FALSE,
        'ext_tip'        => '',
        'callback_check' => '',
    ];

    $options['input_name'] = ($options['multiple']) ? str_replace( "[]", "", $input_name ) : $input_name;

    $options['template_type'] = 'button_group';

    list( $options['error_class'], $options['error_text'] ) = form_errors( $options );

    set_field_config( [
        'title'          => $title,
        'input_name'     => $options['input_name'],
        'id'             => $options['input_id'],
        'type'           => 'dropdown',
        'required'       => $options['required'],
        'callback_check' => $options['callback_check'],
        'safemode'       => $options['safemode'],
        'error_text'     => $options['error_text'],
        'delimiter'      => $options['delimiter'],
    ] );

    add_to_jquery( "    
    $('#" . $options['input_id'] . " button').on('click', function(e){
        $('#" . $options['input_id'] . " button').removeClass('active');
        $(this).toggleClass('active');
        value = $(this).data('value');
        $('#" . $options['input_id'] . "-text').val(value);
    });
    " );

    ksort( $options );

    return fusion_get_template( 'form_inputs', [
        "input_name"    => $input_name,
        "input_label"   => $label,
        "input_value"   => $input_value,
        "input_options" => $options,
    ] );


}
