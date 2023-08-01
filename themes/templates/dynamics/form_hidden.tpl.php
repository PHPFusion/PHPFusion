<?php

if ($options['show_title']) {
    $html .= "<div id='" . $options['input_id'] . "-field' class='form-group " . ($options['inline'] ? 'display-block overflow-hide ' : '') . $options['class'] . " '>\n";
    $html .= ($label) ? "<label class='control-label" . ($options['inline'] ? " col-xs-12 col-sm-3 col-md-3 col-lg-3" : '') . "' for='" . $options['input_id'] . "'>" . $title . ($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') . "</label>\n" : '';
    $html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
}

$html .= "<input type='hidden' name='$input_name' id='" . $options['input_id'] . "' value='$input_value'" . ($options['width'] ? " style='width:" . $options['width'] . "'" : '') . ($options['show_title'] ? "" : " readonly") . " />\n";

if ($options['show_title']) {
    $html .= "<div id='" . $options['input_id'] . "-help'></div>";
    $html .= ($options['inline']) ? "</div>\n" : "";
    $html .= "</div>\n";
}
