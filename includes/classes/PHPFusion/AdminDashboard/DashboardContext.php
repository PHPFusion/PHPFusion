<?php

namespace PHPFusion\AdminDashboard;

final class DashboardContext
{
    private array $runtimeCache = [];

    public function __construct(
        private array $locale,
        private array $settings,
        private array $userdata
    ) {
    }

    public function text(string $key): string
    {
        return (string)($this->locale[$key] ?? $key);
    }

    public function settings(string $key = ''): mixed
    {
        return $key === '' ? $this->settings : ($this->settings[$key] ?? NULL);
    }

    public function userdata(string $key = ''): mixed
    {
        return $key === '' ? $this->userdata : ($this->userdata[$key] ?? NULL);
    }

    public function aidLink(): string
    {
        return function_exists('fusion_get_aidlink') ? fusion_get_aidlink() : '';
    }

    public function adminUrl(string $path): string
    {
        return (defined('ADMIN') ? ADMIN : '') . ltrim($path, '/');
    }

    public function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public function remember(string $key, callable $loader): mixed
    {
        if (!array_key_exists($key, $this->runtimeCache)) {
            $this->runtimeCache[$key] = call_user_func($loader);
        }

        return $this->runtimeCache[$key];
    }
}
