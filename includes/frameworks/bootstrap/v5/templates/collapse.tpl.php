<?php

defined('IN_FUSION') || exit;

/**
 * Render a Bootstrap 5 accordion using the shared collapse helper contract.
 */
function bootstrap_render_collapse(array $options): string
{
    $callback = (string)($options['callback'] ?? '');
    $id = htmlspecialchars((string)($options['id'] ?? 'collapse'), ENT_QUOTES, 'UTF-8');

    if ($callback === 'opencollapse') {
        return '<div class="accordion" id="'.$id.'-accordion">';
    }
    if ($callback === 'closecollapse') {
        return '</div>';
    }
    if ($callback === 'closecollapsebody') {
        return '</div></div></div>';
    }
    if ($callback !== 'opencollapsebody') {
        return '';
    }

    $group = htmlspecialchars((string)($options['group_id'] ?? ''), ENT_QUOTES, 'UTF-8');
    $class = trim((string)preg_replace('/[^a-zA-Z0-9_:\-\[\]\/.% ]/', '', (string)($options['class'] ?? '')));
    $title = $options['title'] ?? '';
    $active = !empty($options['active']);
    $expanded = $active ? 'true' : 'false';
    $collapsed = $active ? '' : ' collapsed';
    $show = $active ? ' show' : '';
    $heading = max(2, min(6, (int)($options['heading_size'] ?? 2)));
    $html = '<div class="accordion-item'.($class !== '' ? ' '.$class : '').'">';

    if (!empty($options['custom_header'])) {
        $html .= $options['custom_header'];
    } elseif (($options['type'] ?? '') === 'admin_header') {
        $html .= '<div class="d-flex align-items-center gap-3 bg-body-tertiary px-3 py-2">';
        $html .= '<div class="card-title mb-0 flex-grow-1">'.$title.'</div>';
        $html .= '<button class="btn btn-sm btn-outline-secondary adm-btn'.$collapsed.'" type="button" '.
            'data-bs-toggle="collapse" data-bs-target="#'.$id.'-collapse" aria-expanded="'.$expanded.
            '" aria-controls="'.$id.'-collapse" data-label-expand="Expand" data-label-close="Close">'.
            ($active ? 'Close' : 'Expand').'</button></div>';
    } else {
        $html .= '<h'.$heading.' class="accordion-header" id="'.$id.'-collapse-heading">';
        $html .= '<button class="accordion-button'.$collapsed.'" type="button" data-bs-toggle="collapse" '.
            'data-bs-target="#'.$id.'-collapse" aria-expanded="'.$expanded.'" aria-controls="'.$id.'-collapse">'.
            $title.'</button></h'.$heading.'>';
    }

    $html .= '<div id="'.$id.'-collapse" class="accordion-collapse collapse'.$show.'" '.
        'aria-labelledby="'.$id.'-collapse-heading"'.
        ($group !== '' ? ' data-bs-parent="#'.$group.'-accordion"' : '').'>';

    return $html.'<div class="accordion-body">';
}
