<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'account/user_timezone/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

require_once __DIR__ . '/TimezoneOptions.php';

use PHPFusion\Apps\UserFields\Account\UserTimezone\TimezoneOptions;

return [
    'id'              => 'account.timezone',
    'category'        => 'account',
    'label'           => $locale['utz_100'],
    'description'     => $locale['utz_101'],
    'icon'            => 'clock-3',
    'order'           => 10,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'endpoint_handler' => require __DIR__ . '/endpoint.php',
    'option_providers' => [
        'account.timezone.options' => [TimezoneOptions::class, 'options'],
    ],
    'field' => [
        'name'             => 'timezone',
        'label'            => $locale['utz_100'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_timezone',
        'options_provider' => 'account.timezone.options',
        'required'         => TRUE,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 50,
            'nullable' => FALSE,
            'default'  => 'Europe/London',
        ],
    ],
];
