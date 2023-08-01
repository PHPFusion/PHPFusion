<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_colorpicker.php
| Author: Frederick MC CHan (Chan)
| Credits: https://farbelous.github.io/bootstrap-colorpicker/
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
 * @param array  $options
 *
 * @return string
 */
function form_colorpicker($input_name, $label = '', $input_value = '', array $options = []) {

    $locale = fusion_get_locale();

    fusion_load_script(DYNAMICS.'assets/colorpick/jscolor.min.js');

    $input_value = clean_input_value($input_value);

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $options += [
        'input_name'    => clean_input_name($input_name),
        'input_id'    => clean_input_id($input_name),
        'required'    => FALSE,
        'placeholder' => '',
        'deactivate'  => FALSE,
        'width'       => '250px',
        'inner_width' => '100%',
        'class'       => '',
        'inline'      => FALSE,
        'error_text'  => $locale['error_input_default'],
        'safemode'    => FALSE,
        'icon'        => '',
        'tip'         => '',
    ];

    $options['template_type'] = 'colorpicker';

    list($options['error_class'], $options['error_text']) = form_errors($options);

    set_field_config([
        'input_name' => $options['input_name'],
        'type'       => 'color',
        'title'      => $title,
        'id'         => $options['input_id'],
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text']
    ]);

    ksort( $options );

    return fusion_get_template( 'form_inputs', [
        "input_name"    => $input_name,
        "input_label"   => $label,
        "input_value"   => $options['priority_value'] ?? $input_value,
        "input_options" => $options,
    ] );


}
