<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_name.php
| Author: Frederick MC CHan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_name($input_name, $label = "", $input_value = FALSE, array $options = array()) {

    $locale = fusion_get_locale();

    $title = (isset($label) && (!empty($label))) ? $label : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    // NOTE (remember to parse readback value as of '|' seperator)
    if (!empty($input_value)) {
        if (!is_array($input_value)) {
            $input_value = construct_array($input_value, '', '|');
        }
    } else {
        $input_value['0'] = '';
        $input_value['1'] = '';
        $input_value['2'] = '';
    }

    $options += [
        'input_id'     => $input_name,
        'required'     => FALSE,
        'placeholder'  => '',
        'deactivate'   => FALSE,
        'width'        => '100%',
        'class'        => '',
        'inline'       => FALSE,
        'error_text'   => !empty($options['error_text']) ? $options['error_text'] : $locale['firstname_error'],
        'error_text_2' => !empty($options['error_text']) ? $options['error_text_2'] : $locale['lastname_error'],
        'tip'          => '',
        'safemode'     => FALSE,
        'stacked'      => '',
    ];
    $options['input_id'] = trim(str_replace("[", "-", $options['input_id']), "]");
    $error_class = \Defender::inputHasError($input_name.'-firstname') || \Defender::inputHasError($input_name.'-lastname') ? "has-error " : "";
    $html = "<div id='".$options['input_id']."-field' class='form-group clearfix ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' >\n";

    if ($label) {
        $html .= "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'> ".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
	    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
	    </label>\n";
    }

    $html .= ($options['inline']) ? "<div class='col-xs-12 ".($title ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 col-md-12 col-lg-12")."'>\n" : "";

    $html .= "<div class='row p-l-15'>\n";

    $html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 m-b-10 p-l-0'>\n";

    $html .= "<input type='text' name='".$input_name."[]' class='form-control textbox' id='".$options['input_id']."-firstname' value='".$input_value['0']."' placeholder='".$locale['first_name']." ".($options['required'] ? '*' : '')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";

    $html .= ($options['required'] == 1 && \Defender::inputHasError($input_name[0])) || \Defender::inputHasError($input_name[0]) ? "<div id='".$options['input_id']."-firstname-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";

    $html .= "</div>\n";

    $html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 m-b-10'>\n";
    $html .= "<input type='text' name='".$input_name."[]' class='form-control textbox' id='".$options['input_id']."-lastname' value='".$input_value['1']."' placeholder='".$locale['last_name']." ".($options['required'] ? '*' : '')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
    $html .= ($options['required'] == 1 && \Defender::inputHasError($input_name[1])) || \Defender::inputHasError($input_name[1]) ? "<div id='".$options['input_id']."-lastname-help' class='label label-danger p-5 display-inline-block'>".$options['error_text_2']."</div>" : "";
    $html .= "</div>\n";

    $html .= $options['stacked'];

    $html .= "</div>\n"; // close inner row

    $html .= ($options['inline']) ? "</div>\n" : "";

    $html .= "</div>\n";

    \Defender::getInstance()->add_field_session([
        'input_name'   => $input_name,
        'type'         => 'name',
        'title'        => $title,
        'id'           => $options['input_id'],
        'required'     => $options['required'],
        'safemode'     => $options['safemode'],
        'error_text'   => $options['error_text'],
        'error_text_2' => $options['error_text_2']
    ]);

    return $html;
}
