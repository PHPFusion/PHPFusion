<?php

defined('IN_FUSION') || exit;

function pro_admin_component_tpl(string $component, array $info = []): string
{
    $class = trim((string)($info['class'] ?? ''));
    $class_attribute = $class !== '' ? ' '.htmlspecialchars($class, ENT_QUOTES) : '';

    if ($component === 'openside') {
        $value = $info['value'] ?? '';
        $collapse = $info['collapse'] ?? FALSE;
        $html = '<aside class="pf-side'.$class_attribute.'">';

        if ($value || $collapse) {
            $html .= '<div class="pf-side-header">';
            $html .= '<div class="side-header-title">'.$value.'</div>';

            if ($collapse) {
                if ($collapse == 1) {
                    $html .= '<button class="side-header-btn" data-toggle="sidex"><span>Expand</span></button>';
                } else {
                    $html .= $collapse;
                }
            }

            $html .= '</div>';
        }

        $body_style = !$collapse ? ' style="display:block !important;margin-top:0;"' : '';
        $html .= '<div class="pf-side-body"'.$body_style.'>';

        return $html;
    }

    if ($component === 'closeside') {
        return '</div></aside>';
    }

    if ($component === 'opengrid') {
        $count = max(1, (int)($info['value'] ?? 1));

        return '<div class="pf-grid'.$class_attribute.'" style="grid-template-columns: repeat('.
            $count.', 1fr);">';
    }

    if ($component === 'closegrid') {
        return '</div>';
    }

    return '';
}
