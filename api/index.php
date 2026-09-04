<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Central API HTTP dispatcher
+--------------------------------------------------------*/

defined('FUSION_API_REQUEST') || define('FUSION_API_REQUEST', TRUE);

require_once __DIR__ . '/../maincore.php';
require_once __DIR__ . '/manifests/api.php';

use PHPFusion\Api\ApiKernel;
use PHPFusion\Api\ApiRequest;

$scope = defined('FUSION_API_SCOPE') ? (string)FUSION_API_SCOPE : '';
$request = ApiRequest::fromGlobals($scope);
$endpoint = trim((string)get('api'));
$route = trim((string)get('route'));

if ($route === '') {
    $pathInfo = (string)($_SERVER['PATH_INFO'] ?? '');
    if ($pathInfo !== '') {
        $route = $pathInfo;
    } else {
        $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        $apiPosition = is_string($requestPath) ? strpos($requestPath, '/api/v1/') : FALSE;
        if ($apiPosition !== FALSE) {
            $route = substr($requestPath, $apiPosition + 4);
        }
    }
}

$profileEndpoint = str_starts_with($endpoint, 'profile-global')
    || str_starts_with($endpoint, 'core-profile')
    || in_array($endpoint, ['account.state.options', 'account-state-options'], TRUE);
$profileRoute = str_starts_with('/' . ltrim($route, '/'), '/v1/profile-global/')
    || str_starts_with('/' . ltrim($route, '/'), '/v1/core/profile/')
    || str_starts_with('/' . ltrim($route, '/'), '/v1/profile-modules/');

if (($profileEndpoint || $profileRoute) && function_exists('fusion_load_profile_modules')) {
    fusion_load_profile_modules();
}

(new ApiKernel())->handle($request, $endpoint, $route)->send();
