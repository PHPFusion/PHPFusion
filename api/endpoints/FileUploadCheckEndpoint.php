<?php

namespace PHPFusion\Api;

use Defender\ImageValidation;

/**
 * Read-only validation for files selected by form_fileinput().
 *
 * This endpoint never stores or moves an uploaded file. Defender remains the
 * authority for final validation and storage when the parent form is submitted.
 */
final class FileUploadCheckEndpoint
{
    private const ALLOWED_TYPES = ['image', 'video', 'audio', 'text', 'file', 'object'];

    public static function check(ApiRequest $request): ApiResponse
    {
        if (!fusion_safe()) {
            $locale = fusion_get_locale();

            return ApiResponse::error(
                (string)($locale['token_error'] ?? 'The security token is invalid.'),
                403
            );
        }

        $policy = self::policy($request->input('policy'));
        if ($policy === NULL) {
            return ApiResponse::error('The file validation policy is invalid.', 400);
        }

        $files = self::uploadedFiles();
        if ($files === []) {
            return ApiResponse::error('No files were received for validation.', 422);
        }

        $results = [];
        foreach ($files as $index => $file) {
            $results[] = self::validateFile($file, $policy, $index);
        }

        $valid = !in_array(FALSE, array_column($results, 'valid'), TRUE);
        $message = $valid
            ? (count($results) === 1 ? 'The file is ready to upload.' : 'The files are ready to upload.')
            : 'One or more files need attention.';

        return ApiResponse::json([
            'success' => $valid,
            'message' => $message,
            'data'    => ['files' => $results],
        ], $valid ? 200 : 422);
    }

    private static function policy(mixed $value): ?array
    {
        if (is_string($value)) {
            $value = json_decode($value, TRUE);
        }

        if (!is_array($value)) {
            return NULL;
        }

        $extensions = self::normalizeList($value['extensions'] ?? [], '/^[a-z0-9]{1,12}$/');
        $types = array_values(array_intersect(
            self::normalizeList($value['types'] ?? ['file'], '/^[a-z]+$/'),
            self::ALLOWED_TYPES
        ));
        if ($types === []) {
            $types = ['file'];
        }

        $serverMaxCount = max(1, (int)ini_get('max_file_uploads'));
        $maxCount = min($serverMaxCount, max(1, (int)($value['maxCount'] ?? 1)));

        $serverMaxBytes = function_exists('max_server_upload') ? (int)max_server_upload() : 0;
        $maxBytes = max(1, (int)($value['maxBytes'] ?? $serverMaxBytes));
        if ($serverMaxBytes > 0) {
            $maxBytes = min($maxBytes, $serverMaxBytes);
        }

        return [
            'extensions' => $extensions,
            'types'      => $types,
            'maxBytes'   => $maxBytes,
            'maxCount'   => $maxCount,
            'maxWidth'   => min(100000, max(0, (int)($value['maxWidth'] ?? 0))),
            'maxHeight'  => min(100000, max(0, (int)($value['maxHeight'] ?? 0))),
        ];
    }

