<?php

defined('IN_FUSION') || exit;

require_once __DIR__ . '/ApiHalt.php';
require_once __DIR__ . '/ApiRequest.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiRegistry.php';
require_once __DIR__ . '/ApiKernel.php';
require_once dirname(__DIR__) . '/endpoints/AuthEndpoint.php';

use PHPFusion\Api\ApiKernel;
use PHPFusion\Api\ApiRequest;
use PHPFusion\Api\ApiResponse;

if (!function_exists('fusion_api_invoke')) {
    /**
     * Invoke any registered endpoint without an HTTP round trip.
     */
    function fusion_api_invoke(string $endpoint, array $payload = [], array $context = []): ApiResponse
    {
        return (new ApiKernel())->handle(ApiRequest::direct($payload, $context), $endpoint);
    }
}
