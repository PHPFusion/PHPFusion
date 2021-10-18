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

    $input_value = clean_input_value($input_value);

    fusion_load_script(DYNAMICS.'assets/colorpick/jscolor.min.js');

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_name = stripinput($input_name);
    $input_value = stripinput($input_value);
    $default_options = [
        'input_id'    => $input_name,
        'required'    => FALSE,
        'placeholder' => '',
        'deactivate'  => FALSE,
        'width'       => '',
        'inner_width' => '100%',
        'class'       => '',
        'inline'      => FALSE,
        'error_text'  => $locale['error_input_default'],
        'safemode'    => FALSE,
        'icon'        => "",
        'tip'         => "",
    ];
    $options += $default_options;
    if (!$options['width']) {
        $options['width'] = $default_options['width'];
    }
    $input_id = $options['input_id'] ?: $default_options['input_id'];

    $error_class = "";
    if (\Defender::inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = \Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addnotice("danger", $options['error_text']);
        }
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').(!empty($error_class) ? $error_class : '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'".($options['width'] && !$label ? " style='width: ".$options['width']."'" : '').">";
    $html .= $label ? "<label class='control-label ".($options['inline'] ? 'col-xs-12 col-sm-3 col-md-3 col-lg-3' : '')."' for='$input_id'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
    </label>\n" : '';
    $html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
    $html .= "<input type='text' data-jscolor='{}' name='$input_name' class='form-control ".$options['class']."' ".($options['inner_width'] ? "style='width:".$options['inner_width'].";'" : '')." id='".$input_id."' value='$input_value'".($options['placeholder'] ? " placeholder='".$options['placeholder']."'" : '')."".($options['deactivate'] ? " readonly" : "").">";
    $html .= $options['inline'] && $label ? "</div>\n" : "";
    $html .= "</div>\n";

    \Defender::getInstance()->add_field_session([
        'input_name' => clean_input_name($input_name),
        'type'       => 'color',
        'title'      => $title,
        'id'         => $input_id,
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text']
    ]);

    return $html;
}
