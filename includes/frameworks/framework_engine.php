<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: framework_engine.php
| Framework discovery and component dispatch
+--------------------------------------------------------*/

defined('IN_FUSION') || exit;

require_once __DIR__.'/framework_css.php';

/**
 * Discover frameworks from includes/frameworks/{framework}/framework_db.php.
 *
 * A framework manifest declares:
 * - $framework_key: stable selection key
 * - $framework_files: files loaded when selected
 */
function fusion_frameworks(): array
{
    static $frameworks;

    if (is_array($frameworks)) {
        return $frameworks;
    }

    $frameworks = [];
    $manifests = glob(__DIR__.'/*/framework_db.php') ?: [];
    sort($manifests, SORT_STRING);

    foreach ($manifests as $manifest) {
        $metadata = (static function (string $file): array {
            $framework_key = '';
            $framework_name = '';
            $framework_description = '';
            $framework_version = '';
            $framework_files = [];

            include $file;

            return compact(
                'framework_key',
                'framework_name',
                'framework_description',
                'framework_version',
                'framework_files'
            );
        })($manifest);

        $directory = dirname($manifest);
        $key = strtolower(trim((string)($metadata['framework_key'] ?: basename($directory))));
        if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/', $key)) {
            continue;
        }

        $metadata['key'] = $key;
        $metadata['directory'] = $directory;
        $frameworks[$key] = $metadata;
    }

    return $frameworks;
}

/**
 * Resolve the framework declared by the active public or admin theme.
 */
function fusion_framework_requested(): ?string
{
    $requested = NULL;

    if (defined('UI_FRAMEWORK')) {
        $requested = UI_FRAMEWORK;
    } elseif (defined('THEME_FRAMEWORK')) {
        $requested = THEME_FRAMEWORK;
    } elseif (defined('BOOTSTRAP') && BOOTSTRAP) {
        $requested = 'bootstrap';
    }

    if (!is_scalar($requested)) {
        return NULL;
    }

    $requested = strtolower(trim((string)$requested));

    return preg_match('/^[a-z0-9][a-z0-9_-]*$/', $requested) ? $requested : NULL;
}

/**
 * Load the selected framework once.
 */
function fusion_framework_boot(string $context = 'site'): ?array
{
    static $booted = FALSE;
    static $active;

    if ($booted) {
        return $active;
    }

    $booted = TRUE;
    $requested = fusion_framework_requested();
    $frameworks = fusion_frameworks();

    if ($requested === NULL || !isset($frameworks[$requested])) {
        return NULL;
    }

    $active = $frameworks[$requested];
    $active['context'] = $context;
    $root = realpath($active['directory']);

    foreach ((array)$active['framework_files'] as $relative_file) {
        $file = realpath($active['directory'].'/'.ltrim((string)$relative_file, '/\\'));
        if ($root && $file && str_starts_with($file, $root.DIRECTORY_SEPARATOR) && is_file($file)) {
            require_once $file;
        }
    }

    $GLOBALS['fusion_framework_active'] = $active;

    return $active;
}

function fusion_framework_active(): ?array
{
    return $GLOBALS['fusion_framework_active'] ?? NULL;
}

/**
 * Register project-owned component translators for the active framework.
 *
 * Each definition accepts:
 * - file: PHP template to require
 * - callback: renderer called with the component information array
 */
function fusion_register_framework_components(string $framework, array $components): void
{
    $active = fusion_framework_active();
    $requested = fusion_framework_requested();

    if (($active['key'] ?? $requested) !== $framework) {
        return;
    }

    foreach ($components as $name => $definition) {
        if (is_string($name) && is_array($definition)) {
            $GLOBALS['fusion_framework_components'][$name] = $definition;
        }
    }
}

/**
 * Render a semantic component through the active framework translator.
 */
function fusion_render_framework_component(string $component, array $info = []): string
{
    $definition = $GLOBALS['fusion_framework_components'][$component] ?? NULL;
    if (!is_array($definition)) {
        return '';
    }

    $file = $definition['file'] ?? '';
    if ($file !== '') {
        $file = realpath((string)$file);
        $active_root = realpath((string)(fusion_framework_active()['directory'] ?? ''));
        if (!$file || !$active_root || !str_starts_with($file, $active_root.DIRECTORY_SEPARATOR)) {
            return '';
        }
        require_once $file;
    }

    $callback = $definition['callback'] ?? NULL;

    $info['_framework_component'] = $component;

    return is_callable($callback) ? (string)call_user_func($callback, $info) : '';
}
