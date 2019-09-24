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

/**
 * Checkbox Input
 * @param        $input_name
 * @param string $label
 * @param string $input_value
 * @param array  $options
 *
 * @return string
 * @throws ReflectionException
 */
function form_checkbox($input_name, $label = '', $input_value = '0', array $options = []) {

    $locale = fusion_get_locale('', LOCALE.LOCALESET.'global.php');

    $default_options = [
        'input_id'       => $input_name,
        'inline'         => FALSE,
        'inline_options' => FALSE,
        'required'       => FALSE,
        'deactivate'     => FALSE,
        'class'          => '',
        'button_class'   => 'btn-default', // default, success, danger, warning, info
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
        'stacked' => '',
    ];

    $options += $default_options;

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

    $title = $label ?: ucfirst(strtolower(str_replace('_', ' ', $input_name)));

    $options['input_id'] = trim(str_replace("[", "-", $options['input_id']), "]");

    \Defender::add_field_session([
        'input_name' => clean_input_name($input_name),
        'title'      => clean_input_name($title),
        'id'         => $options['input_id'],
        'type'       => $options['type'],
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text'],
        'delimiter'  => $options['delimiter'],
    ]);

    if (\Defender::inputHasError($input_name)) {
        // $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = \Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    // $on_label = $options['toggle_text'][1];
    // $off_label = $options['toggle_text'][0];
    // if ($options['keyflip']) {
    //     $on_label = $options['toggle_text'][0];
    //     $off_label = $options['toggle_text'][1];
    // }
    //
    // if (!empty($options['options']) && is_array($options['options'])) {
    //
    //     $options['toggle'] = FALSE; // force toggle to be false if options existed
    //
    //     if (!empty($input_value)) {
    //
    //         $option_value = array_flip(explode($options['delimiter'], (string)$input_value)); // require key to value
    //
    //     }
    //
    //     // for checkbox only
    //     // if there are options, and i want the options to be having input value.
    //     // options_value
    //     if ($options['type'] == 'checkbox' && count($options['options']) > 1) {
    //         $input_value = [];
    //         $default_checked = empty($option_value) ? TRUE : FALSE;
    //         foreach (array_keys($options['options']) as $key) {
    //             $input_value[$key] = isset($option_value[$key]) ? (!empty($options['options_value'][$key]) ? $options['options_value'][$key] : 1) : 0;
    //         }
    //     }
    // }

    $fusion_steam = new \PHPFusion\Steam('bootstrap3');
    $html = $fusion_steam->load('Form')->checkbox($input_name, $label, $input_value, $options);

    return (string)$html;
}
