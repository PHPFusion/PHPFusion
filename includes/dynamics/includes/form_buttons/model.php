<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_buttons.php
| Author: Meangczac (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\ImageRepo;

/**
 * Generate a multi framework semantic styled button
 *
 * @param string $input_name                 The name attribute for the button
 * @param string $title                      The button text
 * @param string $input_value                The value attribute for the button
 * @param array  $options                    Additional options:
 *                                           - input_id: ID attribute (defaults to input_name)
 *                                           - input_value: Value attribute (defaults to input_name)
 *                                           - class: Button classes (default: "btn-outline-secondary")
 *                                           Bootstrap 5 options:
 *                                           - Colors: btn-primary, btn-secondary, btn-success,
 *                                           btn-danger, btn-warning, btn-info, btn-light, btn-dark
 *                                           - Outline: btn-outline-primary, btn-outline-secondary, etc.
 *                                           - Sizes: btn-sm, btn-lg
 *                                           - Width: w-100 (full width)
 *                                           - icon_class: Icon spacing class (default: "me-2")
 *                                           - icon: Icon class name (e.g., "fa fa-search")
 *                                           - iconify: Iconify icon name (e.g., "tabler:printer")
 *                                           - deactivate: Whether to disable the button (default: false)
 *                                           - type: Button type - "submit", "button", or "link" (default: "submit")
 *                                           - svg: SVG icon instead of font icon
 *                                           - block: Whether to make button full width (default: false) - uses w-100
 *                                           - alt: Title/tooltip text
 *                                           - data: Array of data attributes
 *
 * @return string HTML button code
 */
function form_button($input_name, $title, $input_value, array $options = []) {
	
	$html = "";
	$input_value = clean_input_value($input_value);
	
	// 1. Initialize data array to prevent undefined variable bugs
	$options_data = [];
	
	$default_options = [
		'input_id'    => $input_name,
		'input_value' => $input_name,
		'class'       => "",
		'icon_class'  => "me-2",
		'icon'        => "",
		'iconify'     => "",
		'deactivate'  => FALSE,
		'type'        => "submit",
		'svg'         => '',
		'block'       => FALSE,
		'alt'         => $title,
		'data'        => [],
	];
	$options += $default_options;
	
	if ( $options['block'] ) {
		$options['class'] = $options['class'] . " w-100";
	}
	
	// Safely parse data attributes
	if (!empty($options['data'])) {
		array_walk($options['data'], function ($a, $b) use (&$options_data) {
			$options_data[] = "data-$b='$a'";
		});
	}
	
	// Resolve SVG / Icons
	$has_button_text = trim(strip_tags((string)$title)) !== '';
	$icon_class = $has_button_text ? trim((string)$options['icon_class']) : 'me-0';
	$icon = ( $options['icon'] ? "<i class='" . $options['icon'] . " " . $icon_class . "'></i>" : '' );
	if ( ! empty($options['iconify']) ) {
		$icon_name = htmlspecialchars((string)$options['iconify'], ENT_QUOTES, 'UTF-8');
		$icon_class_attr = $icon_class !== '' ? " class='" . htmlspecialchars($icon_class, ENT_QUOTES, 'UTF-8') . "'" : '';
		$icon = "<iconify-icon icon='{$icon_name}'{$icon_class_attr} aria-hidden='true'></iconify-icon>";
	}
	else if ( ! empty($options['svg']) ) {
		$icon = ImageRepo::getSVG($options['svg']);
		if ( $icon_class !== '' ) {
			if ( preg_match('/<svg\b[^>]*\bclass=([\'"])/i', $icon) ) {
				$icon = preg_replace_callback(
					'/(<svg\b[^>]*\bclass=)([\'"])(.*?)(\2)/i',
					static function (array $matches) use ($icon_class) {
						return $matches[1] . $matches[2] . trim($matches[3] . ' ' . $icon_class) . $matches[4];
					},
					$icon,
					1
				);
			}
			else {
				$icon = preg_replace('/<svg\b/i', '<svg class="' . $icon_class . '"', $icon, 1);
			}
		}
	}
	if ( $icon && $has_button_text ) {
		$icon = $icon . ' ';
	}
	
	// Common button attributes
	$common_attrs = "id='" . $options['input_id'] . "' title='" . $options['alt'] . "' " .
		"class='btn " . $options['class'] . " " . ( $options['deactivate'] ? 'disabled' : '' ) . "' " .
		( ! empty($options_data) ? implode(' ', $options_data) : '' ) .
		( $options['deactivate'] ? " aria-disabled='true'" : "" );
	
	// Render Markup variants
	if ( $options['type'] == 'link' ) {
		$html .= "<a " . $common_attrs . " href='" . $input_name . "' data-value='" . $input_value . "' " .
			( $options['deactivate'] ? "tabindex='-1' role='button' aria-disabled='true'" : "" ) . ">" .
			$icon . $title . "</a>";
	}
	else if ( $options['type'] == 'button' ) {
		$html .= "<button " . $common_attrs . " name='" . $input_name . "' value='" . $input_value . "' type='button' " .
			( $options['deactivate'] ? " disabled" : "" ) . ">" . $icon . $title . "</button>";
	}
	else {
		$html .= "<button " . $common_attrs . " name='" . $input_name . "' value='" . $input_value . "' type='submit' " .
			( $options['deactivate'] ? " disabled" : "" ) . ">" . $icon . $title . "</button>";
	}
	
	return dynamics_render_component_template('form_buttons', $html);
}

/**
 * Button Groups - Bootstrap 5 styled (radio/checkbox toggle group)
 *
 * @param string $input_name  The name attribute for the group
 * @param string $label       The field label
 * @param string $input_value The current value (for radio) or comma-separated list (for multiple)
 * @param array  $options     Additional options:
 *                            - options: Array of [value => label]
 *                            - input_id: ID attribute (defaults to input_name)
 *                            - class: Extra wrapper classes
 *                            - inner_class: Button classes (default: btn-outline-secondary)
 *                            - multiple: TRUE for checkboxes (multi-select), FALSE for radio (default)
 *                            - deactivate: Disable entire group
 *                            - inline: Inline layout (default: false)
 *                            - error_text: Custom error text
 *                            - required: Whether field is required
 *                            - ext_tip: Help text below
 *                            - callback_check: Validation callback
 *                            - delimiter: For multi values (default: ",")
 *
 * @return string HTML for the button group
 */
function form_btngroup_backup($input_name, $label, $input_value, array $options = []) {

    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    // FIX 1: Ensure input_value is treated as a string for comparison
    $input_value = ( isset($input_value) && $input_value !== '' ) ? (string)$input_value : '';

    $default_options = [
        'options'        => [ $locale['disable'], $locale['enable'] ],
        'input_id'       => $input_name,
        'class'          => '',
        'inner_class'    => '',
        'multiple'       => FALSE,
        'delimiter'      => ',',
        'deactivate'     => FALSE,
        'inline'         => FALSE,
        'error_text'     => '',
        'required'       => FALSE,
        'ext_tip'        => '',
        'callback_check' => '',
        'meter'          => FALSE,
        'segmented'      => FALSE,
    ];

    $options += $default_options;

    // Form handling logic...
    $input_value = clean_input_value($input_value);
    if ( $options['multiple'] && check_post([ $input_name ]) ) {
        $input_value = post([ $input_value ]);
    }
    else if ( check_post($input_value) ) {
        $input_value = post($input_value);
    }

    // Error handling...
    $error_class = '';
    if ( \Defender::inputHasError($input_name) ) {
        $error_class = 'has-error';
        $options['error_text'] = \Defender::getErrorText($input_name) ? : $options['error_text'];
        if ( ! empty($options['error_text']) ) {
            addnotice('danger', $options['error_text']);
        }
    }

    array_walk($options['data'],
        function ($a, $b) use (&$options_data) {

            $options_data[] = "data-$b='$a'";
        },
               $options_data);
    $data_options = ( ! empty($options_data) ? ' ' . implode(' ', $options_data) : '' );

    // Layout classes
    $form_group_class = " {$options['class']}";
    $label_class = $options['inline'] ? "col-form-label col-sm-12 col-md-3 col-lg-3" : "form-label";
    $field_wrapper_class = $options['inline'] ? "col-sm-12 col-md-9 col-lg-9" : "";

    $html = "<div id='{$options['input_id']}-field' class='{$form_group_class} " . ( $options['inline'] && $label ? 'row ' : '' ) . "{$error_class}'>";
    $html .= ( $label ) ? "<div class='{$label_class}' for='{$options['input_id']}'>" . $label . ( $options['required'] ? "<span class='required'>&nbsp;*</span>" : '' ) . "</div>" : '';

    if ( $options['inline'] && $label ) {
        $html .= "<div class='{$field_wrapper_class}'>";
    }

    // Determine selected values
    $selected_values = [];
    if ( $options['multiple'] ) {
        $selected_values = is_array($input_value) ? $input_value : array_map('trim', explode($options['delimiter'], $input_value));
    }
    else {
        $selected_values = [ (string)$input_value ]; // Cast to string for strict in_array
    }

    $meter_mode = ( $options['meter'] || $options['segmented'] );
    $meter_class = $meter_mode ? " meter-group d-flex align-items-center gap-1" : " btn-group";
    $flex_wrap = $options['multiple'] && ! $options['meter'] ? " flex-wrap" : "";

    $html .= "<div class='{$meter_class}{$flex_wrap}{$form_group_class}' role='group' aria-label='{$input_name}' id='{$options['input_id']}-group'>";

    if ( ! empty($options['options']) && is_array($options['options']) ) {
        $i = 1;
        $total_options = count($options['options']);

        foreach ( $options['options'] as $value => $text ) {
            // FIX 2: Strict string comparison for the 'checked' state
            $is_checked = in_array((string)$value, $selected_values, TRUE) ? ' checked' : '';
            $input_id_unique = $options['input_id'] . '_' . $i;
            $disabled = $options['deactivate'] ? ' disabled' : '';
            $input_type = $options['multiple'] ? 'checkbox' : 'radio';
            $value_css = ' btn-' . strtolower(str_replace(' ', '-', $text));

            if ( $meter_mode ) {
                $color = get_meter_color($i, $total_options);
                $dark_inactive = 'rgba(255, 255, 255, 0.07)';
                $light_inactive = 'rgba(0, 0, 0, 0.05)';

                $label_class = $options['segmented'] ? "meter-label meter-segment d-flex align-items-center justify-content-center" : "meter-label flex-grow-1 rounded-1";

                // Adjust styles
                $label_style = $options['segmented']
                    ? "cursor: pointer; border: 1px solid $color; color: $color; transition: all 0.2s; font-size: .9rem; font-weight: 500;padding:0 6px; width: fit-content; height: 24px; border-radius: 8px; background: transparent;"
                    : "cursor: pointer; transition: all 0.2s; height: 12px;"; // Added fixed height for bar meter

                $display_text = $options['segmented'] ? $text : '';

                $html .= "
                 <input type='{$input_type}' class='btn-check meter-input'
                   name='{$input_name}" . ( $options['multiple'] ? "[]" : "" ) . "'
                   id='{$input_id_unique}'
                   value='{$value}'
                   autocomplete='off'
                   data-index='{$i}'
                   data-type='" . ( $options['segmented'] ? 'segmented' : 'meter' ) . "'
                   data-active-color='{$color}'
                   data-dark-inactive='{$dark_inactive}'
                   data-light-inactive='{$light_inactive}'
                   {$is_checked}{$disabled}>
                 <label class='{$label_class}{$value_css}' style='{$label_style}' for='{$input_id_unique}'>{$display_text}</label>
               ";
            }
            else {
                $html .= "
                    <input type='{$input_type}' class='btn-check' name='{$input_name}" . ( $options['multiple'] ? "[]" : "" ) . "' id='{$input_id_unique}' value='{$value}' autocomplete='off'{$is_checked}{$disabled}>
                    <label class='btn {$options['inner_class']}{$value_css}' for='{$input_id_unique}'>{$text}</label>
                ";
            }
            $i++;
        }

        if ( $options['meter'] ) {
            $html .= "<div class='meter-indicator ms-2 fw-bold fs-5' style='width: 30px; text-align: center; color: rgba(150,150,150,0.5);'>-</div>";
        }

        // FIX 3: Load the JS if EITHER meter or segmented is active
        if ( $meter_mode ) {
            fusion_load_script(INCLUDES . "jscripts/btnmeter.js");
        }
    }

    $html .= "</div>";

    // Footers/Defender registration...
    if ( $options['ext_tip'] ) {
        $html .= "<div class='form-text mt-2'>".dynamics_field_help($options['ext_tip'], TRUE)."</div>";
    }
    if ( \Defender::inputHasError($input_name) ) {
        $html .= "<div class='invalid-feedback d-block'>{$options['error_text']}</div>";
    }
    if ( $options['inline'] && $label ) {
        $html .= "</div>";
    }
    $html .= "</div>";

    $input_name_clean = $options['multiple'] ? str_replace("[]", "", $input_name) : $input_name;
    \Defender::add_field_session([
                                     'input_name'     => $input_name_clean,
                                     'title'          => trim($title, '[]'),
                                     'id'             => $options['input_id'],
                                     'type'           => $options['multiple'] ? 'checkbox' : 'radio',
                                     'required'       => $options['required'],
                                     'callback_check' => $options['callback_check'],
                                     'safemode'       => $options['safemode'] ?? FALSE,
                                     'error_text'     => $options['error_text'],
                                     'delimiter'      => $options['delimiter'],
                                 ]);

    return dynamics_render_component_template('form_buttons', $html);
}

/**
 * Button Groups / Meters / Tags - Extended
 */
function form_btngroup($input_name, $label, $input_value, array $options = []) {

    $locale = fusion_get_locale();
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $default_options = [
        'options'        => [ $locale['disable'], $locale['enable'] ],
        'input_id'       => $input_name,
        'class'          => '',
        'inner_class'    => 'btn-outline',
        'multiple'       => FALSE,
        'delimiter'      => ',',
        'deactivate'     => FALSE,
        'inline'         => FALSE,
        'error_text'     => '',
        'required'       => FALSE,
        'ext_tip'        => '',
        'callback_check' => '',
        'safemode'       => FALSE,
        'meter'          => FALSE,
        'segmented'      => FALSE,
        'tags'           => FALSE,
        'data'           => [],
    ];

    $options += $default_options;

    // 1. DATA PERSISTENCE & POST CHECK
    $input_name_clean = ( $options['multiple'] || $options['tags'] ) ? str_replace("[]", "", $input_name) : $input_name;
    if ( check_post($input_name_clean) ) {
        $input_value = post($input_name_clean);
    }

    // 2. MULTI-VALUE NORMALIZATION
    $selected_values = [];
    if ( $options['multiple'] || $options['tags'] ) {
        $selected_values = is_array($input_value) ? $input_value : array_filter(array_map('trim', explode($options['delimiter'], (string)$input_value)));
    }
    else {
        $selected_values = [ (string)$input_value ];
    }

    // 3. ERROR HANDLING
    $error_class = '';
    if ( \Defender::inputHasError($input_name_clean) ) {
        $error_class = ' has-error';
        $options['error_text'] = \Defender::getErrorText($input_name_clean) ? : $options['error_text'];
        if ( ! empty($options['error_text']) ) {
            addnotice('danger', $options['error_text']);
        }
    }

    array_walk($options['data'],
        function ($a, $b) use (&$options_data) {

            $options_data[] = "data-$b='$a'";
        },
               $options_data);
    $data_options = ( ! empty($options_data) ? ' ' . implode(' ', $options_data) : '' );

    // 4. LAYOUT SETUP
    $meter_mode = ( $options['meter'] || $options['segmented'] );
    $tag_mode = $options['tags'];

    $container_class = " btn-group";
    if ( $meter_mode ) {
        $container_class = " meter-group d-flex align-items-center gap-1";
    }
    if ( $tag_mode ) {
        $container_class = " d-flex flex-wrap gap-2";
    }

    $label_class = $options['inline'] ? "col-form-label col-sm-12 col-md-3 col-lg-3" : "form-label";
    $field_wrapper_class = $options['inline'] ? "col-sm-12 col-md-9 col-lg-9" : "";

    // --- RESTORED: $options['class'] implementation on the wrapper ---
    $html = "<div id='{$options['input_id']}-field' class='form-group {$options['class']}" . ( $options['inline'] && $label ? ' row' : '' ) . "{$error_class}'>";

    if ( $label ) {
        $html .= "<label class='{$label_class}'>" . $label . ( $options['required'] ? "<span class='required'>&nbsp;*</span>" : '' ) . "</label>";
    }

    if ( $options['inline'] && $label ) {
        $html .= "<div class='{$field_wrapper_class}'>";
    }

    $html .= "<div class='{$container_class}' role='group' id='{$options['input_id']}-group'>";

    if ( ! empty($options['options']) && is_array($options['options']) ) {
        $i = 1;
        $total_options = count($options['options']);

        foreach ( $options['options'] as $value => $text ) {
            $is_checked = in_array((string)$value, $selected_values, TRUE) ? ' checked' : '';
            $input_id_unique = $options['input_id'] . '_' . $i;
            $disabled = $options['deactivate'] ? ' disabled' : '';
            $input_type = ( $options['multiple'] || $tag_mode ) ? 'checkbox' : 'radio';
            $actual_name = $input_name_clean . ( ( $options['multiple'] || $tag_mode ) ? "[]" : "" );

            // RESTORED: value_css logic for custom label styling
            $value_css = ' btn-' . strtolower(str_replace(' ', '-', $text));

            // Data Attributes for JS
            $color = get_meter_color($i, $total_options);
            $dark_inactive = 'rgba(255, 255, 255, 0.07)';
            $light_inactive = 'rgba(0, 0, 0, 0.05)';
            $type_attr = $options['segmented'] ? 'segmented' : ( $tag_mode ? 'tag' : 'meter' );
            $data_attrs = "data-index='{$i}' data-type='{$type_attr}' data-active-color='{$color}' data-dark-inactive='{$dark_inactive}' data-light-inactive='{$light_inactive}'";

            if ( $meter_mode ) {
                $m_label_class = $options['segmented'] ? "meter-label meter-segment d-flex align-items-center justify-content-center" : "meter-label flex-grow-1 rounded-1";
                $label_style = $options['segmented']
                    ? "cursor: pointer; border: 1px solid $color; color: $color; transition: all 0.2s; font-size: .85rem; font-weight: 500; padding: 0 10px; height: 30px; border-radius: 6px; background: transparent;"
                    : "cursor: pointer; transition: all 0.2s; height: 14px;";

                $html .= "
                 <input type='{$input_type}' class='btn-check meter-input' name='{$actual_name}' id='{$input_id_unique}' value='{$value}' autocomplete='off' {$data_attrs} {$is_checked}{$disabled}{$data_options}>
                 <label class='{$m_label_class}{$value_css}' style='{$label_style}' for='{$input_id_unique}'>" . ( $options['segmented'] ? $text : '' ) . "</label>";
            }
            else if ( $tag_mode ) {
                // --- RESTORED: inner_class for tags ---
                $html .= "
               <input type='{$input_type}' class='btn-check meter-input' name='{$actual_name}' id='{$input_id_unique}' value='{$value}' autocomplete='off' {$data_attrs} {$is_checked}{$disabled}{$data_options}>
               <label class='btn {$options['inner_class']} {$value_css} rounded-pill px-3 border-dashed' style='font-size: 0.85rem;' for='{$input_id_unique}'>
                  {$text}
               </label>";
            }
            else {
                // --- RESTORED: inner_class for standard buttons ---
                $html .= "
               <input type='{$input_type}' class='btn-check' name='{$actual_name}' id='{$input_id_unique}' value='{$value}' autocomplete='off' {$is_checked}{$disabled}{$data_options}>
               <label class='btn {$options['inner_class']}{$value_css}' for='{$input_id_unique}'>{$text}</label>";
            }
            $i++;
        }

        if ( $options['meter'] && ! $options['segmented'] ) {
            // If we have a value, use it. Otherwise default to '-'
            $display_val = ( $input_value !== '' && $input_value !== NULL ) ? $input_value : '-';

            // Logic to get the color for the indicator based on the current value
            $indicator_color = 'rgba(150,150,150,0.5)';
            if ( $display_val !== '-' ) {
                $val_index = array_search($input_value, array_keys($options['options'])) + 1;
                $indicator_color = get_meter_color($val_index, count($options['options']));
            }
            $html .= "<div class='meter-indicator ms-2 fw-bold fs-5' style='width: 30px; text-align: center; color: {$indicator_color};'>{$display_val}</div>";
            // $html .= "<div class='meter-indicator ms-2 fw-bold fs-5' style='width: 30px; text-align: center; color: rgba(150,150,150,0.5);'>-</div>";
        }

        if ( $meter_mode || $tag_mode ) {
            fusion_load_script(INCLUDES . "jscripts/btnmeter.js");
        }
    }

    $html .= "</div>";

    // 5. DEFENDER REGISTRATION
    \Defender::add_field_session([
                                     'input_name'     => $input_name_clean,
                                     'title'          => $title,
                                     'id'             => $options['input_id'],
                                     'type'           => ( $options['multiple'] || $tag_mode ) ? 'checkbox' : 'radio',
                                     'required'       => $options['required'],
                                     'callback_check' => $options['callback_check'],
                                     'safemode'       => $options['safemode'],
                                     'error_text'     => $options['error_text'],
                                     'delimiter'      => $options['delimiter'],
                                 ]);

    if ( $options['ext_tip'] ) {
        $html .= "<div class='form-text mt-2'>".dynamics_field_help($options['ext_tip'], TRUE)."</div>";
    }
    if ( $options['inline'] && $label ) {
        $html .= "</div>";
    }
    $html .= "</div>";

    return dynamics_render_component_template('form_buttons', $html);
}

/**
 * @param $step
 * @param $total
 *
 * @return string
 */
function get_meter_color($step, $total) {

    $stops = [
        [ 'pct' => 0.00, 'color' => [ 255, 75, 75 ] ],   // Red
        [ 'pct' => 0.30, 'color' => [ 255, 75, 75 ] ],   // Red
        [ 'pct' => 0.50, 'color' => [ 255, 140, 66 ] ],  // Orange (at 5)
        [ 'pct' => 0.65, 'color' => [ 255, 209, 102 ] ], // Yellow (at 6.5)
        [ 'pct' => 0.75, 'color' => [ 155, 217, 130 ] ], // Light Green (Starts at 7.5)
        [ 'pct' => 1.00, 'color' => [ 0, 209, 130 ] ]    // Pure Green (at 10)
    ];

    $pct = $total > 1 ? ( $step - 1 ) / ( $total - 1 ) : 0;

    // Find bounds
    $lower = $stops[0];
    $upper = $stops[ count($stops) - 1 ];

    for ( $i = 0; $i < count($stops) - 1; $i++ ) {
        if ( $pct >= $stops[ $i ]['pct'] && $pct <= $stops[ $i + 1 ]['pct'] ) {
            $lower = $stops[ $i ];
            $upper = $stops[ $i + 1 ];
            break;
        }
    }

    $range = $upper['pct'] - $lower['pct'];
    if ( $range <= 0 ) {
        return "rgb({$lower['color'][0]}, {$lower['color'][1]}, {$lower['color'][2]})";
    }

    $range_pct = ( $pct - $lower['pct'] ) / $range;

    $r = round($lower['color'][0] + ( $upper['color'][0] - $lower['color'][0] ) * $range_pct);
    $g = round($lower['color'][1] + ( $upper['color'][1] - $lower['color'][1] ) * $range_pct);
    $b = round($lower['color'][2] + ( $upper['color'][2] - $lower['color'][2] ) * $range_pct);

    return "rgb($r, $g, $b)";
}
