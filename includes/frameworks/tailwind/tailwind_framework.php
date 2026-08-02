<?php

defined('IN_FUSION') || exit;

$tailwind_css = require __DIR__.'/tailwind_css.php';
fusion_register_framework_css('tailwind', $tailwind_css);

function tailwind_framework_header(string $context = 'site'): void
{
    $css = __DIR__.'/tailwind.css';
    $script = __DIR__.'/tailwind.js';
    $css_version = is_file($css) ? filemtime($css) : '1';
    $script_version = is_file($script) ? filemtime($script) : '1';

    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<link rel="stylesheet" href="'.INCLUDES.'frameworks/tailwind/tailwind.css?v='.$css_version.'">';
    echo '<script defer src="'.INCLUDES.'frameworks/tailwind/tailwind.js?v='.$script_version.'"></script>';
}

fusion_add_hook('fusion_framework_header', 'tailwind_framework_header', 10, [], 1);

$tailwind_template = __DIR__.'/templates/components.tpl.php';
$tailwind_breadcrumb_template = __DIR__.'/templates/breadcrumbs.tpl.php';
fusion_register_framework_components('tailwind', [
    'breadcrumbs' => ['file' => $tailwind_breadcrumb_template, 'callback' => 'tailwind_render_breadcrumbs'],
    'showsublinks' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_navbar'],
    'form_inputs' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_dynamic_ui'],
    'alert' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_alert'],
    'badge' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_badge'],
    'progress' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_progress'],
    'collapse' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_collapse'],
    'tabs' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_tabs'],
    'modal' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_modal'],
    'notices' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_notices'],
    'openside' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_admin_component'],
    'closeside' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_admin_component'],
    'opengrid' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_admin_component'],
    'closegrid' => ['file' => $tailwind_template, 'callback' => 'tailwind_render_admin_component'],
]);
