<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'profile/user_avatar/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

require_once __DIR__ . '/AvatarModule.php';

return [
    'id'              => 'profile.avatar',
    'category'        => 'profile',
    'label'           => $locale['uav_100'],
    'description'     => $locale['uav_101'],
    'icon'            => 'camera',
    'order'           => 10,
    'default_enabled' => TRUE,
    'essential'       => TRUE,
    'public'          => TRUE,
    'header'          => TRUE,
    'header_slot'     => 'avatar',
    'class'           => \PHPFusion\Apps\UserFields\Profile\Avatar\AvatarModule::class,
    'field'           => [
        'name'          => 'avatar_file',
        'storage'       => 'user_column',
        'column'        => 'user_avatar',
        'column_schema' => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => '',
        ],
    ],
];
