<?php
function render_hidden_input($input_name, $input_value, $options = [])
{
    $input_id = $options['input_id'] ?? $input_name;
    $width_style = !empty($options['width']) && !empty($options['inner_width'])
        ? ' style="width:' . htmlspecialchars($options['inner_width']) . ';"'
        : '';
    $readonly = !empty($options['deactivate']) ? ' readonly' : '';

    $html = '<input type="hidden" name="' . htmlspecialchars($input_name) . '" id="' . htmlspecialchars($input_id) . '" value="' . htmlspecialchars($input_value) . '"' . $width_style . $readonly . '>';

    return $html;
}
