<?php

class Form {

    /**
     * Renders form input
     *
     * @param $input_name
     * @param $label
     * @param $input_value
     * @param $options
     *
     * @return string
     */
    public static function form_input($input_name, $label, $input_value, $options) {

        $tpl = \PHPFusion\Template::getInstance('field-'.$options['input_id']);
        $tpl->set_template(__DIR__.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'form_input.html');
        $tpl->set_tag("input_name", $input_name);

        // input id
        $tpl->set_tag("input_name", $input_name);
        $tpl->set_tag("input_id", $options['input_id']);
        $tpl->set_tag("input_type", $options['type']);
        // form-group css class
        $grp_class = ($options['inline'] ? ' overflow-hide' : '');
        $grp_class .= ($options['class'] ? ' '.$options['class'] : '');
        $grp_class .= (!empty($options['icon']) ? ' has-feedback' : '');
        $grp_class .= ($options['error_class'] ? ' '.$options['error_class'] : '');

        $tpl->set_tag("group_class", $grp_class);

        // form-group css style
        $grp_inline = ($options['width'] && !$label ? ' style="width: '.$options['width'].'"' : '');
        $tpl->set_tag("group_inline_css", $grp_inline);

        $is_inline = $options['inline'] && $label ? TRUE : FALSE;
        // i want it false, then no need to push a grid.

        if ($label) {
            $control_label = [
                'label_grid'     => ($is_inline ? ' {[col(100,100,20,20)]}' : ' display-block'),
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
            $is_grouped = ($options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? TRUE : FALSE;
            if ($is_grouped) {
                $tpl->set_block('group_start', [
                    'group_size'  => ($options['group_size'] ? ' input-group-'.$options['group_size'] : ''),
                    'group_width' => ($options['width'] ? ' style="width:'.$options['width'].'"' : '')
                ]);
                $tpl->set_block('group_end');
            }
            $is_prepend_button = $options['prepend_button'] && $options['prepend_type'] && $options['prepend_form_value'] && $options['prepend_class'] && $options['prepend_value'] ? TRUE : FALSE;
            $is_append_button = ($options['append_button'] && $options['append_type'] && $options['append_form_value'] && $options['append_class'] && $options['append_value'] ? TRUE : FALSE);
            if ($is_prepend_button) {
                $tpl->set_block('input_prepend_button', [
                    'prepend_id'    => $options['prepend_button_id'],
                    'prepend_name'  => $options['prepend_button_name'],
                    'prepend_type'  => $options['prepend_type'],
                    'prepend_value' => $options['prepend_form_value'],
                    'prepend_size'  => $options['prepend_size'] ? ' '.$options['prepend_size'] : '',
                    'prepend_class' => $options['prepend_class'] ? ' '.$options['prepend_class'] : '',
                    'prepend_text'  => $options['prepend_value'],
                ]);
            } else if ($options['prepend_value']) {
                $tpl->set_block('input_prepend', [
                    'prepend_id'   => $options['prepend_id'],
                    'prepend_text' => $options['prepend_value'],
                ]);
            }

            if ($is_append_button) {
                $tpl->set_block('input_append_button', [
                    'prepend_id'    => $options['append_button_id'],
                    'prepend_name'  => $options['append_button_name'],
                    'prepend_type'  => $options['append_type'],
                    'prepend_value' => $options['append_form_value'],
                    'prepend_size'  => $options['append_size'] ? ' '.$options['append_size'] : '',
                    'prepend_class' => $options['append_class'] ? ' '.$options['append_class'] : '',
                    'prepend_text'  => $options['append_value'],
                ]);
            } else if ($options['append_value']) {
                $tpl->set_block('input_append', [
                    'prepend_id'   => $options['append_id'],
                    'prepend_text' => $options['append_value'],
                ]);
            }
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
                'allowclear'     => $options['allowclear'],
                'parent_opts'    => $options['parent_opts'],
                'options'        => $options['options_options'],
                'required_input' => $options['dropdown_required_input']
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
        if ($options['ext_tip']) {
            $tpl->set_block("tip", ['tip_text' => $options['ext_tip']]);
        }

        if (\Defender::inputHasError($input_name)) {
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
