<?php

defined('IN_FUSION') || exit;

use PHPFusion\Administration\Settings\Main\SettingsMainEndpoint;

$bootstrap = __DIR__.'/bootstrap.php';
$make = static fn(string $slug, string $section): array => [
    'handler' => [SettingsMainEndpoint::class, 'handle'],
    'bootstrap' => [$bootstrap],
    'route' => '/v1/admin/settings/main/'.$slug,
    'methods' => ['GET', 'POST'],
    'aliases' => ['admin-settings-main-'.$slug],
    'channels' => ['http', 'direct'],
    'section' => $section,
];

return [
    'admin.settings.main.site-identity' => $make('site-identity', 'site_identity'),
    'admin.settings.main.site-content' => $make('site-content', 'site_content'),
    'admin.settings.main.search' => $make('search', 'search'),
    'admin.settings.main.url' => $make('url', 'url'),
    'admin.settings.main.domains' => $make('domains', 'domains'),
];
