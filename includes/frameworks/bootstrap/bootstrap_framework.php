<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bootstrap_framework.php
| Author: meangczac (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Get bootstrap framework file paths
 *
 * @param        $part
 * @param string $version
 *
 * @return string
 */
function get_bootstrap($part, $version = '5') {

    static $framework_paths = [];

    if ( empty( $framework_paths ) ) {

        if ( $version < 3 ) {
            $version = 3;
        } else if ( $version > 5 ) {
            $version = 5;
        }
        $version = 'v' . $version;

        // Headers and footers
        require_once __DIR__ . '/' . $version . '/index.php';

        $_dir = __DIR__ . '/' . $version . '/templates/';

        $framework_paths['php'] = [
            /**
             * Menu
             * @uses render_navbar()
             */
            'showsublinks' => ['dir' => $_dir, 'file' => 'navbar.tpl.php', 'callback'=>'render_navbar'],
            /**
             * Dynamics UI
             * @uses render_dynamic_ui()
             */
            'form_inputs'  => ['dir' => $_dir, 'file' => 'dynamics-ui.tpl.php', 'callback'=>'render_dynamic_ui'],
            /**
             * Alerts
             * @uses bootstrap_render_alert()
             */
            'alert' => ['dir' => $_dir, 'file' => 'components.tpl.php', 'callback' => 'bootstrap_render_alert'],
            /**
             * Labels and badges
             * @uses bootstrap_render_badge()
             */
            'badge' => ['dir' => $_dir, 'file' => 'components.tpl.php', 'callback' => 'bootstrap_render_badge'],
            /**
             * Progress indicators
             * @uses bootstrap_render_progress()
             */
            'progress' => ['dir' => $_dir, 'file' => 'components.tpl.php', 'callback' => 'bootstrap_render_progress'],
            /**
             * Collapse UI
             * @uses render_collapse()
             */
            'collapse'     => ['dir' => $_dir, 'file' => 'collapse.tpl.php', 'callback'=>'bootstrap_render_collapse'],
            /**
             * Tabs UI
             * @uses render_tabs()
             */
            'tabs' => ['dir' => $_dir, 'file' => 'tabs.tpl.php', 'callback'=>'bootstrap_render_tabs'],
            /**
             * Modal UI
             * @uses render_modal()
             */
            'modal' =>  ['dir' => $_dir, 'file' => 'modal.tpl.php', 'callback'=>'bootstrap_render_modal'],
            /**
             * Notice UI
             * @uses render_notices()
             */
            'notices' => ['dir' => $_dir, 'file' => 'notices.tpl.php', 'callback'=>'bootstrap_render_notices'],
            /**
             * Breadcrumb navigation
             * @uses bootstrap_render_breadcrumbs()
             */
            'breadcrumbs' => ['dir' => $_dir, 'file' => 'breadcrumbs.tpl.php', 'callback'=>'bootstrap_render_breadcrumbs'],
        ];
    }

    $_type = 'php';

    return $framework_paths[$_type][$part] ?? '';
}

/**
 * Resolve the Bootstrap presentation variant selected by the active theme.
 */
function bootstrap_framework_variant(): string
{
    $variant = defined('UI_FRAMEWORK_VARIANT')
        ? strtolower(trim((string)UI_FRAMEWORK_VARIANT))
        : 'bootstrap';

    return preg_match('/^[a-z0-9][a-z0-9_-]*$/', $variant) ? $variant : 'bootstrap';
}

$bootstrap_version = defined('UI_FRAMEWORK_VERSION')
    ? (string)UI_FRAMEWORK_VERSION
    : (defined('BOOTSTRAP') && is_numeric(BOOTSTRAP) ? (string)BOOTSTRAP : '5');

if (bootstrap_framework_variant() === 'tabler') {
    require_once __DIR__.'/tabler/svgs/tabler.php';
}

get_bootstrap('load', $bootstrap_version);
fusion_add_hook('fusion_framework_header', 'bootstrap_header', 10, [], 1);
fusion_add_hook('fusion_framework_footer', 'bootstrap_footer', 10, [], 1);

$bootstrap_components = [];
foreach (['showsublinks', 'form_inputs', 'alert', 'badge', 'progress', 'collapse', 'tabs', 'modal', 'notices', 'breadcrumbs'] as $component) {
    if ($definition = get_bootstrap($component, $bootstrap_version)) {
        $bootstrap_components[$component] = [
            'file' => $definition['dir'].$definition['file'],
            'callback' => $definition['callback']
        ];
    }
}
fusion_register_framework_components('bootstrap', $bootstrap_components);
