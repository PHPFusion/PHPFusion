<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
 * @param        $input_name
 * @param string $label
 * @param string $input_value
 * @param array  $options
 * @return string
 */
function form_hidden($input_name, $label = "", $input_value = "", array $options = array()) {

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $html = '';
    $default_options = array(
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
    );
    $options += $default_options;

    if ($options['show_title']) {
        $html .= "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$options['class']." '>\n";
        $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$title.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."</label>\n" : '';
        $html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
    }
    $html .= "<input type='hidden' name='$input_name' id='".$options['input_id']."' value='$input_value' ".($options['width'] ? "style='width:".$options['width']."'" : '')." ".($options['show_title'] ? "" : "readonly")." />\n";
    if ($options['show_title']) {
        $html .= "<div id='".$options['input_id']."-help'></div>";
        $html .= ($options['inline']) ? "</div>\n" : "";
        $html .= "</div>\n";
    }
    \defender::getInstance()->add_field_session(array(
                                     'input_name' => $input_name,
                                     'title'      => trim($title, '[]'),
                                     'type'       => 'textbox',
                                     'id'         => $options['input_id'],
                                     'required'   => $options['required'],
                                     'safemode'   => '0',
                                     "delimiter"  => $options['delimiter'],
                                     'error_text' => $options['error_text']
                                 ));

    return $html;
}