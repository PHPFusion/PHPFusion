<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: dynamics.php
| Author: Frederick MC Chan (Chan)
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
 * Class Dynamics
 *
 * @package PHPFusion
 */
class Dynamics {

    private static $instance = NULL;

    private function __construct() {
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->__load_dynamic_components();
        }

        return self::$instance;
    }

    private function __load_dynamic_components() {
        foreach (dynamics_component_manifest() as $component) {
            $model = DYNAMICS.'includes/'.$component.'/model.php';

            if (is_file($model)) {
                require_once $model;
            }
        }
    }
}

/**
 * Ordered Dynamics component registry.
 *
 * form_main must load first because it provides the shared form helpers used
 * by the field models. New field plugins can be added without placing PHP
 * entry points back in the includes root.
 */
function dynamics_component_manifest(): array {
    return [
        'form_main',
        'form_buttons',
        'form_checkbox',
        'form_colorpicker',
        'form_contact',
        'form_datepicker',
        'form_document',
        'form_elite_textarea',
        'form_fileinput',
        'form_geomap',
        'form_hidden',
        'form_modal',
        'form_name',
        'form_ordering',
        'form_paragraph',
        'form_range',
        'form_select',
        'form_text',
        'form_textarea',
        'form_textarea_backup',
    ];
}

/**
 * Render model output through its component-owned template.
 */
function dynamics_render_component_template(string $component, string $html): string {
    if (!in_array($component, dynamics_component_manifest(), TRUE)) {
        return $html;
    }

    $template = DYNAMICS.'includes/'.$component.'/template.php';
    if (!is_file($template)) {
        return $html;
    }

    $rendered = require $template;

    return is_string($rendered) ? $rendered : $html;
}

/**
 * Translate canonical PHPFusion/Bootstrap-compatible class tokens at the
 * template boundary while preserving project-specific classes.
 */
function dynamics_framework_css_template(string $html): string {
    if ($html === '' || !function_exists('framework_css')) {
        return $html;
    }

    return preg_replace_callback(
        '/\\bclass\\s*=\\s*(["\'])(.*?)\\1/is',
        static function (array $matches): string {
            return 'class='.$matches[1].framework_css($matches[2]).$matches[1];
        },
        $html
    ) ?? $html;
}
