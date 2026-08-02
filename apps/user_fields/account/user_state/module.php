<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'account/user_state/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

require_once __DIR__ . '/StateOptions.php';
require_once __DIR__ . '/StateOptionsEndpoint.php';

use PHPFusion\Apps\UserFields\Account\UserState\StateOptions;
use PHPFusion\Apps\UserFields\Account\UserState\StateOptionsEndpoint;

return [
    'id'              => 'account.state',
    'category'        => 'account',
    'label'           => $locale['ust_100'],
    'description'     => $locale['ust_101'],
    'icon'            => 'map',
    'order'           => 14,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'requires'        => ['account.location'],
    'option_providers' => [
        'account.state.options' => [StateOptions::class, 'options'],
    ],
    'api_endpoints'   => [
        'account.state.options' => [
            'handler'  => [StateOptionsEndpoint::class, 'handle'],
            'route'    => '/v1/profile-modules/account/state/options',
            'methods'  => ['GET'],
            'aliases'  => ['account-state-options'],
            'channels' => ['http', 'direct'],
        ],
    ],
    'registration'    => [
        'enabled' => TRUE,
        'order'   => 20,
    ],
    'field' => [
        'name'             => 'user_state',
        'label'            => $locale['ust_102'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_state',
        'options_provider' => 'account.state.options',
        'depends_on'       => 'user_location',
        'options_endpoint' => 'account-state-options',
        'empty_options_label' => $locale['ust_103'],
        'options_placeholder' => $locale['ust_104'],
        'loading_options_label' => $locale['ust_105'],
        'options_error_label' => $locale['ust_106'],
        'loading_options_status' => $locale['ust_107'],
        'empty_options_status' => $locale['ust_108'],
        'options_error_status' => $locale['ust_109'],
        'required'         => FALSE,
        'max_length'       => 100,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => '',
            'after'    => 'user_location',
        ],
    ],
];
