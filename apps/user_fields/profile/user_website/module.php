<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'profile/user_website/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

return [
    'id'              => 'profile.website',
    'category'        => 'profile',
    'label'           => $locale['uweb_100'],
    'description'     => $locale['uweb_101'],
    'icon'            => 'globe',
    'order'           => 30,
    'default_enabled' => TRUE,
    'public'          => TRUE,
    'header'          => FALSE,
    'field' => [
        'name'        => 'user_website',
        'label'       => $locale['uweb_102'],
        'type'        => 'url',
        'storage'     => 'user_column',
        'column'      => 'user_website',
        'max_length'  => 255,
        'placeholder' => $locale['uweb_103'],
        'column_schema' => [
            'type'     => 'varchar',
            'length'   => 255,
            'nullable' => FALSE,
            'default'  => '',
            'after'    => 'user_web',
        ],
    ],
];
