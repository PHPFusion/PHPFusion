<?php

defined('IN_FUSION') || exit;

/** Escape Dynamics JSON editor content and attributes. */
function form_json_escape(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Normalize valid JSON for display while preserving invalid input for correction. */
function form_json_normalize(mixed $value, string $rootType = 'object'): string
{
    if (is_array($value) || is_object($value)) {
        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        return is_string($encoded) ? $encoded : ($rootType === 'array' ? '[]' : '{}');
    }

    $value = trim((string)$value);
    if ($value === '') {
        return $rootType === 'array' ? '[]' : '{}';
    }

    $decoded = json_decode($value, TRUE);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return $value;
    }

    $encoded = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    return is_string($encoded) ? $encoded : $value;
}

/** Return the top-level item count and detected root type. */
function form_json_summary(string $json, string $configuredRoot = 'auto'): array
{
    $decoded = json_decode($json, TRUE);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return ['count' => 0, 'root' => $configuredRoot === 'array' ? 'array' : 'object', 'valid' => FALSE];
    }

    $isList = array_keys($decoded) === range(0, count($decoded) - 1);
    if ($decoded === []) {
        $isList = $configuredRoot === 'array';
    }

    return ['count' => count($decoded), 'root' => $isList ? 'array' : 'object', 'valid' => TRUE];
}

/**
 * Render a compact JSON field with a progressively enhanced modal editor.
 *
 * The hidden textarea remains the authoritative submitted form value. The
 * modal supports object properties and array items without exposing business
 * logic to the component.
 */
