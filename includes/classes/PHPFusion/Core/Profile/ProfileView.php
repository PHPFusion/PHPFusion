<?php

namespace PHPFusion\Core\Profile;

use RuntimeException;

final class ProfileView
{
    public function render(string $template, array $data): void
    {
        $path = __DIR__ . '/templates/' . basename($template) . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('Core profile template was not found.');
        }

        extract($data, EXTR_SKIP);
        require $path;
    }
}
