<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Template;

class Form {

    public static function form_input($input_name, $label, $input_value, $options) {
        $info = array(
            "input_name"  => $input_name,
            "label"       => $label,
            "Input_value" => $input_value,
            "options"     => $options,
            "error"       => input_has_error($input_name)
        );
        return fusion_render(BOILERPLATES.'bootstrap4/html/', 'form-input.twig', $info, TRUE);
    }

    /**
     * Check for completion before commit
     *
     *
     * if (\Defender::inputHasError($input_name)) {
     * $tpl->set_block("error_message", [
     * 'error_class' => (!$options['inline'] ? ' display-block' : ''),
     * 'input_id'    => $options['input_id'],
     * 'error_text'  => $options['error_text']
     * ]);
     * }
     *
     */
    /**
     * Checkbox, Radio, Toggle Switch, Toggle Button
     *
     * @param $input_name
     * @param $label
     * @param $input_value
     * @param $options
     *
     * @return string
     */
    public static function form_checkbox($input_name, $label, $input_value, $options) {

        $info = array(
            "input_name"  => $input_name,
            "label"       => $label,
            "input_value" => $input_value,
            "options"     => $options,
            "error"       => input_has_error($input_name)
        );

        if ($options["type"] == "button") {
            if (!defined('btn-checkbox-js')) {
                define('btn-checkbox-js', TRUE);

                add_to_jquery("
        	$('.button-checkbox').each(function () {
            // Settings
            var widget = $(this),
            button = widget.find('button'),
            checkbox = widget.find('input:checkbox'),
            color = button.data('color'),
            settings = {
                on: {
                    icon: 'glyphicon glyphicon-check fa-fw'
                },
                off: {
                    icon: 'glyphicon glyphicon-unchecked fa-fw'
                }
            };
        // Event Handlers
        button.on('click', function () {
            checkbox.prop('checked', !checkbox.is(':checked'));
            checkbox.triggerHandler('change');
            updateDisplay();
        });
        checkbox.on('change', function () {
            updateDisplay();
        });
        // Actions
        function updateDisplay() {
            var isChecked = checkbox.is(':checked');
            // Set the button's state
            button.data('state', (isChecked) ? \"on\" : \"off\");
            // Set the button's icon
            button.find('.state-icon').removeClass().addClass('state-icon ' + settings[button.data('state')].icon);
            // Update the button's color
            if (isChecked) {
                button.removeClass('btn-default').addClass('' + color + ' active');
            } else {
                button.removeClass('' + color + ' active').addClass('btn-default');
            }
        }
        // Initialization
        function init() {
            updateDisplay();
            // Inject the icon if applicable
            if (button.find('.state-icon').length == 0) {
                button.prepend('<i class=\"state-icon ' + settings[button.data('state')].icon + ' \"></i>');
            }
        }
        init();
        });
        ");
            }
        }

        return fusion_render(BOILERPLATES.'bootstrap4/html/', 'form-input.twig', $info, TRUE);
    }

    /**
     * @param $input_name
     * @param $label
     * @param $input_value
     * @param $options
     *
     * @return string
     * @throws Exception
     */
    private static function form_input_deprecate($input_name, $label, $input_value, $options) {

        // form-group css class
        $grp_class = ($options['class'] ? ' '.$options['class'] : '');
        $grp_class .= ($options['inline'] ? ' clearfix' : '');

        // Bootstrap 4 doesn't have this - ?
        //$grp_class .= (!empty($options['icon']) ? ' has-feedback' : '');

        // Validation needs to be done via JS in Fusion X
        //$grp_class .= ($options['error_class'] ? ' '.$options['error_class'] : '');
        //
        //$tpl->set_tag("group_class", $grp_class);

        // form-group css style
        //$grp_inline = ($options['width'] && !$label ? ' style="width: '.$options['width'].'"' : '');
        //$tpl->set_tag("group_inline_css", $grp_inline);

        //$is_inline = $options['inline'] && $label ? TRUE : FALSE;
        // i want it false, then no need to push a grid.

        if ($label) {
            $control_label = [
                'label_grid'     => ($is_inline ? ' {[col(100,20,20,20)]}' : ' display-block'),
                'label_icon'     => ($options['label_icon']) ?: '',
                'label_text'     => $label,
                'label_required' => $options['required'] ? '<span class="required">*</span>' : '',
                'label_tip'      => ($options['tip'] ? ' <i class="pointer fa fa-question-circle" title="'.$options['tip'].'"></i>' : '')
            ];
            $tpl->set_block('control_label', $control_label);
        }

        if ($is_inline) {
            $tpl->set_block('inline_start');
            $tpl->set_block('inline_end');
        }

        $is_text_type = (in_array($options['type'], ['text', 'number', 'email', 'url', 'password']) ? TRUE : FALSE);
        $is_grouped = FALSE;

        if ($is_text_type) {
            // Type Text Field
            $tpl->set_block('input_text', [
                'input_data'   => $options['options_data'] ? implode(' ', $options['options_data']) : '',
                'min'          => $options['min'],
                'max'          => $options['max'],
                'step'         => $options['step'],
                'inner_class'  => ($options['inner_class'] ? " ".$options['inner_class']." " : ''),
                'inner_width'  => ($options['inner_width'] ? " style='width:".$options['inner_width'].";'" : ''),
                'max_length'   => $options['max_length'],
                'input_value'  => $input_value,
                'placeholder'  => ($options['placeholder'] ? $options['placeholder'] : ''),
                'autocomplete' => ($options['autocomplete_off'] ? ' autocomplete="off"' : ''),
                'readonly'     => $options['deactivate'] ? ' readonly' : '',
                'required'     => $options['required'] ? ' required' : '',
                'pwstrength'   => $options['password_strength'] ? '<div class="pwstrength_viewport_progress"></div>' : '' // do this for external plugin
            ]);

            if ($options['feedback_icon'] && $options['icon']) {
                $tpl->set_block("feedback", [
                    'icon' => $options['icon']
                ]);
            }

        } else if ($options['type'] == 'dropdown') {

            $config = [
                'input_name'     => $input_name,
                'input_id'       => $options['input_id'],
                'input_class'    => $options['input_class'],
                'input_width'    => $options['input_width'],
                'readonly'       => $options['deactivate'] ? ' disabled' : '',
                'onchange'       => $options['onchange'] ? ' onchange="'.$options['onchange'].'"' : '',
                'multiple'       => $options['multiple'] ? ' multiple' : '',
                'class'          => $options['select2_disabled'] == TRUE ? " class='form-control'" : "",
                'allowclear'     => $options['allowclear'],
                'parent_opts'    => $options['parent_opts'],
                'options'        => $options['options_options'],
                'required_input' => $options['dropdown_required_input'],
                'required'       => $options['required'] ? ' required' : '',
            ];

            $tpl->set_block('input_dropdown', $config);

        } else if ($options['type'] == 'custom') {
            $tpl->set_block('input_custom', [
                'input_field' => $options['input_field']
            ]);
        }


        if ($options['stacked']) {
            $tpl->set_block("stacked", ['content' => $options['stacked']]);
        }


        if (input_has_error($input_name)) {
            $tpl->set_block("error_message", [
                'error_class' => (!$is_inline or $is_grouped ? ' display-block' : ''),
                'input_id'    => $options['input_id'],
                'error_text'  => $options['error_text']
            ]);
        }

        if (!empty($options['append_html'])) {
            $tpl->set_block("append_html", ['content' => $options['append_html']]);
        }

        return $tpl->get_output();
    }
}
