<?php

defined('IN_FUSION') || exit;

/**
 * Safe Bootstrap 5 modal renderer for direct framework-component calls.
 * The public theme helpers continue to use their established Bootstrap modal flow.
 */
function bootstrap_render_modal(array $input): string
{
    $part = (string)($input['modal'] ?? '');
    $settings = array_replace($input, (array)($input['options'] ?? []));
    $id = htmlspecialchars((string)($input['id'] ?? $settings['id'] ?? 'bootstrap'), ENT_QUOTES, 'UTF-8');
    $close = htmlspecialchars((string)($settings['close_label'] ?? 'Close'), ENT_QUOTES, 'UTF-8');
    $dismiss = empty($settings['static']) || !empty($settings['close']);

    if ($part === 'open') {
        $size = match ((int)($settings['size'] ?? 3)) {
            1 => 'modal-sm',
            4 => 'modal-xl',
            2 => '',
            default => 'modal-lg',
        };
        $position = match ((string)($settings['position'] ?? 'middle')) {
            'top' => 'modal-dialog-top',
            'bottom' => 'modal-dialog-bottom',
            default => 'modal-dialog-centered',
        };
        $class = trim((string)preg_replace('/[^a-zA-Z0-9_:\-\[\]\/.% ]/', '', (string)($settings['class'] ?? '')));
        $trigger = htmlspecialchars((string)($input['trigger'] ?? ''), ENT_QUOTES, 'UTF-8');
        $auto_show = $trigger === '' && empty($settings['hidden']);
        $html = '<div class="modal fade" id="'.$id.'_Modal" tabindex="-1" '.
            'aria-labelledby="'.$id.'_title" aria-hidden="true"'.(!empty($settings['static'])
                ? ' data-bs-backdrop="static" data-bs-keyboard="false"' : '').
            ($trigger !== '' ? ' data-fusion-modal-trigger="'.$trigger.'"' : '').
            ($auto_show ? ' data-fusion-modal-auto-show="true"' : '').'>';
        $dialog_classes = trim($size.' '.$position.' '.$class);
        $html .= '<div class="modal-dialog '.$dialog_classes.'"><div class="modal-content">';
        if (!empty($input['header_content']) || $dismiss) {
            $html .= '<div class="modal-header">';
            if (!empty($input['header_content'])) {
                $html .= '<h5 class="modal-title" id="'.$id.'_title">'.$input['header_content'].'</h5>';
            }
            if ($dismiss) {
                $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="'.$close.'"></button>';
            }
            $html .= '</div>';
        }

        return $html.'<div class="modal-body">';
    }

    if ($part === 'footer') {
        $html = '</div><div class="modal-footer">'.($input['footer_content'] ?? '');
        if (empty($input['static'])) {
            $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.$close.'</button>';
        }

        return $html;
    }

    if ($part === 'close') {
        return '</div></div></div></div>';
    }

    return '';
}
