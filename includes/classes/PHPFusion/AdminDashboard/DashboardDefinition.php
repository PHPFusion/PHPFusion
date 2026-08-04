<?php

namespace PHPFusion\AdminDashboard;

use InvalidArgumentException;

final class DashboardDefinition
{
    private const ALLOWED_SPANS = [3, 4, 6, 8, 9, 12];

    private array $span;
    private array $rights;

    public function __construct(
        private string $id,
        private string $className,
        private string $title,
        private string $description,
        private string $icon,
        private string $source,
        private string $sourceTitle,
        private bool $defaultVisible,
        private int $order,
        array $span,
        private string $right = '',
        array $rights = [],
        private string $rightsMode = 'any',
        private bool $superAdmin = FALSE
    ) {
        if (!preg_match('/^[a-z0-9][a-z0-9._-]{2,79}$/i', $id)) {
            throw new InvalidArgumentException('Dashboard widget id is invalid: ' . $id);
        }
        if ($className === '' || !is_a($className, DashboardWidgetInterface::class, TRUE)) {
            throw new InvalidArgumentException('Dashboard widget class is invalid: ' . $className);
        }
        if ($title === '') {
            throw new InvalidArgumentException('Dashboard widget title cannot be empty: ' . $id);
        }

        $this->span = $this->normalizeSpan($span);
        $this->rights = array_values(array_unique(array_filter(array_map('strval', $rights))));
        $this->rightsMode = $rightsMode === 'all' ? 'all' : 'any';
    }

    public static function fromArray(
        string $id,
        array $definition,
        string $source,
        string $sourceTitle,
        array $locale = []
    ): self {
        $title = trim((string)($definition['title'] ?? ''));
        $titleKey = trim((string)($definition['title_key'] ?? ''));
        if ($title === '' && $titleKey !== '') {
            $title = (string)($locale[$titleKey] ?? $titleKey);
        }

        $description = trim((string)($definition['description'] ?? ''));
        $descriptionKey = trim((string)($definition['description_key'] ?? ''));
        if ($description === '' && $descriptionKey !== '') {
            $description = (string)($locale[$descriptionKey] ?? $descriptionKey);
        }

        return new self(
            $id,
            trim((string)($definition['class'] ?? '')),
            $title,
            $description,
            trim((string)($definition['icon'] ?? 'dashboard')),
            $source,
            $sourceTitle,
            (bool)($definition['default_visible'] ?? FALSE),
            (int)($definition['order'] ?? 500),
            (array)($definition['span'] ?? []),
            trim((string)($definition['right'] ?? '')),
            (array)($definition['rights'] ?? []),
            (string)($definition['rights_mode'] ?? 'any'),
            (bool)($definition['super_admin'] ?? FALSE)
        );
    }

    public function canView(): bool
    {
        if ($this->superAdmin && (!defined('iSUPERADMIN') || !iSUPERADMIN)) {
            return FALSE;
        }
        if (!function_exists('checkrights')) {
            return $this->right === '' && $this->rights === [] && !$this->superAdmin;
        }
        if ($this->right !== '' && !checkrights($this->right)) {
            return FALSE;
        }
        if ($this->rights === []) {
            return TRUE;
        }

        $matches = array_map(static fn(string $right): bool => checkrights($right), $this->rights);

        return $this->rightsMode === 'all'
            ? !in_array(FALSE, $matches, TRUE)
            : in_array(TRUE, $matches, TRUE);
    }

    public function createWidget(): DashboardWidgetInterface
    {
        return new $this->className();
    }

    public function id(): string { return $this->id; }
    public function title(): string { return $this->title; }
    public function description(): string { return $this->description; }
    public function icon(): string { return $this->icon; }
    public function source(): string { return $this->source; }
    public function sourceTitle(): string { return $this->sourceTitle; }
    public function defaultVisible(): bool { return $this->defaultVisible; }
    public function order(): int { return $this->order; }
    public function span(): array { return $this->span; }

    private function normalizeSpan(array $span): array
    {
        $normalized = ['sm' => 12, 'md' => 6, 'lg' => 6, 'xl' => 4];
        foreach ($normalized as $breakpoint => $default) {
            $value = (int)($span[$breakpoint] ?? $default);
            $normalized[$breakpoint] = in_array($value, self::ALLOWED_SPANS, TRUE) ? $value : $default;
        }

        return $normalized;
    }
}
