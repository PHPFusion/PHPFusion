<?php

defined('IN_FUSION') || exit;

/**
 * Render the shadcn breadcrumb anatomy for the Tailwind framework.
 */
function tailwind_render_breadcrumbs(array $info): string
{
    $crumbs = (array)($info['breadcrumbs'] ?? []);
    if ($crumbs === []) {
        return '';
    }

    $escape = static fn(mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    $safe_class = static fn(mixed $value): string => trim(
        preg_replace('/[^a-zA-Z0-9_:\-\[\]\/.% ]/', '', (string)$value)
    );
    $first_key = array_key_first($crumbs);
    $last_key = array_key_last($crumbs);
    $list_class = $safe_class($info['class'] ?? '');

    $html = '<nav class="tw-breadcrumb-nav" aria-label="'.
        $escape($info['aria_label'] ?? 'Breadcrumb').'">';
    $html .= '<ol class="tw-breadcrumb-list'.($list_class !== '' ? ' '.$list_class : '').'">';

    foreach ($crumbs as $index => $crumb) {
        if ($index !== $first_key) {
            $html .= '<li class="tw-breadcrumb-separator" role="presentation" aria-hidden="true">';
            $html .= '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" '.
                'stroke-linecap="round" stroke-linejoin="round" focusable="false">'.
                '<path d="m9 18 6-6-6-6"></path></svg></li>';
        }

        $item_class = $safe_class($crumb['class'] ?? '');
        $is_current = empty($crumb['link']) || $index === $last_key;
        $html .= '<li class="tw-breadcrumb-item'.($item_class !== '' ? ' '.$item_class : '').'">';

        if (!$is_current) {
            $plain_title = trim(strip_tags((string)($crumb['title'] ?? '')));
            $html .= '<a class="tw-breadcrumb-link" href="'.$escape($crumb['link']).
                '" title="'.$escape($plain_title).'">'.($crumb['title'] ?? '').'</a>';
        } else {
            $html .= '<span class="tw-breadcrumb-page" aria-current="page">'.
                ($crumb['title'] ?? '').'</span>';
        }

        $html .= '</li>';
    }

    return $html.'</ol></nav>';
}
