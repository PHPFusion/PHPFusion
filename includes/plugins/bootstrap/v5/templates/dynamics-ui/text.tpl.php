<?php

function render_text_input($input_name, $input_label, $input_value, $options = [])
{
    // --- Defaults ---
    $options = array_merge([
        'input_id' => '',
        'input_type' => 'text',
        'inner_class' => '',
        'group_size' => '', // e.g. 'input-group-sm'
        'placeholder' => '',
        'required' => false,
        'deactivate' => false,
        'autocomplete_off' => false,
        'floating_label' => false,
        'prepend_button' => false,
        'prepend_value' => '',
        'prepend_id' => '',
        'prepend_type' => '',
        'prepend_form_value' => '',
        'prepend_class' => '',
        'append_button' => false,
        'append_value' => '',
        'append_id' => '',
        'append_type' => '',
        'append_form_value' => '',
        'append_class' => '',
        'append_html' => '',
        'feedback_icon' => false,
        'icon' => '',
        'input_error' => false,
        'error_text' => '',
        'ext_tip' => '',
        'stacked' => '',
        'password_strength' => false,
    ], $options);

    // --- Validation feedback ---
    $errorText = '';
    $errorClass = '';
    if (!empty($options['input_error'])) {
        $errorClass = ' is-invalid';
        $errorText = sprintf(
            '<div id="%s-help" class="invalid-feedback d-block">%s</div>',
            htmlspecialchars($options['input_id']),
            $options['error_text']
        );
    }

    // --- Input attributes ---
    $attr = [];
    if (!empty($options['placeholder'])) $attr[] = 'placeholder="' . htmlspecialchars($options['placeholder']) . '"';
    if (!empty($options['required'])) $attr[] = 'required';
    if (!empty($options['deactivate'])) $attr[] = 'readonly';
    if (!empty($options['autocomplete_off'])) $attr[] = 'autocomplete="off"';
    $attr_str = implode(' ', $attr);

    // --- Input group open (Bootstrap 5 uses .input-group + optional size) ---
    $has_group = (
        !empty($options['append_button']) ||
        !empty($options['prepend_button']) ||
        !empty($options['append_value']) ||
        !empty($options['prepend_value'])
    );

    $html = '';
    if ($has_group) {
        $html .= '<div class="input-group ' . htmlspecialchars($options['group_size']) . '">';
    }

    // --- Prepend (text or button) ---
    if (!empty($options['prepend_button']) && $options['prepend_value']) {
        $html .= sprintf(
            '<span class="input-group-text">
                <button id="%s" name="%s" type="%s" value="%s" class="btn %s">%s</button>
            </span>',
            htmlspecialchars($options['prepend_button_id'] ?? ''),
            htmlspecialchars($options['prepend_button_name'] ?? ''),
            htmlspecialchars($options['prepend_type']),
            htmlspecialchars($options['prepend_form_value']),
            htmlspecialchars(trim(($options['prepend_size'] ?? '') . ' ' . ($options['prepend_class'] ?? ''))),
            $options['prepend_value']
        );
    } elseif (!empty($options['prepend_value'])) {
        $html .= sprintf(
            '<span class="input-group-text" id="%s">%s</span>',
            htmlspecialchars($options['prepend_id']),
            $options['prepend_value']
        );
    }

    // --- Floating label start ---
    if (!empty($options['floating_label']) && !empty($options['placeholder'])) {
        $html .= '<div class="form-floating">';
    }

    // --- Input field ---
    $html .= sprintf(
        '<input type="%s" data-type="%s" class="form-control bg-light %s%s" name="%s" id="%s" value="%s" %s>',
        htmlspecialchars($options['input_type']),
        htmlspecialchars($options['input_type']),
        htmlspecialchars(trim('textbox ' . $options['inner_class'])),
        $errorClass,
        htmlspecialchars($input_name),
        htmlspecialchars($options['input_id']),
        htmlspecialchars($input_value),
        $attr_str
    );

    // --- Floating label end ---
    if (!empty($options['floating_label']) && !empty($options['placeholder'])) {
        $html .= $options['label_dom'] ?? '';
        $html .= '</div>';
    }

    // --- Append (text or button) ---
    if (!empty($options['append_button']) && $options['append_value']) {
        $html .= sprintf(
            '<span class="input-group-text">
                <button id="%s" name="%s" type="%s" value="%s" class="btn %s">%s</button>
            </span>',
            htmlspecialchars($options['append_button_id'] ?? ''),
            htmlspecialchars($options['append_button_name'] ?? ''),
            htmlspecialchars($options['append_type']),
            htmlspecialchars($options['append_form_value']),
            htmlspecialchars(trim(($options['append_size'] ?? '') . ' ' . ($options['append_class'] ?? ''))),
            $options['append_value']
        );
    } elseif (!empty($options['append_value'])) {
        $html .= sprintf(
            '<span class="input-group-text" id="%s">%s</span>',
            htmlspecialchars($options['append_id']),
            $options['append_value']
        );
    }

    // --- Feedback icon (Bootstrap 5 has no .form-control-feedback) ---
    if (!empty($options['feedback_icon']) && !empty($options['icon'])) {
        $html .= sprintf(
            '<div class="position-absolute top-0 end-0 translate-middle-y pe-2"><i class="%s"></i></div>',
            htmlspecialchars($options['icon'])
        );
    }

    // --- Close group ---
    if ($has_group) {
        $html .= '</div>';
    }

    // --- Additional content ---
    if (!empty($options['stacked'])) {
        $html .= $options['stacked'];
    }

    if (!empty($options['ext_tip'])) {
        $html .= '<span class="form-text">' . $options['ext_tip'] . '</span>';
    }

    $html .= $errorText;
    $html .= $options['append_html'];

    if (!empty($options['password_strength'])) {
        $html .= '<div class="mt-2 pwstrength_viewport_progress"></div>';
    }

    return $html;
}
