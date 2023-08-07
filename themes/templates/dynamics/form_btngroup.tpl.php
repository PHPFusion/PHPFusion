
$html = "<div id='" . $options['input_id'] . "-field' class='form-group " . ($options['inline'] && $label ? 'row ' : '') . $error_class . " clearfix'>\n";
$html .= ($label) ? "<label class='control-label " . ($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '') . "' for='" . $options['input_id'] . "'>" . $label . ($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') . "</label>\n" : '';
$html .= ($options['inline'] && $label) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n" : "";

    $html .= "<div class='btn-group' id='" . $options['input_id'] . "'>";

    if (!empty( $options['options'] ) && is_array( $options['options'] )) {
    $i = 1;
    $option_count = count( $options['options'] );

    foreach ($options['options'] as $arr => $v) {
    $child_class = ($option_count == $i ? ' last-child ' : '');
    $active_class = ($input_value == $arr ? ' active' : '');

    if ($options['type'] == 'submit') {
    $html .= "<button name='$arr' type='submit' data-value='$arr' value='$arr' class='btn " . $options['btn_class'] . $child_class . $active_class . "'>$v</button>\n";
    } else {
    $html .= "<button type='button' data-value='$arr' class='btn " . $options['btn_class'] . $child_class . $active_class . "'>$v</button>\n";
    }

    $i++;
    }
    }

    $html .= "</div>\n";

$html .= "<input name='$input_name' type='hidden' id='" . $options['input_id'] . "-text' value='$input_value' />\n";

$html .= $options['ext_tip'] ? "<br/>\n<div class='m-t-10 tip'><i>" . $options['ext_tip'] . "</i></div>" : "";
$html .= \Defender::inputHasError( $input_name ) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";
$html .= ($options['inline'] && $label) ? "</div>\n" : "";
$html .= "</div>\n";


return $html;