<?php

function checkbox_input($input_name, $input_label, $input_value, $options = [], $nested = false)
{
    $html = '';

    // If multiple options are provided
    if (!empty($options['options']) && count($options['options']) > 0) {
        foreach ($options['options'] as $key => $value) {
            $deactivation = isset($options['deactivate_key']) && $options['deactivate_key'] == $key;
            $checked = isset($input_value[$key]) && $input_value[$key] ? 'checked' : '';
            $disabled = $deactivation ? 'disabled' : '';
            $onclick = !empty($options['onclick']) ? 'onclick="' . $options['onclick'] . '"' : '';

            $checkbox_input_id = $options['input_id'] . '-' . $key;

            if ($deactivation) {
                $html .= '<input type="hidden" name="' . $input_name . '" id="' . $key . '">';
            }

            // Determine if inline
            $inline_class = !empty($options['inline_options']) ? ' form-check-inline me-2' : '';

            $html .= '
            <div class="form-check' . $inline_class . '">
                <input class="form-check-input" id="' . $checkbox_input_id . '" 
                    name="' . $input_name . '" 
                    value="' . htmlspecialchars($key) . '" 
                    type="' . htmlspecialchars($options['type'] ?? 'checkbox') . '" 
                    ' . $checked . ' ' . $disabled . ' ' . $onclick . '>
                <label class="form-check-label me-3" for="' . $checkbox_input_id . '">' . $value . '</label>
            </div>';
        }
    } else {
        // Single checkbox/radio
        $checked = isset($input_value) && $input_value == ($options['value'] ?? '') ? 'checked' : '';
        $disabled = !empty($options['deactivate']) ? 'disabled' : '';
        $onclick = !empty($options['onclick']) ? 'onclick="' . $options['onclick'] . '"' : '';
        $toggle = !empty($options['toggle']) && $options['toggle'] == 1;

        $float_class = $toggle ? ' float-end' : (!empty($input_label) ? ' float-start me-2' : '');

        $html .= '
        <div class="form-check' . $float_class . '">
            <input name="' . $input_name . '" 
                id="' . htmlspecialchars($options['input_id'] ?? $input_name) . '" 
                class="form-check-input" 
                value="' . htmlspecialchars($options['value'] ?? '1') . '" 
                type="' . htmlspecialchars($options['type'] ?? 'checkbox') . '" 
                ' . $checked . ' ' . $disabled . ' ' . $onclick . '>';

//        if (!empty($input_label)) {
//            $html .= '<label class="form-check-label" for="' . htmlspecialchars($options['input_id'] ?? $input_name) . '">' . $input_label . '</label>';
//        }

        $html .= '</div>';
    }

    return $html;
}
