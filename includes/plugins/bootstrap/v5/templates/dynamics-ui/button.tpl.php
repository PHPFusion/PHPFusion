<?php

function render_button_input($input_name, $input_label, $input_value, $options = [])
{
    // Defaults
    $options = array_merge([
        'type' => 'button',   // or 'link'
        'class' => '',
        'icon' => '',
        'icon_class' => '',
        'input_id' => '',
        'deactivate' => false,
        'options_data' => [],
    ], $options);

    $disabled_class = $options['deactivate'] ? 'disabled ' : '';
    $disabled_attr  = $options['deactivate'] ? 'disabled="disabled"' : '';

    $icon = '';
    if (!empty($options['icon'])) {
        $icon_class = trim($options['icon_class'] ?? '');
        $icon = '<i class="' . htmlspecialchars($options['icon'] . ' ' . $icon_class) . '"></i>';
    }

    $data_attrs = '';
    if (!empty($options['options_data']) && is_array($options['options_data'])) {
        $data_attrs = implode(' ', array_map('htmlspecialchars', $options['options_data']));
    }

    // Link type
    if ($options['type'] === 'link') {
        $html = sprintf(
            '<a id="%s" title="%s" class="%sbtn %s button" href="%s" data-value="%s" %s %s>%s%s</a>',
            htmlspecialchars($options['input_id']),
            htmlspecialchars($input_label),
            $disabled_class,
            htmlspecialchars($options['class']),
            htmlspecialchars($input_name),
            htmlspecialchars($input_value),
            $data_attrs,
            $disabled_attr,
            $icon,
            htmlspecialchars($input_label)
        );

        // Button type
    } else {
        $btn_type = ($options['type'] === 'submit') ? 'submit' : 'button';

        $html = sprintf(
            '<button name="%s" type="%s" id="%s" title="%s" class="%sbtn %s button" data-value="%s" value="%s" %s %s>%s%s</button>',
            htmlspecialchars($input_name),
            htmlspecialchars($btn_type),
            htmlspecialchars($options['input_id']),
            htmlspecialchars($input_label),
            $disabled_class,
            htmlspecialchars($options['class']),
            htmlspecialchars($input_value),
            htmlspecialchars($input_value),
            $data_attrs,
            $disabled_attr,
            $icon,
            htmlspecialchars($input_label)
        );
    }

    return $html;
}
