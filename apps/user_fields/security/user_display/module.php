<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'security/user_display/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

return [
    'id'              => 'security.profile-visibility',
    'category'        => 'security',
    'label'           => $locale['usd_100'],
    'description'     => $locale['usd_101'],
    'icon'            => 'eye',
    'order'           => 10,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'policies'        => [
        'public_profile_access' => [
            'field'          => 'user_display',
            'allowed_values' => ['1'],
        ],
    ],
    'field' => [
        'name'        => 'profile_visible',
        'label'       => $locale['usd_102'],
        'type'        => 'switch',
        'storage'     => 'user_column',
        'column'      => 'user_display',
        'description' => $locale['usd_103'],
        'column_schema' => [
            'type'     => 'tinyint',
            'length'   => 1,
            'unsigned' => TRUE,
            'nullable' => FALSE,
            'default'  => 1,
        ],
    ],
];
