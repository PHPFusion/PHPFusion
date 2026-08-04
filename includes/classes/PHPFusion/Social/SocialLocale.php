<?php

namespace PHPFusion\Social;

final class SocialLocale {

    private static ?array $locale = NULL;

    public static function all(): array {
        if (self::$locale === NULL) {
            self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'social.php');
        }

        return self::$locale;
    }

    public static function get(string $key): string {
        return (string) (self::all()[$key] ?? $key);
    }

    public static function forLanguage(string $language): array {
        $language = preg_replace('/[^A-Za-z0-9_-]/', '', $language);
        $locale_file = LOCALE.$language.'/social.php';
        if (!is_file($locale_file)) {
            foreach ((array) glob(LOCALE.'*', GLOB_ONLYDIR) as $locale_directory) {
                if (strcasecmp(basename($locale_directory), $language) === 0
                    && is_file($locale_directory.'/social.php')) {
                    $locale_file = $locale_directory.'/social.php';
                    break;
                }
            }
        }
        if (!is_file($locale_file)) {
            $locale_file = LOCALE.'English/social.php';
        }

        $locale = [];
        include $locale_file;

        return $locale;
    }
}
