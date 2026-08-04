<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_elite_textarea.php
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

/**
 * Render a Tiptap-only textarea with the Elite Genie AI response box and action button.
 *
 * This helper wraps form_textarea(), so normal textarea options still work exactly as
 * they do there. CKEditor is deliberately disabled because AI textareas use Tiptap only.
 *
 * @param string $input_name Textarea name and default editor key. [observation]
 * @param string $label Textarea label. [Student observation and classroom engagement]
 * @param string $input_value Existing textarea content. [Great focus today.]
 * @param array  $options Supported options:
 *                        - class: Wrapper class passed to form_textarea(). [assessment-field]
 *                        - rows: Native textarea rows passed to form_textarea(). [4]
 *                        - placeholder: Native textarea placeholder. [Write remarks...]
 *                        - remember: Enable form_textarea() draft persistence. [TRUE]
 *                        - mention: Tiptap/plain mention configuration. [['endpoint' => 'student']]
 *                        - ai_enabled: Render Genie response UI and button. [TRUE]
 *                        - ai_field: Logical AI route field sent as POST field. [observation]
 *                        - ai_endpoint_url: POST endpoint for Genie. [BASEDIR . 'api/?api=genie']
 *                        - ai_button_label: Button text. [Genie, improve this]
 *                        - ai_button_id: Button id. [improve_observation]
 *                        - ai_response_id: Response container id. [observation_response]
 *                        - ai_response_title: Response heading text. [Genie is suggesting these enhancements:]
 *                        - ai_command: Command sent in the AI payload. [improvisation]
 *                        - ai_data: Fixed context rendered as data-ai-extra-* attributes. [['student-id' => 123, 'mode' => 'remarks']]
 *                        - ai_payload_js: JavaScript expression returning live payload data. [window.getAssessmentGeniePayload(activeFieldName)]
 *                        - ai_fields: Payload field map of name => Tiptap editor id. [['observation' => 'observation', 'assessment' => 'assessment']]
 *                        - on_response_click_js: JavaScript run after a suggestion is selected. [console.log(selectedVal);]
 *                        - extra_actions: Extra action buttons rendered beside the Genie button.
 *
 * @return string
 */
