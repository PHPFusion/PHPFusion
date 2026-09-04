<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: genie_ui.php
| Author: PHPFusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license.
+--------------------------------------------------------*/

use Defender\Token;

defined('IN_FUSION') || exit;

/**
 * Add the reusable Genie interface to rendered textarea markup.
 *
 * The textarea remains owned by form_textarea(); this decorator owns only
 * Genie presentation and behaviour. A plain textarea and a Tiptap textarea
 * are both supported.
 *
 * Basic usage:
 *
 *     echo genie_ui(form_textarea('notes', 'Notes'));
 *
 * @param string $textarea Rendered form_textarea() markup.
 * @param array  $options  Genie configuration:
 *                         - endpoint: POST endpoint. [api/?api=genie]
 *                         - field: Logical prompt field. [textarea name]
 *                         - command: Genie command. [improvisation]
 *                         - namespace: Application AI namespace.
 *                         - task_key: Application AI task key.
 *                         - button_label: Trigger label. [Genie, improve this]
 *                         - response_title: Suggestion panel title.
 *                         - response_id: Optional application-owned response region id.
 *                         - show_trigger: Render the standard trigger. [TRUE]
 *                         - data: Fixed request context.
 *                         - fields: Request field => editor id map.
 *                         - payload_callback: Named window function returning live data.
 *                         - selection_callback: Named window function run for a selected choice.
 *                         - response_callback: Named window function run for success and failure responses.
 *                         - apply_selection: Put a selected choice in the textarea. [TRUE]
 *                         - extra_actions: Additional developer-owned action buttons.
 *                         - actions_layout: inline or split. [inline]
 *                         - actions_class: Additional class for the action row.
 *
 * @return string
 */
