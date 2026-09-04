<?php

function load_form_checkbox_assets(): void
{
	static $loaded = FALSE;

	if (!$loaded) {
		// Refresh the component after a rebuild, including development-mode pages.
		fusion_load_script(DYNAMICS.'includes/form_checkbox/component.css?v='.filemtime(__DIR__.'/component.css'), 'css');
		$loaded = TRUE;
	}
}

/**
 * @param        $input_name
 * @param string $label
 * @param string $input_value
 * @param array $options
 *
 * @return string
 */
function form_checkbox($input_name, $label = '', $input_value = '0', array $options = [])
{
	load_form_checkbox_assets();
	$locale = fusion_get_locale('', LOCALE . LOCALESET . 'global.php');
	$has_label_reverse = array_key_exists('label_reverse', $options);
	$has_reverse_label = array_key_exists('reverse_label', $options);
	$has_box_direction = array_key_exists('box_direction', $options);
	$has_hyphenated_box_direction = array_key_exists('box-direction', $options);

	$options += [
		'input_id'       => $input_name,
		'inline'         => FALSE,
		'floating_label' => FALSE,
		'inline_options' => FALSE,
		'required'       => FALSE,
		'deactivate'     => FALSE,
		'label_class'    => '',
		'class'          => '',
		'type'           => 'checkbox',
		'multiple'       => FALSE,
		'max_select'     => 10,
		'toggle'         => FALSE,
		'options'        => [],
		'options_value'  => [],
		'delimiter'      => ',',
		'safemode'       => FALSE,
		'keyflip'        => FALSE,
		'error_text'     => $locale['error_input_checkbox'] ?? '',
		'value'          => 1,
		'tip'            => '',
		'ext_tip'        => '',
		'inner_text'     => '',
		'inner_width'    => '',
		'inner_class'    => 'd-inline-block',
		'tag_indicator'  => '',
		'label_reverse'  => FALSE,
		'reverse_label'  => FALSE,
		'box_direction'  => 'row',
		'deactivate_key' => NULL,
		'onclick'        => '',
	];
	$options['label_reverse'] = $has_label_reverse
		? (bool)$options['label_reverse']
		: ($has_reverse_label ? (bool)$options['reverse_label'] : FALSE);
	$options['reverse_label'] = $options['label_reverse'];
	// Keep the presentation flag compatible with the established Box type.
	if (!empty($options['box_options'])) {
		$options['type'] = 'box';
	}
	$box_direction = $has_box_direction
		? strtolower(trim((string)$options['box_direction']))
		: ($has_hyphenated_box_direction ? strtolower(trim((string)$options['box-direction'])) : 'row');
	$options['box_direction'] = in_array($box_direction, ['row', 'column'], TRUE) ? $box_direction : 'row';
	if ($options['ext_tip'] !== '') {
        $options['ext_tip'] = dynamics_field_help($options['ext_tip'], TRUE);
    }
	$options['delimiter'] = (string)$options['delimiter'] !== '' ? (string)$options['delimiter'] : ',';

	$input_name_var = clean_input_name($input_name);
	if (check_post($input_name_var)) {
		$input_value = $options['multiple']
			? post([$input_name_var])
			: post($input_name_var);
	}
	
	$real_input_name = $input_name;
	if ($options['multiple'] && !str_contains($input_name, '[]')) {
		$real_input_name = $input_name . '[]';
	}
	
	if ($options['multiple']) {
		$input_value = dynamics_multiple_value_list(clean_input_value($input_value), $options['delimiter']);
	}
	
	$options['toggle'] = ($options['type'] == 'toggle');
	
	$switch_css_1 = '';
	$switch_css_2 = '';
	if ($options['toggle']) {
//		$options['class'] .= ' form-switch';
		$switch_css_1 = 'ps-0 form-switch';
		$switch_css_2 = 'w-100';
	}
	
	$title = ($label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name))));
	
	// Sanitize ID for HTML/JS compatibility
	$options['input_id'] = trim(str_replace(['[', ']'], ['_', ''], $options['input_id']), "-");
	
	$error_class = '';
	if (class_exists('Defender') && Defender::inputHasError($input_name)) {
		$error_class = " has-error";
	}
	
	$has_multiple_options = !empty($options['options']) && is_array($options['options']);
	$should_use_inline = $options['inline'] && $label && $has_multiple_options;
	$deactivated_keys = (array)$options['deactivate_key'];
	$reverse_class = $options['label_reverse'] ? ' flex-row-reverse dynamics-choice--reverse' : '';
	$inner_texts = is_array($options['inner_text']) ? $options['inner_text'] : [];
	$single_inner_text = is_array($options['inner_text']) ? '' : trim((string)$options['inner_text']);
	
	$checkbox = '';
	if ($has_multiple_options) {
		
		$is_box = $options['type'] === 'box';
		$is_tag = $options['type'] === 'tag';
		$input_type = ($is_box || $is_tag) ? ($options['multiple'] ? 'checkbox' : 'radio') : ($options['type'] === 'radio' ? 'radio' : 'checkbox');
		$input_class = ($input_type == 'checkbox') ? 'check' : $input_type;
		$opt_count = count($options['options']);
		
		if ($options['toggle'] && $opt_count === 2) {
			// 1. Sort the options by key (0, 1, 2...)
			ksort($options['options']);
			$keys = array_keys($options['options']);
			
			// Key 0 is OFF, Key 1 is ON
			$off_key = $keys[0];
			$on_key = $keys[1];
			
			$off_label = $options['options'][$off_key];
			$on_label = $options['options'][$on_key];
			
			// Check if the current value matches the ON state
			$is_checked = ((string)$input_value === (string)$on_key);
			
			$input_id = $options['input_id'];
			$disabled = $options['deactivate'] ? 'disabled' : '';
			$onclick = $options['onclick'] ? ' onclick="' . $options['onclick'] . '"' : '';
			
			// Render ONLY ONE toggle and skip the loop
			$checkbox .= "<div class='form-check form-switch form-switch-lg dynamics-choice{$reverse_class}'>";
			$checkbox .= "<input type='checkbox' name='{$real_input_name}' id='{$input_id}' value='{$on_key}' class='form-check-input' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">";
			
			// first one is form-check-label-on , need to sort the key first.
			$on_inner_text = array_key_exists($on_key, $inner_texts) ? trim((string)$inner_texts[$on_key]) : '';
			$off_inner_text = array_key_exists($off_key, $inner_texts) ? trim((string)$inner_texts[$off_key]) : '';
			$checkbox .= "<span class='dynamics-choice__content dynamics-choice__state form-check-label-on'>";
			$checkbox .= "<label class='form-check-label dynamics-choice__label' for='{$input_id}'><span class='dynamics-choice__option-title'>" . stripinput($on_label) . "</span>";
			$checkbox .= $on_inner_text !== '' ? "<span class='dynamics-choice__description'>{$on_inner_text}</span>" : '';
			$checkbox .= "</label></span>";
			$checkbox .= "<span class='dynamics-choice__content dynamics-choice__state form-check-label-off'>";
			$checkbox .= "<label class='form-check-label dynamics-choice__label' for='{$input_id}'><span class='dynamics-choice__option-title'>" . stripinput($off_label) . "</span>";
			$checkbox .= $off_inner_text !== '' ? "<span class='dynamics-choice__description'>{$off_inner_text}</span>" : '';
			$checkbox .= "</label></span>";
			
			$checkbox .= "</div>";
			
		} else {
			
			
			foreach ($options['options'] as $key => $value) {
				// Box labels accept [title, trailing text]; scalar labels remain supported.
				$option_trailing = is_array($value) ? htmlspecialchars((string)($value[1] ?? ''), ENT_QUOTES, 'UTF-8') : '';
				$value = is_array($value) ? htmlspecialchars((string)($value[0] ?? ''), ENT_QUOTES, 'UTF-8') : $value;
				$option_inner_text = array_key_exists($key, $inner_texts) ? trim((string)$inner_texts[$key]) : '';
				
				if (is_array($input_value)) {
					$is_checked = in_array((string)$key, array_map('strval', $input_value));
				} else {
					$is_checked = (strlen((string)$input_value) > 0 && (string)$input_value === (string)$key);
				}
				
				$input_id = $options['input_id'] . "-$key";
				$is_disabled = ($options['deactivate'] || in_array($key, $deactivated_keys));
				$disabled = $is_disabled ? 'disabled' : '';
				$disabled_class = $is_disabled ? ' opacity-50 pointer-events-none' : '';
				$onclick = $options['onclick'] ? ' onclick="' . $options['onclick'] . '"' : '';
				$opt_count = count($options['options']);
				
				if ($is_box) {
					
					$active_class = $is_checked ? ' active' : ' border-light-subtle';
					
					$checkbox .= "
					<label for='{$input_id}' class='form-check-label box-option{$disabled_class}'>
						<input type='{$input_type}' name='{$real_input_name}' id='{$input_id}' value='{$key}' class='form-check-input' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">
						<span class='box-option__content'><span class='dynamics-choice__option-title'>{$value}</span>" .
						($option_inner_text !== '' ? "<span class='dynamics-choice__description'>{$option_inner_text}</span>" : '') . "</span>" .
						($input_type === 'radio' && $is_checked ? "<span class='box-option__current'>Current</span>" : '') .
						($option_trailing !== '' ? "<span class='box-option__trailing'>{$option_trailing}</span>" : '') . "</label>";
					
				} elseif ($is_tag) {
					
					$active_class = $is_checked ? ' active tag-option--on' : '';
					$indicator = $options['tag_indicator'] ?: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
					
					$checkbox .= "
					<input type='{$input_type}' name='{$real_input_name}' id='{$input_id}' value='{$key}' class='d-none' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">
					<label for='{$input_id}' class='form-check-label tag-option {$options['inner_class']}{$disabled_class}{$active_class}' style='cursor:pointer;' role='{$input_type}' aria-checked='" . ($is_checked ? 'true' : 'false') . "'>
						<span class='tag-option-indicator'>{$indicator}</span>
						<span class='tag-option-label'><span class='dynamics-choice__option-title'>{$value}</span>" .
						($option_inner_text !== '' ? "<span class='dynamics-choice__description'>{$option_inner_text}</span>" : '') . "</span>
					</label>";
					
				} else {
					$checkbox .= "<div class='dynamics-choice d-flex gap-2{$reverse_class}" . ($options['inline_options'] ? ' dynamics-choice--inline' : '') . "'>";
					$checkbox .= "<input id='{$input_id}' name='{$real_input_name}' value='{$key}' type='{$input_type}' class='form-check-input' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . " />";
					$checkbox .= "<div class='dynamics-choice__content'" . ($options['inner_width'] ? " style='width:{$options['inner_width']}'" : '') . ">";
					$checkbox .= "<label class='form-check-label dynamics-choice__label' for='{$input_id}'><span class='dynamics-choice__option-title'>{$value}</span>";
					$checkbox .= $option_inner_text !== '' ? "<span class='dynamics-choice__description'>{$option_inner_text}</span>" : '';
					$checkbox .= "</label></div></div>";
				}
			}
		}
	} else {
		
		// Single Input Logic
		$is_box = $options['type'] === 'box';
		$is_tag = $options['type'] === 'tag';
		$is_checked = ((string)$input_value === (string)$options['value']);
		$input_id = $options['input_id'];
		$is_disabled = $options['deactivate'] || in_array($input_id, $deactivated_keys);
		$disabled = $is_disabled ? 'disabled' : '';
		$disabled_class = $is_disabled ? ' opacity-50 pointer-events-none' : '';
		$onclick = $options['onclick'] ? ' onclick="' . $options['onclick'] . '"' : '';
		
		if ($is_box) {
			$active_class = $is_checked ? ' active' : ' border-light-subtle';
			$checkbox .= "
            <label for='{$input_id}' class='form-check-label box-option{$disabled_class}'>
                <input type='checkbox' name='{$real_input_name}' id='{$input_id}' value='{$options['value']}' class='form-check-input' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">
				<span class='box-option__content'><span class='dynamics-choice__option-title'>{$label}</span>" .
				($single_inner_text !== '' ? "<span class='dynamics-choice__description'>{$single_inner_text}</span>" : '') . "</span>
            </label>";
		} elseif ($is_tag) {
			$active_class = $is_checked ? ' active tag-option--on' : '';
			$indicator = $options['tag_indicator'] ?: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
			$checkbox .= "
            <input type='checkbox' name='{$real_input_name}' id='{$input_id}' value='{$options['value']}' class='d-none' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">
            <label for='{$input_id}' class='form-check-label tag-option {$options['inner_class']}{$disabled_class}{$active_class}' style='cursor:pointer;' role='checkbox' aria-checked='" . ($is_checked ? 'true' : 'false') . "'>
                <span class='tag-option-indicator'>{$indicator}</span>
				<span class='tag-option-label'><span class='dynamics-choice__option-title'>{$label}</span>" .
				($single_inner_text !== '' ? "<span class='dynamics-choice__description'>{$single_inner_text}</span>" : '') . "</span>
            </label>";
		} elseif ($options['toggle']) {
			
			// Single Option Toggle Logic
			// We check against $options['value'] (defaults to 1)
			$is_checked = ((string)$input_value === (string)$options['value']);
			
			$input_id = $options['input_id'];
			$disabled = $options['deactivate'] ? 'disabled' : '';
			$onclick = $options['onclick'] ? ' onclick="' . $options['onclick'] . '"' : '';
			
			// Render ONLY ONE toggle
			$checkbox .= "<div class='form-check form-switch form-switch-lg dynamics-choice{$reverse_class}'>";
			$checkbox .= "<input type='checkbox' name='{$real_input_name}' id='{$input_id}' value='{$options['value']}' class='form-check-input' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">";
			
			// Keep toggle identity and help together, with descriptive copy below.
			if ($label || $single_inner_text !== '') {
				$checkbox .= "<div class='dynamics-choice__content'>";
				if ($label) {
					$checkbox .= "<div class='dynamics-choice__title'><label class='form-check-label dynamics-choice__label' for='{$input_id}'><span class='dynamics-choice__option-title'>" . stripinput($label) .
						($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') . "</span>" .
						($single_inner_text !== '' ? "<span class='dynamics-choice__description'>{$single_inner_text}</span>" : '') .
						"</label>" . dynamics_field_help($options['tip']) . "</div>";
				} elseif ($single_inner_text !== '') {
					$checkbox .= "<label class='form-check-label dynamics-choice__label' for='{$input_id}'><span class='dynamics-choice__description'>{$single_inner_text}</span></label>";
				}
				$checkbox .= "</div>";
			}
			
			$checkbox .= "</div>";
			
		} else {
			$checkbox .= "<div class='dynamics-choice d-flex gap-2{$reverse_class}'>";
			$checkbox .= "<input id='{$input_id}' class='form-check-input' name='{$real_input_name}' value='{$options['value']}' type='{$options['type']}' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">";
			if ($label || $single_inner_text !== '') {
				$checkbox .= "<div class='dynamics-choice__content'>";
				if ($label) {
					$checkbox .= "<div class='dynamics-choice__title'><label class='form-check-label dynamics-choice__label' for='{$input_id}'><span class='dynamics-choice__option-title'>{$label}" .
						($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') . "</span>" .
						($single_inner_text !== '' ? "<span class='dynamics-choice__description'>{$single_inner_text}</span>" : '') .
						"</label>" . dynamics_field_help($options['tip']) . "</div>";
				} elseif ($single_inner_text !== '') {
					$checkbox .= "<label class='form-check-label dynamics-choice__label' for='{$input_id}'><span class='dynamics-choice__description'>{$single_inner_text}</span></label>";
				}
				$checkbox .= "</div>";
			}
			$checkbox .= "</div>";
		}
	}
	
	// Prepare Data Attributes for the Global JS
	$data_attrs = "";
	if ($options['type'] === 'box' || $options['type'] === 'tag') {
		$data_attrs = " data-multiple='" . ($options['multiple'] ? '1' : '0') . "' data-max-select='{$options['max_select']}'";
		$options['class'] .= ' js-checkbox-box-container';
	}
	
	// HTML Wrapper
	$html = "<div id='{$options['input_id']}-field' class='dynamics-choice-field w-100" .
		($should_use_inline ? ' row' : '') .
		(!empty($error_class) ? $error_class : '') .
		(trim($options['class']) ? ' ' . $options['class'] : '') . "'{$data_attrs}>";
	
	if ($has_multiple_options && !empty($label)) {
		$html .= "<div class='dynamics-choice__group-label form-label {$switch_css_1} {$options['label_class']} " . ($should_use_inline ? 'col-sm-12 col-md-3 col-lg-3' : '') . "' id='{$options['input_id']}-label'>" .
			$label . ($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') .
			dynamics_field_help($options['tip']) . "</div>";
	}
	
	$html .= $should_use_inline ? "<div class='col-sm-12 col-md-9 col-lg-9'>" : "";
	if ($has_multiple_options) {
		if ($options['type'] === 'box') {
			$options_layout = ' dynamics-choice__options--box-' . $options['box_direction'];
			$options_reverse = $options['label_reverse']
				? ($options['box_direction'] === 'column' ? ' flex-column-reverse' : ' flex-row-reverse')
				: '';
		} else {
			$options_layout = ($options['inline_options'] || $options['type'] === 'tag') ? ' dynamics-choice__options--inline' : '';
			$options_reverse = $options['label_reverse'] && $options['type'] === 'tag' ? ' flex-row-reverse' : '';
		}
		$checkbox = "<div class='dynamics-choice__options{$options_layout}{$options_reverse}' role='group' aria-labelledby='{$options['input_id']}-label'>{$checkbox}</div>";
	}
	$html .= $options['floating_label'] && !$options['inline'] ? "<div class='dynamics-choice--floating'>{$checkbox}</div>" : $checkbox;
	if (!$has_multiple_options && in_array($options['type'], ['box', 'tag'], TRUE)) {
		$html .= dynamics_field_help($options['tip']);
		$html .= $options['required'] ? "<span class='required'>&nbsp;*</span>" : '';
	}
	if ($should_use_inline) $html .= "</div>";
	
	$html .= $options['ext_tip'] ? "<div class='dynamics-choice__group-description form-text'>{$options['ext_tip']}</div>" : "";
	
	if (class_exists('Defender') && Defender::inputHasError($input_name)) {
		$html .= "<div class='invalid-feedback d-block'>{$options['error_text']}</div>";
	}
	$html .= "</div>";
	
	if (class_exists('Defender')) {
		$defender_type = ($options['type'] == 'box' || $options['type'] == 'tag') ? ($options['multiple'] ? 'checkbox' : 'radio') : $options['type'];
		Defender::add_field_session([
			'input_name' => clean_input_name($input_name),
			'title'      => trim($title, '[]'),
			'id'         => $options['input_id'],
			'type'       => $defender_type,
			'required'   => $options['required'],
			'safemode'   => $options['safemode'],
			'error_text' => $options['error_text'],
			'delimiter'  => $options['delimiter'],
		]);
	}
	
	if (($options['type'] === 'box' || $options['type'] === 'tag') && !defined('JS_CHECKBOX')) {
		define('JS_CHECKBOX', TRUE);
		
		add_to_jquery("
        // Delegate change event to the document for all box containers
        $(document).on('change', '.js-checkbox-box-container input', function(e) {
            var \$input = $(this);
            var \$container = \$input.closest('.js-checkbox-box-container');
            var isMult = \$container.data('multiple') == '1';
            var max = parseInt(\$container.data('max-select')) || 1;

            if (isMult) {
                var checkedCount = \$container.find('input:checked').length;
                if (checkedCount > max) {
                    \$input.prop('checked', false);
                    alert('Maximum ' + max + ' selections allowed.');
                    return false;
                }
            }

            // Sync visual states for labels
            \$container.find('input').each(function() {
                var \$el = $(this);
                
                var \$label = \$container.find('label[for=\"' + \$el.attr('id') + '\"]');
                // Box state follows native inputs through CSS, including keyboard changes.
                if (\$label.hasClass('box-option')) return;
                
                if (\$el.is(':checked')) {
					\$label.addClass('active tag-option--on').removeClass('border-light-subtle');
                    \$label.attr('aria-checked', 'true');
                } else {
					\$label.removeClass('active tag-option--on').addClass('border-light-subtle');
                    \$label.attr('aria-checked', 'false');
                }
            });
        });

        // Sync visibility on Modal shown and on Initial Page Load
        $(document).on('shown.bs.modal', function () {
            $('.js-checkbox-box-container').find('input:checked').trigger('change');
        });
        
        $('.js-checkbox-box-container').find('input:checked').trigger('change');
      ");
	}
	
	return dynamics_render_component_template('form_checkbox', $html);
}
