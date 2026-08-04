<?php

namespace PHPFusion\Core\Profile\Blocks;

use PHPFusion\ProfileGlobal\ProfileUi;
use PHPFusion\ProfileGlobal\ProfileRegistrationFields;

final class LinksBlock
{
    public static function definition(): array
    {
        return [
            'namespace' => self::class,
            'title' => 'Links',
            'description' => 'Your website and social links',
            'order' => 20,
            'fields' => [
                ['name' => 'website', 'column' => 'user_web', 'type' => 'url', 'max_length' => 200, 'required' => FALSE],
                ['name' => 'twitter', 'column' => 'user_twitter', 'type' => 'text', 'max_length' => 100, 'required' => FALSE],
                ['name' => 'linkedin', 'column' => 'user_linkedin', 'type' => 'text', 'max_length' => 100, 'required' => FALSE],
                ['name' => 'discord', 'column' => 'user_discord', 'type' => 'text', 'max_length' => 100, 'required' => FALSE],
            ],
            'editor' => [self::class, 'editor'],
            'public' => [self::class, 'publicCard'],
        ];
    }

    public static function editor(array $user, array $data): string
    {
        $e = static fn(mixed $value): string => ProfileUi::escape($value);
        $fields = new ProfileRegistrationFields();
        $labels = ['website' => 'Website', 'twitter' => 'X (Twitter)', 'linkedin' => 'Linkedin', 'discord' => 'Discord'];
        ob_start();
        ?>
        <section class="card mb-4" data-public-profile-card="<?= $e(self::class) ?>">
            <div class="card-body p-4">
                <h2 class="h4 mb-1">Links</h2>
                <p class="text-secondary mb-4">Your website and social links</p>
                <div class="row g-3">
                    <?php foreach ($labels as $name => $label) : ?>
                        <?php if (!array_key_exists($name, $data)) { continue; } ?>
                        <div class="<?= $name === 'website' ? 'col-12' : 'col-12 col-md-4' ?>">
                            <?= $fields->renderField([
                                'name' => $name,
                                'label' => $label,
                                'type' => $name === 'website' ? 'url' : 'text',
                                'value' => $data[$name],
                                'max_length' => $name === 'website' ? 200 : 100,
                                'required' => FALSE,
                                'input_id' => 'public-profile-' . $name,
                                'aria_describedby' => 'public-profile-' . $name . '-error',
                                'data' => ['public-preview-input' => $name],
                            ]) ?>
                            <div class="invalid-feedback d-block" id="public-profile-<?= $e($name) ?>-error" data-profile-field-error="<?= $e($name) ?>" role="status"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
        return (string)ob_get_clean();
    }

    public static function publicCard(array $user, array $data): string
    {
        $links = [];
        foreach ($data as $name => $value) {
            $value = trim((string)$value);
            if ($value === '') {
                continue;
            }
            if ($name === 'website' && !preg_match('#^https?://#i', $value)) {
                continue;
            }
            $links[$name] = $value;
        }
        if (!$links) {
            return '';
        }

        $html = '<section class="card"><div class="card-body p-4"><h2 class="h4 mb-3">Links</h2><div class="d-flex flex-wrap gap-2">';
        $labels = ['website' => 'Website', 'twitter' => 'X (Twitter)', 'linkedin' => 'Linkedin', 'discord' => 'Discord'];
        foreach ($links as $name => $value) {
            $url = $name === 'website' ? $value : self::socialUrl($name, $value);
            $html .= '<a class="btn btn-outline-secondary" href="' . ProfileUi::escape($url) . '" target="_blank" rel="noopener noreferrer"><span>' . ProfileUi::escape($labels[$name] ?? ucfirst($name)) . '</span></a>';
        }

        return $html . '</div></div></section>';
    }

    private static function socialUrl(string $network, string $value): string
    {
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }
        $hosts = ['twitter' => 'https://x.com/', 'linkedin' => 'https://linkedin.com/in/', 'discord' => 'https://discord.com/users/'];
        return ($hosts[$network] ?? '') . ltrim($value, '@/');
    }
}
