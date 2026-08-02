<?php

/**
 * Render an Iconify web component.
 *
 * Legacy PHPFusion SVG keys are normalised when the Tabler collection is used
 * so callers can migrate from ImageRepo without knowing Iconify's exact names.
 */
if (!function_exists('iconify')) {
    function iconify(string $icon, string $set = 'heroicons-outline', string $class = ''): string
    {
        $aliases = [
            'cancel' => 'circle-x',
            'delete' => 'trash',
            'print' => 'printer',
            'reset' => 'restore',
            'whatsapp' => 'brand-whatsapp',
        ];
        $icon = trim($icon);
        if ($set === 'tabler' && !str_contains($icon, ':')) {
            $icon = $aliases[$icon] ?? $icon;
        }
        $iconName = str_contains($icon, ':') ? $icon : $set.':'.$icon;

        return '<iconify-icon icon="'.htmlspecialchars($iconName, ENT_QUOTES).'"'.
            ($class !== '' ? ' class="'.htmlspecialchars($class, ENT_QUOTES).'"' : '').
            ' aria-hidden="true"></iconify-icon>';
    }
}

if (!function_exists('load_iconify')) {
    function load_iconify(): void
    {
        fusion_load_script('https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js', 'js');
    }
}

load_iconify();
