<?php

namespace PHPFusion\Administration\Settings\Main;

use PHPFusion\Administration\Api\AdminApiCache;
use PHPFusion\Administration\Api\AdminApiGuard;
use PHPFusion\Administration\Api\AdminApiResponse;
use PHPFusion\Api\ApiRequest;
use PHPFusion\Api\ApiResponse;
use Throwable;

final class SettingsMainEndpoint
{
    public static function handle(ApiRequest $request, array $endpoint = []): ApiResponse
    {
        if ($denied = AdminApiGuard::authorize($request, 'S1')) {
            return $denied;
        }

        $section = (string)($endpoint['section'] ?? '');
        if (!array_key_exists($section, MainSettingsSchema::storage())) {
            return AdminApiResponse::error($request, 'Settings section was not found.', 404);
        }

        $service = new SettingsMainService();
        if ($request->method() === 'GET') {
            $values = AdminApiCache::remember('settings-main:'.$section, 30, static fn(): array => $service->read($section));

            return AdminApiResponse::success($request, [
                'section' => $section,
                'values' => $values,
                'revision' => self::revision($values),
            ], 'Settings loaded.');
        }

        $onlyField = !empty($request->input('validate_only'))
            ? trim((string)$request->input('validate_field'))
            : '';
        if ($onlyField !== '' && !isset(MainSettingsSchema::storage()[$section][$onlyField])) {
            return AdminApiResponse::validation(
                $request,
                [$onlyField => ['This field is not part of the requested settings section.']]
            );
        }
        $errors = $service->validate($section, $request->input(), $onlyField);
        if ($errors !== []) {
            return AdminApiResponse::validation($request, $errors);
        }
        if ($onlyField !== '') {
            return AdminApiResponse::success($request, ['field' => $onlyField], 'Value is valid.');
        }

        try {
            $values = $service->update($section, $request->input());
        } catch (Throwable $exception) {
            if (function_exists('set_error')) {
                set_error(E_USER_WARNING, $exception->getMessage(), $exception->getFile(), $exception->getLine());
            }

            return AdminApiResponse::error(
                $request,
                'The settings could not be updated. Review the values and try again.',
                500
            );
        }
        AdminApiCache::forget('settings-main:'.$section);

        return AdminApiResponse::success($request, [
            'section' => $section,
            'values' => $values,
            'revision' => self::revision($values),
            'updated_at' => time(),
        ], 'Settings updated.');
    }

    private static function revision(array $values): string
    {
        ksort($values);

        return hash('sha256', json_encode($values, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
