<?php

namespace PHPFusion\AdminDashboard;

use Throwable;

final class DashboardRegistry
{
    /** @var array<string, DashboardDefinition> */
    private array $definitions = [];
    private bool $discovered = FALSE;

    public function __construct(
        private array $locale = [],
        private string $coreSourceTitle = 'Core'
    ) {
    }

    public function discover(): self
    {
        if ($this->discovered) {
            return $this;
        }
        $this->discovered = TRUE;

        $coreManifest = defined('ADMIN') ? ADMIN . 'dashboard/widgets.php' : '';
        if ($coreManifest !== '' && is_file($coreManifest)) {
            $this->loadManifest($coreManifest, 'core', $this->coreSourceTitle);
        }

        $this->discoverInstalledInfusions();

        return $this;
    }

    /** @return DashboardDefinition[] */
    public function all(bool $authorizedOnly = TRUE): array
    {
        $this->discover();
        $definitions = array_values($this->definitions);

        return $authorizedOnly
            ? array_values(array_filter($definitions, static fn(DashboardDefinition $definition): bool => $definition->canView()))
            : $definitions;
    }

    public function get(string $id, bool $authorizedOnly = TRUE): ?DashboardDefinition
    {
        $this->discover();
        $definition = $this->definitions[$id] ?? NULL;
        if ($definition && (!$authorizedOnly || $definition->canView())) {
            return $definition;
        }

        return NULL;
    }

    private function discoverInstalledInfusions(): void
    {
        if (!defined('DB_INFUSIONS') || !defined('INFUSIONS') || !function_exists('dbquery')) {
            return;
        }

        try {
            $result = dbquery('SELECT inf_folder, inf_title FROM ' . DB_INFUSIONS . ' ORDER BY inf_title, inf_folder');
            while ($infusion = dbarray($result)) {
                $folder = trim((string)($infusion['inf_folder'] ?? ''));
                if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $folder)) {
                    $this->report('Skipped invalid dashboard infusion folder: ' . $folder);
                    continue;
                }

                $root = realpath(INFUSIONS);
                $infusionRoot = realpath(INFUSIONS . $folder);
                if (!$root || !$infusionRoot || !str_starts_with(strtolower($infusionRoot), strtolower($root . DIRECTORY_SEPARATOR))) {
                    continue;
                }

                $manifest = $infusionRoot . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR . 'widgets.php';
                if (is_file($manifest)) {
                    $title = trim((string)($infusion['inf_title'] ?? '')) ?: $folder;
                    $this->loadManifest($manifest, $folder, $title);
                }
            }
        } catch (Throwable $exception) {
            $this->report('Dashboard infusion discovery failed: ' . $exception->getMessage());
        }
    }

    private function loadManifest(string $manifest, string $source, string $sourceTitle): void
    {
        try {
            $widgets = require $manifest;
        } catch (Throwable $exception) {
            $this->report('Dashboard manifest failed: ' . $manifest . ' - ' . $exception->getMessage());
            return;
        }

        if (!is_array($widgets)) {
            $this->report('Dashboard manifest did not return an array: ' . $manifest);
            return;
        }

        foreach ($widgets as $id => $widget) {
            $id = trim((string)$id);
            if (!is_array($widget) || isset($this->definitions[$id])) {
                $this->report('Skipped duplicate or invalid dashboard widget: ' . $id);
                continue;
            }

            try {
                $this->definitions[$id] = DashboardDefinition::fromArray(
                    $id,
                    $widget,
                    $source,
                    $sourceTitle,
                    $this->locale
                );
            } catch (Throwable $exception) {
                $this->report($exception->getMessage());
            }
        }
    }

    private function report(string $message): void
    {
        if (function_exists('set_error')) {
            set_error(E_USER_WARNING, $message, __FILE__, __LINE__);
            return;
        }

        error_log($message);
    }
}
