<?php

namespace PHPFusion\Administration\Api;

use PHPFusion\Api\ApiRequest;
use PHPFusion\Api\ApiResponse;

final class AdminApiResponse
{
    public static function success(
        ApiRequest $request,
        array $data = [],
        string $message = 'Saved.',
        int $status = 200
    ): ApiResponse {
        return ApiResponse::json([
            'success' => TRUE,
            'message' => $message,
            'errors' => [],
            'data' => $data,
            'token' => self::token($request),
        ], $status);
    }

    public static function validation(ApiRequest $request, array $errors, string $message = ''): ApiResponse
    {
        return ApiResponse::json([
            'success' => FALSE,
            'message' => $message ?: 'Some fields need attention.',
            'errors' => $errors,
            'data' => [],
            'token' => self::token($request),
        ], 422);
    }

    public static function error(
        ApiRequest $request,
        string $message,
        int $status = 400,
        array $data = []
    ): ApiResponse {
        return ApiResponse::json([
            'success' => FALSE,
            'message' => $message,
            'errors' => [],
            'data' => $data,
            'token' => self::token($request),
        ], $status);
    }

    public static function token(ApiRequest $request): array
    {
        $formId = trim((string)$request->input('form_id'));
        if ($formId === '') {
            return [];
        }

        return [
            'form_id' => $formId,
            'fusion_token' => fusion_get_token($formId, (int)fusion_get_settings('form_tokens')),
        ];
    }
}
