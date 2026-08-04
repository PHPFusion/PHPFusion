<?php

namespace PHPFusion\Apps\UserFields\Account\UserLocation;

use PHPFusion\ProfileGlobal\ProfileContext;

final class CountryOptions
{
    public static function options(
        ProfileContext $context,
        array $field,
        array $module
    ): array {
        $locale = fusion_get_locale();
        $countries = [];
        require __DIR__ . '/data/countries.php';

        $options = ['' => $locale['ulo_102']];
        foreach ($countries as $code => $country) {
            $options[(string)$code] = (string)($country['name'] ?? $code);
        }

        return $options;
    }
}
