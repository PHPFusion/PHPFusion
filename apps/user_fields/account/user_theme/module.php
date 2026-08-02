<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'account/user_theme/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

require_once __DIR__ . '/ThemeOptions.php';

use PHPFusion\Apps\UserFields\Account\UserTheme\ThemeOptions;

return [
    'id'              => 'account.user-theme',
    'category'        => 'account',
    'label'           => $locale['uth_101'],
    'description'     => $locale['uth_102'],
    'icon'            => 'palette',
    'order'           => 40,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'success_message' => $locale['uth_103'],
    'option_providers' => [
        'account.site-theme.options' => [ThemeOptions::class, 'options'],
    ],
    'field' => [
        'name'             => 'theme',
        'label'            => $locale['uth_101'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_theme',
        'options_provider' => 'account.site-theme.options',
        'default'          => 'Default',
        'required'         => TRUE,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => 'Default',
        ],
    ],
];