function genie_ui(string $textarea, array $options = []): string {
    if (trim($textarea) === '') {
        return '';
    }

    $textarea_id = '';
    $textarea_name = '';
    if (preg_match('/<textarea\b[^>]*\bid=(["\'])(.*?)\1/i', $textarea, $match)) {
        $textarea_id = html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('/<textarea\b[^>]*\bname=(["\'])(.*?)\1/i', $textarea, $match)) {
        $textarea_name = html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');
    }
    if ($textarea_id === '') {
        return $textarea;
    }

    $safe_id = preg_replace('/[^a-zA-Z0-9_-]/', '_', $textarea_id);
    $defaults = [
        'endpoint'           => defined('BASEDIR') ? BASEDIR.'api/?api=genie' : '',
        'field'              => $textarea_name !== '' ? $textarea_name : $textarea_id,
        'command'            => 'improvisation',
        'namespace'          => '',
        'task_key'           => '',
        'button_label'       => 'Genie, improve this',
        'response_title'     => 'Genie suggestions',
        'response_id'        => '',
        'show_trigger'       => TRUE,
        'data'               => [],
        'fields'             => [],
        'payload_callback'   => '',
        'selection_callback' => '',
        'response_callback'  => '',
        'apply_selection'    => TRUE,
        'extra_actions'      => [],
        'actions_layout'     => 'inline',
        'actions_class'      => '',
    ];
    $options += $defaults;

    $trigger_id = $safe_id.'_genie_trigger';
    $form_id = 'genie_'.$safe_id;
    $response_id = $options['response_id'] !== ''
        ? preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$options['response_id'])
        : $safe_id.'_response';
    $config = [
        'editorId'          => $textarea_id,
        'textareaName'      => $textarea_name,
        'field'             => stripinput((string)$options['field']),
        'endpoint'          => (string)$options['endpoint'],
        'command'           => stripinput((string)$options['command']),
        'namespace'         => stripinput(strtolower(trim((string)$options['namespace']))),
        'taskKey'           => stripinput(strtolower(trim((string)$options['task_key']))),
        'formId'            => $form_id,
        'buttonLabel'       => (string)$options['button_label'],
        'triggerId'         => $trigger_id,
        'responseId'        => $response_id,
        'data'              => is_array($options['data']) ? $options['data'] : [],
        'fields'            => is_array($options['fields']) ? $options['fields'] : [],
        'payloadCallback'   => (string)$options['payload_callback'],
        'selectionCallback' => (string)$options['selection_callback'],
        'responseCallback'  => (string)$options['response_callback'],
        'applySelection'    => (bool)$options['apply_selection'],
    ];
    $config_json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';

    fusion_load_script(BASEDIR.'assets/libs/genie/dist/genie.css', 'css');
    fusion_load_script(BASEDIR.'assets/libs/genie/dist/genie.min.js');

    $escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $render_action = static function (array $action, string $fallback_id) use ($escape): string {
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)($action['id'] ?? $fallback_id));
        $label = (string)($action['label'] ?? 'Action');
        $class = trim('btn '.(string)($action['class'] ?? 'btn-outline-secondary'));
        $icon = (string)($action['icon'] ?? '');
        $attributes = '';
        foreach ((array)($action['data'] ?? []) as $key => $value) {
            $key = preg_replace('/[^a-zA-Z0-9_-]/', '-', (string)$key);
            $attributes .= ' data-'.$key.'="'.$escape($value).'"';
        }
        $html = '<button type="button" id="'.$escape($id).'" class="'.$escape($class).'"'.$attributes.'>';
        $html .= $icon !== '' ? $icon : $escape($label);
        if ($icon !== '') {
            $html .= '<span class="visually-hidden">'.$escape($label).'</span>';
        }
        return $html.'</button>';
    };

    $start_actions = '';
    $end_actions = '';
    foreach ((array)$options['extra_actions'] as $index => $action) {
        if (!is_array($action)) {
            continue;
        }
        $button = $render_action($action, $safe_id.'_genie_action_'.$index);
        if (($action['align'] ?? '') === 'start') {
            $start_actions .= $button;
        } else {
            $end_actions .= $button;
        }
    }

    $trigger = '';
    if ($options['show_trigger']) {
        $trigger = '<button type="button" id="'.$escape($trigger_id).'" class="btn btn-outline-secondary btn-sm genie-ui-trigger" aria-controls="'.$escape($response_id).'">'
            .'<svg class="genie-ui-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3Z"/></svg>'
            .'<span class="genie-ui-spinner" aria-hidden="true"></span>'
            .'<span class="genie-ui-trigger-label">'.$escape($options['button_label']).'</span>'
            .'</button>';
    }

    $actions_class = trim('genie-ui-actions d-flex align-items-center gap-2 '.(string)$options['actions_class']);
    $html = '<div class="genie-ui position-relative mb-3" data-genie-config="'.$escape($config_json).'">';
    if ($options['namespace'] !== '' && $options['task_key'] !== '') {
        $html .= '<input type="hidden" data-genie-form-id value="'.$escape($form_id).'">';
        $html .= '<input type="hidden" data-genie-fusion-token value="'.$escape(Token::generate_token($form_id)).'">';
    }
    $html .= '<section id="'.$escape($response_id).'" class="genie-ui-response card" hidden aria-live="polite" aria-label="'.$escape($options['response_title']).'">';
    $html .= '<header class="genie-ui-response-header d-flex align-items-center gap-2 p-2">';
    $html .= '<svg class="genie-ui-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3Z"/></svg>';
    $html .= '<strong class="genie-ui-response-title">'.$escape($options['response_title']).'</strong>';
    $html .= '<button type="button" class="btn btn-ghost btn-icon btn-sm ms-auto genie-ui-close" aria-label="Close Genie suggestions"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>';
    $html .= '</header><div class="genie-ui-choices"></div></section>';
    $html .= $textarea;

    if ($options['actions_layout'] === 'split') {
        $html .= '<div class="'.$escape($actions_class).' justify-content-between">';
        $html .= '<div class="genie-ui-actions-start d-flex align-items-center gap-2">'.$start_actions.'</div>';
        $html .= '<div class="genie-ui-status text-truncate flex-grow-1" role="status" aria-live="polite"></div>';
        $html .= '<div class="genie-ui-actions-end d-flex align-items-center gap-2">'.$trigger.$end_actions.'</div>';
        $html .= '</div>';
    } else {
        $html .= '<div class="'.$escape($actions_class).' justify-content-end">';
        $html .= '<div class="genie-ui-status text-truncate me-auto" role="status" aria-live="polite"></div>';
        $html .= $start_actions.$trigger.$end_actions.'</div>';
    }
    $html .= '</div>';

    return dynamics_framework_css_template($html);
}
