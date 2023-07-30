<?php

function text($options) {
    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').(!empty($error_class) ? $error_class : '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'".($options['width'] && !$label ? " style='width: ".$options['width']."'" : '').">";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$options['label_icon'].$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>" : '';
    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>" : "";
    $html .= ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? "<div class='input-group".($options['group_size'] ? ' input-group-'.$options['group_size'] : '')."' ".($options['width'] ? "style='width: ".$options['width']."'" : '').">" : "";
    if ($options['prepend_button'] && $options['prepend_type'] && $options['prepend_form_value'] && $options['prepend_class'] && $options['prepend_value']) {
        $html .= "<span class='input-group-btn input-group-prepend'>";
        $html .= "<button id='".$options['prepend_button_id']."' name='".$options['prepend_button_name']."' type='".$options['prepend_type']."' value='".$options['prepend_form_value']."' class='btn ".$options['prepend_size']." ".$options['prepend_class']."'>".$options['prepend_value']."</button>";
        $html .= "</span>\n";
    } else if ($options['prepend_value']) {
        $html .= "<span class='input-group-addon input-group-prepend' id='".$options['prepend_id']."'><span class='input-group-text'>".$options['prepend_value']."</span></span>";
    }
    $html .= "<input type='".$input_type."' data-type='".$input_type."' ".(!empty($options_data) ? implode(' ', $options_data) : '')." ".$min.$max.$step."class='form-control textbox ".($options['inner_class'] ? " ".$options['inner_class']." " : '')."' ".($options['inner_width'] ? "style='width:".$options['inner_width'].";'" : '').$max_length." name='".$input_name."' id='".$options['input_id']."' value='".$input_value."'".($options['placeholder'] ? " placeholder='".$options['placeholder']."' " : '')."".($options['autocomplete_off'] ? " autocomplete='off'" : '')." ".($options['deactivate'] ? 'readonly' : '').">";
    if ($options['append_button'] && $options['append_type'] && $options['append_form_value'] && $options['append_class'] && $options['append_value']) {
        $html .= "<span class='input-group-btn input-group-append'>";
        $html .= "<button id='".$options['append_button_id']."' name='".$options['append_button_name']."' type='".$options['append_type']."' value='".$options['append_form_value']."' class='btn ".$options['append_size']." ".$options['append_class']."'>".$options['append_value']."</button>";
        $html .= "</span>\n";
    } else if ($options['append_value']) {
        $html .= "<span class='input-group-addon input-group-append' id='".$options['append_id']."'><span class='input-group-text'>".$options['append_value']."</span></span>";
    }
    $html .= ($options['feedback_icon'] ? "<div class='form-control-feedback' style='top:0;'><i class='".$options['icon']."'></i></div>" : '');
    $html .= $options['stacked'];
    $html .= ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? "</div>" : "";
    $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";
    $html .= (\Defender::inputHasError($input_name) ? "<div class='input-error".((!$options['inline'] || $options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : "");
    $html .= $options['append_html'];
    $html .= ($options['password_strength'] == TRUE ? '<div class="m-t-5 pwstrength_viewport_progress"></div>' : "");
    $html .= (($options['inline'] && $label) ? "</div>" : "");
    $html .= "</div>";
    return $html;
}