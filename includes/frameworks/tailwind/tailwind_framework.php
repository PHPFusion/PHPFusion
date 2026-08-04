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
    $loader_background = $context === 'admin' ? '#090d10' : '#ffffff';
    $loader_track = $context === 'admin' ? 'rgba(255,255,255,.16)' : 'rgba(15,23,42,.16)';
    $loader_accent = $context === 'admin' ? '#f0b90b' : '#2563eb';

    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<style id="fusion-ui-cloak">'
        .':root{--fusion-ui-loader-bg:'.$loader_background.';--fusion-ui-loader-track:'.$loader_track.';--fusion-ui-loader-accent:'.$loader_accent.'}'
        .'html.fusion-ui-adapting{overflow:hidden;background:var(--ui-background,var(--fusion-ui-loader-bg))}'
        .'html.fusion-ui-adapting body{visibility:hidden!important}'
        .'html.fusion-ui-adapting::before{content:"";position:fixed;inset:0;z-index:2147483646;background:var(--ui-background,var(--fusion-ui-loader-bg))}'
        .'html.fusion-ui-adapting::after{content:"";position:fixed;z-index:2147483647;top:50%;left:50%;width:2rem;height:2rem;margin:-1rem 0 0 -1rem;border:2px solid var(--fusion-ui-loader-track);border-top-color:var(--ui-primary,var(--fusion-ui-loader-accent));border-radius:9999px;opacity:0;animation:fusion-ui-loader-reveal .12s ease .15s forwards,fusion-ui-loader-spin .7s linear .15s infinite}'
        .'@keyframes fusion-ui-loader-reveal{to{opacity:1}}'
        .'@keyframes fusion-ui-loader-spin{to{transform:rotate(360deg)}}'
        .'@media(prefers-reduced-motion:reduce){html.fusion-ui-adapting::after{animation:fusion-ui-loader-reveal .12s ease .15s forwards}}'
        .'</style>';
    echo '<script>(function(root){'
        .'root.classList.add("fusion-ui-adapting");root.setAttribute("aria-busy","true");'
        .'window.FusionUI=window.FusionUI||{};'
        .'window.FusionUI.reveal=function(){'
            .'window.clearTimeout(window.FusionUI.cloakTimer);'
            .'root.classList.remove("fusion-ui-adapting");root.classList.add("fusion-ui-ready");root.removeAttribute("aria-busy");'
            .'root.dispatchEvent(new CustomEvent("fusionui:ready",{bubbles:true}));'
        .'};'
        .'window.FusionUI.cloakTimer=window.setTimeout(window.FusionUI.reveal,4000);'
        .'})(document.documentElement);</script>';
    echo '<link rel="stylesheet" href="'.INCLUDES.'frameworks/tailwind/tailwind.css?v='.$css_version.'">';
    echo '<script defer fetchpriority="high" src="'.INCLUDES.'frameworks/tailwind/tailwind.js?v='.$script_version.'"></script>';
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
