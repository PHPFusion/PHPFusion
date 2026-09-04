<?php

/**
 * Render an Iconify web component.
 *
 * Legacy PHPFusion SVG keys are normalised when the Tabler collection is used
 * so callers can migrate from ImageRepo without knowing Iconify's exact names.
 */
if (!function_exists('iconify')) {
    function iconify(
        string $icon,
        string $set = 'heroicons-outline',
        string $class = '',
        ?int $size = NULL,
        ?float $strokeWidth = NULL
    ): string
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
        $styles = [];
        if ($size !== NULL) {
            $styles[] = 'font-size:'.max(1, $size).'px';
        }
        if ($strokeWidth !== NULL) {
            $styles[] = '--svg-stroke-width--2px:'.max(0.5, $strokeWidth).'px';
        }

        return '<iconify-icon icon="'.htmlspecialchars($iconName, ENT_QUOTES).'"'.
            ($class !== '' ? ' class="'.htmlspecialchars($class, ENT_QUOTES).'"' : '').
            ($styles ? ' style="'.implode(';', $styles).'"' : '').
            ' aria-hidden="true"></iconify-icon>';
    }
}

if (!function_exists('load_iconify')) {
    function load_iconify(): void
    {
        fusion_load_script('https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js', 'js');
    }
}

load_iconify();
