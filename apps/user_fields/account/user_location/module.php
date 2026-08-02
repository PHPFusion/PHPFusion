<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'account/user_location/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

require_once __DIR__ . '/CountryOptions.php';

use PHPFusion\Apps\UserFields\Account\UserLocation\CountryOptions;

return [
    'id'              => 'account.location',
    'category'        => 'account',
    'label'           => $locale['ulo_100'],
    'description'     => $locale['ulo_101'],
    'icon'            => 'map-pin',
    'order'           => 12,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'option_providers' => [
        'account.location.countries' => [CountryOptions::class, 'options'],
    ],
    'registration'    => [
        'enabled' => TRUE,
        'order'   => 10,
    ],
    'field' => [
        'name'             => 'user_location',
        'label'            => $locale['ulo_100'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_location',
        'options_provider' => 'account.location.countries',
        'required'         => FALSE,
        'max_length'       => 2,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 50,
            'nullable' => FALSE,
            'default'  => '',
        ],
    ],
];
