<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'profile/user_sig/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

return [
    'id'              => 'profile.bio',
    'category'        => 'profile',
    'label'           => $locale['usi_100'],
    'description'     => $locale['usi_101'],
    'icon'            => 'align-left',
    'order'           => 20,
    'default_enabled' => TRUE,
    'public'          => TRUE,
    'header'          => FALSE,
    'field' => [
        'name'        => 'user_sig',
        'label'       => $locale['usi_102'],
        'type'        => 'textarea',
        'storage'     => 'user_column',
        'column'      => 'user_sig',
        'max_length'  => 1000,
        'rows'        => 7,
        'placeholder' => $locale['usi_103'],
        'column_schema' => [
            'type'     => 'text',
            'nullable' => FALSE,
        ],
    ],
];
