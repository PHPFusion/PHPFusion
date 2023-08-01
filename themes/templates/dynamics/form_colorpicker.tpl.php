<?php


$html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').(!empty($error_class) ? $error_class : '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'".($options['width'] && !$label ? " style='width: ".$options['width']."'" : '').">";
$html .= $label ? "<label class='control-label ".($options['inline'] ? 'col-xs-12 col-sm-3 col-md-3 col-lg-3' : '')."' for='$input_id'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
    </label>\n" : '';
$html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
$html .= "<input type='text' data-jscolor='{}' name='$input_name' class='form-control ".$options['class']."' ".($options['inner_width'] ? "style='width:".$options['inner_width'].";'" : '')." id='".$input_id."' value='$input_value'".($options['placeholder'] ? " placeholder='".$options['placeholder']."'" : '')."".($options['deactivate'] ? " readonly" : "").">";
$html .= $options['inline'] && $label ? "</div>\n" : "";
$html .= "</div>\n";