<?php

namespace PHPFusion\Apps\UserFields\Profile\Avatar;

use PHPFusion\ProfileGlobal\ProfileContext;
use PHPFusion\ProfileGlobal\ProfileModuleInterface;
use PHPFusion\ProfileGlobal\ProfileRepository;
use PHPFusion\Core\Profile\ProfileAvatar;

final class AvatarModule implements ProfileModuleInterface
{
    private array $definition;
    private ProfileRepository $repository;

    public function __construct(array $definition, ProfileRepository $repository)
    {
        $this->definition = $definition;
        $this->repository = $repository;
    }

    public function definition(): array
    {
        return $this->definition;
    }

    public function schema(ProfileContext $context): array
    {
        $locale = fusion_get_locale();
        $field = (array)($this->definition['field'] ?? []);

        return [array_replace($field, [
            'name'        => 'avatar_file',
            'label'       => $locale['uav_100'],
            'type'        => 'avatar',
            'accept'      => 'image/jpeg,image/png,image/webp',
            'value'       => $this->avatarUrl((string)$context->userValue('user_avatar')),
            'description' => $locale['uav_102'],
        ])];
    }

    public function values(ProfileContext $context): array
    {
        $avatar = (string)$context->userValue('user_avatar');

        return [
            'avatar_name' => $avatar,
            'avatar_url'  => $this->avatarUrl($avatar),
        ];
    }

    public function update(ProfileContext $context, array $input): array
    {
        $locale = fusion_get_locale();

        if (!$context->canEdit()) {
            return $this->error($locale['uav_103'], 403);
        }

        $action = (string)($input['avatar_action'] ?? 'upload');
        $currentAvatar = (string)$context->userValue('user_avatar');

        if ($action === 'delete') {
            $this->repository->updateUserColumn($context->subjectId(), 'user_avatar', '');
            $this->removeFile($currentAvatar);

            return $this->success($locale['uav_104'], '', $this->avatarUrl());
        }

        if (!isset($_FILES['avatar_file']) || !check_file_uploaded('avatar_file')) {
            return $this->error($locale['uav_105'], 422, [
                'avatar_file' => [$locale['uav_106']],
            ]);
        }

        $targetName = 'profile_' . $context->subjectId() . '_' . bin2hex(random_bytes(8));
        $upload = upload_image(
            source_image: 'avatar_file',
            target_name: $targetName,
            target_folder: IMAGES . 'avatars/',
            target_width: 4096,
            target_height: 4096,
            max_size: 5242880,
            delete_original: FALSE,
            thumb1: FALSE,
            thumb2: FALSE,
            allowed_extensions: ['.jpg', '.jpeg', '.png', '.webp'],
            replace_upload: TRUE
        );

        $errorCode = (int)($upload['error'] ?? 6);
        $filename = (string)($upload['image_name'] ?? '');
        if ($errorCode !== 0 || $filename === '' || basename($filename) !== $filename) {
            $messages = [
                1 => $locale['uav_107'],
                2 => $locale['uav_108'],
                3 => $locale['uav_109'],
                4 => $locale['uav_110'],
                5 => $locale['uav_106'],
                6 => $locale['uav_111'],
            ];

            return $this->error($messages[$errorCode] ?? $locale['uav_112'], 422, [
                'avatar_file' => [$messages[$errorCode] ?? $locale['uav_112']],
            ]);
        }

        $this->repository->updateUserColumn($context->subjectId(), 'user_avatar', $filename);
        if ($currentAvatar !== $filename) {
            $this->removeFile($currentAvatar);
        }

        return $this->success($locale['uav_113'], $filename, $this->avatarUrl($filename));
    }

    private function avatarUrl(string $filename = ''): string
    {
        return ProfileAvatar::url($filename);
    }

    private function removeFile(string $filename): void
    {
        $protected = ['default-avatar.png', 'default-avatar-w.png', 'no-avatar.jpg', 'staff-avatar.webp'];
        if ($filename === '' || basename($filename) !== $filename || in_array($filename, $protected, TRUE)) {
            return;
        }

        $path = IMAGES . 'avatars/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function success(string $message, string $filename, string $url): array
    {
        return [
            'success' => TRUE,
            'status'  => 200,
            'message' => $message,
            'errors'  => [],
            'values'  => [
                'avatar_name' => $filename,
                'avatar_url'  => $url,
            ],
        ];
    }

    private function error(string $message, int $status, array $errors = []): array
    {
        return [
            'success' => FALSE,
            'status'  => $status,
            'message' => $message,
            'errors'  => $errors,
            'values'  => [],
        ];
    }
}
