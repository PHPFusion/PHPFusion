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
 * @param array  $options
 *
 * @return string
 */
function form_hidden($input_name, $label = "", $input_value = "", array $options = []) {

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $input_value = clean_input_value($input_value);

    $html = '';
    $default_options = [
        'input_id'    => $input_name,
        'show_title'  => FALSE,
        'width'       => '100%',
        'class'       => '',
        'inline'      => FALSE,
        'required'    => FALSE,
        'placeholder' => '',
        'deactivate'  => FALSE,
        'delimiter'   => ',',
        'error_text'  => '',
        'inner_class' => '',
        'readonly' => FALSE,
    ];
    $options += $default_options;

    if ($options['inner_class']) {
        $inner_class = " class='{$options['inner_class']}' ";
    }

    if ($options['show_title']) {
        $html .= "<div id='{$options['input_id']}-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$options['class']." '>\n";
        $html .= ($label) ? "<label class='control-label".($options['inline'] ? " col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$title.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."</label>\n" : '';
        $html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>" : '';
    }
    $html .= "<input type='hidden' ".($inner_class ?? '')." name='$input_name' id='{$options['input_id']}' value='$input_value'".($options['width'] ? " style='width:".$options['width']."'" : '').($options['readonly'] ? " readonly" : "").">";

    if ($options['show_title']) {
        $html .= "<div id='".$options['input_id']."-help'></div>";
        $html .= ($options['inline']) ? "</div>" : "";
        $html .= "</div>";
    }

    \Defender::add_field_session([
        'input_name' => clean_input_name($input_name),
        'title'      => trim($title, '[]'),
        'type'       => 'textbox',
        'id'         => $options['input_id'],
        'required'   => $options['required'],
        'safemode'   => '0',
        "delimiter"  => $options['delimiter'],
        'error_text' => $options['error_text']
    ]);

    return dynamics_render_component_template('form_hidden', $html);
}
