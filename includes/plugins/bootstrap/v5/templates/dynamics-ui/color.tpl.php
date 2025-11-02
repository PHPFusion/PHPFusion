<?php
function render_color_input($input_name, $input_value, $options = [])
{
    $input_id = $options['input_id'] ?? $input_name;
    $classes = 'form-control textbox';
    if (!empty($options['class'])) {
        $classes .= ' ' . htmlspecialchars($options['class']);
    }

    $style = !empty($options['inner_width'])
        ? ' style="width:' . htmlspecialchars($options['inner_width']) . ';"'
        : '';

    $placeholder = !empty($options['placeholder'])
        ? ' placeholder="' . htmlspecialchars($options['placeholder']) . '"'
        : '';

    $readonly = !empty($options['deactivate'])
        ? ' readonly'
        : '';

    $html = '
    <input type="text"
           data-jscolor="{}"
           name="' . htmlspecialchars($input_name) . '"
           id="' . htmlspecialchars($input_id) . '"
           class="' . $classes . '"
           value="' . htmlspecialchars($input_value) . '"' . $style . $placeholder . $readonly . '>';

    return $html;
}
