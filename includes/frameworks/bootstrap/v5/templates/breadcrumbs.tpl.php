<?php

defined('IN_FUSION') || exit;

/**
 * Render Bootstrap breadcrumbs, adding Tabler's native arrow presentation
 * when the active Bootstrap framework variant is Tabler.
 */
function bootstrap_render_breadcrumbs(array $info): string
{
    $crumbs = (array)($info['breadcrumbs'] ?? []);
    if ($crumbs === []) {
        return '';
    }

    $escape = static fn(mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    $safe_class = static fn(mixed $value): string => trim(
        preg_replace('/[^a-zA-Z0-9_:\-\[\]\/.% ]/', '', (string)$value)
    );
    $classes = preg_split('/\s+/', $safe_class($info['class'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $classes[] = 'breadcrumb';

    if (function_exists('bootstrap_framework_variant') && bootstrap_framework_variant() === 'tabler') {
        $classes[] = 'breadcrumb-arrows';
    }

    $classes = array_values(array_unique($classes));
    $last_key = array_key_last($crumbs);
    $html = '<nav aria-label="'.$escape($info['aria_label'] ?? 'Breadcrumb').'">';
    $html .= '<ol class="'.implode(' ', $classes).'">';

    foreach ($crumbs as $index => $crumb) {
        $is_current = empty($crumb['link']) || $index === $last_key;
        $item_class = $safe_class($crumb['class'] ?? '');
        $html .= '<li class="breadcrumb-item'.($item_class !== '' ? ' '.$item_class : '').
            ($is_current ? ' active" aria-current="page"' : '"').'>';

        if (!$is_current) {
            $plain_title = trim(strip_tags((string)($crumb['title'] ?? '')));
            $html .= '<a href="'.$escape($crumb['link']).'" title="'.$escape($plain_title).'">'.
                ($crumb['title'] ?? '').'</a>';
        } else {
            $html .= $crumb['title'] ?? '';
        }

        $html .= '</li>';
    }

    return $html.'</ol></nav>';
}
