<?php

namespace PHPFusion\Core\Profile\Blocks;

use PHPFusion\Core\Profile\ProfileAvatar;
use PHPFusion\ProfileGlobal\ProfileRegistrationFields;
use PHPFusion\ProfileGlobal\ProfileUi;

final class BasicInformationBlock
{
    public static function definition(): array
    {
        return [
            'namespace' => self::class,
            'title' => 'Basic information',
            'description' => 'Your public profile information',
            'order' => 10,
            'fields' => [
                ['name' => 'bio', 'column' => 'user_sig', 'type' => 'textarea', 'max_length' => 1000],
                ['name' => 'location', 'column' => 'user_location', 'type' => 'text', 'max_length' => 100],
                ['name' => 'avatar', 'column' => 'user_avatar', 'type' => 'avatar'],
            ],
            'userdata' => [self::class, 'userdata'],
            'editor' => [self::class, 'editor'],
            'public' => [self::class, 'publicCard'],
        ];
    }

    public static function userdata(array $user, array $data): array
    {
        return [
            'avatar_url' => ProfileAvatar::url($user),
            'display_name' => (string)($user['name'] ?? $user['user_name'] ?? 'Member'),
            'username' => (string)($user['user_name'] ?? ''),
            'role' => function_exists('getgroupname') ? (string)getgroupname((int)($user['user_level'] ?? 0)) : 'Member',
        ];
    }

    public static function editor(array $user, array $data): string
    {
        $e = static fn(mixed $value): string => ProfileUi::escape($value);
        $fields = new ProfileRegistrationFields();
        ob_start();
        ?>
        <section class="card mb-4" data-public-profile-card="<?= $e(self::class) ?>">
            <div class="card-body p-4">
                <h2 class="h4 mb-1">Basic information</h2>
                <p class="text-secondary mb-4">Your public profile information</p>
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mb-4">
                    <img class="avatar avatar-xl rounded-circle" src="<?= $e($data['avatar_url']) ?>" alt="Current profile photo" data-public-avatar-preview>
                    <div class="flex-fill">
                        <?= $fields->renderField([
                            'name' => 'user_avatar',
                            'label' => 'Profile photo',
                            'type' => 'avatar',
                            'required' => FALSE,
                            'input_id' => 'public-profile-avatar',
                            'description' => 'JPG, PNG, GIF, or WebP. Maximum 5MB.',
                            'aria_describedby' => 'public-profile-avatar-error',
                            'data' => ['public-avatar-input' => ''],
                        ]) ?>
                        <div class="invalid-feedback d-block" id="public-profile-avatar-error" data-profile-field-error="user_avatar" role="status"></div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <?= $fields->renderField([
                            'name' => 'display_name', 'label' => 'Name', 'type' => 'text',
                            'value' => $data['display_name'], 'readonly' => TRUE, 'required' => FALSE,
                            'input_id' => 'public-profile-name', 'data' => ['public-preview-input' => 'name'],
                        ]) ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $fields->renderField([
                            'name' => 'username', 'label' => 'Username', 'type' => 'text',
                            'value' => $data['username'], 'readonly' => TRUE, 'required' => FALSE,
                            'input_id' => 'public-profile-username', 'data' => ['public-preview-input' => 'username'],
                        ]) ?>
                    </div>
                    <div class="col-12">
                        <?= $fields->renderField([
                            'name' => 'role', 'label' => 'Role / title', 'type' => 'text',
                            'value' => $data['role'], 'readonly' => TRUE, 'required' => FALSE,
                            'input_id' => 'public-profile-role', 'data' => ['public-preview-input' => 'role'],
                        ]) ?>
                    </div>
                    <div class="col-12">
                        <?= $fields->renderField([
                            'name' => 'bio', 'label' => 'Bio', 'type' => 'textarea',
                            'value' => $data['bio'] ?? '', 'rows' => 4, 'max_length' => 1000,
                            'required' => FALSE, 'input_id' => 'public-profile-bio',
                            'aria_describedby' => 'public-profile-bio-error',
                            'data' => ['public-preview-input' => 'bio'],
                        ]) ?>
                        <div class="invalid-feedback d-block" id="public-profile-bio-error" data-profile-field-error="bio" role="status"></div>
                    </div>
                    <div class="col-12">
                        <?= $fields->renderField([
                            'name' => 'location', 'label' => 'Location', 'type' => 'text',
                            'value' => $data['location'] ?? '', 'max_length' => 100,
                            'required' => FALSE, 'input_id' => 'public-profile-location',
                            'aria_describedby' => 'public-profile-location-error',
                            'data' => ['public-preview-input' => 'location'],
                        ]) ?>
                        <div class="invalid-feedback d-block" id="public-profile-location-error" data-profile-field-error="location" role="status"></div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return (string)ob_get_clean();
    }

    public static function publicCard(array $user, array $data): string
    {
        if (trim((string)($data['bio'] ?? '')) === '' && trim((string)($data['location'] ?? '')) === '') {
            return '';
        }

        $e = static fn(mixed $value): string => ProfileUi::escape($value);
        return '<section class="card"><div class="card-body p-4">'
            . '<h2 class="h4 mb-3">About</h2>'
            . (!empty($data['bio']) ? '<p>' . nl2br($e($data['bio'])) . '</p>' : '')
            . (!empty($data['location']) ? '<p class="text-secondary mb-0">' . ProfileUi::icon('map-pin') . ' ' . $e($data['location']) . '</p>' : '')
            . '</div></section>';
    }
}
