<?php

namespace PHPFusion\Core\Profile;

use PHPFusion\Api\ApiRequest;
use PHPFusion\Api\ApiResponse;
use PHPFusion\ProfileGlobal\ProfileContext;
use PHPFusion\ProfileGlobal\ProfileFieldValidator;

final class CoreProfileApiEndpoint
{
    public static function publicBlock(ApiRequest $request): ApiResponse
    {
        if (!defined('iMEMBER') || !iMEMBER) {
            return ApiResponse::error('Authentication required.', 401);
        }

        $registry = new PublicProfileBlockRegistry();
        $model = new ProfileModel();
        $userId = (int)fusion_get_userdata('user_id');
        $user = $model->findUser($userId);

        if ($request->method() === 'GET') {
            $namespace = trim((string)$request->query('namespace', ''));
            if ($namespace !== '') {
                $block = $registry->find($namespace);
                if ($block === NULL) {
                    return ApiResponse::error('Public profile block was not found.', 404);
                }

                return ApiResponse::success([
                    'namespace' => $namespace,
                    'userdata' => $registry->data($block, $user),
                ]);
            }

            return ApiResponse::success(['blocks' => self::serializeBlocks($registry, $user)]);
        }

        if (!in_array($request->method(), ['POST', 'PATCH'], TRUE)) {
            return ApiResponse::error('HTTP method is not allowed.', 405);
        }
        if (!fusion_safe()) {
            return ApiResponse::error('Security validation failed. Refresh the page and try again.', 403);
        }

        $input = $request->input();
        $context = new ProfileContext($user, $userId, defined('iADMIN') && iADMIN);
        $validator = new ProfileFieldValidator();
        $values = [];
        $errors = [];
        $avatarColumn = '';

        foreach ($registry->all() as $block) {
            foreach ($block['fields'] as $field) {
                $name = (string)$field['name'];
                $column = (string)$field['column'];
                $type = (string)($field['type'] ?? 'text');
                if (!column_exists(DB_USERS, $column, FALSE)) {
                    continue;
                }

                if ($type === 'avatar') {
                    $avatarColumn = $column;
                    continue;
                }

                $raw = $input[$name] ?? '';
                $validation = $validator->validate($field, $raw, $context, $block);
                if (
                    $type === 'url'
                    && $validation['value'] !== ''
                    && !preg_match('#^https?://#i', (string)$validation['value'])
                ) {
                    $validation['errors'][] = 'Use a complete http:// or https:// URL.';
                }
                if ($validation['errors']) {
                    $errors[$name] = $validation['errors'];
                } else {
                    $values[$column] = $validation['value'];
                }
            }
        }

        if ($errors) {
            return ApiResponse::json([
                'success' => FALSE,
                'code' => 'validation_failed',
                'message' => 'Check the highlighted profile fields.',
                'errors' => $errors,
                'data' => [],
            ], 422);
        }

        if ($avatarColumn !== '') {
            $avatarResult = ProfileAvatar::upload($userId, (string)($user['user_avatar'] ?? ''), 'user_avatar');
            if (empty($avatarResult['success'])) {
                return ApiResponse::json([
                    'success' => FALSE,
                    'code' => 'validation_failed',
                    'message' => 'Check the highlighted profile fields.',
                    'errors' => ['user_avatar' => [(string)$avatarResult['message']]],
                    'data' => [],
                ], 422);
            }
            if (($avatarResult['filename'] ?? '') !== ($user['user_avatar'] ?? '')) {
                $values[$avatarColumn] = (string)$avatarResult['filename'];
            }
        }

        $model->updateUserColumns($userId, $values, $registry->allowedColumns());
        $freshUser = $model->findUser($userId);

        $formId = trim((string)$request->input('form_id'));
        $token = $formId !== '' ? [
            'form_id' => $formId,
            'fusion_token' => fusion_get_token($formId, (int)fusion_get_settings('form_tokens')),
        ] : [];

        return ApiResponse::success($token + [
            'blocks' => self::serializeBlocks($registry, $freshUser),
            'avatar_url' => ProfileAvatar::url($freshUser),
            'updated_at' => time(),
        ], 'Public profile updated.');
    }

    private static function serializeBlocks(PublicProfileBlockRegistry $registry, array $user): array
    {
        $blocks = [];
        foreach ($registry->all() as $namespace => $block) {
            $blocks[] = [
                'namespace' => $namespace,
                'title' => (string)($block['title'] ?? ''),
                'userdata' => $registry->data($block, $user),
            ];
        }

        return $blocks;
    }
}
