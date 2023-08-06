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
function form_btngroup( $input_name, $label, $input_value, array $options = [] ) {
    $locale = fusion_get_locale();

    $title = $label ? stripinput( $label ) : ucfirst( strtolower( str_replace( "_", " ", $input_name ) ) );
    $input_value = (isset( $input_value ) && (!empty( $input_value ))) ? stripinput( $input_value ) : NULL;

    $default_options = [
        'options'        => [$locale['disable'], $locale['enable']],
        'input_id'       => $input_name,
        'class'          => "btn-default",
        'type'           => 'button',
        'icon'           => "",
        'multiple'       => FALSE,
        'delimiter'      => ",",
        'deactivate'     => FALSE,
        'error_text'     => "",
        'inline'         => FALSE,
        'safemode'       => FALSE,
        'required'       => FALSE,
        'ext_tip'        => '',
        'callback_check' => '',
    ];

    $options += $default_options;

    $error_class = "";
    if (\Defender::inputHasError( $input_name )) {
        $error_class = "has-error";
        if (!empty( $options['error_text'] )) {
            $new_error_text = \Defender::getErrorText( $input_name );
            if (!empty( $new_error_text )) {
                $options['error_text'] = $new_error_text;
            }
            addnotice( 'danger', $options['error_text'] );
        }
    }

    $html = "<div id='" . $options['input_id'] . "-field' class='form-group " . ($options['inline'] && $label ? 'row ' : '') . $error_class . " clearfix'>\n";
    $html .= ($label) ? "<label class='control-label " . ($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '') . "' for='" . $options['input_id'] . "'>" . $label . ($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') . "</label>\n" : '';
    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n" : "";

    $html .= "<div class='btn-group' id='" . $options['input_id'] . "'>";

    if (!empty( $options['options'] ) && is_array( $options['options'] )) {
        $i = 1;
        $option_count = count( $options['options'] );

        foreach ($options['options'] as $arr => $v) {
            $child_class = ($option_count == $i ? ' last-child ' : '');
            $active_class = ($input_value == $arr ? ' active' : '');

            if ($options['type'] == 'submit') {
                $html .= "<button name='$arr' type='submit' data-value='$arr' value='$arr' class='btn " . $options['class'] . $child_class . $active_class . "'>$v</button>\n";
            } else {
                $html .= "<button type='button' data-value='$arr' class='btn " . $options['class'] . $child_class . $active_class . "'>$v</button>\n";
            }

            $i++;
        }
    }

    $html .= "</div>\n";
    $html .= "<input name='$input_name' type='hidden' id='" . $options['input_id'] . "-text' value='$input_value' />\n";

    $html .= $options['ext_tip'] ? "<br/>\n<div class='m-t-10 tip'><i>" . $options['ext_tip'] . "</i></div>" : "";
    $html .= \Defender::inputHasError( $input_name ) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";
    $html .= ($options['inline'] && $label) ? "</div>\n" : "";
    $html .= "</div>\n";

    $input_name = ($options['multiple']) ? str_replace( "[]", "", $input_name ) : $input_name;

    \Defender::add_field_session( [
        'input_name'     => $input_name,
        'title'          => trim( $title, '[]' ),
        'id'             => $options['input_id'],
        'type'           => 'dropdown',
        'required'       => $options['required'],
        'callback_check' => $options['callback_check'],
        'safemode'       => $options['safemode'],
        'error_text'     => $options['error_text'],
        'delimiter'      => $options['delimiter'],
    ] );
    add_to_jquery( "
    $('#" . $options['input_id'] . " button').bind('click', function(e){
        $('#" . $options['input_id'] . " button').removeClass('active');
        $(this).toggleClass('active');
        value = $(this).data('value');
        $('#" . $options['input_id'] . "-text').val(value);
    });
    " );

    return $html;
}
