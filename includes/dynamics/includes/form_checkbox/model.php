<?php
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
	$locale = fusion_get_locale('', LOCALE . LOCALESET . 'global.php');
	
	if (check_post($input_name)) {
		$input_value = post($input_name);
	}
	
	$options += [
		'input_id'       => $input_name,
		'inline'         => FALSE,
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
		'inner_width'    => '',
		'inner_class'    => 'd-inline-block',
		'tag_indicator'  => '',
		'reverse_label'  => FALSE,
		'deactivate_key' => NULL,
		'onclick'        => '',
	];
	
	$real_input_name = $input_name;
	if ($options['multiple'] && !str_contains($input_name, '[]')) {
		$real_input_name = $input_name . '[]';
	}
	
	if ($options['multiple'] && !is_array($input_value)) {
		$input_value = !empty($input_value) ? explode($options['delimiter'], $input_value) : [];
	}
	
	$options['toggle'] = ($options['type'] == 'toggle');
	
	$switch_css_1 = '';
	$switch_css_2 = '';
	if ($options['toggle']) {
//		$options['class'] .= ' form-switch';
		$options['reverse_label'] = TRUE;
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
			$checkbox .= "<label class='form-check form-switch form-switch-lg' style='cursor:pointer;'>";
			$checkbox .= "<input type='checkbox' name='{$real_input_name}' id='{$input_id}' value='{$on_key}' class='form-check-input' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">";
			
			// first one is form-check-label-on , need to sort the key first.
			$checkbox .= "<span class='form-check-label form-check-label-on'>" . stripinput($on_label) . "</span>";
			$checkbox .= "<span class='form-check-label form-check-label-off'>" . stripinput($off_label) . "</span>";
			
			$checkbox .= "</label>";
			
		} else {
			
			
			foreach ($options['options'] as $key => $value) {
				
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
					
					$active_class = $is_checked ? ' active border-primary bg-light' : ' border-light-subtle';
					
					$checkbox .= "
					<input type='{$input_type}' name='{$real_input_name}' id='{$input_id}' value='{$key}' class='d-none' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">
					<label for='{$input_id}' class='form-check-label box-option border rounded {$options['inner_class']}{$disabled_class}{$active_class}' style='cursor:pointer;'>
						{$value}
					</label>";
					
				} elseif ($is_tag) {
					
					$active_class = $is_checked ? ' active tag-option--on' : '';
					$indicator = $options['tag_indicator'] ?: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
					
					$checkbox .= "
					<input type='{$input_type}' name='{$real_input_name}' id='{$input_id}' value='{$key}' class='d-none' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">
					<label for='{$input_id}' class='form-check-label tag-option {$options['inner_class']}{$disabled_class}{$active_class}' style='cursor:pointer;' role='{$input_type}' aria-checked='" . ($is_checked ? 'true' : 'false') . "'>
						<span class='tag-option-indicator'>{$indicator}</span>
						<span class='tag-option-label'>{$value}</span>
					</label>";
					
				} else {
					$checkbox .= "<div class='form-{$input_class}" . ($options['inline_options'] ? ' form-check-inline me-3' : '') . "'>";
					$checkbox .= "<label class='form-check-label' for='{$input_id}'" . ($options['inner_width'] ? " style='width:{$options['inner_width']}'" : '') . ">";
					$checkbox .= "<input id='{$input_id}' name='{$real_input_name}' value='{$key}' type='{$input_type}' class='form-check-input me-2' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . " />";
					$checkbox .= $value . "</label></div>";
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
			$active_class = $is_checked ? ' active border-primary bg-light' : ' border-light-subtle';
			$checkbox .= "
            <input type='checkbox' name='{$real_input_name}' id='{$input_id}' value='{$options['value']}' class='d-none' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">
            <label for='{$input_id}' class='form-check-label d-inline-block box-option border rounded p-3 me-3 text-center{$disabled_class}{$active_class}' style='cursor:pointer; min-width:120px;'>
                {$label}
            </label>";
		} elseif ($is_tag) {
			$active_class = $is_checked ? ' active tag-option--on' : '';
			$indicator = $options['tag_indicator'] ?: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
			$checkbox .= "
            <input type='checkbox' name='{$real_input_name}' id='{$input_id}' value='{$options['value']}' class='d-none' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">
            <label for='{$input_id}' class='form-check-label tag-option {$options['inner_class']}{$disabled_class}{$active_class}' style='cursor:pointer;' role='checkbox' aria-checked='" . ($is_checked ? 'true' : 'false') . "'>
                <span class='tag-option-indicator'>{$indicator}</span>
                <span class='tag-option-label'>{$label}</span>
            </label>";
		} elseif ($options['toggle']) {
			
			// Single Option Toggle Logic
			// We check against $options['value'] (defaults to 1)
			$is_checked = ((string)$input_value === (string)$options['value']);
			
			$input_id = $options['input_id'];
			$disabled = $options['deactivate'] ? 'disabled' : '';
			$onclick = $options['onclick'] ? ' onclick="' . $options['onclick'] . '"' : '';
			
			// Render ONLY ONE toggle
			$checkbox .= "<label class='form-check form-switch form-switch-lg' style='cursor:pointer;'>";
			$checkbox .= "<input type='checkbox' name='{$real_input_name}' id='{$input_id}' value='{$options['value']}' class='form-check-input' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">";
			
			// Use the main $label passed to the function
			if ($label) {
				$checkbox .= "<span class='form-check-label'>" . stripinput($label) . "</span>";
			}
			
			$checkbox .= "</label>";
			
		} else {
			$checkbox_class = ($options['type'] == 'checkbox') ? 'form-check' : "form-{$options['type']}";
			$checkbox .= "<div class='{$checkbox_class}'>";
			$label_tag = "<label class='form-check-label ms-2 {$switch_css_2}' for='{$input_id}'>{$label}" .
				($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') .
				($options['tip'] ? " <i class='pointer fa fa-question-circle text-muted' title='{$options['tip']}'></i>" : '') . "</label>";
			
			if ($label && $options['reverse_label'] == TRUE) $checkbox .= $label_tag;
			$checkbox .= "<input id='{$input_id}' class='form-check-input' name='{$real_input_name}' value='{$options['value']}' type='{$options['type']}' {$disabled}{$onclick}" . ($is_checked ? ' checked' : '') . ">";
			if ($label && $options['reverse_label'] == FALSE) $checkbox .= $label_tag;
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
	$html = "<div id='{$options['input_id']}-field' class='w-100" .
		($should_use_inline ? ' row' : '') .
		(!empty($error_class) ? $error_class : '') .
		(trim($options['class']) ? ' ' . $options['class'] : '') . "'{$data_attrs}>";
	
	if ($has_multiple_options && !empty($label)) {
		$html .= "<label class='form-label {$switch_css_1} {$options['label_class']} " . ($should_use_inline ? 'col-sm-12 col-md-3 col-lg-3' : '') . "' for='{$options['input_id']}'>" .
			$label . ($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') .
			($options['tip'] ? " <i class='pointer fa fa-question-circle text-muted' title='{$options['tip']}'></i>" : '') . "</label>";
	}
	
	$html .= $should_use_inline ? "<div class='col-sm-12 col-md-9 col-lg-9'>" : "";
	$html .= $checkbox;
	if ($should_use_inline) $html .= "</div>";
	
	$html .= $options['ext_tip'] ? "<div class='form-text'>{$options['ext_tip']}</div>" : "";
	
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
                
                if (\$el.is(':checked')) {
                    \$label.addClass('active border-primary bg-light tag-option--on').removeClass('border-light-subtle');
                    \$label.attr('aria-checked', 'true');
                } else {
                    \$label.removeClass('active border-primary bg-light tag-option--on').addClass('border-light-subtle');
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
