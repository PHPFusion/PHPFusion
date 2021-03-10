<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_checkbox.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
 *
 * @return string
 */
function form_checkbox($input_name, $label = '', $input_value = '0', array $options = []) {

    $locale = fusion_get_locale('', LOCALE.LOCALESET.'global.php');

    $input_value = clean_input_value($input_value);

    $default_options = [
        'input_id'       => $input_name,
        'inline'         => FALSE,
        'inline_options' => FALSE,
        'required'       => FALSE,
        'deactivate'     => FALSE,
        'class'          => '',
        'type'           => 'checkbox',
        'toggle'         => FALSE,
        'toggle_text'    => [$locale['no'], $locale['yes']],
        'options'        => [],
        'options_value'  => [],
        'delimiter'      => ',',
        'safemode'       => FALSE,
        'keyflip'        => FALSE,
        'error_text'     => $locale['error_input_checkbox'],
        'value'          => 1,
        'tip'            => '',
        'ext_tip'        => '',
        'inner_width'    => '',
        'reverse_label'  => FALSE,
        'deactivate_key' => NULL,
        'onclick'        => '',
    ];

    $options += $default_options;

    $error_class = '';

    $option_value = [];

    $default_checked = FALSE;

    if ($options['toggle']) {
        if (!defined("CHECKBOX_SWITCH_CSS")) {
            define("CHECKBOX_SWITCH_CSS", TRUE);
            add_to_head("<link rel='stylesheet' href='".DYNAMICS."assets/switch/switch.min.css'>");
        }
    }

    $title = ($label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name))));

    // 'input_id[]' becomes 'input_id-', due to foreach has multiple options, and these DOM selectors are needed
    $options['input_id'] = trim(str_replace('[', '-', $options['input_id']), "]");

    if (\defender::inputHasError($input_name)) {
        $error_class = " has-error";
        if (!empty($options['error_text'])) {
            $new_error_text = \defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    if (!empty($options['options']) && is_array($options['options'])) {

        $options['toggle'] = FALSE; // force toggle to be false if options existed

        if (!empty($input_value) && !is_array($input_value)) {
            $option_value = array_flip(explode($options['delimiter'], (string)$input_value)); // require key to value
        }

        // if there are options, and i want the options to be having input value.
        // options_value
        $input_value = [];

        $default_checked = empty($option_value);

        foreach (array_keys($options['options']) as $key) {
            $input_value[$key] = isset($option_value[$key]) ? (!empty($options['options_value'][$key]) ? $options['options_value'][$key] : 1) : 0;
        }
    }

    $checkbox = $options['inline'] && $label ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>" : "";

    if (!empty($options['options']) && is_array($options['options'])) {

        foreach ($options['options'] as $key => $value) {

            if ($options['deactivate_key'] !== NULL && $options['deactivate_key'] == $key) {
                $checkbox .= form_hidden($input_name, '', $key);
            }

            $checkbox .= "<div class='".($options['type'] == 'radio' ? 'radio' : 'checkbox').($options['inline_options'] ? ' display-inline-block m-r-5' : '')."'>";

            $checkbox .= "<label class='control-label m-r-10' for='".$options['input_id']."-$key'".($options['inner_width'] ? " style='width: ".$options['inner_width']."'" : '').">";

            $checkbox .= "<input id='".$options['input_id']."-$key' name='$input_name' value='$key' type='".$options['type']."'

            ".($options['deactivate'] || $options['deactivate_key'] === $key ? 'disabled' : '').($options['onclick'] ? ' onclick="'.$options['onclick'].'"' : '').($input_value[$key] == TRUE || $default_checked && $key == FALSE ? ' checked' : '')." />";

            $checkbox .= $value;

            $checkbox .= "</label>";

            $checkbox .= "</div>";
        }

    } else {
        $checkbox .= "<div class='".(!empty($label) ? 'pull-left' : 'text-center')." m-r-10'>";
        $checkbox .= "<input id='".$options['input_id']."' style='margin:0;vertical-align:middle;' name='$input_name' value='".$options['value']."' type='".$options['type']."'".($options['deactivate'] ? ' disabled' : '').($options['onclick'] ? ' onclick="'.$options['onclick'].'"' : '').($input_value == $options['value'] ? ' checked' : '').">";
        $checkbox .= "</div>";
    }

    $html = "<div id='".$options['input_id']."-field' class='".($options['toggle'] ? 'checkbox-switch clearfix ' : '')."form-group ".($options['inline'] && $label ? 'row ' : '').($error_class ? $error_class : '').($options['class'] ? ' '.$options['class'] : '')."'>";

    $html .= (!empty($label)) ? "<label class='control-label".($options['inline'] ? " col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' data-checked='".(!empty($input_value) ? "1" : "0")."' for='".$options['input_id']."'".($options['inner_width'] ? " style='width: ".$options['inner_width']."'" : '').">" : "";

    $html .= ($options['reverse_label'] == TRUE ? $checkbox : "");

    $html .= (!empty($label)) ? "<div class='overflow-hide'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '').($options['tip'] ? " <i class='pointer fa fa-question-circle text-lighter' title='".$options['tip']."'></i>" : '')."</div></label>" : "";

    $html .= ($options['reverse_label'] == FALSE ? $checkbox : "");

    $html .= $options['ext_tip'] ? "<br/><span class='tip'><i>".$options['ext_tip']."</i></span>" : "";

    $html .= \defender::inputHasError($input_name) ? "<span class='m-l-10'></span>" : "";

    $html .= \defender::inputHasError($input_name) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";

    $html .= $options['inline'] && $label ? "</div>" : "";

    $html .= "</div>";

    \defender::add_field_session([
        'input_name' => clean_input_name($input_name),
        'title'      => trim($title, '[]'),
        'id'         => $options['input_id'],
        'type'       => $options['type'],
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text'],
        'delimiter'  => $options['delimiter'],
    ]);

    return (string)$html;
}