    private static function normalizeList(mixed $values, string $pattern): array
    {
        if (is_string($values)) {
            $values = preg_split('/[\s,|]+/', $values);
        }

        $normalized = [];
        foreach ((array)$values as $value) {
            $value = strtolower(ltrim(trim((string)$value), '.'));
            if ($value !== '' && preg_match($pattern, $value)) {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }

    private static function uploadedFiles(): array
    {
        $upload = $_FILES['files'] ?? NULL;
        if (!is_array($upload) || !isset($upload['name'])) {
            return [];
        }

        $names = is_array($upload['name']) ? $upload['name'] : [$upload['name']];
        $files = [];
        foreach (array_keys($names) as $index) {
            $files[] = [
                'name'     => (string)($names[$index] ?? ''),
                'type'     => (string)(is_array($upload['type'] ?? NULL) ? ($upload['type'][$index] ?? '') : ($upload['type'] ?? '')),
                'tmp_name' => (string)(is_array($upload['tmp_name'] ?? NULL) ? ($upload['tmp_name'][$index] ?? '') : ($upload['tmp_name'] ?? '')),
                'error'    => (int)(is_array($upload['error'] ?? NULL) ? ($upload['error'][$index] ?? UPLOAD_ERR_NO_FILE) : ($upload['error'] ?? UPLOAD_ERR_NO_FILE)),
                'size'     => (int)(is_array($upload['size'] ?? NULL) ? ($upload['size'][$index] ?? 0) : ($upload['size'] ?? 0)),
            ];
        }

        return $files;
    }

    private static function validateFile(array $file, array $policy, int $index): array
    {
        $name = basename(str_replace('\\', '/', $file['name']));
        $extension = strtolower((string)pathinfo($name, PATHINFO_EXTENSION));
        $tmpName = $file['tmp_name'];
        $size = $file['size'];
        $mime = $file['type'];
        $width = NULL;
        $height = NULL;
        $message = '';

        if ($index >= $policy['maxCount']) {
            $message = 'Too many files were selected.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $message = self::uploadErrorMessage($file['error']);
        } elseif ($name === '' || $tmpName === '' || !is_uploaded_file($tmpName)) {
            $message = 'The uploaded file could not be inspected.';
        } elseif ($size <= 0) {
            $message = 'The uploaded file is empty.';
        } elseif ($size > $policy['maxBytes']) {
            $message = 'The uploaded file exceeds the allowed size.';
        } elseif ($policy['extensions'] !== [] && !in_array($extension, $policy['extensions'], TRUE)) {
            $message = 'The file extension is not allowed.';
        }

        if ($message === '' && extension_loaded('fileinfo')) {
            $detected = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpName);
            if (is_string($detected) && $detected !== '') {
                $mime = $detected;
            }
        }

        if (
            $message === '' &&
            fusion_get_settings('mime_check') &&
            $policy['extensions'] !== [] &&
            !ImageValidation::mimeCheck($tmpName, $extension, $policy['extensions'])
        ) {
            $message = 'The file content does not match its extension.';
        }

        if ($message === '' && !self::matchesTypes($mime, $policy['types'])) {
            $message = 'The file content is not an accepted type.';
        }

        if ($message === '' && str_starts_with(strtolower($mime), 'image/')) {
            $image = @getimagesize($tmpName);
            if (!is_array($image)) {
                $message = 'The selected file is not a readable image.';
            } else {
                $width = (int)$image[0];
                $height = (int)$image[1];
                if ($policy['maxWidth'] > 0 && $width > $policy['maxWidth']) {
                    $message = 'The image width exceeds the allowed dimensions.';
                } elseif ($policy['maxHeight'] > 0 && $height > $policy['maxHeight']) {
                    $message = 'The image height exceeds the allowed dimensions.';
                }
            }
        }

        return [
            'name'      => $name,
            'size'      => $size,
            'extension' => $extension,
            'mime'      => $mime,
            'width'     => $width,
            'height'    => $height,
            'valid'     => $message === '',
            'message'   => $message,
        ];
    }

    private static function matchesTypes(string $mime, array $types): bool
    {
        if (array_intersect($types, ['file', 'object'])) {
            return TRUE;
        }

        foreach ($types as $type) {
            if (str_starts_with(strtolower($mime), $type . '/')) {
                return TRUE;
            }
            if ($type === 'text' && strtolower($mime) === 'application/json') {
                return TRUE;
            }
        }

        return FALSE;
    }

    private static function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the server size limit.',
            UPLOAD_ERR_PARTIAL                        => 'The file upload was incomplete.',
            UPLOAD_ERR_NO_FILE                        => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR                     => 'The upload temporary directory is unavailable.',
            UPLOAD_ERR_CANT_WRITE                     => 'The server could not receive the uploaded file.',
            UPLOAD_ERR_EXTENSION                      => 'A server extension stopped the file upload.',
            default                                   => 'The uploaded file could not be inspected.',
        };
    }
}
