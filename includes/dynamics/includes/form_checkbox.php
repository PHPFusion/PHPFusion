<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_checkbox.php
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
function form_checkbox($input_name, $label = '', $input_value = '0', array $options = []) {

    $locale = fusion_get_locale('', LOCALE.LOCALESET.'global.php');

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
        'onclick'        => ''
    ];

    $options += $default_options;

	$error_class = '';

	$option_value = [];

	$default_checked = FALSE;

	$switch_class = '';

    if ($options['toggle']) {
    	$switch_class = 'is-bootstrap-switch ';
	    if (!defined("BOOTSTRAP_SWITCH_ASSETS")) {
		    define("BOOTSTRAP_SWITCH_ASSETS", TRUE);
		    // http://www.bootstrap-switch.org
		    add_to_head("<link href='".DYNAMICS."assets/switch/css/bootstrap-switch.min.css' rel='stylesheet' />");
		    add_to_footer("<script src='".DYNAMICS."assets/switch/js/bootstrap-switch.min.js'></script>");
		    add_to_jquery("$('.is-bootstrap-switch input[type=checkbox]').bootstrapSwitch();");
	    }
    }

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

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

    //@todo: this can use ksort/uksort
	$on_label = $options['toggle_text'][1];
	$off_label = $options['toggle_text'][0];
	if ($options['keyflip']) {
		$on_label = $options['toggle_text'][0];
		$off_label = $options['toggle_text'][1];
	}

    if (!empty($options['options']) && is_array($options['options'])) {

        $options['toggle'] = FALSE; // force toggle to be false if options existed

        if (!empty($input_value)) {

            $option_value = array_flip(explode($options['delimiter'], (string)$input_value)); // require key to value

        }

        // if there are options, and i want the options to be having input value.
        // options_value
        $input_value = [];

        $default_checked = empty($option_value) ? TRUE : FALSE;

        foreach (array_keys($options['options']) as $key) {
            $input_value[$key] = isset($option_value[$key]) ? (!empty($options['options_value'][$key]) ? $options['options_value'][$key] : 1) : 0;
        }
    }

    $checkbox = $options['inline'] ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n" : "\n";

    if (!empty($options['options']) && is_array($options['options'])) {

        foreach ($options['options'] as $key => $value) {

            if ($options['deactivate_key'] !== NULL && $options['deactivate_key'] == $key) {
                $checkbox .= form_hidden($input_name, '', $key);
            }

            $checkbox .= "<div class='".($options['type'] == 'radio' ? 'radio' : 'checkbox').($options['inline_options'] ? ' display-inline-block m-r-5' : '')."'>\n";

            $checkbox .= "<label class='control-label m-r-10' for='".$options['input_id']."-$key'".($options['inner_width'] ? " style='width: ".$options['inner_width']."'" : '').">";

            $checkbox .= "<input id='".$options['input_id']."-$key' name='$input_name' value='$key' type='".$options['type']."'
    
            ".($options['deactivate'] || $options['deactivate_key'] === $key ? 'disabled' : '').($options['onclick'] ? ' onclick="'.$options['onclick'].'"' : '').($input_value[$key] == TRUE || $default_checked && $key == FALSE ? ' checked' : '')." />\n";

            $checkbox .= $value;

            $checkbox .= "</label>\n";

            $checkbox .= "</div>\n";
        }

    } else {

        $checkbox .= "<div class='".(!empty($label) ? 'pull-left' : 'text-center')." m-r-10'>\n<input id='".$options['input_id']."'".($options['toggle'] ? " data-on-text='".$on_label."' data-off-text='".$off_label."'" : "")." style='margin: 0; vertical-align: middle' name='$input_name' value='".$options['value']."' type='".$options['type']."'".($options['deactivate'] ? ' disabled' : '').($options['onclick'] ? ' onclick="'.$options['onclick'].'"' : '').($input_value == $options['value'] ? ' checked' : '')." />\n</div>\n";

    }

    $html = "<div id='".$options['input_id']."-field' class='$switch_class form-group clearfix".($options['inline'] ? ' display-block overflow-hide' : '').($error_class ? $error_class : '').($options['class'] ? ' '.$options['class'] : '')."'>\n";

    $html .= (!empty($label)) ? "<label class='control-label".($options['inline'] ? " col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."' data-checked='".(!empty($input_value) ? "1" : "0")."' for='".$options['input_id']."'".($options['inner_width'] ? " style='width: ".$options['inner_width']."'" : '').">\n" : "";

    $html .= ($options['reverse_label'] == TRUE) ? $checkbox : "";

    $html .= (!empty($label)) ? "<div class='overflow-hide'>\n".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '').($options['tip'] ? " <i class='pointer fa fa-question-circle text-lighter' title='".$options['tip']."'></i>" : '')."</div>\n</label>\n" : "";

    $html .= ($options['reverse_label'] == FALSE) ? $checkbox : "";

    $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";

    $html .= \defender::inputHasError($input_name) ? "<span class='m-l-10'></span>" : "";

    $html .= \defender::inputHasError($input_name) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";

    $html .= $options['inline'] ? "</div>\n" : "";

    $html .= "</div>\n";

    \defender::add_field_session([
        'input_name' => str_replace("[]", "", $input_name),
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
