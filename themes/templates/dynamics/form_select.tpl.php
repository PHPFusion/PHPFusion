<?php
/*
 * -------------------------------------------------------+
 * | PHPFusion Content Management System
 * | Copyright (C) PHP Fusion Inc
 * | https://phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename: theme.php
 * | Author:  meangczac (Chan)
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */

// Form select default tpl

$html = "<div id='" . $options['input_id'] . "-field' class='form-group " . ($options['inline'] && $label ? 'row' : '') . $error_class . ' ' . $options['class'] . "' " . ($options['width'] && !$label ? "style='width: " . $options['width'] . "'" : '') . ">\n";
$html .= ($label) ? "<label class='control-label " . ($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '') . "' for='" . $options['input_id'] . "'>" . $label . ($options['required'] == TRUE ? "<span class='required'>&nbsp;*</span>" : '') . "
    " . ($options['tip'] ? "<i class='pointer fa fa-question-circle' title='" . $options['tip'] . "'></i>" : '') . "
    </label>\n" : '';
$html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";

if ($options['jsonmode'] || $options['tags']) {
    // json mode.
    $html .= "<div id='" . $options['input_id'] . "-spinner' style='display:none;'>\n<img src='" . fusion_get_settings( 'siteurl' ) . "images/loader.svg'>\n</div>\n";
    $html .= "<input " . ($options['required'] ? "class='req'" : '') . " type='hidden' name='$input_name' id='" . $options['input_id'] . "' style='width: " . ($options['width'] ? $options['inner_width'] : $default_options['width']) . "'/>\n";

} else {
    // normal mode
    $html .= "<select " . ($options['select2_disabled'] == TRUE ? " class='form-control' " : "") . " name='$input_name' id='" . $options['input_id'] . "' style='width: " . (!empty( $options['inner_width'] ) ? $options['inner_width'] : $default_options['inner_width']) . "'" . ($options['deactivate'] ? " disabled" : "") . ($options['onchange'] ? ' onchange="' . $options['onchange'] . '"' : '') . ($options['multiple'] ? " multiple" : "") . ">\n";

    $html .= ($options['allowclear']) ? "<option value=''></option>\n" : '';

    // add parent value
    if ($options['no_root'] == FALSE && !empty( $options['cat_col'] ) || $options['add_parent_opts'] === TRUE) { // api options to remove root from selector. used in items creation.
        $this_select = '';
        if ($input_value !== NULL) {
            if ($input_value !== '') {
                $this_select = 'selected';
            }
        }
        $html .= "<option value='0' " . $this_select . " >" . $options['parent_value'] . "</option>\n";
    }

    /**
     * Supported Formatting
     * ---------------------
     * Have an array that looks like this in 'options' key
     * array('text' => 'Parent Text', 'children' => array(1 => 'Child A' , 2 => 'Child B'));
     * or
     * array(1 => 'Option A', 2 => 'Option B');
     */
    if (is_array( $options['options'] )) {
        // Test if this is an optgroup
        $test_array = $options['options'];
        foreach ($test_array as $v) {
            if (isset( $v['text'] )) {
                $options['optgroup'] = TRUE;
                break;
            }
        }
        if ($options['optgroup']) {

            $html .= form_select_build_optgroup( $options['options'], $input_value, $options );
        } else {

            foreach ($options['options'] as $arr => $v) { // outputs: key, value, class - in order
                $select = '';
                $chain = '';
                // Chain method always bind to option's array key
                if (isset( $options['chain_index'][$arr] )) {
                    $chain = " class='" . $options['chain_index'][$arr] . "' ";
                }

                // do a disable for filter_opts item.
                if ($options['keyflip']) { // flip mode = store array values
                    if ($input_value !== '') {
                        $select = ($input_value == $v) ? " selected" : "";
                    }
                    $disabled = $disable_opts && in_array( $arr, $disable_opts );
                    $hide = $disabled && $options['hide_disabled'];
                    $html .= (!$hide ? "<option value='$v'" . $chain . $select . ($disabled ? 'disabled' : '') . ">" . html_entity_decode( $v ) . " " . ($options['show_current'] && $input_value == $v ? '(Current Item)' : '') . "</option>\n" : "");
                } else {
                    if ($input_value !== '') {
                        //$input_value = stripinput($input_value); // not sure if can turn FALSE to zero not null.
                        $select = (isset( $input_value ) && $input_value == $arr) ? ' selected' : '';
                    }
                    $disabled = $disable_opts && in_array( $arr, $disable_opts );
                    $hide = $disabled && $options['hide_disabled'];
                    $html .= (!$hide ? "<option value='$arr'" . $chain . $select . ($disabled ? 'disabled' : '') . ">" . html_entity_decode( $v ) . " " . ($options['show_current'] && $input_value == $v ? '(Current Item)' : '') . "</option>\n" : "");
                }
            }
        }
    }
    $html .= "</select>\n";
}

$html .= $options['stacked'];
$html .= $options['ext_tip'] ? "<br/>\n<div class='m-t-10 tip'><i>" . $options['ext_tip'] . "</i></div>" : "";
$html .= \Defender::inputHasError( $input_name ) && !$options['inline'] ? "<br/>" : "";
$html .= \Defender::inputHasError( $input_name ) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";
$html .= $options['inline'] && $label ? "</div>\n" : '';
$html .= "</div>\n";
if ($options['required']) {
    $html .= "<input class='req' id='dummy-" . $options['input_id'] . "' type='hidden'>\n"; // for jscheck
}

