<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_colorpicker.php
| Author: Frederick MC CHan (Chan)
| Credits: Mjolnic @ http://mjolnic.github.io/bootstrap-colorpicker/
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_colorpicker($input_name, $label = '', $input_value = '', array $options = array()) {

    $locale = fusion_get_locale();

    if (!defined("COLORPICKER")) {
        define("COLORPICKER", TRUE);
        add_to_head("<link href='".DYNAMICS."assets/colorpick/css/bootstrap-colorpicker.css' rel='stylesheet' media='screen' />");
        add_to_head("<script src='".DYNAMICS."assets/colorpick/js/bootstrap-colorpicker.js'></script>");
    }
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_name = stripinput($input_name);
    $input_value = stripinput($input_value);
    $default_options = array(
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
        "tip"         => "",
        'format'      => 'hex', //options = the color format - hex | rgb | rgba.
    );
    $options += $default_options;
    if (!$options['width']) {
        $options['width'] = $default_options['width'];
    }
    $input_id = $options['input_id'] ?: $default_options['input_id'];

    $error_class = "";
    if (\defender::inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = \defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    $html = "<div id='$input_id-field' class='form-group clearfix ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']." '>\n";
    $html .= $label ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
	".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
	</label>\n" : '';
    $html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "<br/>\n";
    $html .= "<div id='$input_id' ".($options['width'] && !$label ? "style='width: ".$options['width']."'" : '')." class='input-group colorpicker-component bscp colorpicker-element m-b-10' data-color='$input_value' data-color-format='".$options['format']."'>";
    $html .= "<input type='text' name='$input_name' class='form-control ".$options['class']."' id='".$input_id."' value='$input_value' data-color-format='".$options['format']."'".($options['placeholder'] ? " placeholder='".$options['placeholder']."'" : '')." style='width:".($options['inner_width'] ? $options['inner_width'] : $default_options['inner_width'])."' ".($options['deactivate'] ? "readonly" : "").">";
    $html .= "<span id='$input_id-cp' class='input-group-addon'>";
    $html .= "<i style='background: rgba(255,255,255,1);'></i>";
    $html .= "</span></div>";
    $html .= $options['inline'] ? "</div>\n" : "";
    $html .= "</div>\n";

    \defender::getInstance()->add_field_session(array(
                                     'input_name' => $input_name,
                                     'type'       => 'color',
                                     'title'      => $title,
                                     'id'         => $input_id,
                                     'required'   => $options['required'],
                                     'safemode'   => $options['safemode'],
                                     'error_text' => $options['error_text']
                                 ));
    add_to_jquery("$('#$input_id').colorpicker({ format : '".$options['format']."'  });");

    return $html;
}
