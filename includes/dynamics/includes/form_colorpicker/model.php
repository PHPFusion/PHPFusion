<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_colorpicker.php
| Author: Frederick MC CHan (Chan)
| Component: Dynamics Color Picker (dependency-free)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/**
 * Inline color picker. formats: ALL, HEX, RGB, CSS, HSL or a subset array.
 * format chooses the initial output format; floating_label/inline are ignored.
 */
function form_colorpicker($input_name, $label = '', $input_value = '', array $options = []) {
    static $assets_loaded = FALSE;
    if (!$assets_loaded) {
        foreach (['css', 'js'] as $extension) {
            $asset = 'includes/form_colorpicker/component.'.$extension;
            fusion_load_script(DYNAMICS.$asset.'?v='.filemtime(__DIR__.'/component.'.$extension), $extension);
        }
        $assets_loaded = TRUE;
    }
    $locale = fusion_get_locale();
    $options += [
        'input_id' => $input_name, 'required' => FALSE, 'deactivate' => FALSE,
        'class' => '', 'tip' => '', 'ext_tip' => '', 'inner_text' => '', 'safemode' => FALSE,
        'error_text' => $locale['error_input_default'] ?? 'Choose a color.',
        'formats' => 'ALL', 'format' => 'HEX', 'placeholder' => 'Choose color',
    ];
    $allowed = ['HEX', 'RGB', 'CSS', 'HSL'];
    $requested = is_array($options['formats']) ? $options['formats'] : explode(',', (string)$options['formats']);
    $requested = array_map(static fn($format) => strtoupper(trim((string)$format)), $requested);
    $formats = in_array('ALL', $requested, TRUE) ? $allowed : array_values(array_intersect($allowed, $requested));
    if (!$formats) $formats = $allowed;
    $format = strtoupper((string)$options['format']);
    if (!in_array($format, $formats, TRUE)) $format = $formats[0];
    $name = clean_input_name($input_name);
    if (check_post($name)) $input_value = post($name);
    if (!is_scalar($input_value)) $input_value = '';
    $escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $id = $escape(trim(str_replace(['[', ']'], ['_', ''], $options['input_id']), '-'));
    $title = $label ?: ucfirst(str_replace('_', ' ', $name));
    $error = class_exists('Defender') && Defender::inputHasError($input_name);
    $error_text = $error ? (Defender::getErrorText($input_name) ?: $options['error_text']) : '';
    $disabled = $options['deactivate'] ? ' disabled' : '';
    $help = dynamics_field_help($options['tip']);
    $extended = $options['ext_tip'] ? dynamics_field_help($options['ext_tip'], TRUE) : '';
    $description_ids = "{$id}-status".($options['inner_text'] !== '' ? " {$id}-description" : '').($extended ? " {$id}-help" : '');
    $chevron = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
    $eyedropper = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11 7 6 6M5 19l-2 2 2-6L16 4a2.8 2.8 0 0 1 4 4L9 19H5Z"/></svg>';
    $html = "<div id='{$id}-field' class='dynamics-colorpicker ".$escape($options['class'])."' data-colorpicker data-format='{$format}' data-placeholder='".$escape($options['placeholder'])."'>";
    $html .= "<div class='dynamics-colorpicker__row'><div class='dynamics-colorpicker__identity'>";
    if ($label) {
        $html .= "<span class='dynamics-colorpicker__label'><label class='form-label' for='{$id}-trigger'>".$escape($label).($options['required'] ? "<span class='required'> *</span>" : '')."</label>{$help}</span>";
    } else {
        $html .= $help;
    }
    $html .= $options['inner_text'] !== '' ? "<div class='dynamics-colorpicker__description form-text' id='{$id}-description'>".$escape($options['inner_text'])."</div>" : '';
    $html .= '</div>';
    $html .= "<input type='hidden' data-color-value id='{$id}' name='".$escape($input_name)."' value='".$escape($input_value)."'{$disabled}>";
    $html .= "<button type='button' class='dynamics-colorpicker__trigger' id='{$id}-trigger' aria-haspopup='dialog' aria-expanded='false' aria-controls='{$id}-panel' aria-label='".$escape($title)."' aria-describedby='{$description_ids}'".($error ? " aria-invalid='true'" : '')."{$disabled}>";
    $html .= "<span class='dynamics-colorpicker__swatch' aria-hidden='true'><span data-color-swatch></span></span><span data-color-format>{$format}</span><span data-color-summary>".$escape($input_value ?: $options['placeholder'])."</span>{$chevron}</button>";
    $html .= '</div>';
    $html .= $extended ? "<div class='dynamics-colorpicker__help form-text' id='{$id}-help'>{$extended}</div>" : '';
    $html .= "<div id='{$id}-panel' class='dynamics-colorpicker__panel' role='dialog' aria-label='".$escape($title.' color picker')."' popover='auto' hidden>";
    $html .= "<div class='dynamics-colorpicker__palette' data-color-palette role='slider' tabindex='0' aria-label='Color palette; left and right change saturation, up and down change brightness' aria-valuemin='0' aria-valuemax='100' aria-valuenow='0'><span data-color-cursor></span></div>";
    $html .= "<input class='dynamics-colorpicker__slider dynamics-colorpicker__hue' data-color-hue type='range' min='0' max='360' step='1' value='0' aria-label='Hue'>";
    $html .= "<div class='dynamics-colorpicker__alpha-track'><input class='dynamics-colorpicker__slider' data-color-alpha type='range' min='0' max='100' step='1' value='100' aria-label='Opacity'></div>";
    $html .= "<div class='dynamics-colorpicker__controls'><button type='button' class='dynamics-colorpicker__eye' data-color-eye aria-label='Pick color from screen' title='Pick color from screen'>{$eyedropper}</button>";
    if (count($formats) > 1) {
        $html .= "<select data-color-type data-fusion-no-framework-aliases aria-label='Color format'>";
        foreach ($formats as $item) $html .= "<option value='{$item}'".($item === $format ? ' selected' : '').">{$item}</option>";
        $html .= '</select>';
    }
    $html .= "<input data-color-text type='text' aria-label='Color value' autocomplete='off' spellcheck='false' aria-describedby='{$id}-status'>";
    $html .= "<label class='dynamics-colorpicker__alpha-value'><input data-color-opacity type='number' min='0' max='100' step='1' value='100' aria-label='Opacity percentage'><span aria-hidden='true'>%</span></label></div>";
    $html .= "<p class='dynamics-colorpicker__status' id='{$id}-status' data-color-status role='status'>".$escape($error_text)."</p></div>";
    $html .= "<noscript><span>Enable JavaScript to edit this color.</span></noscript></div>";
    if (class_exists('Defender')) {
        Defender::getInstance()->add_field_session([
            'input_name' => $name, 'type' => 'color', 'title' => $title,
            'id' => html_entity_decode($id, ENT_QUOTES, 'UTF-8'), 'required' => $options['required'],
            'safemode' => $options['safemode'], 'error_text' => $options['error_text'],
        ]);
    }
    return dynamics_render_component_template('form_colorpicker', $html);
}
