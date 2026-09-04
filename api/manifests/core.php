<?php

defined('IN_FUSION') || exit;

use PHPFusion\Api\AuthEndpoint;
use PHPFusion\Api\FileUploadCheckEndpoint;

$legacy = static function (
    string $file,
    string $route,
    array $methods = ['GET', 'POST'],
    array $aliases = []
): array {
    return [
        'path'     => dirname(__DIR__) . '/endpoints/' . $file,
        'hook'     => 'fusion_filters',
        'route'    => '/v1/' . ltrim($route, '/'),
        'methods'  => $methods,
        'aliases'  => $aliases,
        'channels' => ['http', 'direct'],
    ];
};

$endpoints = [
    'core.file-upload-check' => [
        'handler'   => [FileUploadCheckEndpoint::class, 'check'],
        'bootstrap' => [dirname(__DIR__) . '/endpoints/FileUploadCheckEndpoint.php'],
        'route'     => '/v1/files/preflight',
        'methods'   => ['POST'],
        'aliases'   => ['file-upload-check'],
        'channels'  => ['http'],
    ],
    'core.username-check' => $legacy('username_validation.php', 'users/username-check', ['GET', 'POST'], ['username-check']),
    'core.userpass-check' => $legacy('userpass_validation.php', 'users/password-check', ['POST'], ['userpass-check']),
    'core.calling-codes' => $legacy('calling_codes.php', 'geo/calling-codes', ['GET'], ['calling-codes']),
    'core.geomap-states' => $legacy('states.php', 'geo/states', ['GET'], ['geomap-states']),
    'core.analytics' => $legacy('analytics.php', 'analytics', ['POST'], ['analytics']),
    'core.textarea-sessions' => $legacy('textarea_sessions.php', 'textarea/sessions', ['POST'], ['textarea-sessions']),
    'core.social' => $legacy('social.php', 'social', ['GET', 'POST'], ['social']),
    'core.set-language' => $legacy('set_language.php', 'locale', ['GET', 'POST'], ['set-language']),
    'core.userhideemail-update' => $legacy('userhideemail_update.php', 'users/hide-email', ['POST'], ['userhideemail-u']),
    'core.userprofiledisplay-update' => $legacy('userprofiledisplay_update.php', 'users/profile-display', ['POST'], ['userprofiledisplay-u']),
    'auth.admin-login' => [
        'handler'  => [AuthEndpoint::class, 'adminLogin'],
        'route'    => '/v1/auth/admin-login',
        'methods'  => ['POST'],
        'aliases'  => ['admin-login'],
        'channels' => ['http', 'direct'],
    ],
    'auth.member-identity' => [
        'handler'  => [AuthEndpoint::class, 'memberIdentity'],
        'route'    => '/v1/auth/member/identity',
        'methods'  => ['POST'],
        'aliases'  => ['member-login-identity'],
        'channels' => ['http', 'direct'],
    ],
    'auth.member-password' => [
        'handler'  => [AuthEndpoint::class, 'memberPassword'],
        'route'    => '/v1/auth/member/password',
        'methods'  => ['POST'],
        'aliases'  => ['member-login-password'],
        'channels' => ['http', 'direct'],
    ],
];

$adminManifest = __DIR__.'/admin.php';
if (is_file($adminManifest)) {
    $endpoints = array_replace($endpoints, (array)require $adminManifest);
}

return $endpoints;
