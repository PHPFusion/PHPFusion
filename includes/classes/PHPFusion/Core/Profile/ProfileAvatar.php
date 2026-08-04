<?php

namespace PHPFusion\Core\Profile;

final class ProfileAvatar
{
    public static function url(array|string $user): string
    {
        $filename = is_array($user) ? (string)($user['user_avatar'] ?? '') : $user;

        return IMAGES . 'avatars/' . ($filename !== '' ? basename($filename) : 'default-avatar.png');
    }

    public static function upload(int $userId, string $currentFilename, string $inputName = 'user_avatar'): array
    {
        if (!isset($_FILES[$inputName]) || !check_file_uploaded($inputName)) {
            return ['success' => TRUE, 'filename' => $currentFilename, 'url' => self::url($currentFilename)];
        }

        $upload = upload_image(
            source_image: $inputName,
            target_name: 'profile_' . $userId . '_' . bin2hex(random_bytes(8)),
            target_folder: IMAGES . 'avatars/',
            target_width: 4096,
            target_height: 4096,
            max_size: 5242880,
            delete_original: FALSE,
            thumb1: FALSE,
            thumb2: FALSE,
            allowed_extensions: ['.jpg', '.jpeg', '.png', '.webp', '.gif'],
            replace_upload: TRUE
        );

        $filename = (string)($upload['image_name'] ?? '');
        if ((int)($upload['error'] ?? 6) !== 0 || $filename === '' || basename($filename) !== $filename) {
            return [
                'success' => FALSE,
                'message' => 'Use a JPG, PNG, GIF, or WebP image no larger than 5MB.',
                'filename' => $currentFilename,
                'url' => self::url($currentFilename),
            ];
        }

        if ($currentFilename !== '' && $currentFilename !== $filename) {
            self::remove($currentFilename);
        }

        return ['success' => TRUE, 'filename' => $filename, 'url' => self::url($filename)];
    }

    public static function remove(string $filename): void
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
}
