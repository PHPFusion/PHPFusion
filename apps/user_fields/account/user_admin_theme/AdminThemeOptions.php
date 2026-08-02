<?php

namespace PHPFusion\Apps\UserFields\Account\UserAdminTheme;

use PHPFusion\ProfileGlobal\ProfileContext;

final class AdminThemeOptions
{
    public static function options(
        ProfileContext $context,
        array $field,
        array $module
    ): array {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();

        $defaultTheme = (string)($settings['admin_theme'] ?? '');
        $options = [
            'Default' => $defaultTheme !== ''
                ? "{$locale['ato_100']} ({$defaultTheme})"
                : $locale['ato_100'],
        ];

        foreach (glob(THEMES . 'admin_themes/*/acp_theme.php') ?: [] as $themeFile) {
            $theme = basename(dirname($themeFile));
            if (preg_match('/^[a-z0-9_-]{2,50}$/i', $theme)) {
                $options[$theme] = $theme;
            }
        }

        natcasesort($options);

        return ['Default' => $options['Default']]
            + array_diff_key($options, ['Default' => TRUE]);
    }
}
