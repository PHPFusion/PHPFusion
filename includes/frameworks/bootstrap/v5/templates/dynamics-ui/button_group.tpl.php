<?php

function render_btngroup_input($input_name, $input_value, $input_options = [])
{
    // Defaults
    $input_options = array_merge([
        'input_id' => '',
        'options' => [],
        'type' => 'button',        // or 'submit'
        'btn_class' => 'btn-outline-primary',
    ], $input_options);

    // Begin wrapper (Bootstrap 5: use `d-block` instead of custom `display-block`)
    $html = '<div class="d-block">';
    $html .= '<div class="btn-group" role="group" id="' . htmlspecialchars($input_options['input_id']) . '">';

    if (!empty($input_options['options']) && is_array($input_options['options'])) {
        $option_count = count($input_options['options']);
        $i = 1;

        foreach ($input_options['options'] as $arr => $v) {
            $is_last = ($option_count == $i);
            $child_class = $is_last ? 'rounded-end ' : ''; // Bootstrap 5 uses `rounded-end` for right corner
            $active_class = ($input_value == $arr) ? ' active' : '';
            $child_type = ($input_options['type'] === 'submit') ? 'submit' : 'button';

            $html .= sprintf(
                '<button name="%s__%s" type="%s" data-value="%s" value="%s" class="btn %s %s%s">%s</button>',
                htmlspecialchars($input_name),
                htmlspecialchars($arr),
                htmlspecialchars($child_type),
                htmlspecialchars($arr),
                htmlspecialchars($arr),
                htmlspecialchars($input_options['btn_class']),
                $child_class,
                $active_class,
                $v // raw HTML allowed for label
            );

            $i++;
        }
    }

    $html .= '</div>';
    $html .= sprintf(
        '<input name="%s" type="hidden" id="%s-text" value="%s">',
        htmlspecialchars($input_name),
        htmlspecialchars($input_options['input_id']),
        htmlspecialchars($input_value)
    );
    $html .= '</div>';

    return $html;
}
