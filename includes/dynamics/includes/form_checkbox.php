<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_checkbox.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_checkbox($input_name, $label = '', $input_value = '0', array $options = array()) {
    global $defender, $locale;
    $options += array(
        'input_id' => !empty($options['input_id']) ? $options['input_id'] : $input_name,
        'class' => !empty($options['class']) ? $options['class'] : '',
        "type" => "checkbox",
        'toggle' => !empty($options['toggle']) && $options['toggle'] == 1 ? 1 : 0,
        'toggle_text' => !empty($options['toggle_text']) && (!empty($options['toggle_text'][0]) && !empty($options['toggle_text'][1])) ? $options['toggle_text'] : array(
            $locale['no'],
            $locale['yes']
        ),
        'safemode' => FALSE,
        'delimiter' => ',',
        "options" => array(),
        'keyflip' => !empty($options['keyflip']) && $options['keyflip'] == 1 ? 1 : 0,
        'error_text' => !empty($options['error_text']) ? $options['error_text'] : $locale['error_input_checkbox'],
        'inline' => !empty($options['inline']) ? TRUE : FALSE,
        'required' => !empty($options['required']) ? TRUE : FALSE,
        'disabled' => !empty($options['disabled']) ? TRUE : FALSE,
        'value' => !empty($options['value']) && $options['value'] ? $options['value'] : 1,
        'tip' => !empty($options['tip']) ? $options['tip'] : '',
        "reverse_label" => !empty($options['reverse_label']) ? TRUE : FALSE,
    );
    if ($options['toggle'] && !defined("BOOTSTRAP_SWITCH_ASSETS")) {
        define("BOOTSTRAP_SWITCH_ASSETS", TRUE);
        // http://www.bootstrap-switch.org
        add_to_head("<link href='".DYNAMICS."assets/switch/css/bootstrap-switch.min.css' rel='stylesheet' />");
        add_to_footer("<script src='".DYNAMICS."assets/switch/js/bootstrap-switch.min.js'></script>");
        // Target by class and type, not IDs. We don't want repetitive code
        add_to_jquery("$('.is-bootstrap-switch input[type=checkbox]').bootstrapSwitch();");
    }
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $options['input_id'] = trim($options['input_id'], "[]");

    $error_class = "";
    if ($defender->inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = $defender->getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    $on_label = "";
    $off_label = "";
    $switch_class = "";
    if (!empty($options['options']) && is_array($options['options'])) {
        $options['toggle'] = "";
        $value = array();
        if (!empty($input_value)) {
            $value = array_flip(explode(",", $input_value)); // require key to value
        }
        $input_value = array();
        foreach (array_keys($options['options']) as $key) {
            $input_value[$key] = isset($value[$key]) ? 1 : 0;
        }
        if (!empty($label)) {
            add_to_jquery("
			$('#".$options['input_id']."-field > .control-label').bind('click', function() {
				var checked_status = $(this).data('checked');
				$('#".$options['input_id']."-field input:checkbox').prop('checked', $(this).data('checked'));
				if ($(this).data('checked') == '1') {
					$(this).data('checked', 0);
				} else {
					$(this).data('checked', 1);
				}
			});
			");
        }
    } else {
        $switch_class = $options['toggle'] ? "is-bootstrap-switch" : "";
        $on_label = $options['toggle_text'][1];
        $off_label = $options['toggle_text'][0];
        if ($options['keyflip']) {
            $on_label = $options['toggle_text'][0];
            $off_label = $options['toggle_text'][1];
        }
    }

    $checkbox = $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "\n";
    if (!empty($options['options']) && is_array($options['options'])) {
        foreach ($options['options'] as $key => $value) {
            $checkbox .= "<input id='".$options['input_id']."-$key' style='vertical-align: middle' name='$input_name' value='$key' type='".$options['type']."' ".($options['disabled'] ? 'disabled' : '')." ".($input_value[$key] == '1' ? 'checked' : '')." /> <label class='control-label m-r-10' for='".$options['input_id']."-$key'>".$value."</label>\n";
        }
    } else {
        $checkbox .= "<input id='".$options['input_id']."' ".($options['toggle'] ? "data-on-text='".$on_label."' data-off-text='".$off_label."'" : "")." style='margin: 0;vertical-align: middle' name='$input_name' value='".$options['value']."' type='checkbox' ".($options['disabled'] ? 'disabled' : '')." ".($input_value == '1' ? 'checked' : '')." />\n";
    }
    $checkbox .= $defender->inputHasError($input_name) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $checkbox .= $options['inline'] ? "</div>\n" : "";
    $html = "<div id='".$options['input_id']."-field' class='$switch_class form-group clearfix ".$error_class.$options['class']."'>\n";
    $html .= (!empty($label)) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' data-checked='".(!empty($input_value) ? "1" : "0")."'  for='".$options['input_id']."'>\n" : "";
    $html .= ($options['reverse_label'] == TRUE) ? $checkbox : "";
    $html .= (!empty($label)) ? "$label ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n" : "";
    $html .= ($options['reverse_label'] == FALSE) ? $checkbox : "";
    $html .= "</div>\n";

    $defender->add_field_session(
        array(
            'input_name' => str_replace("[]", "", $input_name),
            'title' => trim($title, '[]'),
            'id' => $options['input_id'],
            'type' => $options['type'],
            'required' => $options['required'],
            'safemode' => $options['safemode'],
            'error_text' => $options['error_text'],
            'delimiter' => $options['delimiter'],
        ));

    return $html;
}