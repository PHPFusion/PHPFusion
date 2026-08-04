<?php

namespace PHPFusion\Administration\Api;

use PHPFusion\Api\ApiRequest;
use PHPFusion\Api\ApiResponse;

final class AdminApiGuard
{
    private static array $authenticators = [];

    /**
     * Register a future bearer-token or application authenticator.
     * The callback receives ApiRequest and the required right, returning TRUE
     * only after it has verified both identity and capability.
     */
    public static function registerAuthenticator(callable $authenticator): void
    {
        self::$authenticators[] = $authenticator;
    }

    public static function authorize(ApiRequest $request, string $right): ?ApiResponse
    {
        foreach (self::$authenticators as $authenticator) {
            if (call_user_func($authenticator, $request, $right) === TRUE) {
                return NULL;
            }
        }

        if (!defined('iADMIN') || !iADMIN || ($right !== '' && !checkrights($right))) {
            return AdminApiResponse::error($request, 'Administrator permission required.', 403);
        }

        if ($request->method() !== 'GET' && !fusion_safe()) {
            return AdminApiResponse::error(
                $request,
                'Security validation failed. Refresh the page and try again.',
                403
            );
        }

        return NULL;
    }
}
