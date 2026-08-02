<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'security/user_hide_email/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

return [
    'id'              => 'security.email-privacy',
    'category'        => 'security',
    'label'           => $locale['uhe_100'],
    'description'     => $locale['uhe_101'],
    'icon'            => 'mail',
    'order'           => 20,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'policies'        => [
        'public_field_visibility' => [
            'target'               => 'email',
            'field'                => 'user_hide_email',
            'allowed_values'       => ['0'],
            'administrator_bypass' => TRUE,
        ],
    ],
    'field' => [
        'name'        => 'hide_email',
        'label'       => $locale['uhe_102'],
        'type'        => 'switch',
        'storage'     => 'user_column',
        'column'      => 'user_hide_email',
        'description' => $locale['uhe_103'],
        'column_schema' => [
            'type'     => 'tinyint',
            'length'   => 1,
            'unsigned' => TRUE,
            'nullable' => FALSE,
            'default'  => 1,
        ],
    ],
];
