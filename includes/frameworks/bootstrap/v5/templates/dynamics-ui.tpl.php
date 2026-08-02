<?php

/**
 * @param $args
 * @return string
 */
function render_dynamic_ui($args)
{

    $input_options = $args['input_options'];

    $template_type = $input_options['template_type'] ?? 'text';

    $input_name = $args['input_name'];

    $input_label = $args['input_label'];

    $input_value = $args['input_value'];

    $input_options = $args['input_options'];

    $html = '';

    // --- CASE: BUTTON TYPE ---
    if ($template_type === 'button') {

        require_once __DIR__.'/dynamics-ui/button.tpl.php';

        $html .= render_button_input($input_name, $input_label, $input_value, $input_options);
        return $html;
    }

    // --- TEXT-BASED TYPES ---
    $form_text = ['text', 'number', 'password', 'email', 'price', 'ip'];

    $ct_row_offset = (!empty($input_options['inline']) && !empty($input_label)) ? ' row' : '';
    $ct_icon_class = !empty($input_options['icon']) ? ' has-feedback' : '';
    $ct_width = (!empty($input_options['width']) && empty($input_label)) ? ' style="width:' . htmlspecialchars($input_options['width']) . ';"' : '';
    $checkbox_tip = '';
    $group_class = 'form-group';
    $ct_checkbox_class = '';
    $inline_start = $inline_end = '';
    $prepend_checkbox_input = $append_checkbox_input = '';
    $checkbox_label = '';
    $checkbox_style = '';
    
    // --- Floating label support ---
    if (!empty($input_options['floating_label']) && !empty($input_options['placeholder'])) {
        $group_class = 'form-floating';
    }

    // --- CHECKBOX TYPE ---
    if ($template_type === 'checkbox') {

        require_once __DIR__.'/dynamics-ui/checkbox.tpl.php';

        $group_class = 'form-check';

        if (!empty($input_options['options'])) {
            $group_class = '';
        }

        $checkbox_dom = checkbox_input($input_name, $input_label, $input_value, $input_options);

        $ct_checkbox_class = (!empty($input_options['toggle']) ? 'checkbox-switch ' : '') . 'check-group';

        $checkbox_label = ' data-checked="' . (!empty($input_value) ? '1' : '0') . '"';
        
        $checkbox_style = !empty($input_options['inner_width'])
            ? ' style="width:' . htmlspecialchars($input_options['inner_width']) . ';"'
            : '';

        if (!empty($input_options['reverse_label'])) {
            $prepend_checkbox_input = $checkbox_dom;
        } else {
            $append_checkbox_input = $checkbox_dom;
        }

        if (!empty($input_options['ext_tip'])) {
            $checkbox_tip = '<p class="small mb-0">' . $input_options['ext_tip'] . '</p>';
        }
    }

    // --- LABEL GENERATION ---
	// This label cannot be used in a checkbox as it requires inline build
    $label_dom = '';
    if (!empty($input_label) && $template_type != 'checkbox') {
    	
        if (!empty($input_options['inline'])) {
            $inline_start = '<div class="col-12 col-md-9 clearfix">';
            $inline_end = '</div>';
        }

        $required_mark = !empty($input_options['required']) ? '<span class="required">*</span>' : '';
        $ext_tip = !empty($input_options['tip'])
            ? '<i class="pointer fa fa-question-circle" title="' . htmlspecialchars($input_options['tip']) . '"></i>'
            : '';

        $label_class = 'form-label';
        if ($template_type === 'checkbox' && empty($input_options['options'])) {
            $label_class = 'form-check-label';
        }

        $label_dom = '<label for="' . htmlspecialchars($input_options['input_id'] ?? $input_name) . '" class="' . $label_class . '"' .
            $checkbox_label . $checkbox_style . '>' .
            ($input_options['label_icon'] ?? '') .
            $input_label .
            $required_mark . $ext_tip . $checkbox_tip .
            '</label>';

        $input_options['label_dom'] = $label_dom;
    }

    // --- WRAPPER OPEN ---
    $wrapper_classes = trim(
        $group_class . ' ' .
        $ct_row_offset . ' ' .
        $ct_checkbox_class . ' ' .
        ($input_options['error_class'] ?? '') . ' ' .
        ($input_options['class'] ?? '') . ' ' .
        $ct_icon_class
    );

    $html .= '<div id="' . htmlspecialchars($input_options['input_id'] ?? $input_name) . '-field" class="' . $wrapper_classes . '"' . $ct_width . '>';

    $html .= $prepend_checkbox_input;

    // Non-floating label goes before input
    if ($group_class !== 'form-floating') {
        $html .= $label_dom;
    }

    $html .= $inline_start;

    $html .= $append_checkbox_input;

    // --- INPUT TYPE HANDLING ---
    if (in_array($template_type, $form_text)) {

        require_once __DIR__.'/dynamics-ui/text.tpl.php';
        $html .= render_text_input($input_name, $input_label, $input_value, $input_options);

    } elseif ($template_type === 'dropdown') {

        require_once __DIR__.'/dynamics-ui/dropdown.tpl.php';
        $html .= render_dropdown_input($input_name, $input_value, $input_options);

    } elseif ($template_type === 'datepicker') {

        require_once __DIR__.'/dynamics-ui/date.tpl.php';
        $html .= render_date_input($input_name, $input_value, $input_options);

    } elseif ($template_type === 'colorpicker') {

        require_once __DIR__.'/dynamics-ui/color.tpl.php';
        $html .= render_color_input($input_name, $input_value, $input_options);

    } elseif ($template_type === 'hidden') {

        require_once __DIR__.'/dynamics-ui/hidden.tpl.php';
        $html .= render_hidden_input($input_name, $input_value, $input_options);

    } elseif ($template_type === 'textarea') {

        require_once __DIR__.'/dynamics-ui/textarea.tpl.php';
        $html .= render_textarea_input($input_name, $input_value, $input_options);

    } elseif ($template_type === 'button_group') {

        require_once __DIR__.'/dynamics-ui/button_group.tpl.php';
        $html .= render_btngroup_input($input_name, $input_value, $input_options);
    }

    $html .= $inline_end;
    $html .= '</div>';

    return $html;
}
