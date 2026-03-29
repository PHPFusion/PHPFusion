<?php

/**
 * Render a Bootstrap 5 dropdown (select or JSON/tags input)
 *
 * @param string $input_name
 * @param string $input_value
 * @param array $options
 * @return string
 */
function render_dropdown_input($input_name, $input_value = '', array $options = []): string
{
    $input_id     = $options['input_id'] ?? uniqid('select_');
    $input_label = !empty($options['input_label']) ?: '';
    $inner_width  = !empty($options['inner_width']) ? 'style="width:' . htmlspecialchars($options['inner_width']) . ';"' : '';
    $inner_class  = $options['inner_class'] ?? '';
    $is_required  = !empty($options['required']) ? 'required' : '';
    $is_disabled  = !empty($options['deactivate']) ? 'disabled' : '';
    $placeholder  = !empty($options['placeholder']) ? 'placeholder="' . htmlspecialchars($options['placeholder']) . '"' : '';
    $autocomplete = !empty($options['autocomplete_off']) ? 'autocomplete="off"' : '';
    $onchange     = !empty($options['onchange']) ? 'onchange="' . htmlspecialchars($options['onchange']) . '"' : '';
    $data_options = !empty($options['data_options']) ? implode(' ', (array) $options['data_options']) : '';
    $input_error  = !empty($options['input_error']) ? 'is-invalid' : '';
    
//    $input_value  = htmlspecialchars($options['input_value'] ?? '');
    $input_value = htmlspecialchars($input_value);

    $options_html = $options['options_html'] ?? '';

    // --- SELECT2 / JSON MODE / TAGS MODE ---
    if (!empty($options['jsonmode']) || !empty($options['tags'])) {
        $dropdown_class = 'form-select';
        $spinner = '
            <div id="' . $input_id . '-spinner" class="text-center my-2" style="display:none;">
                <img src="' . fusion_get_settings("siteurl") . '/images/loader.svg" alt="">
            </div>
        ';

        return '
        <div class="d-block position-relative">
            ' . $spinner . '
            <input 
                type="hidden"
                name="' . htmlspecialchars($input_name) . '"
                id="' . htmlspecialchars($input_id) . '"
                class="' . $dropdown_class . ' ' . $inner_class . ' ' . $input_error . '"
                value="' . $input_value . '"
                ' . $inner_width . ' 
                ' . $placeholder . '
                ' . $autocomplete . '
                ' . $is_disabled . '
                ' . $is_required . '
            >
            ' . ($is_required ? '<input class="required" id="dummy-' . $input_id . '" type="hidden">' : '') . '
        </div>';
    }

    // --- NORMAL SELECT MODE ---
    $select2_disabled = $options['select2_disabled'] ?? false;
    $class = $select2_disabled ? 'form-select' : '';
    $multiple = !empty($options['multiple']) ? 'multiple' : '';

    $label_attr = $input_label ? 'aria-label="' . htmlspecialchars($input_label) . '"' : '';

    return '
        <select
            name="' . htmlspecialchars($input_name) . '"
            id="' . htmlspecialchars($input_id) . '"
            class="mb-3 ' . $class . ' ' . $inner_class . ' ' . $input_error . '"
            ' . $inner_width . '
            ' . $placeholder . '
            ' . $autocomplete . '
            ' . $onchange . '
            ' . $data_options . '
            ' . $is_disabled . '
            ' . $multiple . '
            ' . $is_required . '
            ' . $label_attr . '
        >
            ' . $options_html . '
        </select>
        ' . ($is_required ? '<input class="required" id="dummy-' . $input_id . '" type="hidden">' : '') . '
    ';
}
