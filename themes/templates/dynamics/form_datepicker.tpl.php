<?php
$html = "<div id='" . $options["input_id"] . "-field' class='form-group " . ($options['inline'] && $label ? 'row ' : '') . $error_class . $options['class'] . "'>\n";

$html .= ($label ? "<label class='control-label" . ($options['inline'] ? " col-xs-12 col-sm-3 col-md-3 col-lg-3" : '') . "' for='" . $options["input_id"] . "'>" . $label . ($options['required'] ? "<span class='required'>&nbsp;*</span> " : '') . ($options['tip'] ? "<i class='pointer fa fa-question-circle' title='" . $options['tip'] . "'></i>" : '') . "</label>" : "");

$html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";

$html .= "<div id='" . $options["input_id"] . "_datepicker' data-target-input='nearest' class='input-group date'" . ($options['width'] ? " style='width: " . $options['width'] . "'" : "") . ">\n";

$html .= "<input type='text' name='" . $input_name . "' id='" . $options["input_id"] . "' data-target='#" . $options["input_id"] . "-datepicker' value='" . $input_value . "' class='datetimepicker-input form-control textbox'" . ($options['inner_width'] ? " style='width:" . $options['inner_width'] . ";'" : '') . ($options['placeholder'] ? " placeholder='" . $options['placeholder'] . "'" : '') . "/>\n";

$html .= "<span class='input-group-addon input-group-append " . ($options['fieldicon_off'] ? 'display-none' : '') . "' data-target='#" . $options["input_id"] . "-datepicker' data-toggle='datetimepicker'><i class='input-group-text fa fa-calendar'></i></span>\n";

$html .= "</div>\n";

$html .= (($options['required'] == 1 && \Defender::inputHasError( $input_name )) || \Defender::inputHasError( $input_name ) ? "<div id='" . $options["input_id"] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "");

$html .= $options['stacked'];

$html .= ($options['inline'] && $label ? "</div>" : "");

$html .= "</div>";