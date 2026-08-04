<?php

defined('IN_FUSION') || exit;

use PHPFusion\Api\ApiRequest;
use PHPFusion\Api\ApiResponse;
use PHPFusion\ProfileGlobal\ProfileApiEndpoint;

return static fn(ApiRequest $request, array $endpoint = []): ApiResponse =>
    ProfileApiEndpoint::module($request, $endpoint);