function form_json(string $inputName, string $label = '', mixed $inputValue = [], array $options = []): string
{
    $locale = fusion_get_locale();
    $options += [
        'input_id' => $inputName,
        'required' => FALSE,
        'class' => '',
        'description' => '',
        'root_type' => 'auto',
        'button_text' => 'Edit',
        'error_text' => $locale['error_input_default'] ?? 'Enter valid JSON.',
        'deactivate' => FALSE,
    ];
    $options['root_type'] = in_array($options['root_type'], ['auto', 'object', 'array'], TRUE)
        ? $options['root_type']
        : 'auto';

    $inputName = stripinput($inputName);
    $inputId = preg_replace('/[^A-Za-z0-9_-]/', '-', trim((string)$options['input_id'], '[]')) ?: 'json-field';
    if (check_post($inputName)) {
        $inputValue = post($inputName);
    }
    $json = form_json_normalize($inputValue, $options['root_type'] === 'array' ? 'array' : 'object');
    $summary = form_json_summary($json, $options['root_type']);
    $countLabel = $summary['count'].' '.($summary['root'] === 'array'
        ? ($summary['count'] === 1 ? 'item' : 'items')
        : ($summary['count'] === 1 ? 'property' : 'properties'));

    $errorClass = '';
    if (\Defender::inputHasError($inputName)) {
        $errorClass = ' has-error';
        $defenderError = \Defender::getErrorText($inputName);
        if ($defenderError !== '') {
            $options['error_text'] = $defenderError;
        }
        addnotice('danger', $options['error_text']);
    }

    \Defender::add_field_session([
        'input_name' => clean_input_name($inputName),
        'type' => 'textarea',
        'title' => $label,
        'id' => $inputId,
        'required' => (bool)$options['required'],
        'safemode' => FALSE,
        'error_text' => $options['error_text'],
    ]);

    static $assetsLoaded = FALSE;
    if (!$assetsLoaded) {
        fusion_load_script(DYNAMICS.'includes/form_json/component.css', 'css');
        fusion_load_script(DYNAMICS.'includes/form_json/component.js');
        $assetsLoaded = TRUE;
    }

    $modalId = $inputId.'-json-editor';
    $buttonId = $inputId.'-json-edit';
    $labelId = $inputId.'-json-label';
    $descriptionId = $inputId.'-json-description';
    $errorId = $inputId.'-json-error';
    $config = json_encode([
        'rootType' => $options['root_type'],
        'strings' => [
            'invalidStored' => 'The stored JSON is invalid and must be corrected before changes can be applied.',
            'propertyRequired' => 'Enter a property name.',
            'indexInvalid' => 'Enter a valid existing array index, or leave it blank to append an item.',
            'removeProperty' => 'Enter an existing property name to remove.',
            'removeIndex' => 'Enter an existing array index to remove.',
            'nestedValue' => 'This property contains nested values. Select it from Parent to edit its children.',
        ],
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $html = "<div id='".form_json_escape($inputId)."-field' class='form-group dynamics-json ".form_json_escape($options['class']).$errorClass."' data-dynamics-json='".form_json_escape($config ?: '{}')."'>";
    $html .= "<div class='d-flex align-items-center justify-content-between gap-3 dynamics-json__summary'><div class='min-w-0'>";
    $html .= "<div id='".form_json_escape($labelId)."' class='fw-semibold'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '').'</div>';
    if ($options['description'] !== '') {
        $html .= "<p id='".form_json_escape($descriptionId)."' class='form-text text-muted mb-0'>".$options['description'].'</p>';
    }
    $html .= "<div class='small text-muted' data-dynamics-json-count aria-live='polite'>".form_json_escape($summary['valid'] ? $countLabel : 'Invalid JSON').'</div></div>';
    $html .= "<button id='".form_json_escape($buttonId)."' class='btn btn-default btn-sm flex-shrink-0' type='button' aria-haspopup='dialog' aria-controls='".form_json_escape($modalId)."_Modal'".($options['deactivate'] ? ' disabled' : '').">".form_json_escape($options['button_text']).'</button></div>';
    $html .= "<textarea id='".form_json_escape($inputId)."' name='".form_json_escape($inputName)."' data-dynamics-json-storage hidden";
    $html .= $options['description'] !== '' ? " aria-describedby='".form_json_escape($descriptionId)."'" : '';
    $html .= '>'.form_json_escape($json).'</textarea>';
    if (\Defender::inputHasError($inputName)) {
        $html .= "<div id='".form_json_escape($errorId)."' class='form-text text-danger'>".$options['error_text'].'</div>';
    }

    $html .= openmodal($modalId, '<span data-dynamics-json-modal-title>Edit '.form_json_escape(strip_tags($label)).'</span>', [
        'hidden' => TRUE,
        'button_id' => $buttonId,
        'size' => 3,
    ]);
    $html .= "<div data-dynamics-json-modal><p class='small text-muted mb-4'>Choose where the value belongs. Comma-separated values become an array; one value remains a string.</p>";
    $html .= "<div class='d-flex flex-column gap-3'>";
    $html .= "<div class='form-floating w-100'><select id='".form_json_escape($inputId)."-entry-parent' class='form-select w-100' data-dynamics-json-parent><option value=''>Root</option></select><label for='".form_json_escape($inputId)."-entry-parent'>Parent</label></div>";
    $html .= "<div class='form-floating w-100'><input id='".form_json_escape($inputId)."-entry-key' class='form-control w-100' type='text' autocomplete='off' placeholder='Property name' data-dynamics-json-key><label for='".form_json_escape($inputId)."-entry-key' data-dynamics-json-key-label>Property name</label></div>";
    $html .= "<div class='form-floating w-100'><input id='".form_json_escape($inputId)."-entry-value' class='form-control w-100' type='text' autocomplete='off' placeholder='Clear, practical, encouraging' data-dynamics-json-value><label for='".form_json_escape($inputId)."-entry-value'>Value</label></div>";
    $html .= "<div class='d-flex flex-wrap gap-2'><button class='btn btn-default' type='button' data-dynamics-json-upsert>Add or update</button><button class='btn btn-outline-danger' type='button' data-dynamics-json-remove>Remove</button></div></div>";
    $html .= "<p class='small text-danger mt-3 mb-0' data-dynamics-json-error role='alert' aria-live='polite' hidden></p>";
    $html .= "<div class='d-flex align-items-center justify-content-between gap-3 mt-4 mb-2'><div id='".form_json_escape($inputId)."-preview-label' class='form-label mb-0'>JSON preview</div><span class='small text-muted' data-dynamics-json-modal-count>".form_json_escape($countLabel).'</span></div>';
    $html .= "<pre class='dynamics-json__preview' tabindex='0' aria-labelledby='".form_json_escape($inputId)."-preview-label'><code data-dynamics-json-preview></code></pre></div>";
    $html .= modalfooter("<button class='btn btn-primary' type='button' data-dynamics-json-apply>Apply changes</button>", TRUE);
    $html .= closemodal().'</div>';

    return dynamics_render_component_template('form_json', $html);
}
