<?php

namespace PHPFusion\Apps\UserFields\Account\UserTimezone;

use PHPFusion\ProfileGlobal\ProfileContext;

final class TimezoneOptions
{
    private static ?array $fallbackOptions = NULL;

    public static function options(
        ProfileContext $context,
        array $field,
        array $module
    ): array {
        if (function_exists('get_timezones')) {
            return (array)get_timezones();
        }

        return self::fallbackOptions();
    }

    private static function fallbackOptions(): array
    {
        if (self::$fallbackOptions !== NULL) {
            return self::$fallbackOptions;
        }

        self::$fallbackOptions = [];
        $timezoneFile = defined('INCLUDES') ? INCLUDES . 'geomap/timezones.json' : '';
        $timezones = $timezoneFile !== '' && is_file($timezoneFile)
            ? json_decode((string)file_get_contents($timezoneFile), TRUE)
            : [];

        if (!is_array($timezones)) {
            return self::$fallbackOptions;
        }

        foreach ($timezones as $zone => $zoneCity) {
            try {
                $date = new \DateTime('now', new \DateTimeZone((string)$zone));
            } catch (\Exception) {
                continue;
            }

            $offset = $date->getOffset() / 3600;
            self::$fallbackOptions[(string)$zone] = '(GMT' . ($offset < 0 ? $offset : '+' . $offset) . ') ' . (string)$zoneCity;
        }

        return self::$fallbackOptions;
    }
}
