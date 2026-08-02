<?php

namespace PHPFusion\Apps\UserFields\Account\UserTheme;

use PHPFusion\ProfileGlobal\ProfileContext;

final class ThemeOptions
{
    public static function options(
        ProfileContext $context,
        array $field,
        array $module
    ): array {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        $defaultTheme = (string)($settings['theme'] ?? '');
        $options = [
            'Default' => $defaultTheme !== ''
                ? "{$locale['uth_100']} ({$defaultTheme})"
                : $locale['uth_100'],
        ];

        foreach (glob(THEMES . '*/theme.php') ?: [] as $themeFile) {
            $theme = basename(dirname($themeFile));
            if (function_exists('theme_exists') && theme_exists($theme)) {
                $options[$theme] = $theme;
            }
        }

        natcasesort($options);

        return ['Default' => $options['Default']]
            + array_diff_key($options, ['Default' => TRUE]);
    }
}
