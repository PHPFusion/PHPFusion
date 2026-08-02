<?php

namespace PHPFusion\Apps\UserFields\Account\UserState;

use PHPFusion\ProfileGlobal\ProfileContext;

final class StateOptions
{
    public static function options(
        ProfileContext $context,
        array $field,
        array $module
    ): array {
        $locale = fusion_get_locale();
        $countryField = (string)($field['depends_on'] ?? 'user_location');
        $countryCode = strtoupper(trim((string)$context->userValue($countryField)));
        $options = [
            '' => $countryCode === '' ? $locale['ust_103'] : $locale['ust_104'],
        ];

        foreach (self::forCountry($countryCode) as $state) {
            $options[$state] = $state;
        }

        return $options;
    }

    public static function items(string $countryCode): array
    {
        return array_map(
            static fn(string $state): array => ['id' => $state, 'text' => $state],
            self::forCountry($countryCode)
        );
    }

    private static function forCountry(string $countryCode): array
    {
        $states = [];
        require __DIR__ . '/data/states.php';

        return array_values(array_map(
            'strval',
            (array)($states[strtoupper(trim($countryCode))] ?? [])
        ));
    }
}
