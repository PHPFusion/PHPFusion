<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: framework_css.php
| Framework-aware CSS utility translation
+--------------------------------------------------------*/

defined('IN_FUSION') || exit;

/**
 * Register the CSS class translations owned by a framework.
 *
 * Bootstrap/Tabler is PHPFusion's canonical class vocabulary, so it does not
 * need an identity map. Other frameworks translate only known tokens.
 */
function fusion_register_framework_css(string $framework, array $utilities): void
{
    $framework = strtolower(trim($framework));
    if ($framework === '' || !preg_match('/^[a-z0-9][a-z0-9_-]*$/', $framework)) {
        return;
    }

    $GLOBALS['fusion_framework_css'][$framework] = array_replace(
        $GLOBALS['fusion_framework_css'][$framework] ?? [],
        $utilities
    );
}

/**
 * Translate a Bootstrap 5 compatible class list for the active framework.
 *
 * Unknown and project-specific classes are preserved.
 */
function framework_css(string|array $classes): string
{
    $input = is_array($classes) ? $classes : [$classes];
    $tokens = [];

    foreach ($input as $class_list) {
        if (!is_scalar($class_list)) {
            continue;
        }

        foreach (preg_split('/\s+/', trim((string)$class_list), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $token) {
            $tokens[] = $token;
        }
    }

    $framework = fusion_framework_active()['key'] ?? fusion_framework_requested() ?? 'bootstrap';
    $map = $GLOBALS['fusion_framework_css'][$framework] ?? [];
    $translated = [];

    foreach ($tokens as $token) {
        $replacement = $map[$token] ?? $token;
        $replacement = is_array($replacement) ? $replacement : [$replacement];

        foreach ($replacement as $class_list) {
            foreach (preg_split('/\s+/', trim((string)$class_list), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $class) {
                $translated[$class] = TRUE;
            }
        }
    }

    return implode(' ', array_keys($translated));
}
