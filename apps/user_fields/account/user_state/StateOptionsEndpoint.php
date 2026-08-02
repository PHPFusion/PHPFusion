<?php

namespace PHPFusion\Apps\UserFields\Account\UserState;

use PHPFusion\Api\ApiRequest;
use PHPFusion\Api\ApiResponse;

final class StateOptionsEndpoint
{
    public static function handle(ApiRequest $request, array $endpoint = []): ApiResponse
    {
        $countryCode = strtoupper(trim((string)$request->query('id', '')));
        if ($countryCode === '' || !preg_match('/^[A-Z]{2}$/', $countryCode)) {
            return ApiResponse::success([]);
        }

        return ApiResponse::success(StateOptions::items($countryCode));
    }
}
