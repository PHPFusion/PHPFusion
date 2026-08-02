<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'account/user_admin_theme/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

require_once __DIR__ . '/AdminThemeOptions.php';

use PHPFusion\Apps\UserFields\Account\UserAdminTheme\AdminThemeOptions;

return [
    'id'              => 'account.user-admin-theme',
    'category'        => 'account',
    'label'           => $locale['ato_101'],
    'description'     => $locale['ato_102'],
    'icon'            => 'panel-top',
    'order'           => 50,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'audience'        => 'administrator',
    'success_message' => $locale['ato_103'],
    'option_providers' => [
        'account.admin-theme.options' => [AdminThemeOptions::class, 'options'],
    ],
    'field' => [
        'name'             => 'admin_theme',
        'label'            => $locale['ato_101'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_admin_theme',
        'options_provider' => 'account.admin-theme.options',
        'default'          => 'Default',
        'required'         => TRUE,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => 'Default',
            'after'    => 'user_theme',
        ],
    ],
];
