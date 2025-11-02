<?php
function render_date_input($input_name, $input_value, $options = [])
{
    $input_id = $options['input_id'] ?? $input_name;
    $width_style = !empty($options['width']) ? ' style="width:' . $options['width'] . ';"' : '';
    $inner_width = !empty($options['inner_width']) ? ' style="width:' . $options['inner_width'] . ';"' : '';
    $placeholder = !empty($options['placeholder']) ? ' placeholder="' . htmlspecialchars($options['placeholder']) . '"' : '';

    // Optional icon display (on by default unless explicitly disabled)
    $icon_off = !empty($options['fieldicon_off']);
    $icon_html = '';
    if (!$icon_off) {
        $icon_html = '
            <span class="input-group-text">
                <i class="fa fa-calendar"></i>
            </span>';
    }

    $html = '
    <div id="' . $input_id . '_datepicker" class="input-group date"' . $width_style . '>
        <input type="datetime-local"
               name="' . htmlspecialchars($input_name) . '"
               id="' . htmlspecialchars($input_id) . '"
               class="form-control textbox"
               data-target="#' . htmlspecialchars($input_id) . '_datepicker"
               value="' . htmlspecialchars($input_value) . '"' . $inner_width . $placeholder . '>
        ' . $icon_html . '
    </div>';

    return $html;
}
