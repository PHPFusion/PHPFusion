<?php

namespace PHPFusion\Apps\UserFields\Account\UserTimezone;

use PHPFusion\ProfileGlobal\ProfileContext;

final class TimezoneOptions
{
    public static function options(
        ProfileContext $context,
        array $field,
        array $module
    ): array {
        return function_exists('get_timezones') ? (array)get_timezones() : [];
    }
}