function elite_textarea($input_name, $label = '', $input_value = '', array $options = []) {

    $input_name = (isset($input_name) && $input_name !== '') ? stripinput($input_name) : '';
    $safe_input_id = str_replace("-", "_", $options['input_id'] ?? $input_name);

    $defaults = [
        'ai_enabled'          => TRUE,
        'ai_field'            => '',
        'ai_endpoint_url'     => defined('BASEDIR') ? BASEDIR . 'api/?api=genie' : '',
        'ai_button_label'     => 'Genie, improve this',
        'ai_button_id'        => 'improve_' . $safe_input_id,
        'ai_button_enabled'   => TRUE,
        'ai_response_id'      => $safe_input_id . '_response',
        'ai_response_title'   => 'Genie is suggesting these enhancements:',
        'ai_command'          => 'improvisation',
        'ai_data'             => [],
        'ai_payload_js'       => '',
        'ai_fields'           => [],
        'on_response_click_js'=> '',
        'extra_action_enabled'=> FALSE,
        'extra_action_id'     => '',
        'extra_action_label'  => '',
        'extra_action_icon'   => '',
        'extra_action_class'  => 'btn btn-dark',
        'extra_action_data'   => [],
        'extra_actions'       => [],
        'actions_layout'      => 'inline',
        'actions_class'       => 'elite-textarea-actions d-flex align-items-center justify-content-end gap-2 mt-2',
    ];
    $options += $defaults;

    $textarea_options = $options;
    foreach (array_keys($defaults) as $ai_option) {
        unset($textarea_options[$ai_option]);
    }

    $textarea_options['input_id'] = $safe_input_id;
    $textarea_options['tiptap'] = TRUE;
    $textarea_options['ckeditor'] = FALSE;

    if (empty($options['ai_enabled'])) {
        return form_textarea($input_name, $label, $input_value, $textarea_options);
    }

    fusion_load_script(INCLUDES . "jscripts/geniebtn.js");

    $response_id = stripinput($options['ai_response_id']);
    $button_id = stripinput($options['ai_button_id']);
    $endpoint_url = $options['ai_endpoint_url'];
    $command = stripinput($options['ai_command']);
    $ai_fields = is_array($options['ai_fields']) ? $options['ai_fields'] : [];
    $ai_field = !empty($options['ai_field']) ? stripinput($options['ai_field']) : $input_name;

    if (empty($options['ai_field']) && count($ai_fields) === 1) {
        $ai_field = (string)array_key_first($ai_fields);
    }

    if (!defined('ELITE_TEXTAREA_AI_SCRIPT')) {
        define('ELITE_TEXTAREA_AI_SCRIPT', TRUE);
        add_to_jquery(<<<'JAVASCRIPT'
(function () {
    if (window.eliteTextareaAI && window.eliteTextareaAI.ready) return;

    function kebabToSnake(value) {
        return String(value || '').replace(/-/g, '_');
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function readEditorValue(editorId) {
        if (window.eliteEditors && window.eliteEditors[editorId] && window.eliteEditors[editorId].storage && window.eliteEditors[editorId].storage.markdown) {
            return window.eliteEditors[editorId].storage.markdown.getMarkdown();
        }
        var $textarea = $('#' + editorId);
        return $textarea.length ? $textarea.val() : '';
    }

    function fixedDataPayload(wrapper) {
        var payload = {};
        if (!wrapper) return payload;

        $.each(wrapper.attributes, function () {
            if (!this || this.name.indexOf('data-ai-extra-') !== 0) return;
            var key = kebabToSnake(this.name.replace('data-ai-extra-', ''));
            payload[key] = this.value;
        });

        return payload;
    }

    function livePayload(config, activeFieldName, $wrapper) {
        if (!config.aiPayloadJs) return {};

        try {
            var payload = (new Function('activeFieldName', '$wrapper', 'config', 'return (' + config.aiPayloadJs + ');'))(activeFieldName, $wrapper, config);
            return (payload && typeof payload === 'object') ? payload : {};
        } catch (error) {
            console.error('elite_textarea ai_payload_js failed:', error);
            return {};
        }
    }

    function runSelectionHook(config, selectedVal, $textarea, $wrapper, $responseContainer) {
        if (!config.onResponseClickJs) return;

        try {
            (new Function('selectedVal', '$textarea', '$wrapper', '$responseContainer', 'config', config.onResponseClickJs))(selectedVal, $textarea, $wrapper, $responseContainer, config);
        } catch (error) {
            console.error('elite_textarea on_response_click_js failed:', error);
        }
    }

    function buildSubmitData(config, $wrapper) {
        var activeFieldName = config.field;
        var fields = $.extend({}, config.aiFields || {});
        var submitData = {};

        if ($.isEmptyObject(fields)) {
            fields[activeFieldName] = config.editorId;
        }

        $.each(fields, function (payloadField, editorId) {
            submitData[payloadField] = readEditorValue(editorId);
        });

        submitData = $.extend(submitData, fixedDataPayload($wrapper.get(0)));
        submitData = $.extend(submitData, livePayload(config, activeFieldName, $wrapper));
        submitData.field = activeFieldName;
        submitData.fusion_token = submitData.fusion_token || $('input[name="fusion_token"]').val();
        submitData.command = config.command;

        return submitData;
    }

    function renderChoices(config, response, genie) {
        var responseError = response && (response.error || response.errors) ? (response.error || response.errors) : '';
        var rawChoices = [];
        if (response && response.results) {
            rawChoices = Array.isArray(response.results) ? response.results : [response.results];
        } else if (response && response.improved_text) {
            rawChoices = [response.improved_text];
        }

        var finalizedChoices = [];
        for (var i = 0; i < rawChoices.length; i++) {
            var textChunk = rawChoices[i];
            if (typeof textChunk === 'string') {
                if (textChunk.indexOf('----') !== -1) {
                    var splitLines = textChunk.split('----');
                    for (var j = 0; j < splitLines.length; j++) {
                        if (splitLines[j].trim() !== '') finalizedChoices.push({label: splitLines[j].trim(), value: splitLines[j].trim()});
                    }
                } else if (textChunk.trim() !== '') {
                    finalizedChoices.push({label: textChunk.trim(), value: textChunk.trim()});
                }
            } else if (textChunk && typeof textChunk === 'object') {
                var objectLabel = textChunk.label || textChunk.text || textChunk.title || textChunk.value || '';
                var objectValue = textChunk.value || textChunk.raw || JSON.stringify(textChunk);
                if (String(objectLabel).trim() !== '') finalizedChoices.push({label: String(objectLabel).trim(), value: String(objectValue)});
            }
        }

        if (finalizedChoices.length < 1) {
            genie.complete();
            if (responseError) {
                alert(responseError);
            } else {
                alert("Genie couldn't formulate variations with the current metrics.");
            }
            return;
        }

        var $responseContainer = $('#' + config.responseId);
        var $chatbox = $responseContainer.find('.genie-chatbox-response').empty();

        for (var k = 0; k < finalizedChoices.length; k++) {
            var choiceText = finalizedChoices[k].label;
            var choiceValue = finalizedChoices[k].value;
            var parsedHtmlContent = (typeof parseMarkdown === 'function') ? parseMarkdown(choiceText) : escapeHtml(choiceText);
            var $optionBtn = $(
                '<button type="button" class="btn d-flex align-items-start justify-content-start gap-2 p-2 response-selection w-100 mb-2 text-start text-wrap lh-base">' +
                    '<svg width="20" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0 text-finish-suggestion-icon mt-1">' +
                        '<path d="M11 17H13V13H17V11H13V7H11V11H7V13H11V17ZM5 21C4.45 21 3.97917 20.8042 3.5875 20.4125C3.19583 20.0208 3 19.55 3 19V5C3 4.45 3.19583 3.97917 3.5875 3.5875C3.97917 3.19583 4.45 3 5 3H19C19.55 3 20.0208 3.19583 20.4125 3.5875C20.8042 3.97917 21 4.45 21 5V19C21 19.55 20.8042 20.0208 20.4125 20.4125C20.0208 20.8042 19.55 21 19 21H5Z" fill="currentColor"></path>' +
                    '</svg>' +
                    '<div class="choice-text" style="white-space: pre-wrap;"></div>' +
                '</button>'
            );

            $optionBtn.find('.choice-text').html(parsedHtmlContent);
            $optionBtn.data('raw-value', choiceValue);
            $chatbox.append($optionBtn);
        }

        if (response && response.fusion_token) {
            $('input[name="fusion_token"]').val(response.fusion_token);
        }

        $responseContainer.get(0).genieControlInstance = genie;
        $responseContainer.show();
        genie.complete();
    }

    function init(config) {
        if (!config || !config.buttonId || window.eliteTextareaAI.bound[config.buttonId]) return;
        if (!$('#' + config.buttonId).length) return;

        window.eliteTextareaAI.bound[config.buttonId] = true;

        new GenieAIButtonPlugin('#' + config.buttonId, {
            onAction: function (textToImprove, genie) {
                var $button = $('#' + config.buttonId);
                var $wrapper = $button.closest('.genie-response-wrapper');
                var submitData = buildSubmitData(config, $wrapper);

                $.ajax({
                    url: config.endpointUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: submitData,
                    success: function (response) {
                        renderChoices(config, response, genie);
                    },
                    error: function (xhr) {
                        genie.complete();
                        var message = xhr && xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.errors)
                            ? (xhr.responseJSON.error || xhr.responseJSON.errors)
                            : "Genie ran into an unexpected hiccup processing the sheet matrix. Please try again!";
                        alert(message);
                    }
                });
            }
        });
    }

    window.eliteTextareaAI = {
        ready: true,
        bound: {},
        configs: {},
        register: function (config) {
            this.configs[config.responseId] = config;
            init(config);
        }
    };

    $(document).on('click', '.response-selection', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var selectedVal = $btn.data('raw-value') || $btn.find('.choice-text').text().trim();
        var $wrapper = $btn.closest('.genie-response-wrapper');
        var $responseContainer = $btn.closest('[id$="_response"]');
        var $textarea = $wrapper.find('textarea');
        var config = window.eliteTextareaAI.configs[$responseContainer.attr('id')] || {};

        if (selectedVal && $textarea.length) {
            var fieldName = $textarea.attr('id') || $textarea.attr('name');

            if (window.eliteEditors && window.eliteEditors[fieldName]) {
                window.eliteEditors[fieldName].commands.setContent(selectedVal);
            } else {
                $textarea.val(selectedVal).trigger('input').trigger('change');
            }

            runSelectionHook(config, selectedVal, $textarea, $wrapper, $responseContainer);

            if ($responseContainer.length) {
                var savedGenieInstance = $responseContainer.get(0).genieControlInstance;
                if (savedGenieInstance && typeof savedGenieInstance.complete === 'function') {
                    savedGenieInstance.complete(selectedVal);
                }

                $responseContainer.hide();
            }
        }
    });

    $(document).on('click', '.btn-genie-close', function() {
        var $responseContainer = $(this).closest('[id$="_response"]');
        var genie = $responseContainer.length ? $responseContainer.get(0).genieControlInstance : null;

        if (genie && typeof genie.complete === 'function') {
            genie.complete();
        }
        $responseContainer.hide();
    });
})();
JAVASCRIPT);
    }

    $config = [
        'field'             => $ai_field,
        'textareaName'      => $input_name,
        'editorId'          => $safe_input_id,
        'buttonId'          => $button_id,
        'responseId'        => $response_id,
        'endpointUrl'       => $endpoint_url,
        'command'           => $command,
        'aiFields'          => $ai_fields,
        'aiPayloadJs'       => (string)$options['ai_payload_js'],
        'onResponseClickJs' => (string)$options['on_response_click_js'],
    ];
    $config_json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    add_to_jquery("window.eliteTextareaAI && window.eliteTextareaAI.register({$config_json});");

    $extra_attrs = [
        'data-ai-field'           => $ai_field,
        'data-ai-textarea-name'   => $input_name,
        'data-ai-editor-id'       => $safe_input_id,
        'data-ai-endpoint-url'    => $endpoint_url,
        'data-ai-command'         => $command,
        'data-ai-response-target' => $response_id,
        'data-ai-button-id'       => $button_id,
    ];

    if (is_array($options['ai_data'])) {
        foreach ($options['ai_data'] as $data_key => $data_value) {
            $data_key = preg_replace('/[^a-zA-Z0-9_-]/', '-', (string)$data_key);
            $extra_attrs['data-ai-extra-' . $data_key] = $data_value;
        }
    }

    $attr_html = '';
    foreach ($extra_attrs as $attr_name => $attr_value) {
        $attr_html .= ' ' . $attr_name . '="' . htmlspecialchars((string)$attr_value, ENT_QUOTES) . '"';
    }

    $html = '<div class="d-flex flex-column position-relative mb-3 genie-response-wrapper"' . $attr_html . '>';
    $html .= '<div id="' . $response_id . '" style="display:none;" data-ai-field="' . htmlspecialchars($ai_field, ENT_QUOTES) . '" data-ai-response-target="' . htmlspecialchars($response_id, ENT_QUOTES) . '">';
    $html .= '<div class="card">';
    $html .= '<div class="d-flex align-items-center gap-2 w-100 p-2">';
    $html .= get_svg("ai");
    $html .= '<span class="agent-response">' . $options['ai_response_title'] . '</span>';
    $html .= '<button type="button" class="btn btn-genie-close ms-auto btn btn-sm border-0" data-genie-option="close">';
    $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x text-ask-human-pagination-text">';
    $html .= '<path d="M18 6 6 18"></path>';
    $html .= '<path d="m6 6 12 12"></path>';
    $html .= '</svg>';
    $html .= '</button>';
    $html .= '</div>';
    $html .= '<div class="genie-chatbox-response"></div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= form_textarea($input_name, $label, $input_value, $textarea_options);
    $ai_button_data = [
        'ai-field'           => $ai_field,
        'ai-textarea-name'   => $input_name,
        'ai-editor-id'       => $safe_input_id,
        'ai-endpoint-url'    => $endpoint_url,
        'ai-command'         => $command,
        'ai-response-target' => $response_id,
    ];
    $ai_button_attrs = '';
    foreach ($ai_button_data as $data_key => $data_value) {
        $ai_button_attrs .= ' data-' . $data_key . '="' . htmlspecialchars((string)$data_value, ENT_QUOTES) . '"';
    }
    $ai_button = '';
    if (!empty($options['ai_button_enabled'])) {
        $ai_button = '<button id="' . htmlspecialchars($button_id, ENT_QUOTES) . '" title="' . htmlspecialchars((string)$options['ai_button_label'], ENT_QUOTES) . '" class="btn btn-ai btn-sm ms-auto btn-alakazam d-inline-flex align-items-center gap-2 pe-3" name="' . htmlspecialchars($button_id, ENT_QUOTES) . '" value="aihelp" type="button"' . $ai_button_attrs . '>';
        $ai_button .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon genie-magic-star" style="overflow: visible;">';
        $ai_button .= '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z" style="stroke: url(#genie-cosmic-flow); stroke-width: 2.5;" />';
        $ai_button .= '<defs><linearGradient id="genie-cosmic-flow" x1="0%" y1="0%" x2="100%" y2="100%" gradientTransform="rotate(45)"><stop offset="0%" stop-color="#4e54c8" /><stop offset="25%" stop-color="#00ffcc" /><stop offset="50%" stop-color="#fffc00" /><stop offset="75%" stop-color="#ff007f" /><stop offset="100%" stop-color="#4e54c8" /></linearGradient></defs>';
        $ai_button .= '</svg>';
        $ai_button .= '<div class="genie-spinner"></div>';
        $ai_button .= '<span class="genie-btn-text">' . htmlspecialchars((string)$options['ai_button_label'], ENT_QUOTES) . '</span>';
        $ai_button .= '</button>';
    }

    $render_extra_action = static function (array $action, string $fallback_id) {
        $action_id = stripinput((string)($action['id'] ?? $fallback_id));
        $action_label = (string)($action['label'] ?? '');
        $action_icon = (string)($action['icon'] ?? '');
        $action_class = (string)($action['class'] ?? 'btn btn-dark');
        $action_data = is_array($action['data'] ?? NULL) ? $action['data'] : [];
        $action_attr = '';
        foreach ($action_data as $data_key => $data_value) {
            $data_key = preg_replace('/[^a-zA-Z0-9_-]/', '-', (string)$data_key);
            $action_attr .= ' data-' . $data_key . '="' . htmlspecialchars((string)$data_value, ENT_QUOTES) . '"';
        }

        $button = '<button type="button" id="' . htmlspecialchars($action_id, ENT_QUOTES) . '" class="' . htmlspecialchars($action_class, ENT_QUOTES) . '"' . $action_attr . '>';
        $button .= $action_icon !== '' ? $action_icon : htmlspecialchars($action_label, ENT_QUOTES);
        if ($action_icon !== '' && $action_label !== '') {
            $button .= '<span class="visually-hidden">' . htmlspecialchars($action_label, ENT_QUOTES) . '</span>';
        }
        $button .= '</button>';

        return $button;
    };

    $extra_actions = [];
    if (!empty($options['extra_action_enabled'])) {
        $extra_actions[] = [
            'id'    => $options['extra_action_id'] ?: $safe_input_id . '_extra_action',
            'label' => $options['extra_action_label'],
            'icon'  => $options['extra_action_icon'],
            'class' => $options['extra_action_class'],
            'data'  => $options['extra_action_data'],
        ];
    }
    if (is_array($options['extra_actions'])) {
        foreach ($options['extra_actions'] as $extra_action) {
            if (is_array($extra_action)) {
                $extra_actions[] = $extra_action;
            }
        }
    }
    $start_actions_html = '';
    $end_actions_html = '';
    foreach ($extra_actions as $extra_index => $extra_action) {
        $action_html = $render_extra_action($extra_action, $safe_input_id . '_extra_action_' . $extra_index);
        if (($extra_action['align'] ?? '') === 'start') {
            $start_actions_html .= $action_html;
        } else {
            $end_actions_html .= $action_html;
        }
    }

    if ($options['actions_layout'] === 'split') {
        $html .= '<div class="position-absolute bottom-0 start-0 end-0 d-flex align-items-center justify-content-between gap-2 px-3 pb-3" style="pointer-events: none;">';
        $html .= '<div class="elite-textarea-actions-start d-flex align-items-center gap-2" style="pointer-events: auto;">' . $start_actions_html . '</div>';
        $html .= '<div class="genie-status-text text-truncate flex-grow-1 pe-2" style="font-style: italic; opacity: 0.8; font-weight: 500;"></div>';
        $html .= '<div class="' . htmlspecialchars(trim((string)$options['actions_class']), ENT_QUOTES) . '" style="pointer-events: auto;">';
        $html .= '<div class="elite-textarea-actions-end ms-auto text-end d-flex align-items-center justify-content-end gap-2">' . $ai_button . $end_actions_html . '</div>';
        $html .= '</div>';
    } else {
        $html .= '<div class="position-absolute bottom-0 start-0 end-0 d-flex align-items-center justify-content-between px-3 pb-3" style="pointer-events: none;">';
        $html .= '<div class="genie-status-text text-truncate pe-2" style="font-style: italic; opacity: 0.8; font-weight: 500;"></div>';
        $html .= '<div class="' . htmlspecialchars(trim((string)$options['actions_class'] . ' flex-grow-1'), ENT_QUOTES) . '" style="pointer-events: auto;">';
        $html .= $ai_button . $start_actions_html . $end_actions_html;
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    return dynamics_render_component_template('form_elite_textarea', $html);
}

/**
 * SDK-style alias for elite_textarea().
 *
 * Example: form_textarea_ai('observation', 'Observation', '', ['ai_endpoint_url' => BASEDIR . 'api/?api=genie']);
 *
 * @return string
 */
function form_textarea_ai($input_name, $label = '', $input_value = '', array $options = []) {
    return elite_textarea($input_name, $label, $input_value, $options);
}
