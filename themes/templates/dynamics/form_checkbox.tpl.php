<?php

$checkbox = $options['inline'] && $label ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>" : "";

if ( !empty( $options['options'] ) && is_array( $options['options'] ) ) {
    foreach ( $options['options'] as $key => $value ) {
        // Adds deactivated options as hidden input
        if ( $options['deactivate_key'] !== NULL && $options['deactivate_key'] == $key ) {
            $checkbox .= form_hidden( $input_name, '', $key );
        }

        $checkbox .= "<div class='" . ( $options['type'] == 'radio' ? 'radio' : 'checkbox' ) . ( $options['inline_options'] ? ' display-inline-block m-r-5' : '' ) . "'>";
        $checkbox .= "<label class='control-label m-r-10' for='" . $options['input_id'] . "-$key'" . ( $options['inner_width'] ? " style='width: " . $options['inner_width'] . "'" : '' ) . ">";
        $checkbox .= "<input id='" . $options['input_id'] . "-$key' name='$input_name' value='$key' type='" . $options['type'] . "' " . ( $options['deactivate'] || $options['deactivate_key'] === $key ? 'disabled' : '' ) . ( $options['onclick'] ? ' onclick="' . $options['onclick'] . '"' : '' ) . ( $input_value[$key] == TRUE ? ' checked' : '' ) . " />";
        $checkbox .= $value;
        $checkbox .= "</label>";
        $checkbox .= "</div>";
    }
} else {
    $checkbox .= "<div class='" . ( !empty( $label ) ? 'pull-right' : '' ) . "'>";
    $checkbox .= "<input id='" . $options['input_id'] . "' style='margin:0;vertical-align:middle;' name='$input_name' value='" . $options['value'] . "' type='" . $options['type'] . "'" . ( $options['deactivate'] ? ' disabled' : '' ) . ( $options['onclick'] ? ' onclick="' . $options['onclick'] . '"' : '' ) . ( $input_value == $options['value'] ? ' checked' : '' ) . ">";
    $checkbox .= "</div>";
}

$html = "<div id='" . $options['input_id'] . "-field' class='" . ( $options['toggle'] ? 'checkbox-switch ' : '' ) . "form-group check-group " . ( $options['inline'] && $label ? 'row ' : '' ) .
    ( !empty( $error_class ) ? $error_class : '' ) . ( $options['class'] ? ' ' . $options['class'] : '' ) . "'>";
$html .= ( !empty( $label ) ) ? "<label class='control-label" . ( $options['inline'] ? " col-xs-12 col-sm-3 col-md-3 col-lg-3" : '' ) . "' data-checked='" . ( !empty( $input_value ) ? "1" : "0" ) . "' for='" . $options['input_id'] . "'" . ( $options['inner_width'] ? " style='width: " . $options['inner_width'] . "'" : '' ) . ">" : "";

$html .= ( $options['reverse_label'] == TRUE ? $checkbox : "" );

$html .= ( !empty( $label ) ) ? "<div class='overflow-hide'>" . $label . ( $options['required'] ? "<span class='required'>&nbsp;*</span>" : '' ) . ( $options['tip'] ? " <i class='pointer fa fa-question-circle text-lighter' title='" . $options['tip'] . "'></i>" : '' ) . "</div></label>" : "";

$html .= ( $options['reverse_label'] == FALSE ? $checkbox : "" );

$html .= $options['ext_tip'] ? "<br/><span class='tip'><i>" . $options['ext_tip'] . "</i></span>" : "";

$html .= Defender::inputHasError( $input_name ) ? "<span class='m-l-10'></span>" : "";

$html .= Defender::inputHasError( $input_name ) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";

$html .= $options['inline'] && $label ? "</div>" : "";

$html .= "</div>";
