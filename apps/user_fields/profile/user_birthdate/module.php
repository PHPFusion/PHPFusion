<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'profile/user_birthdate/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

return [
    'id'               => 'profile.birthdate',
    'category'         => 'profile',
    'label'            => $locale['ubd_100'],
    'description'      => $locale['ubd_101'],
    'icon'             => 'calendar-days',
    'order'            => 17,
    'default_enabled'  => TRUE,
    'public'           => FALSE,
    'success_message'  => $locale['ubd_103'],
    'endpoint_handler' => require __DIR__ . '/endpoint.php',
    'registration'     => [
        'enabled' => TRUE,
        'order'   => 30,
    ],
    'field'            => [
        'name'                => 'user_birthdate',
        'label'               => $locale['ubd_100'],
        'type'                => 'date',
        'storage'             => 'user_column',
        'column'              => 'user_birthdate',
        'required'            => FALSE,
        'date_message'        => $locale['ubd_102'],
        'display_format'      => 'd / m / Y',
        'empty_storage_value' => '1900-01-01',
        'column_schema'       => [
            'type'     => 'date',
            'nullable' => FALSE,
            'default'  => '1900-01-01',
            'after'    => 'user_location',
        ],
    ],
];
