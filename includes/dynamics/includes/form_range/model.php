<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_range.php
| Author: Core Development Team
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
 * @param string $input_name
 * @param string $label
 * @param string $input_value
 * @param array  $options
 *
 * @return string
 */
function load_form_range_assets(): void {
    static $loaded = FALSE;

    if (!$loaded) {
        fusion_load_script(DYNAMICS.'includes/form_range/component.css', 'css');
        $loaded = TRUE;
    }
}

function form_range($input_name, $label = "", $input_value = "", array $options = []) {

    load_form_range_assets();

    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $input_id = trim(str_replace("[", "-", $input_name), "]");

    $input_value = clean_input_value($input_value);

    $default_options = [
        'type'            => 'text',
        'required'        => FALSE, // whether required or not
        'label_icon'      => '', // icon for the label
        'safemode'        => FALSE, // whether strict sanitization mode or not
        'callback_check'  => FALSE, // check values based on your own function
        'input_id'        => $input_id, // input id
        'placeholder'     => '', // placeholder for the input field
        'deactivate'      => FALSE, // readonly or not
        'width'           => '', // outer container width
        'inner_width'     => '', // inner element width
        'class'           => '', // outer container class
        'inner_class'     => '', // inner element class
        'inline'          => FALSE, // whether element is inline or not
        'floating_label'  => FALSE,
        'min'             => 1, // minimum slider value
        'max'             => 100, // maximum slider value
        'step'            => 1, // per slider step, set to 0 for fluid
        'tip'             => '', // the tip on label
        'ext_tip'         => '', // the tip on below field
        'error_text'      => '', // text to show during error
        'stacked'         => '', // adds html into the dom element
        'data'            => [], // adds data attributes to the element
        'append_html'     => '', // adds html
        'display_percent' => FALSE, // element is displayed as % or unit value
        'value_unit'      => '%', // unit displayed after the current value
        'range_buttons'   => FALSE, // display 4 quick buttons to set the slider value
    ];

    $options += $default_options;
    if (!empty($options['ext_tip'])) {
        $options['ext_tip'] = dynamics_field_help($options['ext_tip'], TRUE);
    }

    $options['type'] = 'number';

    $options_data = [];
    if (!empty($options['data'])) {
        array_walk($options['data'], function ($a, $b) use (&$options_data) {
            $options_data[] = "data-$b='$a'";
        }, $options_data);
    }

    // Error messages based on settings
    $options['error_text'] = empty($options['error_text']) ? $locale['error_input_default'] : $options['error_text'];

    $error_class = "";
    if (\Defender::inputHasError($input_name)) {
        $error_class = " has-error";
        if (!empty($options['error_text'])) {
            $new_error_text = \Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }

            addnotice("danger", $options['error_text']);
        }
    }

    $min = ((!empty($options['min']) || $options['min'] === "0") && isnum($options['min']) ? "min='".$options['min']."' " : '');

    $max = ((!empty($options['max']) || $options['max'] === "0") && isnum($options['max']) ? "max='".$options['max']."' " : '');

    $step = $options['step'] ? "step='".$options['step']."' " : '';

    $html = "<div id='".$options['input_id']."-field' class='form-group dynamics-range-field".$error_class.($options['class'] ? ' '.$options['class'] : '')."'".($options['width'] ? " style='width: ".$options['width']."'" : '').">";

    $floating_label = $options['floating_label'] && !$options['inline'];
    $range_class = 'dynamics-range'.($floating_label ? ' dynamics-range--floating' : '').($options['inline'] ? ' dynamics-range--inline' : '');
    $minimum = is_numeric($options['min']) ? (float)$options['min'] : 0.0;
    $maximum = is_numeric($options['max']) ? (float)$options['max'] : 100.0;
    $numeric_value = is_numeric($input_value) ? (float)$input_value : $minimum;
    $progress = $maximum > $minimum ? (($numeric_value - $minimum) / ($maximum - $minimum)) * 100 : 0;
    $progress = max(0, min(100, $progress));
    $display_value = $options['display_percent'] ? (string)round($progress) : (string)$input_value;
    $value_unit = stripinput((string)$options['value_unit']);

    $html .= "<div class='{$range_class}' style='--dynamics-range-progress: ".round($progress, 4)."%;'>";
    $html .= "<div class='dynamics-range__header'>";
    $html .= ($label) ? "<label class='dynamics-range__label control-label' for='".$options['input_id']."'>".$options['label_icon'].$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')." ".dynamics_field_help($options['tip'])."</label>" : '<span></span>';
    $html .= "<div class='dynamics-range__reading'><output class='dynamics-range__value' for='".$options['input_id']."'><span id='".$options['input_id']."_pct'>".$display_value."</span></output>";
    $html .= $value_unit !== '' ? "<span class='dynamics-range__unit'>{$value_unit}</span>" : '';
    $html .= "</div></div>";
    $html .= "<input type='range' ".(!empty($options_data) ? implode(' ', $options_data) : '')." ".$min.$max.$step."class='form-range dynamics-range__input".($options['inner_class'] ? " ".$options['inner_class'] : '')."' ".($options['inner_width'] ? "style='width:".$options['inner_width'].";'" : '')." name='".$input_name."' id='".$options['input_id']."' value='".$input_value."'".($options['placeholder'] ? " placeholder='".$options['placeholder']."' " : '')." ".($options['deactivate'] ? 'disabled' : '').">";

    if ($options['max'] - $options['min'] && $options['range_buttons']) {

        $range = [
            ($options['max'] * 25 / 100),
            ($options['max'] * 50 / 100),
            ($options['max'] * 70 / 100),
            ($options['max'] * 100 / 100),
        ];

        $html .= '<div class="dynamics-range__presets d-flex flex-row gap-2">
        <button type="button" data-value="'.$range[0].'" class="btn btn-xs btn-range btn-default">25%</button>
        <button type="button" data-value="'.$range[1].'" class="btn btn-xs btn-range btn-default">50%</button>
        <button type="button" data-value="'.$range[2].'" class="btn btn-xs btn-range btn-default">75%</button>
        <button type="button" data-value="'.$range[3].'" class="btn btn-xs btn-range btn-default">Max</button>
        </div>';

    }

    add_to_jquery("
    (function () {
        const slider = document.getElementById('".$options['input_id']."');
        const output = document.getElementById('".$options['input_id']."_pct');
        const wrapper = slider ? slider.closest('.dynamics-range') : null;
        if (!slider || !output || !wrapper) return;
        const syncRange = function () {
            const min = Number(slider.min || 0);
            const max = Number(slider.max || 100);
            const value = Number(slider.value);
            const percent = max > min ? Math.max(0, Math.min(100, ((value - min) / (max - min)) * 100)) : 0;
            wrapper.style.setProperty('--dynamics-range-progress', percent + '%');
            output.textContent = ".($options['display_percent'] ? 'Math.round(percent)' : 'slider.value').";
        };
        slider.addEventListener('input', syncRange);
        wrapper.querySelectorAll('.btn-range').forEach(function (button) {
            button.addEventListener('click', function () {
                slider.value = button.dataset.value;
                slider.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });
        syncRange();
    }());
    ");


    $html .= $options['stacked'];
    $html .= '</div>';

    $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";

    $html .= (\Defender::inputHasError($input_name) ? "<div class='input-error".((!$options['inline'] || $options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : "");

    $html .= $options['append_html'];

    $html .= "</div>";

    // Add input settings in the SESSION
    \Defender::add_field_session([
        'input_name'     => clean_input_name($input_name),
        'title'          => clean_input_name($title),
        'id'             => $options['input_id'],
        'type'           => 'number',
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'callback_check' => $options['callback_check'],
        'descript'       => TRUE,
    ]);

    return dynamics_render_component_template('form_range', $html);
}
