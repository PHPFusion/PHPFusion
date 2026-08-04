<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'profile/user_realname/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

require_once __DIR__ . '/RealNameModule.php';

return [
    'id'               => 'profile.realname',
    'category'         => 'profile',
    'label'            => $locale['urn_100'],
    'description'      => $locale['urn_101'],
    'icon'             => 'id-card',
    'order'            => 15,
    'default_enabled'  => TRUE,
    'public'           => FALSE,
    'endpoint_handler' => require __DIR__ . '/endpoint.php',
    'class'            => \PHPFusion\Apps\UserFields\Profile\RealName\RealNameModule::class,
    'field_layout'     => 'row',
    'summary_field'    => 'user_realname',
    'field'            => [
        'name'          => 'user_realname',
        'type'          => 'text',
        'storage'       => 'user_column',
        'column'        => 'user_realname',
        'column_schema' => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => '',
            'after'    => 'user_name',
        ],
    ],
];
