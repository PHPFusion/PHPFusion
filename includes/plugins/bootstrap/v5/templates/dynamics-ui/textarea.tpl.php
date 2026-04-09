<?php

/**
 * Render a textarea component with optional live toolbar and counter (Bootstrap 5 version)
 *
 * @param string $input_name
 * @param string $input_value
 * @param array $options
 * @return string
 */
function render_textarea_input($input_name, $input_value = '', array $options = [])
: string {
	// Determine if it should render the live editor (toolbar) layout
	//$is_live = !empty($options['html']) || (!empty($options['type']) && in_array($options['type'], ['html', 'bbcode'])) || !empty($options['bbcode']);
	return textarea($input_name, $input_value, $options);
//    return $is_live
//        ? live_textarea($input_name, $input_value, $options)
//        : basic_textarea($input_name, $input_value, $options);
}

require_once __DIR__.'/tiptap.tpl.php';

/**
 * Render a live textarea (with toolbar and counter)
 *
 * @param string $input_name
 * @param string $input_value
 * @param array $options
 * @return string
 */
function live_textarea($input_name, $input_value = '', array $options = [])
: string {
	$input_id = $options['input_id'] ?? uniqid('textarea_');
	$maxlength = $options['maxlength'] ?? NULL;
	
	return '
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
            <div>' . ($options['toolbar'] ?? '') . '</div>
            <div>' . ($options['toolbar_1'] ?? '') . '</div>
        </div>

        <div class="card-body p-0">
            ' . basic_textarea($input_name, $input_value, $options) . '
        </div>

        <div class="card-footer bg-body-tertiary py-2 px-3">
            <div class="d-flex justify-content-end small text-secondary">
                Word count: 
                <span id="' . $input_id . '-counter" class="ms-1 fw-semibold c-counter">0</span>' .
		($maxlength ? ' / ' . $maxlength : '') . '
            </div>
        </div>
    </div>';
}

/**
 * Render a basic Bootstrap 5 textarea (used by both live_textarea and textarea_input)
 *
 * @param string $input_name
 * @param string $input_value
 * @param array $options
 * @return string
 */
function basic_textarea($input_name, $input_value = '', array $options = [])
: string {
	$input_id = $options['input_id'] ?? uniqid('textarea_');
	$height = $options['height'] ?? '150px';
	$width = $options['inner_width'] ?? '100%';
	$rows = $options['rows'] ?? 4;
	$placeholder = $options['placeholder'] ?? '';
	$deactivate = !empty($options['deactivate']) ? 'readonly' : '';
	$maxlength = !empty($options['maxlength']) ? 'maxlength="' . htmlspecialchars($options['maxlength']) . '"' : '';
	$no_resize = !empty($options['no_resize']) ? 'resize: none;' : '';
	$autosize = !empty($options['autosize']) ? ' animated-height' : '';
	$inner_class = $options['inner_class'] ?? '';
	$bbcodeClass = !empty($options['bbcode_options']) ? ' rounded-0 border-0' : '';
	
	return '
    <textarea 
        name="' . htmlspecialchars($input_name) . '" 
        id="' . htmlspecialchars($input_id) . '"
        class="form-control ' . $inner_class . $bbcodeClass . $autosize . '"
        style="width:' . $width . '; height:' . $height . '; ' . $no_resize . '"
        rows="' . intval($rows) . '"
        placeholder="' . htmlspecialchars($placeholder) . '"
        ' . $deactivate . ' ' . $maxlength . '
    >' . htmlspecialchars($input_value) . '</textarea>';
}
