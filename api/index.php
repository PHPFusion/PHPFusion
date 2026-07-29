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

(new ApiKernel())->handle($request, $endpoint, $route)->send();
