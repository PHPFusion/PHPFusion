<?php

defined('IN_FUSION') || exit;

function bootstrap_render_notices(array $input): string
{
    $notices = (array)($input['notices'] ?? $input);
    $options = (array)($input['options'] ?? []);
    $container = !array_key_exists('container', $options) || !empty($options['container']);
    $html = '';

    foreach ($notices as $status => $notice) {
        $tone = preg_replace('/[^a-z-]/', '', strtolower((string)$status)) ?: 'secondary';
        $html .= '<div class="alert alert-'.$tone.' alert-dismissible fade show d-flex align-items-start gap-3" '.
            'role="'.($tone === 'danger' ? 'alert' : 'status').'">';
        $html .= '<ul class="list-unstyled flex-grow-1 mb-0"><li>'.
            implode('</li><li>', (array)$notice).'</li></ul>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }

    return $html !== '' && $container ? '<div class="container-fluid">'.$html.'</div>' : $html;
}
