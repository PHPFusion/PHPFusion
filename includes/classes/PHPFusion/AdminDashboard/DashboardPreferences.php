<?php

namespace PHPFusion\AdminDashboard;

final class DashboardPreferences
{
    public const VERSION = 1;
    public const MAX_COOKIE_BYTES = 3800;

    private array $visibility = [];
    private array $order = [];

    public function __construct(string $raw = '')
    {
        if ($raw === '' || strlen($raw) > self::MAX_COOKIE_BYTES) {
            return;
        }

        $data = json_decode($raw, TRUE);
        if (!is_array($data) || (int)($data['version'] ?? 0) !== self::VERSION) {
            return;
        }

        foreach ((array)($data['visibility'] ?? []) as $id => $visible) {
            if ($this->validId((string)$id) && is_bool($visible)) {
                $this->visibility[(string)$id] = $visible;
            }
        }

        foreach ((array)($data['order'] ?? []) as $id) {
            $id = (string)$id;
            if ($this->validId($id) && !in_array($id, $this->order, TRUE)) {
                $this->order[] = $id;
            }
            if (count($this->order) >= 100) {
                break;
            }
        }
    }

    public static function cookieName(): string
    {
        $prefix = defined('COOKIE_PREFIX') ? (string)COOKIE_PREFIX : 'fusion_';
        $userId = function_exists('fusion_get_userdata') ? (int)fusion_get_userdata('user_id') : 0;

        return preg_replace('/[^a-z0-9_-]+/i', '_', $prefix . 'admin_dashboard_' . $userId);
    }

    public static function fromCurrentCookie(): self
    {
        $name = self::cookieName();
        return new self(isset($_COOKIE[$name]) ? (string)$_COOKIE[$name] : '');
    }

    public function isVisible(DashboardDefinition $definition): bool
    {
        return $this->visibility[$definition->id()] ?? $definition->defaultVisible();
    }

    /** @param DashboardDefinition[] $definitions */
    public function sort(array $definitions): array
    {
        $positions = array_flip($this->order);
        usort($definitions, static function (DashboardDefinition $left, DashboardDefinition $right) use ($positions): int {
            $leftPosition = $positions[$left->id()] ?? PHP_INT_MAX;
            $rightPosition = $positions[$right->id()] ?? PHP_INT_MAX;
            if ($leftPosition !== $rightPosition) {
                return $leftPosition <=> $rightPosition;
            }

            return [$left->order(), $left->id()] <=> [$right->order(), $right->id()];
        });

        return $definitions;
    }

    public function export(): array
    {
        return [
            'version' => self::VERSION,
            'visibility' => $this->visibility,
            'order' => $this->order,
        ];
    }

    private function validId(string $id): bool
    {
        return (bool)preg_match('/^[a-z0-9][a-z0-9._-]{2,79}$/i', $id);
    }
}
