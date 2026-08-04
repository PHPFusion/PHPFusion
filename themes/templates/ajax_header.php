<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ajax_header.php
| Shared theme and framework bootstrap for AJAX fragments
+--------------------------------------------------------*/

defined('IN_FUSION') || exit;

require_once INCLUDES.'ajax_include.php';

$ajaxThemeContext = defined('AJAX_THEME_CONTEXT')
    ? strtolower((string)AJAX_THEME_CONTEXT)
    : 'site';
$ajaxThemeContext = in_array($ajaxThemeContext, ['admin', 'site'], TRUE)
    ? $ajaxThemeContext
    : 'site';

// A direct server-side invocation can already have a theme loaded. In that
// case, retain its declared framework and only ensure the engine is booted.
if (!defined('UI_FRAMEWORK')) {
    $settings = fusion_get_settings();

    if ($ajaxThemeContext === 'admin') {
        $userdata = fusion_get_userdata();
        $globalAdminTheme = (string)($settings['admin_theme'] ?? '');
        $userAdminTheme = (string)($userdata['user_admin_theme'] ?? 'Default');
        $adminTheme = $userAdminTheme !== 'Default' ? $userAdminTheme : $globalAdminTheme;
        $adminThemeIsValid = static fn(string $theme): bool =>
            (bool)preg_match('/^[a-z0-9_-]{2,50}$/i', $theme)
            && is_file(THEMES.'admin_themes/'.$theme.'/acp_theme.php');

        if (!$adminThemeIsValid($adminTheme) && $adminTheme !== $globalAdminTheme) {
            $adminTheme = $globalAdminTheme;
        }

        if (!$adminThemeIsValid($adminTheme)) {
            throw new RuntimeException('The configured admin theme is not valid for this AJAX request.');
        }

        if (!defined('ADMIN_THEME_NAME')) {
            define('ADMIN_THEME_NAME', $adminTheme);
        }
        require_once THEMES.'admin_themes/'.$adminTheme.'/acp_theme.php';
    } else {
        $siteTheme = (string)($settings['theme'] ?? '');
        $siteThemeFile = THEMES.$siteTheme.'/theme.php';

        if (!preg_match('/^[a-z0-9_-]{2,50}$/i', $siteTheme) || !is_file($siteThemeFile)) {
            throw new RuntimeException('The configured site theme is not valid for this AJAX request.');
        }

        require_once $siteThemeFile;
    }
}

require_once INCLUDES.'frameworks/framework_engine.php';
fusion_framework_boot($ajaxThemeContext);

// API requests intentionally skip page-only Dynamics initialization in
// maincore. AJAX fragments that render form components need the same models.
Dynamics::getInstance();
